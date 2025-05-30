<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AdmissionsV1 extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        //User Level Validation
        
        $userlevel = $this->session->userdata('intUserLevel');   
        $ip = $this->input->ip_address();        
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 7 && $userlevel != 3 &&  $ip != "172.16.80.22")
		  redirect(base_url()."unity");

		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
		
        $settings = $this->data_fetcher->fetch_table('su-tb_sys_settings');
		foreach($settings as $setting)
		{
			$this->settings[$setting['strSettingName']] = $setting['strSettingValue'];
		}
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Unity";
        $this->load->library("email");	
        $this->load->helper("cms_form");	
		$this->load->model("user_model");
        $this->config->load('courses');
        $this->data['department_config'] = $this->config->item('department');
        $this->data['terms'] = $this->config->item('terms');
        $this->data['term_type'] = $this->config->item('term_type');
        $this->data['unit_fee'] = $this->config->item('unit_fee');
        $this->data['misc_fee'] = $this->config->item('misc_fee');
        $this->data['lab_fee'] = $this->config->item('lab_fee');
        $this->data['id_fee'] = $this->config->item('id_fee');
        $this->data['athletic'] = $this->config->item('athletic');
        $this->data['srf'] = $this->config->item('srf');
        $this->data['sfdf'] = $this->config->item('sfdf');
        $this->data['csg'] = $this->config->item('csg');
        $this->data['campus'] = $this->config->item('campus');
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "subjects";

        $sem = $this->data_fetcher->get_processing_sem();        
        $this->data['current_sem'] = $sem['intID'];
    }
    
    public function edit_submit_ay()
    {
        $post = $this->input->post();                
       // $this->data_poster->set
        $this->data_poster->post_data('tb_mas_sy',$post,$post['intID']);
        $this->data_poster->log_action('Admissions Details Updated','Updated Term Info: '.$post['enumSem']." ".$post['strYearStart']." - ".$post['strYearEnd'],'aqua');
        redirect(base_url()."admissionsV1/edit_ay/".$post['intID']);
            
    }

    public function view_classlist_archive_admin($sem = null, $program = 0, $dissolved = 0, $has_faculty = 0)
    {
        if($this->data["user"]["special_role"] >= 1 && $this->data["user"]["intUserLevel"] != 7)
        {  
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            
            $active_sem = $this->data_fetcher->get_active_sem();
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
            $this->data['program'] = $program;
            $this->data['dissolved'] = $dissolved;
            $this->data['has_faculty'] = $has_faculty;
           
            $this->data['page'] = "classlist_archive";
            $this->data['opentree'] = "leads";
            // $this->data['opentree'] = "registrar";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/classlist_view_admin",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/classlist_view_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");   
    
    }

    public function edit_ay($id = 0){

        if($id == 0)
            $this->data['item'] = $this->data_fetcher->get_active_sem();
        else
            $this->data['item'] = $this->data_fetcher->getAy($id);  
        
        $this->data['page'] = "admissions_sy_setup";                    
        
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_sy_admissions",$this->data);
        $this->load->view("common/footer",$this->data);         
        $this->load->view("common/edit_sy_conf",$this->data); 
    }
    
    public function view_all_leads($term = 0)
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);  
                
            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
            
            $this->data['page'] = "view_leads";
            $this->data['opentree'] = "leads";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/leads_view",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/subjects_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");  
    }

    public function view_reserved_leads($term = 0)
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);                  
            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
            $this->data['page'] = "view_reserved";
            $this->data['opentree'] = "leads";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/reserved_students",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/reserved_students_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");  
    }

    public function view_interviewed_leads($term = 0)
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);                  
            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
            $this->data['page'] = "view_interviewed";
            $this->data['opentree'] = "leads";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/interviewed_students",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/interviewed_students_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");  
    }

    public function admissions_report($term = 0,$start = 0,$end=0)    
    {
        if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);  
                
            
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $this->data['current_sem'] = $term['intID'];
        $this->data['registrations'] = json_encode($this->db->select('tb_mas_users.slug,tb_mas_registration.dteRegistered')
                                                ->from('tb_mas_registration')
                                                ->join('tb_mas_users','tb_mas_registration.intStudentID = tb_mas_users.intID')
                                                ->where(array('intAYID'=>$term['intID']))
                                                ->get()
                                                ->result_array());    
        
        $this->data['start'] = $start;
        $this->data['end'] = $end;        
        $this->data['active_sem'] = $this->data_fetcher->get_processing_sem();
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/admissions_report",$this->data);
        $this->load->view("common/footer",$this->data); 
        
    }

    public function enrollment_summary($sem = 0)    
    {
        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }
        else{
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
            $this->data['sem'] = $active_sem['intID'];
        }        
        $this->data['pdf_link'] = base_url()."pdf/enrollment_summary/".$this->data['sem'];
        $this->data['excel_link'] = base_url()."excel/enrollment_summary/".$this->data['sem'];

        
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/enrollment_summary",$this->data);
        $this->load->view("common/footer",$this->data); 
        
    }

    public function enrollment_summary_data($sem = 0){

        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $sem = $active_sem['intID'];
        }
        else{
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
            $sem = $active_sem['intID'];
        }


        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $type = $active_sem['term_student_type'];
        if($type == "shs")
            $programs = $this->db->get_where('tb_mas_programs',array('type'=>$type))->result_array();
        elseif($type == "college")
            $programs = $this->db->where('type','college')
                                 ->or_where('type','other')
                                 ->get('tb_mas_programs')
                                 ->result_array();
        elseif($type == "next")
            $programs = $this->db->where('type','next')
            ->or_where('type','other')
            ->get('tb_mas_programs')
            ->result_array();
                                 
        $data['programs'] = $programs;
        $ret = [];        

        foreach($programs as $program){
            $st = [];
            $program['enrolled_transferee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,2));
            $program['enrolled_freshman'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,1));
            $program['enrolled_foreign'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,3));
            $program['enrolled_second'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,4));            
            $program['enrolled_continuing'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,5));
            $program['enrolled_shiftee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,6));
            $program['enrolled_returnee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,4,$sem,7));
            $ret[] = $program; 
        }

        $data['data'] = $ret;

        echo json_encode($data);

    }

    public function add_new_student(){
        
    
        $ip = $this->input->ip_address();
        if($ip == "172.16.80.22"){       
            $post = $this->input->post();
            $student = $this->db->get_where('tb_mas_users',array('slug'=>$post['slug']))->first_row();     
            if($post['level'] == "next"){
                $post['intProgramID'] = 73;            
            }
            $data['data'] = $post;                                
            $post['strAcademicStanding'] = "regular";                                                
            $post['intTuitionYear'] = (get_stype($post['level']) == "college")?$this->data_fetcher->getDefaultTuitionYearID():$this->data_fetcher->getDefaultTuitionYearIDShs();
            $post['intCurriculumID'] = $this->data_fetcher->getCurriculumIDByCourse($post['intProgramID'])?$this->data_fetcher->getCurriculumIDByCourse($post['intProgramID']):'1';                
            
            if(!$student){
                $tempNum = $this->data_fetcher->generateNewTempNumber();                                                                            
                $post['strStudentNumber'] = $tempNum;
                $post['dteCreated'] = date("Y-m-d");
                $this->data_poster->post_data('tb_mas_users',$post);
                
            }
            else{
                $this->data_poster->post_data('tb_mas_users',$post,$student['intID']);
            }
            $data['message'] = "success";
            $data['success'] = true;
        }
        else{
            $data['message'] = "Access Denied: you are using an invalid ip address";
            $data['success'] = false;
            $data['data'] = null;
        }

        $data['ip_address']  = $ip;
            

        echo json_encode($data);
    }
    
    public function schools(){
        
        $this->data['page'] = "schools";        
                           
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/schools",$this->data);
        $this->load->view("common/footer",$this->data); 
    }

    public function awareness_stats($term = 0){
        if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);  
                
            
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $this->data['current_sem'] = $term['intID'];
        $this->data['page'] = "awareness_stats";
        $this->data['opentree'] = "leads";
                   
        $this->data['active_sem'] = $this->data_fetcher->get_processing_sem();
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/awareness_stats",$this->data);
        $this->load->view("common/footer",$this->data); 
    }

    public function awareness_stats_data($term){

    }

    public function programs($slug,$sem = 0){
        $ret['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
        $ret['sy'] = $this->db->get('tb_mas_sy')->result_array();
        $ret['current_term'] = $this->data_fetcher->get_sem_by_id($sem);
        $ret['entrance_exam'] = $this->db->get_where('tb_mas_student_exam',array('student_id'=>$slug))->first_row('array'); 
        // print_r($ret['entrance_exam']);
        $scorePerSectionArray = [];
        if(isset($ret['entrance_exam'])){
            $examPerSection = $this->db->get_where('tb_mas_student_exam_score_per_section',array('tb_mas_student_exam_id'=>$ret['entrance_exam']['intID']))->result_array('array');
            // print_r($examPerSection);
            // print($ret['entrance_exam']['intID']);
            // die();
            foreach($examPerSection as $exam){
                $scorePerSectionArray[] = array(
                    'section' => $exam['section'],
                    'score' =>  $exam['score'],
                    'items' => $exam['exam_overall'],
                    'percentage'=> $exam['percentage'],
                );
            }            
        }
        $ret['section_scores'] = $scorePerSectionArray;
        echo json_encode($ret);
    }

    public function get_exam_types(){
        $data['exam_types'] = $this->db->get('tb_mas_exam')->result_array();
        echo json_encode($data);
    }

    public function update_requirements(){
        $this->load->view('common/header',$this->data);        
		$this->load->view('admin/update_requirements',$this->data);
		$this->load->view('common/footer',$this->data);
    }

    public function view_lead($id) {
        if(in_array($this->session->userdata('intUserLevel'),array(2,3,5,6,7)))
        {
            $this->data['exam_type']= $this->data_fetcher->fetch_table('tb_mas_exam');
            $this->data['userlevel'] = $this->session->userdata('intUserLevel');
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_lead",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/subjects_conf",$this->data); 
        }
        else
            redirect(base_url()."users");            
    }
    public function view_lead_new($id) {
        if(in_array($this->session->userdata('intUserLevel'),array(2,3,5,6,7)))
        {
            $this->data['exam_type']= $this->data_fetcher->fetch_table('tb_mas_exam');
            $this->data['userlevel'] = $this->session->userdata('intUserLevel');
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_lead_new",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/subjects_conf",$this->data); 
        }
        else
            redirect(base_url()."users/login");            
    }


    public function fi_calendar() {
        if(in_array($this->session->userdata('intUserLevel'),array(2,3,5,6)))
        {
               
            $this->data['page'] = "fi_calendar";
            $this->data['opentree'] = "leads";
            $this->data['userlevel'] = $this->session->userdata('intUserLevel');
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/calendar_view",$this->data);
            $this->load->view("common/footer",$this->data);             
        }
        else
            redirect(base_url()."users/login");            
    }

    public function paid_applicants($term = 0) {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);  
                
            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
            
            $this->data['page'] = "view_paid";
            $this->data['opentree'] = "leads";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_paid_applicants",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/applicant_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");            
    }
    
   
    
    public function faculty_logged_in()
    {
        if($this->session->userdata('faculty_logged'))
            return true;
        else
            return false;
    }
    
    
    public function is_admin()
    {
         $admin = $this->session->userdata('intUserLevel');
        if($admin == 1 || $this->is_super_admin())
            return true;
        else
            return false;
    }
    
    public function is_super_admin()
    {
         $admin = $this->session->userdata('intUserLevel');
        if($admin == 2)
            return true;
        else
            return false;
    }
    
    public function is_registrar()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 3)
            return true;
        else
            return false;
        
    }
    
    public function is_admissions()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 5)
            return true;
        else
            return false;
        
    }

    public function view_paid_app_fee()
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);                  
            
            
            $applicants = $this->data_fetcher->fetch_table('tb_mas_sy');

            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
            $this->data['page'] = "view_reserved";
            $this->data['opentree'] = "leads";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/reserved_students",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/reserved_students_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");
    } 

}