<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileInformationRequest;
use App\Http\Requests\Profile\UpdateShippingAddressRequest;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'provinces' => config('regions.provinces', []),
        ]);
    }

    public function updateInformation(UpdateProfileInformationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->profileService->updateInformation($user, $request->validated());

        return to_route('profile.edit')->with('status', __('ui.profile.info_updated'));
    }

    public function updateAddress(UpdateShippingAddressRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->profileService->updateAddress($user, $request->validated());

        return to_route('profile.edit')->with('status', __('ui.profile.address_updated'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $hadPassword = $user->hasPassword();
        $this->profileService->updatePassword($user, (string) $request->validated('password'));

        return to_route('profile.edit')->with(
            'status',
            $hadPassword ? 'Password berhasil diperbarui.' : 'Password berhasil dibuat. Sekarang Anda bisa login dengan email dan password.',
        );
    }
}
