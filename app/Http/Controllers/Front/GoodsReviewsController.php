<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\GoodsItemId;
use App\Models\GoodsItemReviews;
use App\Models\InfoItemId;
use App\Models\InfoLineId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class GoodsReviewsController extends Controller
{

    public function ajaxSaveGoodsItemReview(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(184),
            ]);
        }

        $validator = Validator::make($request->all(),
            [
                'review_text' => 'required',
                'rating' => 'required',
                //'files' => 'nullable|max:5', //upload maximum 5 photos
                //'files.*' => 'image|mimes:jpeg,png,jpg|max:10240', //max5 поставить
                'agree' => 'required'
            ]/*,
            [   Fotografia trebuie să fie un fișier de tipul: {values}.
                'files.*.mimes' => str_replace('{values}', ':values', ShowLabelById(252, $this->lang_id)),
                'files.*.max' => str_replace('{max}', ':max', ShowLabelById(253, $this->lang_id)),
            ]*/
        );

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'messages' => $validator->messages(),
            ]);

        if (reCaptchaVersionThree($request->input('g-recaptcha-response')) == false)
            return response()->json([
                'status' => false,
                'messages' => ['Spam'],
            ]);

        /*if (empty($request->input('rating'))) {
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(39, $this->lang_id),
            ]);
        }*/

        $goods_item_id = '';
        $goods_item_review = '';

        $goods_item_review = new GoodsItemReviews();
        $goods_item_review->goods_item_id = (int)$request->input('goods_item_id');
        $goods_item_review->front_user_id = $user->id;
        $goods_item_review->review_text = $request->input('review_text');
        $goods_item_review->rating = (int)$request->input('rating');
        $goods_item_review->active = 0;
        $goods_item_review->save();

        if ($goods_item_review) {


            $goods_item_id = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('id', $goods_item_review->goods_item_id)
                ->has('itemByLang')
                ->with('itemByLang')
                ->first();

            $email_message = getItemByAlias('email-new-review', 'MenuId');
            $subject = $goods_item_id->itemByLang && $email_message && $email_message->itemByLang ? $email_message->itemByLang->h1_title . ' - ' . $goods_item_id->itemByLang->name : ShowLabelById(46, $this);
            $emails_array = explode(',', showSettingBodyByAlias('email-phone'));

            if (!empty($emails_array) && count($emails_array)) {
                foreach ($emails_array as $one_email) {
                    $one_email = trim($one_email);
                    if (filter_var($one_email, FILTER_VALIDATE_EMAIL)) {
                        Mail::send('front.email.emailNewGoodsReview', ['goods_item_id' => $goods_item_id, 'goods_item_review' => $goods_item_review, 'email_message' => $email_message], function ($message) use ($one_email, $subject) {
                            $message->from(showSettingBodyByAlias('send-email-from'), ShowLabelById(46));
                            $message->to($one_email);
                            $message->subject($subject);
                        });
                    }
                }
            }
        }

        return response()->json([
            'status' => true,
            //'render_reviews_items' => $render_reviews_items,
            //'reviews_count' => $reviews_count . ' ' . trans_choice('variables.reviews_count', $reviews_count),
            'message' => ShowLabelById(185),
            'redirect' => $request->input('current_url')
        ]);
    }


}

