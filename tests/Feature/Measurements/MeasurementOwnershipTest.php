<?php

declare(strict_types=1);

namespace Tests\Feature\Measurements;

use App\Enums\UserRole;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasurementOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_measurement_history(): void
    {
        $measurement = Measurement::factory()->create();

        $this->get(route('history.index'))->assertRedirect(route('login'));
        $this->get(route('history.show', $measurement))->assertRedirect(route('login'));
    }

    public function test_customer_history_contains_only_their_own_records(): void
    {
        $owner = User::factory()->create();
        $ownMeasurement = Measurement::factory()->for($owner)->create();
        $otherMeasurement = Measurement::factory()->for(User::factory())->create();
        Measurement::factory()->create(['user_id' => null]);

        $this->actingAs($owner)
            ->getJson(route('history.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownMeasurement->id)
            ->assertJsonMissing(['id' => $otherMeasurement->id]);
    }

    public function test_owner_can_view_print_and_delete_their_measurement(): void
    {
        $owner = User::factory()->create();
        $measurement = Measurement::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->getJson(route('history.show', $measurement))
            ->assertOk()
            ->assertJsonPath('data.id', $measurement->id);

        $this->actingAs($owner)
            ->getJson(route('history.print', $measurement))
            ->assertOk()
            ->assertJsonPath('data.id', $measurement->id);

        $this->actingAs($owner)
            ->deleteJson(route('history.destroy', $measurement))
            ->assertNoContent();

        $this->assertModelMissing($measurement);
    }

    public function test_user_cannot_view_print_or_delete_another_users_measurement(): void
    {
        $intruder = User::factory()->create();
        $measurement = Measurement::factory()->for(User::factory())->create();

        $this->actingAs($intruder)->getJson(route('history.show', $measurement))->assertForbidden();
        $this->actingAs($intruder)->getJson(route('history.print', $measurement))->assertForbidden();
        $this->actingAs($intruder)->deleteJson(route('history.destroy', $measurement))->assertForbidden();

        $this->assertModelExists($measurement);
    }

    public function test_customer_cannot_access_an_ownerless_guest_measurement(): void
    {
        $customer = User::factory()->create();
        $measurement = Measurement::factory()->create(['user_id' => null]);

        $this->actingAs($customer)->getJson(route('history.show', $measurement))->assertForbidden();
        $this->actingAs($customer)->getJson(route('history.print', $measurement))->assertForbidden();
        $this->actingAs($customer)->deleteJson(route('history.destroy', $measurement))->assertForbidden();

        $this->assertModelExists($measurement);
    }

    public function test_admin_can_list_and_view_all_records_but_cannot_print_or_delete_unowned_records(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $ownedMeasurement = Measurement::factory()->for(User::factory())->create();
        $guestMeasurement = Measurement::factory()->create(['user_id' => null]);

        $this->actingAs($admin)
            ->getJson(route('history.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($admin)->getJson(route('history.show', $ownedMeasurement))->assertOk();
        $this->actingAs($admin)->getJson(route('history.show', $guestMeasurement))->assertOk();
        $this->actingAs($admin)->getJson(route('history.print', $ownedMeasurement))->assertForbidden();
        $this->actingAs($admin)->getJson(route('history.print', $guestMeasurement))->assertForbidden();
        $this->actingAs($admin)->deleteJson(route('history.destroy', $ownedMeasurement))->assertForbidden();

        $this->assertModelExists($ownedMeasurement);
    }
}
