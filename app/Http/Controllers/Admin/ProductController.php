<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->with(['category', 'primaryImage'])->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', ['categories' => Category::query()->orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create(
            $request->safe()->except(['images', 'new_primary_image_index']),
            $request->file('images', []),
            $request->filled('new_primary_image_index') ? $request->integer('new_primary_image_index') : null,
        );

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update(
            $product,
            $request->safe()->except(['images', 'primary_image_id', 'new_primary_image_index']),
            $request->file('images', []),
            $request->filled('primary_image_id') ? $request->integer('primary_image_id') : null,
            $request->filled('new_primary_image_index') ? $request->integer('new_primary_image_index') : null,
        );

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil dihapus.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        $this->productService->deleteImage($product, $image);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Gambar produk berhasil dihapus.');
    }
}
