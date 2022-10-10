<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admissions extends CI_Controller {

    public $validation_config = [];
    
	public $docroot;
    
    public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
		
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";
        
        //echo $docroot;
        
        switch ($_SERVER['DOCUMENT_ROOT'])
        {
            CASE '/home/cityco9/public_html/unity':
                $this->docroot = "/home/cityco9/public_html/unity";                
                break;
            
            CASE '/home/cityco9/public_html/portal':
                $this->docroot = "/home/cityco9/public_html/portal";                
                break;
                
            CASE '/home/cityco9/public_html/dev':
                $this->docroot = "/home/cityco9/public_html/dev";                
                break;
            
            CASE '/var/www/html':
                $this->docroot = "/var/www/html/cctUnityTesting";                
                break;
            
            CASE 'C:/xampp/htdocs':
                $this->docroot = "C:/xampp/htdocs/cctunity";                
                break;
            
        }
        
        
        $this->data['student_pics'] = "https://portal.citycollegeoftagaytay.edu.ph/assets/photos/";
        $this->data['temp_pics'] = base_url()."assets/temp/";
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
        
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = array();
        
        $this->load->library('parser');
        
        $this->data['all_messages'] = array();
        
        $this->data['trashed_messages'] = array();
        
        $this->data['sent_messages'] = array();
        
        $this->config_all();
        
        
    }
    
    public function signup_form2()
    {
        echo "<h1>Due to an unexpected high volume of users sign up will be under maintenance until further notice. Thank you for being patient";
    }
    
    public function signup_form()
    {
        
        $date = date("Y-m-d");
        $date2 = "2020-07-08"; //change this date to adjust start date
        $date3 = "2020-07-11"; //change this date to adjust end date
        $this->data['date'] = $date2;
        $this->data['date_end'] = $date3;
        
        if(strtotime($date) < strtotime($date2)){
            $this->load->view("common/signup_header",$this->data);
            $this->parser->parse('admin/signup_form_message', $this->data);                 
            
        }
        elseif(strtotime($date) >= strtotime($date3)){
            $this->load->view("common/signup_header",$this->data);
            $this->parser->parse('admin/signup_form_message_end', $this->data);
        }
        else{
            $provinces = $this->data_fetcher->fetch_table('refprovince',array('provDesc','ASC'));
            $pr = array(0=>'----SELECT PROVINCE----');
            foreach($provinces as $prov)
            {
                $pr[$prov['provCode']] = $prov['provDesc'];
            }
            $this->data['provinces'] = $pr;
            $this->data['formAction'] = base_url()."admissions/submit_form";
            $this->load->view("common/signup_header",$this->data);
            $this->parser->parse('admin/signup_form', $this->data);             
            $this->load->view("common/admissions_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
            
       
    }
    
    public function signup_form_back()
    {
        
        $this->data['info'] = $this->input->post();
        if($this->data['info']['strAppPicture']!="")
            unlink($this->docroot.'/assets/temp/'.$this->data['info']['strAppPicture']);
        //print_r($this->data['info']);
        
        $provinces = $this->data_fetcher->fetch_table('refprovince',array('provDesc','ASC'));
        $pr = array(0=>'----SELECT PROVINCE----');
        foreach($provinces as $prov)
        {
            $pr[$prov['provCode']] = $prov['provDesc'];
        }
        $this->data['provinces'] = $pr;
        $this->data['formAction'] = base_url()."admissions/submit_form";
        $this->load->view("common/signup_header",$this->data);
        $this->parser->parse('admin/signup_form', $this->data);
        $this->load->view("common/admissions_conf",$this->data); 
        //print_r($this->data['classlist']);
            
    }
    
    public function confirm_code_form()
    {
        $this->load->view("common/signup_header",$this->data);
        $this->parser->parse('admin/submit_confirmation', $this->data);             
        $this->load->view("common/submit_confirmation_conf",$this->data); 
        
    }
    
    public function delete_application_data()
    {
        if($this->is_super_admin() || $this->is_admissions())
        {
            $id = $this->input->post('id');

            $this->data_poster->deleteItem('tb_mas_applications',$id,'intApplicationID');
            $this->data_poster->deleteItem('tb_mas_exam_info',$id,'intApplicationID');
        }
        
        echo json_encode(array("message"=>"success"));
    }
    
    public function confirm_code()
    {
        $post = $this->input->post();
        
        $app = [];
        
        if($post['code'] != '0'){            
            $app = $this->data_fetcher->getApplicationByCode($post['code']);
        }
        
        $ret = [];
        
        
        if(isset($app['intApplicationID']))
        {
            $this->data_poster->updateApplicationCode($app['intApplicationID']);
            $this->data_poster->updateExamConfirmation($app['intApplicationID']);
            $ret['message'] = 1;
            $ret['msg'] = "Code successfully confirmed";
        }
        else{
            $ret['message'] = 0;
            $ret['msg'] = "Failed to confirm code, the code you entered does not match any of the applications or is already used";
        }
            
        echo json_encode($ret);
    }
    
    public function confirm_form()
    {

        $post = $this->input->post();
        $file = $this->docroot.'/assets/temp/'.$post['strAppPicture'];
        $newfile = $this->docroot.'/assets/photos/'.$post['strAppPicture'];
        
        copy($file, $newfile);

        unlink($file);

        $active_sem = $this->data_fetcher->get_active_sem();
        
        $post['strAppDate'] = date("Y-m-d h:i:s");
        $post['strAppNumber'] = $this->data_fetcher->generateAppNumber(date('Y'));
        $post['strConfirmationCode'] = $this->data_fetcher->generateConfirmationCode(date('Y'));
        $post['dteAppBirthdate'] = date("Y-m-d h:i:s",strtotime($post['dteAppBirthdate']));
        $post['dteScheduleExam'] = date("Y-m-d h:i:s",strtotime($post['dteScheduleExam']));
        $post['enumSem'] = $active_sem['intID'];
        $this->data_poster->post_data('tb_mas_applications',$post);
        
        $this->session->set_flashdata('appLName',$post['strLastname']);
        $this->session->set_flashdata('appFName',$post['strFirstname']);
        $this->session->set_flashdata('appMName',$post['strMiddlename']);
        $this->session->set_flashdata('appNumber',$post['strAppNumber']);
        $this->session->set_flashdata('email',$post['strAppEmail']);
        $this->session->set_flashdata('examSched',$post['dteScheduleExam']);
        
        $exam['intApplicationID'] = $this->db->insert_id();
        $this->data_poster->post_data('tb_mas_exam_info',$exam);
        
        redirect(base_url().'admissions/success_page');
    
    }
    
    public function success_page()
    {
        $this->data['appLName'] = $this->session->flashdata("appLName");
        $this->data['appFName'] = $this->session->flashdata("appFName");
        $this->data['appMName'] = $this->session->flashdata("appMName");
        $this->data['appNumber'] = $this->session->flashdata("appNumber");
        $this->data['email'] = $this->session->flashdata("email");
        //$this->data['examDate'] = date("M j,Y",strtotime($this->session->flashdata("examSched")));
        
        $this->load->view("common/signup_header",$this->data);
        $this->parser->parse('admin/admission_success', $this->data);             
        $this->load->view("common/admissions_conf",$this->data); 
    }
    
    public function submit_form()
    {
        $post = $this->input->post();
        
        $config['upload_path'] = $this->docroot.'/assets/temp';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['max_size']	= '400';
        $config['file_name'] = md5(date('Ymdhis'));
		$config['max_width']  = '300';
        $config['min_width']  = '300';
		$config['max_height']  = '300';
        $config['min_height']  = '300';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload("strAppPicture"))
		{
            
            $data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $this->data['file_error'] = $this->upload->display_errors();
            $file['file_name'] = "";
            $post['strAppPicture'] = $file['file_name'];
              
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $this->data['file_error'] = "";
            $post['strAppPicture'] = $file['file_name'];
              
            //redirect(base_url()."unity/view_all_students");
        }
        
        
//        $post['course1Desc'] = $this->data_fetcher->getCourseName($post['enumCourse1']);
//        $post['course2Desc'] = $this->data_fetcher->getCourseName($post['enumCourse2']);        
//        $course3 = $this->data_fetcher->getCourseName($post['enumCourse3']);
//        $post['course3Desc'] = ($course3=="")?'None':$course3;
        
        $post['provinceDesc'] = $this->data_fetcher->getGeneralDesc('refprovince',$post['strAppProvince'],'provCode','provDesc');
        $post['cityDesc'] = $this->data_fetcher->getGeneralDesc('refcitymun',$post['strAppCity'],'citymunCode','citymunDesc');
        $post['brgyDesc'] = $this->data_fetcher->getGeneralDesc('refbrgy',$post['strAppBrgy'],'brgyCode','brgyDesc');
        
        $this->data['info'] = $post;
        
        $this->load->view("common/signup_header",$this->data);
        $this->load->view("admin/signup_submit",$this->data);
        $this->load->view("common/admissions_conf",$this->data); 
        
    }
    
    
    
  
    
    public function config_all()
    {
        $this->validation_config =  array(
                array(
                        'field' => 'strFirstname',
                        'label' => 'Firstname',
                        'rules' => 'required'                        
                ),
                array(
                        'field' => 'strLastname',
                        'label' => 'Lastname',
                        'rules' => 'required'
                ),
                 array(
                        'field' => 'strAppPicture',
                        'label' => 'Picture',
                        'rules' => 'required',
                        'errors'=>array(
                            'required'=>'Choose a picture'
                        )
                ),/*
                array(
                        'field'=>'strAppLRN',
                        'label'=>'Learner Reference Number',
                        'rules'=>'required|numeric|exact_length[12]|is_unique[tb_mas_applications.strAppLRN]',
                        'errors'=>array(
                            'is_unique'=>'This LRN number has already been registered'
                        )
                ),*/
                array(
                        'field'=>'strAppEmail',
                        'label'=>'Email Address',
                        'rules'=>'required|valid_email|is_unique[tb_mas_applications.strAppEmail]|callback_email_check',
                        'errors'=>array(
                            'is_unique'=>'This Email Address has already been registered'
                        )
                ),
                array(
                        'field'=>'strAppPhoneNumber',
                        'label'=>'Contact Number',
                        'rules'=>'required|min_length[9]',
                ),
                array(
                        'field'=>'strAppProvince',
                        'label'=>'Province',
                        'rules'=>'required|numeric|greater_than[0]',
                        'errors'=>array(
                            'greater_than'=>'Please select a province'   
                        )
                ),
                array(
                        'field'=>'strAppCity',
                        'label'=>'City/Municipality',
                        'rules'=>'required|numeric|greater_than[0]',
                        'errors'=>array(
                            'greater_than'=>'Please select a city/municipality'   
                        )
                ),
                array(
                        'field'=>'strAppBrgy',
                        'label'=>'Barangay',
                        'rules'=>'required|numeric|greater_than[0]',
                        'errors'=>array(
                            'greater_than'=>'Please select a barangay'   
                        )
                ),
                array(
                        'field'=>'enumCourse1',
                        'label'=>'1st Choice',
                        'rules'=>'required',                        
                ),
                array(
                        'field'=>'enumCourse2',
                        'label'=>'2nd Choice',
                        'rules'=>'required',                        
                ),
                array(
                        'field' => 'strAppFather',
                        'label' => 'Father\'s Name',
                        'rules' => 'required'
                ),
                array(
                        'field' => 'strAppMother',
                        'label' => 'Mother\'s Name',
                        'rules' => 'required'
                ),
                array(
                        'field' => 'strAppAdress',
                        'label' => 'Address',
                        'rules' => 'required|min_length[5]'
                ),
                array(
                        'field' => 'strAppLastSchool',
                        'label' => 'Last School',
                        'rules' => 'required'
                ),
                array(
                        'field' => 'dteAppBirthdate',
                        'label' => 'Birthdate',
                        'rules' => 'required'
                ),
                array(
                        'field' => 'dteScheduleExam',
                        'label' => 'Exam Schedule',
                        'rules' => 'required'
                ),
                
            
            
               
        );
    }
    
    public function email_check($email)
	{
		$this->form_validation->set_message('email_check', 'Domain must gmail id');
		return strpos($email, '@gmail.com') !== false;
	}
    
    public function validate_form()
    {
        $ret = [];
        $ret['message'] = "";
        $ret['errors'] = [];
        $this->load->library('form_validation');
        
        
        $this->form_validation->set_rules($this->validation_config);

        if ($this->form_validation->run() == FALSE)
        {
                foreach($this->validation_config as $conf){
                    if(form_error($conf['field']))
                        $ret['errors'][$conf['field']] = form_error($conf['field']);
                }
                $ret['message'] =  "failed";
        }
        else
        {                
                $ret['message'] =  "success";
        }
        
        echo json_encode($ret);
    }
    
    public function validate_field()
    {
        $post = $this->input->post();
        
        $this->load->library('form_validation');
        $config = [];
        $ret['errors'] = [];
        foreach($this->validation_config as $conf)
            foreach($post as $key=>$val)
            {
                if($conf['field'] == $key)
                    $config[] = $conf;
            }
        if(!empty($config)){
            $this->form_validation->set_rules($config);
        
            if ($this->form_validation->run() == FALSE)
            {
                    if(isset($config[0]) && form_error($config[0]['field']))
                        $ret['errors'][$config[0]['field']] = form_error($config[0]['field']);

                    $ret['message'] =  "failed";
            }
            else
            {                
                    $ret['message'] =  "success";
            }
        }
        else
            $ret['message'] =  "success";
        
        echo json_encode($ret);
        
    }
    
    public function get_municipality()
    {
        $post = $this->input->post();
        $where = array('provCode'=>$post['code']);
        $municipality = $this->data_fetcher->fetch_table('refcitymun',array('citymunDesc','ASC'),null,$where);
        $pr = "<option value='0'>----SELECT CITY/MUNICIPALITY----</option>";
        foreach($municipality as $mun)
        {
            $pr .= "<option value='".$mun['citymunCode']."'>".$mun['citymunDesc']."</option>";
        }
    
        $ret['citymun'] = $pr;
        
        echo json_encode($ret);
    }
    
    public function get_courses()
    {
        $post = $this->input->post();        
        $courses = $this->data_fetcher->fetch_table('tb_mas_programs',array('strProgramDescription','ASC'),null,array('enumEnabled'=>1));
        $pr = "<option value=''>----SELECT COURSE----</option>";
        foreach($courses as $course)
        {
            $major = ($course['strMajor'] == 'None')?'':$course['strMajor'];
            //$pr .= "<option value='".$course['intProgramID']."'>".$course['strProgramDescription']." ".$major."</option>";
            $pr .= "<option value='".$course['strProgramCode']."'>".$course['strProgramCode']." - ".$course['strProgramDescription']." ".$major."</option>";
        }
    
        $ret['courses'] = $pr;
        
        echo json_encode($ret);
    }
    
    public function get_brgy()
    {
        $post = $this->input->post();
        $where = array('citymunCode'=>$post['code']);
        $municipality = $this->data_fetcher->fetch_table('refbrgy',array('brgyDesc','ASC'),null,$where);
        $pr = "";
        $pr = "<option value='0'>----SELECT BRGY----</option>";
        foreach($municipality as $mun)
        {
            $pr .= "<option value='".$mun['brgyCode']."'>".$mun['brgyDesc']."</option>";
        }
    
        $ret['citymun'] = $pr;
        
        echo json_encode($ret);
    }
    
    
    public function view_all_applicants($course = 0,$appdate=0,$gender=0,$sem=0)
    {
        if($this->is_super_admin() || $this->is_admissions())
        {
            $this->data['page'] = "view_applicants";
            $this->data['opentree'] = "applicants";
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
      
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['course'] = $course;
            $this->data['appdate'] = $appdate;
            //$this->data['confirmed'] = $confirmed;
            //$this->data['postyear'] = $year;
            $this->data['gender'] = $gender;
            //$this->data['graduate'] = $graduate;
            //$this->data['scholarship'] = $scholarship;
            //$this->data['registered'] = $registered;
            $this->data['sem'] = $sem;
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/applicant_view",$this->data);
            $this->load->view("common/footer",$this->data);            
            $this->load->view("common/admissions_table_conf",$this->data);
            //$this->load->view("common/users_table_conf",$this->data);

            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url());  
    }
    
    public function update_data($table,$field){
        
        $post = $this->input->post();
        $this->data_poster->post_data($table,$post,$post['intApplicationID'],$field);
        
        echo json_encode('success');
    }
    public function view_applicant($id)
    {
        if($this->is_super_admin() || $this->is_admissions())
        {
           
            $applicant = $this->data_fetcher->getApplicant($id);
            $exam = $this->data_fetcher->getExamInfo($id);

            $exam['isConfirmed'] = ($exam['isConfirmed'])?'Yes':'No';

//            $applicant['course1Desc'] = $this->data_fetcher->getCourseName($applicant['enumCourse1']);
//            $applicant['course2Desc'] = $this->data_fetcher->getCourseName($applicant['enumCourse2']);        
//            $course3 = $this->data_fetcher->getCourseName($applicant['enumCourse3']);
//            $applicant['course3Desc'] = ($course3=="")?'None':$course3;

            $this->data['applicant'] = $applicant;
            $this->data['exam'] = $exam;

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/applicant_viewer",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/admissions_js",$this->data);
        }
        else
            redirect(base_url());  

        
    }
    
    public function faculty_logged_in()
    {
        if($this->session->userdata('faculty_logged'))
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
    
    public function is_super_admin()
    {
         $admin = $this->session->userdata('intUserLevel');
        if($admin == 2)
            return true;
        else
            return false;
    }
   


}