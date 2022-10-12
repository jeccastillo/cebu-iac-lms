<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Site extends CI_Controller {

	
    function __construct() {
        parent::__construct();
		/*--------------THEMES-----------------------*/
		$this->config->load('themes');
		$theme = $this->config->item('users');
		$theme = 'site';
			
		$this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";
		$this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
		$this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";		
		$this->theme = $theme;
		$this->data['logged_in'] = $this->session->userdata('user_logged');
		

    }
	
	
    public function index() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('home.php',$this->data);
		$this->load->view('common/footer',$this->data);
    }

   }

?>
