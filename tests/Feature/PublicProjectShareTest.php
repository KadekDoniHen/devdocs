<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProjectShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_toggle_and_regenerate_public_share_link(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'owner_id' => $user->id,
            'name' => 'Public API Docs',
            'slug' => 'public-api-docs',
            'status' => 'active',
        ]);

        $this->assertFalse($project->isPubliclyAccessible());

        // Enable public sharing
        $this->actingAs($user)
            ->post(route('projects.share.toggle', $project->slug))
            ->assertRedirect();

        $project->refresh();
        $this->assertTrue($project->isPubliclyAccessible());
        $this->assertNotEmpty($project->share_token);

        $firstToken = $project->share_token;

        // Regenerate share token
        $this->actingAs($user)
            ->post(route('projects.share.regenerate', $project->slug))
            ->assertRedirect();

        $project->refresh();
        $this->assertTrue($project->isPubliclyAccessible());
        $this->assertNotSame($firstToken, $project->share_token);

        // Disable public sharing
        $this->actingAs($user)
            ->post(route('projects.share.toggle', $project->slug))
            ->assertRedirect();

        $project->refresh();
        $this->assertFalse($project->isPubliclyAccessible());
    }

    public function test_guest_can_view_public_project_and_published_documents_without_login(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'owner_id' => $user->id,
            'name' => 'Shared Architecture',
            'slug' => 'shared-architecture',
            'description' => 'Dokumentasi publik read-only.',
            'status' => 'active',
            'is_public' => true,
            'share_token' => Project::generateShareToken(),
        ]);

        $publishedDoc = Document::create([
            'project_id' => $project->id,
            'author_id' => $user->id,
            'last_editor_id' => $user->id,
            'title' => 'Panduan Integrasi',
            'slug' => 'panduan-integrasi',
            'content_markdown' => '# Panduan Integrasi',
            'status' => 'published',
            'version' => 1,
        ]);

        $draftDoc = Document::create([
            'project_id' => $project->id,
            'author_id' => $user->id,
            'last_editor_id' => $user->id,
            'title' => 'Catatan Internal Rahasia',
            'slug' => 'catatan-internal-rahasia',
            'content_markdown' => '# Draft Rahasia',
            'status' => 'draft',
            'version' => 1,
        ]);

        // Guest can access public project overview and only sees published document
        $this->get(route('public.projects.show', ['token' => $project->share_token]))
            ->assertOk()
            ->assertSee('Shared Architecture')
            ->assertSee('Panduan Integrasi')
            ->assertDontSee('Catatan Internal Rahasia')
            ->assertDontSee('Edit Dokumen');

        // Guest can view published document (read-only)
        $this->get(route('public.projects.document', [
            'token' => $project->share_token,
            'documentSlug' => $publishedDoc->slug,
        ]))
            ->assertOk()
            ->assertSee('Panduan Integrasi')
            ->assertDontSee('Edit Dokumen')
            ->assertDontSee('Hapus Dokumen');

        // Guest cannot view draft document
        $this->get(route('public.projects.document', [
            'token' => $project->share_token,
            'documentSlug' => $draftDoc->slug,
        ]))->assertNotFound();
    }

    public function test_private_project_returns_404_on_public_share_url(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'owner_id' => $user->id,
            'name' => 'Private Project',
            'slug' => 'private-project',
            'status' => 'active',
            'is_public' => false,
            'share_token' => Project::generateShareToken(),
        ]);

        $this->get(route('public.projects.show', ['token' => $project->share_token]))
            ->assertNotFound();
    }

    public function test_guest_cannot_access_authenticated_edit_or_share_routes(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'owner_id' => $user->id,
            'name' => 'Protected Project',
            'slug' => 'protected-project',
            'status' => 'active',
            'is_public' => true,
            'share_token' => Project::generateShareToken(),
        ]);

        $this->get(route('projects.edit', $project->slug))
            ->assertRedirect(route('login'));

        $this->post(route('projects.share.toggle', $project->slug))
            ->assertRedirect(route('login'));
    }
}
