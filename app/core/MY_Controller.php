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

