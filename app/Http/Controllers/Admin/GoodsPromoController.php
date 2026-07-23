<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsItemId;
use App\Models\GoodsPromo;
use App\Models\GoodsPromoItems;
use App\Models\GoodsPromoLang;
use App\Models\GoodsSubjectId;
use App\Models\Lang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class GoodsPromoController extends Controller
{
    public function index()
    {
        $view = 'admin.goods-promo.goods-promo-list';

        $goods_promo_list = [];
        $goods_promo_list = GoodsPromo::orderBy('created_at', 'desc')
            ->paginate(config('custom.back.goods_promo_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function createGoodsPromo()
    {
        $view = 'admin.goods-promo.create-goods-promo';

        return view($view, get_defined_vars());
    }

    public function editGoodsPromo($id)
    {
        $view = 'admin.goods-promo.edit-goods-promo';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_promo_items = [];

        $goods_promo = GoodsPromo::where('id', $id)->first();

        if (is_null($goods_promo)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_promo_lang = $goods_promo->goodsPromoLangItems->keyBy('lang_id');

        $goods_promo_items = GoodsPromoItems::where('goods_promo_id', $goods_promo->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $goods_promo_items_codou = GoodsPromoItems::where('goods_promo_id', $goods_promo->id)
            ->where('is_cadou', 1)
            ->count();

        $goods_promo_items_produs = GoodsPromoItems::where('goods_promo_id', $goods_promo->id)
            ->where('is_produs', 1)
            ->count();

        $goods_subject_list = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->join('goods_subject', 'goods_subject.goods_subject_id', '=', 'goods_subject_id.id')
            ->where('lang_id', LANG_ID)
            ->whereRaw('goods_subject_id.id IN(SELECT DISTINCT goods_subject_id FROM goods_item_id)')
            ->orderBy('name', 'asc')
            ->select('*', 'goods_subject_id.id as id')
            ->get();

        $brands_list = BrandId::where('active', 1)
            ->where('deleted', 0)
            ->join('goods_brand', 'goods_brand.goods_brand_id', '=', 'goods_brand_id.id')
            ->where('lang_id', LANG_ID)
            ->select('*', 'goods_brand_id.id as id')
            ->orderBy('name', 'asc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
            ]);
        }

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $promo = GoodsPromo::updateOrCreate(['id' => $id], [
            'promo_type' => $request->input('promo_type'),
            'name' => $request->input('name'),
            'data_start' => Carbon::parse($request->input('data_start'))->format('Y-m-d H:i'),
            'data_end' => Carbon::parse($request->input('data_end'))->format('Y-m-d H:i'),
            'tip_price' => 'b2c',
            'discount_procent' => intval($request->input('discount_procent')),
            'discount_summa' => $request->input('discount_summa'),
            'cant_pentru_disc' => intval($request->input('cant_pentru_disc')),
            'cant_cadou' => intval($request->input('cant_cadou')),
            'promocod' => $request->input('promocod'),
            'show_tag_in_products' => $request->input('show_tag_in_products') == 'on' ? 1 : 0,
            'tag_color' => $request->input('tag_color')
        ]);

        $lang_list = Lang::where('active', 1)->get();

        if(!empty($lang_list) && count($lang_list)){
            foreach ($lang_list as $one_lang){
                GoodsPromoLang::updateOrCreate([
                    'goods_promo_id' => $promo->id,
                    'lang_id' => $one_lang->id,
                ], [
                    'tag_name' => $request->input('tag_name')[$one_lang->id],
                ]);
            }
        }

        if(is_null($id)){
            $promo->one_c_id = $promo->id;
            $promo->save();
        }else{
            if ($promo->promo_type == 1) {

                $data_start = strtotime($promo->data_start);
                $data_end = strtotime($promo->data_end);
                $data_curr = time();

                $find_goods_in_promo = GoodsPromoItems::where('goods_promo_id', $id)
                    ->pluck('goods_item_id')
                    ->toArray();

                if (!empty($find_goods_in_promo))
                    $update_goods_promo_price = GoodsItemId::whereIn('id', $find_goods_in_promo)
                        ->where('active', 1)
                        ->where('deleted', 0)
                        ->get();

                if (!empty($update_goods_promo_price)) {
                    foreach ($update_goods_promo_price as $one_goods_promo) {

                        GoodsItemId::where('one_c_code', $one_goods_promo->one_c_code)->update(['price_promo' => null]);

                        if ($promo->discount_procent > 0 || $promo->discount_summa > 0) {
                            if ($promo->discount_procent > 0 && $data_curr >= $data_start && $data_curr <= $data_end) {
                                $one_goods_promo->price_promo = round($one_goods_promo->price - ($one_goods_promo->price * ($promo->discount_procent / 100)));
                            } elseif ($promo->discount_summa > 0 && $data_curr >= $data_start && $data_curr <= $data_end) {
                                $one_goods_promo->price_promo = $one_goods_promo->price - $promo->discount_summa;
                            }
                            $one_goods_promo->save();
                        }
                    }
                }
            }
        }

        if (is_null($id)) {
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForFunctionLanguage(LANG, '')
            ]);
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editGoodsPromo/' . $id . '/' . LANG_ID)
        ]);
    }

    public function destroyGoodsPromo(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_promo = GoodsPromo::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_promo->isEmpty()) {

                $del_message = '';

                foreach ($goods_promo as $one_promo) {

                    $goods_promo_name = GoodsPromo::where('id', $one_promo->id)
                        ->first();

                    $del_message .= 'Promo - ' . $goods_promo_name->name . ', ';

                    GoodsPromo::destroy($one_promo->id);
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

    public function savePromoItem(Request $request)
    {
        $item = Validator::make($request->all(), [
            //'one_c_code' => 'required',
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $find_goods_items = [];
        $goods_one_c_code = [];
        $goods_promo = null;
        $data_start = '';
        $data_end = '';

        $goods_promo_id = intval($request->input('goods_promo_id'));
        $promo_type = $request->input('goods_promo_type');
        $explode_goods_one_c_code = $request->input('one_c_code');
        $goods_one_c_code = explode(',', $explode_goods_one_c_code);

        $goods_subject_list = $request->input('goods_subject');
        $brands_list = $request->input('brands_list');

        if ($promo_type && $promo_type == 1) {
            $goods_promo = GoodsPromo::where('id', $goods_promo_id)->first();
            $data_start = strtotime($goods_promo->data_start);
            $data_end = strtotime($goods_promo->data_end);
        }
        $data_curr = time();

        if (!empty($goods_subject_list) || !empty($brands_list)) {

            $find_goods_items = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->when($goods_subject_list, function ($query) use ($goods_subject_list) {
                    $query->whereIn('goods_subject_id', $goods_subject_list);
                })
                ->when($brands_list, function ($query) use ($brands_list) {
                    $query->whereIn('brand_id', $brands_list);
                })
                ->get();

        } elseif ($goods_one_c_code) {

            $find_goods_items = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->where(function ($q) use ($goods_one_c_code) {
                    $q->orWhereIn('one_c_code', $goods_one_c_code)
                        ->orWhereIn('articol', $goods_one_c_code);
                })
                ->get();
        }

        if (!empty($find_goods_items)) {

            foreach ($find_goods_items as $one_find_goods) {

                $if_goods_exists = GoodsPromoItems::where('goods_promo_id', $goods_promo_id)
                    ->where('goods_item_id', $one_find_goods->id)
                    ->first();

                if ($if_goods_exists)
                    GoodsPromoItems::destroy($if_goods_exists->id);

                if ($promo_type && $promo_type == 1) {
                    if ($goods_promo)
                        if ($goods_promo->discount_procent > 0 || $goods_promo->discount_summa > 0) {
                            if ($goods_promo->discount_procent > 0 && $data_curr >= $data_start && $data_curr <= $data_end) {
                                $one_find_goods->price_promo = round($one_find_goods->price - ($one_find_goods->price * ($goods_promo->discount_procent / 100)));
                            } elseif ($goods_promo->discount_summa > 0 && $data_curr >= $data_start && $data_curr <= $data_end) {
                                $one_find_goods->price_promo = $one_find_goods->price - $goods_promo->discount_summa;
                            }
                            $one_find_goods->save();
                        }
                }

                $new_promo_item = new GoodsPromoItems();
                $new_promo_item->goods_promo_id = $goods_promo_id;
                $new_promo_item->goods_item_id = $one_find_goods->id;
                $new_promo_item->one_c_id = $one_find_goods->one_c_code;
                $new_promo_item->is_produs = $request->input('product_cadou') == 'is_produs' ? 1 : 0;
                $new_promo_item->is_cadou = $request->input('product_cadou') == 'is_cadou' ? 1 : 0;
                $new_promo_item->save();
            }

            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.add_product', LANG)],
                'redirect' => urlForLanguage(LANG, 'editGoodsPromo/' . $goods_promo_id . '/' . LANG_ID)
            ]);
        } else
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.product_non_exists', LANG)],
            ]);

        /*elseif (!empty($find_goods_item)) {

            $if_goods_exists = GoodsPromoItems::where('goods_promo_id', $goods_promo_id)
                ->where('goods_item_id', $find_goods_item->id)->exists();

            if ($if_goods_exists) {
                return response()->json([
                    'status' => false,
                    'messages' => [controllerTrans('variables.goods_exists', $this->lang)],
                ]);
            }

            if ($promo_type && $promo_type == 1) {
                if ($goods_promo)
                    if ($goods_promo->discount_procent > 0 || $goods_promo->discount_summa > 0) {
                        if ($goods_promo->discount_procent > 0 && $data_curr >= $data_start && $data_curr <= $data_end) {
                            $find_goods_item->price_promo = round($find_goods_item->price - ($find_goods_item->price * ($goods_promo->discount_procent / 100)));
                        } elseif ($goods_promo->discount_summa > 0 && $data_curr >= $data_start && $data_curr <= $data_end) {
                            $find_goods_item->price_promo = $find_goods_item->price - $goods_promo->discount_summa;
                        }
                        $find_goods_item->update();
                    }
            }

            $new_promo_item = new GoodsPromoItems();
            $new_promo_item->goods_promo_id = (int)$goods_promo_id;
            $new_promo_item->goods_item_id = (int)$find_goods_item->id;
            $new_promo_item->one_c_id = (int)$find_goods_item->one_c_code;
            $new_promo_item->is_produs = Input::get('product_cadou') == 'is_produs' ? 1 : 0;
            $new_promo_item->is_cadou = Input::get('product_cadou') == 'is_cadou' ? 1 : 0;
            $new_promo_item->save();

            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.add_product', $this->lang)],
                'redirect' => urlForLanguage($this->lang, 'editGoodsPromo/' . $goods_promo_id . '/' . $updated_lang_id)
            ]);
        }*/
    }

    public function destroyGoodsPromoItems(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_promo_items = GoodsPromoItems::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_promo_items->isEmpty()) {

                $del_message = '';

                foreach ($goods_promo_items as $one_promo_item) {

                    $goods_promo_name = GoodsPromoItems::where('id', $one_promo_item->id)
                        ->first();

                    $get_promo_type = GoodsPromo::where('id', $one_promo_item->goods_promo_id)->first();

                    if($get_promo_type->promo_type == 1){
                        GoodsItemId::where('active', 1)
                            ->where('deleted', 0)
                            ->where('id', $one_promo_item->goods_item_id)
                            ->update(['price_promo' => null]);
                    }

                    $del_message .= $goods_promo_name->getGoodsItemId->itemByLang->name . ', ';

                    GoodsPromoItems::destroy($one_promo_item->id);
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
