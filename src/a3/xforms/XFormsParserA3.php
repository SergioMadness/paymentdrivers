<?php

namespace professionalweb\paymentdrivers\a3\xforms;

class XFormsParserA3 extends XFormsParser
{
    /**
     * A3 namespace
     */
    const NAMESPACE_A3 = 'http://www.a-3.ru/xforms/schema';

    /**
     * Field name
     */
    const FIELD_TRANSACTION_ID = 'transactionId';

    /**
     * Get XForms instance
     *
     * @return array
     */
    public function getInstance($namespace = self::NAMESPACE_A3)
    {
        return parent::getInstance($namespace);
    }

    /**
     * Get transaction ID
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->getInstanceValue(self::FIELD_TRANSACTION_ID);
    }
}