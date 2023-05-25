<?php

use App\Jobs\SendSMSJob;
use App\Models\Options;
use Carbon\Carbon;

function verifySMS($number, $code)
{
    $username = "";
    $password = '';
    $from = "";
    $pattern_code = "";
    $to = array($number);
    $input_data = array("code" => $code, 'company' => '');
    $url = "https://ippanel.com/patterns/pattern?username=" . $username . "&password=" . urlencode($password) . "&from=$from&to=" . json_encode($to) . "&input_data=" . urlencode(json_encode($input_data)) . "&pattern_code=$pattern_code";
    $handler = curl_init($url);
    curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($handler, CURLOPT_POSTFIELDS, $input_data);
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($handler);
}
