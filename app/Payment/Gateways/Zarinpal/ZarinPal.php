<?php


namespace App\Payment\Gateways\Zarinpal;


use App\Payment\Gateways\GatewayAbstract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ZarinPal extends GatewayAbstract
{
    private $merchantID;

    /**
     * ZarinPal constructor.
     */
    public function __construct()
    {
        $this->merchantID = config('payment.gateways.zarinpal.merchant_id');
    }

    protected $apiResponse;

    /**
     * Pay
     *
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pay(array $attributes = [])
    {
        $res = $this->sendPayRequest();

        if(empty($res['errors'])) {
            $this->setAuthorityCode($res['data']['authority']);
            $this->setPayURL(config('payment.gateways.zarinpal.pay_url') . $res['data']['authority']);
        } else {
            $this->setErrorMessage($res['errors']['message']);
            $this->setErrorCode($res['errors']['code']);
        }

        return $this;
    }

    /**
     * Verify
     *
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify(array $attributes = [])
    {
        $res = $this->sendVerifyRequest();

        if(empty($res['errors'])) {
            $this->setReferenceID($res['data']['ref_id']);
        } else {
            $this->setErrorMessage($res['errors']['message']);
            $this->setErrorCode($res['errors']['code']);
        }

        return $this;
    }

    /**
     * Send Pay Request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPayRequest()
    {
        $client = new Client();
        try {
            $response = $client->post(config('payment.gateways.zarinpal.request_url'), [
                'form_params' => [
                    'merchant_id' => $this->merchantID,
                    'amount' => $this->amount,
                    'callback_url' => $this->callback_url,
                    'description' => $this->description,
                    'metadata' => [
                        'email' => $this->email,
                        'mobile' => $this->mobile
                    ]
                ]
            ]);
            $res = json_decode($response->getBody(), true);
        } catch (RequestException $exception) {
            $res = json_decode($exception->getResponse()->getBody(), true);
        }
        $this->apiResponse = $res;
        return $res;
    }

    /**
     * Send verify request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendVerifyRequest()
    {
        $client = new Client();
        try {
            $response = $client->post(config('payment.gateways.zarinpal.verify_url'), [
                'form_params' => [
                    'merchant_id' => $this->merchantID,
                    'authority' => $this->authorityCode,
                    'amount' => $this->amount
                ]
            ]);
            $res = json_decode($response->getBody(), true);
        } catch (RequestException $exception) {
            $res = json_decode($exception->getResponse()->getBody(), true);
        }

        $this->apiResponse = $res;
        return $res;
    }

    /**
     * check request has not error
     *
     * @return bool
     */
    public function isOk()
    {
        return empty($this->apiResponse['errors']);
    }

}
