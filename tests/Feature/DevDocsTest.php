<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Folder;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DevDocsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@devdocs.local',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_authenticated_user_can_access_dashboard_and_projects(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Payment Center');
    }

    public function test_can_view_document_reader_with_markdown_and_mermaid(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();
        $doc = Document::where('project_id', $project->id)->where('slug', 'qris-mpm-callback')->first();

        $response = $this->actingAs($user)->get("/projects/{$project->slug}/docs/{$doc->slug}");
        $response->assertStatus(200);
        $response->assertSee($doc->title);
        $response->assertSee('sequenceDiagram');
    }

    public function test_autosave_updates_content_and_increments_version(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();
        $doc = Document::where('project_id', $project->id)->where('slug', 'qris-mpm-callback')->first();

        $initialVersion = $doc->version;

        $response = $this->actingAs($user)->postJson("/projects/{$project->slug}/docs/{$doc->slug}/autosave", [
            'title' => 'QRIS MPM Callback (Updated via Autosave)',
            'content_markdown' => '# Updated Content',
            'version' => $initialVersion,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'version' => $initialVersion + 1,
        ]);

        $doc->refresh();
        $this->assertEquals($initialVersion + 1, $doc->version);
        $this->assertEquals('QRIS MPM Callback (Updated via Autosave)', $doc->title);
    }

    public function test_autosave_detects_optimistic_locking_conflict(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();
        $doc = Document::where('project_id', $project->id)->where('slug', 'qris-mpm-callback')->first();

        // Send an outdated version number (e.g. 999 instead of current version)
        $response = $this->actingAs($user)->postJson("/projects/{$project->slug}/docs/{$doc->slug}/autosave", [
            'title' => 'Conflicted Title',
            'content_markdown' => '# Conflict',
            'version' => 999,
        ]);

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'conflict' => true,
        ]);
    }

    public function test_search_finds_relevant_documents(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $doc = Document::where('slug', 'qris-mpm-callback')->first();

        $response = $this->actingAs($user)->get('/search?q=QRIS');
        $response->assertStatus(200);
        $response->assertSee($doc->title);
    }

    public function test_can_delete_document_using_soft_delete(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();
        $doc = Document::create([
            'project_id' => $project->id,
            'author_id' => $user->id,
            'title' => 'Dokumen Dihapus Test',
            'slug' => 'dokumen-dihapus-test',
            'content_markdown' => '# Hapus',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->delete("/projects/{$project->slug}/docs/{$doc->slug}");
        $response->assertRedirect("/projects/{$project->slug}");

        $this->assertSoftDeleted('documents', [
            'id' => $doc->id,
        ]);
    }

    public function test_can_delete_project_and_its_associated_data(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::create([
            'owner_id' => $user->id,
            'name' => 'Proyek Uji Coba Hapus',
            'slug' => 'proyek-uji-coba-hapus',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete("/projects/{$project->slug}");
        $response->assertRedirect('/projects');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_sidebar_is_rendered_with_navigation_and_projects(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();

        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(200);
        $response->assertSee('DevDocs');
        $response->assertSee('Technical Hub');
        $response->assertSee('Navigasi Utama');
        $response->assertSee('Dashboard');
        $response->assertSee('Daftar Proyek');
        $response->assertSee('Proyek Saya');
        $response->assertSee('openDeleteProjectModal');
    }

    public function test_can_move_document_to_different_folder(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();

        // Create a fresh doc and a fresh target folder so the test is fully isolated
        $sourceFolder = Folder::create(['project_id' => $project->id, 'name' => 'Source Folder Move Test', 'position' => 99]);
        $targetFolder = Folder::create(['project_id' => $project->id, 'name' => 'Target Folder Move Test', 'position' => 100]);
        $doc = Document::create([
            'project_id' => $project->id,
            'folder_id' => $sourceFolder->id,
            'author_id' => $user->id,
            'last_editor_id' => $user->id,
            'title' => 'Move Test Document',
            'slug' => 'move-test-document-' . uniqid(),
            'status' => 'draft',
            'version' => 1,
        ]);

        $response = $this->actingAs($user)->patch(
            "/projects/{$project->slug}/docs/{$doc->slug}/move",
            ['folder_id' => $targetFolder->id]
        );

        $response->assertRedirect("/projects/{$project->slug}/docs/{$doc->slug}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('documents', [
            'id' => $doc->id,
            'folder_id' => $targetFolder->id,
        ]);
    }

    public function test_can_move_document_to_root(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();

        // Create a fresh doc inside a folder so we can move it to root
        $folder = Folder::create(['project_id' => $project->id, 'name' => 'Root Move Source Folder', 'position' => 99]);
        $doc = Document::create([
            'project_id' => $project->id,
            'folder_id' => $folder->id,
            'author_id' => $user->id,
            'last_editor_id' => $user->id,
            'title' => 'Move To Root Test Document',
            'slug' => 'move-to-root-test-' . uniqid(),
            'status' => 'draft',
            'version' => 1,
        ]);

        $response = $this->actingAs($user)->patch(
            "/projects/{$project->slug}/docs/{$doc->slug}/move",
            ['folder_id' => null]
        );

        $response->assertRedirect("/projects/{$project->slug}/docs/{$doc->slug}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('documents', [
            'id' => $doc->id,
            'folder_id' => null,
        ]);
    }

    public function test_cannot_move_document_to_folder_from_different_project(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();
        $doc = Document::where('project_id', $project->id)->first();

        // Create an isolated other-project with its own folder for this test
        $otherProject = Project::create([
            'owner_id' => $user->id,
            'name' => 'Other Project Cross Move Test',
            'slug' => 'other-project-cross-move-' . uniqid(),
            'status' => 'active',
        ]);
        $otherFolder = Folder::create([
            'project_id' => $otherProject->id,
            'name' => 'Cross Project Folder',
            'position' => 1,
        ]);

        $response = $this->actingAs($user)->patch(
            "/projects/{$project->slug}/docs/{$doc->slug}/move",
            ['folder_id' => $otherFolder->id]
        );

        // Should redirect back with an error — folder not in this project
        $response->assertSessionHasErrors('folder_id');
    }

    public function test_creating_document_redirects_directly_to_editor(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();

        $title = 'Test Redirect Editor ' . uniqid();
        $response = $this->actingAs($user)->post('/documents', [
            'project_id' => $project->id,
            'folder_id' => null,
            'title' => $title,
            'summary' => 'Ringkasan test',
            'content_markdown' => '# Hello Editor',
            'status' => 'draft',
        ]);

        $doc = Document::where('project_id', $project->id)->where('title', $title)->firstOrFail();
        $response->assertRedirect(route('documents.edit', ['project' => $project->slug, 'document' => $doc->slug]));
    }

    public function test_cannot_create_document_with_folder_from_different_project(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();

        $otherProject = Project::create([
            'owner_id' => $user->id,
            'name' => 'Other Project Create Test',
            'slug' => 'other-project-create-' . uniqid(),
            'status' => 'active',
        ]);
        $otherFolder = Folder::create([
            'project_id' => $otherProject->id,
            'name' => 'Other Project Folder',
            'position' => 1,
        ]);

        $response = $this->actingAs($user)->post('/documents', [
            'project_id' => $project->id,
            'folder_id' => $otherFolder->id,
            'title' => 'Invalid Cross Folder Doc',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('folder_id');
    }

    public function test_scoped_route_binding_blocks_cross_project_document_access(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $project = Project::where('slug', 'payment-center')->first();
        $doc = Document::where('project_id', $project->id)->first();

        $otherProject = Project::create([
            'owner_id' => $user->id,
            'name' => 'Other Project Scope Test',
            'slug' => 'other-project-scope-' . uniqid(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get("/projects/{$otherProject->slug}/docs/{$doc->slug}");
        $response->assertStatus(404);
    }

    public function test_user_can_view_account_settings_page(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();

        $response = $this->actingAs($user)->get('/account');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_profile_name_and_email(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();
        $newEmail = 'updated-' . uniqid() . '@devdocs.local';

        $response = $this->actingAs($user)->put('/account/profile', [
            'name' => 'Developer Updated Name',
            'email' => $newEmail,
        ]);

        $response->assertRedirect('/account');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Developer Updated Name',
            'email' => $newEmail,
        ]);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();

        $response = $this->actingAs($user)->put('/account/password', [
            'current_password' => 'password',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ]);

        $response->assertRedirect('/account');
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewSecret123', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::where('email', 'admin@devdocs.local')->first();

        $response = $this->actingAs($user)->put('/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
