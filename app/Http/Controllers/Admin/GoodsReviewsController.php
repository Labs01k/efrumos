<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsItemId;
use App\Models\GoodsItemReviews;
use App\Models\GoodsPromo;
use App\Models\GoodsPromoItems;
use App\Models\GoodsSubjectId;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class GoodsReviewsController extends Controller
{
    public function index(Request $request)
    {
        $view = 'admin.goods-reviews.goods-reviews-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/goods-reviews';

        $goods_item_id = $request->input('item');

        $goods_with_reviews = GoodsItemReviews::join('front_user', 'front_user.id', '=', 'goods_item_reviews.front_user_id')
            ->when($goods_item_id, function ($query) use ($goods_item_id) {
                $query->where('goods_item_id', $goods_item_id);
            })
            ->select('*', 'goods_item_reviews.id as id', 'goods_item_reviews.active as active', 'goods_item_reviews.created_at as created_at')
            ->orderBy('goods_item_reviews.created_at', 'desc')
            ->paginate(config('custom.back.goods_reviews_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = intval($request->input('id'));
        $change_active = $active == 1 ? 0:1;

        $goods_item_review = GoodsItemReviews::where('id', $id)->first();
        $goods_item_review->active = $change_active;
        $goods_item_review->save();

        $element_name = '';
        $rating = CountRatingByGoodsItemID($goods_item_review->goods_item_id);

        if ($active == 1) {
            $msg = controllerTrans('variables.element_is_inactive', LANG, ['name' => $element_name]);
            GoodsItemId::where('id', $goods_item_review->goods_item_id)->update(['rating' => $rating]);
        } else {
            $msg = controllerTrans('variables.element_is_active', LANG, ['name' => $element_name]);
            GoodsItemId::where('id', $goods_item_review->goods_item_id)->update(['rating' => $rating]);
        }

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);

    }

    public function destroyReviewItem(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $reviews_ids = GoodsItemReviews::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$reviews_ids->isEmpty()) {

                $del_message = '';

                foreach ($reviews_ids as $one_review_item) {
                    GoodsItemReviews::destroy($one_review_item->id);

                    $rating = CountRatingByGoodsItemID($one_review_item->goods_item_id);
                    GoodsItemId::where('id', $one_review_item->goods_item_id)->update(['rating' => $rating]);

                    $del_message .= 'Review ' . ', ';
                }

                if (!empty($del_message)) {
                    $del_message = substr($del_message, 0, -2) . '<br />' . controllerTrans('variables.success_deleted', LANG);
                }

                return response()->json([
                    'status' => true,
                    'messages' => $del_message,
                    'deleted_elements' => $deleted_elements_id_arr,
                    'redirect' => $data_current_url,
                ]);
            }

            return response()->json([
                'status' => false
            ]);
        } else {
            return response()->json([
                'status' => false
            ]);
        }
    }
}
