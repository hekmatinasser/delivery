<?php


namespace App\Payment\Gateways;


abstract class GatewayAbstract
{
    protected $amount;
    protected $callback_url;
    protected $description = '';
    protected $userID;
    protected $email = '';
    protected $mobile = '';
    protected $authorityCode;
    protected $errorMessage = '';
    protected $errorCode = 100;
    protected $referenceID;
    protected $payURL;
    protected $isOk;

    /**
     * Set amount
     *
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Set callback url
     *
     * @param $callbackURL
     * @return $this
     */
    public function setCallbackURL($callbackURL)
    {
        $this->callback_url = $callbackURL;
        return $this;
    }

    /**
     * Set description
     *
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set email
     *
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Set email
     *
     * @param $userID
     * @return $this
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;
        return $this;
    }

    /**
     * Set mobile
     *
     * @param $mobile
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * Set authority code
     *
     * @param $authorityCode
     * @return $this
     */
    public function setAuthorityCode($authorityCode)
    {
        $this->authorityCode = $authorityCode;
        return $this;
    }

    /**
     * Get authority code
     *
     * @return mixed
     */
    public function getAuthorityCode()
    {
        return $this->authorityCode;
    }

    /**
     * Set error message
     *
     * @param $message
     * @return $this
     */
    protected function setErrorMessage($message)
    {
        $this->errorMessage = $message;
        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set error code
     *
     * @param $errorCode
     * @return $this
     */
    protected function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * Get error code
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set verify url
     *
     * @param $url
     * @return $this
     */
    public function setPayURL($url)
    {
        $this->payURL = $url;
        return $this;
    }

    /**
     * Get verify url
     *
     * @return mixed
     */
    public function getPayURL()
    {
        return $this->payURL;
    }

    /**
     * Set reference id
     *
     * @param $refID
     * @return $this
     */
    protected function setReferenceID($refID)
    {
        $this->referenceID = $refID;
        return $this;
    }


    /**
     * Get reference id
     *
     * @return mixed
     */
    public function getReferenceID()
    {
        return $this->referenceID;
    }


    /**
     * @return mixed
     */
    abstract public function pay(array $attributes = []);

    /**
     * @return mixed
     */
    abstract public function verify(array $attributes = []);

    /**
     * @return mixed
     */
    abstract public function isOk();

}
