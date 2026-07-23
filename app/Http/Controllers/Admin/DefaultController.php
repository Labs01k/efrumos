<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\RoleManager;
use App\Models\FeedForm;
use App\Models\FrontUser;
use App\Models\GalleryItem;
use App\Models\GalleryItemId;
use App\Models\GallerySubject;
use App\Models\GallerySubjectId;
use App\Models\GoodsItemId;
use App\Models\InfoItemId;
use App\Models\Orders;
use App\Models\OrdersData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;

class DefaultController extends RoleManager
{
    public function index(){
        $view = 'admin.index';

        //Gallery subject
        $gallery_subject_id = GallerySubjectId::where('active', 1)
            ->where('deleted', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        $gallery_subject = null;
        $gallery_item = [];
        $count_gallery_items = 0;
        if(!is_null($gallery_subject_id)) {
            $gallery_subject = GallerySubject::where('gallery_subject_id', $gallery_subject_id->id)
                ->where('lang_id', LANG_ID)
                ->first();

            $gallery_item_id = GalleryItemId::where('gallery_subject_id', $gallery_subject_id->id)
                ->where('active', 1)
                ->where('deleted', 0)
                ->orderBy('created_at', 'desc')
                ->take(14)
                ->get();

            $count_gallery_items = GalleryItemId::where('gallery_subject_id', $gallery_subject_id->id)
                ->where('deleted', 0)
                ->count();

            if(!$gallery_item_id->isEmpty()) {
                foreach ($gallery_item_id as $item) {
                    $gallery_item[] = GalleryItem::where('gallery_item_id', $item->id)
                    ->where('lang_id', LANG_ID)
                    ->first();
                }

                $gallery_item = array_filter($gallery_item);
            }
        }

        //Orders
        $orders = Orders::orderBy('created_at', 'desc')
            ->where('active', 1)
            ->where('deleted', 0)
            ->with('ordersUsers', 'ordersFrontUser')
            ->get();

        $current_date = Carbon::now();
        $ago_week = $current_date->subDays($current_date->dayOfWeek)->subWeek();

        $orders_ago_week = Orders::where('created_at', '>=', $ago_week)
            ->count();

        $orders_total_price = OrdersData::sum('total_price');

        $orders_total_price_ago_week = OrdersData::where('created_at', '>=', $ago_week)
            ->sum('total_price');

        //Feedback
        $feedback = FeedForm::orderBy('created_at', 'desc')
            ->get();

        $feedback_ago_week = FeedForm::where('created_at', '>=', $ago_week)
            ->count();

        //Front users
        $front_users = FrontUser::orderBy('created_at', 'desc')
            ->get();

        $front_users_ago_week = FrontUser::where('created_at', '>=', $ago_week)
            ->count();

        //Goods items
        $goods_items = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'getSubjectId')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        //Info items
        /*$info_items = InfoItemId::where('active', 1)->where('deleted', 0)->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();*/

        return view($view, get_defined_vars());
    }

    public function ajaxCountFeedback()
    {
        $new_feedform = FeedForm::where('seen', 0)->count();
//        $new_comment = GoodsItemComments::where('seen', 0)->count();

        $count_feedform = 0;
        $count_comment = 0;
        $alias_feedform = '';
        $alias_comment = '';

        if($new_feedform > 0) {
            $count_feedform = $new_feedform;
            $alias_feedform = 'feedform';
        }
//
//        if($new_comment > 0) {
//            $count_comment = $new_comment;
//            $alias_comment = 'comments';
//        }

            return response()->json([
                'status' => true,
                'feedform' => [
                    'count' => $count_feedform,
                    'alias' => $alias_feedform,
                    'messages' => controllerTrans('variables.feedform_count', LANG, ['digits' => '<span>' . $count_feedform . '</span>'])
                ],
//                'comments' => [
//                    'count' => $count_comment,
//                    'alias' => $alias_comment,
//                    'messages' => controllerTrans('variables.comments_count', $this->lang, ['digits' => '<span>' . $count_comment . '</span>'])
//                ],
            ]);
    }

    public function hideMenuAjax()
    {
        if(!is_null(request()->input('is_visible'))) {
            if (!is_null(Cookie::get('sidebar'))) {
                Cookie::queue(Cookie::forget('sidebar'));
            }

            Cookie::queue('sidebar', request()->input('is_visible'), 45000);

            return response()->json([
               'status' => true
            ]);
        }

        return response()->json([
            'status' => false
        ]);
    }

}
