<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Blocksection extends CI_Controller {

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
        $this->data['page'] = "classroom";
    }
    
    public function block_section($id = 0)
    {        
        if($this->is_super_admin() || $this->is_registrar())
        {
            $this->data['id'] = $id;
            $this->data['page'] = "add_blocksection";
            $this->data['opentree'] = "registrar";
            $this->load->view("common/header",$this->data);
            $this->load->view("block_section",$this->data);
            $this->load->view("common/footer",$this->data);             
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");  
       
    }

    public function block_section_data($id){
        $section = $this->data_fetcher->fetch_single_entry('tb_mas_block_sections',$id);
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
        $active_sem = $this->data_fetcher->get_active_sem();

        $ret['data']['section'] = $section;        
        $ret['data']['active_sem'] = $active_sem;
        $ret['sy'] = $this->db->get('tb_mas_sy')->result_array();
        $ret['data']['programs'] = $programs;
        $ret['success'] = true;
        $ret['message'] = "success"; 
        
        echo json_encode($ret);

    }
    
    
    // public function classroom_viewer($id)
    // {
        
    //     if($this->is_admin())
    //     {
          
    //         $this->data['item'] = $this->data_fetcher->getClassroom($id);
    //         $active_sem = $this->data_fetcher->get_active_sem();
    //         $this->data['schedules'] = $this->data_fetcher->getScheduleByRoomID($id,$active_sem['intID']);
            
    //         $this->data['days'] = Array('1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat');
    //         $this->data['types'] = Array('lect','lab');
    //         $this->data['timeslots'] = Array('7:00','7:30','8:00','8:30','9:00','9:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00');
            
    //         $this->data['subjects'] = $this->data_fetcher->getSubjects();
            
    //         $this->load->view("common/header",$this->data);
    //         $this->load->view("admin/classroom_viewer",$this->data);
    //         $this->load->view("common/footer",$this->data); 
    //         $this->load->view("common/classroom_viewer_conf",$this->data); 
    //        // print_r($this->data['classlists']);
            
    //     }
    //     else
    //         redirect(base_url()."unity");    
        
        
    // }

    public function submit_block_section($id = 0){        
        $data['success'] = false;
        $data['message'] = "failed to add";
        $post = $this->input->post();  
        $section = $this->data_fetcher->fetch_single_entry('tb_mas_block_sections',$post['name'],'name');
        if($section && $id == 0){
            $data['message'] = "Section already exists";
        }
        elseif($this->is_super_admin() || $this->is_registrar())
        {                                         
            if($id != 0)                 
                $this->data_poster->post_data('tb_mas_block_sections',$post,$id);
            else
                $this->data_poster->post_data('tb_mas_block_sections',$post);

            $this->data_poster->log_action('Block Section','Updated/Added Section'.$post['name'],'green');
            $data['data'] = $post;
            $data['message'] = "successfully submitted";
            $data['success'] = true;
        }
        echo json_encode($data);
    }
    
    public function view_block_sections()
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            
            $this->data['page'] = "view_blocksection";
            $this->data['opentree'] = "registrar";            
            $this->load->view("common/header",$this->data);
            $this->load->view("block_section_view",$this->data);
            $this->load->view("common/footer",$this->data);     
            $this->load->view("common/block_section_datatables",$this->data);                         
            //print_r($this->data['classlist']);            
        }
        else
            redirect(base_url()."unity");   
    
    }

    public function block_section_viewer_data($id){
        $active_sem = $this->data_fetcher->get_active_sem();
        $data['schedule'] = $this->data_fetcher->getScheduleBySectionNew($id,$active_sem['intID']);
        $data['section'] = $this->data_fetcher->fetch_single_entry('tb_mas_block_sections',$id);
        
        $data['message'] = "success";
        $data['success'] = true;
        echo json_encode($data);
        
    }

    public function block_section_viewer($id){
        $this->data['id'] = $id;
        $this->data['page'] = "view_blocksection";
        $this->data['opentree'] = "registrar";
        $this->data['sched_table'] = $this->load->view('sched_table', $this->data, true);
        $this->load->view("common/header",$this->data);
        $this->load->view("block_section_viewer",$this->data);
        $this->load->view("common/footer",$this->data);             
    }
    
    public function delete_blocksection()
    {
        $data['message'] = "failed";
        $data['success'] = false;
        if($this->is_super_admin() || $this->is_registrar()){
            $post = $this->input->post();            
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_block_sections',$post['id']);            
            $this->data_poster->deleteItem('tb_mas_block_sections',$post['id'],'intID');
            $this->data_poster->log_action('Block Section','Deleted a Section '.$info['name'],'red');
            $data['message'] = "success";
            $data['success'] = true;
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