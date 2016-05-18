<?php
defined('RESPONSE_MESSAGE') or define('RESPONSE_MESSAGE', 'response');

class PaymentSystemTest extends \PHPUnit_Framework_TestCase
{
    const PAYMENTID  = 'HYROD678';
    const SERVICEID  = 290;
    const AMOUNT     = 2.98;
    const SESSIONSTR = 'dfsgkjh478hjhj';
    const FEE        = 1.02;
    const URL        = 'http://url.to/payment/api';

    private $mock;
    protected $params = array(
        'param1' => 1,
        'param2' => 2
    );

    protected function setUp()
    {
        $this->mock = Codeception\Util\Stub::construct('\professionalweb\paymentdrivers\abstraction\PaymentSystem',
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
        $this->assertEquals($this->params,
            $this->mock->setParams($this->params)->getParams());
        $this->assertEquals(self::AMOUNT,
            $this->mock->setAmount(self::AMOUNT)->getAmount());
        $this->assertEquals(self::URL, $this->mock->setUrl(self::URL)->getUrl());
        $this->assertEquals(self::PAYMENTID,
            $this->mock->setPaymentId(self::PAYMENTID)->getPaymentId());
        $this->assertEquals(self::SERVICEID,
            $this->mock->setServiceId(self::SERVICEID)->getServiceId());
        $this->assertEquals(self::SESSIONSTR,
            $this->mock->setSessionStr(self::SESSIONSTR)->getSessionStr());
        $this->assertEquals(self::FEE, $this->mock->setFee(self::FEE)->getFee());
    }
}