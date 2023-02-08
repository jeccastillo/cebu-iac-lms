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
		$term = $this->data_fetcher->get_active_sem();
		$this->data['current_term'] = $term['intID'];
		
        $this->load->view('common/header',$this->data);    		     
		$this->load->view('student_application',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function awesome($type = null) {
		$type = urldecode($type);
		if($type == "Application Payment"){
			$this->data['text'] = 'That wasn’t so hard, was it? Now, all you have
							to
							do is
							wait for our
							Admissions
							Team to evaluate your application and schedule you for an interview – the
							last step before finding out if you will become an <span class="font-bold">iACADEMY Game
								Changer!</span>
							Keep your lines open and check your email!';
		}
		elseif($type == "sched"){
			$this->data['text'] = '<h3>You have successfully scheduled your interview.</h3> 
									<br />
									Please arrive at iACADEMY cebu 30 min before your scheduled interview. Good luck.';
		}
		elseif($type == "Reservation Payment"){
			$this->data['text'] = 'You are a game changer. The interests that you have cultivated through the years are leading you down the path of a successful career. How you reach that path is now in your hands.
			<br /><br />
			This early on, we can see that you are one of the few people who have the capacity TO shape the course of the future. It is for this reason that iACADEMY would like to offer you the opportunity to pursue your passions and develop practical skills that you will need to enter the industry of your choice with a competitive edge. 						
			<br /><br />
			See you soon, <span class="font-bold">Game Changer</span>';
		}
		else{
			$this->data['text'] = 'Payment Successful';
		}

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

	public function latest_news() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('latest_news',$this->data);
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

	public function student_tuition_payment($slug) {                
        $this->data['student_slug'] = $slug;
            
        $this->load->view('common/header',$this->data);        
		$this->load->view('finance/payment_tuition',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function student_other_payment($slug) {                
        $this->data['student_slug'] = $slug;
        //API - registrar/get_registration_info - returns reg data and current sem
        $this->load->view('common/header',$this->data);        		
		$this->load->view('finance/payment_other',$this->data);		
		$this->load->view('common/footer',$this->data);
    }

	public function view_active_programs(){
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1));
        $ret = [];
        foreach($programs as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret[] = $temp;
        }

        $data['data'] = $ret;

        echo json_encode($data);

    }

   }

?>