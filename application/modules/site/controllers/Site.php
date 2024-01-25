<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Site extends CI_Controller {

	
    function __construct() {
        parent::__construct();
		/*--------------THEMES-----------------------*/
		$this->config->load('themes');
		$theme = $this->config->item('users');
		$this->config->load('courses');
		$theme = 'site';
			
		$this->data['term_type'] = $this->config->item('term_type');
		$this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";
		$this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
		$this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";	
		$this->data['campus'] = $this->config->item('campus');	
		$this->theme = $theme;
		$this->data['logged_in'] = $this->session->userdata('user_logged');
		

    }
	
	
    public function index() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('home.php',$this->data);
		$this->load->view('common/footer',$this->data);
    }

	public function student_application($term = 0,$type = 0) {		

		if($term == 0){
			if($type == 0)
            	$term = $this->data_fetcher->get_processing_sem();        			
			else
				$term = $this->data_fetcher->get_processing_sem_shs();        						
		}
		else
			$term = $this->data_fetcher->get_sem_by_id($term);

		if((date("Y-m-d h:i:s") >= $term['endOfApplicationPeriod']) || !$term['endOfApplicationPeriod']){
			echo "Application Period has ended for this term";
			die();
		}

		$this->data['term'] = $term;

		$this->data['current_term'] = $term['intID'];		
        
		$this->load->view('common/header_new',$this->data);  
		
		if($this->data['campus'] == "Cebu")  		     
			$this->load->view('student_application',$this->data);
		else
			$this->load->view('student_application_makati',$this->data);

		$this->load->view('common/footer_new',$this->data);
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
		elseif($type == "confirm"){
			$this->data['text'] = '<h3>You have successfully confirmed your program.</h3>';
		}
		else{
			$this->data['text'] = 'Payment Successful';
		}

        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/awesome',$this->data);
		$this->load->view('common/footer_new',$this->data);
    }
	
	public function applicant_first_step() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/first_step',$this->data);
		$this->load->view('common/footer_new',$this->data);
    }

	public function initial_requirements() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/initial_requirements',$this->data);
		$this->load->view('common/footer_new',$this->data);
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
		$this->load->view('common/footer_new',$this->data);
    }

	public function admissions_student_payment_reservation() {
        $this->load->view('common/header',$this->data);        
		$this->load->view('student_applicants/payment',$this->data);
		$this->load->view('common/footer_new',$this->data);
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

	public function view_active_programs($syid){
		$term = $this->data_fetcher->get_sem_by_id($syid);
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1,'type'=>'college'));
        $ret = [];
        foreach($programs as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret[] = $temp;
        }

		$data['sy'] = $this->db->where(array('endOfApplicationPeriod != '=>NULL,'endOfApplicationPeriod >'=>date("Y:m:d H:i:s"),'term_student_type'=>$term['term_student_type']))
								->order_by("strYearStart ASC, enumSem ASC")
								->get('tb_mas_sy')
								->result_array();
		$data['term'] = $term;
        $data['data'] = $ret;

        echo json_encode($data);

    }

	public function view_active_programs_makati($syid){
		$term = $this->data_fetcher->get_sem_by_id($syid);
		
        $programs_college = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1,'type'=>'college'));
		$programs_shs = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1,'type'=>'shs'));
		$programs_sd = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1,'type'=>'other'));
		$programs_drive = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1,'type'=>'drive'));
        $ret = array(
			'college' => [],
			'shs' => [],
			'sd' =>[],
			'drive'=>[],
		);
		
        foreach($programs_college as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret['college'][] = $temp;
        }

		foreach($programs_shs as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret['shs'][] = $temp;
        }

		foreach($programs_sd as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret['sd'][] = $temp;
        }

		foreach($programs_drive as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret['drive'][] = $temp;
        }

		$data['sy'] = $this->db->where(array('endOfApplicationPeriod != '=>NULL,'endOfApplicationPeriod >'=>date("Y:m:d H:i:s"),'term_student_type'=>$term['term_student_type']))
								->order_by("strYearStart ASC, enumSem ASC")
								->get('tb_mas_sy')
								->result_array();

		$data['term'] = $term;								
		

        $data['data'] = $ret;

        echo json_encode($data);

    }

		public function maya_redirect_url($status) {

			$data['event'] = $status;
			$this->load->view('common/header_maya',$this->data);        
			$this->load->view('maya_redirect_url/events',$data);
			$this->load->view('common/footer_new',$this->data);
	}

   }

?>