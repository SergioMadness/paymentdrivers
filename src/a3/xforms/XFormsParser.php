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
            $this->schema = array();
            foreach ($this->getXMLDocument()->xpath('//xsd:schema') as $input) {
                foreach ($input->children(self::NAMESPACE_SCHEMA) as $element) {
                    $name = reset($element->attributes()->name);
                    if (isset($element->restriction)) {
                        $this->schema[$name] = array();
                        $restriction         = $element->restriction;
                        foreach ($restriction->children(self::NAMESPACE_SCHEMA) as $restrictionElName => $restrictionEl) {
                            $this->schema[$name][$restrictionElName] = reset($restrictionEl->attributes()->value);
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
            $this->submission = array();
            foreach ($this->getXMLDocument()->xpath('//xforms:submission') as $processor) {
                $attributes            = $processor->attributes();
                $id                    = reset($attributes->id);
                $this->submission[$id] = array();
                foreach ($attributes as $attrName => $attrValue) {
                    $this->submission[$id][$attrName] = reset($attrValue);
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
            $this->inputs = array();
            $schema       = $this->getSchema();
            foreach ($this->getXMLDocument()->xpath('//xforms:input') as $input) {
                $fieldId                        = reset($input->attributes()->id);
                $code                           = reset($input->attributes()->ref);
                $this->inputs[$fieldId]         = array();
                $this->inputs[$fieldId]['id']   = $fieldId;
                $this->inputs[$fieldId]['code'] = $code;
                foreach ($input->children(self::NAMESPACE_XFORMS) as $name => $info) {
                    $this->inputs[$fieldId][$name] = reset($info);
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
            $this->errors = array();
            foreach ($this->getXMLDocument()->xpath('//xhtml:p[contains(@class, ERROR)]') as $error) {
                $ref = reset($error->attributes()->ref);

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
            $this->binds = array();
            foreach ($this->getXMLDocument()->xpath('//xforms:bind') as $bind) {
                $ref = reset($bind->attributes()->nodeset);

                $this->binds[$ref] = array(
                    'nodeset' => $ref,
                    'required' => reset($bind->attributes()->required) == 'true',
                    'readonly' => reset($bind->attributes()->readonly) == 'true',
                );
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
            $this->buttons = array();
            foreach ($this->getXMLDocument()->xpath('//xforms:submit') as $button) {
                $id                 = reset($button->attributes()->id);
                $processor          = reset($button->attributes()->submission);
                $this->buttons[$id] = array(
                    'id' => $id,
                    'submission' => $processor
                );
                foreach ($button->children(self::NAMESPACE_XFORMS) as $name => $info) {
                    $this->buttons[$id][$name] = reset($info);
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
            $this->instance = array();
            if (($instance       = $this->getXMLDocument()->xpath('//xforms:instance'))
                && count($instance) > 0) {
                foreach ($instance[0]->children($namespace) as $name => $data) {
                    $this->instance[$name] = array();
                    foreach ($data->children() as $fieldName => $fieldValue) {
                        $this->instance[$name][$fieldName] = reset($fieldValue);
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