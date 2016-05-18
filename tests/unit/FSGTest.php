<?php
define('FSG_RESPONSE', 'tests/_data/fsg_response.zip');
define('FSG_RESPONSE_XML', 'tests/_data/fsg_ans.xml');

use Codeception\Util\Stub;
use professionalweb\paymentdrivers\FSG\FSG;

class FSGTest extends \PHPUnit_Framework_TestCase
{
    const TERMINALNAME    = 'terminal';
    const CERTIFICATEPATH = '/tests/_data/fsg_cert.pem';
    const HOST            = '127.0.0.1';
    const PORT            = 7653;

    private $mock;

    protected function setUp()
    {
        $this->mock = Stub::construct('\professionalweb\paymentdrivers\FSG\FSG',
                array(),
                array(
                'sendRequest' => function ($data) {
                    return file_get_contents(FSG_RESPONSE);
                }
            ))->setCertificate(self::CERTIFICATEPATH);
    }

    protected function tearDown()
    {
        $this->mock = null;
    }

    // Test getters and setters
    public function testGettersAndSetters()
    {
        $this->assertEquals(FSG::MESSAGE_ReqCreateOrders,
            $this->mock->setMessage(FSG::MESSAGE_ReqCreateOrders)->getMessage());
        $this->assertEquals(self::TERMINALNAME,
            $this->mock->setTerminalName(self::TERMINALNAME)->getTerminalName());
        $this->assertEquals(self::CERTIFICATEPATH,
            $this->mock->setCertificate(self::CERTIFICATEPATH)->getCertificate());
        $this->assertEquals(self::HOST,
            $this->mock->setHost(self::HOST)->getHost());
        $this->assertEquals(self::PORT,
            $this->mock->setPort(self::PORT)->getPort());
        $this->assertTrue($this->mock->setIsTLS(true)->isTLS());
    }

    // Test send message
    public function testWrapMethods()
    {
        $response = file_get_contents(FSG_RESPONSE_XML);
        $this->assertEquals($response, $this->mock->getPPPInfo());
        $this->assertEquals($response, $this->mock->getFormInfo(1));
        $this->assertEquals($response,
            $this->mock->formEvent('eventName', 'sForm'));
        $this->assertEquals($response, $this->mock->createOrder('sForm'));
        $this->assertEquals($response, $this->mock->completeOrder('sForm', 1));
    }
}