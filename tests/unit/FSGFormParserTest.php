<?php
define('SFORMPATH', 'tests/_data/fsg_form.xml');

class FSGFormParserTest extends \PHPUnit_Framework_TestCase
{
    private $mock;

    protected function setUp()
    {
        $this->mock = Codeception\Util\Stub::construct('\professionalweb\paymentdrivers\FSG\FormParser',
                array(
                'xml' => ''
        ));
    }

    protected function tearDown()
    {
        $this->mock = null;
    }

    // Test all
    public function testIt()
    {
        $sForm = file_get_contents(SFORMPATH);
        $this->mock->setSForm($sForm);

        $this->assertEquals($sForm, $this->mock->getSForm());

        $this->assertFalse($this->mock->hasAbonentFields());
        $this->assertNotEmpty($this->mock->getFields());
        $this->assertEmpty($this->mock->getInitEventName());
        $this->assertEquals('validate', $this->mock->getValidateEventName());
        $this->mock->setFieldsValues(array(
                'MTSID' => 1
            ))
            ->setFieldAttribute('MTSID', 'test', 'value')
            ->clear()
            ->translateDataTypes($sForm);
    }
}