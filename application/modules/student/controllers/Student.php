<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Student extends CI_Controller {

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
        $this->data['campus'] = $this->config->item('campus');
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
    }
    
    public function add_student()
    {
        if($this->is_registrar() || $this->is_super_admin() || $this->is_admissions())
        {   
            $this->data['page'] = "add_student";
            $this->data['opentree'] = "students";
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['yearStart'] = $active_sem['strYearStart'];
            switch($active_sem['enumSem'])
            {
                case "1st":
                    $sem = 1;
                    break;
                case "2nd":
                    $sem = 2;
                    break;
                case "3rd":
                    $sem = 3;
                    break;
                default:
                    $sem = 1;
            }
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['curriculum'] = $this->data_fetcher->fetch_table('tb_mas_curriculum');
            $this->data['subjects'] = $this->data_fetcher->get_subjects_by_course(2,$sem);
            
            if(!empty($this->data['subjects']))
                $this->data['sections'] = $this->data_fetcher->fetch_classlist_by_subject($this->data['subjects'][0]['intID'],$active_sem['intID']);
            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_student",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("student_validation_js",$this->data); 
            $this->load->view("common/add_student_conf",$this->data);
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function edit_student($id)
    {
        
        if($this->is_registrar() || $this->is_super_admin() || $this->is_admissions() || $this->is_department_head())
        {  
          
            $this->data['student'] = $this->data_fetcher->getStudent($id);            
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['curriculum'] = $this->data_fetcher->fetch_table('tb_mas_curriculum');
            $this->data['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('deduction_type'=>'scholarship'))->result_array();
            $this->data['discounts'] = $this->db->get_where('tb_mas_scholarships',array('deduction_type'=>'discount'))->result_array();
            $this->data['block_sections'] = $this->data_fetcher->fetch_table('tb_mas_block_sections');
            $this->data['tuition_years'] = $this->data_fetcher->fetch_table('tb_mas_tuition_year');
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_student",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("student_validation_js",$this->data); 
            $this->load->view("common/edit_student_conf",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }

    public function edit_student_scholarship($id = 0)
    {
        
        if($this->is_osas() || $this->is_super_admin() )
        {  
            $post = $this->input->post();
            
            if(isset($post['studentID']))
                $id = $post['studentID'];

            $this->data['page'] = "student_scholarship";
            $this->data['opentree'] = "osas";
          
            $this->data['student'] = $this->data_fetcher->getStudent($id);            
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['curriculum'] = $this->data_fetcher->fetch_table('tb_mas_curriculum');
            $this->data['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('deduction_type'=>'scholarship'))->result_array();
            $this->data['discounts'] = $this->db->get_where('tb_mas_scholarships',array('deduction_type'=>'discount'))->result_array();
            $this->data['block_sections'] = $this->data_fetcher->fetch_table('tb_mas_block_sections');
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_scholarship",$this->data);
            $this->load->view("common/footer",$this->data);             
            $this->load->view("common/edit_student_conf",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    public function update_passwords($sem,$program){
        $users = $this->db->get('tb_mas_users')
        ->join('tb_mas_registration','tb_mas_users.intID = tb_mas_registration.intStudentID')
        ->where(array('intROG'=>1,'intAYID'=>$sem,'intProgramID'=>$prog))
        ->result_array();
        foreach($users as $user){
            $newPass = pw_unhash($user['strPass']);
            $newPass = password_hash($newPass, PASSWORD_DEFAULT);
            $this->db->where('intID',$user['intID'])
                    ->update('tb_mas_users',array('strPass'=>$newPass));

            echo "updated ".$user['strFirstname'];
        }
        
    }
    
    public function view_all_students($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$sem=0,$scholarship=0,$registered=0,$level=0,$inactive='active')
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_students";
            $this->data['opentree'] = "students";
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();

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
            $this->data['inactive'] = $inactive;
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_view",$this->data);
            $this->load->view("common/footer",$this->data);
            
            
            $this->load->view("common/users_table_conf",$this->data);

            
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
     public function view_all_students2($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$sem=0,$scholarship=0,$registered=0)
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_students2";
            $this->data['opentree'] = "students";
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            
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
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_view2",$this->data);
            $this->load->view("common/footer",$this->data);
            
            
            $this->load->view("common/users_table_conf2",$this->data);

            
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function student_qr_search()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_students";
            $this->data['opentree'] = "students";
           // $this->data['offset'] = $offset;
            
            //$students = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
           // $this->data['registered'] = count($students);
            
            /*if($search == null)
                $this->data['students'] = $this->data_fetcher->fetch_students('tb_mas_users',array('strLastName','asc'),20,null,$offset);
            else {
              //put code for search algorithm
                $this->data['students'] = $this->data_fetcher->search_for_students();
            */
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_qr_search",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/qr_conf",$this->data);
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function submit_student()
    {
        $post = $this->input->post(); 
        
        $post['dteBirthDate'] = date("Y-m-d",strtotime($post['dteBirthDate']));
        
        //print_r($post);
        //
        
        $config['upload_path'] = './assets/photos';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']	= '400';
        $config['file_name'] = $post['strStudentNumber'].$post['strLastname'];
		$config['max_width']  = '1024';
		$config['max_height']  = '768';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload("strPicture"))
		{
            
            $data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $file['file_name'] = "";
            $post['strPicture'] = $file['file_name'];
            $this->data_poster->post_data('tb_mas_users',$post);  
            $ret['studentID'] = $this->db->insert_id();
            $this->data_poster->log_action('Students','Added a Student: '.$post['strFirstname']." ".$post['strLastname'],'green');
		      
            //redirect(base_url()."unity/view_all_students");
			//$error = array('error' => $this->upload->display_errors());
			//print_r($error);
            //$data = array('upload_data'
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $post['strPicture'] = $file['file_name'];
            $this->data_poster->post_data('tb_mas_users',$post);  
            $ret['studentID'] = $this->db->insert_id();
            $this->data_poster->log_action('Students','Added a Student: '.$post['strFirstname']." ".$post['strLastname'],'green');
		      
            //redirect(base_url()."unity/view_all_students");
        }
        
       
        redirect(base_url().'unity/student_viewer/'.$ret['studentID']);
        
        
            
    }
    
    public function delete_student()
    {
        if($this->is_super_admin()){
            $post = $this->input->post();
            $info = $this->data_fetcher->getStudent($post['id']);
            $this->data_poster->deleteStudent($post['id']);
            $data['message'] = "success";
            $this->data_poster->log_action('Student','Deleted a Student | Student Number:'.$info['strStudentNumber'].' Name: '.$info['strFirstname'].' '.$info['strLastname'],'red');
        }
        else
        {
            $data['message'] = "failed";
        }
        echo json_encode($data);
    }
    
    public function edit_submit_student()
    {
        $post = $this->input->post();
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$post['intID']))->first_row('array');
        $post['dteBirthDate'] = date("Y-m-d",strtotime($post['dteBirthDate']));
        if($post['date_of_graduation'] == ''){
            unset($post['date_of_graduation']);
        }
        
        //print_r($post);
        $config['upload_path'] = './assets/photos';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']	= '400';
        $config['file_name'] = $post['intID']."-".rand(1000,9999);
		$config['max_width']  = '1024';
		$config['max_height']  = '768';

		$this->load->library('upload', $config);
        
        if($post['strPass'] && $post['strPass'] != "")
            $post['strPass'] = password_hash($post['strPass'], PASSWORD_DEFAULT);
        else
            unset($post['strPass']);

        if($post['student_status'] != $student['student_status']){
            $this->data_poster->log_action('Leave of Abscences','Updated Status of '.$post['strFirstname']." ".$post['strLastname']." to ".$post['student_status'],'green');
        }

		if ( ! $this->upload->do_upload("strPicture"))
		{
			$this->session->set_flashdata('upload_errors',$this->upload->display_errors());
            $this->data_poster->post_data('tb_mas_users',$post,$post['intID']);
            $this->data_poster->log_action('Students','Updated a Student: '.$post['strFirstname']." ".$post['strLastname'],'green');
            redirect(base_url()."unity/student_viewer/".$post['intID']);
            
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $post['strPicture'] = $file['file_name'];
            $this->data_poster->post_data('tb_mas_users',$post,$post['intID']);
            $this->data_poster->log_action('Students','Updated a Student: '.$post['strFirstname']." ".$post['strLastname'],'green');
            redirect(base_url()."unity/student_viewer/".$post['intID']);
        }
    }
    
    public function view_all_registered_students()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_registered_students";
            $this->data['opentree'] = "students";
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
           // $this->data['offset'] = $offset;
            
            //$students = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
           // $this->data['registered'] = count($students);
            
            /*if($search == null)
                $this->data['students'] = $this->data_fetcher->fetch_students('tb_mas_users',array('strLastName','asc'),20,null,$offset);
            else {
              //put code for search algorithm
                $this->data['students'] = $this->data_fetcher->search_for_students();
            */
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_view_registered",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/users_table_conf_registered",$this->data);
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

    public function is_osas()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 7)
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