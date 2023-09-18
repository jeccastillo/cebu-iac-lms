<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Department extends CI_Controller {

	public function __construct()
	{
		parent::__construct();                

        if(!$this->is_registrar() && !$this->is_super_admin())
		  redirect(base_url()."unity");
        
		$this->config->load('themes');
        $this->config->load('schedule');
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
		$this->load->model("google_login");	
		$this->load->model("facebook_login");	
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
        
        //$this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        //$this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		
	}
    
    public function index()
	{	
        
        if($this->faculty_logged_in())
            redirect(base_url()."unity/faculty_dashboard");
        
        else
            redirect(base_url()."users/login");
        
        
	}
    
    
    public function subject_loading()
    {
       
        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['page'] = "advise_student";
        $this->data['opentree'] = "department";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/advise_student",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/advise_student_conf",$this->data);
        
        //print_r($this->data['classlist']);
            
       
    }
    
    public function faculty_loading()
    {
        
        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['page'] = "faculty_loading";
        $this->data['opentree'] = "department";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/faculty_search",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/faculty_search_conf",$this->data);
                
    }
    
    public function add_credits()
    {
        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['page'] = "add_credits";
        $this->data['opentree'] = "department";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/add_credits",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/advise_student_conf",$this->data);
        
        //print_r($this->data['classlist']);
                    
    }
    
    public function student_function($f = "rog")
    {
        
        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        
        $this->load->view("common/header",$this->data);
        switch($f)
        {
            case 'rog':
                $this->load->view("admin/rog_student",$this->data);
            break;
            case 'assessment':
                $this->load->view("admin/curriculum_assessment",$this->data);
            break;
            
        }
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/advise_student_conf",$this->data);
        
        //print_r($this->data['classlist']);0        
    }
    public function crediting($id=null)
    {
        
        $post = $this->input->post();
        if(!empty($post))
            $id = $post['studentID'];
            
        //$this->data['sy'] = $this->data_fetcher->getSemStudent($id);

        $this->data['errors'] = $this->session->flashdata('upload_errors');
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        
        
        $this->data['student'] = $this->data_fetcher->getStudent($id);
    
        $this->data['subjects_not_taken'] = $this->data_fetcher->getSubjectsInCurriculum($this->data['student']['intCurriculumID']);
        
        $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$this->data['active_sem']['intID']);
        
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
        
        $this->data['credited_subjects'] = $this->data_fetcher->getCreditedSubjects($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
        
        
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/crediting",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/crediting_conf",$this->data); 
        $this->load->view("crediting_validation_js",$this->data); 
        
    }
    public function submit_crediting()
    {
        $post = $this->input->post();
        if($post['floatFinalGrade'] == "5")
            $post['strRemarks'] = "Failed";
        elseif($post['floatFinalGrade'] == "3.5")
            $post['strRemarks'] = "lack of reqts.";
        else
            $post['strRemarks'] = "Passed";
        
        $this->data_poster->post_data('tb_mas_credited_grades',$post);
        redirect(base_url().'department/crediting/'.$post['intStudentID']);
    }

    public function load_subjects_data($studNum,$sem){

        //$this->data['active_sem'] = $this->data_fetcher->get_processing_sem();
        $data['student'] = $this->data_fetcher->getStudent($studNum);
        switch($data['student']['level']){
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
        
        
        $data['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        $data['sy'] = $this->db->get_where('tb_mas_sy',array('term_student_type'=>$stype))->result_array();
        
        $data['prev_sem'] = $this->data_fetcher->get_prev_sem($data['active_sem']['intID'],$studNum);
        $data['selected_ay'] = $data['active_sem']['intID'];
        
        
        
        if(!empty($data['prev_sem']))
        {
         
            $data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($data['student']['intID'],$data['prev_sem']['intID']);
            
            while(empty($data['prev_records']))
            {
                if(empty($data['prev_sem']))
                    break;
                
                $data['prev_sem'] = $this->data_fetcher->get_prev_sem($data['prev_sem']['intID'],$studNum);
                $data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($data['student']['intID'],$data['prev_sem']['intID']);
            }
            
        }
        else
            $data['prev_records'] = null;
        
        
        $data['subjects_not_taken'] = $this->data_fetcher->getRequiredSubjects($data['student']['intID'],$data['student']['intCurriculumID'], $data['selected_ay']);
        
        
        
        $data['reg_status'] = $this->data_fetcher->getRegistrationStatus($studNum,$data['selected_ay']);
        
        $grades = $this->data_fetcher->assessCurriculum($data['student']['intID'],$data['student']['intCurriculumID']);
        array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
        $data['grades'] = $grades;
        
        $data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculumMain($data['student']['intCurriculumID']);
        $data['equivalent_subjects'] = $this->data_fetcher->getSubjectsInCurriculumEqu($data['student']['intCurriculumID']);
        
        
        
        $data['advised_subjects'] = $this->data_fetcher->getAdvisedSubjects($data['student']['intID'],$data['active_sem']['intID']);
        
        $data['academic_standing'] = $this->data_fetcher->getAcademicStanding($data['student']['intID'],$data['student']['intCurriculumID']);

        echo json_encode($data);

    }

    public function load_subjects($studNum = null,$sem = 0){
        
        if($studNum == null){
            $post = $this->input->post();
            $this->data['id'] = $post['studentID'];
        }
        else
            $this->data['id']  = $studNum;

        //$this->data['active_sem'] = $this->data_fetcher->get_processing_sem();
        $student = $this->data_fetcher->getStudent($this->data['id']);
        switch($student['level']){
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
        
        if($sem != 0)
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        elseif($stype == 'shs')
            $active_sem = $this->data_fetcher->get_active_sem_shs();
        else
            $active_sem = $this->data_fetcher->get_active_sem();

        $this->data['sem'] = $active_sem['intID'];

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/advising_v",$this->data);
        $this->load->view("common/footer",$this->data);

    }
    
    public function load_subjects_old($studNum = null)
    {
        if($studNum == null){
            $post = $this->input->post();
            $id = $post['studentID'];
        }
        else
            $id  = $studNum;
        //$this->data['sy'] = $this->data_fetcher->getSemStudent($id);
        
        $this->data['errors'] = $this->session->flashdata('upload_errors');
        //$this->data['active_sem'] = $this->data_fetcher->get_processing_sem();
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->data['prev_sem'] = $this->data_fetcher->get_prev_sem($this->data['active_sem']['intID'],$studNum);
        
        
        
        
        $this->data['selected_ay'] = $this->data['active_sem']['intID'];
        
        
        //$this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        if($studNum==null){
            $post = $this->input->post();
            $this->data['student'] = $this->data_fetcher->getStudent($post['studentID']);
        }
        else
            $this->data['student'] = $this->data_fetcher->getStudent($studNum);
        
        
        if(!empty($this->data['prev_sem']))
        {
            
            $this->data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($this->data['student']['intID'],$this->data['prev_sem']['intID']);
            
            while(empty($this->data['prev_records']))
            {
                if(empty($this->data['prev_sem']))
                    break;
                
                $this->data['prev_sem'] = $this->data_fetcher->get_prev_sem($this->data['prev_sem']['intID'],$studNum);
                $this->data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($this->data['student']['intID'],$this->data['prev_sem']['intID']);
            }
            
        }
        else
            $this->data['prev_records'] = null;
        
        
        $this->data['subjects_not_taken'] = $this->data_fetcher->getRequiredSubjects($this->data['student']['intID'],$this->data['student']['intCurriculumID'], $this->data['selected_ay']);
        
        
        
        $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$this->data['selected_ay']);
        
        $grades = $this->data_fetcher->assessCurriculum($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
        array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
        $this->data['grades'] = $grades;
        
        $this->data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculumMain($this->data['student']['intCurriculumID']);
        $this->data['equivalent_subjects'] = $this->data_fetcher->getSubjectsInCurriculumEqu($this->data['student']['intCurriculumID']);
        
        
        
        $this->data['advised_subjects'] = $this->data_fetcher->getAdvisedSubjects($this->data['student']['intID'],$this->data['active_sem']['intID']);
        
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
        

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/advising",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/advised_conf",$this->data); 
        // print_r($this->data['classlists']); 
        
    }
    
    public function faculty_load_subjects()
    {
        if($this->faculty_logged_in())
        {
			$post = $this->input->post();
            $id = $post['facultyID'];
            
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $this->data['faculty'] = $this->data_fetcher->getFaculty($id);
            $this->data['sc'] = [];
            
            $faculty_classlists = $this->data_fetcher->fetch_classlist_by_faculty($id,$this->data['active_sem']['intID']);            
            
            foreach($faculty_classlists as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['intID']);
                //print_r($record['schedule']);
                $this->data['classlist'][] = $record;
            }
            
            
            $classlists = $this->data_fetcher->fetch_classlists_unassigned($this->data['active_sem']['intID'],null,$this->data['faculty']['strDepartment']);
            $ret = [];
            foreach($classlists as $classlist){
                $classlist['schedule'] = $this->data_fetcher->getScheduleByCode($classlist['intID']);
                $ret[] = $classlist;
            }

            $this->data['all_classlist'] = $ret;
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/load_subjects",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/load_subjects_conf",$this->data); 
            
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    public function show_advised_students($program=0)
    {
       
        $this->data['page'] = "show_advised_students";
        $this->data['opentree'] = "department";
        $this->data['course'] = current($this->data_fetcher->fetch_table('tb_mas_programs',array('strProgramCode','asc'),null,array('intProgramID'=>$program)));
        
        $this->data['courses'] = $this->data_fetcher->fetch_table('tb_mas_programs',array('strProgramCode','asc'));
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->data['program'] = $program;
        $as[$program] = $this->data_fetcher->getItem('tb_mas_programs',$program,'intProgramID'); 
            
        
        
        $this->data['as'] = $as;
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/advised_students",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/advised_students_conf",$this->data);
                   
    }
    
    
    //ADVISED SUBMIT---------------------------------------------------------------------------------------ßß
    public function submit_advised()
    {
        $post = $this->input->post();
        $ay = $post['strAcademicYear'];
        $student = $post['studentID'];
        
        
        //print_r($post);
        if(isset($post['subjects']))
        {
            $post['subjects'] = json_decode($post['subjects']); 
            
            //TODO: CHECK if advised if not advised for the semester add advised data
            if(!$this->data_fetcher->checkStudentAdvised($post['studentID'],$ay)) //check per subject
            {
                $data_advised['intStudentID'] = $post['studentID'];
                $data_advised['intSYID'] = $ay;

                $this->data_poster->post_data('tb_mas_advised',$data_advised);  
                $id = $this->db->insert_id();
                
            }
            else
            {
                $id = $this->data_fetcher->getAdvisedID($post['studentID'],$ay);
            }
            
            $this->data_poster->removeAdvisedSubjects($post['studentID'],$ay);//check per subject

            foreach($post['subjects'] as $subject)
            {
                    
                $data_subject['intSubjectID'] = $subject->intID;
                $data_subject['intAdvisedID'] = $id;
                $this->data_poster->post_data('tb_mas_advised_subjects',$data_subject);  

                
              

            }
        }
        else
        {
         
         
        }
        
        $data['message'] = "Success";
        $data['sid'] = $student;
        
        echo json_encode($data);
    }
    
    
    public function submit_loaded_classlist()
    {
        $post = $this->input->post();
        $faculty = $post['facultyID'];
        $ay = $post['ay'];
        
        $post['intSem'] = $ay;
        
        
        $rec = $this->data_fetcher->fetch_classlist_by_faculty($faculty,$ay);
        $faculty_list = array();
        
        foreach($rec as $r)
        {
            $faculty_list[] = $r['intID'];
        }
        
        if(!isset($post['classlists']))
            $post['classlists'] = array();
     
        
        
        $set = array_diff($faculty_list,$post['classlists']);
     
        $d['intEncoderID'] = $this->session->userdata('intID');

        foreach($set as $s)
        {
            $this->data_poster->removeFacultyClasslist($s);            
        }

        foreach($post['classlists'] as $cs)
        {
            $classlist = $this->data_fetcher->getClasslistDetails($cs);
            $data_subject['intFacultyID'] = $faculty;
            

            $rooms = $this->data_fetcher->getRoomsSelected($classlist->intSubjectID,'lecture');
            $rooms_lab = $this->data_fetcher->getRoomsSelected($classlist->intSubjectID,'laboratory');
            $schema = $this->data_fetcher->getSelectedDays($classlist->intSubjectID);
            if(!empty($post['days'])){
                shuffle($post['days']);
                if(!empty($schema))
                {
                    if(!$this->data_fetcher->getFromSchedule($cs,$ay)){
                        $selected_sch = array();
                        foreach($schema as $sch)
                        {
                            $sc = explode(' ',$sch['strDays']);

                            if(empty(array_diff($sc,$post['days'])))
                            {
                                $selected_sch[] = $sc;

                            }
                        }

                        if(!empty($selected_sch))
                        {
                            foreach($selected_sch as $sel)
                            {
                                if(count($sel) == 3)
                                    $hours = "1_hour";
                                else
                                    $hours = "1_5_hour";

                                foreach($rooms as $room)
                                {
                                    foreach($this->config->item($hours) as $key=>$value){
                                        $d['dteStart'] = $key;
                                        $d['dteEnd'] = $value;
                                        $d['intRoomID'] = $room['intID'];

                                        $conflict = $this->data_fetcher->schedule_conflict($d,null,$ay,$sel);
                                        $sconflict = $this->data_fetcher->section_conflict($d,null,$classlist->strSection,$ay,$sel);
                                        $fconflict = $this->data_fetcher->faculty_conflict($d,null,$faculty,$ay,$sel);

                                        if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                        {

                                            //$this->data_poster->deleteFromSchedule($cs,$ay,'lect',$dvalue);

                                            foreach($sel as $dvalue){
                                                $d['strScheduleCode'] = $cs;
                                                $d['intSem'] = $ay;
                                                $d['enumClassType'] = "lect";
                                                $d['strDay'] = $dvalue;

                                                if(!$this->data_fetcher->getFromSchedule($cs,$ay,'lect',$dvalue))
                                                    $this->data_poster->post_data('tb_mas_room_schedule',$d);


                                            }
                                            break;
                                        }

                                    }
                                    if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                        break;
                                }
                                if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                        break; 
                            }
                        }
                    }
                }
                else
                {
                    foreach($post['days'] as $dkey=>$dvalue)
                    {
                        foreach($rooms as $room)
                        {
                            foreach($this->config->item($classlist->intLectHours.'_hour') as $key=>$value){
                                $d['dteStart'] = $key;
                                $d['dteEnd'] = $value;
                                $d['strDay'] = $dvalue;
                                $d['intRoomID'] = $room['intID'];
                                $conflict = $this->data_fetcher->schedule_conflict($d,null,$ay);
                                $sconflict = $this->data_fetcher->section_conflict($d,null,$classlist->strSection,$ay);
                                $fconflict = $this->data_fetcher->faculty_conflict($d,null,$faculty,$ay);


                                if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                {
                                    $d['strScheduleCode'] = $cs;
                                    $d['intSem'] = $ay;
                                    $d['enumClassType'] = "lect";

                                    if(!$this->data_fetcher->getFromSchedule($cs,$ay))
                                        $this->data_poster->post_data('tb_mas_room_schedule',$d);

                                    if($classlist->intLab > 0)
                                    {
                                        $temp = $post['days'][0];
                                        $post['days'][0] = $dvalue;
                                        $post[$dkey] = $temp;
                                    }
                                    break;
                                }

                            }
                            if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                break;
                        }
                        if(empty($conflict) && empty($sconflict) && empty($fconflict))
                            break;
                    }

                    if($classlist->intLab > 0)
                    {
                        foreach($post['days'] as $day)
                        {
                            foreach($rooms_lab as $room)
                            {
                                foreach($this->config->item($classlist->intLab.'_hour') as $key=>$value){
                                    $d['dteStart'] = $key;
                                    $d['dteEnd'] = $value;
                                    $d['strDay'] = $day;
                                    $d['intRoomID'] = $room['intID'];
                                    $conflict = $this->data_fetcher->schedule_conflict($d,null,$ay);
                                    $sconflict = $this->data_fetcher->section_conflict($d,null,$classlist->strSection,$ay);
                                    $fconflict = $this->data_fetcher->faculty_conflict($d,null,$faculty,$ay);

                                    if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                    {
                                        $d['strScheduleCode'] = $cs;
                                        $d['intSem'] = $ay;
                                        $d['enumClassType'] = "lab";

                                        if(!$this->data_fetcher->getFromSchedule($cs,$ay,'lab'))
                                            $this->data_poster->post_data('tb_mas_room_schedule',$d);
                                         //   echo "new";

                                        break;
                                    }

                                }
                                if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                    break;
                            }
                            if(empty($conflict) && empty($sconflict) && empty($fconflict))
                                break;
                        }

                    }
                }
            }
                
                
                $this->data_poster->post_data('tb_mas_classlist',$data_subject,$cs);

            }
      
        
        $data['message'] = "Success";
        
        echo json_encode($data);
    }
    
    public function delete_credited()
    {
        $data['message'] = "failed";
        if($this->is_admin() || $this->is_department_head()){
            $post = $this->input->post();
            $this->data_poster->deleteItem('tb_mas_credited_grades',$post['id'],'intID');
            $data['message'] = "success";
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
    
    public function is_department_head()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 4)
            return true;
        else
            return false;
        
    }
    
}