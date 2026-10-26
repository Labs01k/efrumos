<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\FrontUserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * E6-BES-02 — CRUD адресов доставки для личного кабинета сайта. «Общие с
 * приложением» по задаче — но приложение адресует пользователя по
 * client_id, а эта таблица по front_user_id; без связки аккаунтов (E1,
 * ещё не готов целиком) приложению отсюда напрямую не прочитать свои
 * адреса. Здесь — только сторона сайта, она самостоятельно нужна уже
 * сейчас (чекаут сайта), не только ради приложения.
 */
class AddressController extends Controller
{
    public function ajaxListAddresses(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $addresses = FrontUserAddress::where('front_user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json(['status' => true, 'addresses' => $addresses]);
    }

    public function ajaxCreateAddress(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $data = $validator->validated();
        $data['front_user_id'] = $user->id;

        $isFirst = !FrontUserAddress::where('front_user_id', $user->id)->exists();
        $data['is_default'] = $isFirst || (bool) ($request->input('is_default') ?? false);

        $address = DB::transaction(function () use ($data, $user) {
            if ($data['is_default']) {
                FrontUserAddress::where('front_user_id', $user->id)->update(['is_default' => false]);
            }

            return FrontUserAddress::create($data);
        });

        return response()->json(['status' => true, 'address' => $address]);
    }

    public function ajaxUpdateAddress(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $address = FrontUserAddress::where('id', (int) $request->input('id'))
            ->where('front_user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Адрес не найден.']);
        }

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $address, $request, $user) {
            if ($request->boolean('is_default')) {
                FrontUserAddress::where('front_user_id', $user->id)->update(['is_default' => false]);
                $data['is_default'] = true;
            }

            $address->update($data);
        });

        return response()->json(['status' => true, 'address' => $address->fresh()]);
    }

    public function ajaxDeleteAddress(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $address = FrontUserAddress::where('id', (int) $request->input('id'))
            ->where('front_user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Адрес не найден.']);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            // Без "адреса по умолчанию" чекаут не должен молча остаться без
            // выбора — переносим флаг на любой оставшийся адрес, если есть.
            FrontUserAddress::where('front_user_id', $user->id)->orderByDesc('id')->first()?->update(['is_default' => true]);
        }

        return response()->json(['status' => true]);
    }

    public function ajaxSetDefaultAddress(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json(['status' => false, 'message' => ShowLabelById(62)]);
        }

        $address = FrontUserAddress::where('id', (int) $request->input('id'))
            ->where('front_user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Адрес не найден.']);
        }

        DB::transaction(function () use ($address, $user) {
            FrontUserAddress::where('front_user_id', $user->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json(['status' => true]);
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
}
