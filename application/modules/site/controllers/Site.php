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

	public function student_application() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_application',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function awesome() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/awesome',$this->data);
		$this->load->view('common/footer',$this->data);
    }
	
	public function applicant_first_step() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/first_step',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function initial_requirements() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/initial_requirements',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function articles() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('articles',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function article_details() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('article_details',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function applicant_calendar() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/calendar',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function admissions_student_payment() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/payment',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function admissions_student_payment_reservation() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/payment',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function finance_payment($student_id) {                
        $this->data['student_id'] = $student_id;
        //API - registrar/get_registration_info - returns reg data and current sem
        $this->load->view('common/header',$this->data);        
		$this->load->view('finance/payment',$this->data);
		$this->load->view('common/footer',$this->data);
    }


   }

?>