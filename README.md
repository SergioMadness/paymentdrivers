Payment systems' drivers
========================

[http://web-development.pw/](http://web-development.pw/)

[![Latest Stable Version](https://poser.pugx.org/professionalweb/paymentdrivers/v/stable)](https://packagist.org/packages/professionalweb/paymentdrivers)
[![Build Status](https://travis-ci.org/SergioMadness/paymentdrivers.svg?branch=dev)](https://travis-ci.org/SergioMadness/paymentdrivers)
[![Code Climate](https://codeclimate.com/github/SergioMadness/paymentdrivers/badges/gpa.svg)](https://codeclimate.com/github/SergioMadness/paymentdrivers)
[![Coverage Status](https://coveralls.io/repos/github/SergioMadness/paymentdrivers/badge.svg?branch=dev)](https://coveralls.io/github/SergioMadness/paymentdrivers?branch=dev)
[![Dependency Status](https://www.versioneye.com/user/projects/573c5c00ce8d0e004130bd62/badge.svg?style=flat)](https://www.versioneye.com/user/projects/573c5c00ce8d0e004130bd62)
[![License](https://poser.pugx.org/professionalweb/paymentdrivers/license)](https://packagist.org/packages/professionalweb/paymentdrivers)
[![Latest Unstable Version](https://poser.pugx.org/professionalweb/paymentdrivers/v/unstable)](https://packagist.org/packages/professionalweb/paymentdrivers)


Requirements
------------
 - PHP 5.4+

Dependencies
------------
 - [robrichards/xmlseclibs](https://github.com/robrichards/xmlseclibs)
 - [professionalweb/helpers](https://github.com/SergioMadness/pwf-helpers)


Installation
------------
Module is available through [composer](https://getcomposer.org/)

composer require professionalweb/paymentdrivers "dev-master"

Alternatively you can add the following to the `require` section in your `composer.json` manually:

```json
"professionalweb/paymentdrivers": "dev-master"
```
Run `composer update` afterwards.


Payment systems
---------------
 - [Cyberplat](https://www.cyberplat.com/)
 - [Rapida](https://rapida.ru/)
 - [FSG](http://www.kvartplata.ru/Pages/default.aspx)
 - [A3](https://www.a-3.ru/)

Examples
--------
```php
class PaymentDriverFabric
{
    const DRIVER_FSG       = 'fsg';
    const DRIVER_CYBERPLAT = 'cyberplat';
    const DRIVER_RAPIDA    = 'rapida';
    const DRIVER_A3        = 'a3';

    /**
     * Get driver by name
     *
     * @param string $driverName
     * @return \professionalweb\paymentdrivers\interfaces\PaymentSystem
     */
    public static function getDriver($driverName)
    {
        $result = null;
        switch ($driverName) {
            case self::DRIVER_FSG:
                $result = self::getFsgDriver();
                break;
            case self::DRIVER_CYBERPLAT:
                $result = self::getCyberplatDriver();
                break;
            case self::DRIVER_RAPIDA:
                $result = self::getRapidaDriver();
                break;
            case self::DRIVER_A3:
                $result = self::getA3Driver();
                break;
        }
        return $result;
    }

    /**
     * Get FSG payment driver
     *
     * @return \professionalweb\paymentdrivers\interfaces\PaymentSystem
     */
    public static function getFsgDriver()
    {
        $driver = new \professionalweb\paymentdrivers\FSG\FSG;
        return $driver
                ->setCertificate(FDG_CERT_PATH)
                ->setHost(FSG_HOST)
                ->setPort(FSG_PORT)
                ->setTerminalName(FSG_TERMINAL_NAME)
                ->setIsTLS(FSG_IS_TLS);
    }

    /**
     * Get cyberplat payment driver
     *
     * @return \professionalweb\paymentdrivers\interfaces\PaymentSystem
     */
    public static function getCyberplatDriver()
    {
        $driver = new \professionalweb\paymentdrivers\cyberplat\Cyberplat();
        return $driver
                ->setAP(CYBERPLAT_AP)
                ->setOP(CYBERPLAT_OP)
                ->setSD(CYBERPLAT_SD)
                ->setTermId(CYBERPLAT_TERM_ID)
                ->setSerial(CYBERPLAT_SERIAL)
                ->setSecretKey(file_get_contents(CYBERPLAT_PATH_TO_SECRET_KEY))
                ->setPublicKey(file_get_contents(CYBERPLAT_PATH_TO_PUBLIC_KEY))
                ->setSecretKeyPassword(CYBERPLAT_SSL_KEY_PASSWORD)
                ->setNoRoute(CYBERPLAT_NO_ROUTE)
                ->setPayTool(CYBERPLAT_PAY_TOOL)
                ->setAcceptedKeys(CYBERPLAT_ACCEPT_KEYS);
    }

    /**
     * Get rapida payment driver
     *
     * @return \professionalweb\paymentdrivers\interfaces\PaymentSystem
     */
    public static function getRapidaDriver()
    {
        $driver = new \professionalweb\paymentdrivers\rapida\Rapida();
        return $driver
                ->setUrl(RAPIDA_URL)
                ->setCAPath(RAPIDA_PATH_TO_CA)
                ->setSSLCertPath(RAPIDA_PATH_TO_SSL_CERT)
                ->setSSLKeyPath(RAPIDA_PATH_TO_SSL_KEY)
                ->setSSLKeyPassword(RAPIDA_SSL_KEY_PASSWORD)
                ->setTermType(RAPIDA_TERM_TYPE)
                ->setTermId(RAPIDA_TERM_ID);
    }

    /**
     * Get A3 payment driver
     *
     * @return \professionalweb\paymentdrivers\interfaces\PaymentSystem
     */
    public static function getA3Driver()
    {
        $driver = new \professionalweb\paymentdrivers\a3\A3();
        return $driver
                ->setUrl(A3_WSDL_URL)
                ->setSSLCertificatePath(A3_PATH_TO_SSL_CERT)
                ->setSSLCertificatePassword(A3_SSL_CERT_PASSWORD)
                ->setCertificatePath(A3_PATH_TO_SIGN_CERT)
                ->setCertificatePassword(A3_SIGN_CERT_PASSWORD);
    }
}
```



The MIT License (MIT)
---------------------

Copyright (c) 2016 Sergey Zinchenko, [Professional web](http://web-development.pw)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.