<?php

namespace App\Http\Controllers\Auth;

use App\Mail\verifyEmail;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user= User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'verifyToken'=>Str::random(40),
        ]);
        $thisUser= User::findOrFail($user->id);
        $this->sendEmail($thisUser);
    }
    public function sendEmail($thisUsr)
    {
        Mail::to($thisUsr['email'])->send(new verifyEmail($thisUsr));
    }
    public function verifyEmailFirst()
    {
        return view('email.verifyEmailFirst');
    }
    public function sendEmailDone($email,$verifyToken)
    {
        $user=User::where(['email'=>$email,'verifyToken'=>$verifyToken])->first();
        if($user)
        {
            return User::where(['email'=>$email,'verifyToken'=>$verifyToken])->update(['status'=>'1','verifyToken'=>NULL]);
        }
        else
        {
            return 'user not found';
        }
    }
}
