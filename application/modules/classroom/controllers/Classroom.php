<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Classroom extends CI_Controller {

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
        $this->data['campus'] = $this->config->item('campus');
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "classroom";
    }
    
    public function add_classroom()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {   
            $this->data['crType'] = $this->config->item('crType');
            $this->data['page'] = "add_classroom";
            $this->data['opentree'] = "classroom";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_classroom",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("classroom_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function edit_classroom($id)
    {
        
        if($this->is_registrar() || $this->is_super_admin())
        {
          
            $this->data['item'] = $this->data_fetcher->getClassroom($id);
            $this->data['crType'] = $this->config->item('crType');
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_classroom",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("classroom_validation_js",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    public function submit_classroom()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {   
            $post = $this->input->post();
            //print_r($post);
            $this->data_poster->log_action('Classroom','Added a new Classroom '.$post['strRoomCode'],'green');
            $this->data_poster->post_data('tb_mas_classrooms',$post);
        }
        redirect(base_url()."classroom/view_classrooms");
            
    }
    
    public function submit_edit_classroom()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {   
            $post = $this->input->post();
            //print_r($post);
            $this->data_poster->post_data('tb_mas_classrooms',$post,$post['intID']);
            $this->data_poster->log_action('Classroom','Updated Classroom Info: '.$post['strRoomCode']." ".$post['enumType'],'green');
        }
        redirect(base_url()."classroom/view_classrooms");
            
    }
    
    public function classroom_viewer($id)
    {
        
        if($this->is_registrar() || $this->is_super_admin())
        {
          
            $this->data['item'] = $this->data_fetcher->getClassroom($id);
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['schedules'] = $this->data_fetcher->getScheduleByRoomID($id,$active_sem['intID']);
            
            $this->data['days'] = Array('1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat');
            $this->data['types'] = Array('lect','lab');
            $this->data['timeslots'] = Array('7:00','7:30','8:00','8:30','9:00','9:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00');
            
            $this->data['subjects'] = $this->data_fetcher->getSubjects();
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/classroom_viewer",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/classroom_viewer_conf",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    public function view_classrooms()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {
            $this->data['classrooms'] = $this->data_fetcher->fetch_table('tb_mas_classrooms');
            $this->data['page'] = "view_classrooms";
            $this->data['opentree'] = "classroom";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/classroom_view",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/classroom_conf",$this->data);
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");   
    
    }
    
    public function delete_classroom()
    {
        $data['message'] = "failed";
        if($this->is_admin()){
            $post = $this->input->post();
            $info = $this->data_fetcher->getClassroom($post['id']);
            $this->data_poster->deleteClassroom($post['id']);
            $this->data_poster->log_action('Classroom','Deleted a Classroom '.$info['strRoomCode'].' '.$info['enumType'],'red');
            $data['message'] = "success";
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