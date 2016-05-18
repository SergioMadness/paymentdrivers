<?php

namespace professionalweb\paymentdrivers\rapida;

class Rapida extends \professionalweb\paymentdrivers\abstraction\PaymentSystem
{
    //
    //<editor-fold desc="Constants" defaultstate="collapsed">
    /**
     * Function name for payment checking
     */
    const FUNCTION_CHECK = 'check';

    /**
     * Function name for payment
     */
    const FUNCTION_PAY = 'payment';

    /**
     * Function name for payment status checking
     */
    const FUNCTION_CHECK_STATUS = 'getstate';

    //</editor-fold>
    //
    //<editor-fold desc="Variables" defaultstate="collapsed">
    /**
     * Terminal id
     *
     * @var int
     */
    private $termId;

    /**
     * TermType param
     *
     * @var string
     */
    private $termType;

    /**
     * Path to ca-info
     *
     * @var string
     */
    private $caInfoPath;

    /**
     * Path to ceritificate
     *
     * @var string
     */
    private $sslCertPath;

    /**
     * Path to private key
     *
     * @var string
     */
    private $sslKeyPath;

    /**
     * Private key password
     *
     * @var string
     */
    private $sslKeyPassword;

    //</editor-fold>

    /**
     * Prepare params for request
     *
     * @param array $params
     * @return string
     */
    protected function prepareParams(array $params)
    {
        $result = array();
        foreach ($params as $key => $value) {
            $result[] = urldecode($key.' '.$value);
        }
        return implode(';', $result);
    }

    /**
     * Execute request
     *
     * @param array $params
     * @return mixed
     */
    public function sendMessage(array $params = array())
    {
        return \pwf\helpers\HttpHelper::sendCurl($this->getUrl().'?'.http_build_query($params),
                array(
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_CAINFO => $this->getCAPath(),
                CURLOPT_SSLCERT => $this->getSSLCertPath(),
                CURLOPT_SSLKEY => $this->getSSLKeyPath(),
                CURLOPT_SSLKEYPASSWD => $this->getSSLKeyPassword(),
                CURLOPT_RETURNTRANSFER => true
        ));
    }

    /**
     * Validate
     *
     * @return mixed
     */
    public function validate()
    {
        return $this->sendMessage(array(
                'Function' => self::FUNCTION_CHECK,
                'PaymExtId' => $this->getSessionStr(),
                'PaymSubjTp' => $this->getServiceId(),
                'Amount' => $this->getAmount(),
                'Params' => $this->prepareParams($this->getParams()),
                'TermType' => $this->getTermType(),
                'TermID' => $this->getTermId(),
                'FeeSum' => $this->getFee()
        ));
    }

    /**
     * Cut field code from error message
     *
     * @param string $errorMessage
     * @return int
     */
    public function parseFieldId($errorMessage)
    {
        $result = 0;

        $matches = array();
        if (preg_match('/\s(\d+)\s/', $errorMessage, $matches)) {
            $result = (int) $matches[1];
        }

        return $result;
    }

    /**
     * Check status
     *
     * @return mixed
     */
    public function checkStatus()
    {
        return $this->sendMessage(array(
                'Function' => self::FUNCTION_CHECK_STATUS,
                'PaymExtId' => $this->getPaymentId()
        ));
    }

    /**
     * @inheritdoc
     */
    public function pay()
    {
        return $this->sendMessage(array(
                'Function' => self::FUNCTION_PAY,
                'PaymExtId' => $this->getPaymentId(),
                'PaymSubjTp' => $this->getServiceId(),
                'Amount' => $this->getAmount(),
                'Params' => $this->prepareParams($this->getParams()),
                'TermType' => $this->getTermType(),
                'TermID' => $this->getTermId(),
                'FeeSum' => $this->getFee(),
                'TermTime' => date('Ymd\THisO')
        ));
    }

    //<editor-fold desc="Getters and setters" defaultstate="collapsed">
    /**
     * Set path to ca
     *
     * @param string $path
     * @return Rapida
     */
    public function setCAPath($path)
    {
        $this->caInfoPath = $path;
        return $this;
    }

    /**
     * Get path to ca
     *
     * @return string
     */
    public function getCAPath()
    {
        return $this->caInfoPath;
    }

    /**
     * Set path to ssl certificate
     *
     * @param string $path
     * @return Rapida
     */
    public function setSSLCertPath($path)
    {
        $this->sslCertPath = $path;
        return $this;
    }

    /**
     * Get path to ssl certificate
     *
     * @return string
     */
    public function getSSLCertPath()
    {
        return $this->sslCertPath;
    }

    /**
     * Set path to ssl private key
     *
     * @param string $path
     * @return Rapida
     */
    public function setSSLKeyPath($path)
    {
        $this->sslKeyPath = $path;
        return $this;
    }

    /**
     * Get path to ssl private key
     *
     * @return string
     */
    public function getSSLKeyPath()
    {
        return $this->sslKeyPath;
    }

    /**
     * Set private key password
     *
     * @param string $password
     * @return Rapida
     */
    public function setSSLKeyPassword($password)
    {
        $this->sslKeyPassword = $password;
        return $this;
    }

    /**
     * Get private key password
     *
     * @return string
     */
    public function getSSLKeyPassword()
    {
        return $this->sslKeyPassword;
    }

    /**
     * Set term type
     *
     * @param string $type
     * @return Rapida
     */
    public function setTermType($type)
    {
        $this->termType = $type;
        return $this;
    }

    /**
     * Get term type
     *
     * @return string
     */
    public function getTermType()
    {
        return $this->termType;
    }

    /**
     * Set terminal id
     *
     * @param int $termId
     * @return Rapida
     */
    public function setTermId($termId)
    {
        $this->termId = $termId;
        return $this;
    }

    /**
     * Get terminal id
     *
     * @return int
     */
    public function getTermId()
    {
        return $this->termId;
    }
    //</editor-fold>
}