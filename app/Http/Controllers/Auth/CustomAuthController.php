<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomAuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('post'))
            return $this->checkLogin($request);

        if (Auth::user()) {
            return redirect(LANG . '/back');
        }

        $view = 'auth.login';

        //session()->put('backUrl', url()->previous());

        return view($view, get_defined_vars());
    }

    public function register(Request $request)
    {
        if ($request->isMethod('post'))
            return $this->checkRegister();

        $admin_user = User::get();

        if (count($admin_user) != 0) {
            return redirect(LANG . '/back/auth/login');
        }

        $view = 'auth.register';

        return view($view, get_defined_vars());
    }


    public function checkLogin(Request $request)
    {
        $messages = array();
        $item = Validator::make($request->all(), [
            'login' => 'required|min:3',
            'password' => 'required|min:4',
        ]);

        if ($item->fails())
            $messages = $item->messages();

        if (count($messages))
            return response()->json([
                'status' => false,
                'messages' => $messages,
            ]);

        $remember_me = $request->has('remember') ? true : false;

        //checkAuthFunction(true, Input::get('login'), Input::get('password'), Input::get('_token'));
        if (!Auth::attempt(array('login' => $request->input('login'), 'password' => $request->input('password')), $remember_me))
            return response()->json([
                'status' => false,
                'message' => [Lang::get('variables.user_not_exist', array(), LANG)],
            ]);

        setcookie('__amc', md5(md5('File man cookie security!') . date('Y.m.d')), time() + (86400 * 30), "/");

        Log::info('Name: ' . Auth::user()->name);

        /*if (Session::has('backUrl')) {
            return response()->json([
                'status' => true,
                'messages' => [Lang::get('variables.login_success_full', array(), LANG)],
                'redirect' => url(Session::get('backUrl')),
            ]);
        }*/

        return response()->json([
            'status' => true,
            'messages' => [trans('variables.login_success_full')],
            'redirect' => url(LANG . '/back'),
        ]);

    }


    public function checkRegister(Request $request)
    {

        $messages = array();
        $item = Validator::make($request->all(), [
            'name' => 'required',
            'login' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:4',
            'repeat_password' => 'required|same:password',
        ]);

        if ($item->fails())
            $messages = $item->messages();

        if (count($messages))
            return response()->json([
                'status' => false,
                'messages' => $messages,
            ]);

        $find_user = User::where('email', '=', $request->input('email'))->first();

        if ($find_user)
            return response()->json([
                'status' => false,
                'messages' => [Lang::get('variables.user_exist', array(), $this->lang()['lang'])],
            ]);

        User::create([
            'name' => $request->input('name'),
            'login' => $request->input('login'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'remember_token' => $request->input('_token'),
            'admin_user_group_id' => 1,

        ]);


        return response()->json([
            'status' => true,
            'messages' => [Lang::get('variables.register_success_full', array(), $this->lang()['lang'])],
            'redirect' => url($this->lang()['lang'] . '/back/auth/login'),
        ]);
    }

    public function logout()
    {
        Auth::logout();

        //checkAuthFunction(false);

        Cookie::queue(Cookie::forget('__amc'));

        return redirect(LANG . '/back/auth/login');
    }
}
