<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.base_url' => 'https://rajaongkir.test/starter',
            'services.rajaongkir.api_key' => 'rajaongkir-test-key',
        ]);
    }

    public function test_profile_page_requires_authentication_and_renders_three_premium_sections(): void
    {
        Http::fake([
            '*province*' => Http::response(['rajaongkir' => ['results' => [
                ['province_id' => '10', 'province' => 'Jawa Tengah'],
            ]]]),
        ]);
        $user = User::factory()->create(['province_id' => '10', 'city_id' => '399']);

        $this->get('/profile')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSeeText('Pengaturan Akun')
            ->assertSeeText('Informasi Pribadi')
            ->assertSeeText('Ganti Password')
            ->assertSeeText('Alamat Pengiriman')
            ->assertSee('data-profile-address-form', false)
            ->assertSee('data-cities-url=', false)
            ->assertSeeText('Jawa Tengah');

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

    public function test_user_can_update_shipping_address_and_load_cities_for_a_province(): void
    {
        Http::fake([
            '*city*' => Http::response(['rajaongkir' => ['results' => [
                ['city_id' => '399', 'type' => 'Kota', 'city_name' => 'Semarang'],
            ]]]),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile/address', [
            'address' => 'Jl. Melati No. 8, Banyumanik',
            'province_id' => '10',
            'city_id' => '399',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'address' => 'Jl. Melati No. 8, Banyumanik',
            'province_id' => '10',
            'city_id' => '399',
        ]);
        $this->actingAs($user)->get('/profile/logistics/cities?province_id=10')
            ->assertOk()->assertJsonPath('data.0.name', 'Kota Semarang');
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

    public function test_profile_columns_exist_and_are_nullable(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['phone', 'address', 'province_id', 'city_id']));
        $user = User::factory()->create();
        $this->assertNull($user->phone);
        $this->assertNull($user->address);
    }

    public function test_saved_account_details_prefill_authenticated_checkout(): void
    {
        Http::fake([
            '*province*' => Http::response(['rajaongkir' => ['results' => [
                ['province_id' => '10', 'province' => 'Jawa Tengah'],
            ]]]),
        ]);
        $user = User::factory()->create([
            'phone' => '+628111111111',
            'address' => 'Jl. Default Checkout No. 1',
            'province_id' => '10',
            'city_id' => '399',
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
            ->assertSee('+628111111111')
            ->assertSee('Jl. Default Checkout No. 1')
            ->assertSee('data-old-value="399"', false)
            ->assertSee('value="10" selected', false);
    }
}
