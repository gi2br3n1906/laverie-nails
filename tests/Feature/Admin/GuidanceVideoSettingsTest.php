<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuidanceVideoSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_update_the_guidance_youtube_url(): void
    {
        $admin = User::factory()->create(['roles' => [UserRole::Admin->value]]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Video Panduan')
            ->assertSee('youtube.com');

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), [
                'java_cost' => 15000,
                'outside_java_cost' => 20000,
                'guidance_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status');

        $this->assertSame(
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            Setting::get('guidance.video_url'),
        );

        $this->get(route('guidance'))
            ->assertOk()
            ->assertSee('data-guidance-video', false)
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ', false)
            ->assertDontSee('data-guidance-video-placeholder', false);
    }

    public function test_guidance_video_url_accepts_youtube_formats_and_rejects_other_hosts(): void
    {
        $admin = User::factory()->create(['roles' => [UserRole::Admin->value]]);
        $payload = [
            'java_cost' => 15000,
            'outside_java_cost' => 20000,
        ];

        $this->actingAs($admin)
            ->from(route('admin.settings.edit'))
            ->patch(route('admin.settings.update'), $payload + [
                'guidance_video_url' => 'https://vimeo.com/123456789',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors('guidance_video_url');

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), $payload + [
                'guidance_video_url' => 'https://youtu.be/dQw4w9WgXcQ?t=42',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasNoErrors();

        $this->assertSame('https://youtu.be/dQw4w9WgXcQ?t=42', Setting::get('guidance.video_url'));
    }

    public function test_non_admin_cannot_update_the_guidance_video_url(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch(route('admin.settings.update'), [
                'java_cost' => 15000,
                'outside_java_cost' => 20000,
                'guidance_video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            ])
            ->assertForbidden();
    }
}
