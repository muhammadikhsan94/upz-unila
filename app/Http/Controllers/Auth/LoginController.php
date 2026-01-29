<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use SSO\SSO;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'username' => ['Username atau kata sandi yang Anda masukkan salah.'],
        ]);
    }

    public function username()
    {
        $login = request()->input('username');
        $field = 'no_punggung';

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        }

        request()->merge([$field => $login]);

        return $field;
    }

    public function signing_process() {
        if(SSO::authenticate()) //mengecek apakah user telah login atau belum
        {
            if(SSO::check()) {
                $check = User::where('email', SSO::getUser()->email)->first(); //mengecek apakah pengguna SSO memiliki username yang sama dengan database aplikasi
                if(!is_null($check)) {
                    Auth::loginUsingId($check->id); //mengotentikasi pengguna aplikasi
                    session()->flash('success', 'You are logged in!');
                    return redirect()->url('/');
                } else {
                    session()->flash('error', 'Failed login!');
                    return redirect()->route('login'); //mengarahkan ke halaman login jika pengguna gagal diotentikasi oleh aplikasi
                }
            }
        } else {
            return redirect()->route('logout'); //me-*redirect* user jika otentikasi SSO gagal, diarahkan untuk mengakhiri sesi login (jika ada)
        }
    }
}
