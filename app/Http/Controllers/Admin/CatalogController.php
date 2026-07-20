<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CatalogSize;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNailCatalogRequest;
use App\Http\Requests\Admin\UpdateNailCatalogRequest;
use App\Models\NailCatalog;
use App\Services\NailCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly NailCatalogService $catalogService) {}

    public function index(): View
    {
        return view('admin.catalogs.index', ['catalogs' => NailCatalog::query()->latest()->get()]);
    }

    public function create(): View
    {
        return view('admin.catalogs.create', ['sizes' => CatalogSize::cases()]);
    }

    public function store(StoreNailCatalogRequest $request): RedirectResponse
    {
        $catalog = $this->catalogService->create(
            $request->safe()->except('images'),
            $request->file('images', []),
        );

        return redirect()->route('admin.catalogs.show', $catalog)->with('status', 'Produk Laverie berhasil ditambahkan.');
    }

    public function show(NailCatalog $catalog): View
    {
        return view('admin.catalogs.show', compact('catalog'));
    }

    public function edit(NailCatalog $catalog): View
    {
        return view('admin.catalogs.edit', ['catalog' => $catalog, 'sizes' => CatalogSize::cases()]);
    }

    public function update(UpdateNailCatalogRequest $request, NailCatalog $catalog): RedirectResponse
    {
        $this->catalogService->update(
            $catalog,
            $request->safe()->except('images'),
            $request->file('images', []),
        );

        return redirect()->route('admin.catalogs.show', $catalog)->with('status', 'Produk Laverie berhasil diperbarui.');
    }

    public function destroy(NailCatalog $catalog): RedirectResponse
    {
        $this->catalogService->delete($catalog);

        return redirect()->route('admin.catalogs.index')->with('status', 'Produk Laverie berhasil dihapus.');
    }
}
