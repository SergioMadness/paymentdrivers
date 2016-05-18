<?php

namespace professionalweb\paymentdrivers\cyberplat;

class Cyberplat extends \professionalweb\paymentdrivers\abstraction\PaymentSystem
{
    //
    //<editor-fold desc="Constants" defaultstate="collapsed">
    /**
     * Url for 'check' function testing
     */
    const TEST_URL_CHECK = 'http://payment.cyberplat.ru/cgi-bin/es/es_pay_check.cgi';

    /**
     * Url for 'status check' function testing
     */
    const TEST_URL_STATUS = 'http://payment.cyberplat.ru/cgi-bin/es/es_pay_status.cgi';

    /**
     * Url for 'pay' function testing
     */
    const TEST_URL_PAY = 'http://payment.cyberplat.ru/cgi-bin/es/es_pay.cgi';

    /**
     * Processor type "check"
     */
    const PROCESSOR_TYPE_CHECK = 'check';

    /**
     * Processor type "payment"
     */
    const PROCESSOR_TYPE_PAYMENT = 'payment';

    /**
     * Processor type "status"
     */
    const PROCESSOR_TYPE_STATUS = 'status';

    /**
     * Processor type "getstep"
     */
    const PROCESSOR_TYPE_GETSTEP = 'getstep';

    /**
     * Line separator in request message
     */
    const MESSAGE_LINE_SEPARATOR = "\r\n";

    //</editor-fold>
    //
    //<editor-fold desc="Fields" defaultstate="collapsed">
    /**
     * Agent code
     *
     * @var int
     */
    private $sd;

    /**
     * Point code
     *
     * @var int
     */
    private $ap;

    /**
     * Operator code
     *
     * @var int
     */
    private $op;

    /**
     * Terminal id
     *
     * @var int
     */
    private $termId;

    /**
     * Accepted keys
     *
     * @var string
     */
    private $acceptedKeys;

    /**
     * Secret key for request signing
     *
     * @var string
     */
    private $secretKey;

    /**
     * Public key
     *
     * @var string
     */
    private $publicKey;

    /**
     * Password for secret key
     *
     * @var string
     */
    private $secretKeyPassword;

    /**
     * Serial
     *
     * @var int
     */
    private $serial;

    /**
     * REQ_TYPE param
     *
     * @var int
     */
    private $reqType;

    /**
     * PAY_TOOL param
     *
     * @var int
     */
    private $payTool;

    /**
     * NO_ROUTE param
     *
     * @var int
     */
    private $noRoute;

    //</editor-fold>

    /**
     * Send request
     *
     * @param array $params
     * @return mixed
     */
    public function sendMessage(array $params = array())
    {
        $clientParams = array(
            'SD' => $this->getSD(),
            'AP' => $this->getAP(),
            'OP' => $this->getOP(),
            'SESSION' => $this->getSessionStr(),
            'REQ_TYPE' => $this->getReqType(),
            'PAY_TOOL' => $this->getPayTool(),
            'TERM_ID' => $this->getTermId(),
            'ACCEPT_KEYS' => $this->getAcceptedKeys(),
            'NO_ROUTE' => $this->getNoRoute()
        );

        $message = implode(self::MESSAGE_LINE_SEPARATOR,
            $this->prepareParams(array_merge($clientParams, $params)));

        $signedMessage = ipriv_sign(mb_convert_encoding($message, 'cp1251',
                'utf-8'), $this->getSecretKey(), $this->getSecretKeyPassword());

        $inputMessage = 'inputmessage='.urlencode($signedMessage[1]);

        $response = $this->getResponse($inputMessage);

//        $response = $this->verifyResponse($respon se);

        return $this->decryptResponse($response);
    }

    /**
     * Get response from payment system
     *
     * @param string $inputMessage
     * @return string
     */
    protected function getResponse($inputMessage)
    {
        return \pwf\helpers\HttpHelper::sendCurl($this->getUrl(),
                array(
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $inputMessage,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
                CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: '.strlen($inputMessage))
        ));
    }

    /**
     * Decrypt response
     *
     * @param string $response
     * @return int
     */
    protected function decryptResponse($response)
    {
        $result = $this->parseResult($response);
//        $verifyResult = ipriv_decrypt($response, $this->getPublicKey(),
//            $this->getSerial());
//
//        return $verifyResult[0];
        return $result;
    }

    /**
     * Verify response
     *
     * @param string $response
     * @return string
     * @throws Exception
     */
    protected function verifyResponse($response)
    {
//        $verifyResult = ipriv_verify($response, $this->getPublicKey());
//        if (!$verifyResult[0] !== 0) {
//            throw new \Exception('Corrupted signature');
//        }
//        return $verifyResult[1];
        return true;
    }

    /**
     * Prepare params
     *
     * @param array $params
     * @return array
     */
    protected function prepareParams(array $params)
    {
        $result = array();

        foreach ($params as $key => $value) {
            $result[] = $key.'='.$value;
        }

        return $result;
    }

