<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Configuration file for Email library
 */
$config['protocol'] = 'smtp';
$config['charset'] = 'utf-8';
$config['mailtype'] = 'html';
$config['smtp_timeout'] = 5;
$config['smtp_host'] = 'smtp.mandrillapp.com';
$config['smtp_port'] = 587;
$config['smtp_user'] = $this->config->item('mandrill_username');
$config['smtp_pass'] = $this->config->item('mandrill_password'); // It's your API key generated. 