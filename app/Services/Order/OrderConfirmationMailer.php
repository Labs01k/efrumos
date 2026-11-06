<?php

namespace App\Services\Order;

use App\Models\Basket;
use App\Models\GoodsItemId;
use App\Models\Labels;
use App\Models\Orders;
use App\Models\SettingsId;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Единая точка отправки письма-подтверждения заказа — раньше почти один в
 * один дублировалась в Front\OrderController (сайт) и
 * AppApi\AppOrderService (приложение). Собирает всё из $order по его связям,
 * поэтому одинаково годится и для немедленной отправки (cash — сразу при
 * создании заказа), и для отложенной (card — после реального капчура у
 * банка, когда появляются RRN/код авторизации/карта для чека, см.
 * App\Listeners\SendOrderConfirmationEmailAfterPayment).
 */
class OrderConfirmationMailer
{
    public function send(Orders $order): void
    {
        $ordersData = $order->ordersData;
        $ordersUsers = $order->ordersUsers;

        if (!$ordersData || !$ordersUsers || $ordersData->email_sent) {
            return;
        }

        $email = $ordersUsers->email;
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Банковские поля чека (RRN/код авторизации/карта) — только у
        // оплаты картой, и только после того, как они реально появились
        // (см. VictoriaBankPaymentResultHandler/backref()). У cash их нет
        // и не будет, $payment остаётся null.
        $payment = $order->pay_method === 'card'
            ? $order->payments()->where('provider', 'victoriabank')->first()
            : null;

        // orders язык оформления не хранит. Для cash это не важно — письмо
        // шлётся сразу же, внутри того же запроса оформления, где локаль уже
        // верно выставлена SetLocale-мидлварью. Для card письмо шлётся
        // отложенно, из вебхука банка (вне локализованной группы роутов,
        // без какой-либо локали вообще) — единственный источник языка здесь
        // это то, что сохранили в VictoriaBankController::initiate().
        //
        // Это должно случиться ДО любого запроса, трогающего
        // locale-зависимые CMS-связи (itemByLang и т.п. ниже, включая
        // basket) — они жёстко завязаны на LANG_ID, который без этого
        // вообще не определён в контексте вебхука (живой случай: "Undefined
        // constant LANG_ID" при первом тесте этой отправки).
        $previousLocale = app()->getLocale();
        if ($payment && $payment->lang && in_array($payment->lang, config('app.locales', []), true)) {
            app()->setLocale($payment->lang);
        }
        $this->ensureLocaleBootstrapped();

        try {
            $basket = Basket::where('basket_id', $order->basket_id)->with('goodsItemId.itemByLang')->get();

            $cadou = null;
            foreach ($basket as $item) {
                if ($item->has_cadou == 1 && $item->related_one_c_id > 0) {
                    $cadou = GoodsItemId::where('one_c_code', $item->related_one_c_id)
                        ->where('active', 1)
                        ->where('deleted', 0)
                        ->first();
                    break;
                }
            }

            $userInfo = $order->ordersFrontUser ?: $ordersUsers;
            $userDistrict = $ordersUsers->district_id ? getItemByIdSimple($ordersUsers->district_id, 'DeliveryState') : null;

            $emailMessage = getItemByAlias('email-success-message', 'MenuId');
            // h1_title бывает NULL даже при существующей lang_id-строке —
            // не на всех языках CMS-текст реально заполнен (живой случай:
            // 'ru' для 'email-success-message' существует, но пустой).
            $subject = $emailMessage && $emailMessage->itemByLang && $emailMessage->itemByLang->h1_title
                ? str_replace('{order_id}', (string) $order->id, $emailMessage->itemByLang->h1_title)
                : ShowLabelById(46);

            $viewData = [
                'orders_users' => $ordersUsers,
                'orders' => $order,
                'orders_data' => $ordersData,
                'basket' => $basket,
                'cadou' => $cadou,
                'discount_goods_price' => $ordersData->total_discount,
                'user_info' => $userInfo,
                'user_district' => $userDistrict,
                'email_message' => $emailMessage,
                'payment' => $payment,
            ];

            // Клиент (if_admin=0) первым, затем BCC-список из CMS-настройки
            // 'email-phone' (if_admin=1) — те же два прохода, что были раньше,
            // просто без риска продублировать адрес, если он совпадает с клиентским.
            // Заказы из приложения менеджерам на почту исторически не шлются —
            // они приходят через Platon/Progression (см. докблок AppOrderService) —
            // это поведение сохраняем и здесь, а не только для cash-веток сайта.
            $recipients = [$email => 0];
            if ($order->source !== 'app') {
                foreach (explode(',', (string) showSettingBodyByAlias('email-phone')) as $adminEmail) {
                    $adminEmail = trim($adminEmail);
                    if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL) && !isset($recipients[$adminEmail])) {
                        $recipients[$adminEmail] = 1;
                    }
                }
            }

            // CMS-настройка 'send-email-from' в деве/локально сейчас пустая
            // (см. blockers.md) — без фолбэка Mail::from(null, ...) падает
            // TypeError'ом и письмо не уходит вообще никому, даже cash.
            $fromAddress = showSettingBodyByAlias('send-email-from') ?: config('mail.from.address');

            foreach ($recipients as $recipient => $ifAdmin) {
                Mail::send('front.email.emailNewOrder', $viewData + ['if_admin' => $ifAdmin], function ($message) use ($recipient, $subject, $fromAddress) {
                    $message->from($fromAddress, ShowLabelById(46));
                    $message->to($recipient);
                    $message->subject($subject);
                });
            }

            $ordersData->update(['email_sent' => 1]);
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    /**
     * Отложенная отправка (card) идёт из вебхука банка —
     * `payments/bank/*` зарегистрирован без единого мидлвара (см.
     * routes/web.php), в отличие от обычных страниц сайта
     * (SetLocale/GetTranslate/GetSettings) и даже AppApi
     * (appApiLocale/GetTranslate/GetSettings). Без них: LANG_ID не
     * определена (ShowLabelById/itemByLang падают), config('translator.*')
     * пустой (ShowLabelById возвращает "Error translate" для каждой
     * надписи в письме), config('settings.*') тоже пустой
     * (showSettingBodyByAlias — в т.ч. 'send-email-from' — возвращает
     * null). Раньше это было не видно: card-заказы уходили сразу при
     * оформлении, внутри обычного HTTP-запроса сайта, где всё это уже
     * отработало. Здесь — тот же бутстрап вручную, один раз за вызов,
     * ничего не трогает на обычном HTTP-запросе (cash), где он уже сделан.
     */
    private function ensureLocaleBootstrapped(): void
    {
        if (defined('LANG_ID')) {
            return;
        }

        $locale = app()->getLocale();
        $langId = array_search($locale, config('app.locales', []), true);
        $langId = $langId !== false ? $langId : null;

        define('LANG', $locale);
        define('LANG_ID', $langId);
        define('LANGS_ADMIN', collect());
        define('LANGS_FRONT', collect(config('app.locales', [])));

        $translator = $langId !== null
            ? Labels::select('name', 'labels_id')->where('lang_id', $langId)->get()->pluck('name', 'labels_id')->toArray()
            : [];
        Config::set('translator', $translator);

        $settings = SettingsId::with('ItemByLang')->get()->keyBy('alias')
            ->map(fn ($item) => $item->lang_dependent == 1 ? ($item->ItemByLang->body ?? '') : $item->body_without_lang)
            ->toArray();
        Config::set('settings', $settings);
    }
}
