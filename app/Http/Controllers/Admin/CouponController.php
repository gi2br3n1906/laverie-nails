<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::query()->orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        Coupon::query()->create($this->couponData($request->validated()));

        return redirect()->route('admin.coupons.index')->with('status', 'Promo berhasil dibuat.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->couponData($request->validated()));

        return redirect()->route('admin.coupons.index')->with('status', 'Promo berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', 'Promo berhasil dihapus.');
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function couponData(array $data): array
    {
        $percentage = $data['discount_type'] === 'percentage';

        return [
            'code' => strtoupper(trim($data['code'])),
            'discount_type' => $data['discount_type'],
            'discount_percentage' => $percentage ? (int) $data['discount_percentage'] : 0,
            'discount_amount' => $percentage ? 0 : (int) $data['discount_amount'],
            'minimum_order_amount' => (int) $data['minimum_order_amount'],
            'expires_at' => $data['expires_at'] ?: null,
            'is_active' => (bool) $data['is_active'],
        ];
    }
}
