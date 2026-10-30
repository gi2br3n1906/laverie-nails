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
        ]);
    }

    public function updateInformation(UpdateProfileInformationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->profileService->updateInformation($user, $request->validated());

        return to_route('profile.edit')->with('status', 'Informasi pribadi berhasil diperbarui.');
    }

    public function updateAddress(UpdateShippingAddressRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->profileService->updateAddress($user, $request->validated());

        return to_route('profile.edit')->with('status', 'Alamat pengiriman berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->profileService->updatePassword($user, (string) $request->validated('password'));

        return to_route('profile.edit')->with('status', 'Password berhasil diperbarui.');
    }
}