    /**
     * Check payment
     *
     * @return mixed
     */
    public function validate()
    {
        return $this->sendMessage(array_merge($this->getParams(),
                    array(
                'AMOUNT' => number_format($this->getAmount(), 2, '.', ''),
                'AMOUNT_ALL' => number_format($this->getAmount(), 2, '.', ''),
        )));
    }

    /**
     * Check status
     *
     * @return mixed
     */
    public function checkStatus()
    {
        return $this->sendMessage(array_merge(array(
                'ACCEPT_KEYS' => $this->getAcceptedKeys(),
                'SESSION' => $this->getSessionStr()
                    ), $this->getParams()));
    }

    /**
     * Pay
     *
     * @return boolean
     */
    public function pay()
    {
        return $this->sendMessage(array_merge(array(
                'AMOUNT' => number_format($this->getAmount(), 2),
                'AMOUNT_ALL' => number_format($this->getAmount(), 2),
                'DATE' => date('d.m.Y H:i:s'),
                'RRN' => $this->getPaymentId()
                    ), $this->getParams()));
    }

    /**
     * Parse cyberplat response
     *
     * @param string $text
     * @return array
     * @throws \Exception
     */
    protected function parseResult($text)
    {
        $result = array();
        $parsed = array();
        if (preg_match_all('/^(.*?)=(.*)/mi', $text, $parsed) !== false) {
            for ($i = 0; $i < count($parsed[0]); $i++) {
                if ($parsed[1][$i] !== '') {
                    $result[$parsed[1][$i]] = mb_convert_encoding(trim(urldecode($parsed[2][$i])),
                        'utf-8', 'cp1251');
                }
            }
        }

        return $result;
    }
    //
    //<editor-fold desc="Getters and setters" defaultstate="collapsed">

    /**
     * Set secret key password
     *
     * @param string $password
     * @return Cyberplat
     */
    public function setSecretKeyPassword($password)
    {
        $this->secretKeyPassword = $password;
        return $this;
    }

    /**
     * Get secret key password
     *
     * @return string
     */
    public function getSecretKeyPassword()
    {
        return $this->secretKeyPassword;
    }

    /**
     * Set secret key
     *
     * @param string $key
     * @return Cyberplat
     */
    public function setSecretKey($key)
    {
        $this->secretKey = $key;
        return $this;
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set public key
     *
     * @param string $key
     * @return Cyberplat
     */
    public function setPublicKey($key)
    {
        $this->publicKey = $key;
        return $this;
    }

    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set keys
     *
     * @param string $keys
     * @return Cyberplat
     */
    public function setAcceptedKeys($keys)
    {
        $this->acceptedKeys = $keys;
        return $this;
    }

    /**
     * Get keys
     *
     * @return string
     */
    public function getAcceptedKeys()
    {
        return $this->acceptedKeys;
    }

    /**
     * Set agent code
     *
     * @param int $sd
     * @return Cyberplat
     */
    public function setSD($sd)
    {
        $this->sd = $sd;
        return $this;
    }

    /**
     * Get agent code
     *
     * @return int
     */
    public function getSD()
    {
        return $this->sd;
    }

    /**
     * Set point code
     *
     * @param int $ap
     * @return Cyberplat
     */
    public function setAP($ap)
    {
        $this->ap = $ap;
        return $this;
    }

    /**
     * Get point code
     *
     * @return int
     */
    public function getAP()
    {
        return $this->ap;
    }

    /**
     * Set operator code
     *
     * @param int $op
     * @return Cyberplat
     */
    public function setOP($op)
    {
        $this->op = $op;
        return $this;
    }

    /**
     * Get operator code
     *
     * @return int
     */
    public function getOP()
    {
        return $this->op;
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

    /**
     * Set serial
     *
     * @param int $serial
     * @return Rapida
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;
        return $this;
    }

    /**
     * Get serial
     *
     * @return int
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Set PAY_TOOL param
     *
     * @param int $payTool
     * @return \project\Libs\PaymentGate\Cyberplat
     */
    public function setPayTool($payTool)
    {
        $this->payTool = $payTool;
        return $this;
    }

    /**
     * Get PAY_TOOL param
     *
     * @return int
     */
    public function getPayTool()
    {
        return $this->payTool;
    }

    /**
     * Set REQ_TYPE param
     *
     * @param int $type
     * @return \project\Libs\PaymentGate\Cyberplat
     */
    public function setReqType($type)
    {
        $this->reqType = $type;
        return $this;
    }

    /**
     * Get REQ_TYPE param
     *
     * @return int
     */
    public function getReqType()
    {
        return $this->reqType;
    }

    /**
     * Set NO_ROUTE param
     *
     * @param int $noRoute
     * @return \project\Libs\PaymentGate\Cyberplat
     */
    public function setNoRoute($noRoute)
    {
        $this->noRoute = $noRoute;
        return $this;
    }

    /**
     * Get NO_ROUTE param
     *
     * @return int
     */
    public function getNoRoute()
    {
        return $this->noRoute;
    }
    //</editor-fold>
}