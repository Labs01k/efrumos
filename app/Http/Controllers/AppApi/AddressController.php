<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use App\Models\FrontUserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * E6-BES-02 (часть «общее с приложением») — адреса для приложения, один
 * источник данных с сайтом (`front_user_addresses`, те же таблицы и правила,
 * что `Front\AddressController`) — личный кабинет и приложение видят один и
 * тот же список, не отдельную копию.
 *
 * Идентификация — та же, что у EFA-77 (избранное): `front_user.app_loyalty_client_id`
 * резолвится из `X-App-Client-Id`. Без подтверждённого телефона такого
 * `front_user` не найдётся (404, не 500) — то же известное ограничение,
 * что у избранного.
 */
class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $addresses = FrontUserAddress::where('front_user_id', $frontUser->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['front_user_id'] = $frontUser->id;

        $isFirst = !FrontUserAddress::where('front_user_id', $frontUser->id)->exists();
        $data['is_default'] = $isFirst || (bool) ($request->input('is_default') ?? false);

        $address = DB::transaction(function () use ($data, $frontUser) {
            if ($data['is_default']) {
                FrontUserAddress::where('front_user_id', $frontUser->id)->update(['is_default' => false]);
            }

            return FrontUserAddress::create($data);
        });

        return response()->json(['success' => true, 'data' => $address], 201);
    }

    public function update(Request $request, int $addressId): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $address = FrontUserAddress::where('id', $addressId)->where('front_user_id', $frontUser->id)->first();

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found.'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $address, $request, $frontUser) {
            if ($request->boolean('is_default')) {
                FrontUserAddress::where('front_user_id', $frontUser->id)->update(['is_default' => false]);
                $data['is_default'] = true;
            }

            $address->update($data);
        });

        return response()->json(['success' => true, 'data' => $address->fresh()]);
    }

    public function destroy(Request $request, int $addressId): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $address = FrontUserAddress::where('id', $addressId)->where('front_user_id', $frontUser->id)->first();

        if (!$address) {
            // Идемпотентно: удаление отсутствующего адреса — тоже успех.
            return response()->json(['success' => true]);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            FrontUserAddress::where('front_user_id', $frontUser->id)->orderByDesc('id')->first()?->update(['is_default' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function setDefault(Request $request, int $addressId): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $address = FrontUserAddress::where('id', $addressId)->where('front_user_id', $frontUser->id)->first();

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found.'], 404);
        }

        DB::transaction(function () use ($address, $frontUser) {
            FrontUserAddress::where('front_user_id', $frontUser->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json(['success' => true]);
    }

    private function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:100'],
            'district_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:191'],
            'city_area' => ['nullable', 'string', 'max:191'],
            'address' => ['required', 'string', 'max:255'],
            'apartment' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    private function resolveFrontUser(Request $request): ?FrontUser
    {
        $clientId = trim((string) $request->header('X-App-Client-Id'));

        if ($clientId === '' || strlen($clientId) > 64) {
            return null;
        }

        return FrontUser::where('app_loyalty_client_id', $clientId)->first();
    }

    private function notLinked(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Phone not confirmed — no linked site account for this client.',
        ], 404);
    }
}
