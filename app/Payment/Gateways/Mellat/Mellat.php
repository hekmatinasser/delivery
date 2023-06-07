<?php


namespace App\Payment\Gateways\Mellat;


use App\Payment\Gateways\GatewayAbstract;
use Exception;

class Mellat extends GatewayAbstract
{
    private $terminalID;
    private $username;
    private $password;
    protected $client;

    /**
     * Mellat constructor.
     */
    public function __construct()
    {
        $this->terminalID = config('payment.gateways.mellat.terminal_id');
        $this->username = config('payment.gateways.mellat.username');
        $this->password = config('payment.gateways.mellat.password');
    }

    /**
     * Pay
     *
     * @return mixed|void
     */
    public function pay(array $attributes = [])
    {
        $res = $this->sendPayRequest();
        if (!empty($res->return)) {
            $ex_res = explode(',', $res->return);
        } else {
            $ex_res = explode(',', $res);
        }

        if ($res && $ex_res[0] == 0) {
            $this->isOk = true;
            $this->setPayURL(route('payment::mellat.pay', $ex_res[1]));
            $this->setReferenceID($res[1]);
        } else {
            $this->isOk = false;
            $this->setErrorCode(401);
            $this->setErrorMessage('مشکل در اتصال به درگاه');
        }

        return $this;
    }

    /**
     * Verify payment
     *
     * @param array $attributes
     * @return $this|mixed
     */
    public function verify(array $attributes = [])
    {
        $res = $this->sendVerifyRequest($attributes['sale_ref_id']);

        if ($res && $res[0] == 0) {
            $this->setReferenceID($attributes['ref_id']);
            $this->isOk = true;
        } else {
            $this->setErrorCode(401);
            $this->setErrorMessage('مشکل در تایید پرداخت رخ داد');
        }

        return $this;
    }

    /**
     * Send pay request to gateway
     *
     * @return false
     */
    protected function sendPayRequest()
    {
        $namespace 	='http://interfaces.core.sw.bps.com/';

        try {
            $client = new \SoapClient(config('payment.gateways.mellat.request_url'));
            $this->client = $client;
        } catch (Exception $e) {
            return false;
        }

        $parameters = array(
            'terminalId' => $this->terminalID,
            'userName' => $this->username,
            'userPassword' => $this->password,
            'amount' => $this->amount,
            'orderId' => '',
            'localDate' => date('Ymd'),
            'localTime' => date('Gis'),
            'additionalData' => '',
            'callBackUrl' => $this->callback_url,
            'payerId' => $this->userID);

        // send request to gateway
        $result = $client->bpPayRequest($parameters, $namespace);
        return $result;
    }

    /**
     * Send verify request
     *
     * @param $ref_id
     * @return false
     */
    public function sendVerifyRequest($ref_id)
    {
        try {
            $client = new \SoapClient(config('payment.gateways.mellat.verify_url'));
            $this->client = $client;
        } catch (Exception $e) {
            return false;
        }

        $namespace='http://interfaces.core.sw.bps.com/';

        $parameters = array(
            'terminalId' => $this->terminalID,
            'userName' => $this->username,
            'userPassword' => $this->password,
            'orderId' => '',
            'saleOrderId' => '',
            'saleReferenceId' => $ref_id);
        $result = $client->bpVerifyRequest($parameters, $namespace);

        if (!empty($result->return)) {
            $ex_res = explode(',', $result->return);
        } else {
            $ex_res = explode(',', $result);
        }

        if ($result && $ex_res[0] == 0) {
            $resultsettle = $this->client->bpSettleRequest($parameters, $namespace);
            $resultStrsettle = $resultsettle->return;
            $ressettle = @explode (',',$resultStrsettle);
            $ResCodesettle = $ressettle[0];
            return $ResCodesettle;
        }

        return false;
    }

    /**
     * Check is ok
     * @return mixed
     */
    public function isOk()
    {
        return $this->isOk;
    }
}
