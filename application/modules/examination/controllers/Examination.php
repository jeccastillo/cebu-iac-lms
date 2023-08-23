<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examination extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        //User Level Validation
        
        $userlevel = $this->session->userdata('intUserLevel');   
        $ip = $this->input->ip_address();        
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 3 &&  $ip != "172.16.80.22")
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
        $this->data['campus'] = $this->config->item('campus');
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "subjects";

        $sem = $this->data_fetcher->get_active_sem();        
        $this->data['current_sem'] = $sem['intID'];
    }
    
    
    public function index() {
        $this->data['opentree'] = "examination";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/student_exam_list",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/student_exam_conf",$this->data); 
    }

    public function question_list() {
        $this->data['page'] = "view_questions";
        $this->data['opentree'] = "examination";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/question_list",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/question_conf",$this->data); 
    }

     public function add_question() {
        $this->data['page'] = "add_question";
        $this->data['opentree'] = "examination";
        $this->data['exam_type']= $this->data_fetcher->fetch_table('tb_mas_exam');
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/add_question",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/question_conf",$this->data);         
    }

     public function edit_question($id) {
        $this->data['opentree'] = "examination";
        $this->data['exam_type']= $this->data_fetcher->fetch_table('tb_mas_exam');
        $this->data['choices']= $this->data_fetcher->getChoice($id);
        $this->data['exam']= $this->data_fetcher->getExam($id);
        $this->data['question']= $this->data_fetcher->getQuestion($id);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_question",$this->data);
        $this->load->view("common/footer",$this->data); 
    }

    public function exam_type_list() {
        $this->data['page'] = "exam_type_list";
        $this->data['opentree'] = "examination";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/exam_type_list",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/exam_type_conf",$this->data); 
    }

     public function add_exam_type() {
         $this->data['page'] = "add_exam_type";
        $this->data['opentree'] = "examination";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/add_exam_type",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/student_exam_conf",$this->data); 
    }

     public function edit_exam_type($id) {
         $this->data['item']= $this->data_fetcher->getExam($id);
         $this->data['question']= $this->data_fetcher->getExamQuestion($id);
         $this->data['choices']= $this->data_fetcher->getExamQuestionChoice($id);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_exam_type",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/exam_type_conf",$this->data); 
    }

    public function submit_exam_type()
    {        
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $this->data_poster->log_action('Exam','Added a new exam '.$post['name'],'green');
            $this->data_poster->post_data('tb_mas_exam',$post);
            redirect(base_url()."examination/edit_exam_type/".$this->db->insert_id());
        }else
            redirect(base_url()."unity");
    }

    public function submit_edit_exam_type()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $this->data_poster->post_data('tb_mas_exam',$post,$post['intID']);
            $this->data_poster->log_action('Exam','Updated Exam Info: '.$post['name'],'green');
        }
        redirect(base_url()."examination/edit_exam_type/".$post['intID']);
    }

    public function delete_exam_type()
    {
        $data['message'] = "failed";
        $data['success'] = false;
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();            
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_exam',$post['id']);            
            $this->data_poster->deleteItem('tb_mas_exam',$post['id'],'intID');
            $this->data_poster->log_action('Exam','Deleted an exam: '.$info['strName'],'red');
            $data['message'] = "success";
            $data['success'] = true;
        }
        echo json_encode($data);
    }

    public function submit_question()
    {    
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $this->data_poster->log_action('Exam Question','Added a new question: '.$post['strTitle'],'green');
            $this->data_poster->post_data('tb_mas_questions',$post);
           redirect(base_url()."examination/edit_exam_type/".$post['exam_id']);
        }else
            redirect(base_url()."unity");
    }

    public function submit_edit_question()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $this->data_poster->post_data('tb_mas_questions',$post,$post['intID']);
            $this->data_poster->log_action('Exam Question','Updated Question Info: '.$post['name'],'green');
        }
        redirect(base_url()."examination/edit_question/".$post['intID']);
    }

    public function delete_question($id)
    {
        $data['message'] = "failed";
        $data['success'] = false;
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();            
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_questions',$post['id']);            
            $this->data_poster->deleteItem('tb_mas_questions',$id,'intID');
            $this->data_poster->deleteItem('tb_mas_choices',$id,'question_id');
            $this->data_poster->log_action('Question','Deleted a question: '.$info['strTitle'],'red');
            $data['message'] = "success";
            $data['success'] = true;
        }
        echo json_encode($data);
    }

    public function submit_choice()
    {    
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $this->data_poster->deleteItem('tb_mas_choices',$post['question_id'],'question_id');
            for($index = 0; $index < count($post['strChoice']); $index++){
                if($post['strChoice'][$index]){
                    $questionChoice = [];
                    $questionChoice['question_id'] = $post['question_id'];
                    $questionChoice['strChoice'] = $post['strChoice'][$index1];
                    $questionChoice['is_correct'] = $post['is_correct'] == $index ? 1 : 0;
                    $this->data_poster->post_data('tb_mas_choices',$questionChoice);
                    $this->data_poster->log_action('Choice','Added choices: '.$post['choice'],'green');
                }
            }
            redirect(base_url()."examination/edit_question/".$post['question_id']);
        }else
            redirect(base_url()."unity");
    }

    public function delete_choice()
    {
        $data['message'] = "failed";
        $data['success'] = false;
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();            
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_choices',$post['id']);
            $this->data_poster->deleteItem('tb_mas_choices',$post['id'],'intID');
            $this->data_poster->log_action('Choice','Deleted a choice: '.$info['choice'],'red');
            $data['message'] = "success";
            $data['success'] = true;
        }
        echo json_encode($data);
    }

    public function generate_exam()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $post['token'] = generateRandomString(10);
            $this->data_poster->post_data('tb_mas_student_exam',$post);
            $this->data_poster->log_action('Student Exam','Added a new student exam: '.$post['student_name'],'green');
            redirect(base_url()."examination/edit_exam_type/".$post['exam_id']);
        }else
            redirect(base_url()."unity");
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
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