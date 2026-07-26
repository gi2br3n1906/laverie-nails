<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_categories(): void
    {
        $this->get('/admin/categories')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/admin/categories')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/categories')->assertOk()->assertSee('Kelola Kategori');
    }

    public function test_admin_can_create_update_and_delete_a_category_with_generated_slug(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Soft Glam'])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->sole();
        $this->assertSame('soft-glam', $category->slug);

        $this->actingAs($admin)->put('/admin/categories/'.$category->id, ['name' => 'Modern Muse'])
            ->assertRedirect(route('admin.categories.index'));
        $this->assertSame('modern-muse', $category->refresh()->slug);

        $this->actingAs($admin)->delete('/admin/categories/'.$category->id)
            ->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_category_name_and_slug_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Classy', 'slug' => 'classy']);

        $this->actingAs($this->admin())->post('/admin/categories', ['name' => 'Classy'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    private function admin(): User
    {
        return User::factory()->withRole(UserRole::Admin)->create();
    }
}
