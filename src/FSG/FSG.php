<?php

namespace professionalweb\paymentdrivers\FSG;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class FSG extends \professionalweb\paymentdrivers\abstraction\PaymentSystem
{
    //<editor-fold desc="Variables" defaultstate="collapsed">
    /**
     * Terminal name
     *
     * @var string
     */
    private $terminalName;

    /**
     * Path to certificate
     *
     * @var string
     */
    private $certificatePath;

    /**
     * FSG string
     *
     * @var string
     */
    private $host;

    /**
     * FSG port
     *
     * @var int
     */
    private $port;

    /**
     * Is connection secured
     *
     * @var bool
     */
    private $isTLS = true;

    /**
     * XML message
     *
     * @var string
     */
    private $message;

    //</editor-fold>
    //
    //<editor-fold desc="Constants" defaultstate="collapsed">
    /**
     * Cashless payment type
     */
    const PAYTYPE_CASHLESS = 'CASHLESS';

    /**
     * Payment by cash
     */
    const PAYTYPE_CASH = 'CASH';

    /**
     * Agent info request
     */
    const MESSAGE_ReqPPPInfo = '
<Document>
<dsig:Object xmlns="" xmlns:dsig="http://www.w3.org/2000/01/xmldsig">
        <GIN2>
            <INFO>
                <ReqPPPInfo Id="container">
                </ReqPPPInfo>
            </INFO>
            <META-INF>
                <ENTRY>
                    <name>PI</name>
                    <value>GIN_WEB</value>
                </ENTRY>
                <ENTRY>
                    <name>TERMINAL</name>
                    <value>{TERMINAL_NAME}</value>
                </ENTRY>
            </META-INF>
        </GIN2>
    </dsig:Object></Document>';

    /**
     * Form request
     */
    const MESSAGE_ReqForm = '<Document>
<dsig:Object xmlns="" xmlns:dsig="http://www.w3.org/2000/01/xmldsig">
	<GIN2>
		<FORMS>
			<ReqForm Id="container">
			</ReqForm>
		</FORMS>
		<META-INF>
			<ENTRY>
				<name>PI</name>
				<value>GIN_WEB</value>
			</ENTRY>
			<ENTRY>
				<name>TERMINAL</name>
				<value>{TERMINAL_NAME}</value>
			</ENTRY>
		</META-INF>
	</GIN2>
    </dsig:Object></Document>';

    /**
     * Form event
     */
    const MESSAGE_ReqFormEvent = '
<Document>
<dsig:Object xmlns="" xmlns:dsig="http://www.w3.org/2000/01/xmldsig">
	<GIN2>
		<FORMS>
			<ReqFormEvent Id="container">

			</ReqFormEvent>
		</FORMS>
		<META-INF>
			<ENTRY>
				<name>PI</name>
				<value>GIN_WEB</value>
			</ENTRY>
			<ENTRY>
				<name>TERMINAL</name>
				<value>{TERMINAL_NAME}</value>
			</ENTRY>
		</META-INF>
	</GIN2>
        </dsig:Object>
</Document>
';

    /**
     * Create order
     */
    const MESSAGE_ReqCreateOrders = '
<Document>
<dsig:Object xmlns="" xmlns:dsig="http://www.w3.org/2000/01/xmldsig">
	<GIN2>
		<PAY>
			<ReqCreateOrders Id="container">
				
			</ReqCreateOrders>
		</PAY>
		<META-INF>
			<ENTRY>
				<name>PI</name>
				<value>GIN_WEB</value>
			</ENTRY>
			<ENTRY>
				<name>TERMINAL</name>
				<value>{TERMINAL_NAME}</value>
			</ENTRY>
		</META-INF>
	</GIN2>
        </dsig:Object>
</Document>
';
    const MESSAGE_ReqNoticeOrders = '
<Document>
<dsig:Object xmlns="" xmlns:dsig="http://www.w3.org/2000/01/xmldsig">
<GIN2>
	<PAY>
		<ReqNoticeOrders Id="container">
			
