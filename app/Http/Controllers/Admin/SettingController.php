<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShippingRatesRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'javaCost' => (int) Setting::get('shipping.java_cost', config('shipping.defaults.java')),
            'outsideJavaCost' => (int) Setting::get('shipping.outside_java_cost', config('shipping.defaults.outside_java')),
            'guidanceVideoUrl' => (string) Setting::get('guidance.video_url', ''),
        ]);
    }

    public function update(UpdateShippingRatesRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Setting::putMany([
            'shipping.java_cost' => (string) $data['java_cost'],
            'shipping.outside_java_cost' => (string) $data['outside_java_cost'],
            'guidance.video_url' => (string) ($data['guidance_video_url'] ?? ''),
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Tarif ongkir berhasil disimpan.');
    }
}
