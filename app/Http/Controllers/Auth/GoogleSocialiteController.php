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
   
class GoogleSocialiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        // dd(555);
        return Socialite::driver('google')->redirect();
    }
       
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {
     
            $user = Socialite::driver('google')->user();

            // dd($user->token);

            // $user1 = Socialite::driver('google')->userFromToken($user->token);
            // dd($user1);

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
                    'social_type'=> 'google',
                    'password' => Hash::make('my-google')
                ]);
     
                Auth::login($newUser);
      
                return redirect('/dashboard');
            }
     
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return redirect()->back()->with(['message' => $validator->messages()], 200);
        }

       

        try {

            if (Auth::attempt($credentials)) {
                // Authentication passed...
                return redirect()->intended('dashboard');
            }else{
                return redirect()->back()->with(['message' => "Credentials not matched"], 200);
            }

        } catch (Exception $e) {

            dd($e->getMessage());
        }

    }
}