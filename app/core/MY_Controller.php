<?php

require_once APPPATH."libraries/Stripe.php";

class MY_Controller extends CI_Controller{
	protected $data = array();

	function __construct(){
		parent::__construct();
	}

	function render($view){
		$this->load->view('embed/header');
		$this->load->view($view);
	}

	function _load_partial($name, $data = null){
		if($data){
			return $this->load->view('partials/'.$name, $data, true);
		}
		else{
			return $this->load->view('partials/'.$name, null, true);
		}
	}

	function _send_mail($to, $from, $subject, $message){
		$this->email->to($to);
		$this->email->from($from);
		$this->email->subject($subject);
		$this->email->message($message);
		$this->email->send();
	}
}

class Ajax extends MY_Controller{
	function __construct(){
		parent::__construct();
		if(!$this->input->is_ajax_request()){
			redirect(site_url());
		}		
	}

	function _render_json($data){
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($data));
	}

	function _s3($key){
		$now = strtotime(date("Y-m-d\TG:i:s")); 
		$expire = date('Y-m-d\TG:i:s\Z', strtotime('+ 10 minutes', $now)); // credentials valid 10 minutes from now 


		$aws_access_key_id = 'yourAccessKeyId'; 
		$aws_secret_key = 'yourSecretKey'; 
		$bucket = 'your-bucket'; 
		$acl = 'public-read'; // if you prefer you can use 'private'  
		$url = 'https://'.$bucket.'.s3.amazonaws.com'; 
		$success_action_redirect = 'http://birkoff.net'; 
		$policy_document='
		{"expiration": "'.$expire.'",
		  "conditions": [
		    {"bucket": "'.$bucket.'"},
		    ["starts-with", "$key", "'.$key.'"],
		    {"acl": "'.$acl.'"},
		    {"success_action_redirect": "'.$success_action_redirect.'"},
		    ["starts-with", "$Content-Type", ""]
		  ]
		}';

		// create policy
		$policy = base64_encode($policy_document); 

		// create signature
		// hex2b64 and hmacsha1 are functions that we will create
		$signature = hex2b64(hmacsha1($aws_secret_key, $policy));
	}
}

class Dashboard extends MY_Controller{
	function __construct(){
		parent::__construct();

		if(!$this->common->loggedin()){
			redirect(site_url('login'));
		}
		else{
			$this->data['loggedin_user'] $this->users_model->find($this->session->userdata("uid"));
		}
	}
}

class Admin extends Dashboard{
	function __construct(){
		parent::__construct();
		if(!$this->common->isAdmin()){
			redirect(site_url('login'));
		}
	}
}

class Payment extends MY_Controller{
	function __construcT(){
		parent::__construct();

		$stripe_live_key = $this->config->item('stripe_live_key');
		$stripe_test_key = $this->config->item('stripe_test_key');
		$stripe_test_mode = $this->config->item('stripe_test_mode');
		$stripe_enabled = $this->config->item('stripe_enabled');


		$key = $stripe_test_key;

		if($stripe_test_mode == FALSE){
			$key = $stripe_live_key;
		}

		if($stripe_enabled){
			Stripe::setApiKey($this->config->item($key));
		}
	}

	function _stripe_charge_card($card, $amount, $description = null){
		$charge = array(
			"card" => $card,
			"amount" => $amount * 100,
			"currency" => $this->config->item('stripe_currency')
		);

		if($description){
			$charge['description'] = $description;
		}

		try{
			$chargeObject = Stripe_Charge::create($charge);
			return $chargeObject;
		}
		catch(Stripe_CardError $e){
			$this->session->set_userdata("STRIPE_CHARGE_ERROR", $e->message);
			return FALSE;
		}
	}

	function _stripe_charge_customer($customer, $amount, $description = null){
		$charge = array(
			"custoemr" => $customer,
			"amount" => $amount * 100,
			"currency" => $this->config->item('stripe_currency')
		);

		if($description){
			$charge['description'] = $description;
		}

		try{
			$chargeObject = Stripe_Charge::create($charge);
			return $chargeObject;
		}
		catch(Stripe_CardError $e){
			$this->session->set_userdata("STRIPE_CHARGE_ERROR", $e->message);
			return FALSE;
		}
	}

	function _stripe_transfer($recipient, $amount, $description = null){
		$transfer = array();

		$transferObject = Stripe_Transfer::create($transfer);
		
		if($transferObject['failure_code'] == null){
			//store transfer in database
			return $transferObject;
		}
		else{
			$error = "";

			switch($transferObject['failure_code']){

			}			

			$this->session->set_userdata("STRIPE_TRANSFER_ERROR", $error);
		}
	}

	function _stripe_create_customer($name, $email = null, $card = null, $plan = null){
		$customer = array(
			"name" => $name
		);

		if($email){
			$customer['email'] = $email;
		}

		if($card){
			$customer['card'] = $card;
		}

		if($plan){
			$customer['plan'] = $plan;
		}

		$customerObject = Stripe_Customer::create($customer);

		return $customerObject;
	}

	function _stripe_create_recipient($name = null, $bank = null, $tax_id = null, $type = 'individual'){
		$recipient = array(
			"name" => $name,
			"type" => $type
		);

		if($bank){
			$recipient['bank'] = $bank;
		}

		if($tax_id){
			$recipient['tax_id'] = $bank;
		}

		$recipientObject = Stripe_Recipient::create($recipient);

		return $recipientObject;
	}

	function _stripe_update_customer($customer_id, $name = null, $card = null){

	}

	function _stripe_update_recipient($recipient_id, $name = null, $bank = null, $tax_id = null, $type = 'individual'){

	}

	function _stripe_refund_charge($charge_id, $amount){

	}
}

function hmacsha1($key,$data) 
  {
    $blocksize=64;
    $hashfunc='sha1';
    if (strlen($key)&gt;$blocksize)
    $key=pack('H*', $hashfunc($key));
    $key=str_pad($key,$blocksize,chr(0x00));
    $ipad=str_repeat(chr(0x36),$blocksize);
    $opad=str_repeat(chr(0x5c),$blocksize);
    $hmac = pack(
                'H*',$hashfunc(
                    ($key^$opad).pack(
                        'H*',$hashfunc(
                            ($key^$ipad).$data

                        )
                    )
                )
            );
    return bin2hex($hmac);
  }

  function hex2b64($str)
  {
      $raw = '';
      for ($i=0; $i &lt; strlen($str); $i+=2)
      {
          $raw .= chr(hexdec(substr($str, $i, 2)));
      }
      return base64_encode($raw);
  }
