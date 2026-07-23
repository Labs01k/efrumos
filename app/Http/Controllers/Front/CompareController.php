<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Compare;
use App\Models\CompareId;
use App\Models\GoodsItemId;
use App\Models\GoodsParametrId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CompareController extends Controller
{
    protected $provider;

    public function compare(Request $request)
    {
        $view = 'front.pages.compare-list';

        $cookie_compare = $request->cookie('compare');
        $compare_list = [];
        if (!is_null($cookie_compare)) {
            $compare_id = CompareId::where('id', $cookie_compare)->first();

            if (!is_null($compare_id)) {

                $compare_list = Compare::where('compare_id', $compare_id->id)
                    ->orderBy('position', 'asc')
                    ->get();

                $parameters = [];
                $parameters = GoodsParametrId::where('active', 1)
                    ->where('deleted', 0)
                    ->where('goods_subject_id', getMainCatalogId())
                    //->wherein('parametr_type', ['select', 'radio', 'checkbox'])
                    ->has('itemByLang')
                    ->with('itemByLang')
                    ->orderBy('position', 'asc')
                    ->get();
            }
        }

        $meta = collect([]);
        $meta->meta_static = 'Compare' . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }

    public function ajaxCompare(Request $request)
    {
        $goods_id = $request->input('goods_id');
        $cookie_compare = $request->cookie('compare');
        $user = app('global_user');
        $goods_item_id = GoodsItemId::where('id', $goods_id)
            ->where('active', 1)
            ->where('deleted', 0)
            ->first();
        if (is_null($goods_item_id))
            return response()->json([
                'status' => false
            ]);
        $maxPosition = GetMaxPosition('compare');
        $compare_id = null;

        if (!is_null($cookie_compare)) {
            $compare_id = Compare::where('goods_item_id', $goods_item_id->id)
                ->where('compare_id', $cookie_compare)
                ->first();
        }
        if (!is_null($compare_id)) {
            Compare::where('goods_item_id', $goods_item_id->id)
                //->where('goods_subject_id', $goods_item_id->goods_subject_id)
                ->where('compare_id', $cookie_compare)
                ->delete();

            $compare_after_delete = Compare::where('compare_id', $cookie_compare)
                ->count();

            if ($compare_after_delete < 1) {
                CompareId::where('id', $cookie_compare)->delete();

                if (!is_null($request->cookie('compare'))) {
                    Cookie::queue(Cookie::forget('compare'));
                }
            }
            //Count for header
            $compare_count = Compare::where('compare_id', $cookie_compare)->count();
            return response()->json([
                'status' => true,
                'compare_count' => $compare_count,
                'compare_item' => 0,
                'message' => 'Success message'
            ]);
        } else {
            $compare_id = CompareId::where('id', $cookie_compare)->first();

            if (!is_null($compare_id)) {
                Compare::create([
                    'compare_id' => $compare_id->id,
                    'goods_item_id' => $goods_item_id->id,
                    'goods_subject_id' => $goods_item_id->goods_subject_id,
                    'position' => $maxPosition + 1
                ]);
            } else {
                $compare_id = CompareId::create([
                    'user_ip' => request()->ip(),
                    'front_user_id' => $user ? $user->id : null
                ]);
                Compare::create([
                    'compare_id' => $compare_id->id,
                    'goods_item_id' => $goods_item_id->id,
                    'goods_subject_id' => $goods_item_id->goods_subject_id,
                    'position' => $maxPosition + 1
                ]);
            }
            //Count for header
            $compare_count = Compare::where('compare_id', $compare_id->id)->count();

            if (!is_null($request->cookie('compare'))) {
                Cookie::queue(Cookie::forget('compare'));
            }

            Cookie::queue('compare', $compare_id->id, env('COOKIE_USER_REMEMBER_TIME'));
        }
        return response()->json([
            'status' => true,
            'compare_count' => $compare_count,
            'compare_item' => 1,
            'message' => 'Success message'
        ]);
    }

    public function destroyCompareItem(Request $request)
    {
        $goods_item = $request->input('goods_id');
        $cookie_compare = Cookie::get('compare');

        $compare_item = Compare::where('goods_item_id', $goods_item)
            ->where('compare_id', $cookie_compare)
            ->first();

        if (is_null($compare_item) || is_null($cookie_compare))
            return response()->json([
                'status' => false
            ]);

        Compare::where('goods_item_id', $goods_item)
            ->where('compare_id', $cookie_compare)
            ->delete();

        $compare_count = Compare::where('compare_id', $cookie_compare)->count('id');

        $compare_item_after_delete = Compare::where('compare_id', $cookie_compare)
            ->count();

        if ($compare_item_after_delete < 1) {
            CompareId::where('id', $cookie_compare)->delete();

            if (!is_null(Cookie::get('compare'))) {
                Cookie::queue(Cookie::forget('compare'));
            }
        }

        return response()->json([
            'status' => true,
            'compare_count' => $compare_count,
            'message' => 'Success message'
        ]);
    }

}

