<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Enums\UserRole;
use App\Models\CatalogReview;
use App\Models\NailCatalog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_review_a_public_catalog_once(): void
    {
        $user = User::factory()->create();
        $catalog = NailCatalog::factory()->create();

        $this->actingAs($user)
            ->post(route('products.reviews.store', $catalog), [
                'rating' => 5,
                'comment' => 'Beautiful fit and finish.',
            ])
            ->assertRedirect(route('products.show', $catalog));

        $review = CatalogReview::query()->sole();
        $this->assertSame($user->id, $review->user_id);
        $this->assertSame($catalog->id, $review->catalog_id);
        $this->assertSame(5, $review->rating);

        $this->actingAs($user)
            ->post(route('products.reviews.store', $catalog), [
                'rating' => 4,
                'comment' => 'Second review is forbidden.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('catalog_reviews', 1);
    }

    public function test_review_requires_authentication_and_valid_rating_and_comment(): void
    {
        $catalog = NailCatalog::factory()->create();
        $user = User::factory()->create();

        $this->post(route('products.reviews.store', $catalog), [
            'rating' => 5,
            'comment' => 'Valid review.',
        ])->assertRedirect(route('login'));

        $this->actingAs($user)
            ->post(route('products.reviews.store', $catalog), [
                'rating' => 6,
                'comment' => '',
            ])
            ->assertSessionHasErrors(['rating', 'comment']);
    }

    public function test_admin_cannot_submit_a_customer_review(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $catalog = NailCatalog::factory()->create();

        $this->actingAs($admin)
            ->post(route('products.reviews.store', $catalog), [
                'rating' => 5,
                'comment' => 'Admin review.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('catalog_reviews', 0);
    }

    public function test_inactive_catalog_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();
        $inactive = NailCatalog::factory()->inactive()->create();

        $this->actingAs($user)->post(route('products.reviews.store', $inactive), ['rating' => 4, 'comment' => 'No.'])->assertNotFound();
    }

    public function test_product_detail_displays_reviews_and_dynamic_average_rating(): void
    {
        $catalog = NailCatalog::factory()->create();
        CatalogReview::factory()->for($catalog, 'catalog')->create(['rating' => 5, 'comment' => 'Five star review']);
        CatalogReview::factory()->for($catalog, 'catalog')->create(['rating' => 3, 'comment' => 'Three star review']);

        $this->get(route('products.show', $catalog))
            ->assertOk()
            ->assertSee('4.0')
            ->assertSee('Five star review')
            ->assertSee('Three star review');
    }

    public function test_database_enforces_unique_reviews_and_reviews_cascade_with_catalog(): void
    {
        $user = User::factory()->create();
        $catalog = NailCatalog::factory()->create();
        CatalogReview::factory()->for($catalog, 'catalog')->for($user)->create();

        try {
            CatalogReview::factory()->for($catalog, 'catalog')->for($user)->create();
            $this->fail('The database accepted a duplicate catalog review.');
        } catch (QueryException) {
            $this->assertDatabaseCount('catalog_reviews', 1);
        }

        $catalog->delete();
        $this->assertDatabaseCount('catalog_reviews', 0);
    }
}
