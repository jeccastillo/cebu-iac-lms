<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subject extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');

        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2 && $userlevel != 6 && $userlevel != 4 && $userlevel != 3) 
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
    
    public function add_subject()
    {
        if($this->is_admin() || $this->is_registrar())
        {
            $dpt = array(); 
            foreach($this->data['department_config'] as $dept)
                $dpt[$dept] = $dept;

            $this->data['lab_types'] = $this->data_fetcher->getLabTypesForDropdown();
            
            $this->data['dpt'] = $dpt;
            $this->data['page'] = "add_subject";
            $this->data['opentree'] = "subject";
            
           
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_subject",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("subject_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function edit_subject($id)
    {
        
        
        $dpt = array(); 
        foreach($this->data['department_config'] as $dept)
        $dpt[$dept] = $dept;

        $this->data['userlevel'] = $this->session->userdata('intUserLevel');
        
        $this->data['lab_types'] = $this->data_fetcher->getLabTypesForDropdown();
        $this->data['dpt'] = $dpt;
        $this->data['subject'] = $this->data_fetcher->getSubjectPlain($id);
        $this->data['grading_systems'] = $this->data_fetcher->fetch_table('tb_mas_grading');
        $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
        
        $prereq = $this->data_fetcher->getSubjectsNotSelected($id);
        $eq = $this->data_fetcher->getSubjectsNotSelectedEquivalent($id);
        
        $this->data['rooms'] = $this->data_fetcher->getRoomsNotSelected($id);
        $this->data['selected_rooms'] = $this->data_fetcher->getRoomsSelected($id);
        
        $this->data['selected_prereq'] = $this->data_fetcher->getPrereq($id);
        $this->data['selected_eq'] = $this->data_fetcher->getPrereqEq($id);
        
        $days = array("1 2 3 4 5 6","1 3","1 3 5","3 5","2 4","2 4 6");
        $selected_days = $this->data_fetcher->getSelectedDays($id);
        $dy = array();
        
        foreach($selected_days as $sel)
        {
            $dy[] = $sel['strDays'];
        }
        
        
        $this->data['selected_days'] = $dy;
        $this->data['days'] = array_diff($days,$dy);
        
        $this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
        
        
        
        $this->data['prereq'] = $prereq;
        $this->data['all_eq'] = $eq;
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_subject",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("subject_validation_js",$this->data); 
        // print_r($this->data['classlists']);
                
        
    }
    
    public function submit_subject()
    {
        if($this->is_admin() || $this->is_registrar()){
            $post = $this->input->post();
            //print_r($post);
            $this->data_poster->log_action('Subject','Added a new Subject '.$post['strCode'],'yellow');
            $this->data_poster->post_data('tb_mas_subjects',$post);
            redirect(base_url()."subject/view_all_subjects");
            
        }
    }
    
    public function submit_room_subject()
    {
        $post = $this->input->post();
        $subject = $post['intSubjectID'];
        $this->data_poster->delete_room_subject($subject);
        
        if(isset($post['rooms']))
        {
            

            foreach($post['rooms'] as $room)
            {
                $data['intRoomID'] = $room;
                $data['intSubjectID'] = $subject;
                $this->data_poster->post_data('tb_mas_room_subject',$data);
            }
         
        }
        
        $data['message'] = "Success";
        
        echo json_encode($data);
    }
    
    public function submit_prereq_subject()
    {
        $post = $this->input->post();
        $subject = $post['intSubjectID'];
        $this->data_poster->delete_prereq_subject($subject);
        
        if(isset($post['subj']))
        {
            

            foreach($post['subj'] as $subj)
            {
                $data['intPrerequisiteID'] = $subj;
                $data['intSubjectID'] = $subject;
                $this->data_poster->post_data('tb_mas_prerequisites',$data);
            }
         
        }
        
        $data['message'] = "Success";
        
        echo json_encode($data);
    }

    public function submit_eq_subject(){
        $post = $this->input->post();
        $subject = $post['intSubjectID'];
        $this->data_poster->delete_eq_subject($subject);
        
        if(isset($post['subj']))
        {
            

            foreach($post['subj'] as $subj)
            {
                $data['intEquivalentID'] = $subj;
                $data['intSubjectID'] = $subject;
                $this->data_poster->post_data('tb_mas_equivalents',$data);
            }
         
        }
        
        $data['message'] = "Success";
        
        echo json_encode($data);
    }
    
    public function submit_days_subject()
    {
        $post = $this->input->post();
        $subject = $post['intSubjectID'];
        $this->data_poster->delete_days_subject($subject);
        
        if(isset($post['subj']))
        {
            

            foreach($post['subj'] as $subj)
            {
                $data['strDays'] = $subj;
                $data['intSubjectID'] = $subject;
                $this->data_poster->post_data('tb_mas_days',$data);
            }
         
        }
        
        $data['message'] = "Success";
        
        echo json_encode($data);
    }
    
    public function edit_submit_subject()
    {
        $post = $this->input->post();
        //print_r($post);
        $this->data_poster->log_action('Subjects','Updated a Subject '.$post['strCode'],'blue');
        $this->data_poster->post_data('tb_mas_subjects',$post,$post['intID']);
        redirect(base_url()."subject/edit_subject/".$post['intID']);
            
    }
    
    public function view_all_subjects()
    {
        
        $this->data['page'] = "view_subjects";
        $this->data['opentree'] = "subject";
        //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/subject_view",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/subjects_conf",$this->data); 
        //print_r($this->data['classlist']);
        
    }
    
    public function subject_viewer($id,$sem = null)
    {
        
        if($this->is_admin() || $this->is_registrar()){
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $active_sem = $this->data_fetcher->get_active_sem();
			//$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
            $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
			//$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
           
            $this->data['subjects'] = $this->data_fetcher->getSubjectPlain($id);
            
            $records = $this->data_fetcher->fetch_classlist_by_sujects($id,$this->data['active_sem']['intID']);
            
            foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['intID']);
                //print_r($record['schedule']);
                $this->data['classlist'][] = $record;
            }
            
            $sem_temp = $this->data['active_sem'];
            
            $this->data['grades_charts'] = [];
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/subject_viewer",$this->data);
            $this->load->view("common//footer",$this->data); 
            $this->load->view("common/subject_viewer_conf",$this->data);
            $this->load->view("subject_data_js",$this->data);
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    public function delete_subject()
    {
        if($this->is_admin() || $this->is_registrar()){
            $post = $this->input->post();
            $deleted = $this->data_poster->deleteSubject($post['id']);
            if($deleted)
            {
                $data['message'] = "success";
                $this->data_poster->log_action('Subject','Deleted a Subject '.$post['code'],'red');
            }
            else
                $data['message'] = "failed";
            echo json_encode($data);
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