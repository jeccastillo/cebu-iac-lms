<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Academics extends CI_Controller {

	public function __construct()
	{
		parent::__construct();                

        if(!$this->is_academics() && !$this->is_super_admin())
		  redirect(base_url()."unity");
        
		$this->config->load('themes');
        $this->config->load('schedule');
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
		$this->load->model("google_login");	
		$this->load->model("facebook_login");	
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
        
        //$this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        //$this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		
	}
    
    public function index()
	{	
        
        if($this->faculty_logged_in())
            redirect(base_url()."unity/faculty_dashboard");
        
        else
            redirect(base_url()."users/login");
        
        
	}
           

    public function view_all_students($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$sem=0,$scholarship=0,$registered=0)
    {
        if($this->data["user"]["special_role"] >= 1)
        {            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $this->data['page'] = "students";
            $this->data['opentree'] = "academics_students";

            if($sem == 0){
                $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
                $sem = $this->data['active_sem']['intID'];
            }
           // $this->data['offset'] = $offset;
            
            //$students = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
           // $this->data['registered'] = count($students);
            
            /*if($search == null)
                $this->data['students'] = $this->data_fetcher->fetch_students('tb_mas_users',array('strLastName','asc'),20,null,$offset);
            else {
              //put code for search algorithm
                $this->data['students'] = $this->data_fetcher->search_for_students();
            */
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['course'] = $course;
            $this->data['postreg'] = $regular;
            $this->data['postyear'] = $year;
            $this->data['gender'] = $gender;
            $this->data['graduate'] = $graduate;
            $this->data['scholarship'] = $scholarship;
            $this->data['registered'] = $registered;
            $this->data['sem'] = $sem;
            $this->data['level'] = $level;
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/all_students",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/all_students_conf",$this->data);

            
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
    
    public function is_academics()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 0)
            return true;
        else
            return false;
        
    }
    
}