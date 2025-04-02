<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Guidance extends CI_Controller {

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

        $userlevel = $this->session->userdata('intUserLevel');   
        $ip = $this->input->ip_address();        
        if($userlevel != 2 && $userlevel != 12 &&  $ip != "172.16.80.22")
		  redirect(base_url()."unity");
        
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
        $this->data['campus'] = $this->config->item('campus');
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
        $this->data['page'] = "classroom";
    }
    
    public function guidance_records($id = 0)
    {        
        
        if($id == 0){
            $post = $this->input->post();
            $this->data['id'] = $post['studentID'];
        }
        else
            $this->data['id'] = $id;

        $this->data['page'] = "guidance_records";
        $this->data['opentree'] = "guidance";
        $this->load->view("common/header",$this->data);
        $this->load->view("guidance_records",$this->data);
        $this->load->view("common/footer",$this->data);             
        //print_r($this->data['classlist']);
        
    }

    public function guidance_records_data($id){

        $ret['student'] =  $this->data_fetcher->getStudent($id);

        switch($ret['student']['level']){
            case 'shs':
                $stype = 'shs';
            break;
            case 'drive':
                $stype = 'shs';
            break;
            case 'college':
                $stype = 'college';
            break;
            case 'other':
                $stype = 'college';
            break;
            default: 
                $stype = 'college';
        }

        
        $ret['guidance_records'] =  $this->db->get_where('tb_mas_guidance_records',array('patient_id'=>$id,'classification'=>$stype))->result_array();
        $ret['stype'] = $stype;
        echo json_encode($ret);
    }

    public function view_all_records()
    {
        
            $this->data['page'] = "view_records";
            $this->data['opentree'] = "guidance";            
            $this->load->view("common/header",$this->data);
            $this->load->view("records_view",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/records_view_conf",$this->data);

    }

    public function view_record($id,$type){
        if($type == "employee")
            redirect(base_url()."guidance/guidance_records_employee/".$id);
        else
            redirect(base_url()."guidance/guidance_records/".$id);
        
    }

    public function guidance_records_employee($id = 0)
    {        
        
        if($id == 0){
            $post = $this->input->post();
            $this->data['id'] = $post['studentID'];
        }
        else
            $this->data['id'] = $id;

        $this->data['page'] = "guidance_records_employee";
        $this->data['opentree'] = "guidance";
        $this->load->view("common/header",$this->data);
        $this->load->view("guidance_records_employee",$this->data);
        $this->load->view("common/footer",$this->data);             
        //print_r($this->data['classlist']);
        
    }

    public function guidance_records_employee_data($id){

        $ret['employee'] =  $this->data_fetcher->getFaculty($id);
        $stype = "employee";        
        $ret['guidance_records'] =  $this->db->get_where('tb_mas_guidance_records',array('patient_id'=>$id,'classification'=>$stype))->result_array();
        $ret['stype'] = $stype;
        echo json_encode($ret);
    }

    public function student_search(){
        $this->data['opentree'] = "guidance";
        $this->data['page'] = "guidance_records";              
        $this->load->view("common/header",$this->data);
        $this->load->view("search_student",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/search_student_conf",$this->data);
    }

    public function employee_search(){
        $this->data['opentree'] = "guidance";
        $this->data['page'] = "guidance_records_employee";              
        $this->load->view("common/header",$this->data);
        $this->load->view("search_employee",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/search_employee_conf",$this->data);
    }
    
    public function add_guidance_record(){
        $post = $this->input->post();        
        if($this->db->insert('tb_mas_guidance_records',$post)){
            $data['success'] = true;
            $data['message'] = "Successfully Added Record";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Failed to Add";
        }

        echo json_encode($data);
        
    }

    public function delete_guidance_record(){
        $post = $this->input->post();        
        $record = $this->db->where(array('id'=>$post['id']))->get('tb_mas_guidance_records')->first_row('array');        
        if($this->db->where(array('id'=>$post['id']))->delete('tb_mas_guidance_records')){
            $this->data_poster->log_action('Guidance','Deleted Record: '.$record['last_name'].", ".$record['first_name']." ".$record['chief_complaint'],'red');
            $data['success'] = true;
            $data['message'] = "Successfully Deleted Record";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Failed to Delete";
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