<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_users_and_cannot_delete_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);

        // Regular member is forbidden from user management routes
        $this->actingAs($member)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('users.store'), [
                'name' => 'Unauthorized User',
                'email' => 'unauth@devdocs.local',
                'password' => 'secret123',
                'role' => 'member',
            ])
            ->assertForbidden();

        // Admin can view users list and create a new member
        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($member->email);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Rina Engineer',
                'email' => 'rina@devdocs.local',
                'password' => 'secret123',
                'role' => 'member',
            ])
            ->assertRedirect();

        $createdUser = User::where('email', 'rina@devdocs.local')->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('member', $createdUser->role);

        // Admin can update user role and delete other users
        $this->actingAs($admin)
            ->put(route('users.update', $createdUser), [
                'name' => 'Rina Senior',
                'email' => 'rina@devdocs.local',
                'role' => 'admin',
            ])
            ->assertRedirect();

        $this->assertSame('admin', $createdUser->fresh()->role);

        // Admin cannot delete themselves
        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Admin can delete another user
        $this->actingAs($admin)
            ->delete(route('users.destroy', $createdUser))
            ->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $createdUser->id]);
    }

    public function test_project_sharing_permissions_enforce_viewer_editor_and_manager_access(): void
    {
        $owner = User::factory()->create(['role' => 'member']);
        $viewer = User::factory()->create(['role' => 'member']);
        $editor = User::factory()->create(['role' => 'member']);
        $manager = User::factory()->create(['role' => 'member']);
        $outsider = User::factory()->create(['role' => 'member']);

        $project = Project::create([
            'owner_id' => $owner->id,
            'name' => 'Core Payment Gateway',
            'slug' => 'core-payment-gateway',
            'status' => 'active',
        ]);

        $doc = Document::create([
            'project_id' => $project->id,
            'author_id' => $owner->id,
            'last_editor_id' => $owner->id,
            'title' => 'Webhook Specification',
            'slug' => 'webhook-specification',
            'content_markdown' => '# Webhook Specification',
            'status' => 'published',
            'version' => 1,
        ]);

        // Owner shares project with viewer, editor, and manager
        $this->actingAs($owner)
            ->post(route('projects.members.store', $project->slug), [
                'user_id' => $viewer->id,
                'permission' => 'viewer',
            ])->assertRedirect();

        $this->actingAs($owner)
            ->post(route('projects.members.store', $project->slug), [
                'user_id' => $editor->id,
                'permission' => 'editor',
            ])->assertRedirect();

        $this->actingAs($owner)
            ->post(route('projects.members.store', $project->slug), [
                'user_id' => $manager->id,
                'permission' => 'manager',
            ])->assertRedirect();

        // Outsider has no access at all
        $this->actingAs($outsider)
            ->get(route('projects.show', $project->slug))
            ->assertForbidden();

        // Viewer can read project & document, but cannot edit, create folder, or manage project
        $this->actingAs($viewer)
            ->get(route('projects.show', $project->slug))
            ->assertOk()
            ->assertDontSee('+ Tulis Dokumen');

        $this->actingAs($viewer)
            ->get(route('documents.show', ['project' => $project->slug, 'document' => $doc->slug]))
            ->assertOk()
            ->assertDontSee('Edit Dokumen');

        $this->actingAs($viewer)
            ->get(route('documents.edit', ['project' => $project->slug, 'document' => $doc->slug]))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('projects.folders.store', $project->slug), ['name' => 'Illegal Folder'])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('projects.share.toggle', $project->slug))
            ->assertForbidden();

        // Editor can edit document and add folder, but cannot edit project settings or manage members
        $this->actingAs($editor)
            ->get(route('documents.edit', ['project' => $project->slug, 'document' => $doc->slug]))
            ->assertOk();

        $this->actingAs($editor)
            ->put(route('documents.update', ['project' => $project->slug, 'document' => $doc->slug]), [
                'title' => 'Webhook Specification v2',
                'content_markdown' => '# Updated by Editor',
                'status' => 'published',
                'version' => 1,
            ])
            ->assertRedirect();

        $this->assertSame('Webhook Specification v2', $doc->fresh()->title);

        $this->actingAs($editor)
            ->get(route('projects.edit', $project->slug))
            ->assertForbidden();

        $this->actingAs($editor)
            ->post(route('projects.members.store', $project->slug), [
                'user_id' => $outsider->id,
                'permission' => 'viewer',
            ])
            ->assertForbidden();

        // Manager can edit project settings and toggle public share
        $this->actingAs($manager)
            ->get(route('projects.edit', $project->slug))
            ->assertOk();

        $this->actingAs($manager)
            ->post(route('projects.share.toggle', $project->slug))
            ->assertRedirect();

        $this->assertTrue($project->fresh()->isPubliclyAccessible());
    }

    public function test_project_visibility_is_isolated_for_non_members(): void
    {
        $owner = User::factory()->create(['role' => 'member']);
        $otherUser = User::factory()->create(['role' => 'member']);

        $secretProject = Project::create([
            'owner_id' => $owner->id,
            'name' => 'Secret Infra Docs',
            'slug' => 'secret-infra-docs',
            'status' => 'active',
        ]);

        Document::create([
            'project_id' => $secretProject->id,
            'author_id' => $owner->id,
            'last_editor_id' => $owner->id,
            'title' => 'Secret Runbook',
            'slug' => 'secret-runbook',
            'content_markdown' => '# Top Secret',
            'status' => 'published',
            'version' => 1,
        ]);

        $this->actingAs($otherUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Secret Infra Docs')
            ->assertDontSee('Secret Runbook');

        $this->actingAs($otherUser)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('Secret Infra Docs');

        $this->actingAs($otherUser)
            ->get(route('search', ['q' => 'Secret']))
            ->assertOk()
            ->assertDontSee('Secret Infra Docs')
            ->assertDontSee('Secret Runbook');
    }

    public function test_team_members_can_add_and_delete_document_comments_and_record_activity_logs(): void
    {
        $owner = User::factory()->create(['role' => 'member', 'name' => 'Owner Dev']);
        $viewer = User::factory()->create(['role' => 'member', 'name' => 'Viewer QA']);

        $project = Project::create([
            'owner_id' => $owner->id,
            'name' => 'Order Service',
            'slug' => 'order-service',
            'status' => 'active',
        ]);

        $project->members()->attach($viewer->id, ['permission' => 'viewer']);

        $doc = Document::create([
            'project_id' => $project->id,
            'author_id' => $owner->id,
            'last_editor_id' => $owner->id,
            'title' => 'Checkout API',
            'slug' => 'checkout-api',
            'content_markdown' => '# Checkout API',
            'status' => 'published',
            'version' => 1,
        ]);

        // Viewer can post a comment on shared project's document
        $this->actingAs($viewer)
            ->post(route('documents.comments.store', ['project' => $project->slug, 'document' => $doc->slug]), [
                'body' => 'Tolong tambahkan contoh response 422 pada endpoint ini.',
            ])
            ->assertRedirect();

        $comment = DocumentComment::where('document_id', $doc->id)->first();
        $this->assertNotNull($comment);

        $this->actingAs($viewer)
            ->get(route('documents.show', ['project' => $project->slug, 'document' => $doc->slug]))
            ->assertOk()
            ->assertSee('Tolong tambahkan contoh response 422 pada endpoint ini.');

        // Activity log was recorded and is visible on dashboard
        $this->assertTrue(
            ActivityLog::where('project_id', $project->id)
                ->where('action', 'comment.added')
                ->exists()
        );

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas Tim Terbaru')
            ->assertSee('Viewer QA');

        // Viewer can delete their own comment
        $this->actingAs($viewer)
            ->delete(route('documents.comments.destroy', [
                'project' => $project->slug,
                'document' => $doc->slug,
                'comment' => $comment->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('document_comments', ['id' => $comment->id]);
    }
}
