<?php
defined('RESPONSE_MESSAGE') or define('RESPONSE_MESSAGE', 'response');

use Codeception\Util\Stub;

class A3Test extends \PHPUnit_Framework_TestCase
{
    const SERVICENAME            = 'service name';
    const CERTIFICATIONPATH      = '/path/to/certificate';
    const CERTIFICATEPASSWORD    = 'password';
    const SSLCERTIFICATEPATH     = '/path/to/ssl.key';
    const SSLCERTIFICATEPASSWORD = 'password';
    const METHODNAME             = 'method';
    const METHODNAME2            = 'method2';

    private $mock;

    protected function setUp()
    {
        $self       = $this;
        $this->mock = Stub::construct('\professionalweb\paymentdrivers\a3\A3', array(),
                array(
                'sendMessage' => function(array $params = array()) use ($self) {
                return $self->mock->getMethod();
            }
        ));
    }

    protected function tearDown()
    {
        $this->mock = null;
    }

    // Test getters and setters
    public function testGettersAndSetters()
    {
        $this->assertEquals(self::SERVICENAME,
            $this->mock->setServiceName(self::SERVICENAME)->getServiceName());
        $this->assertEquals(self::CERTIFICATIONPATH,
            $this->mock->setCertificatePath(self::CERTIFICATIONPATH)->getCertificatePath());
        $this->assertEquals(self::CERTIFICATEPASSWORD,
            $this->mock->setCertificatePassword(self::CERTIFICATEPASSWORD)->getCertificatePassword());
        $this->assertEquals(self::SSLCERTIFICATEPATH,
            $this->mock->setSSLCertificatePath(self::SSLCERTIFICATEPATH)->getSSLCertificatePath());
        $this->assertEquals(self::SSLCERTIFICATEPASSWORD,
            $this->mock->setSSLCertificatePassword(self::SSLCERTIFICATEPASSWORD)->getSSLCertificatePassword());
        $this->assertEquals(self::METHODNAME,
            $this->mock->setMethod(self::METHODNAME)->getMethod());
        $soap = Stub::makeEmpty('SoapClient');
        $this->assertEquals($soap,
            $this->mock->setSoapClient('test', $soap)->getSoapClient('test'));
    }

    // Test SOAP method call
    public function testSoapMethodCall()
    {
        $this->assertEquals(self::METHODNAME,
            $this->mock->setMethod(self::METHODNAME)->sendMessage());
        $this->assertEquals(self::METHODNAME2,
            $this->mock->{self::METHODNAME2}());
    }
}