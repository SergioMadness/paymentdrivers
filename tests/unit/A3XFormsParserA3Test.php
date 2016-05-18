<?php

class A3XFormsParserA3Test extends \PHPUnit_Framework_TestCase
{
    const TRANSACTION_ID = 1970322;
    const XFORMS_PATH    = 'tests/_data/xforms.xml';

    private $mock;

    protected function setUp()
    {
        $this->mock = Codeception\Util\Stub::construct('\professionalweb\paymentdrivers\a3\xforms\XFormsParserA3',
                array(
                'xml' => ''
        ));
    }

    protected function tearDown()
    {
        $this->mock = null;
    }

    // Test getters and setters
    public function testIt()
    {
        $xform = file_get_contents(__DIR__.'/../../'.self::XFORMS_PATH);
        $this->fail($xform);
        $this->mock->setXMLData($xform);
        $this->assertEquals($xform, $this->mock->getXMLData());

        $this->assertNotEmpty($this->mock->getSchema());
        $this->assertNotEmpty($this->mock->getSubmission());
        $this->assertNotEmpty($this->mock->getInputs());
        $this->assertNotEmpty($this->mock->getErrors());
        $this->assertNotEmpty($this->mock->getBind());
        $this->assertNotEmpty($this->mock->getButtons());
        $this->assertNotEmpty($this->mock->getInstance());
        $this->assertNotEmpty($this->mock->instanceAsXml());

        $this->assertEquals(self::TRANSACTION_ID,
            $this->mock->getTransactionId());

        $this->assertEquals(12,
            $this->mock->setInstanceValue('a3_PERSONAL_ACCOUNT1_1', 12)->getInstanceValue('a3_PERSONAL_ACCOUNT1_1'));

        $this->assertEquals('<root/>', $this->mock->clear()->getXMLData());
    }
}