		</ReqNoticeOrders>
	</PAY>
	<META-INF>
		<ENTRY>
			<name>PI</name>
			<value>GIN_WEB</value>
		</ENTRY>
		<ENTRY>
			<name>TERMINAL</name>
			<value>{TERMINAL_NAME}</value>
		</ENTRY>
	</META-INF>
</GIN2>
</dsig:Object>
</Document>
';

    //</editor-fold>

    /**
     * Send message and get response
     *
     * @param array $params
     * @return string
     */
    public function sendMessage(array $params = array())
    {
        return $this->unzipResponse(
                $this->sendRequest(
                    $this->zipRequest(
                        $this->signMessage(
                            $this->prepareMessage($this->getMessage(),
                                array_merge($this->getParams(), $params))
                        )
                    )
                )
        );
    }

    /**
     * Prepare message
     *
     * @param string $xmlMessage
     * @param array $params
     * @return string
     */
    protected function prepareMessage($xmlMessage, array $params = array())
    {
        $xmlMessage       = str_replace('{TERMINAL_NAME}',
            $this->getTerminalName(), $xmlMessage);
        $doc              = simplexml_load_string($xmlMessage);
        $containerElement = $doc->xpath('//*[@Id="container"]');
        if (count($containerElement) > 0) {
            $this->insertNode($containerElement[0], $params);
        }
        return str_replace(' Id="container"', '', $doc->asXML());
    }

