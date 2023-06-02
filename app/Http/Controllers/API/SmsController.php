<?php
namespace App\Http\Controllers\API;
use App\Models\Log;
use App\Models\user\UserVerify;
use App\Models\VerifyCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\JsonResponse;
use SoapClient;

class SmsController extends BaseController
{
    public function send($number, $content)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $sms_client = new \SoapClient('http://payamak-service.ir/SendService.svc?wsdl', array('encoding' => 'UTF-8'));
        try {
            $parameters['userName'] = "";
            $parameters['password'] = '';
            $parameters['fromNumber'] = "10000100000";
            $parameters['toNumbers'] = array($number);
            $parameters['messageContent'] = $content;
            $parameters['recId'] = &$recId;
            $parameters['status'] = &$status;
            $res = $sms_client->SendSMS($parameters)->SendSMSResult;
            return true;
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }


    }

  
}
