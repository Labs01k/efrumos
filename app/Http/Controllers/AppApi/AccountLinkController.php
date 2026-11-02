<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Services\AccountLink\AccountLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * EFA-35 / E1-BE-04 — бэкенд приложения погашает одноразовый токен,
 * выданный сайтом после логина на странице режима связки (EFA-43 /
 * AccountLinkController::buildAppRedirectUrl). Погашение одноразовое —
 * повторный вызов с тем же токеном получит 409.
 */
class AccountLinkController extends Controller
{
    public function redeem(Request $request, AccountLinkService $service): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $frontUser = $service->redeem((string) $request->input('token'));
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'front_user_id' => $frontUser->id,
                'name' => $frontUser->name,
                'last_name' => $frontUser->last_name,
                'email' => $frontUser->email,
                'phone' => $frontUser->phone,
                'phone_confirmed' => $frontUser->phone_confirmed_at !== null,
                'app_loyalty_client_id' => $frontUser->app_loyalty_client_id,
            ],
        ]);
    }
}
