<?php
class Users_Model extends MY_Model{
	protected $table = "users";

	function signup($name, $email, $password){
		$hash = md5(time() + 'rand');
		$salt = md5(time());
		$Encryptedpassword = sha1($salt.$password);

		if($this->common->unique_email($email)){
			$newUser = array(
				"name" => $name,
				"email" => $email,
				"salt" => $salt,
				"password" => $Encryptedpassword,
				'activation_hash' => $hash,
				"created" => time()
			);

			$id = $this->db->insert($this->table, $newUser);

			if($id){
				$this->session->set_userdata("uid", $id);
				
				$reset_template = $this->load->view("email/forgot", array(
					"hash" => $hash
				), true);

				$this->email->to($email);
				$this->email->from($this->config->item('application_noreply_email'));
				$this->email->subject('Reset Password');
				$this->email->message($reset_template);
				$this->email->send();

				redirect(site_url());
			}
			else{
				$this->session->set_flashdata("error", "Unable to signup at this time. Please try again");
				redirect(site_url('signup'));
			}
		}
		else{
			$this->session->set_flashdata("error", "This email address is already registered");
			redirect(site_url('signup'));
		}
	}


	function login($email, $password){
		$this->db->where("email", $email);
		$user = $this->db->get($this->table)->row_array();

		if($user){
			if($user['password'] == sha1($user['salt'].$password)){
				$this->session->set_userdata("uid", $user['id']);
				redirect(site_url());
			}
			else{
				$this->session->set_flashdata("error", "You have entered an incorrect email and password combo");
				redirect(site_url('signup'));
			}
		}
		else{
			$this->session->set_flashdata("error", "This email address is not registered");
			redirect(site_url('login'));
		}
	}

	function logout(){
		$this->session->sess_destroy();
		redirect(site_url());
	}
}