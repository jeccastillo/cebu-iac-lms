<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Scholarship extends CI_Controller {

	
    function __construct() {
        parent::__construct();
        
        if(!$this->is_osas() && !$this->is_super_admin())
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
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Unity";
        $this->load->library("email");	       
        
                
        
        $this->data['page'] = "finance";
        
        //$this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        //$this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		

    }
    
    
    public function scholarships(){
        $this->data['page'] = "scholarships";
        $this->data['opentree'] = "scholarship";
        $this->load->view("common/header",$this->data);
        $this->load->view("scholarships",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/scholarship_list_conf",$this->data);
    }

    public function assign_scholarship($sem = 0){
        
        if($sem != 0)
            $ret['sem'] = $sem;
        else{
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }
        
        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['page'] = "assign_scholarship";
        $this->data['opentree'] = "scholarship";
                                                                

        $this->load->view("common/header",$this->data);
        $this->load->view("assign_scholarship",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function assign_scholarship_data($sem){

        $ret['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'scholarship'))->result_array();
        $ret['discounts'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discounts'))->result_array();
        $ret['terms'] = $this->db->get('tb_mas_sy')->result_array();
        
        $ret['student_deductions'] = $this->db->select('tb_mas_student_discounts.*,tb_mas_scholarships.deduction_type,tb_mas_scholarships.name,tb_mas_scholarships.description')
                                    ->where(array('syid'=>$sem))
                                    ->get('tb_mas_student_discounts')
                                     ->result_array();

        echo json_encode($ret);

    }

    public function view($id){
        $this->data['page'] = "add_scholarship";
        $this->data['opentree'] = "scholarship";
        $this->data['id'] = $id;
        $this->load->view("common/header",$this->data);
        $this->load->view("scholarship_view",$this->data);
        $this->load->view("common/footer",$this->data);
    }

    public function data($id){
        $data['scholarship'] = $this->db->get_where('tb_mas_scholarships',array('intID'=>$id))->row();        
        $data['status_options'] = get_enum_values('tb_mas_scholarships','status');
        $data['type_options'] = get_enum_values('tb_mas_scholarships','type');
        echo json_encode($data);

    }

    public function submit_form(){
        $post = $this->input->post();
        if($post['intID'] == 0){
            unset($post['intID']);
            $post['created_by_id'] =  $this->data["user"]["intID"];
            $this->db->insert('tb_mas_scholarships',$post);
            $data['id'] = $this->db->insert_id();
        }
        else{
            $this->db
				 ->where('intID',$post['intID'])
				 ->update('tb_mas_scholarships',$post);
            
            $data['id'] = $post['intID'];
        }
        $data['message'] = "Success";
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

    public function is_osas()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 7)
            return true;
        else
            return false;
        
    }
    
    public function is_department_head()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 4)
            return true;
        else
            return false;
        
    }
    
    public function is_accounting()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 6)
            return true;
        else
            return false;
        
    }



   }

