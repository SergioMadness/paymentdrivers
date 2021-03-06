<?php

namespace professionalweb\paymentdrivers\a3\xforms;

use pwf\helpers\ArrayHelper;
use pwf\helpers\ConvertHelper;

class XFormsParser
{
    /**
     * XForms namespace (xforms:)
     */
    const NAMESPACE_XFORMS = 'http://www.w3.org/2002/xforms';

    /**
     * XMLSchema namespace (xsd:)
     */
    const NAMESPACE_SCHEMA = 'http://www.w3.org/2001/XMLSchema';

    //<editor-fold desc="Variables" defaultstate="collapsed">
    /**
     * XForms xml
     *
     * @var string
     */
    private $xmlData;

    /**
     * XML object
     *
     * @var \SimpleXMLElement
     */
    private $xmlDocument;

    /**
     * XForm instance
     *
     * @var array
     */
    private $instance;

    /**
     * Schema cache
     *
     * @var array
     */
    private $schema = null;

    /**
     * Submission cache
     *
     * @var array
     */
    private $submission = null;

    /**
     * Inputs cache
     *
     * @var array
     */
    private $inputs = null;

    /**
     * Binds cache
     *
     * @var mixed
     */
    private $binds = null;

    /**
     * Buttons cache
     *
     * @var array
     */
    private $buttons = null;

    /**
     * Buttons cache
     *
     * @var array
     */
    private $errors = null;

    //</editor-fold>
    public function __construct($xml)
    {
        $this->setXMLData($xml);
    }

    /**
     * Get schema
     *
     * @return array
     */
    public function getSchema()
    {
        if ($this->schema === null) {
            $this->schema = [];
            foreach ($this->getXMLDocument()->xpath('//xsd:schema') as $input) {
                foreach ($input->children(self::NAMESPACE_SCHEMA) as $element) {
                    $name = (string) $element->attributes()->name;
                    if (isset($element->restriction)) {
                        $this->schema[$name] = [];
                        $restriction         = $element->restriction;
                        foreach ($restriction->children(self::NAMESPACE_SCHEMA) as $restrictionElName => $restrictionEl) {
                            $this->schema[$name][$restrictionElName] = (string) $restrictionEl->attributes()->value;
                        }
                    }
                }
            }
        }

        return $this->schema;
    }

    /**
     * Get "submission" block
     *
     * @return array
     */
    public function getSubmission()
    {
        if ($this->submission === null) {
            $this->submission = [];
            foreach ($this->getXMLDocument()->xpath('//xforms:submission') as $processor) {
                $attributes            = $processor->attributes();
                $id                    = (string) $attributes->id;
                $this->submission[$id] = [];
                foreach ($attributes as $attrName => $attrValue) {
                    $this->submission[$id][$attrName] = (string) $attrValue;
                }
            }
        }
        return $this->submission;
    }

    /**
     * Get inputs
     *
     * @return array
     */
    public function getInputs()
    {
        if ($this->inputs === null) {
            $this->inputs = [];
            $schema       = $this->getSchema();
            foreach ($this->getXMLDocument()->xpath('//xforms:input') as $input) {
                $fieldId                        = (string) $input->attributes()->id;
                $code                           = (string) $input->attributes()->ref;
                $this->inputs[$fieldId]         = [];
                $this->inputs[$fieldId]['id']   = $fieldId;
                $this->inputs[$fieldId]['code'] = $code;
                foreach ($input->children(self::NAMESPACE_XFORMS) as $name => $info) {
                    $this->inputs[$fieldId][$name] = (string) $info;
                }
                if (isset($schema[$fieldId])) {
                    $this->inputs[$fieldId] = array_merge($this->inputs[$fieldId],
                        $schema[$fieldId]);
                }
            }
        }
        return $this->inputs;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        if ($this->errors === null) {
            $this->errors = [];
            foreach ($this->getXMLDocument()->xpath('//xhtml:p[contains(@class, ERROR)]') as $error) {
                $ref = (string) $error->attributes()->ref;

                $this->errors[$ref] = (string) $error;
            }
        }

        return $this->errors;
    }

