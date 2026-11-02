<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use App\Models\GoodsItemId;
use App\Models\Wish;
use App\Models\WishId;
use App\Services\AppApi\ProductPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * E4-BES-01 / EFA-77 — избранное для приложения. Один источник данных с
 * сайтом (`wish_id`/`wish`, те же таблицы, что `Front\WishController`) —
 * личный кабинет и приложение видят один и тот же список, не отдельную
 * копию.
 *
 * Идентификация пользователя — та же, что уже использует E1-BES-02/EFA-39
 * (подтверждение телефона): `front_user.app_loyalty_client_id` резолвится
 * из `X-App-Client-Id`, как и в `OrderController::clientId()`. Пользователь
 * без подтверждённого телефона избранным из приложения пользоваться не
 * может — такого `front_user` просто не найдётся (404, не 500).
 *
 * Связка через WebView-логин (EFA-35/43, `users.site_front_user_id` на
 * бэкенде) здесь не резолвится — это ДРУГОЙ мост идентичности, не
 * синхронизированный с `app_loyalty_client_id`. Известный пробел, см.
 * `efrumos-docs/mob/jira/tasks-progress.md` (EFA-36).
 */
class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $wishId = WishId::where('front_user_id', $frontUser->id)->first();

        if (!$wishId) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $goodsIds = Wish::where('wish_id', $wishId->id)->orderByDesc('position')->pluck('goods_item_id');

        $goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->whereIn('id', $goodsIds)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $goods->map(fn ($g) => ProductPresenter::summary($g))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $goodsItemId = (int) $request->input('product_id');

        $goods = GoodsItemId::where('id', $goodsItemId)->where('active', 1)->where('deleted', 0)->first();

        if (!$goods) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $wishId = WishId::firstOrCreate(['front_user_id' => $frontUser->id], ['user_ip' => $request->ip()]);

        // Идемпотентно: уже в избранном — не ошибка и не дубль. Намеренно
        // НЕ toggle (в отличие от Front\WishController::ajaxAddToWish) —
        // повторный вызов add должен оставаться add, не превращаться в remove.
        $exists = Wish::where('wish_id', $wishId->id)->where('goods_item_id', $goodsItemId)->exists();

        if (!$exists) {
            $nextPosition = (int) Wish::where('wish_id', $wishId->id)->max('position') + 1;

            Wish::create([
                'wish_id' => $wishId->id,
                'goods_item_id' => $goodsItemId,
                'position' => $nextPosition,
            ]);
        }

        return response()->json(['success' => true], 201);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $frontUser = $this->resolveFrontUser($request);

        if (!$frontUser) {
            return $this->notLinked();
        }

        $wishId = WishId::where('front_user_id', $frontUser->id)->first();

        if ($wishId) {
            Wish::where('wish_id', $wishId->id)->where('goods_item_id', $productId)->delete();
        }

        // Идемпотентно: удаление отсутствующего элемента — тоже успех.
        return response()->json(['success' => true]);
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
