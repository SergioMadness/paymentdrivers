<?php

namespace professionalweb\paymentdrivers\abstraction;

abstract class PaymentSystem implements \professionalweb\paymentdrivers\interfaces\PaymentSystem
{
    /**
     * Request params
     *
     * @var array
     */
    private $params;

    /**
     * WebService url
     *
     * @var string
     */
    private $url;

    /*
     * Payment ID
     *
     * @var int
     */
    private $paymentId;

    /**
     * Service id
     *
     * @var int
     */
    private $serviceId;

    /**
     * Amount
     *
     * @var float
     */
    private $amount;

    /**
     * Payment fee
     *
     * @var float
     */
    private $fee;

    /**
     * Session string
     *
     * @var string
     */
    private $sessionStr;

    public function __construct()
    {
        $this->setParams([]);
    }

    /**
     * Set params
     *
     * @param array $params
     * @return \professionalweb\paymentdrivers\abstraction\PaymentSystem
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return \professionalweb\paymentdrivers\abstraction\PaymentSystem
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set payment id
     *
     * @param int $id
     * @return \project\Libs\PaymentGate\interfaces\Paymentgate
     */
    public function setPaymentId($id)
    {
        $this->paymentId = $id;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Set service ID
     *
     * @param int $serviceId
     * @return \project\Libs\PaymentGate\interfaces\Paymentgate
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * Get service id
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return \project\Libs\PaymentGate\interfaces\Paymentgate
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set session ID
     *
     * @param string $str
     * @return \project\Libs\PaymentGate\abstraction\PaymentGate
     */
    public function setSessionStr($str)
    {
        $this->sessionStr = $str;
        return $this;
    }

    /**
     * Session identity
     *
     * @return string
     */
    public function getSessionStr()
    {
        return $this->sessionStr;
    }

    public function setFee($fee)
    {
        $this->fee = $fee;
        return $this;
    }

    public function getFee()
    {
        return $this->fee;
    }
}