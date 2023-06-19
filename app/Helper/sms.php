<?php

use App\Jobs\SendSMSJob;
use App\Models\Options;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// function verifySMS($number, $code)
// {
//     $username = "";
//     $password = '';
//     $from = "";
//     $pattern_code = "";
//     $to = array($number);
//     $input_data = array("code" => $code, 'company' => '');
//     $url = "https://ippanel.com/patterns/pattern?username=" . $username . "&password=" . urlencode($password) . "&from=$from&to=" . json_encode($to) . "&input_data=" . urlencode(json_encode($input_data)) . "&pattern_code=$pattern_code";
//     $handler = curl_init($url);
//     curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
//     curl_setopt($handler, CURLOPT_POSTFIELDS, $input_data);
//     curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
//     $response = curl_exec($handler);
// }

function verifySMS($number, $code)
{
    if (config('app.env') == 'local') return false;
    $userName = env('SMS_PANEL_USERNAME', 'username');
    $password = env('SMS_PANEL_USERNAME', 'password');
    $fromNumber = env('SMS_PANEL_USERNAME', '1000000');

    $toNumbers = $number;
    $messageContent = "کد تایید شما : " . $code;
    $url = "http://sms1.webhoma.ir/SMSInOutBox/SendSms?username=" . $userName . "&password=" . $password . "&from=" . $fromNumber . "&to=" . $toNumbers . "&text=" . $messageContent;
    $response = Http::get($url);
    if ($response != 'SendWasSuccessful') {
        throw new Exception('SMS was not sent!');
    }
}
