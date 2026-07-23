<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsItemId;
use App\Models\GoodsPageId;
use App\Models\GoodsSubjectId;
use App\Models\InfoLineId;
use App\Models\MenuId;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;


class SitemapsController extends Controller
{
    public function index()
    {

        $langs_list = LANGS_FRONT;

        $sitemap = Sitemap::create();

        $top_header_menu_links = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'header-menu')
            ->with('children')
            ->first();

        $footer_menu_links = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'footer-menu')
            ->with('children')
            ->first();

        $footer_menu_info_links = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'footer-menu-info')
            ->with('children')
            ->first();

        $goods_items_list = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->orderBy('position')
            ->get();

        $goods_subjects_l1 = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->where('id', getMainCatalogId())
            ->with('children.children')
            ->orderBy('position')
            ->first();

        $goods_brands = BrandId::where('active', 1)
            ->where('deleted', 0)
            ->where('p_id', 0)
            ->with('children.children')
            ->orderBy('position')
            ->get();

        $news = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'news')
            ->with('infoItems')
            ->first();

        $blog = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'blog')
            ->with('infoItems')
            ->first();

        $promo = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'promo')
            ->with('infoItems')
            ->first();

        $goods_seo_pages = GoodsPageId::where('active', 1)
            ->where('deleted', 0)
            ->orderBy('position')
            ->get();

        if (!empty($langs_list) && count($langs_list)) {
            foreach ($langs_list as $one_lang) {

                //Header links
                if ($top_header_menu_links && $top_header_menu_links->children->isNotEmpty()) {
                    foreach ($top_header_menu_links->children as $one_menu_element) {
                        if ($one_menu_element->page_type == 'page')
                            $sitemap->add(Url::create("/{$one_lang}/{$one_menu_element->alias}"));
                    }
                }
                //End Header links

                //Footer links
                if ($footer_menu_links && $footer_menu_links->children->isNotEmpty()) {
                    foreach ($footer_menu_links->children as $one_menu_element) {
                        if ($one_menu_element->page_type == 'page')
                            $sitemap->add(Url::create("/{$one_lang}/{$one_menu_element->alias}"));
                    }
                }
                //End Footer links

                //Footer info links
                if ($footer_menu_info_links && $footer_menu_info_links->children->isNotEmpty()) {
                    foreach ($footer_menu_info_links->children as $one_menu_element) {
                        if ($one_menu_element->page_type == 'page')
                            $sitemap->add(Url::create("/{$one_lang}/{$one_menu_element->alias}"));
                    }
                }
                //End Footer info links

                //Goods Subjects with 3 levels
                if ($goods_subjects_l1 && $goods_subjects_l1->children->isNotEmpty()) {
                    $sitemap->add(Url::create("/{$one_lang}/catalog"));

                    foreach ($goods_subjects_l1->children as $one_goods_subject_l2) {
                        $sitemap->add(Url::create("/{$one_lang}/category/{$one_goods_subject_l2->alias}"));

                        if ($one_goods_subject_l2->children->isNotEmpty()) {
                            foreach ($one_goods_subject_l2->children as $one_goods_subject_l3) {
                                $sitemap->add(Url::create("/{$one_lang}/category/{$one_goods_subject_l3->alias}"));
                            }
                        }
                    }
                }
                //End goods Subjects with 3 levels

                //Goods items list
                if (!empty($goods_items_list) && count($goods_items_list)) {
                    foreach ($goods_items_list as $one_goods) {
                        $sitemap->add(Url::create("/{$one_lang}/catalog/product/{$one_goods->alias}"));
                    }
                }
                //End Goods items list

                //Goods brands
                if (!empty($goods_brands) && count($goods_brands)) {
                    foreach ($goods_brands as $one_goods_brand_l1) {
                        $sitemap->add(Url::create("/{$one_lang}/brands/{$one_goods_brand_l1->alias}"));

                        if ($one_goods_brand_l1->children->isNotEmpty()) {
                            foreach ($one_goods_brand_l1->children as $one_goods_brand_l2) {
                                $sitemap->add(Url::create("/{$one_lang}/brands/{$one_goods_brand_l2->alias}"));
                            }
                        }
                    }
                }
                //End Goods brands

                //Goods SEO pages
                if (!empty($goods_seo_pages) && count($goods_seo_pages)) {
                    foreach ($goods_seo_pages as $one_goods_page) {
                        $sitemap->add(Url::create("/{$one_lang}/category-page/{$one_goods_page->alias}"));
                    }
                }
                //End Goods SEO pages

                //News
                if ($news && $news->infoItems->isNotEmpty()) {
                    foreach ($news->infoItems as $one_news) {
                        $sitemap->add(Url::create("/{$one_lang}/news/{$one_news->alias}"));
                    }
                }
                //End News

                //Blog
                if ($blog && $blog->infoItems->isNotEmpty()) {
                    foreach ($blog->infoItems as $one_blog) {
                        $sitemap->add(Url::create("/{$one_lang}/blog/{$one_blog->alias}"));
                    }
                }
                //End Blog

                //Promo
                if ($promo && $promo->infoItems->isNotEmpty()) {
                    foreach ($promo->infoItems as $one_promo) {
                        $sitemap->add(Url::create("/{$one_lang}/promo/{$one_promo->alias}"));
                    }
                }
                //End Promo
            }
        }

        if (File::exists('sitemap.xml')) {
            File::delete('sitemap.xml');
            $sitemap->writeToFile(public_path('sitemap.xml'));
        } else {
            $sitemap->writeToFile(public_path('sitemap.xml'));
        }

        if (request()->isMethod('post')) {
            return response()->json([
                'status' => true,
                'type' => 'info',
                'messages' => [controllerTrans('variables.sitemap_msg', LANG)]
            ]);
        }else{
            return 'Sitemap generated successfully';
        }

        //return $sitemap_xml;

    }


}
