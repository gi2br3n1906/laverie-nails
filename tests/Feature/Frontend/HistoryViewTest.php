<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Enums\UserRole;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_render_history_index_detail_and_print_views(): void
    {
        $owner = User::factory()->create();
        $measurement = Measurement::factory()->for($owner)->create([
            'classified_size_right' => 'OWNER-SIZE',
        ]);
        Measurement::factory()->for(User::factory())->create([
            'classified_size_right' => 'OTHER-SIZE',
        ]);

        $this->actingAs($owner)
            ->get(route('history.index'))
            ->assertOk()
            ->assertViewIs('measurements.history.index')
            ->assertSee('OWNER-SIZE')
            ->assertDontSee('OTHER-SIZE');

        $this->actingAs($owner)
            ->get(route('history.show', $measurement))
            ->assertOk()
            ->assertViewIs('measurements.history.show')
            ->assertSee('OWNER-SIZE');

        $this->actingAs($owner)
            ->get(route('history.print', $measurement))
            ->assertOk()
            ->assertViewIs('measurements.history.print')
            ->assertSee('OWNER-SIZE');
    }

    public function test_admin_browser_history_lists_all_records_but_policy_still_blocks_printing_unowned_records(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        $first = Measurement::factory()->for(User::factory())->create(['classified_size_right' => 'FIRST']);
        Measurement::factory()->create(['user_id' => null, 'classified_size_right' => 'GUEST']);

        $this->actingAs($admin)
            ->get(route('history.index'))
            ->assertOk()
            ->assertSee('FIRST')
            ->assertSee('GUEST');

        $this->actingAs($admin)
            ->get(route('history.print', $first))
            ->assertForbidden();
    }

    public function test_json_history_contract_remains_unchanged(): void
    {
        $owner = User::factory()->create();
        $measurement = Measurement::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->getJson(route('history.show', $measurement))
            ->assertOk()
            ->assertJsonPath('data.id', $measurement->id);
    }

    public function test_browser_delete_redirects_to_history_while_json_delete_remains_no_content(): void
    {
        $owner = User::factory()->create();
        $browserMeasurement = Measurement::factory()->for($owner)->create();
        $jsonMeasurement = Measurement::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->delete(route('history.destroy', $browserMeasurement))
            ->assertRedirect(route('history.index'));

        $this->actingAs($owner)
            ->deleteJson(route('history.destroy', $jsonMeasurement))
            ->assertNoContent();

        $this->assertModelMissing($browserMeasurement);
        $this->assertModelMissing($jsonMeasurement);
    }
}
