<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Facebook_login extends CI_Model {
	function __construct() {
        
    }
	
    function facebook_signin($url=null) {
        
		$this->config->load('fbconfig');
		// Parameters
		$client_id = $this->config->item('app_id');
		
		
		if($url==null)
			$redirect_url = site_url('users/fb_login_success');
		else
		{
			$this->session->set_userdata('redirect_page',$url);
			$redirect_url = site_url('users/fb_login_success_not_home');
		}	
		$this->session->set_userdata('state', md5(uniqid(rand(), TRUE))); //CSRF protection
		$signin_url = "http://www.facebook.com/dialog/oauth/?client_id=" . $client_id . "&redirect_uri=" . $redirect_url."&state=".$this->session->userdata('state')."&scope=email";
		
        return $signin_url;
    }

}

?>
