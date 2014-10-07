<?php
class Main extends MY_Controller{
	function index(){
		$this->render("main/homepage");
	}

	function login(){
		if($this->input->post()){
			$email = $this->input->post("email");
			$password = $this->input->post("password");

			$this->users_model->login($email, $password);
		}

		$this->render();
	}

	function signup(){
		if($this->input->post()){
			$name = $this->input->post("name");
			$email = $this->input->post("email");
			$password = $this->input->post("password");

			$this->users_model->signup($name, $email, $password);
		}

		$this->render();
	}

	function logout(){
		$this->users_model->logout();
	}

	function forgot_password(){
		if($this->input->post()){
			$email = $this->input->post("email");
			$hash = md5(time());

			$user = $this->users_model->find_where(array(
				"email" => $email
			), true);

			if($user){
				$update = array(
					"reset_hash" => $hash
				);

				$this->users_model->save($update, $user['id']);

				$reset_template = $this->load->view("email/forgot", array(
					"hash" => $hash
				), true);

				$this->email->to($email);
				$this->email->from($this->config->item('application_noreply_email'));
				$this->email->subject('Reset Password');
				$this->email->message($reset_template);
				$this->email->send();
			}
		}

		$this->render("main/forgot_password");
	}

	function reset_password($hash = null){
		if($hash){
			$user = $this->users_model->find_where(array(
				"reset_hash" => $hash
			), true);

			if($user){
				if($this->input->post()){
					$salt = md5(time());

					$password = $this->input->post("password");
					$confpassword = $this->input->post("confpassword");

					if($password == $confpassword){
						$update = array(
							"salt" => $salt,
							"password" => sha1($salt.$password),
							"reset_hash" => ""
						);

						$this->users_model->save($update, $user['id']);
					}
					else{
						$this->session->set_flashdata("error", "Your passwords must match");
						redirect(site_url('reset_password/'.$hash));
					}
				}

				$this->render("main/reset_password");
			}
			else{
				redirect(site_url());
			}
		}
		else{
			redirect(site_url());
		}
	}

	function activate($hash = null){

	}

	function notfound(){
		
	}
}