<?php
define('RESPONSE_PATH', 'tests/_data/cyberplat_response');

use Codeception\Util\Stub;

function ipriv_sign($message, $secretKey, $secretPassword)
{
    return 'signed message';
}

class CyberplatTest extends \PHPUnit_Framework_TestCase
{
    const SECRETKEY      = '/path/to/secret.key';
    const SECRETPASSWORD = 'password';
    const PUBLICKEY      = '/path/to/public.key';
    const ACCEPTEDKEYS   = 'accepted keys';
    const SD             = '3883883';
    const AP             = '12321321';
    const OP             = '12321321';
    const TERMID         = '884884';
    const SERIAL         = 'serial number';
    const PAYTOOL        = 'tool';
    const REQTYPE        = 'type';
    const NOROUTE        = 0;

    private $mock;

    protected function setUp()
    {
        $this->mock = Stub::construct('\professionalweb\paymentdrivers\cyberplat\Cyberplat',
                array(),
                array(
                'getResponse' => function($inputMessage) {
                    return file_get_contents(RESPONSE_PATH);
                }
        ));
    }

    protected function tearDown()
    {
        $this->mock = null;
    }

    // Gettters and setters
    public function testGettersAndSetters()
    {
        $this->assertEquals(self::SECRETKEY,
            $this->mock->setSecretKey(self::SECRETKEY)->getSecretKey());

        $this->assertEquals(self::SECRETPASSWORD,
            $this->mock->setSecretKeyPassword(self::SECRETPASSWORD)->getSecretKeyPassword());

        $this->assertEquals(self::PUBLICKEY,
            $this->mock->setPublicKey(self::PUBLICKEY)->getPublicKey());

        $this->assertEquals(self::ACCEPTEDKEYS,
            $this->mock->setAcceptedKeys(self::ACCEPTEDKEYS)->getAcceptedKeys());

        $this->assertEquals(self::SD, $this->mock->setSD(self::SD)->getSD());
        $this->assertEquals(self::AP, $this->mock->setAP(self::AP)->getAP());
        $this->assertEquals(self::OP, $this->mock->setOP(self::OP)->getOP());
        $this->assertEquals(self::TERMID,
            $this->mock->setTermId(self::TERMID)->getTermId());
        $this->assertEquals(self::SERIAL,
            $this->mock->setSerial(self::SERIAL)->getSerial());
        $this->assertEquals(self::PAYTOOL,
            $this->mock->setPayTool(self::PAYTOOL)->getPayTool());
        $this->assertEquals(self::REQTYPE,
            $this->mock->setReqType(self::REQTYPE)->getReqType());
        $this->assertEquals(self::NOROUTE,
            $this->mock->setNoRoute(self::NOROUTE)->getNoRoute());
    }

    // sendMessage test
    public function testSendMessage()
    {
        $this->assertArraySubset(array(
            'ERROR' => 0
            ), $this->mock->sendMessage());
    }

    // Test other methods
    public function testWrapMethods()
    {
        $this->assertArraySubset(array(
            'ERROR' => 0
            ), $this->mock->validate());
        $this->assertArraySubset(array(
            'ERROR' => 0
            ), $this->mock->checkStatus());
        $this->assertArraySubset(array(
            'ERROR' => 0
            ), $this->mock->pay());
    }
}