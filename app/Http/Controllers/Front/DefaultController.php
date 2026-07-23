<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BannerId;
use App\Models\BannerTopId;
use App\Models\BrandId;
use App\Models\FeedForm;
use App\Models\GallerySubjectId;
use App\Models\GoodsItemId;
use App\Models\InfoLineId;
use App\Models\MenuId;
use App\Models\Subscribers;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DefaultController extends Controller
{
    public function closed()
    {
        return view('front.app-close');
    }

    public function index()
    {
        $view = 'front.pages.index';

        //Declared variables for banners
        $square_banner = '';
        $columns_count = '';

        $meta_main_page = MenuId::where('deleted', 0)
            ->where('alias', 'main-page')
            ->has('itemByLang')
            ->with('itemByLang')
            ->first();

        $slider = BannerTopId::where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        $two_banners_under_slider = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'two-banners-under-slider')
            ->with(['children' => function ($q) {
                $q->limit(2);
            }])
            ->first();

        $popular_categories = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'popular-categories')
            ->has('itemByLang')
            ->with('itemByLang', 'children')
            ->first();

        $three_banners_under_catalog = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'three-banners-under-popular-categories')
            ->with(['children' => function ($q) {
                $q->limit(3);
            }])
            ->first();

        $main_page_banner_promo_goods = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'mpb-promo-goods')
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->first();

        $promo_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('price_promo', '>', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang', 'checkIfWishItemExist', 'goodsItemReviews', 'goodsPromoTags')
            ->orderBy('in_stoc', 'desc')
            ->orderBy(config('custom.sorting.sort_promo_goods_slider')[0], config('custom.sorting.sort_promo_goods_slider')[1])
            ->limit(config('custom.front.products_in_slider'))
            ->get();

        $main_page_banner_bestseller_goods = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'mpb-bestseller-goods')
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->first();

        $bestseller_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('popular_element', 1)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang', 'checkIfWishItemExist', 'goodsItemReviews', 'goodsPromoTags')
            ->orderBy('in_stoc', 'desc')
            ->orderBy(config('custom.sorting.sort_bestseller_goods_slider')[0], config('custom.sorting.sort_bestseller_goods_slider')[1])
            ->limit(config('custom.front.products_in_slider'))
            ->get();

        $two_banners_under_bestseller_goods = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'two-banners-under-bestseller-goods')
            ->with(['children' => function ($q) {
                $q->limit(2);
            }])
            ->first();

        $main_page_banner_new_goods = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'mpb-new-goods')
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->first();

        $new_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('new_element', 1)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang', 'checkIfWishItemExist', 'goodsItemReviews', 'goodsPromoTags')
            ->orderBy('in_stoc', 'desc')
            ->orderBy(config('custom.sorting.sort_new_goods_slider')[0], config('custom.sorting.sort_new_goods_slider')[1])
            ->limit(config('custom.front.products_in_slider'))
            ->get();

        $video_gallery = GallerySubjectId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'video-gallery')
            ->has('itemByLang')
            ->with('itemByLang', 'galleryMediaVideo')
            ->first();

        $three_banners_under_video = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'three-banners-under-video')
            ->with(['children' => function ($q) {
                $q->limit(3);
            }])
            ->first();

        $news = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'news')
            ->has('itemByLang')
            ->with(['itemByLang', 'infoItems' => function ($q) {
                $q->limit(config('custom.front.info_items_in_slider'));
            }])
            ->first();

        $blog = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'blog')
            ->has('itemByLang')
            ->with(['itemByLang', 'infoItems' => function ($q) {
                $q->limit(config('custom.front.info_items_in_slider'));
            }])
            ->first();

        $brands = BrandId::where('active', 1)
            ->where('deleted', 0)
            ->where('p_id', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->orderBy('position')
            ->get();

        $three_banners_under_brands = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'three-banners-under-brand')
            ->with('children')
            ->first();

        $about_shop = getItemByAlias('mp-about-shop', 'MenuId');

        $advantages = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'advantages-under-about-shop')
            ->with(['children' => function ($q) {
                $q->limit(4);
            }])
            ->first();

        //For GA4
        $goods_objects_promo = GoogleEcommerce::goodsCollectionsToObjects($promo_goods,null, ['item_list_name' => 'List of promo products on index page']);
        $goods_objects_bestseller = GoogleEcommerce::goodsCollectionsToObjects($bestseller_goods,null, ['item_list_name' => 'List of bestseller products on index page']);
        $goods_objects_new = GoogleEcommerce::goodsCollectionsToObjects($new_goods,null, ['item_list_name' => 'List of new products on index page']);

        //$advantages = getItemWithChildrenByAlias('advantages', 'MenuId');
        //$pawning = getItemWithChildrenByAlias('pawning', 'MenuId');

        $meta = $meta_main_page ?? collect([]);
        $meta->current_meta_img = asset('front-assets/img/share-logo.png');
        //$meta->meta_static = 'Meta title static';

        return view($view, get_defined_vars());
    }

    public function menuElements($parent, $children = null)
    {

        $lang_id = LANG_ID;

        $parent_menu = MenuId::where('alias', $parent)
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->with('children.children')
            ->with('oImage')
            ->first();

        if (!is_null($children)) {
            switch ($parent) {
                default:
                    return abort(404, 'Unauthorized action.');
                //return redirect($lang);
            }
        } else {
            if (is_null($parent_menu) || is_null($parent))
                return abort(404, 'Unauthorized action.');

            switch ($parent) {
                case 'contacts':
                    return $this->ContactsPage($parent_menu);
                case 'about-company':
                    return $this->aboutPage($parent_menu);
                default:
                    return $this->textPage($parent_menu, $children, $lang_id);

            }
        }
    }

    public function ContactsPage($parent_menu)
    {
        $view = 'front.pages.info.contacts';

        $meta = $parent_menu ?? collect([]);

        return view($view, get_defined_vars());
    }

    public function textPage($parent_menu, $children)
    {
        $view = 'front.pages.info.text-page';

        if (is_null($parent_menu))
            return abort(404, 'Unauthorized action.');

        //For meta tags
        $meta = $parent_menu ?? collect([]);

        if (!is_null($children))
            return abort(404, 'Unauthorized action.');

        return view($view, get_defined_vars());
    }

    public function simpleFeedbackAjax(Request $request)
    {
        $item = Validator::make($request->all(), [
            'name' => 'required|min:2|max:30',
            'email' => 'required|email|max:250',
            'phone' => 'required',
            'comment' => 'required',
            'agree' => 'required'
        ]);

        if ($item->fails())
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);

        if (reCaptchaVersionThree($request->input('g-recaptcha-response')) == false)
            return response()->json([
                'status' => false,
                'messages' => ['Spam'],
            ]);

        $new_message = new FeedForm();
        $new_message->name = $request->input('name');
        $new_message->email = $request->input('email');
        $new_message->phone = $request->input('phone');
        //$new_message->subject = $request->input('subject');
        $new_message->comment = $request->input('comment');
        $new_message->ip = $request->ip();
        $new_message->active = 0;
        $new_message->seen = 0;
        $new_message->save();

        $my_email = showSettingBodyByAlias('email-phone');
        $subject = ShowLabelById(147);

        if (filter_var($my_email, FILTER_VALIDATE_EMAIL)) {
            Mail::send('front.email.emailFeedback', ['data' => $new_message], function ($message) use ($my_email, $subject, $request) {
                $message->from(showSettingBodyByAlias('send-email-from'), $request->input('name'));
                $message->to($my_email);
                $message->subject($subject);
            });
        }

        return response()->json([
            'status' => true,
            'remove_inputs_value' => 1,
            'message' => ShowLabelById(148),
        ]);
    }

    public function ajaxNewSubscribers(Request $request)
    {
        $item = Validator::make($request->all(), [
            'subscribers_email' => 'required|email|max:250|unique:subscribers,email',
        ]);

        if ($item->fails())
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);

        if (reCaptchaVersionThree($request->input('g-recaptcha-response')) == false)
            return response()->json([
                'status' => false,
                'messages' => ['Spam'],
            ]);

        $new_subscriber = new Subscribers();
        $new_subscriber->email = $request->input('subscribers_email');
        $new_subscriber->save();

        //$my_email = showSettingBodyByAlias('email-phone');
        //$subject = ShowLabelById(147);

        /*if (filter_var($my_email, FILTER_VALIDATE_EMAIL)) {
            Mail::send('front.email.emailFeedback', ['data' => $new_message], function ($message) use ($my_email, $subject, $request) {
                $message->from(showSettingBodyByAlias('send-email-from'), $request->input('name'));
                $message->to($my_email);
                $message->subject($subject);
            });
        }*/

        return response()->json([
            'status' => true,
            'remove_inputs_value' => 1,
            'message' => ShowLabelById(163),
        ]);
    }

}