    /**
     * Insert data as XML nodes to XML element
     *
     * @param \SimpleXMLElement $container
     * @param array $data
     */
    protected function insertNode(\SimpleXMLElement $container, array $data)
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $this->insertNode($container->addChild($key), $val);
            } else {
                $container->addChild($key, $val);
            }
        }
    }

    /**
     * Send request through socket
     *
     * @param string $data
     * @return string
     */
    protected function sendRequest($data)
    {
        $result = '';

        $context = stream_context_create(
            $this->isTLS() ? array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'ciphers' => 'aGOST',
                    'disable_compression' => false,
                ),
                ) : array()
        );

        $port   = $this->getPort();
        $socket = stream_socket_client($this->getHost().($port > 0 ? ':'.$port : ''),
            $errno, $errstr, 60, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            die("Не могу соединиться: $errstr ($errno)");
        }

        if ($socket) {
            $cnt = pack("N", strlen($data));
            fwrite($socket, $cnt.$data);

            $cnt = fread($socket, 4);
            $cnt = unpack("N", $cnt);
            $cnt = $cnt[1];
            if ($cnt < 1024) {
                $result = fread($socket, $cnt);
            } else {
                $ansCnt = 0;
                do {
                    $buf = fread($socket, 1024);
                    $result .= $buf;
                    $ansCnt += strlen($buf);
                } while ($buf != '' || $ansCnt < $cnt);
            }
            fclose($socket);
        }
        return $result;
    }

    /**
     * Unzip response
     *
     * @param string $response
     * @return string
     */
    protected function unzipResponse($response)
    {
        $result = '';
        $name   = time().'zip';
        file_put_contents($name, $response);
        $zip    = new \ZipArchive;
        if (($res    = $zip->open($name)) === true) {
            $result = $zip->getFromName('response');
            $zip->close();
        }
        unlink($name);
        return $result;
    }

    /**
     * Add signature to xml
     *
     * @param string $xml
     * @return string
     */
    protected function signMessage($xml)
    {
        $doc    = new \DOMDocument('1.0', 'UTF-8');
        $result = $xml;
        if ($doc->loadXML($xml) === true) {
            $objects = $doc->getElementsByTagName('Object');

            $objDSig = new XMLSecurityDSig('');

            $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);

            $objDSig->addReference(
                $objects[0], XMLSecurityDSig::SHA1,
                array('http://www.w3.org/2000/09/xmldsig#enveloped-signature')
            );

            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256,
                array('type' => 'private'));

            $objKey->loadKey(getcwd().$this->getCertificate(), true);

            $objDSig->sign($objKey);

            $objDSig->add509Cert(file_get_contents(getcwd().$this->getCertificate()));

            $objDSig->appendSignature($doc->documentElement);

            $result = $doc->saveXML();
        }

        return $result;
    }

    /**
     * Compress request
     *
     * @param string $request
     * @return bin
     */
    protected function zipRequest($request)
    {
        $zip  = new \ZipArchive;
        $name = time().'zip';
        $zip->open($name, \ZipArchive::CREATE);

        $zip->addFromString('request', $request);
        $zip->close();
        $result = file_get_contents($name);
        unlink($name);
        return $result;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return FSG
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get current message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Send ReqPPPInfo message
     *
     * @return string
     */
    public function getPPPInfo()
    {
        return $this->setMessage(self::MESSAGE_ReqPPPInfo)
                ->sendMessage();
    }

    /**
     * Send ReqForm message
     *
     * @param string $formId
     * @return string
     */
    public function getFormInfo($formId)
    {
        return $this->setMessage(self::MESSAGE_ReqForm)
                ->setParams(array(
                    'srvNum' => $formId
                ))
                ->sendMessage();
    }

    /**
     * Trigger form event
     *
     * @return string
     */
    public function formEvent($eventName, $form)
    {
        return $this->setMessage(self::MESSAGE_ReqFormEvent)
                ->setParams(array(
                    'event' => $eventName,
                    'sForm' => $form
                ))
                ->sendMessage();
    }

    /**
     * Create orders
     *
     * @param string $sForm
     * @param string $payType
     * @return string
     */
    public function createOrder($sForm, $payType = self::PAYTYPE_CASHLESS)
    {
        return $this->setMessage(self::MESSAGE_ReqCreateOrders)
                ->setParams(array(
                    'ROrders' => array(
                        'ROrderGroup' => array(
                            'ROrder' => array(
                                'payType' => $payType,
                                'sForm' => $sForm
                            )
                        )
                    )
                ))
                ->sendMessage();
    }

    public function completeOrder($orderId, $transactionId)
    {
        return $this->setMessage(self::MESSAGE_ReqNoticeOrders)
                ->setParams(array(
                    'RNoticeGroup' => array(
                        'groupId' => $orderId,
                        'RNotice' => array(
                            'orderId' => $orderId,
                            'PayInfo' => array(
                                'tranNum' => $transactionId
                            )
                        )
                    ))
                )
                ->sendMessage();
    }

    //<editor-fold desc="Getters and setters" defaultstate="collapsed">
    /**
     * Set terminal name
     *
     * @param string $name
     * @return \project\Libs\PaymentGate\FSG
     */
    public function setTerminalName($name)
    {
        $this->terminalName = $name;
        return $this;
    }

    /**
     * Get terminal name
     *
     * @return string
     */
    public function getTerminalName()
    {
        return $this->terminalName;
    }

    /**
     * Set certificate
     *
     * @param string $path
     * @return \project\Libs\PaymentGate\FSG
     */
    public function setCertificate($path)
    {
        $this->certificatePath = $path;
        return $this;
    }

    /**
     * Get certificate
     *
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificatePath;
    }

    /**
     * Set FSG host
     *
     * @param string $host
     * @return \project\Libs\PaymentGate\FSG
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port
     *
     * @param int $port
     * @return \project\Libs\PaymentGate\FSG
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set connection is secured
     *
     * @param bool $flag
     * @return \project\Libs\PaymentGate\FSG
     */
    public function setIsTLS($flag)
    {
        $this->isTLS = $flag;
        return $this;
    }

    /**
     * Check is connection secured
     *
     * @return bool
     */
    public function isTLS()
    {
        return $this->isTLS;
    }
    //</editor-fold>
}