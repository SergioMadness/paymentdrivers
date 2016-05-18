<?php
defined('RESPONSE_MESSAGE') or define('RESPONSE_MESSAGE', 'response');

class RapidaTest extends \PHPUnit_Framework_TestCase
{
    const CAPATH         = '/path/to/ca';
    const SSLCERTPATH    = '/path/to/ssl/cert';
    const SSLKEYPATH     = '/path/to/ssl.key';
    const SSLKEYPASSWORD = 'password';
    const TERMTYPE       = 'type';
    const TERMID         = '12345';

    private $mock;

    protected function setUp()
    {
        $this->mock = Codeception\Util\Stub::construct('\professionalweb\paymentdrivers\rapida\Rapida',
                array(),
                array(
                'sendMessage' => function(array $params = array()) {
                return RESPONSE_MESSAGE;
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
        $this->assertEquals(self::CAPATH,
            $this->mock->setCAPath(self::CAPATH)->getCAPath());
        $this->assertEquals(self::SSLCERTPATH,
            $this->mock->setSSLCertPath(self::SSLCERTPATH)->getSSLCertPath());
        $this->assertEquals(self::SSLKEYPATH,
            $this->mock->setSSLKeyPath(self::SSLKEYPATH)->getSSLKeyPath());
        $this->assertEquals(self::SSLKEYPASSWORD,
            $this->mock->setSSLKeyPassword(self::SSLKEYPASSWORD)->getSSLKeyPassword());
        $this->assertEquals(self::TERMTYPE,
            $this->mock->setTermType(self::TERMTYPE)->getTermType());
        $this->assertEquals(self::TERMID,
            $this->mock->setTermId(self::TERMID)->getTermId());
    }

    // Test for mthods - wrappers
    public function testWrapMethods()
    {
        $this->assertEquals(RESPONSE_MESSAGE, $this->mock->validate());
        $this->assertEquals(RESPONSE_MESSAGE, $this->mock->checkStatus());
        $this->assertEquals(RESPONSE_MESSAGE, $this->mock->pay());
    }

    // Test helper methods
    public function testHelpers()
    {
        $fieldId      = 7757;
        $errorMessage = 'Error in '.$fieldId.' field';
        $this->assertEquals($fieldId, $this->mock->parseFieldId($errorMessage));
    }
}