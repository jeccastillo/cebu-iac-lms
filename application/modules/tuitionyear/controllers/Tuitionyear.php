<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tuitionyear extends CI_Controller {

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

    public function add_tuition_year()
    {        
        
        $this->data['formAction'] = base_url()."tuitionyear/submit_form";
        $this->load->view("common/header",$this->data);
        $this->load->view("tuitionyear",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/tuitionyear_conf",$this->data);                              
       
    }
    
    public function submit_form()
    {
        $post = $this->input->post();
        
        // $config['upload_path'] = $this->docroot.'/assets/temp';
		// $config['allowed_types'] = 'gif|jpg|png|jpeg';
		// $config['max_size']	= '400';
        // $config['file_name'] = md5(date('Ymdhis'));
		// $config['max_width']  = '300';
        // $config['min_width']  = '300';
		// $config['max_height']  = '300';
        // $config['min_height']  = '300';

		// $this->load->library('upload', $config);


        
        
        $this->data['info'] = $post;
        print_r($post);        
        // $this->load->view("common/tuitionyear_header",$this->data);
        // $this->load->view("tuitionyear",$this->data);
        // $this->load->view("common/tuitionyear_conf",$this->data); 
        
    }
    
    
    
  
    
    public function config_all()
    {
        $this->validation_config =  array(
                array(
                        'field' => 'year',
                        'label' => 'Tuition Year Label',
                        'rules' => 'required'                        
                ),
                array(
                        'field' => 'pricePerUnit',
                        'label' => 'Price Per Unit',
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