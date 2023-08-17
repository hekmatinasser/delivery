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
    if (config('app.env') == 'local')
        return false;
    $userName = config('admin.smsPanel.username');
    $password = config('admin.smsPanel.pass');
    $fromNumber = config('admin.smsPanel.number');

    $toNumbers = $number;
    $messageContent = "کد تایید شما : " . $code;
    $url = "http://sms1.webhoma.ir/SMSInOutBox/SendSms?username=" . $userName . "&password=" . $password . "&from=" . $fromNumber . "&to=" . $toNumbers . "&text=" . $messageContent;
    $response = Http::get($url);
    if ($response != 'SendWasSuccessful') {
        throw new Exception('SMS was not sent!');
    }
}



function registerNotice($number, $pass)
{
    if (config('app.env') == 'local')
        return false;
    $userName = config('admin.smsPanel.username');
    $password = config('admin.smsPanel.pass');
    $fromNumber = config('admin.smsPanel.number');

    $toNumbers = $number;
    $messageContent = "برای ورود از اطلات زیر استفاده کنید:\nنام کاربری:$number\nرمزعبور:$pass";
    $url = "http://sms1.webhoma.ir/SMSInOutBox/SendSms?username=" . $userName . "&password=" . $password . "&from=" . $fromNumber . "&to=" . $toNumbers . "&text=" . $messageContent;
    $response = Http::get($url);
    if ($response != 'SendWasSuccessful') {
        throw new Exception('SMS was not sent!');
    }
}


function updatePassNotice($number, $pass)
{
    if (config('app.env') == 'local')
        return false;
    $userName = config('admin.smsPanel.username');
    $password = config('admin.smsPanel.pass');
    $fromNumber = config('admin.smsPanel.number');

    $toNumbers = $number;
    $messageContent = "رمز عبور جدید : $pass";
    $url = "http://sms1.webhoma.ir/SMSInOutBox/SendSms?username=" . $userName . "&password=" . $password . "&from=" . $fromNumber . "&to=" . $toNumbers . "&text=" . $messageContent;
    $response = Http::get($url);
    if ($response != 'SendWasSuccessful') {
        throw new Exception('SMS was not sent!');
    }
}


function updateUserStatusNotice($number, $newStatus)
{
    $status = '';
    switch ($newStatus) {
        case '1':
            $status = 'فعال';
            break;
        case '-2':
            $status = 'تعلیق';
            break;
        case '-1':
            $status = 'غیر فعال';
            break;
        case '0':
            $status = 'درانتظار بررسی';
            break;
        case '2':
        default:
            $status = 'نامشخص';
            break;
    }
    Log::debug('call sms :: ' . $status);
    if (config('app.env') == 'local')
        return false;
    $userName = config('admin.smsPanel.username');
    $password = config('admin.smsPanel.pass');
    $fromNumber = config('admin.smsPanel.number');

    $toNumbers = $number;
    $messageContent = "وضعیت کاربری شما به $status تغییر پیدا کرد.";
    $url = "http://sms1.webhoma.ir/SMSInOutBox/SendSms?username=" . $userName . "&password=" . $password . "&from=" . $fromNumber . "&to=" . $toNumbers . "&text=" . $messageContent;
    $response = Http::get($url);
    if ($response != 'SendWasSuccessful') {
        throw new Exception('SMS was not sent!');
    }
}