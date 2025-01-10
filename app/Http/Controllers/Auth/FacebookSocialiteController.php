<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Socialite;
use Auth;
use Exception;
use App\Models\User;
use Hash;
use Carbon;


class FacebookSocialiteController extends Controller
{


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleFacebookCallback()
    {

        try {
     
            $user = Socialite::driver('facebook')->user();

            // dd($user);
      
            $finduser = User::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::login($finduser);
     
                return redirect('/dashboard');
      
            }else{
                $newUser = User::create([
                    'name' => $user->name,
                    'username' => $user->email,
                    'email' => $user->email,
                    'social_id'=> $user->id,
                    'social_type'=> 'facebook',
                    'password' => Hash::make('my-google')
                ]);
     
                Auth::login($newUser);
      
                return redirect('/dashboard');
            }
     
        } catch (Exception $e) {
            dd($e->getMessage());
        }




    }
}
