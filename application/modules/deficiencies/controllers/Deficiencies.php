<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deficiencies extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');

        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2 && $this->session->userdata('special_role') < 2) 
		  redirect(base_url()."unity");
          
        $settings = $this->data_fetcher->fetch_table('su-tb_sys_settings');
		foreach($settings as $setting)
		{
			$this->settings[$setting['strSettingName']] = $setting['strSettingValue'];
		}
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "iACADEMY";
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
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "subjects";
    }
    
    public function add_grading()
    {
        if($this->is_admin() || $this->is_registrar())
        {
            $dpt = array(); 
            foreach($this->data['department_config'] as $dept)
                $dpt[$dept] = $dept;

            $this->data['lab_types'] = $this->data_fetcher->getLabTypesForDropdown();
            
            $this->data['dpt'] = $dpt;
            $this->data['page'] = "add_grading_system";
            $this->data['opentree'] = "grading";
            
           
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_grading",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("subject_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function student_search(){
        $this->data['page'] = "deficiencies";               
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/search_student",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/search_student_conf",$this->data);
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


}