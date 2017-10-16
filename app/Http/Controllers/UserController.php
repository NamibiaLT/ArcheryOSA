<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Redirect;
use App\Http\Requests\Users\RegisterValidator;
use App\Http\Requests\Users\UpdateProfileValidator;

class UserController extends Controller
{



    public function PUBLIC_getRegisterView()
    {
        return view ('auth.register');
    }

    public function PUBLIC_getLoginView()
    {
        return view ('auth.login');
    }

    /*****************************************************
     *                                                   *
     *                ADMIN / AUTH METHODS               *
     *                                                   *
     *****************************************************/




    /**
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {

        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')], true) === false) {

            return Redirect::back()
                ->withInput()
                ->withErrors(['email'=>' ', 'password'=>'Invalid Email or Password']);
        }
        return Redirect::route('home');
    }



    /**
     * @param Request $request
     * @return mixed
     */
    public function register(RegisterValidator $request)
    {
        $user                   = new User();
        $user->firstname        = htmlentities($request->input('firstname'));
        $user->lastname         = htmlentities($request->input('lastname'));
        $user->email            = htmlentities($request->input('email'));
        $user->password         = Hash::make($request->input('password'));
        $user->lastipaddress    = $request->ip();
        $user->usertype         = 3;

        $user->save();

        Auth::login($user);

        return Redirect::route('home');

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getProfileView()
    {
        $user = Auth::user();
        $organisations = DB::select("SELECT *
                                    FROM `usermemberships`
                                    JOIN `organisations`
                                    USING (`organisationid`)
                                    WHERE `userid` = '". Auth::id() ."'
        
        ");


        return view('auth.profile', compact('user', 'organisations'));
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updateProfile(UpdateProfileValidator $request)
    {
        // Used for adding days to the event
        if ($request->input('submit') == 'add') {
            return Redirect::route('createusermembershipview');
        }

        $user               = Auth::user();
        $user->email        = request('email');
        $user->firstname    = request('firstname');
        $user->lastname     = request('lastname');
        $user->phone        = request('phone');

        if ($request->hasFile('profileimage')) {
            //clean up old image
            if (empty($user->image) !== true) {
                unlink(public_path('content/profile/' . $user->image));
            }

            $image = $request->file('profileimage');
            $filename = time() . rand(0,999) . '.' . $image->getClientOriginalExtension();
            $location = public_path('content/profile/' . $filename);
            Image::make($image)->resize(200,200)->save($location);
            $user->image = $filename;
        }

        $user->save();

        return redirect('/profile')->with('key', 'Update Successful');
    }



    public function forgotpassword()
    {
        $user = Auth::user();

        dd($user);
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        Auth::logout();
        return Redirect::route('home');
    }

} // classend




