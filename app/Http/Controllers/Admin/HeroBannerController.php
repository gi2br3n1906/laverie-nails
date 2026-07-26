<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHeroBannerRequest;
use App\Http\Requests\Admin\UpdateHeroBannerRequest;
use App\Models\HeroBanner;
use App\Services\HeroBannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeroBannerController extends Controller
{
    public function __construct(private readonly HeroBannerService $bannerService) {}

    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => HeroBanner::query()->orderBy('sequence')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(StoreHeroBannerRequest $request): RedirectResponse
    {
        $this->bannerService->create(
            $request->safe()->except('image'),
            $request->file('image'),
        );

        return redirect()->route('admin.banners.index')->with('status', 'Hero banner berhasil ditambahkan.');
    }

    public function edit(HeroBanner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(UpdateHeroBannerRequest $request, HeroBanner $banner): RedirectResponse
    {
        $this->bannerService->update(
            $banner,
            $request->safe()->except('image'),
            $request->file('image'),
        );

        return redirect()->route('admin.banners.index')->with('status', 'Hero banner berhasil diperbarui.');
    }

    public function destroy(HeroBanner $banner): RedirectResponse
    {
        $this->bannerService->delete($banner);

        return redirect()->route('admin.banners.index')->with('status', 'Hero banner berhasil dihapus.');
    }
}
