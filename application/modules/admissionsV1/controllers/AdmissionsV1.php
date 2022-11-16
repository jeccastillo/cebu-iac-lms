<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AdmissionsV1 extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
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
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "subjects";
    }
    
    
    public function view_all_leads()
    {
        if($this->faculty_logged_in())
        {
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
            redirect(base_url());  
    }

    public function add_new_student(){
        
        $ip = $this->input->ip_address();
        if($ip == "172.16.80.22"){
            $data['message'] = "success";
            $data['success'] = true;
            $post = $this->input->post();
            $data['data'] = $post;                       
            $this->data_poster->post_data('tb_mas_users',$post);
        }
        else{
            $data['message'] = "Access Denied: you are using an invalid ip address";
            $data['success'] = false;
            $data['data'] = null;
        }

        $data['ip_address']  = $ip;
            

        //echo json_encode($data['data']);
    }


    public function view_lead($id) {
        if($this->faculty_logged_in())
            {
               
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_lead",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/subjects_conf",$this->data); 
        }
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