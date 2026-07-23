<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserAuthController extends Controller
{
    public function registerIndex()
    {
        $view = 'front.pages.user-auth.register';

        if (Session::get('session-front-user'))
            return redirect(url(LANG));

        $month_list = showSettingBodyByAlias('month-list') ? explode(';', showSettingBodyByAlias('month-list')) : [];

        $meta = collect([]);
        $meta->meta_static = ShowLabelById(29) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }

    /*public function loginIndex()
    {
        $view = 'front.pages.user-auth.login-page';

        if (Session::get('session-front-user'))
            return redirect(url(LANG));

        $meta = collect([]);
        $meta->meta_static = 'Register' . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }*/

    public function ajaxRegisterUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:front_user,email|max:255',
            'phone' => 'required|min:6|max:18',
            'birth_day' => 'nullable|integer',
            'birth_month' => 'nullable|integer|required_with:birth_day',
            'birth_year' => 'nullable|integer|required_with:birth_day',
            'password' => [
                'required',
                'string',
                Password::min(6) //Require at least 8 characters
                ->mixedCase() //Require at least one uppercase and one lowercase letter
                ->numbers() // Require at least one number
                ->letters(), //Require at least one letter
                //->symbols() //Require at least one symbol
                //->uncompromised(),
                'confirmed'
            ],
            'agree' => 'required'
        ]);

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

        $birth_day = $request->input('birth_day') ?? null;
        $birth_month = $request->input('birth_month') ?? null;
        $birth_year = $request->input('birth_year') ?? null;
        $birth = $birth_day && $birth_month && $birth_year ? $birth_day . '-' . $birth_month . '-' . $birth_year : null;
        //For confirmation user
        //$confirmation_hash = sha1($request->input('email') . time());

        $user = new FrontUser();
        $user->last_name = $request->input('last_name');
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->birth = $birth ? $birth : null;
        $user->password = bcrypt($request->input('password'));
        //$user->confirmation_hash = $confirmation_hash;
        $user->confirmation = 1;
        $user->save();

        if (empty($user))
            return response()->json([
                'status' => false,
                'text' => 'User not found',
            ]);

        if ($user) {

            $user_email = $user->email;
            $email_message = getItemByAlias('message-email-after-registration', 'MenuId');
            $subject = str_replace('{user_name}', $user->last_name . ' ' . $user->name, $email_message->itemByLang->h1_title) ?? ShowLabelById(46);

            Mail::send('front.email.emailUserConfirmation', ['email_message' => $email_message, 'user' => $user], function ($message) use ($user_email, $subject) {
                $message->from(showSettingBodyByAlias('send-email-from', LANG_ID));
                $message->to($user_email);
                $message->subject($subject);
            });

            Session::put('if-register-success', 1);
        } else
            return response()->json([
                'status' => false,
                'text' => 'User not found',
            ]);

        return response()->json([
            'status' => true,
            'remove_inputs_value' => 1,
            //'message' => 'Success message',
            'redirect' => route('register-success'),
        ]);
    }

    //For confirmation user
    /*public function userConfirmation($confirmation_hash)
    {
        if ($confirmation_hash == null)
            return redirect()->route('/');

        $user = FrontUser::where('confirmation_hash', $confirmation_hash)
            ->first();

        if (!$user)
            return redirect()->route('/');

        $user->confirmation = 1;
        $user->confirmation_at = Carbon::now();
        $user->confirmation_hash = null;
        $user->save();

        session()->flash('verification-message', ShowLabelById(199));
        return redirect()->route('/');
    }*/

    public function ajaxLoginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'messages' => $validator->messages(),
            ]);

        $user = FrontUser::where('email', $request->input('email'))->first();

        if ($user && Hash::check($request->input('password'), $user->password) == false) {
            $user = null;
        }

        if (is_null($user))
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(102)
            ]);

        //For confirmation user
        /*if ($user->confirmation == 0) {
            if ($user->confirmation_hash) {
                return response()->json([
                    'status' => 'warning',
                    'message' => str_replace('{link_confirmation}', '<a style="color: #ffffff;text-decoration:underline;font-weight:bold;" href="' . route('resend-user-confirmation', $user->confirmation_hash) . '">' . ShowLabelById(201) . '</a>', showLabelById(200))
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => ShowLabelById(206)
                ]);
            }
        }*/

        if ($user)
            Session::put('session-front-user', $user->id);
        else
            return response()->json([
                'status' => false,
                'text' => 'User not found',
            ]);

        if ($request->input('remember') == 'on') {
            Cookie::queue('front-user-remember', $user->id, config('custom.front.cookie_user_remember_time'));
        }
        return response()->json([
            'status' => true,
            'message' => ShowLabelById(103),
            'redirect' => $request->input('current_url')
        ]);
    }

    //For confirmation user
   /* public function resendUserConfirmation($confirmation_hash)
    {
        if ($confirmation_hash == null)
            return redirect()->route('/');

        $user = FrontUser::where('confirmation_hash', $confirmation_hash)
            ->first();

        if (!$user)
            return redirect()->route('/');

        $user_email = $user->email;
        $email_message = getItemByAlias('message-email-after-registration', 'MenuId');
        $subject = str_replace('{user_name}', $user->last_name . ' ' . $user->name, $email_message->itemByLang->h1_title) ?? ShowLabelById(46);

        Mail::send('front.email.emailUserConfirmation', ['email_message' => $email_message, 'user' => $user], function ($message) use ($user_email, $subject) {
            $message->from(showSettingBodyByAlias('send-email-from', LANG_ID));
            $message->to($user_email);
            $message->subject($subject);
        });

        return redirect(route('/'));
    }*/

    public function userLogout()
    {
        Session::forget('session-front-user');
        Cookie::queue(Cookie::forget('front-user-remember'));
        return back();
    }

    /*public function restorePasswordIndex()
    {
        $view = 'front.pages.user-auth.restore-password-page';

        $meta = collect([]);
        $meta->meta_static = 'Restore password' . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }*/

    public function ajaxRestorePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'agree' => 'required'
        ]);

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

        $user = FrontUser::where('email', $request->input('email'))->first();

        if (is_null($user))
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(126)
            ]);

        $hash_locale = sha1($request->input('email') . time());

        FrontUser::where('email', $request->input('email'))->update(['recovery_hash' => $hash_locale]);

        $my_email = $user->email;

        $email_message = getItemByAlias('message-email-password-reset', 'MenuId');
        $subject = $email_message && $email_message->itemByLang ? $email_message->itemByLang->h1_title : ShowLabelById(46);

        Mail::send('front.email.emailForgetPassword', ['hash_locale' => $hash_locale, 'email_message' => $email_message], function ($message) use ($my_email, $subject) {
            $message->from(showSettingBodyByAlias('send-email-from', LANG_ID));
            $message->to($my_email);
            $message->subject($subject);
        });
        return response()->json([
            'status' => true,
            'hide_modal' => 1,
            'message' => ShowLabelById(128),
            'redirect' => route('/')
        ]);
    }

    /*public function newPasswordIndex()
    {
        $view = 'front.pages.user-auth.new-password-page';

        $recovery_user = null;
        $hash = null;

        $hash = request()->input('h');
        if (!empty($hash))
            $recovery_user = FrontUser::where('recovery_hash', $hash)->first();
        else
            return redirect(LANG . '/');

        $meta = collect([]);
        $meta->meta_static = 'New password' . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }*/

    public function ajaxNewPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                Password::min(6) //Require at least 8 characters
                ->mixedCase() //Require at least one uppercase and one lowercase letter
                ->numbers() // Require at least one number
                ->letters(), //Require at least one letter
                //->symbols() //Require at least one symbol
                //->uncompromised(),
                'confirmed'
            ],
        ]);
        if ($validator->fails())
            return response()->json([
                'status' => false,
                'messages' => $validator->messages(),
            ]);

        if (empty($request->input('hash')))
            return response()->json([
                'status' => false,
                'text' => trans('variables.something_wrong'),
            ]);

        $user = FrontUser::where('recovery_hash', $request->input('hash'))->first();

        if (is_null($user))
            return response()->json([
                'status' => false,
                'text' => 'User not found'
            ]);

        FrontUser::where('id', $user->id)->update([
            'password' => bcrypt($request->input('password')),
            'recovery_hash' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => ShowLabelById(135),
            'hide_modal' => 1,
            //'redirect' => route('/')
        ]);
    }

    public function registerSuccess()
    {
        if (Session::get('if-register-success') == 1) {
            $view = 'front.pages.user-auth.register-success';
            Session::forget('if-register-success');

            $email_message = getItemByAlias('message-after-registration', 'MenuId');

            $meta_static = '';
            $meta_static = 'Register' . ' - ' . env('APP_NAME') ?? env('APP_NAME');
        } else {
            return redirect(route('/'));
        }

        $meta = collect([]);
        $meta->meta_static = 'Register' . ' - ' . env('APP_NAME') ?? env('APP_NAME');
        return view($view, get_defined_vars());

    }
}

