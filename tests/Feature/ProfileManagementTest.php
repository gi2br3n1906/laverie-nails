<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_requires_authentication_and_renders_three_premium_sections(): void
    {
        $user = User::factory()->create(['province_id' => '10', 'city_id' => '399']);

        $this->get('/profile')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSeeText('Account Settings')
            ->assertSeeText('Personal Information')
            ->assertSeeText('Change Password')
            ->assertSeeText('Shipping Address')
            ->assertSee('name="address"', false)
            ->assertSee('name="province_code"', false)
            ->assertSee('data-region-form', false)
            ->assertSeeText('Jawa Tengah')
            ->assertDontSee('data-profile-address-form', false)
            ->assertDontSee('data-cities-url=', false);

        $source = file_get_contents(resource_path('views/profile/edit.blade.php'));
        $this->assertIsString($source);
        $this->assertDoesNotMatchRegularExpression('/style\s*=/i', $source);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $source);
    }

    public function test_user_can_update_personal_information_and_email_remains_unique(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->patch('/profile/information', [
            'name' => 'Alya Laverie',
            'email' => 'alya@example.com',
            'phone' => '+628123456789',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('Alya Laverie', $user->name);
        $this->assertSame('alya@example.com', $user->email);
        $this->assertSame('+628123456789', $user->phone);

        $this->actingAs($user)->from('/profile')->patch('/profile/information', [
            'name' => 'Alya Laverie',
            'email' => $other->email,
            'phone' => '+628123456789',
        ])->assertRedirect('/profile')->assertSessionHasErrors('email');
    }

    public function test_user_can_update_shipping_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile/address', [
            'address' => 'Jl. Melati No. 8, RT 03/RW 04, 50268',
            'province_code' => '33',
            'province_name' => 'Jawa Tengah',
            'regency_code' => '33.74',
            'regency_name' => 'Kota Semarang',
            'district_code' => '33.74.11',
            'district_name' => 'Banyumanik',
            'village_code' => '33.74.11.1001',
            'village_name' => 'Pudakpayung',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'address' => 'Jl. Melati No. 8, RT 03/RW 04, 50268',
            'province_code' => '33',
            'regency_code' => '33.74',
            'district_code' => '33.74.11',
            'village_code' => '33.74.11.1001',
        ]);
    }

    public function test_user_can_change_password_only_with_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->from('/profile')->put('/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'New-password-123',
            'password_confirmation' => 'New-password-123',
        ])->assertRedirect('/profile')->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'New-password-123',
            'password_confirmation' => 'New-password-123',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');
        $this->assertTrue(Hash::check('New-password-123', $user->refresh()->password));
    }

    public function test_google_only_user_can_create_a_password_without_the_current_password(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'provider' => 'google',
            'provider_id' => 'google-profile-1',
        ]);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSeeText('Create Password')
            ->assertDontSee('name="current_password"', false);

        $this->actingAs($user)->put('/profile/password', [
            'password' => 'New-password-123',
            'password_confirmation' => 'New-password-123',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('New-password-123', $user->refresh()->password));
    }

    public function test_profile_columns_exist_and_are_nullable(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['phone', 'address', 'province_id', 'city_id', 'province_code', 'regency_code', 'district_code', 'village_code']));
        $user = User::factory()->create();
        $this->assertNull($user->phone);
        $this->assertNull($user->address);
        $this->assertNull($user->province_code);
    }

    public function test_saved_account_details_prefill_authenticated_checkout(): void
    {
        $user = User::factory()->create([
            'phone' => '+628****1111',
            'address' => 'Jl. Default Checkout No. 1',
            'province_code' => '31',
            'province_name' => 'DKI Jakarta',
            'regency_code' => '31.74',
            'regency_name' => 'Kota Administrasi Jakarta Selatan',
            'district_code' => '31.74.09',
            'district_name' => 'Setiabudi',
            'village_code' => '31.74.09.1002',
            'village_name' => 'Kuningan Barat',
        ]);
        $product = Product::factory()->create(['price' => 100000, 'stock' => 2, 'is_active' => true]);
        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'size_payload' => ['size' => 'M'],
            'size_signature' => hash('sha256', '{"size":"M"}'),
        ]);

        $this->actingAs($user)->get('/checkout')
            ->assertOk()
            ->assertSee('+628****1111')
            ->assertSee('Jl. Default Checkout No. 1')
            ->assertSee('value="31" selected', false)
            ->assertSee('value="31.74" selected', false)
            ->assertDontSee('data-old-value="399"', false)
            ->assertDontSee('value="10" selected', false);
    }
}
