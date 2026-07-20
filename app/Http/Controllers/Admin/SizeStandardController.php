<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSizeStandardRequest;
use App\Http\Requests\Admin\UpdateSizeStandardRequest;
use App\Models\SizeStandard;
use App\Services\SizeStandardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SizeStandardController extends Controller
{
    public function __construct(private readonly SizeStandardService $sizeStandardService) {}

    public function index(): View
    {
        return view('admin.size-standards.index', [
            'sizeStandards' => $this->sizeStandardService->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.size-standards.create');
    }

    public function store(StoreSizeStandardRequest $request): RedirectResponse
    {
        $sizeStandard = $this->sizeStandardService->create($request->validated());

        return redirect()
            ->route('admin.size-standards.show', $sizeStandard)
            ->with('status', 'Size standard created successfully.');
    }

    public function show(SizeStandard $sizeStandard): View
    {
        return view('admin.size-standards.show', compact('sizeStandard'));
    }

    public function edit(SizeStandard $sizeStandard): View
    {
        return view('admin.size-standards.edit', compact('sizeStandard'));
    }

    public function update(UpdateSizeStandardRequest $request, SizeStandard $sizeStandard): RedirectResponse
    {
        $sizeStandard = $this->sizeStandardService->update($sizeStandard, $request->validated());

        return redirect()
            ->route('admin.size-standards.show', $sizeStandard)
            ->with('status', 'Size standard updated successfully.');
    }

    public function destroy(SizeStandard $sizeStandard): RedirectResponse
    {
        $this->sizeStandardService->delete($sizeStandard);

        return redirect()
            ->route('admin.size-standards.index')
            ->with('status', 'Size standard deleted successfully.');
    }
}
