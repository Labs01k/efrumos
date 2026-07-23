<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\GoodsItem;
use App\Models\GoodsItemId;
use App\Models\GoodsSubject;
use App\Models\GoodsSubjectId;
use Illuminate\Support\Str;

class ParseController extends Controller
{
    public function updateGoodsGuid(){

        $json = file_get_contents(public_path('json/goods-guid.json'));
        $json_data = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json), true );

        if(!empty($json_data) && count($json_data)){
            foreach ($json_data as $one_data_item){
                GoodsItemId::where('one_c_code', $one_data_item['one_c_code'])->update(['one_c_code_guid' => $one_data_item['guid']]);
            }
        }

        return 'OK';
    }

    /*public function parseGoodsSubjects()
    {
        $file_to_read = listDirFileByDate(env('ROOT_CATEGORIES_JSON')); //Get last modified file in a
        // directory
        $content_file = file_get_contents(env('ROOT_CATEGORIES_JSON') . $file_to_read);
        //dd($content_file);
        $json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content_file);
        $subjects_array = json_decode($json, true);

        if (!empty($subjects_array)) {

            foreach ($subjects_array['Table'] as $one_subject_item) {
                if (strlen($one_subject_item['Code']) == 3) {

                    $find_subject_l1 = GoodsSubjectId::where('one_c_id', $one_subject_item['ID'])
                        ->where('p_id', 1)
                        ->first();
                    if ($find_subject_l1) {
                        $update_subject_l1 = GoodsSubject::where('goods_subject_id', $find_subject_l1->id)
                            ->where('lang_id', LANG_ID)
                            ->first();

                        $update_subject_l1->name = $one_subject_item['Name'];
                        $update_subject_l1->save();

                    } else {
                        $new_subject_l1 = new GoodsSubjectId();

                        $new_subject_l1->level = 2;
                        $new_subject_l1->p_id = 1;
                        $new_subject_l1->one_c_id = $one_subject_item['ID'];
                        $new_subject_l1->one_c_code = $one_subject_item['Code'];
                        $new_subject_l1->alias = Str::slug($one_subject_item['Name']);
                        if (checkIfExistOnAlias($new_subject_l1->alias, 'goods_subject_id'))
                            $new_subject_l1->alias = $new_subject_l1->alias . '-' . $one_subject_item['ID'];
                        $new_subject_l1->active = 1;
                        $new_subject_l1->deleted = 0;
                        $new_subject_l1->save();

                        if ($new_subject_l1) {
                            $new_subject = new GoodsSubject();
                            $new_subject->goods_subject_id = $new_subject_l1->id;
                            $new_subject->lang_id = LANG_ID;
                            $new_subject->name = $one_subject_item['Name'];
                            $new_subject->save();
                        }
                    }

                    //$test[] = $one_subject_item['Code'];
                } elseif (strlen($one_subject_item['Code']) == 6) {

                    $find_subject_l1_code = substr($one_subject_item['Code'], 0, 3);
                    $find_subject_l1 = GoodsSubjectId::where('one_c_code', $find_subject_l1_code)
                        ->where('p_id', 1)
                        ->first();

                    if ($find_subject_l1) {

                        $find_subject_l2 = GoodsSubjectId::where('one_c_id', $one_subject_item['ID'])
                            ->where('level', 3)
                            ->first();

                        if ($find_subject_l2) {

                            $update_subject_l2 = GoodsSubject::where('goods_subject_id', $find_subject_l2->id)
                                ->where('lang_id', LANG_ID)
                                ->first();

                            $update_subject_l2->name = $one_subject_item['Name'];
                            $update_subject_l2->save();

                        } else {
                            $new_subject_l2 = new GoodsSubjectId();

                            $new_subject_l2->level = 3;
                            $new_subject_l2->p_id = $find_subject_l1->id;
                            $new_subject_l2->one_c_id = $one_subject_item['ID'];
                            $new_subject_l2->one_c_code = $one_subject_item['Code'];
                            $new_subject_l2->alias = Str::slug($one_subject_item['Name']);
                            if (checkIfExistOnAlias($new_subject_l2->alias, 'goods_subject_id'))
                                $new_subject_l2->alias = $new_subject_l2->alias . '-' . $one_subject_item['ID'];
                            $new_subject_l2->active = 1;
                            $new_subject_l2->deleted = 0;
                            $new_subject_l2->save();

                            if ($new_subject_l2) {
                                $new_subject = new GoodsSubject();
                                $new_subject->goods_subject_id = $new_subject_l2->id;
                                $new_subject->lang_id = LANG_ID;
                                $new_subject->name = $one_subject_item['Name'];
                                $new_subject->save();
                            }
                        }
                    }

                } elseif (strlen($one_subject_item['Code']) == 9) {

                    $find_subject_2_code = substr($one_subject_item['Code'], 0, 6);
                    $find_subject_2 = GoodsSubjectId::where('one_c_code', $find_subject_2_code)
                        ->where('level', 3)
                        ->first();

                    if ($find_subject_2) {

                        $find_subject_l3 = GoodsSubjectId::where('one_c_id', $one_subject_item['ID'])
                            ->where('level', 4)
                            ->first();

                        if ($find_subject_l3) {

                            $update_subject_l3 = GoodsSubject::where('goods_subject_id', $find_subject_l3->id)
                                ->where('lang_id', LANG_ID)
                                ->first();

                            $update_subject_l3->name = $one_subject_item['Name'];
                            $update_subject_l3->save();

                        } else {
                            $new_subject_l3 = new GoodsSubjectId();

                            $new_subject_l3->level = 4;
                            $new_subject_l3->p_id = $find_subject_2->id;
                            $new_subject_l3->one_c_id = $one_subject_item['ID'];
                            $new_subject_l3->one_c_code = $one_subject_item['Code'];
                            $new_subject_l3->alias = Str::slug($one_subject_item['Name']);
                            if (checkIfExistOnAlias($new_subject_l3->alias, 'goods_subject_id'))
                                $new_subject_l3->alias = $new_subject_l3->alias . '-' . $one_subject_item['ID'];
                            $new_subject_l3->active = 1;
                            $new_subject_l3->deleted = 0;
                            $new_subject_l3->save();

                            if ($new_subject_l3) {
                                $new_subject = new GoodsSubject();
                                $new_subject->goods_subject_id = $new_subject_l3->id;
                                $new_subject->lang_id = LANG_ID;
                                $new_subject->name = $one_subject_item['Name'];
                                $new_subject->save();
                            }
                        }
                    }
                }
            }
        }
        @unlink(env('ROOT_PRODUCTS_JSON') . $file_to_read);
        return response('OK', 200);
    }*/

    /*public function parseGoodsItems()
    {
        $file_to_read = listDirFileByDate(env('ROOT_PRODUCTS_JSON')); //Get last modified file in a directory
        $content_file = file_get_contents(env('ROOT_PRODUCTS_JSON') . $file_to_read);
        $json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content_file);
        $goods_array = json_decode($json, true);

        if (!empty($goods_array)) {
            foreach ($goods_array['Table'] as $one_goods_item) {

                $find_goods = GoodsItemId::where('one_c_id', $one_goods_item['ID'])->first();
                $find_goods_subject = GoodsSubjectId::where('one_c_id', $one_goods_item['GroupID'])->first();

                $maxPosition = GetMaxPosition('goods_item_id');

                if ($find_goods) {

                    $find_goods->one_c_code = $one_goods_item['Code'];
                    $find_goods->price = $one_goods_item['PriceOut2'];
                    $find_goods->in_stoc = $one_goods_item['Qtty'] > 0 ? 1 : 0;
                    $find_goods->goods_count = $one_goods_item['Qtty'];

                    if ($find_goods_subject)
                        $find_goods->goods_subject_id = $find_goods_subject->id;

                    $find_goods->save();

                    $update_goods = GoodsItem::where('goods_item_id', $find_goods->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    $update_goods->name = $one_goods_item['Name'];
                    $update_goods->save();

                } elseif ($find_goods_subject) {

                    $new_goods_id = new GoodsItemId();
                    $new_goods_id->one_c_id = $one_goods_item['ID'];
                    $new_goods_id->one_c_code = $one_goods_item['Code'];
                    $new_goods_id->goods_subject_id = $find_goods_subject->id;
                    $new_goods_id->price = $one_goods_item['PriceOut2'];
                    $new_goods_id->in_stoc = $one_goods_item['Qtty'] > 0 ? 1 : 0;
                    $new_goods_id->goods_count = $one_goods_item['Qtty'];
                    $new_goods_id->position = $maxPosition + 1;
                    $new_goods_id->alias = Str::slug($one_goods_item['Name']);
                    if (checkIfExistOnAlias($new_goods_id->alias, 'goods_item_id'))
                        $new_goods_id->alias = $new_goods_id->alias . '-' . $one_goods_item['ID'];
                    $new_goods_id->active = 1;
                    $new_goods_id->deleted = 0;
                    $new_goods_id->save();

                    if ($new_goods_id) {
                        $new_goods = new GoodsItem();
                        $new_goods->goods_item_id = $new_goods_id->id;
                        $new_goods->lang_id = LANG_ID;
                        $new_goods->name = $one_goods_item['Name'];
                        $new_goods->save();
                    }

                }
            }
        }
        @unlink(env('ROOT_PRODUCTS_JSON') . $file_to_read);
        return response('OK', 200);
    }*/

    public function parseGoodsSubjectName()
    {
        $all_goods_items = GoodsItemId::with('itemByLang', 'getSubjectId', 'getSubjectId.itemByLang', 'getSubjectId.parent', 'getBrand', 'getBrand.parent', 'getBrand.itemByLang')
            ->get();

        if (!empty($all_goods_items) && count($all_goods_items)) {
            foreach ($all_goods_items as $one_goods_item) {

                if ($one_goods_item->getBrand && $one_goods_item->getBrand->itemByLang && $one_goods_item->getBrand->parent && $one_goods_item->getBrand->parent->itemByLang)
                    $goods_brand_name = $one_goods_item->getBrand->parent->itemByLang->name . '/' . $one_goods_item->getBrand->itemByLang->name;
                else
                    $goods_brand_name = $one_goods_item->getBrand && $one_goods_item->getBrand->itemByLang ? $one_goods_item->getBrand->itemByLang->name : null;

                if ($one_goods_item->getSubjectId && $one_goods_item->getSubjectId->parent && $one_goods_item->itemByLang && ($one_goods_item->itemBylang->subject_name == NULL || $one_goods_item->itemBylang->subject_nav_name == NULL || $one_goods_item->itemBylang->brand_nav_name == NULL)) {
                    GoodsItem::where('goods_item_id', $one_goods_item->id)
                        ->where('lang_id', LANG_ID)
                        ->update([
                            'subject_name' => $one_goods_item->getSubjectId->itemByLang->name,
                            'subject_nav_name' => $one_goods_item->getSubjectId->parent->itemByLang->name . '/' . $one_goods_item->getSubjectId->itemByLang->name,
                            'brand_nav_name' => $goods_brand_name ?? null
                        ]);
                }
            }
            return 'OK';
        } else
            return 'Array empty';

    }
}
