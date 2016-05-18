<?php

namespace professionalweb\paymentdrivers\FSG;

class FormParser
{
    const EVENT_INIT     = 'onInitEvent';
    const EVENT_VALIDATE = 'onValidateEvent';

    protected $availableTags = array(
        'Form' => array('version'),
        'Service' => array('num'),
        'Abonent' => array('*'),
        'Fields' => array(),
        'Field' => array('name', 'value', 'dataType')
    );

    /**
     * Form xml
     *
     * @var string
     */
    private $sForm;

    /**
     * XML
     *
     * @var \SimpleXMLElement
     */
    private $xmlSForm;

    public function __construct($xml)
    {
        $this->setSForm($xml);
    }

    /**
     * Check form have fields with "abonent" attribute
     *
     * @return bool
     */
    public function hasAbonentFields()
    {
        $result = false;

        if (($xml = $this->getSFormXml()) !== false) {
            $elements = $xml->xpath('//*[@abonent]');
            $result   = is_array($elements) && count($elements) > 0;
        }

        return $result;
    }

    /**
     * Get array of fields
     *
     * @param int $visible
     * @return array
     */
    public function getFields($visible = -1)
    {
        $result = array();
        if (($xml    = $this->getSFormXml()) !== false) {
            $xPathQuery = '//Field | //Column';
            if ($visible !== -1) {
                $xPathQuery = '//Field'.($visible ? '[@abonent=1]' : '[@abonent=0] | //Field[not(@abonent)]').' | //Column'.($visible
                            ? '[not(@visible)]' : '[@visible=0]');
            }
            $result = $xml->xpath($xPathQuery);
        }
        return $result;
    }

    /**
     * Set fields values to form
     *
     * @param array $fieldsAndValues
     * @return FormParser
     */
    public function setFieldsValues(array $fieldsAndValues)
    {
        if (($xml = $this->getSFormXml()) !== false) {
            foreach ($fieldsAndValues as $field => $value) {
                $elements = $xml->xpath('//*[@name="'.$field.'"]');
                if (is_array($elements) && count($elements) > 0) {
                    foreach ($elements as $el) {
                        if (!isset($el->attributes()->value)) {
                            $el->addAttribute('value', $value);
                        } else {
                            $el->attributes()->value = $value;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get attribute
     *
     * @param \SimpleXMLElement $node
     * @param string $eventName
     * @return string
     */
    protected function getAttribute($node, $eventName)
    {
        $result     = '';
        $attributes = $node->attributes();
        if (isset($attributes->$eventName)) {
            $result = reset($attributes->$eventName);
        }
        return $result;
    }

    /**
     * Get event name by attribute name
     *
     * @param string $eventName
     * @return string
     */
    protected function getFieldsEventName($eventName)
    {
        $result = '';
        if (($xml    = $this->getSFormXml()) !== false) {
            $result = $this->getAttribute($xml->Fields, $eventName);
        }
        return $result;
    }

    /**
     * Get init event name
     *
     * @return string
     */
    public function getInitEventName()
    {
        return $this->getFieldsEventName(self::EVENT_INIT);
    }

    /**
     * Get validate event name
     *
     * @return string
     */
    public function getValidateEventName()
    {
        return $this->getFieldsEventName(self::EVENT_VALIDATE);
    }

    /**
     * Get form as XML object
     *
     * @return \SimpleXMLElement
     */
    public function getSFormXml()
    {
        if ($this->xmlSForm === null) {
            $this->xmlSForm = simplexml_load_string($this->getSForm());
        }
        return $this->xmlSForm;
    }

    /**
     * Set form xml
     *
     * @param string $xml
     * @return FormParser
     */
    public function setSForm($xml)
    {
        $this->sForm = $xml;
        return $this;
    }

    /**
     * Get form xml
     *
     * @return string
     */
    public function getSForm()
    {
        return $this->sForm;
    }

    /**
     * Clear tags
     *
     * @return \professionalweb\paymentdrivers\FSG\FormParser
     */
    public function clear()
    {
        $tags          = $this->getSFormXml()->xpath('//*');
        $availableTags = array_keys($this->availableTags);
        foreach ($tags as $index => $tag) {
            $name = $tag->getName();
            if (in_array($name, $availableTags)) {
                if (count($this->availableTags[$name]) > 0 && $this->availableTags[$name][0]
                    != '*') {
                    $attributes       = $tag->attributes();
                    $clonedAttributes = clone $attributes;
                    foreach ($clonedAttributes as $attributeName => $attributeValue) {
                        if (!in_array($attributeName,
                                $this->availableTags[$name])) {
                            unset($attributes[$attributeName]);
                        }
                    }
                }
            } else {
                $dom = dom_import_simplexml($tag);
                $dom->parentNode->removeChild($dom);
                unset($tags[$index]);
            }
        }
        return $this;
    }

    /**
     * Set fields attribute
     *
     * @param string $fieldName
     * @param string $attributeName
     * @param string $attributeValue
     * @return \professionalweb\paymentdrivers\FSG\FormParser
     */
    public function setFieldAttribute($fieldName, $attributeName,
                                      $attributeValue)
    {
        $fields = $this->getSFormXml()->xpath('//Field[@name="'.$fieldName.'"]');
        foreach ($fields as $field) {
            $attributes = $field->attributes();
            if (!isset($attributes[$attributeName])) {
                $field->addAttribute($attributeName, $attributeValue);
            } else {
                $field[$attributeName] = $attributeValue;
            }
        }
        return $this;
    }

    /**
     * Copy data types from sForm to current sForm
     *
     * @param string $sForm
     * @return \professionalweb\paymentdrivers\FSG\FormParser
     */
    public function translateDataTypes($sForm)
    {
        if (($sFormXml = simplexml_load_string($sForm)) !== false) {
            $fields = $sFormXml->xpath('//Field');
            foreach ($fields as $field) {
                $attributes = $field->attributes();
                $name       = reset($attributes['name']);
                if (isset($attributes['dataType'])) {
                    $this->setFieldAttribute($name, 'dataType',
                        reset($attributes['dataType']));
                }
            }
        }
        return $this;
    }
}