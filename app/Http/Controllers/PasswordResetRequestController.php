<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class PasswordResetRequestController extends Controller
{

    public function sendPasswordResetEmail(Request $request)
    {
        // If email does not exist
        if (!$this->validEmail($request->email)) {
            // return response()->json([
            //     'message' => 'Email does not exist.'
            // ], Response::HTTP_NOT_FOUND);

            return response()->json([
                'success' => false,
                "data" => ["type" => "error",
                           "message" => 'Email does not exist.',]
            ], Response::HTTP_NOT_FOUND);

        } else {
            // If email exists
            $User = User::where('UserEmail', $request->email)->where('UserType',6)->first();
            $usermname = $User->FirstName.' '.$User->LastName;
            $this->sendMail($request->email, $token = $this->generateToken($request->email), $usermname);

            // return response()->json([
            //     'message' => 'Check your inbox, we have sent a link to reset email.',
            //     'token' => $token,
            // ], Response::HTTP_OK);

            $data['user'] = $usermname;
            $data['token'] = $token;

            return response()->json([
                'success' => true,
                "data" => ["type" => "success",
                           "message" => 'Check your inbox, we have sent a link to reset email.',
                           "data" => $data,]
            ], Response::HTTP_OK);
        }
    }


    public function sendMail($emailTo, $token, $usermname)
    {
        // Mail::to($email)->send(new SendMail($token));
        $isOtherToken = DB::table('password_resets')->where('email', $emailTo)->first();
        $otp = rand(1000,9999);
        if (!empty($isOtherToken)) {
            DB::table('password_resets')->where('email',$emailTo)->update([
                'otp' => $otp
            ]);
        }

        $subject = 'Login OTP';
        $emailFrom = 'no-reply@jfds.co.uk';
        $data_set = ['usermname'=>$usermname,'otp'=>$otp];
        try{
            Mail::send(['html' => 'Mail.Password'], $data_set, function($message) use(&$emailTo, &$subject, &$emailFrom) {
                $message->to($emailTo, $emailTo)->subject($subject);
                if($emailFrom){
                    $message->from($emailFrom, $emailFrom);
                }
            });
        } catch (Exception $e) {
                echo $e->getMessage();
        }
    }

    public function validEmail($email)
    {
        return !!User::where('UserEmail', $email)->where('UserType',6)->first();
    }

    public function generateToken($email)
    {

        $isOtherToken = DB::table('password_resets')->where('email', $email)->first();

        if ($isOtherToken != null) {
            return $isOtherToken->token;
        }

        //   $token = Str::random(80);;
        $token = rand(1, 9999);
        $this->storeToken($token, $email);
        return $token;
    }

    public function storeToken($token, $email)
    {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }
}
