<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Socialite;
use Hash;
use Carbon;
use Illuminate\Validation\Rule;
use File;

class AuthController extends Controller
{

    public function authenticate(Request $request)
    {
        // $credentials = $request->only('email', 'password');

        $credentials = array('UserEmail' => $request->email, 'password' => $request->password, 'UserType' => '6');
        $validator = Validator::make($credentials, [
            'UserEmail' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => $validator->messages()]], 422);
        }

        //Request is validated
        return $this->createToken($credentials);
    }

    public function createToken($credentials)
    {
        //Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {

                return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Login credentials are invalid."]], 500);
            }
        } catch (JWTException $e) {

            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Could not create token."]], 500);
        }

        return $this->respondWithToken($token);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($token);
            return response()->json(['success' => True, "data" => ["type" => "success", 'message' => "User has been logged out"]], 200);

        } catch (JWTException $exception) {

            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Something Went Wrong..."]], 500);
        }
    }

    public function getUser(Request $request)
    {
        $user = JWTAuth::user();

        return response()->json(['success' => True, "data" => ["type" => "success", 'message' => "User has been logged out", "data" => $user]], 200);

    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */

    protected function respondWithToken($token)
    {
        return response()->json(['success' => True, "data" => ["type" => "success", 'message' => "Request perform successfully.", "data" => [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]]], 200);
    }


    public function resetPass(Request $request)
    {

        $data = $request->only('newPassword', 'oldPassword', 'newPasswordConfirm');
        $validator = Validator::make($data, [
            'oldPassword' => 'required|string|min:6|max:50',
            'newPassword' => 'required|string|min:6|max:50|same:newPasswordConfirm',
            'newPasswordConfirm' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => $validator->messages()]], 422);
        }

        $user = JWTAuth::user();

        if (Hash::check($request->oldPassword, $user->password)) {

            $password = $request->newPassword;
            $user->password = Hash::make($password);

            if ($user->save()){

                return response()->json(['success' => True, "data" => ["type" => "success", 'message' => "Password Reset successfully."]], 200);
            }
            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Something Went Wrong..."]], 500);

        }else{

            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Old password not match.."]], 500);
        }

    }

    public function sendResetResponse(Request $request)
    {
        $credentials = $request->only('email');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => $validator->messages()]], 422);
        } else {
            $email = $request->email;
            $isEmailValid = DB::table('users')->where('UserEmail', $email)->first();
            if (!empty($isEmailValid)) {
                $credentials = $request->only('password', 'cpassword');
                $validator = Validator::make($credentials, [
                    'password' => 'required|string|min:6|max:50',
                    'cpassword' => 'required|string|min:6|max:50'
                ]);

                //Send failed response if request is not valid
                if ($validator->fails()) {
                    return response()->json(['success' => False, "data" => ["type" => "error", 'message' => $validator->messages()]], 422);
                } else {
                    if ($request->password == $request->cpassword) {
                        try {
                            $password = $request->password;
                            $request->password = Hash::make($password);
                            DB::table('users')
                                ->where('UserEmail', $email)
                                ->limit(1)
                                ->update(array('password' => $request->password));

                            return response()->json(['success' => True, "data" => ["type" => "success", 'message' => "Password Reset successfully."]], 200);
                        } catch (JWTException $e) {

                            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Something Went Wrong..."]], 500);
                        }
                    } else {

                        return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Password and Confirm Password field should be same..!"]], 500);
                    }
                }
            } else {

                return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Invalid Email Id"]], 500);
            }
        }
    }

    public function updateProfile(Request $request)
    {
        $users = JWTAuth::user();

        $data = $request->only('name', 'email', 'mobile_no', 'profile_photo');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => ['required', Rule::unique('users')->ignore($users->id)],
            'mobile_no' => 'digits_between:10,10',
            'profile_photo' => 'required|image|max:4096',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['success' => False, "data" => ["type" => "error", 'message' => $validator->messages()]], 422);
        }

        $users->name = $request->name;
        $users->email = $request->email;
        $users->mobile_no = $request->mobile_no;

        if ($request->hasFile('profile_photo')) {
            if ($users->profile_photo_path) {
                $old_path = public_path() . '/users/profile_photo/' . $users->profile_photo_path;
                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }
            $image_name = 'profile_photo_' . time() . '.' . $request->profile_photo->extension();
            $request->profile_photo->move(public_path('/users/profile_photo'), $image_name);
        } else {
            $image_name = $users->profile_photo;
        }

        $users->profile_photo_path = $image_name;

        if ($users->save()) {

            return response()->json(['success' => True, "data" => ["type" => "success", 'message' => "Profile Updated successfully."]], 200);
        }
        return response()->json(['success' => False, "data" => ["type" => "error", 'message' => "Something Went Wrong."]], 500);
    }

    public function otpVerify(Request $request){
        // If email does not exist
        if (!$this->validEmail($request->email)) {
            return response()->json([
                'message' => 'Email does not exist.'
            ], Response::HTTP_NOT_FOUND);
        } else {
            // If email exists
            $credentials = $request->only('otp');
            $validator = Validator::make($credentials, [
                'otp' => 'required|digits_between:1,10',
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['success' => False, "data" => ["type" => "error", 'message' => $validator->messages()]], 422);
            } else {
                $User = DB::table('password_resets')->where('email', $request->email)->first();
                if($User->otp == $request->otp){

                    // return response()->json([
                    //     'message' => 'OTP verify successfully',
                    // ], Response::HTTP_OK);

                    return response()->json([
                        'success' => true, 
                        "data" => ["type" => "success", 
                                   "message" => 'OTP verify successfully.',
                                   ]
                    ], Response::HTTP_OK);

                }
                else{
                    // return response()->json([
                    //     'message' => 'Invalid OTP Send',
                    // ], Response::HTTP_UNAUTHORIZED);

                    return response()->json([
                        'success' => false, 
                        "data" => ["type" => "error", 
                                   "message" => 'Invalid OTP Send.',
                                   ]
                    ], Response::HTTP_UNAUTHORIZED);
                }
            }
        }
    }

    public function validEmail($email)
    {
        return !!User::where('UserEmail', $email)->where('UserType',6)->first();
    }
}
