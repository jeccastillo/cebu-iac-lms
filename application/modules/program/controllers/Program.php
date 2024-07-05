<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Program extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');

        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2 && $userlevel != 3)
		  redirect(base_url()."unity");

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
    }
    
    public function add_program()
    {
       
            
        $this->data['page'] = "add_program";
        $this->data['opentree'] = "programs";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/add_program",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("program_validation_js",$this->data); 
        //print_r($this->data['classlist']);
             
    }
    
    public function edit_program($id)
    {        
            
        $this->data['item']= $this->data_fetcher->getProgram($id);
        $this->data['curriculum'] = $this->db->get('tb_mas_curriculum')->result_array();
        $this->data['opentree'] = "programs";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_program",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("program_validation_js",$this->data); 
        //print_r($this->data['classlist']);
      
    }
    
    public function submit_program()
    {
        
        $post = $this->input->post();
        $post['school'] = "iacademy";
        //print_r($post);
        $this->data_poster->log_action('Program','Added a new Program '.$post['strProgramCode'],'yellow');
        $this->data_poster->post_data('tb_mas_programs',$post);        
        redirect(base_url()."program/edit_program/".$this->db->insert_id());
            
        
    }
    
    public function submit_edit_program()
    {
        
        $post = $this->input->post();
        //print_r($post);
        $this->data_poster->log_action('Program','Updated a Program '.$post['strProgramCode'],'yellow');
        $this->data_poster->post_data('tb_mas_programs',$post,$post['intProgramID']);
        redirect(base_url()."program/edit_program/".$post['intProgramID']);
            
        
    }
    
    public function view_all_programs()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_programs";
            $this->data['opentree'] = "programs";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/program_view",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/programs_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");  
    }

    public function programs(){
        $ret['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
        echo json_encode($ret);
    }
    
    public function program_viewer($id,$sem = null)
    {
         $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $active_sem = $this->data_fetcher->get_active_sem();
			//$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
            $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        
        $this->data['program'] = $this->data_fetcher->getProgramDetails($id);
        
       // $records = $this->data_fetcher->fetch_classlist_by_subject($id,$this->data['active_sem']['intID']);
        $records = $this->data_fetcher->fetch_sections_by_program($this->data['program']['strProgramCode'],$this->data['active_sem']['intID']);
          foreach($records as $record)
            {
                //$record['schedule'] = $this->data_fetcher->getScheduleByCode($record['intID']);
                ////print_r($record['schedule']);
                $this->data['classlist'][] = $record;
            }
         $this->load->view("common/header",$this->data);
            $this->load->view("admin/program_viewer",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/program_viewer_conf",$this->data);
            //$this->load->view("subject_data_js",$this->data);
            //print_r($records);
        //print_r($this->data['program']);
    }
    
     public function program_course_viewer($id,$sem = null)
    {
         $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $active_sem = $this->data_fetcher->get_active_sem();
			//$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
            $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        
        $this->data['section'] = $this->data_fetcher->getSectionDetails($id);
        
       // $records = $this->data_fetcher->fetch_classlist_by_subject($id,$this->data['active_sem']['intID']);
        $records = $this->data_fetcher->fetch_classlist_by_section($this->data['section']['strSection'],$this->data['active_sem']['intID']);
          foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['intID']);
                ////print_r($record['schedule']);
                $this->data['classlist'][] = $record;
            }
         $this->load->view("common/header",$this->data);
            $this->load->view("admin/program_course_viewer",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/program_viewer_conf",$this->data);
            //$this->load->view("subject_data_js",$this->data);
            //print_r($records);
        //print_r($this->data['program']);
    }
    
    public function delete_program()
    {
        $data['message'] = "failed";
        if($this->is_admin()){
            $post = $this->input->post();
            $this->data_poster->deleteProgram($post['id']);
            $data['message'] = "success";
            $this->data_poster->log_action('Program','Deleted a Program '.$post['code'],'red');
        }
        echo json_encode($data);
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