<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LogisticsCitiesRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileInformationRequest;
use App\Http\Requests\Profile\UpdateShippingAddressRequest;
use App\Models\User;
use App\Services\LogisticsService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly LogisticsService $logisticsService,
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'provinces' => $this->logisticsService->provinces(),
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

    public function cities(LogisticsCitiesRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->logisticsService->cities((string) $request->validated('province_id')),
        ]);
    }
}
