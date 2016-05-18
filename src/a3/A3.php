<?php

namespace professionalweb\paymentdrivers\a3;

class A3 extends \professionalweb\paymentdrivers\abstraction\PaymentSystem
{
    //
    //<editor-fold desc="Constants" defaultstate="collapsed">
    /**
     * Client service
     */
    const URL_CLIENT_SERVICE = '/client/clientService';

    /**
     * Dictionary service
     */
    const URL_DICTIONARY_SERVICE = '/dictionary/dictionaryService';

    /**
     * General service
     */
    const URL_GENERAL_SERVICE = '/general/generalService';

    /**
     * Invoice service
     */
    const URL_INVOICE_SERVICE = '/invoice/invoiceService';

    /**
     * Payment service
     */
    const URL_PAYMENT_SERVICE = '/payment/paymentService';

    //</editor-fold>
    //
    //<editor-fold desc="Variables" defaultstate="collapsed">
    /**
     * Soap clients
     *
     * @var array
     */
    private $soapClients = [];

    /**
     * Path to certificate
     *
     * @var string
     */
    private $certPath;

    /**
     * Password for certificate
     *
     * @var string
     */
    private $certPass;

    /**
     * Path to SSL certificate
     *
     * @var string
     */
    private $sslCertPath;

    /**
     * Password
     *
     * @var string
     */
    private $sslCertPassword;

    /**
     * Service name
     *
     * @var string
     */
    private $serviceName;

    /**
     * Callable method
     *
     * @var string
     */
    private $method;

    //</editor-fold>

    /**
     * Send message to payment system
     *
     * @param array $params
     * @return \stdClass
     */
    public function sendMessage(array $params = [])
    {
        return $this->getSoapClient($this->getServiceName())->{$this->getMethod()}($params);
    }

    /**
     * Translate call to WebService
     *
     * @param string $name
     * @param array $arguments
     * @return \stdClass
     */
    public function __call($name, $arguments)
    {
        return $this->setMethod($name)->sendMessage($arguments);
    }
    //
    //<editor-fold desc="Getters and setters" defaultstate="collapsed">

    /**
     * Set callable service
     * 
     * @param string $name
     * @return \professionalweb\paymentdrivers\a3\A3
     */
    public function setServiceName($name)
    {
        $this->serviceName = $name;
        return $this;
    }

    /**
     * Get service name
     *
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * Set soap client
     *
     * @param mixed $type
     * @param \SoapClient $client
     * @return A3
     */
    public function setSoapClient($type, \SoapClient $client)
    {
        $this->soapClients[$type] = $client;
        return $this;
    }

    /**
     * Get soap client
     *
     * @param mixed $type
     * @return \SoapClient
     */
    public function getSoapClient($type)
    {
        if (!isset($this->soapClients[$type])) {
            $this->setSoapClient($type,
                new soap\SignedSoapClient($this->getUrl().$type.'?wsdl',
                [
                'local_cert' => getcwd().$this->getSSLCetificatePath(),
                'passphrase' => $this->getSSLCetificatePassword(),
                'connection_timeout' => 20,
                'location' => $this->getUrl().$type,
                'ssl' => array(
                    'cert' => getcwd().$this->getCertificatePath(),
                    'certpasswd' => $this->getCertificatePassword()
                )
                ])
            );
        }
        return $this->soapClients[$type];
    }

    /**
     * Set path to certificate
     *
     * @param string $path
     * @return A3
     */
    public function setCertificatePath($path)
    {
        $this->certPath = $path;
        return $this;
    }

    /**
     * Get path to certificate
     *
     * @return string
     */
    public function getCertificatePath()
    {
        return $this->certPath;
    }

    /**
     * Set password for certificate
     *
     * @param string $pass
     * @return A3
     */
    public function setCertificatePassword($pass)
    {
        $this->certPass = $pass;
        return $this;
    }

    /**
     * Get password for certificate
     *
     * @return string
     */
    public function getCertificatePassword()
    {
        return $this->certPass;
    }

    /**
     * Set path to ssl certificate
     *
     * @param string $path
     * @return A3
     */
    public function setSSLCertificatePath($path)
    {
        $this->sslCertPath = $path;
        return $this;
    }

    /**
     * Get path to SSL certificate
     *
     * @return string
     */
    public function getSSLCertificatePath()
    {
        return $this->sslCertPath;
    }

    /**
     * Set password for SSL certificate
     *
     * @param string $pass
     * @return A3
     */
    public function setSSLCertificatePassword($pass)
    {
        $this->sslCertPassword = $pass;
        return $this;
    }

    /**
     * Get password for SSL certificate
     *
     * @return string
     */
    public function getSSLCertificatePassword()
    {
        return $this->sslCertPassword;
    }

    /**
     * Set method name
     *
     * @param string $name
     * @return \professionalweb\paymentdrivers\a3\A3
     */
    public function setMethod($name)
    {
        $this->method = $name;
        return $this;
    }

    /**
     * Get method name
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
    //</editor-fold>
}