<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Enums\UserRole;
use App\Models\CatalogReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_review_a_public_product_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('storefront.products.reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Beautiful fit and finish.',
            ])
            ->assertRedirect(route('storefront.products.show', $product));

        $review = CatalogReview::query()->sole();
        $this->assertSame($user->id, $review->user_id);
        $this->assertSame($product->id, $review->product_id);
        $this->assertSame(5, $review->rating);

        $this->actingAs($user)
            ->post(route('storefront.products.reviews.store', $product), [
                'rating' => 4,
                'comment' => 'Second review is forbidden.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('catalog_reviews', 1);
    }

    public function test_review_requires_authentication_and_valid_rating_and_comment(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $this->post(route('storefront.products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Valid review.',
        ])->assertRedirect(route('login'));

        $this->actingAs($user)
            ->post(route('storefront.products.reviews.store', $product), [
                'rating' => 6,
                'comment' => '',
            ])
            ->assertSessionHasErrors(['rating', 'comment']);
    }

    public function test_admin_cannot_submit_a_customer_review(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->post(route('storefront.products.reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Admin review.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('catalog_reviews', 0);
    }

    public function test_inactive_product_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();
        $inactive = Product::factory()->inactive()->create();

        $this->actingAs($user)
            ->post(route('storefront.products.reviews.store', $inactive), ['rating' => 4, 'comment' => 'No.'])
            ->assertNotFound();
    }

    public function test_product_detail_displays_reviews_and_dynamic_average_rating(): void
    {
        $product = Product::factory()->create();
        CatalogReview::factory()->for($product)->create(['rating' => 5, 'comment' => 'Five star review']);
        CatalogReview::factory()->for($product)->create(['rating' => 3, 'comment' => 'Three star review']);

        $this->get(route('storefront.products.show', $product))
            ->assertOk()
            ->assertSee('4.0')
            ->assertSee('Five star review')
            ->assertSee('Three star review');
    }

    public function test_database_enforces_unique_reviews_and_reviews_cascade_with_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        CatalogReview::factory()->for($product)->for($user)->create();

        try {
            CatalogReview::factory()->for($product)->for($user)->create();
            $this->fail('The database accepted a duplicate product review.');
        } catch (QueryException) {
            $this->assertDatabaseCount('catalog_reviews', 1);
        }

        $product->delete();
        $this->assertDatabaseCount('catalog_reviews', 0);
    }
}