    /**
     * Get "bind" block
     *
     * @return array
     */
    public function getBind()
    {
        if ($this->binds === null) {
            $this->binds = [];
            foreach ($this->getXMLDocument()->xpath('//xforms:bind') as $bind) {
                $ref = (string) $bind->attributes()->nodeset;

                $this->binds[$ref] = [
                    'nodeset' => $ref,
                    'required' => (string) $bind->attributes()->required == 'true',
                    'readonly' => (string) $bind->attributes()->readonly == 'true',
                ];
            }
        }
        return $this->binds;
    }

    /**
     * Get buttons info
     *
     * @return array
     */
    public function getButtons()
    {
        if ($this->buttons === null) {
            $this->buttons = [];
            foreach ($this->getXMLDocument()->xpath('//xforms:submit') as $button) {
                $id                 = (string) $button->attributes()->id;
                $processor          = (string) $button->attributes()->submission;
                $this->buttons[$id] = [
                    'id' => $id,
                    'submission' => $processor
                ];
                foreach ($button->children(self::NAMESPACE_XFORMS) as $name => $info) {
                    $this->buttons[$id][$name] = (string) $info;
                }
            }
        }

        return $this->buttons;
    }

    /**
     * Get XForms instance
     *
     * @param string $namespace
     * @return array
     */
    public function getInstance($namespace = self::NAMESPACE_XFORMS)
    {
        if ($this->instance === null) {
            $this->instance = [];
            if (($instance       = $this->getXMLDocument()->xpath('//xforms:instance'))
                && count($instance) > 0) {
                foreach ($instance[0]->children($namespace) as $name => $data) {
                    $this->instance[$name] = [];
                    foreach ($data->children() as $fieldName => $fieldValue) {
                        $this->instance[$name][$fieldName] = (string) $fieldValue;
                    }
                }
            }
        }
        return $this->instance;
    }

    /**
     * Get instance as XML
     *
     * @return string
     */
    public function instanceAsXml()
    {
        $instance = $this->getInstance();

        $xml = ConvertHelper::array2xml($instance);

        return $xml->saveXML();
    }

    /**
     * Get XML object
     *
     * @return \SimpleXMLElement
     */
    protected function getXMLDocument()
    {
        if ($this->xmlDocument === null) {
            $this->xmlDocument = simplexml_load_string($this->getXmlData());
        }
        return $this->xmlDocument;
    }

    /**
     * Set instance value
     *
     * @param string $fieldId
     * @param mixed $value
     * @return XFormsParser
     */
    public function setInstanceValue($fieldId, $value)
    {
        $instance       = $this->getInstance();
        ArrayHelper::recursivelySetValue($fieldId, $value, $instance);
        $this->instance = $instance;

        return $this;
    }

    /**
     * Get instance value
     *
     * @param string $fieldId
     * @return mixed
     */
    public function getInstanceValue($fieldId)
    {
        $instance = $this->getInstance();
        return ArrayHelper::recursivelyGetValue($fieldId, $instance);
    }
    //<editor-fold desc="Getters and setters" defaultstate="collapsed">

    /**
     * Set XML data
     *
     * @param string $xml
     * @return XFormsParser
     */
    public function setXMLData($xml)
    {
        $this->clear();
        $this->xmlData = $xml;
        return $this;
    }

    /**
     * Fields to defaults
     */
    public function clear()
    {
        $this->binds       = null;
        $this->buttons     = null;
        $this->inputs      = null;
        $this->instance    = null;
        $this->schema      = null;
        $this->submission  = null;
        $this->xmlData     = null;
        $this->xmlDocument = null;
        return $this;
    }

    /**
     * Get XML data
     *
     * @return string
     */
    public function getXmlData()
    {
        return $this->xmlData === null ? '<root/>' : $this->xmlData;
    }
    //</editor-fold>
}