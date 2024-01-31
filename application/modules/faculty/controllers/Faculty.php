<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Faculty extends CI_Controller {

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
        $this->data['page'] = "faculty";
    }
    
    
    public function add_faculty()
    {
        if($this->is_admin())
        {
            $this->data['page'] = "add_faculty";
            $this->data['opentree'] = "admin";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_faculty",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("faculty_validation_js",$this->data); 
        }
        else
            redirect(base_url()."unity"); 
        
    }
    
    public function edit_faculty($id)
    {
        
        if($this->is_super_admin())
        {
          
            $this->data['faculty'] = $this->data_fetcher->getFaculty($id);
            $this->data['subjects'] = $this->data_fetcher->getSubjects();
            $this->data['selectedSubjects'] = $this->data_fetcher->getSelectedSubjects($id);
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_faculty",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("faculty_validation_js",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
                
    }

    public function edit_faculty_data($id){

        $data['faculty'] = $this->data_fetcher->getFaculty($id);
        $data['subjects'] = $this->data_fetcher->getSubjects();
        $data['selectedSubjects'] = $this->data_fetcher->getSelectedSubjects($id);

        echo json_encode($data);

    }
    
    public function submit_faculty()
    {
        $post = $this->input->post();
        //print_r($post);
        $this->data_poster->log_action('Faculty','Added a new Faculty Member: '.$post['strFirstname']." ".$post['strLastname'],'aqua');        
        $post['strPass'] = password_hash($post['strPass'], PASSWORD_DEFAULT);
        $this->data_poster->post_data('tb_mas_faculty',$post);
        redirect(base_url()."faculty/edit_faculty/".$this->db->insert_id());
            
    }
    
    public function edit_submit_faculty()
    {
        $post = $this->input->post();
        //print_r($post);
        
        if($post['strPass'] && $post['strPass'] != "")            
            $post['strPass'] = password_hash($post['strPass'], PASSWORD_DEFAULT);
        else
            unset($post['strPass']);

        $this->data_poster->post_data('tb_mas_faculty',$post,$post['intID']);
        $this->data_poster->log_action('Faculty','Updated Faculty Info: '.$post['strFirstname']." ".$post['strLastname'],'aqua');
        
        
        redirect(base_url()."faculty/edit_faculty/".$post['intID']);
            
    }
    
    public function view_all_faculty()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_all_faculty";
            $this->data['opentree'] = "admin";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/faculty_view",$this->data);
            $this->load->view("common/footer_datatables",$this->data); 
            $this->load->view("common/faculty_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }

    public function view_all_teachers($sem = 0)
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            if($sem == 0){
                $sem = $this->data_fetcher->get_active_sem();
                $sem = $sem['intID'];
            }
                
            $this->data['sem'] = $sem;
            $this->data['page'] = "faculty_loading";
            $this->data['opentree'] = "registrar";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/teacher_view",$this->data);
            $this->load->view("common/footer_datatables",$this->data); 
            $this->load->view("common/teacher_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    
    public function get_online_users()
    {
        $data['users'] = $this->data_fetcher->getFacultyOnlineUsername();
        echo json_encode($data);
        
    }
    
    public function faculty_logged_in()
    {
        if($this->session->userdata('faculty_logged'))
            return true;
        else
            return false;
    }
    
    public function delete_faculty()
    {
        $data['message'] = "failed";
        
        if($this->is_admin()){
            $post = $this->input->post();
            $this->data_poster->deleteFaculty($post['id']);
            $this->data_poster->log_action('Faculty','Deleted a Faculty '.$post['fname'].' '.$post['lname'],'red');
            $data['message'] = "success";
        }
        echo json_encode($data);
    }
    
    public function faculty_viewer($id, $sem = null)
    {
        if($this->is_admin() || $this->is_department_head())
        { 
           $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
           //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $active_sem = $this->data_fetcher->get_active_sem();
                if($sem!=null)
                    $this->data['selected_ay'] = $sem;
                else
                    $this->data['selected_ay'] = $active_sem['intID'];

            $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
            $this->data['faculty'] = $this->data_fetcher->getFaculty($id);

            $this->data['selected_facID']= $this->data['faculty']['intID'];
            
            //$this->data['classlists'] = $this->data_fetcher->fetch_classlists_all(null,$this->data['selected_ay']);
            $records = $this->data_fetcher->fetch_classlist_by_faculty($id,$this->data['active_sem']['intID']);
            
            foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['intID']);
                //print_r($record['schedule']);
                $this->data['classlist'][] = $record;
            }

            $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/faculty_viewer",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/faculty_viewer_conf",$this->data); 
        }
        else
            redirect(base_url()."unity"); 
    }
    
    public function edit_profile()
    {
        if($this->faculty_logged_in())
        {
           // print_r($this->data['records']);
            $this->data['faculty'] = $this->data_fetcher->getFaculty($this->session->userdata('intID'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_profile",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("faculty_validation_js",$this->data); 
            
        }
        else
            redirect(base_url()."unity");    
    }
    
    public function my_profile($sem = null)
    {
        $this->data['page'] = "my_profile";
        if($this->faculty_logged_in())
        {
           $id = $this->session->userdata('intID');
           $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
           //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $active_sem = $this->data_fetcher->get_active_sem();
                if($sem!=null)
                    $this->data['selected_ay'] = $sem;
                else
                    $this->data['selected_ay'] = $active_sem['intID'];

            $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
            $this->data['faculty'] = $this->data_fetcher->getFaculty($id);
            //$this->data['classlists'] = $this->data_fetcher->fetch_classlists_all(null,$this->data['selected_ay']);
            $records = $this->data_fetcher->fetch_classlist_by_faculty($id,$this->data['active_sem']['intID']);

            foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['intID']);
                //print_r($record['schedule']);
                $this->data['classlist'][] = $record;
            }

            $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/my_profile",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/my_profile_conf",$this->data); 
            }
            else
                redirect(base_url()."unity");   
        
    }
    
    
    public function edit_submit_profile()
    {
        $post = $this->input->post();
        //print_r($post);
        
        
        if($post['strPass'] && $post['strPass'] != "")            
            $post['strPass'] = password_hash($post['strPass'],PASSWORD_DEFAULT);
        else
            unset($post['strPass']);


        $this->data_poster->post_data('tb_mas_faculty',$post,$post['intID']);

        $auth_data = $this->db->get_where('tb_mas_faculty', array('intID'=>$post['intID']), 1)->first_row();
        foreach($auth_data as $key=>$value)
        {
            $this->session->set_userdata($key,$value);
        }
        
        redirect(base_url().'faculty/my_profile');
            
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
    
    public function is_department_head()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 4)
            return true;
        else
            return false;
        
    }
    


}