<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Registrar extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        
        if(!$this->is_registrar() && !$this->is_super_admin() && !$this->is_department_head())
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
		$this->load->model("google_login");	
		$this->load->model("facebook_login");	
		$this->load->model("user_model");
        $this->config->load('courses');
        
        $this->data['department_config'] = $this->config->item('department');
        $this->data['terms'] = $this->config->item('terms');
        $this->data['campus'] = $this->config->item('campus');
        $this->data['term_type'] = $this->config->item('term_type');
        $this->data['unit_fee'] = $this->config->item('unit_fee');
        $this->data['misc_fee'] = $this->config->item('misc_fee');
        $this->data['lab_fee'] = $this->config->item('lab_fee');
        $this->data['id_fee'] = $this->config->item('id_fee');
        $this->data['athletic'] = $this->config->item('athletic');
        $this->data['srf'] = $this->config->item('srf');
        $this->data['sfdf'] = $this->config->item('sfdf');
        $this->data['csg'] = $this->config->item('csg');
        
        $this->data['page'] = "registrar";
        
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
    
    public function available_subjects($id,$sem){

        $student = $this->data_fetcher->getStudent($id);
        $ret['data'] = $this->data_fetcher->getSubjectsInCurriculumAvailable($student['intCurriculumID'],$sem);

        echo json_encode($ret);

    }
    public function registered_students_report($sem = null)
    {
       
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            
            
            if($sem!=null)
            {
                $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
            }
            else
                $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            
            $this->data['selected_ay'] = $this->data['active_sem']['intID'];
            $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
            
            $report = array();
            $total_resident = 0;
            $total_7th_district =0;
            $total_paying = 0; 
            
            foreach($programs as $prog)
            {
                $r['program'] = $prog['strProgramCode'];
                
                $r['resident_scholars'] = $this->data_fetcher->getScholars($prog['intProgramID'],'resident scholar',$this->data['selected_ay']);
                $total_resident+=$r['resident_scholars'];
                
                $r['paying'] = $this->data_fetcher->getScholars($prog['intProgramID'],'paying',$this->data['selected_ay']);
                $total_paying+= $r['paying'];
                
                $r['seventh_district'] = $this->data_fetcher->getScholars($prog['intProgramID'],'7th district scholar',$this->data['selected_ay']);
                $total_7th_district+= $r['seventh_district'];
                
                $report[] = $r;
            }
            
            
            $this->data['total_resident'] = $total_resident;
            $this->data['total_paying'] = $total_paying;
            $this->data['total_seventh_district'] = $total_7th_district;
            $this->data['total_all'] = $total_resident + $total_paying + $total_7th_district;
            $this->data['report'] = $report;
            
            $this->data['page'] = "registered_students";
            $this->data['opentree'] = "reports";
            //print_r($this->data['classlist']);
            $this->load->view("common/print_header",$this->data);
            $this->load->view("admin/registered_students_report",$this->data);
            $this->load->view("common/footer",$this->data); 
           // print_r($this->data['classlists']);
            
        
       
    }

    public function view_extension($id){
        if($this->is_super_admin() || $this->is_registrar())
        {
            $this->data['item'] = $this->db->get_where('tb_mas_sy_grading_extension',array('id'=>$id))
                                           ->first_row('array');

                                                      
            $classlists = $this->db
                              ->select('tb_mas_classlist.intID as classlistID, tb_mas_faculty.intID as facultyID, strClassName,year,strSection,sub_section,strCode,strFirstname,strMiddlename,strLastname')
                              ->where(array('intFacultyID !='=>999))
                              ->join('tb_mas_faculty','tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
                              ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
                              ->get('tb_mas_classlist')
                              ->result_array();

            $ret_fac = [];
            $ret_fac_selected = [];
            foreach($classlists as $cl){
                $tmp = $this->db->select('tb_mas_sy_grading_extension_faculty.id')
                            ->where(array('classlist_id'=>$cl['classlistID'],'grading_extension_id'=>$this->data['item']['id']))
                            ->join('tb_mas_classlist','tb_mas_sy_grading_extension_faculty.classlist_id = tb_mas_classlist.intID')
                            ->get('tb_mas_sy_grading_extension_faculty')
                            ->first_row('array');

                                         
                if($tmp){
                    $cl['extnsion_faculty'] = $tmp['id'];
                    $ret_fac_selected[] = $cl;
                }
                else
                    $ret_fac[] = $cl;
            }

            $this->data['selected_faculty'] = $ret_fac_selected;
            $this->data['non_selected_faculty'] = $ret_fac;

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_extension",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("extension_conf",$this->data);



        }
    }

    public function add_selected(){
        $post = $this->input->post();
        
        foreach($post['classlist'] as $classlist){
            $data = array(
                "grading_extension_id"=>$post['id'],             
                "classlist_id"=>$classlist   
            );

            $this->data_poster->post_data('tb_mas_sy_grading_extension_faculty',$data);

        }

        redirect(base_url()."registrar/view_extension/".$post['id']);
    }

    public function delete_from_selected(){
        $post = $this->input->post();

        $this->db
        ->where('id',$post['id'])
        ->delete('tb_mas_sy_grading_extension_faculty');

        $data['message'] = "deleted";
        echo json_encode($data);
    }
    
    public function submit_extension(){
        $post = $this->input->post();
        $data = array(
                "date" => $post['date'],
                "type" => $post['type'],
                "syid" => $post['id']
        );
        $this->data_poster->post_data('tb_mas_sy_grading_extension',$data);
        redirect(base_url()."registrar/edit_ay/".$post['id']);  
    }

    public function delete_extension(){
        $post = $this->input->post();
        $this->db->where('id',$post['id'])
                 ->delete('tb_mas_sy_grading_extension');

        $this->db->where('grading_extension_id',$post['id'])
                 ->delete('tb_mas_sy_grading_extension_faculty');

        $data['message'] = "Deleted";
        $data['success'] = true;

        echo json_encode($data);
    }

    public function submitted_grades($id){
        $this->data['page'] = "grading_sheet_view";
        $this->data['opentree'] = "registrar";
        $this->data['id'] = $id;
        //print_r($this->data['classlist']);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/submitted_grades",$this->data);
        $this->load->view("common/footer",$this->data); 
    }

    public function student_grade_slip($id,$sem = 0){
        $this->data['page'] = "student_grade_slip";
        $this->data['opentree'] = "registrar";
        $this->data['id'] = $id;        
        $this->data['sem'] = $sem;
        //print_r($this->data['classlist']);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/student_grade_slip",$this->data);
        $this->load->view("common/footer",$this->data); 

    }

    public function student_grade_slip_data($id,$sem){
                        
        $ret['student'] = $this->data_fetcher->getStudent($id);        
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
        
        if($sem != 0)
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        elseif($stype == 'shs')
            $ret['active_sem'] = $this->data_fetcher->get_active_sem_shs();
        else
            $ret['active_sem'] = $this->data_fetcher->get_active_sem();

        $ret['balance'] = $this->data_fetcher->getStudentBalance($id);

        $ret['selected_ay'] = $ret['active_sem']['intID'];

        $records = $this->data_fetcher->getClassListStudentsSt($id,$ret['selected_ay']);        

        $ret['sy'] = $this->db->get_where('tb_mas_sy',array('term_student_type'=>$stype))->result_array();
        $ret['deficiencies'] = $this->db
        ->get_where('tb_mas_student_deficiencies',array('student_id'=>$id,'status'=>'active','temporary_resolve_date <'=> date("Y-m-d")))->result_array();
                
        $sc_ret = [];
        foreach($records as $record)
        {
            $schedule = $this->data_fetcher->getScheduleByCodeNew($record['classlistID']);                                                  
            $record['schedule'] = $schedule;
            $sc_ret[] = $record;
        }        

        $ret['other_data'] = 
        array(
            'academic_standing' => null,
            'totalUnitsEarned' => null,
            'gpa_curriculum' => null,
            'academic_standing' => null,

        );
        
        $ret['class_data'] = $sc_ret;
        $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
        $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);                
        

        echo json_encode($ret);

    }

    public function submitted_grades_data($id){
        $data['students'] = $this->data_fetcher->getClassListStudents($id);
        $data['classlist'] = $this->db->get_where('tb_mas_classlist',array('intID'=>$id))->first_row();

        echo json_encode($data);
    }

    public function search_grading(){
        
        $this->data['page'] = "grading_sheet_view";
        $this->data['opentree'] = "registrar";
        //print_r($this->data['classlist']);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/grading_sheet_view",$this->data);
        $this->load->view("common/footer",$this->data); 
    
    }

    public function search_grading_data($dept){
        
        $data['terms'] = $this->db->get_where('tb_mas_sy',array('term_student_type'=>$dept))->result_array();
        $data['faculty'] = $this->db->get_where('tb_mas_faculty',array('teaching'=>1))->result_array();
        echo json_encode($data);

    }

    public function search_grading_sections($term){
        
        $data['sections'] = $this->db->where(array('strAcademicYear'=>$term))
                                     ->group_by(array('strClassName'))
                                     ->get('tb_mas_classlist')
                                     ->result_array();

        $data['subjects'] = $this->db
                            ->select('tb_mas_subjects.*')
                            ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                            ->where(array('strAcademicYear'=>$term))
                            ->group_by('intSubjectID')
                            ->get('tb_mas_classlist')
                            ->result_array();                                     

        echo json_encode($data);

    }

    public function search_grading_results(){
        $post = $this->input->post();
        $where = array('strAcademicYear'=>$post['term'],'isDissolved'=>0);
        
        if($post['faculty'] != "undefined")
            $where['intFacultyID'] = $post['faculty'];

        if($post['subject'] != "undefined")
            $where['intSubjectID'] = $post['subject'];
        
        if($post['section'] != "undefined"){        
            $where['strSection'] = $post['section'];        
        }
        if($post['year'] != "undefined"){        
            $where['year'] = $post['year'];        
        }
        if($post['class_name'] != "undefined"){        
            $where['strClassName'] = $post['class_name'];        
        }
        if($post['sub_section'] != "undefined"){        
            $where['sub_section'] = $post['sub_section'];        
        }

        $data['results'] = $this->db
                            ->select('tb_mas_classlist.*,strCode,strDescription,strLastname,strFirstname')
                            ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                            ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                            ->where($where)                            
                            ->get('tb_mas_classlist')
                            ->result_array();   

        echo json_encode($data);                            
    }
    
    public function edit_ay($id)
    {
        
        if($this->is_super_admin() || $this->is_registrar())
        {
          
            $this->data['item'] = $this->data_fetcher->getAy($id);
            $this->data['midterm_extensions'] = $this->db->where(array('syid'=>$id,'type'=>'midterm'))
                                                 ->order_by('date','DESC')
                                                 ->get('tb_mas_sy_grading_extension')
                                                 ->result_array();
            
            $this->data['final_extensions'] = $this->db->where(array('syid'=>$id,'type'=>'final'))
                                                 ->order_by('date','DESC')
                                                 ->get('tb_mas_sy_grading_extension')
                                                 ->result_array();

            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_ay",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("ay_validation_js",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    
    
    public function submit_registration_old()
    {
        $post = $this->input->post();
      
        if(isset($post['subjects-loaded']))
        {
            
            $index = 0;
            $program = $this->data_fetcher->getProgram($post['intProgramID']);
            $active_sem = $this->data_fetcher->get_active_sem();
            foreach($post['subjects-loaded'] as $subject)
            {
                
                $subject_data = $this->data_fetcher->getSubjectCurr($subject,$post['intProgramID']);
                               
                $this->data['col1'][] = $subject_data['strCode'];

                if(!$this->data_fetcher->checkSubjectTaken($post['studentID'],$subject)) //if subject has not been taken
                {
                    if(isset($post['section-'.$subject]) && $post['section-'.$subject]!=0 && $post['section-'.$subject] != "new")// if section exists
                    {
                        
                        $cl_get = $this->data_fetcher->fetch_classlist_id($post['section-'.$subject]);

                        $cl_data['intStudentID'] = $post['studentID'];
                        $cl_data['intClassListID'] = $cl_get['intID'];
                        $this->data_poster->addStudentClasslist($cl_data,$this->data["user"]["intID"]);
                        $this->data['col2'][] = "Student Registered to Section ".$cl_get['strClassName'].$cl_get['year'].$cl_get['strSection']." ".$cl_get['sub_section'];
                        $this->data['col3'][] = "<a href='".base_url()."unity/classlist_viewer/".$cl_get['intID']."'>View Classlist</a>";
                        
                        
                    }
                    elseif(isset($post['subjects-section'][$index]) && $post['section-'.$subject] != "new")
                    {
                        
                        $cl_get = $this->data_fetcher->fetch_classlist_id($post['subjects-section'][$index]);
                       
                        $cl_data['intStudentID'] = $post['studentID'];
                        $cl_data['intClassListID'] = $cl_get['intID'];
                        $this->data_poster->addStudentClasslist($cl_data,$this->data["user"]["intID"]);
                        $this->data['col2'][] = "Student Registered to Section ".$cl_get['strSection'];
                        $this->data['col3'][] = "<a href='".base_url()."unity/classlist_viewer/".$cl_get['intID']."'>View Classlist</a>";
                    }
                    else // kapag wla pa section
                    {
                        
                        
                        if(isset($post['section-'.$subject]) && $post['section-'.$subject] == "new")
                        {
                            $cl = $this->data_fetcher->checkClasslistExists($subject,$post['strAcademicYear'],$subject_data['strCode'],"new");
                            
                        }
                        else{
                            $cl = $this->data_fetcher->checkClasslistExists($subject,$post['strAcademicYear'],$subject_data['strCode']); // this is where auto sectioning happens
                            
                        }
                        

                        if(!is_array($cl)) //if $cl is not array
                        {
                             
                            //echo $cl;
                            if($cl!="1"){
                                $cl = explode("-",$cl);
                                $letter = $cl[count($cl)-1];
                                //echo $letter."<br />";
                                $letter++;
                                //echo $letter;
                            }
                            else
                            {
                                $letter = "A";
                            }
                            
                            
                            

                            $classlist['intFacultyID'] = 999;
                            $classlist['intSubjectID'] = $subject;
                            $classlist['strAcademicYear'] = $post['strAcademicYear'];
                            $classlist['strUnits'] = $subject_data['strUnits'];                            
                            $classlist['strSection'] = $subject_data['strCode']."-".$subject_data['intYearLevel']."-".$letter;
                            $classlist['strClassName'] = $subject_data['strCode'];
                           
                            
                            $this->data_poster->post_data('tb_mas_classlist',$classlist);
                            $cid = $this->db->insert_id();
                            $cname = $classlist['strClassName'];
                        }
                        else
                        {
                            $cname = $cl['strClassName'];
                            $cid = $cl['intID'];
                        }

                        
                        $cl_data['intStudentID'] = $post['studentID'];
                        $cl_data['intClassListID'] = $cid;
                        $this->data_poster->addStudentClasslist($cl_data,$this->data["user"]["intID"]);
                        $this->data['col2'][] = "Student Registered to Section ".$cname;
                        $this->data['col3'][] = "<a href='".base_url()."unity/classlist_viewer/".$cid."'>View Classlist</a>";
                        

                    }
                }              
                else
                {
                    
                    $this->data['col2'][] = "already passed or is enrolled in subject";
                    $this->data['col3'][] ="";
                }


                $index++;
                    
            }
            /*
            $academic_standing = $this->data_fetcher->getAcademicStanding($post['studentID'],$post['strAcademicYear']);
            
            if(!$this->data_fetcher->checkRegistered($post['studentID'],$post['strAcademicYear'])){
                $reg['intStudentID'] = $post['studentID'];
                $reg['intAYID'] = $post['strAcademicYear'];
                $reg['intYearLevel'] = $academic_standing['year'];
                $reg['date_enlisted'] = date("Y-m-d");
                $reg['enumRegistrationStatus'] = $post['enumRegistrationStatus'];
                
                if($post['enumStudentType']=="cross")
                    $st = "Cross Registered From ".$post['strFrom'];
                elseif($post['enumStudentType']=="transferee")
                    $st = "Transferred From ".$post['strFrom'];
                else
                    $st = $post['enumStudentType'];
                
                $reg['enumStudentType'] = $st;
                
                
                $this->data_poster->post_data('tb_mas_registration',$reg);
            }
            
            */
        }
        else
        {
            
            if($post['enumRegistrationStatus'] == "regular"){
                $this->session->set_flashdata('message','Select Subjects to Register');
                $this->session->set_flashdata('datapost',$post);
                redirect(base_url()."unity/register_old_student_not_post");
            }
            else
            {
            
                $this->data['col1'][] ="Not Registered"; 
                $this->data['col2'][] = "";
                $this->data['col3'][] ="";   
            }
        }
        
        $this->data['student_link'] = "<a href='".base_url()."unity/student_viewer/".$post['studentID']."'>View Student Info</a>";
        
        $this->data['sid'] = $post['studentID'];
        $this->data['ayid'] = $post['strAcademicYear'];

        echo json_encode($this->data);
        
        $this->session->set_userdata('from_advising',$this->data);
        //redirect(base_url()."registrar/advising_done");
        
    } 

    public function approveCompletion($id){
        if($this->is_super_admin() || $this->is_registrar())
        {
          
            $completion = $this->data_fetcher->getCompletionByID($id);
            if($completion['enumStatus'] != 1){
                $item = $this->data_fetcher->getItem('tb_mas_classlist_student',$completion['intClasslistStudentID'],'intCSID');
                $grade = getAve($item['floatPrelimGrade'],$item['floatMidtermGrade'],$completion['floatNewFinalTermGrade']); 
                

                $data['eq'] = getEquivalent($grade);
                $data['eq_raw'] = $grade;

                //updated final Grade
                $d['floatFinalsGrade'] = $completion['floatNewFinalTermGrade'];
                $d['floatFinalGrade'] = $data['eq'];
                $d['strRemarks'] = $data['remarks'] = getRemarks($data['eq']);
                $d['enumStatus'] = 'act';                

                $this->data_poster->update_classlist('tb_mas_classlist_student',$d,$completion['intClasslistStudentID']);
                $this->data_poster->approve_completion($id);
            }
            redirect(base_url().'/registrar/completions');
            
        }
        else
            redirect(base_url()."unity");   

    }

    public function completions(){

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/completions",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/completions_conf",$this->data); 
    }

    public function enrollment_summary_data($sem = 0){

        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $sem = $active_sem['intID'];
        }
        else{
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
            $sem = $active_sem['intID'];
        }


        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $type = $active_sem['term_student_type'];
        $programs = $this->db->get_where('tb_mas_programs',array('type'=>$type))->result_array();
        $data['programs'] = $programs;
        $ret = [];        

        foreach($programs as $program){
            $st = [];
            $program['enrolled_transferee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,2));
            $program['enrolled_freshman'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,1));
            $program['enrolled_foreign'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,3));
            $program['enrolled_second'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,4));            
            $program['enrolled_continuing'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,5));
            $program['enrolled_shiftee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,6));
            $ret[] = $program; 
        }

        $data['data'] = $ret;

        echo json_encode($data);

    }

    public function daily_enrollment_report($start=0, $end=0, $sem = 0){

        $this->data['start'] = $start;
        $this->data['end'] = $end;

        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $sem = $active_sem['intID'];
        }
        else{
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
            $sem = $active_sem['intID'];
        }

        $this->data['pdf_link'] = base_url()."pdf/daily_enrollment_report/".$sem;
        $this->data['excel_link'] = base_url()."excel/daily_enrollment_report/".$sem;

        $this->data['active_sem'] = $active_sem;
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/daily_enrollment",$this->data);
        $this->load->view("common/footer",$this->data); 
    
    }

    public function daily_enrollment_report_data(){
        $post = $this->input->post();
        $active_sem = $this->data_fetcher->get_sem_by_id($post['sy']);        
        $enrolled = [];
        
        $begin = new DateTime($post['start']);
        $end = new DateTime($post['end']);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        $totals = [
            'freshman' => 0,
            'transferee' => 0,
            'second' => 0,
            'continuing' => 0,
            'shiftee' => 0,
        ];

        foreach ($period as $dt) {
            //echo $dt->format("l Y-m-d H:i:s\n");
            $date = $dt->format("Y-m-d");           
            $data[$date] = [
                'freshman' => 0,
                'transferee' => 0,
                'second' => 0, 
                'continuing' => 0,    
                'shiftee' => 0,           
                'total' => 0,
                'date' => date("M j, Y", strtotime($date))
            ]; 
        
            $enrollment = $this->db->select('tb_mas_registration.*,tb_mas_users.student_type')
                    ->from('tb_mas_registration')
                    ->join('tb_mas_users','tb_mas_users.intID = tb_mas_registration.intStudentID')
                    ->where('intAYID',$active_sem['intID'])
                    ->where('intROG >=',1)
                    ->where('dteRegistered LIKE', $date."%")                     
                    ->order_by('intRegistrationID','desc')
                    ->group_by('intStudentID')
                    ->get()
                    ->result_array();  
                                
            
            foreach($enrollment as $st){
                $data[$date]['total'] += 1;                
                
                if($st['enumStudentType'] == "continuing")
                {
                    $data[$date]['continuing'] += 1;
                    $totals['continuing'] += 1;
                }
                elseif($st['enumStudentType'] == "shiftee")
                {
                    $data[$date]['shiftee'] += 1;
                    $totals['shiftee'] += 1;
                }
                else
                    switch($st['student_type']){
                        case 'freshman':
                            $data[$date]['freshman'] += 1;
                            $totals['freshman'] += 1;
                            break;
                        case 'transferee':
                            $data[$date]['transferee'] += 1;
                            $totals['transferee'] += 1;
                            break;
                        case 'second degree':
                            $data[$date]['second'] += 1;
                            $totals['second'] += 1;
                            break;                        
                        default:
                            $data[$date]['freshman'] += 1;
                            $totals['freshman'] += 1;
                            
                    }                                
            }
        }
                       
        // $program['regular'] = count($this->data_fetcher->getStudentsByTypeOfClass('regular'));
        // $program['online'] = count($this->data_fetcher->getStudentsByTypeOfClass('online'));
        // $program['hybrid'] = count($this->data_fetcher->getStudentsByTypeOfClass('hybrid'));
        // $program['hyflex'] = count($this->data_fetcher->getStudentsByTypeOfClass('hyflex'));
                            
        $ret['totals'] = $totals;
        $ret['data'] = $data;
        $ret['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        echo json_encode($ret);

    }

    public function enrollment_summary($sem = 0)    
    {
        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }
        else{
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
            $this->data['sem'] = $active_sem['intID'];
        }        
        $this->data['pdf_link'] = base_url()."pdf/enrollment_summary/".$this->data['sem'];
        $this->data['excel_link'] = base_url()."excel/enrollment_summary/".$this->data['sem'];

        
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/enrollment_summary",$this->data);
        $this->load->view("common/footer",$this->data); 
        
    }

    public function reservation_summary($sem = 0)    
    {

        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }
        else{
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
            $this->data['sem'] = $active_sem['intID'];
        }

        $this->data['pdf_link'] = base_url()."pdf/reservation_summary/".$this->data['sem'];
        $this->data['excel_link'] = base_url()."excel/reservation_summary/".$this->data['sem'];
        
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/reserved_summary",$this->data);
        $this->load->view("common/footer",$this->data); 
        
    }

    public function enrollment_report($course = 0, $year=1,$gender = 0,$sem=0)    
    {

        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
        $this->data['course'] = $course;        
        $this->data['postyear'] = $year;
        $this->data['gender'] = $gender;                
        $this->data['sem'] = $sem;
        $this->data['pdf_link'] = base_url()."pdf/ched_enrollment_list/".$course."/".$year."/".$gender."/".$sem;
        $this->data['excel_link'] = base_url()."excel/ched_enrollment_list/".$course."/".$year."/".$gender."/".$sem;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/enrollment_report",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/enrollment_report_conf",$this->data); 
    }

    public function enlistment_report($course = 0, $year=1,$gender = 0,$sem=0, $start=0, $end=0)    
    {

        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
        $this->data['course'] = $course;        
        $this->data['postyear'] = $year;
        $this->data['gender'] = $gender;               
        $this->data['sem'] = $sem; 
        if($start != 0){
            $this->data['start'] = $start; 
            $this->data['end'] = $end; 
        }
        else{
            $this->data['start'] = date("Y-m-d"); 
            $this->data['end'] = date("Y-m-d");
        }
        
        $this->data['pdf_link'] = base_url()."pdf/print_enlisted_students/".$course."/".$year."/".$gender."/".$sem."/".$start."/".$end;
        $this->data['excel_link'] = base_url()."excel/enlisted_students/".$course."/".$year."/".$gender."/".$sem."/".$start."/".$end;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/enlistment_report",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/enlistment_report_conf",$this->data); 
    }

    public function nstp_report_data($sem){

        $ret['data'] = $this->data_fetcher->getNSTPGraduates($sem);        
        $ret['success'] = true;
        $ret['message'] = "Success";

        echo json_encode($ret);

    }

    public function promotional_report($term = 0)    
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term); 
                 
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/promotional_report",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/promotion_report_conf",$this->data); 
        }
    }

    public function ched_enrollment_report($term = 0)    
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term); 
                 
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/ched_enrollment_report",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/ched_enrollment_report_conf",$this->data); 
        }
    }

    public function ched_tes_report($term = 0)    
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term); 
                 
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/ched_tes_report",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/ched_tes_report_conf",$this->data); 
        }
    }

    public function nstp_report($term = 0)    
    {
        if($this->faculty_logged_in())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term); 
                 
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];

            $this->load->view("common/header",$this->data);
            $this->load->view("admin/ched_nstp_report",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/ched_nstp_report_conf",$this->data);
        }
    }

    public function advising_done(){

        $data = $this->session->userdata('from_advising');        
        if(isset($data)){
            $this->load->view("common/header",$data);
            $this->load->view("admin/reg_student_result",$data);
            $this->load->view("common/footer",$data);

            $this->session->unset_userdata('from_advising');
        }
        else{
            echo "Invalid Transaction";
        }

    }

    public function sync_program_registration(){
        $students = $this->db->get('tb_mas_users')->result_array();
        foreach($students as $student){
            $data = array(
                'current_program' => $student['intProgramID'],
                'current_curriculum' => $student['intCurriculumID']
            );

            $this->db->where('intStudentID',$student['intID'])->update('tb_mas_registration',$data);
        }
    }
    
    public function submit_registration_old2()
    {
        $post = $this->input->post();
       
       
        $academic_standing = $this->data_fetcher->getAcademicStanding($post['studentID'],$post['strAcademicYear']);        
        $data['sid'] = $post['studentID'];
        $data['ayid'] = $post['strAcademicYear'];
        $student = $this->data_fetcher->getStudent($post['studentID']);
        
        $data['student_link'] = base_url()."unity/student_viewer/".$post['studentID'];
        
        if(!$this->data_fetcher->checkRegistered($post['studentID'],$post['strAcademicYear'])){
            $reg['intStudentID'] = $post['studentID'];
            $reg['enlisted_by'] = $this->data["user"]["intID"];
            $reg['intAYID'] = $post['strAcademicYear'];
            $reg['intYearLevel'] = $academic_standing['year'];
            $reg['date_enlisted'] = date("Y-m-d H:i:s");     
            $reg['enumRegistrationStatus'] = $post['enumRegistrationStatus'];
            $reg['enumScholarship'] = $post['enumScholarship'];        
            $reg['enumStudentType'] = $post['enumStudentType'];
            $reg['intYearLevel'] = $post['intYearLevel'];            
            $reg['type_of_class'] = $post['type_of_class'];
            $reg['current_program'] =$student['intProgramID'];
            $reg['current_curriculum'] =$student['intCurriculumID'];
            $reg['tuition_year'] = $student['intTuitionYear'];
            $s = $this->data_fetcher->get_sem_by_id($data['ayid']);
            $data['message'] = "Congratulations, you have been registered for ".$s['enumSem']." Term S.Y. ".$s['strYearStart']."-".$s['strYearEnd'];
            $data['tuition_payment_link'] = base_url()."unity/student_tuition_payment/".$student['slug'];
            $data['success'] = true;
            $this->data_poster->post_data('tb_mas_registration',$reg);
            
            if($student['strStudentNumber'][0] == "T"){
                $stud['strStudentNumber'] = $tempNum = $this->data_fetcher->generateNewStudentNumber($this->data['campus'],$data['ayid'],get_stype($student['level']));
            }

            $stud['intStudentYear'] = $academic_standing['year']; 
            $this->data_poster->post_data('tb_mas_users',$stud,$post['studentID']);            

        }
        else
        {
            $data['message'] = "Student Already Registered";
        }
        
        echo json_encode($data);
        // $this->load->view("common/header",$this->data);
        // $this->load->view("admin/reg_student_result2",$this->data);
        // $this->load->view("common/footer",$this->data); 
    } 
    
    public function submit_registration_new($post)
    {
           
            $index = 0;
            $program = $this->data_fetcher->getProgram($post['intProgramID']);
            if(isset($post['subjects-loaded']))
            {
                foreach($post['subjects-loaded'] as $subject)
                {
                    $subject_data = $this->data_fetcher->getSubject($subject);


                    $this->data['col1'][] = $subject_data['strCode'];

                    if(!$this->data_fetcher->checkSubjectTaken($post['studentID'],$subject))
                    {

                        if(isset($post['subjects-section'][$index]))
                        {

                            $cl_get = $this->data_fetcher->fetch_classlist_id($post['subjects-section'][$index]);

                            $cl_data['intStudentID'] = $post['studentID'];
                            $cl_data['intClassListID'] = $cl_get['intID'];
                            $this->data_poster->addStudentClasslist($cl_data,$this->data["user"]["intID"]);
                            $this->data['col2'][] = "Student Registered to ";
                            $this->data['col3'][] = "<a href='".base_url()."unity/classlist_viewer/".$cl_get['intID']."'>View Classlist</a>";
                        }
                        else
                        {

                            $cl = $this->data_fetcher->checkClasslistExists($subject,$post['strAcademicYear'],$subject_data['strCode']);
                            if(!is_array($cl))
                            {
                                if($cl!="1"){
                                    $cl = explode("-",$cl);
                                    $letter = $cl[2];
                                    $letter++;
                                }
                                else
                                {
                                    $letter = "A";
                                }

                                $classlist['intFacultyID'] = 999;
                                $classlist['intSubjectID'] = $subject;
                                $classlist['strAcademicYear'] = $post['strAcademicYear'];
                                $classlist['strUnits'] = $subject_data['strUnits'];
                                $classlist['strSection'] = $subject_data['strCode']."-".$subject_data['intYearLevel']."-".$letter;
                                $classlist['strClassName'] = $subject_data['strCode'];
                                $this->data_poster->post_data('tb_mas_classlist',$classlist);
                                $cid = $this->db->insert_id();
                                $cname = $classlist['strClassName'];
                            }
                            else
                            {
                                //print_r($cl);
                                //echo "<br />".$cl['strClassName'];
                                $cname = $cl['strClassName'];
                                $cid = $cl['intID'];
                            }

                            $cl_data['intStudentID'] = $post['studentID'];
                            $cl_data['intClassListID'] = $cid;
                            $this->data_poster->addStudentClasslist($cl_data,$this->data["user"]["intID"]);
                            $this->data['col2'][] = "Student Registered to Section ".$cname;
                            $this->data['col3'][] = "<a href='".base_url()."unity/classlist_viewer/".$cid."'>View Classlist</a>";
                        }
                    }
                    else
                    {
                        $this->data['col2'][] = "already passed or is enrolled in subject";
                        $this->data['col3'][] ="";
                    }



                    $index++;
                }

                $reg['intStudentID'] = $post['studentID'];
                $reg['intAYID'] = $post['strAcademicYear'];
                $reg['intYearLevel'] = $post['intYearLevel'];
                $reg['date_enlisted'] = date("Y-m-d H:i:s");
                $reg['enumRegistrationStatus'] = $post['enumRegistrationStatus'];                

                if($post['enumStudentType']=="cross")
                    $st = "Cross Registered From ".$post['strFrom'];
                elseif($post['enumStudentType']=="transferee")
                    $st = "Transferred From ".$post['strFrom'];
                else
                    $st = $post['enumStudentType'];
            }
        
            $reg['enumStudentType'] = $st;
            $this->data_poster->post_data('tb_mas_registration',$reg);


            $this->data['student_link'] = "<a href='".base_url()."unity/student_viewer/".$post['studentID']."'>View Student Info</a>";
        
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/reg_student_result",$this->data);
            $this->load->view("common/footer",$this->data); 
        
        
    }
    
    
    public function registration_viewer($id,$sem = null)
    {
        $active_sem = $this->data_fetcher->get_active_sem();
        
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        
        
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        $payment = $this->data_fetcher->getTransactionsPayment($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        $pay =  array();
        foreach($payment as $p){
            if(isset($pay[$p['strTransactionType']]))
                $pay[$p['strTransactionType']] += $p['intAmountPaid'];
            else
                $pay[$p['strTransactionType']] = $p['intAmountPaid'];
            
        }
        $this->data['payment'] = $pay;
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['student']['enumScholarship']);
        
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/registration_viewer",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/registration_viewer_conf",$this->data); 
    }
    
    function get_transaction_ajax()
    {
        $post = $this->input->post();
        $or = $post['orNumber'];
        $transactions = $this->data_fetcher->getTransactionsOR($or);
        $total = 0;
        $ret ='<div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">OR Number: <br />'.$or.'</div>
                    <div class="col-sm-4">Date  <br />'.$transactions[0]['dtePaid'].'</div>
                </div>
                <hr />
                
                <div class="row">
                    <div class="col-sm-4">Nature of Collection</div>
                    <div class="col-sm-4">Amount</div>
                </div>
                <hr />
                ';
        
        foreach($transactions as $trans){
            $ret .='<div class="row">
                        <div class="col-sm-4">'.$trans['strTransactionType'].'</div>
                        <div class="col-sm-4">'.$trans['intAmountPaid'].'</div>
                        <div class="col-sm-4"><button type="button" rel="'.$trans['intTransactionID'].'" class="btn btn-box-tool trash-transaction"><i style="font-size:2em;" class="ion ion-trash-a"></i></button></div>
                    </div>';

                    $total += $trans['intAmountPaid'];
        }
        $words = convert_number($total);
           $ret .= '
           <div class="row">
            <div class="col-sm-4" style="text-align:right">Total:</div>
            <div class="col-sm-4">'.$total.'</div>
           </div>
           <hr />
            <div>Amount in words:<br />'.$words.' pesos</div>
           </div>
        </div>';
        $data['viewer'] = $ret; 
        echo json_encode($data);
        
    }

    function registrar_reports(){
        $this->data['page'] = "reports";
        $this->data['opentree'] = "registrar";
        $sem = $this->data_fetcher->get_active_sem();
        $this->data['sem'] = $sem['intID'];
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/registrar_reports",$this->data);
        $this->load->view("common/footer",$this->data);            
    }

    function shifting($id,$sem){
        $this->data['page'] = "shifting";                
        $this->data['sem'] = $sem;
        $this->data['id'] = $id;
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/shifting",$this->data);
        $this->load->view("common/footer",$this->data);            
    }

    function shifting_data($id,$sem){
        $data['registration'] = $this->db->select('tb_mas_registration.*,strProgramCode,tb_mas_programs.intProgramID,strProgramDescription,tb_mas_curriculum.strName')
                                ->join('tb_mas_curriculum','tb_mas_registration.current_curriculum = tb_mas_curriculum.intID')
                                ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                                ->where(array('intStudentID'=>$id,'intAYID'=>$sem))                                
                                ->get('tb_mas_registration')
                                ->first_row('array');

        $data['shifted'] = $this->db->select('strProgramCode,strProgramDescription,tb_mas_curriculum.strName')
                                ->join('tb_mas_curriculum','tb_mas_registration.shifted_curriculum = tb_mas_curriculum.intID')
                                ->join('tb_mas_programs','tb_mas_registration.shifted_program = tb_mas_programs.intProgramID')
                                ->where(array('intStudentID'=>$id,'intAYID'=>$sem))                                
                                ->get('tb_mas_registration')
                                ->first_row();                                        

        $data['student'] = $this->db->get_where('tb_mas_users',array('intID'=>$id))->first_row('array');
        $data['programs'] = $this->db->get_where('tb_mas_programs',array('type'=>get_stype($data['student']['level']),'intProgramID !='=>$data['student']['intProgramID']))->result_array();
        
        $data['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);

        echo json_encode($data);
    }

    function get_curriculum($program){
        $data['curriculum'] = $this->db->get_where('tb_mas_curriculum',array('intProgramID'=>$program))->result_array();
        echo json_encode($data);
    }

    function shift_student(){
        $post = $this->input->post();
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$post['intStudentID']))->first_row('array');        
        if($this->db->where('intRegistrationID',$post['intRegistrationID'])->update('tb_mas_registration',$post)){            
            $shift['intProgramID'] = $post['shifted_program'];
            $shift['intCurriculumID'] = $post['shifted_curriculum'];
            
            $this->db->where('intID',$post['intStudentID'])->update('tb_mas_users',$shift);
            $data['success'] = true;
            $data['message'] = "Successfully Shifted Student";
            $this->data_poster->log_action('Registrar','Shifted Student: '.$student['strFirstname']." ".$student['strLastname'],'green');
        }
        else{
            $data['success'] = false;
            $data['message'] = "Failed";
        }

        echo json_encode($data);
    }

    function revert_shift_student(){
        $post = $this->input->post();
        $registration = $this->db->get_where('tb_mas_registration',array('intRegistrationID'=>$post['intRegistrationID']))->first_row('array');
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$post['intStudentID']))->first_row('array');        
        $this->db->where('intRegistrationID',$post['intRegistrationID'])
                 ->update('tb_mas_registration',array('shifted_program'=>NULL,'shifted_curriculum'=>NULL));
        
        $shift['intProgramID'] = $registration['current_program'];
        $shift['intCurriculumID'] = $registration['current_curriculum'];
        $this->db->where('intID',$post['intStudentID'])->update('tb_mas_users',$shift);
        $this->data_poster->log_action('Registrar','Reverted Shifting: '.$student['strFirstname']." ".$student['strLastname'],'red');
        $data['success'] = true;
        $data['message'] = "Successfully Reverted Shift";
        
        

        echo json_encode($data);
    }

    function get_registration_info($slug){

        $sem = $this->data_fetcher->get_active_sem();
        $sdata['student'] = $this->data_fetcher->fetch_single_entry('tb_mas_users',$slug,'slug');
        $sdata['registration_data'] =  $this->data_fetcher->getRegistrationInfo($sdata['student']['intID'],$sem['intID']);
        $sdata['tuition_data'] =  $this->data_fetcher->getTuition($sdata['student']['intID'],$sem['intID']);
        $sdata['current_sem'] = $sem['intID'];
        
        
        $data['data'] = $sdata;
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);
    }
    
    function get_tuition_ajax()
    {
        $post = $this->input->post();
        //print_r($post);
        if(!isset($post['subjects_loaded']))
        {
            $post['subjects_loaded'] = array();
        }
        $student = $this->db->get_where('tb_mas_users',array("intID"=>$post['studentID']))->first_row('array');
        $tuition = $this->data_fetcher->getTuitionSubjects($post['stype'],$this->data['unit_fee'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$post['scholarship'],$post['subjects_loaded'],$post['studentID'],$student['intTuitionYear']);
       
        
        $ret ='<div class="box box-solid">
            <div class="box-header">
                <h4 class="box-title">ASSESSMENT OF FEES</h4>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">Tuition:</div>
                    <div class="col-sm-6 text-green">'.$tuition['tuition'].'</div>
                </div>
                <hr />
                
                <div class="row">
                    <div class="col-sm-6">Miscellaneous:</div>
                    <div class="col-sm-6 text-green"></div>
                </div>';
            
                foreach($tuition['misc_fee'] as $key=>$val){
                $total_misc = 0;
                $ret .='<div class="row">
                            <div class="col-sm-6" style="text-align:right;">'.$key.'</div>
                            <div class="col-sm-6">'.$val.'</div>
                        </div>';
                 $total_misc += $val;
                }
                
                $ret .= '
                <div class="row">
                    <div class="col-sm-6" style="text-align:right;">Total:</div>
                    <div class="col-sm-6 text-green">'.$total_misc.'</div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-6">ID Fee:</div>
                    <div class="col-sm-6 text-green">'.$tuition['id_fee'].'</div>
                </div>
                <div class="row">
                    <div class="col-sm-6">Athletic Fee:</div>
                    <div class="col-sm-6 text-green">'.$tuition['athletic'].'</div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-6">SRF:</div>
                    <div class="col-sm-6 text-green">'.$tuition['srf'].'</div>
                </div>
                <div class="row">
                    <div class="col-sm-6">SFDF:</div>
                    <div class="col-sm-6 text-green">'.$tuition['sfdf'].'</div>
                </div>
                <div class="row">
                    <div class="col-sm-6">Lab Fee:</div>
                    <div class="col-sm-6">'.$tuition['lab'].'</div>
                </div>
                <div class="row">
                    <div class="col-sm-6" style="text-align:right;">Total:</div>
                    <div class="col-sm-6 text-green">'.$tuition['lab'].'</div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-6">CSG:</div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-6" style="text-align:right;">Student Handbook:</div>
                    <div class="col-sm-6">'.$tuition['csg']['student_handbook'].'</div>
                </div>
                <div class="row">
                    <div class="col-sm-6" style="text-align:right;">Student Publication:</div>
                    <div class="col-sm-6">'.$tuition['csg']['student_publication'].'</div>
                </div>
                <div class="row">
                    <div class="col-sm-6" style="text-align:right;">Total:</div>
                    <div class="col-sm-6 text-green">'.($tuition['csg']['student_handbook']+$tuition['csg']['student_publication']).'</div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-6">Total:</div>
                    <div class="col-sm-6 text-green">'.$tuition['total'].'</div>
                </div>
            </div>
        </div>';
        
        $data['tuition'] = $ret;
        echo json_encode($data);
    }
    
    function generate_student_number()
    {
        
        $post = $this->input->post();
        $data['studentNumber'] = $this->data_fetcher->generateStudentNumber(substr($post['year'],-2));
        
        echo json_encode($data);   
    }
    
    function load_advised_subjects($status = "regular")
    {
        
        $post = $this->input->post();
        $subjects = $this->data_fetcher->getRequiredSubjects($post['sid'],$post['cid'],$post['sem'],$post['year']);
        $s = array();
        
        if($status!="regular")
            for($i=1;$i<=3;$i++)
            {
                if($i != $post['sem']){
                    $s = $this->data_fetcher->getRequiredSubjects($post['sid'],$post['cid'],$i,$post['year']);
                }
                $subjects = array_merge($subjects,$s);
            }
        
        $data = $subjects;
        echo json_encode($data);  
    }
    
    function generate_or()
    {  
        $data['orNumber'] = $this->data_fetcher->generateOR();
        echo json_encode($data);
        
    }

    public function drop_subject(){
        $post = $this->input->post();    
        $registration = $this->data_fetcher->getRegistrationInfo($post['student'],$post['sem']);
        if(!$registration || $registration['intROG'] != 1){
            $data['message'] = "Student has to be enrolled to make adjustments";            
            $data['success'] =  false;
        }    
        elseif($post['date'] == date("Y-m-d")){               
            $section = $this->db->where(array('intID'=>$post['section_to_delete']))->get('tb_mas_classlist')->first_row('array');
            $section_to_swap = $this->db->where(array('intID'=>$post['subject_to_add']))->get('tb_mas_subjects')->first_row('array');
            if($section_to_swap){
                
                $remarks = "Changed to ".$section_to_swap['strCode'];
                $records = $this->data_fetcher->getClassListStudentsSt($post['student'],$post['sem']);
                foreach($records as $record){                    
                    if($record['intClassListID'] != $post['section_to_delete']){
                        $conflict = $this->data_fetcher->student_conflict($post['section_to_add'],$record,$post['sem']);
                        foreach($conflict as $c){
                            if($c){
                                $data['success'] = false;
                                $data['message'] = "There was a conflict with one of the schedules ".$c->conflict['strCode']." ".$c->conflict['strClassName'].$c->conflict['year'].$c->conflict['strSection']." ".$c->conflict['sub_section'];   
                                echo json_encode($data);                             
                                return;
                            }
                        }
                    }
                }

                
            }
            else
                $remarks = "Deleted";

            $subject = $this->db->get_where('tb_mas_subjects',array('intID'=>$section['intSubjectID']))->first_row('array');
            $section_to = $section['strClassName'].$section['year'].$section['strSection'];
            $section_to .= ($section['sub_section'])?"-".$section['sub_section']:"";

            $adj['classlist_student_id'] = $subject['intID'];
            $adj['adjustment_type'] = "Removed";
            $adj['from_subject'] =  $section_to;
            $adj['to_subject'] =  "";
            $adj['syid'] = $post['sem'];
            $adj['date'] = date("Y-m-d H:i:s");  
            $adj['student_id'] =  $post['student'];
            $adj['remarks'] =  $remarks;
            $adj['adjusted_by'] =  $this->session->userdata('intID');
            
            $this->db->insert('tb_mas_classlist_student_adjustment_log',$adj); 
            $this->db->delete('tb_mas_classlist_student', array('intClassListID' => $post['section_to_delete'],'intStudentID'=>$post['student']));

            $down_payment = $this->db->get_where('tb_mas_student_ledger',array('name'=>'Tuition Down Payment','syid'=>$post['sem'],'student_id'=>$post['student'],'is_disabled'=>0))->first_row();
            //record in adjustments table                      
            $tuition = $this->data_fetcher->getTuition($post['student'],$post['sem'],$registration['enumScholarship']);

            if($down_payment)          
                $total = $tuition['ti_before_deductions'];
            else
                $total = $tuition['total_before_deductions'];

            $update['is_disabled'] = 1;
            $this->db->where(array('name'=>'tuition','syid'=>$post['sem'],'student_id'=>$post['student'],'is_disabled'=>0))->update('tb_mas_student_ledger',$update);   
            
            $data['success'] = true;
            $data['message'] = "Success";
            
        }
        else{
            $data['success'] = false;
            $data['message'] = "Invalid";
        }

        echo json_encode($data);
        
    }
    
    public function add_subject_student(){
        $post = $this->input->post();
        $subject = $post['subject_to_add'];
        $replace = false;
        $section_from = "";        
        $section_to = "";
        $data['message'] = "Done";            
        $remarks = "Added";
        $data['success'] =  true;
        
        $records = $this->data_fetcher->getClassListStudentsSt($post['student'],$post['sem']);
        $add_to = $this->db->where(array('intID'=>$post['section_to_add']))->get('tb_mas_classlist')->first_row('array');
        $section_to = $add_to['strClassName'].$add_to['year'].$add_to['strSection'];
        $section_to .= ($add_to['sub_section'])?"-".$add_to['sub_section']:"";
        $registration = $this->data_fetcher->getRegistrationInfo($post['student'],$post['sem']);
        if(!$registration || $registration['intROG'] != 1){
            $data['message'] = "Student has to be enrolled to make adjustments";            
            $data['success'] =  false;
        }
        if($this->data_fetcher->get_classlist_remaining_slots($post['section_to_add']) <= 0){
            $data['success'] = false;
            $data['message'] = "There are no more slots available for this section";
            echo json_encode($data);                             
            return;
        }
        foreach($records as $record){            
            if($subject == $record['subjectID']){
                if($record['classlistID'] == $post['section_to_add'])
                {
                    $data['message'] = "You are transferring the student to the same section";            
                    $data['success'] =  false;                    
                }
                $replace = true;
                $replace_id = $record['classlistID'];
                $classlist_data = $this->db->where(array('intID'=>$record['classlistID']))->get('tb_mas_classlist')->first_row('array');
                $section_from = $classlist_data['strClassName'].$classlist_data['year'].$classlist_data['strSection'];
                $section_from .= ($classlist_data['sub_section'])?"-".$classlist_data['sub_section']:"";
            }
            else{
                $conflict = $this->data_fetcher->student_conflict($post['section_to_add'],$record,$post['sem']);
                foreach($conflict as $c){
                    if($c){
                        $data['success'] = false;
                        $data['message'] = "There was a conflict with one of the schedules ".$c->conflict['strCode']." ".$c->conflict['strClassName'].$c->conflict['year'].$c->conflict['strSection']." ".$c->conflict['sub_section'];   
                        echo json_encode($data);                             
                        return;
                    }
                }
            }
        }

        //remove subject and add new section also add changes to ledger
        

        if($data['success']){
            if($replace){
                $this->db->delete('tb_mas_classlist_student', array('intClassListID' => $replace_id,'intStudentID'=>$post['student']));
                $adj['adjustment_type'] = "Change Section";
                $remarks = "Change Section";
            }
            else{
                $adj['adjustment_type'] = "Add Subject";
                if($post['subject_to_replace'] != 0){
                    $adj['adjustment_type'] = "Replace Subject";
                    $classlist_to_replace = $this->data_fetcher->getClasslistDetails($post['subject_to_replace']);
                    $remarks = "Changed subject from ".$classlist_to_replace->strCode." Section: ".$classlist_to_replace->strClassName.$classlist_to_replace->year.$classlist_to_replace->strSection." ".$classlist_to_replace->sub_section;
                }
            }
                
                $add['date_added'] = date("Y-m-d H:i:s");
                $add['enlisted_user'] = $this->data["user"]["intID"];
                $add['intStudentID'] = $post['student'];
                $add['intClassListID'] = $post['section_to_add'];
                $add['enumStatus'] = "act";   
                $add['intsyID'] = $post['sem'];         
                $this->db->insert('tb_mas_classlist_student',$add);  
                
                
                $adj['classlist_student_id'] = $subject;
                $adj['from_subject'] =  $section_from;
                $adj['to_subject'] =  $section_to;
                $adj['syid'] = $post['sem'];
                $adj['date'] = date("Y-m-d H:i:s");  
                $adj['student_id'] =  $post['student'];
                $adj['remarks'] =  $remarks;
                $adj['adjusted_by'] =  $this->session->userdata('intID');
                
                $this->db->insert('tb_mas_classlist_student_adjustment_log',$adj);  

                $down_payment = $this->db->get_where('tb_mas_student_ledger',array('name'=>'Tuition Down Payment','syid'=>$post['sem'],'student_id'=>$post['student'],'is_disabled'=>0))->first_row();
                //record in adjustments table                      
                $tuition = $this->data_fetcher->getTuition($post['student'],$post['sem'],$registration['enumScholarship']);

                if($down_payment)          
                    $total = $tuition['ti_before_deductions'];
                else
                    $total = $tuition['total_before_deductions'];                            

                if(!$replace){
                    $update['is_disabled'] = 1;
                    $this->db->where(array('name'=>'tuition','syid'=>$post['sem'],'student_id'=>$post['student'],'is_disabled'=>0))->update('tb_mas_student_ledger',$update);                             
                }

        }
                
            

        echo json_encode($data);
    }

    public function sync_current_data(){
        $reg = $this->db->get_where('tb_mas_registration')->result_array();

        foreach($reg as $r){
            $student = $this->db->get_where('tb_mas_users',array('intID'=>$r['intStudentID']))->first_row('array');
            $post['current_program'] = $student['intProgramID'];
            $post['current_curriculum'] = $student['intCurriculumID'];

            $this->db->where('intRegistrationID',$r['intRegistrationID'])->update('tb_mas_registration',$post);

        }
    }


    public function withdraw_student(){
        $post = $this->input->post();
        $auth_data = $this->db->get_where('tb_mas_faculty', array('strUsername'=>$this->session->userdata('strUsername')))->first_row();         
        if(password_verify($post['password'],$auth_data->strPass))
        {
            $records = $this->data_fetcher->getClassListStudentsSt($post['id'],$post['sem']);
            $data['registration'] = $this->db->where(array('intStudentID'=>$post['id'],'intAYID'=>$post['sem']))
            ->get('tb_mas_registration')
            ->first_row();
            //post->period before opening, after opening, end of term
            if($post['period'] == "before"){                
                foreach($records as $record){
                    
                    $adj['classlist_student_id'] = $record['subjectID'];
                    $adj['from_subject'] = "";
                    $adj['to_subject'] =  "";
                    $adj['syid'] = $post['sem'];
                    $adj['date'] = date("Y-m-d H:i:s");  
                    $adj['student_id'] =  $post['id'];
                    $adj['remarks'] =  "Withdrawn";
                    $adj['adjustment_type'] =  "Withdrawn";
                    $adj['adjusted_by'] =  $this->session->userdata('intID');

                    $this->db->insert('tb_mas_classlist_student_adjustment_log',$adj);  
                    
                    $this->db->where(array('intStudentID'=>$post['id'],'intClassListID'=>$record['classlistID']))->delete('tb_mas_classlist_student');                        
                }
                $this->db->where(array('intStudentID'=>$post['id'],'intAYID'=>$post['sem']))->delete('tb_mas_registration');
            }
            else{
                foreach($records as $record){
                    $data =[
                        'floatMidtermGrade' => "OW",
                        'floatFinalGrade' => "OW",
                        'strRemarks' => "Officaly Withdrawn"
                    ];
                    
                    $this->db->where(array('intStudentID'=>$post['id'],'intClassListID'=>$record['classlistID']))->update('tb_mas_classlist_student',$data);
                }     
                $data =[
                    'intROG' => 3,                    
                ];         
                $this->db->where(array('intStudentID'=>$post['id'],'intAYID'=>$post['sem']))->update('tb_mas_registration',$data);
            }

                                
           
            $data['success'] = true;
            $data['message'] = "Student has been withdrawn";
        
        }
        else{
            $data['success'] = false;
            $data['message'] = "Invalid Password";
        }
        

        echo json_encode($data);
        
        

    }

    public function register_old_student($studNum=null,$sem = 0)
    {
        
       
			
            //print_r($post);
            //die();
            $this->data['message'] = $this->session->flashdata('message');
            
            //$this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
            if($studNum==null){
                $post = $this->input->post();
                $this->data['student'] = $this->data_fetcher->getStudent($post['studentNumber']);
            }
            else
                $this->data['student'] = $this->data_fetcher->getStudent($studNum);
                            
            
            if(empty($this->data['student']))
            {
                //Message here for no student found
                $this->session->set_flashdata('error_message','Student does not exist');
                redirect(base_url().'registrar/register_student');
            }
			$this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            if($sem == 0)
                $active_sem = $this->data_fetcher->get_processing_sem();
            else
                $active_sem = $this->data_fetcher->get_sem_by_id($sem);

            $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($this->data['student']['intID'],$active_sem['intID']);
            $sem = 1;
            
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
            
            $this->data['active_sem'] = $active_sem;
                                    
            $this->data['subjects'] = $this->data_fetcher->get_subjects_by_course($this->data['student']['intProgramID'],$sem);
            
            if(!empty($this->data['subjects']))
                $this->data['sections'] = $this->data_fetcher->fetch_classlist_by_subject($this->data['subjects'][0]['intID'],$active_sem['intID']);
           
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/sectioning_student",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/register_old_student_conf",$this->data);
           // print_r($this->data['classlists']);
            
            
    }

    public function cut_off_registration($sem){
        $post = $this->input->post();
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        if($post['date'] == date("Y-m-d")){
            $classlists = $this->db->get_where('tb_mas_classlist',array('strAcademicYear'=>$sem))->result_array();
            foreach($classlists as $classlist){
                $students = $this->db
                                ->select('tb_mas_classlist_student.*,tb_mas_registration.intROG,tb_mas_registration.date_enlisted')
                                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID','left')
                                ->where(array('intClassListID'=>$classlist['intID']))                            
                                ->get('tb_mas_classlist_student')
                                ->result_array();
                foreach($students as $student){                    
                    if(($student['intROG'] == "0" || !isset($student['intROG'])) && $student['date_enlisted'] >= $post['cutoff'] && $student['date_enlisted'] <= $post['cutoff_end']){
                        $this->db->where(array('intCSID'=>$student['intCSID']))                    
                        ->delete('tb_mas_classlist_student');            
                                                                                                                           
                    }
                }
            }

            $this->db->where(array('date_enlisted >='=>$post['cutoff'],'date_enlisted <='=>$post['cutoff_end'],'intROG'=>0))                    
            ->delete('tb_mas_registration');

            $this->data_poster->log_action('Registrar','Cut off Registration for Term: '.$active_sem['term_student_type']." ".$active_sem['enumSem']." ".$active_sem['term_label']." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd'],'green');
            $data['success'] = true;
            $data['message'] = "Successfully Cut off Registration";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Update Failed input valid date";
        }

        echo json_encode($data);

    }
    
    public function leave_of_abscence($id){

        $this->data['id'] =  $id;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/loa",$this->data);
        $this->load->view("common/footer",$this->data); 

    }    

    public function leave_of_abscence_data($id){
        

    }

    public function leave_of_abscence_report($sem){

        $this->data['sem'] =  $sem;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/loa_report",$this->data);
        $this->load->view("common/footer",$this->data); 

    }

    public function leave_of_abscence_report_data($sem){
        

    }

    public function register_old_student_data($studNum,$sem){

        $data['student'] = $this->data_fetcher->getStudent($studNum);
                
        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy',array('intProcessing','desc'));
        $data['scholarship'] = $this->data_fetcher->fetch_single_entry('tb_mas_scholarships',$data['student']['enumScholarship']);
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        $prev_sem = $this->data_fetcher->get_prev_sem($sem,$data['student']['intID']);      
        if(isset($prev_sem))
            $data['prev_reg'] = $this->db->get_where('tb_mas_registration',array('intAYID'=>$prev_sem['intID'],'intStudentID'=>$studNum))->first_row();
        else
            $data['prev_reg'] = null;
        
        $data['reg_status'] = $this->data_fetcher->getRegistrationStatus($data['student']['intID'],$active_sem['intID']);
            $sem = 1;
            
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
            
            $data['active_sem'] = $active_sem;
            $data['term_type'] = $this->data['term_type'];

            $ret['data'] = $data;
            $ret['success'] = true;
            $ret['message'] = "Success";

            echo json_encode($ret);

    }    
    
    public function register_old_student2($studNum=null,$sem)
    {
                   
            if($studNum==null){
                $post = $this->input->post();
                $studNum = $post['studentNumber'];
            }                        

            $this->data['sem'] = $sem;
            
            $this->data['id'] = $studNum;
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/register_old_student",$this->data);
            $this->load->view("common/footer",$this->data); 
            //$this->load->view("common/register_old_student_conf2",$this->data);
            //$this->load->view("registration_validation_js",$this->data);
            // print_r($this->data['classlists']);
            
            
    }

    public function edit_credited_subject(){
        $post = $this->input->post();
        if($this->db->where('id',$post['id'])
                 ->update('tb_mas_credited',$post))
        {
            $data['success'] = true;
            $data['message'] = "Credited Subject successfully updated";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Failed to update";
        }

        echo json_encode($data);
    }

    public function get_sections($subject,$sem){
        
        $sc = [];
        $ret['data'] = $this->data_fetcher->fetch_classlist_by_subject($subject,$sem);
        foreach($ret['data'] as $section){            
            if($section['intID']){
                $sched_text = "";
                $schedules = $this->data_fetcher->getScheduleByCode($section['intID']);
                foreach($schedules as $sched) {
                    if(isset($sched['strDay']))
                        $sched_text = $sched['strDayAbvr'];                    
                        //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
                }
                                
                if(isset($schedules[0]['strDay']))                                                
                    $sched_text .= " ".date('g:ia',strtotime($schedules[0]['dteStart'])).' - '.date('g:ia',strtotime($schedules[0]['dteEnd']));                                                            
                
                                                         
                if(isset($schedules[0]['strDay']))
                    $sched_text.= " ".$schedules[0]['strRoomCode'];
                
                
            }

            $sc[$section['intID']] = $sched_text;
        }
        $ret['schedules'] = $sc;
        echo json_encode($ret);
    }
    
    public function register_old_student_not_post()
    {
        
        
            $this->data['message'] = $this->session->flashdata('message');
            $post = $this->session->flashdata('datapost');
            
            //$this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudentStudentNumber($post['studentNumber']);
			$this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $active_sem = $this->data_fetcher->get_active_sem();
            $sem = 1;
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
            
            $this->data['subjects'] = $this->data_fetcher->get_subjects_by_course($this->data['student']['strProgramCode'],$sem);
            if(!empty($this->data['subjects']))
                $this->data['sections'] = $this->data_fetcher->fetch_classlist_by_subject($this->data['subjects'][0]['intID'],$active_sem['intID']);
           // print_r($this->data['records']);
            
            $this->data['active_sem'] = $active_sem;
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/register_old_student",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/register_old_student_conf",$this->data);
           // print_r($this->data['classlists']);
            
           
    }
    
    public function submit_transaction_ajax()
    {
        $post = $this->input->post();
     
        
        if($this->is_super_admin() || $this->is_accounting()){
            for($i=0;$i < count($post['intAmountPaid']);$i++){
                
                $data['dtePaid'] = date("Y-m-d H:i:s");
                $data['intRegistrationID'] = $post['intRegistrationID'];
                $data['intORNumber'] = $post['intORNumber'];
                $data['intAYID'] = $post['intAYID'];
                $data['intAmountPaid'] = $post['intAmountPaid'][$i];
                $data['strTransactionType'] = $post['strTransactionType'][$i];
                
                $this->data_poster->post_data('tb_mas_transactions',$data);
                $this->data_poster->log_action('Transaction','Added a new Transaction ID: '.$this->db->insert_id(),'green');
                //redirect(base_url()."unity/view_schedules");
                
            }
            $s['message'] = "success";
        }
        else 
            $s['message'] = "Please log in as admin or registrar";
        
        
        echo json_encode($s);
            
    }
    
    public function submit_new_ay()
    {
        $post = $this->input->post();
        $post['strYearEnd'] = $post['strYearStart']+1;
        //print_r($post);
        $this->data_poster->post_data('tb_mas_sy',$post);
        redirect(base_url()."registrar/set_ay");
            
    }
    
    public function set_ay()
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            $this->data['sy'] = $this->db->get_where('tb_mas_sy',array('term_student_type'=>'college'))->result_array();
            $this->data['sy_shs'] = $this->db->get_where('tb_mas_sy',array('term_student_type'=>'shs'))->result_array();
            $current = $this->data_fetcher->get_active_sem();
            $this->data['current'] = $current['intID'];
            $current_shs = $this->data_fetcher->get_active_sem_shs();
            $this->data['current_shs'] = $current_shs['intID'];
            $application = $this->data_fetcher->get_processing_sem();
            $this->data['application'] = $application['intID'];
            $application_shs = $this->data_fetcher->get_processing_sem_shs();
            $this->data['application_shs'] = $application_shs['intID'];
            $this->data['page'] = "set_ay";
            $this->data['opentree'] = "registrar";
            //print_r($this->data['classlist']);
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/set_ay",$this->data);
            $this->load->view("common/footer",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");   
    }
    
    public function submit_ay()
    {
        
        $post = $this->input->post();       

        $current_term = [
            'value' => $post['current']
        ];
        
        $application_term = [
            'value' => $post['application']
        ];

        if($post['currentshs'] || $post['applicationshs'])
        {
            $application_term_shs = [
                'value' => $post['applicationshs']
            ];
            //print_r($post);
            $this->db->where(array('setting_name'=>'shs_default_application'))
                ->update('tb_mas_system_settings',$application_term_shs); 

            $current_term_shs = [
                'value' => $post['currentshs']
            ];
            //print_r($post);
            $this->db->where(array('setting_name'=>'shs_default_term'))
                ->update('tb_mas_system_settings',$current_term_shs); 
            

        }
        
        //print_r($post);
        $this->db->where(array('setting_name'=>'current_term'))
                ->update('tb_mas_system_settings',$current_term);

        //print_r($post);
        $this->db->where(array('setting_name'=>'application_term'))
                ->update('tb_mas_system_settings',$application_term);

                       
        
        $activeSem = $this->data_fetcher->get_active_sem();
        $this->data_poster->log_action('Academic Term','Updated Term: '.$activeSem['enumSem']." term ".$activeSem['strYearStart']."-".$activeSem['strYearEnd'],'blue');
        //echo $activeSem['strYearStart']-1;
        
        redirect(base_url()."registrar/set_ay");
            
    }
    
    public function update_incomplete_subjects($id)
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            $activeSem = $this->data_fetcher->getAy($id);
            $this->data_poster->updateIncompleteSubjects($activeSem['strYearStart'],$activeSem['enumSem']);
        }
        redirect(base_url()."unity");
    }
    
    public function edit_submit_ay()
    {
        $post = $this->input->post();
        //print_r($post);
        if($post['enumStatus'] == "active"){
            $this->data_poster->set_ay_inactive();
            $post['intProcessing'] = "1";
        }
        $post['strYearEnd'] = $post['strYearStart'] + 1;
        $post['midterm_start'] = date("Y-m-d",strtotime($post['midterm_start']));
        $post['midterm_end'] = date("Y-m-d",strtotime($post['midterm_end']));
        $post['final_start'] = date("Y-m-d",strtotime($post['final_start']));
        $post['final_end'] = date("Y-m-d",strtotime($post['final_end']));
        $post['start_of_classes'] = date("Y-m-d",strtotime($post['start_of_classes']));
        $post['final_exam_start'] = date("Y-m-d",strtotime($post['final_exam_start']));
        $post['final_exam_end'] = date("Y-m-d",strtotime($post['final_exam_end']));  
        $post['viewing_midterm_start'] = date("Y-m-d",strtotime($post['viewing_midterm_start']));
        $post['viewing_midterm_end'] = date("Y-m-d",strtotime($post['viewing_midterm_end']));
        $post['viewing_final_start'] = date("Y-m-d",strtotime($post['viewing_final_start']));         
        $post['viewing_final_end'] = date("Y-m-d",strtotime($post['viewing_final_end']));         
       // $this->data_poster->set
        $this->data_poster->post_data('tb_mas_sy',$post,$post['intID']);
        $this->data_poster->log_action('School Year','Updated SY Info: '.$post['enumSem']." ".$post['strYearStart']." - ".$post['strYearEnd'],'aqua');
        redirect(base_url()."registrar/edit_ay/".$post['intID']);
            
    }
    

    
    public function register_student()
    {

        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['page'] = "register_student";
        $this->data['opentree'] = "registrar";
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/register_student",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/registration_conf",$this->data);

        //print_r($this->data['classlist']);
    }
    
    public function add_ay()
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            $this->data['page'] = "add_ay";
            $this->data['opentree'] = "registrar";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_ay",$this->data);
            $this->load->view("common/footer",$this->data); 
            
           // $this->load->view("student_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    
    public function view_all_ay()
    {
        
        $this->data['page'] = "view_academic_year";
        $this->data['opentree'] = "registrar";
        $this->data['academic_years'] = $this->data_fetcher->fetch_table('tb_mas_sy',array('strYearStart','desc'),20);
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/ay_view",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/ay_view_conf",$this->data);             
        //print_r($this->data['classlist']);
            
    }

    public function ched_report($sem)
    {
        $students_array = array();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();

        $students = $this->db->select('tb_mas_users.intID, tb_mas_users.intProgramID, tb_mas_users.strStudentNumber, tb_mas_users.strLastname, tb_mas_users.strFirstname, tb_mas_users.strMiddlename, tb_mas_users.enumGender, tb_mas_users.intStudentYear')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'DESC')
                    ->get()
                    ->result_array();

        foreach($students as $student){
            $student_data = array();
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);  
            $subjects = $this->db->select('tb_mas_subjects.strCode, tb_mas_subjects.strDescription, tb_mas_subjects.strUnits, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
            ->get()
            ->result_array();

            if($subjects){
                $student['course'] = $course['strProgramCode'];
                $student['subjects'] = $subjects;
                $student['student'] = $student['intID'];
                $students_array[] = $student;
            }
        }

        $data['data'] = $students_array;

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
    
    public function is_accounting()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 6)
            return true;
        else
            return false;
        
    }
    
}