<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Academics extends CI_Controller {

	public function __construct()
	{
		parent::__construct();                

        if(!$this->is_academics() && !$this->is_super_admin())
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
           

    public function view_all_students($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$sem=0,$scholarship=0,$registered=0,$level=0)
    {
        if($this->data["user"]["special_role"] >= 1)
        {            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $this->data['page'] = "students";
            $this->data['opentree'] = "academics_students";

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
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/all_students",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/all_students_conf",$this->data);

            
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }   

    public function student_viewer_data($id=0,$sem = null,$tab = null){

        if($this->faculty_logged_in())
        {            
            $post = $this->input->post();
            
             
            if(!empty($post))
               $id = $post['studentID'];
			
            //$this->data['sy'] = $this->data_fetcher->getSemStudent($id);
            $ret['balance'] = $this->data_fetcher->getStudentBalance($id);
            $this->data['upload_errors'] = $this->session->flashdata('upload_errors');
                                   
            
            
            $ret['student'] = $this->data_fetcher->getStudent($id); 
            
            if(!$ret['student'])
                $ret['student'] = $this->data_fetcher->getStudent($id, 'slug');

            $student_type = get_stype($ret['student']['level']);
            $ret['sy'] = $this->db
                              ->where('term_student_type',$student_type)
                              ->get('tb_mas_sy')
                              ->result_array();

            if($sem!=null){
                $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);                 
            }
            else
            {
                if($student_type == "college")
                    $ret['active_sem'] = $this->data_fetcher->get_active_sem();                
                else
                    $ret['active_sem'] = $this->data_fetcher->get_active_sem_shs();                
            }

            $ret['selected_ay'] = $ret['active_sem']['intID'];

            $records = $this->data_fetcher->getClassListStudentsSt($id,$ret['selected_ay']);        
            $sc_ret = [];
            foreach($records as $record)
            {
                $schedule = $this->data_fetcher->getScheduleByCodeNew($record['classlistID']);                                                  
                $sc_ret = array_merge($sc_ret, $schedule);
            }
            
            if($tab!=null)
                $ret['tab'] = $tab;
            else
                $ret['tab'] = "tab_1";

            $ret['other_data'] = 
            array(
                'academic_standing' => null,
                'totalUnitsEarned' => null,
                'gpa_curriculum' => null,
                'academic_standing' => null,

            );


            $ret['deficiencies'] = $this->db
            ->get_where('tb_mas_student_deficiencies',array('student_id'=>$id,'status'=>'active','temporary_resolve_date <'=> date("Y-m-d")))->result_array();
            
            $ret['change_grade'] = $this->db->select('tb_mas_student_grade_change.*,strClassName,year,strSection,sub_section,strCode')
                                             ->join('tb_mas_classlist','tb_mas_student_grade_change.classlist_id = tb_mas_classlist.intID')  
                                             ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
                                             ->where(array('tb_mas_student_grade_change.student_id'=>$id,'strAcademicYear'=>$ret['active_sem']['intID']))
                                             ->order_by('tb_mas_subjects.strCode','ASC')
                                             ->get('tb_mas_student_grade_change')
                                             ->result_array();

 
            $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
            $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);                                             

            $ret['scholarship'] = $this->db->select('tb_mas_scholarships.*')
                                            ->where(array("student_id" => $ret['student']['intID'],"syid"=>$ret['selected_ay'],"deduction_type"=>"scholarship"))
                                            ->join('tb_mas_student_discount','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')  
                                            ->group_by('name')
                                            ->get('tb_mas_scholarships')
                                            ->result_array();

            $ret['discount'] = $this->db->select('tb_mas_scholarships.*')
                                            ->where(array("student_id" => $ret['student']['intID'],"syid"=>$ret['selected_ay'],"deduction_type"=>"discount"))
                                            ->join('tb_mas_student_discount','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')  
                                            ->group_by('name')
                                            ->get('tb_mas_scholarships')
                                            ->result_array();
            

            
            //per faculty info			
            
            $ret['grades'] = $this->data_fetcher->assessCurriculumDept($ret['student']['intID'],$ret['student']['intCurriculumID']);
            
            $ret['other_data']['totalUnitsEarned'] = $this->data_fetcher->unitsEarned($ret['student']['intID'],$ret['student']['intCurriculumID']);
            
            //array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
            //$ret['grades'] = $grades;
            
            $ret['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculum($ret['student']['intCurriculumID'],$ret['selected_ay'],$id);
            
            $ret['other_data']['units_in_curriculum'] = $this->data_fetcher->countUnitsInCurriculum($ret['student']['intCurriculumID']);
            
            if($ret['other_data']['totalUnitsEarned'] != 0)
                $ret['other_data']['gpa_curriculum'] = round($this->data_fetcher->getGPA($ret['student']['intID'],$ret['student']['intCurriculumID'])/$ret['other_data']['totalUnitsEarned'],2);
            else
                $ret['other_data']['gpa_curriculum'] = 0;
            
            $ret['other_data']['academic_standing'] = $this->data_fetcher->getAcademicStanding($ret['student']['intID'],$ret['student']['intCurriculumID']);
            $ret['other_data']['academic_standing']['year'] = switch_num($ret['other_data']['academic_standing']['year']);
            $ret['schedule'] = $sc_ret;

            $ret['user_logged'] = $this->data['user']['intID'];
            $ret['user_level'] = $this->data['user']['intUserLevel'];
                       
            $totalUnits = 0;
            $totalLab = 0;
            $products = [];
            foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
                
                if($record['intLab'] == 1)
                {
                    $totalLab++;
                }                
                if($record['v3'] != 3.50 && $record['v3'] != "0")
                {
                    if ($record['intBridging'] == 1){
                        //$num_of_bridging = count($record['intBridging']);
                        $totalUnits += intval($record['strUnits']);
                        $totalUnits -= 3;
                    }
                    else{
                        $product = intval($record['strUnits']) * $record['v3']; 
                        $products[] = $product;
                        $totalUnits += intval($record['strUnits']);
                    }    
                }                
                    
                if($record['strFirstname']!="unassigned"){
                    $firstNameInitial = substr($record['strFirstname'], 0,1);
                    $record['facultyName'] = $firstNameInitial.". ".$record['strLastname'];  
                }
                else
                    $record['facultyName'] = "unassigned";

                if ($record['strFirstname'] == "unassigned")
                    $record['recStatus'] = "No Assigned Faculty Yet";
                elseif($record['intFinalized'] > 2) 
                    $record['recStatus'] = "Submitted";                    
                else
                    $record['recStatus'] = "Not Yet Submitted";                                                    
                                

                $ret['records'][] = $record;
            }

            $student_grade_table = "";
            $prev_year_sem = '0';
            $sgpa = 0;
            $scount = 0;
            $countBridg = 0;
            $grades = $ret['grades'];
            for($i = 0;$i<count($grades); $i++){
            //echo $prev_year_sem."<br />";
                
                if($grades[$i]['floatFinalGrade']!="0" && $grades[$i]['floatFinalGrade']!="3.5")
                {                                            
                    if ($grades[$i]['intBridging'] == 1) { 
                        $countBridg  = $countBridg + $grades[$i]['intBridging'];
                        $scount += $grades[$i]['strUnits'];
                        $scount-=3;
                    }
                    else {
                        
                        $sgpa += $grades[$i]['floatFinalGrade']*$grades[$i]['strUnits'];
                        $scount+=$grades[$i]['strUnits'];
                    
                    }                
                }

                    
                if($prev_year_sem != $grades[$i]['syID']){
                    $grade = ($grades[$i]['syID'] != 0)?$grades[$i]['enumSem']." Sem A.Y. ".$grades[$i]['strYearStart']." - ".$grades[$i]['strYearEnd']:'Credited Units';
                    $countBridg = 0;
                
                    $student_grade_table = '<table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="4">
                                    '.$grade.'
                                </th>
                            </tr>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Description</th>                                
                                <th>M</th>                                
                                <th>F</th>                                
                                <th>Units</th>
                                <th>Remarks</th>
                                <th>Faculty</th>
                            </tr>
                        </thead>
                        <tbody>';                
                        
                }

                $prev_year_sem = $grades[$i]['syID'];
                if($grades[$i]['strRemarks']=='Failed' || $grades[$i]['strRemarks']=='Failed(U.D.)')
                    $remarks = "red-bg";
                else if(strtoupper($grades[$i]['strRemarks'])=='PASSED')
                    $remarks = "green-bg";
                else
                    $remarks = "";                
                
                $student_grade_table .='    
                    <tr class="'.$remarks.'">
                        <td><a href="'.base_url().'unity/classlist_viewer/'.$grades[$i]['classListID'].'">'.$grades[$i]['strCode'].'</a></td>
                        <td>'.$grades[$i]['strDescription'].'</td>                        
                        <td>'.$grades[$i]['floatMidtermGrade'].'</td>                                                                        
                        <td>'.$grades[$i]['floatFinalGrade'].'</td>
                        <td>'.$grades[$i]['strUnits'].'</td>
                        <td>'.$grades[$i]['strRemarks'].'</td>'; 
                
                if($grades[$i]['strFirstname']!="unassigned"){
                    $firstNameInitial = substr($grades[$i]['strFirstname'], 0,1);
                    $facultyName = $firstNameInitial. ". " . $grades[$i]['strLastname'];  
                }
                else
                    $facultyName = "unassigned";                        

                $student_grade_table .='    
                        <td>'.$facultyName.'</td>
                    </tr>';

                if(isset($grades[$i+1])){
                    if($prev_year_sem != $grades[$i+1]['syID'] || count($grades) == $i+1){
                        $sgpa_computed = $sgpa/$scount;
                        $scount_counted = $scount;
                        $sgpa = 0;
                        $scount = 0;
                    
                    $student_grade_table .='    
                        <tr>
                            <th colspan="4">GPA:'.round($sgpa_computed,2).'</th>
                            <th colspan="6">Units:'.$scount_counted.'</th>
                        </tr>
                        <tr>';

                        if($countBridg > 0){
                            $student_grade_table .='
                                <td colspan="10" style="font-style:italic;font-size:13px;"><small>Note: ('.$countBridg.') Bridging course/s - not computed in units & GPA.</small></td>';
                        }
                        $student_grade_table .='
                                </tr>
                            </tbody>
                        </table>';                
                    }
                }
                
            }
            if($totalUnits > 0)
                $ret['gpa'] = round(array_sum($products) / $totalUnits, 2);
            else
                $ret['gpa'] = 0;

            $ret['total_units'] = $totalUnits;
            $ret['lab_units'] = $totalLab;
            $ret['term_type'] = $this->data['term_type'];
            $ret['img_dir'] = $this->data['img_dir'];
            $ret['photo_dir'] = $this->data['photo_dir'];
            $ret['assessment'] = $student_grade_table;

            $ret['advanced_privilages1'] = (in_array($this->data["user"]['intUserLevel'],array(2,3,4)) )?true:false;
            $ret['advanced_privilages2'] = (in_array($this->data["user"]['intUserLevel'],array(2,3,4,6)) )?true:false;
            $ret['registrar_privilages'] = (in_array($this->data["user"]['intUserLevel'],array(2,3,4,6)) )?true:false;
            
            $sm = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);            
            $term = switch_num_rev($sm['enumSem']);
        
            if(!empty($ret['curriculum_subjects']))
                $ret['sections'] = $this->data_fetcher->fetch_classlist_by_subject($ret['curriculum_subjects'][0]['intSubjectID'],$sm['intID']);
            
            //for total units
            //$ret['total_units'] = $this->data_fetcher->getTotalUnits($id);           
            $ret['success']= true;
        }
        else{
            $ret['message'] = 'denied';
            $ret['success']= false;
        }

        echo json_encode($ret);

    }

    
    public function student_viewer($id=0,$sem = null,$tab = null)
    {
        if($this->data["user"]["special_role"] >= 1)
        {  
            $post = $this->input->post();
            $this->data['id'] = $id;
            $this->data['sem'] = $sem;

            if($sem!=null){
                $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
            }
            else
            {
                $student = $this->data_fetcher->getStudent($id); 
                
                if(!$student)
                    $student = $this->data_fetcher->getStudent($id, 'slug');

                $student_type = get_stype($student['level']);    
                if($student_type == "college")        
                    $ret['active_sem'] = $this->data_fetcher->get_active_sem();
                else
                    $ret['active_sem'] = $this->data_fetcher->get_active_sem_shs();
            }
            
            if(!empty($post))
                $id = $post['studentID'];
            
            if($tab!=null)
                $this->data['tab'] = $tab;
            else
                $this->data['tab'] = "tab_1";
                        
            
            
            $this->data['student'] = $this->data_fetcher->getStudent($id);
            
            if(!$this->data['student'])
                $this->data['student'] = $this->data_fetcher->getStudent($id, 'slug');
            //per faculty info                        

            $this->data['sched_table'] = $this->load->view('sched_table', $this->data, true);
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_viewer",$this->data);
            $this->load->view("common/footer",$this->data);        
        // print_r($this->data['classlists']);
        }
        else
            redirect(base_url()."unity");
            
        
        
    }

    public function view_classlist_archive_admin($sem = null, $program = 0, $dissolved = 0, $has_faculty = 0)
    {
        if($this->data["user"]["special_role"] >= 1)
        {  
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            
            $active_sem = $this->data_fetcher->get_active_sem();
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
            $this->data['program'] = $program;
            $this->data['dissolved'] = $dissolved;
            $this->data['has_faculty'] = $has_faculty;
           
            $this->data['page'] = "classlist_archive";
            $this->data['opentree'] = "classlist_archive";
            // $this->data['opentree'] = "registrar";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/classlist_view_admin",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/classlist_view_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");   
    
    }

    public function deans_listers($term = 0,$period = 0){
        
        if($this->data["user"]["special_role"] >= 1)
        {  
            if($term == 0){
                $term = $this->data_fetcher->get_active_sem();
                $term = $term['intID'];
            }
            if($period == 0)
                $period == "final";

            $this->data['id'] = $id;
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/deans_list",$this->data);
            $this->load->view("common/footer",$this->data); 
        }
        else
            redirect(base_url()."unity");
    }

    public function deans_listers_data($term,$period){
        
        if($this->data["user"]["special_role"] >= 1)
        {  
            $data['success'] = true;
            $data['list'] = $this->db
                                ->where('term_id',$term)
                                ->where('period',$period)
                                ->get('tb_mas_deans_listers')
                                ->result_array();
            $ret['sy'] = $this->db                            
                            ->get('tb_mas_sy')
                            ->result_array();

            echo json_encode($data);
        }
        else
            echo "error";
    }

    public function student_records($id){
        
        if($this->data["user"]["special_role"] >= 1)
        {  
            $this->data['id'] = $id;
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/student_records",$this->data);
            $this->load->view("common/footer",$this->data); 
        }
        else
            redirect(base_url()."unity");
    }

    public function student_records_data($id,$curriculum = 0){

        $data['student'] = $this->data_fetcher->getStudent($id);
        
        $first_registration = $this->db->select('tb_mas_sy.strYearStart,tb_mas_sy.enumSem')
                                ->join('tb_mas_sy','tb_mas_registration.intAYID = tb_mas_sy.intID')  
                                ->where(array('intStudentID'=>$id))
                                ->order_by("strYearStart ASC, enumSem ASC")
                                ->get('tb_mas_registration')
                                ->first_row('array');
        
        if(isset($first_registration['strYearStart']))
            $all_terms = $this->data_fetcher->get_all_past_terms(get_stype($data['student']['level']),$first_registration['strYearStart'],$first_registration['enumSem']);
        else
            $all_terms = [];                          

        if(get_stype($data['student']['level']) == "college")
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_active_sem_shs();

        $data['current_records'] = $this->data_fetcher->getClassListStudentsSt($id,$active_sem['intID']);        

        $registrations = [];
        foreach($all_terms as $trm){
            $registration = $this->db->select('tb_mas_registration.*,tb_mas_sy.enumSem,tb_mas_sy.strYearStart,tb_mas_sy.strYearEnd, tb_mas_sy.term_label,tb_mas_sy.intID as term_id, strProgramCode')
                                  ->join('tb_mas_sy','tb_mas_registration.intAYID = tb_mas_sy.intID')  
                                  ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_registration.current_program')
                                  ->where(array('intStudentID'=>$id,'intAYID'=>$trm['intID']))
                                  ->order_by("strYearStart ASC, enumSem ASC")
                                  ->get('tb_mas_registration')
                                  ->first_row('array');
          
            if(!isset($registration))
                $registrations[] = $trm;
            else    
                $registrations[] = $registration;                   
          
        }

        

        $data['balance'] = $this->data_fetcher->getStudentBalance($id);


        $curicculum = $this->data_fetcher->getSubjectsInCurriculum($data['student']['intCurriculumID']);        
        $data['all_subjects'] = $this->data_fetcher->getSubjectsInCurriculumAlphabetical($data['student']['intCurriculumID']);
        $data['curriculum_subjects'] = [];
        
        $data['deficiencies'] = $this->db
                ->get_where('tb_mas_student_deficiencies',array('student_id'=>$id,'status'=>'active','temporary_resolve_date <'=> date("Y-m-d")))->result_array();

        $data['generated_tor'] = $this->db->get_where('tb_mas_tor_generated',array('student_id'=>$id))->result_array();
        
        $assessment_sum = 0;
        $assessment_units = 0;
        $assessment_units_earned = 0;

        $change_grade = $this->db->select('tb_mas_student_grade_change.*,strClassName,year,strSection,sub_section,strCode,enumSem,term_label,term_student_type,strYearStart,strYearEnd')
            ->join('tb_mas_classlist','tb_mas_student_grade_change.classlist_id = tb_mas_classlist.intID')  
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->join('tb_mas_sy','tb_mas_classlist.strAcademicYear = tb_mas_sy.intID')
            ->where(array('tb_mas_student_grade_change.student_id'=>$id))            
            ->order_by('strYearStart asc, enumSem asc')
            ->get('tb_mas_student_grade_change')
            ->result_array();
        
        foreach($curicculum as $cs){
            $recs = 
            $this->db->select('floatFinalGrade,strRemarks,tb_mas_subjects.strUnits,tb_mas_subjects.include_gwa,tb_mas_subjects.strCode,intFinalized')
                     ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')  
                     ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')                                              
                     ->where(array('tb_mas_classlist.intSubjectID'=>$cs['intSubjectID'],'tb_mas_classlist_student.intStudentID'=>$data['student']['intID'],'tb_mas_classlist_student.strRemarks !='=>'Officially Withdrawn'))                     
                     ->get('tb_mas_classlist_student')
                     ->result_array();            
            foreach($recs as $temp_rec){
                $current = false;                
                foreach($data['current_records'] as $current_rec){
                    if($temp_rec['strCode'] == $current_rec['strCode']){
                        $temp_rec['floatFinalGrade'] = $current_rec['v3'];
                        $current = true;
                        break;
                    }
                }
                if($temp_rec && $temp_rec['include_gwa'] && $temp_rec['intFinalized'] == 2){                
                    switch($temp_rec['floatFinalGrade']){
                        case 'FA':
                            $grade = 5;
                            $temp_rec['bg'] = "#990000";
                            $temp_rec['color'] = "#f2f2f2";
                        break;
                        case 'UD':
                            $grade = 5;
                            $temp_rec['bg'] = "#990000";
                            $temp_rec['color'] = "#f2f2f2";
                        break;
                        default:
                            $grade = $temp_rec['floatFinalGrade'];
                            $temp_rec['bg'] = "#009000";
                            $temp_rec['color'] = "#f2f2f2";
                    }                             
    
                    $assessment_units += $temp_rec['strUnits'];   
                    $assessment_sum += $grade * $temp_rec['strUnits'];         
                }
                if($current){
                    $temp_rec['bg'] = "#ADD8E6";
                    $temp_rec['color'] = "#333";
                }
                if($temp_rec['strRemarks'] == "Passed" && $temp_rec['intFinalized'] == 2){
                    $cs['rec'] = $temp_rec;
                    $assessment_units_earned += $temp_rec['strUnits'];
                    break;
                }
                else
                    $cs['rec'] = $temp_rec;
            }

            $cs['equivalent'] = $this->db->get_where('tb_mas_credited',array('equivalent_subject'=>$cs['intSubjectID'],'student_id'=>$data['student']['intID']))->first_row();
            if(!isset($cs['rec'])){
                if($cs['equivalent'])
                    $cs['rec']['bg'] = "#00AA00";               
            }
            
                     
            $data['curriculum_subjects'][$cs['intYearLevel']][$cs['intSem']]['year'] = $cs['intYearLevel'];
            $data['curriculum_subjects'][$cs['intYearLevel']][$cs['intSem']]['sem'] = $cs['intSem'];
            $data['curriculum_subjects'][$cs['intYearLevel']][$cs['intSem']]['records'][] = $cs;
        }
        $assessment_gwa = 0;
        if($assessment_units > 0){
            $assessment_gwa = $assessment_sum/$assessment_units;
            $assessment_gwa = number_format(round($assessment_gwa,3),3);
        }

        $terms = [];
        $total_units_earned = 0;
        $total_units_gwa = 0;
        $gwa = 0;

        $credited_subjects = [];

        $terms_in_credited = $this->db->where(array('student_id'=>$id))
                                      ->order_by('school_year asc, term asc')
                                      ->group_by(array('school_year','term','completion'))
                                      ->get('tb_mas_credited')
                                      ->result_array();
                    
        foreach($terms_in_credited as $term_credited){

            $credited = $this->db->select('tb_mas_credited.*,strCode,intID')
                                ->join('tb_mas_subjects','tb_mas_credited.equivalent_subject = tb_mas_subjects.intID','left')
                                ->where(array('student_id'=>$id,'term'=>$term_credited['term'],'school_year'=>$term_credited['school_year'],'completion'=>$term_credited['completion']))
                                ->order_by('course_code','asc')                                
                                ->get('tb_mas_credited')
                                ->result_array();
            
            $credited_data = array(
                'term' => $term_credited['term'],
                'school' => $term_credited['completion'],
                'school_year' => $term_credited['school_year'],
            );     

            $credited_subjects[] = array('records'=>$credited,'other_data'=>$credited_data);
        }


        //Check Curriculum for units earned
        foreach($registrations as $reg){
            $syid = isset($reg['intAYID'])?$reg['intAYID']:$reg['intID'];
            $records = $this->data_fetcher->getClassListStudentsSt($id,$syid); 
            $units = 0;
            $sum_grades = 0;
            $units_earned = 0;
            $total = 0;
            foreach($records as $record){
                if($record['intFinalized'] == 2 && $record['strRemarks'] == "Passed" && $record['include_gwa'])
                    $units_earned += $record['strUnits'];
                if($record['intFinalized'] == 2 && $record['include_gwa'] && $record['strRemarks'] != "Officially Withdrawn"){
                    switch($record['v3']){
                        case 'FA':
                            $v3 = 5;
                        break;
                        case 'UD':
                            $v3 = 5;
                        break;
                        default:
                            $v3 = $record['v3'];
                    }                    
                    $sum_grades += $v3 * $record['strUnits'];
                    $total += $record['strUnits'];
                }


            }
            $total_units_earned += $units_earned;
            $term_gwa = 0;
            if($total > 0){
                $term_gwa = $sum_grades/$total;
                $term_gwa = number_format(round($term_gwa,3),3);
            }
            $gwa += $sum_grades;
            $total_units_gwa += $total;
            $terms[] = array('records'=> $records,'reg'=>$reg,'units_earned'=>$units_earned,'gwa'=>$term_gwa);
        }

        if($total_units_gwa > 0){
            $gwa = $gwa/$total_units_gwa;
            $gwa = number_format(round($gwa,3),3);
        }

        $data['gwa'] = $gwa;
        $data['change_grades'] = $change_grade;
        $data['assessment_gwa'] = $assessment_gwa;
        $data['assessment_units'] = $assessment_units_earned;
        $data['total_units_earned'] = $total_units_earned;
        $data['credited_subjects'] = $credited_subjects;
        $data['data'] = $terms;

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
    
    public function is_academics()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 0)
            return true;
        else
            return false;
        
    }
    
}