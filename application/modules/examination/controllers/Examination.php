<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examination extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        //User Level Validation
        
        $userlevel = $this->session->userdata('intUserLevel');   
        $ip = $this->input->ip_address();        
        

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
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 3 &&  $ip != "172.16.80.22")
		  redirect(base_url()."unity");

        $this->data['opentree'] = "examination";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/student_exam_list",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/student_exam_conf",$this->data); 
    }

    public function question_list() {
        
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 3 &&  $ip != "172.16.80.22")
		  redirect(base_url()."unity");

        $this->data['page'] = "view_questions";
        $this->data['opentree'] = "examination";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/question_list",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/question_conf",$this->data); 
    }

     public function add_question() {
        
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 3 &&  $ip != "172.16.80.22")
		  redirect(base_url()."unity");

        $this->data['page'] = "add_question";
        $this->data['opentree'] = "examination";
        $this->data['exam_type']= $this->data_fetcher->fetch_table('tb_mas_exam');
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/add_question",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/question_conf",$this->data);         
    }

    public function edit_question($id) {
        
        if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 3 &&  $ip != "172.16.80.22")
		  redirect(base_url()."unity");

        $this->data['opentree'] = "examination";
        $this->data['exam_type']= $this->data_fetcher->fetch_table('tb_mas_exam');
        $this->data['choices']= $this->data_fetcher->getChoice($id);
        $this->data['exam']= $this->data_fetcher->getExam($id);
        $this->data['question']= $this->data_fetcher->getQuestion($id);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_question",$this->data);
        $this->load->view("common/footer",$this->data); 
    }

     public function student_generate_exam($term = 0)
    {
            if($userlevel != 2 && $userlevel != 5 && $userlevel != 6 && $userlevel != 3 &&  $ip != "172.16.80.22")
		    redirect(base_url()."unity");
       
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term);  
                
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
            
            $this->data['page'] = "student_generate_exam";
            $this->data['opentree'] = "examination";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/generate_exam",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/generate_exam_conf",$this->data); 
            //print_r($this->data['classlist']);
      
    }

    public function get_questions_per_section($id, $token, $student_id)
    {
        $student_exam = $this->db->get_where('tb_mas_student_exam',array('student_id'=>$student_id))->first_row('array');
        if($student_exam){
            if($student_exam['exam_id'] == $id){
                $student_exam_token = $this->db->get_where('tb_mas_student_exam',array('token'=>$token,))->first_row('array');
                if($student_exam_token){
                    $questions = $this->db->get_where('tb_mas_questions',array('exam_id'=>$id))->result_array('array');
                    $question_array = [];                
                    foreach($questions as $question){          
                          
                        $choices = $this->db->get_where('tb_mas_choices',array('question_id'=>$question['intID']))->result_array();
                        
                        $choice_array = [];
                        foreach($choices as $choice){
                            $choice_array[] = array(
                                'id' => $choice['intID'],
                                'choice' => $choice['strChoice'],
                                'choice_image' => $choice['choiceImage'] ? base_url() . 'assets/photos/exam/' . $choice['choiceImage'] : '',
                                'is_selected'=>0,
                            );
                        }
                        $question_array[] = array(
                            'id' => $question['intID'],
                            'title'=> $question['strTitle'],
                            'section'=> $question['strSection'],
                            'image' => $question['questionImage'] ? base_url() . 'assets/photos/exam/' .$question['questionImage'] : '',
                            'choices'=> $choice_array
                        );
                        
                    }
                    
                    $section = array(
                        'section' => 'I',
                        'question' => $question_array,  
                        'success' => true,          
                    );
                }else{
                    $section = array(
                        'section' => [],
                        'question' => [],
                        'message' => 'Exam already taken.',
                        'success' => false,  
                    );
                }
            }else{
                $section = array(
                    'section' => [],
                    'question' => [],
                    'message' => 'Invalid exam link.',
                    'success' => false,  
                );
            }
        }else{
            $section = array(
                'section' => [],
                'question' => [],
                'message' => 'Invalid exam link.',
                'success' => false,  
            );
        }


        echo json_encode($section);
        
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
        $this->data['exam_id'] = $id;
        $this->data['item']= $this->data_fetcher->getExam($id);
        $this->data['question']= $this->data_fetcher->getExamQuestion($id);
        $this->data['choices']= $this->data_fetcher->getExamQuestionChoice($id);
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_exam_type",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/question_conf",$this->data); 
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
        $post = $this->input->post();
        if($this->is_super_admin() || $this->is_admissions()){
            $config['upload_path'] = './assets/photos/exam';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size']	= '400';
            $config['file_name'] = rand(1000,9999);
            $config['max_width']  = '1024';
            $config['max_height']  = '768';
            $this->load->library('upload', $config);
            if ( ! $this->upload->do_upload("questionImage")){
                $this->session->set_flashdata('upload_errors',$this->upload->display_errors());
                $post['questionImage'] = '';
                $this->data_poster->log_action('Exam Question','Added a new question: '.$post['strTitle'],'green');
                $this->data_poster->post_data('tb_mas_questions',$post);
                redirect(base_url()."examination/edit_question/".$this->db->insert_id());
            }else{
                $data = array('upload_data' => $this->upload->data());
                $file = $this->upload->data();
                $post['questionImage'] = $file['file_name'];
                $this->data_poster->log_action('Exam Question','Added a new question: '.$post['strTitle'],'green');
                $this->data_poster->post_data('tb_mas_questions',$post);
                redirect(base_url()."examination/edit_question/".$this->db->insert_id());
            }
        }else
            redirect(base_url()."unity");
    }

    public function submit_edit_question()
    {
        $post = $this->input->post();
        if($this->is_super_admin() || $this->is_admissions()){
            $config['upload_path'] = './assets/photos/exam';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size']	= '400';
            $config['file_name'] = rand(1000,9999);
            $config['max_width']  = '1024';
            $config['max_height']  = '768';
            $this->load->library('upload', $config);
            if ( ! $this->upload->do_upload("questionImage")){
                $this->session->set_flashdata('upload_errors',$this->upload->display_errors());
                $this->data_poster->log_action('Exam Question','Updated Question Info: '.$post['name'],'green');
                $this->data_poster->post_data('tb_mas_questions',$post,$post['intID']);
                redirect(base_url()."examination/edit_question/".$post['intID']);
            }else{
                $data = array('upload_data' => $this->upload->data());
                $file = $this->upload->data();
                $post['questionImage'] = $file['file_name'];
                $this->data_poster->log_action('Exam Question','Updated Question Info: '.$post['name'],'green');
                $this->data_poster->post_data('tb_mas_questions',$post, $post['intID']);
                redirect(base_url()."examination/edit_question/".$post['intID']);
            }
        }else
            redirect(base_url()."unity");
    }
    
    public function delete_image_question($intID)
    {     
        $post['questionImage'] = '';
        $this->data_poster->post_data('tb_mas_questions',$post, $intID);
        redirect(base_url()."examination/edit_question/".$intID);
    }

    public function delete_question($id,$exam_id)
    {
        $data['message'] = "failed";
        $data['success'] = false;
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();            
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_questions',$id);            
            $this->data_poster->deleteItem('tb_mas_questions',$id,'intID');
            $this->data_poster->deleteItem('tb_mas_choices',$id,'question_id');
            $this->data_poster->log_action('Question','Deleted a question: '.$info['strTitle'],'red');
            redirect(base_url()."examination/edit_exam_type/".$exam_id);
        }
        echo json_encode($data);
    }

    public function submit_choice()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            
            $files = $this->reArrayFiles($_FILES['choiceImage']);

            $config['upload_path'] = './assets/photos/exam';
            $config['allowed_types'] = 'gif|jpg|png';
            // $config['max_size']	= '400';
            $config['file_name'] = rand(1000,9999);
            // $config['max_width']  = '1024';
            // $config['max_height']  = '768';
            $this->load->library('upload', $config);

            $choiceCount = count($post['strChoice']);
            $i = 0;
            foreach($post['strChoice'] as $choice){

                if($post['choiceID'][$i]){
                    $questionID = $post['choiceID'][$i];
                    //update choice
                    if ( ! $this->upload->do_upload_original_name($files[$i], $questionID)){

                        $this->session->set_flashdata('upload_errors',$this->upload->display_errors());
                        
                        $questionChoice = array(
                            'question_id'=>$post['question_id'],
                            'strChoice'=>$choice,
                            'is_correct'=>$post['is_correct'][$i],
                        );                   
                        $this->data_poster->post_data('tb_mas_choices',$questionChoice,$post['choiceID'][$i]);
                        $this->data_poster->log_action('Choice','Update choice: '.$post['strChoice'],'green');
                    }else{
                        $file = $this->upload->data();
                        $file = $questionID . '' . preg_replace('/\s+/', '_', $files[$i]['name']);
                        $questionChoice = array(
                            'question_id' => $post['question_id'],
                            'strChoice' => $choice,
                            'choiceImage' => $file,
                            'is_correct' => $post['is_correct'][$i],
                        );                  
                        $this->data_poster->post_data('tb_mas_choices',$questionChoice,$post['choiceID'][$i]);
                        $this->data_poster->log_action('Choice','Update choice: '.$post['strChoice'],'green');
                    }
                }else{
                    $questions = $this->db->order_by('intID','DESC')->get('tb_mas_choices')->first_row('array');
                    $questionID = $questions['intID'] + 1;
                    //add choice
                    if ( ! $this->upload->do_upload_original_name($files[$i], $questionID)){
                        $this->session->set_flashdata('upload_errors',$this->upload->display_errors());

                        $questionChoice = array(
                            'question_id'=>$post['question_id'],
                            'strChoice'=>$choice,
                            'is_correct'=>$post['is_correct'][$i]
                            ,
                        );                   
                        $this->data_poster->post_data('tb_mas_choices',$questionChoice);
                        $this->data_poster->log_action('Choice','Added choice: '.$post['strChoice'],'green');
                    }else{
                        $file = $questionID . '' . preg_replace('/\s+/', '_', $files[$i]['name']);
                        
                        $questionChoice = array(
                            'question_id' => $post['question_id'],
                            'strChoice' => $choice,
                            'choiceImage' => $file,
                            'is_correct' => $post['is_correct'][$i],
                        );                   
                        $this->data_poster->post_data('tb_mas_choices',$questionChoice);
                        $this->data_poster->log_action('Choice','Added choice: '.$post['strChoice'],'green');
                    }
                }
                $i++;
            }
            redirect(base_url()."examination/edit_question/".$post['question_id']);
        }else
            redirect(base_url()."unity");
    }
    
    function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);
    
        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }
    
        return $file_ary;
    }

    public function delete_choice()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();            
        
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_choices',$post['choice_id']);
            $this->data_poster->deleteItem('tb_mas_choices',$post['choice_id'],'intID');
            $this->data_poster->log_action('Choice','Deleted a choice: '.$info['strChoice'],'red');
            redirect(base_url()."examination/edit_question/".$info['question_id']);
        }
        echo json_encode($data);
    }

    public function delete_image_choice($questionID, $intID)
    {
        $post['choiceImage'] = NULL;
        $this->data_poster->post_data('tb_mas_choices',$post, $intID);
        redirect(base_url()."examination/edit_question/".$questionID);
    }

    public function generate_exam()
    {
        if($this->is_super_admin() || $this->is_admissions()){

            $post = $this->input->post();
            $isGenerated = $this->db->get_where('tb_mas_student_exam',array('student_id'=>$post['student_id']))->first_row('array');

            if(!$isGenerated){
                $sem = $this->data_fetcher->get_active_sem();
                $applicant = array(
                    'student_name' => $post['student_name'],
                    'student_id' => $post['student_id'],
                    'exam_id' => $post['exam_id'],
                    'syid' => $sem['intID'],
                    'token' => $this->generateRandomString(),
                    'score' => '0',
                );       
                $this->data_poster->post_data('tb_mas_student_exam',$applicant);
                $this->data_poster->log_action('Student Exam','Added a new student exam: '.$post['student_name'],'green');
                $data['message'] = "Successfully generated.";
                $data['success'] = true;
            }else{
                $data['message'] = "Exam already generated.";
                $data['success'] = false;
            }
        }
        echo json_encode($data);
    }

    public function generate_exam_links()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $applicants = json_decode($post['applicant'], true);

            foreach($applicants as $post){

                $program = $this->db->get_where('tb_mas_programs',array('strProgramDescription'=>$post['program']))->first_row('array');
                if($program){
                    $examTypes = $this->data_fetcher->fetch_table('tb_mas_exam');
                    
                    foreach($examTypes as $examType){
                        // if($program['school'] == $examType['programType']){
                        if((($program['school'] == 'Computing' || $program['school'] == 'computing') && $examType['programType'] == 'computing') ||
                        (($program['school'] == 'Business' || $program['school'] == 'business') && $examType['programType'] == 'business') ||
                        (($program['school'] == 'Design' || $program['school'] == 'design') && $examType['programType'] == 'design') ||
                        (($program['type'] == 'shs' || $program['school'] == 'iacademy') && $examType['programType'] == 'shs')    
                        ){
                            $isGenerated = $this->db->get_where('tb_mas_student_exam',array('student_id'=>$post['slug']))->first_row('array');
                            if(!$isGenerated){
                                $sem = $this->data_fetcher->get_active_sem();
                                $applicant = array(
                                    'student_name' => $post['first_name'] . ' ' . $post['last_name'],
                                    'student_id' => $post['slug'],
                                    'exam_id' => $examType['intID'],
                                    'syid' => $sem['intID'],
                                    'token' => $this->generateRandomString(),
                                    'score' => '0',
                                );
                                $this->data_poster->post_data('tb_mas_student_exam',$applicant);
                                $this->data_poster->log_action('Student Exam','Added a new student exam: '. $post['first_name'] . ' ' . $post['last_name'],'green');
                                $data['message'] = "Successfully generated.";
                                $data['success'] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    public function generate_exam_link()
    {
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();
            $applicants = json_decode($post['applicant'], true);
            $examID = $post['exam_id'];
            $programType = $post['programType'];

            foreach($applicants as $post){
                $program = $this->db->get_where('tb_mas_programs',array('strProgramDescription'=>$post['program']))->first_row('array');
                if($program){
                    $examType = $this->db->get_where('tb_mas_exam',array('programType'=>$programType, 'intID'=> $examID))->first_row('array');
                    if($examType){
                        // if($program['school'] == $examType['programType']){
                        //temporary checking for tb_mas_programs
                        if((($program['school'] == 'Computing' || $program['school'] == 'computing') && $examType['programType'] == 'computing') ||
                        (($program['school'] == 'Business' || $program['school'] == 'business') && $examType['programType'] == 'business') ||
                        (($program['school'] == 'Design' || $program['school'] == 'design') && $examType['programType'] == 'design') ||
                        (($program['type'] == 'shs' || $program['school'] == 'iacademy') && $examType['programType'] == 'shs')    
                        ){
                            $isGenerated = $this->db->get_where('tb_mas_student_exam',array('student_id'=>$post['slug']))->first_row('array');
                            if(!$isGenerated){
                                $sem = $this->data_fetcher->get_active_sem();
                                $applicant = array(
                                    'student_name' => $post['first_name'] . ' ' . $post['last_name'],
                                    'student_id' => $post['slug'],
                                    'exam_id' => $examType['intID'],
                                    'syid' => $sem['intID'],
                                    'token' => $this->generateRandomString(),
                                    'score' => '0',
                                );
                                $this->data_poster->post_data('tb_mas_student_exam',$applicant);
                                $this->data_poster->log_action('Student Exam','Added a new student exam: '. $post['first_name'] . ' ' . $post['last_name'],'green');
                                $data['message'] = "Successfully generated.";
                                $data['success'] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function delete_exam()
    {
        $data['message'] = "failed";
        $data['success'] = false;
        if($this->is_super_admin() || $this->is_admissions()){
            $post = $this->input->post();            
            $info = $this->data_fetcher->fetch_single_entry('tb_mas_student_exam',$post['id']);            
            $this->data_poster->deleteItem('tb_mas_student_exam',$post['id'],'intID');
            $this->data_poster->log_action('Exam','Deleted an exam: '.$info['student_name'],'red');
            $data['message'] = "success";
            $data['success'] = true;
        }
        echo json_encode($data);
    }
    
    public function submit_exam()
    {
        $post = $this->input->post();
        
        $examQuestions = json_decode($post['question'], true);
        print_r($examQuestions);
        die();
        
        $totalScore = $totalOverallScore = 0;
        $sectionArray = array();
        foreach($examQuestions as $examQuestion){
            foreach($examQuestion['choices'] as $choice){
                if($choice['is_selected'] == '1'){
                    $checkChoice = $this->db->get_where('tb_mas_choices',array('intID'=>$choice['id']))->first_row('array');
                    if($checkChoice['is_correct'] == '1'){
                        if(isset($sectionArray[$examQuestion['section']]['score']))
                            $sectionArray[$examQuestion['section']]['score'] += 1;
                        else
                            $sectionArray[$examQuestion['section']]['score'] = 1;
                        $totalScore++;
                    }
                    if(isset($sectionArray[$examQuestion['section']]['exam_overall']))
                        $sectionArray[$examQuestion['section']]['exam_overall'] += 1;
                    else
                        $sectionArray[$examQuestion['section']]['exam_overall'] = 1;
                    $sectionArray[$examQuestion['section']]['section'] = $examQuestion['section'];
                }
            }
            $totalOverallScore++;
        }

        $examArray = array(
            'date_taken'=> date("Y-m-d h:i:s"),
            'score' => $totalScore,
            'exam_overall' => $totalOverallScore,
            'token'=>'',
        );
        $this->data_poster->post_data('tb_mas_student_exam', $examArray, $post['student_id']);

        //save score per section
        foreach($sectionArray as $secArray){
            $totalOverallScore += $secArray['exam_overall'];
            if(!isset($secArray['score'])){
                $secArray['score'] = 0;
            }
            $examID = $this->db->get_where('tb_mas_student_exam',array('student_id'=>$post['student_id']))->first_row('array');

            $scoreArray = array(
                'tb_mas_student_exam_id' => $examID['intID'],
                'score'=> $secArray['score'],
                'exam_overall' => $secArray['exam_overall'],
                'percentage'=> ($secArray['score'] / $secArray['exam_overall']) * 100,
                'section' => $secArray['section'],
            );
            $this->data_poster->post_data('tb_mas_student_exam_score_per_section', $scoreArray);
        }
        
        $data['message'] = "You have successfully finished your Exam, your score is now being recorded. Good luck!";
        $data['success'] = true;

        echo json_encode($data);
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