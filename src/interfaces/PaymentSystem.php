<?php

namespace professionalweb\paymentdrivers\interfaces;

/**
 * Driver interface
 */
interface PaymentSystem
{

    /**
     * Send message to payment system
     *
     * @param array $params
     */
    public function sendMessage(array $params = array());
}