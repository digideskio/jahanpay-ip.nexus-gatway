<?php

class gateway_jahanpay extends gatewayCore
{
	public function __construct( ipsRegistry $registry ) {
		parent::__construct($registry);
		require_once( IPSLib::getAppDir('nexus') .'/sources/gateways/libs/nusoap.php' );
	}
    
  	 public $maxAmounts = array(
        'USD'    => '*',
		'IRR'    => '*'
	);
	
	public function payScreen() {

		if(empty($this->method['m_settings']['api'])) {
			return false;
		}

		$client = new nusoap_client("http://www.jahanpay.com/webservice?wsdl",true);
	    $api = $this->method['m_settings']['api'];
	    $amount = $this->transaction['t_amount'] / 10; //Tooman
	    $callbackUrl = ($this->settings['base_url_https']?$this->settings['base_url_https']:$this->settings['base_url']) . "app=nexus&module=payments&section=receive&validate=jahanpay&nexusinvoice={$this->invoice->__get('id')}&transid={$this->transaction['t_id']}";
	    $orderId = $this->transaction['t_id'];
	    $txt = urlencode($this->item['package_title']);
	    $res = $client->call('requestpayment',array($api , $amount , $callbackUrl , $orderId , $txt));

		return ( is_numeric($res) && $res < 0) ? false : array(
            "formUrl"	=> "http://www.jahanpay.com/pay_invoice/{$res}"
		);
	}

	public function validatePayment()
	{
		$api = $this->method['m_settings']['api'];
		$amount = $this->transaction['t_amount'] / 10; //Tooman
		$client = new nusoap_client("http://www.jahanpay.com/webservice?wsdl",true);
		$result = intval($client->call('verification',array($api,$amount,$_GET["au"])));
		if( $result === 1) {
			return array( 'status' => "okay", 'amount' => $this->transaction['t_amount'], 'gw_id' => $this->request["au"]);
		}
		return false;
	}
}