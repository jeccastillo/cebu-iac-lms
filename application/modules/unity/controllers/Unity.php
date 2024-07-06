<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unity extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
        $this->config->load('courses');
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
		$this->load->model("google_login");	
		$this->load->model("facebook_login");	
		$this->load->model("user_model");
        $this->config->load('courses');
        $this->data['campus'] = $this->config->item('campus');
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
        $this->data['photo_dir'] = base_url()."assets/photos/";
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		
        $this->data["page"] = "default";
        
	}
    
    public function index()
	{	
        
        if($this->faculty_logged_in())
            redirect(base_url()."unity/faculty_dashboard");
        
        else
            redirect(base_url()."users/login");
        
        
	}

    
    public function faculty_dashboard()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "dashboard";
            $this->data['title'] ="Dashboard";
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $this->data['app_sem'] = $this->data_fetcher->get_processing_sem();
            $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
            $this->data['pwd'] = $this->session->userdata('strPass');
            $this->data["faculty_data"] = $this->session->all_userdata();
            $students = $this->data_fetcher->count_table_contents('tb_mas_users',null,array('isGraduate'=>0));
            $resident_scholars = $this->data_fetcher->count_table_contents('tb_mas_users',null,array('enumScholarship'=>'resident scholar','isGraduate'=>0));
            $seventh_district = $this->data_fetcher->count_table_contents('tb_mas_users',null,array('enumScholarship'=>'7th district scholar','isGraduate'=>0));
            $registered_students = $this->data_fetcher->count_table_contents('tb_mas_registration',null,array('intAYID'=>$this->data['active_sem']['intID']));
           
            $this->data['registration_data_all'] = $this->data_fetcher->getRegistrationData($this->data['active_sem']['intID']);
            
            
            $this->data['submitted_classlists'] = $this->data_fetcher->count_classlist(1);
            $this->data['un_submitted_classlists'] = $this->data_fetcher->count_classlist(0);
            $myclasses = $this->data_fetcher->count_table_contents('tb_mas_classlist',null,array('intFacultyID'=>$this->session->userdata('intID'),'strAcademicYear'=>$this->data['active_sem']['intID']));
            $allclasses = $this->data_fetcher->count_table_contents('tb_mas_classlist');
            $this->data['myclasses'] = $myclasses;
            $this->data['allclasses']= $allclasses;
            $this->data['students'] = $students;
            $this->data['registered'] = $registered_students;
            $this->data['student_course'] = array();
            $this->data['scholar1'] = $seventh_district;
            $this->data['scholar2'] = $resident_scholars;
            
            $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
            $ret_prog = array();
            
            foreach($programs as $prog)
            {
                $prog['numStudents'] = $this->data_fetcher->countStudentsByCourse($prog['intProgramID']);
                $ret_prog[] = $prog;    
            }
            
            $this->data['student_course'] = $ret_prog;
            
            $this->data['num_subjects'] = count($this->data["subjects"]);
            $this->data['faculty_logged_in'] = $this->faculty_logged_in();
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/dashboard",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("dashboard_js",$this->data);
        }
        else
            redirect(base_url()."unity");
    }
    
    public function logs($start=null,$end=null, $cat=null)
    {
        if($this->is_super_admin() || ($this->is_accounting() && ($cat == "Cashier" || $cat == "Payment%20Term%20Forwarded")) || $this->is_registrar())
        {
            if($cat == "Leave%20of%20Abscences"){
                $this->data['page'] = "loa_logs";
                $this->data['opentree'] = "students";
                $this->data['title'] ="Logs for Student Status";
            }
            elseif($cat == "Payment%20Term%20Forwarded"){
                $this->data['page'] = "logs_forwarded";
                $this->data['opentree'] = "finance_admin";
                $this->data['title'] ="Logs for Forwarded Payment";
            }
            elseif($cat == "Cashier"){
                $this->data['page'] = "logs_cashier";
                $this->data['opentree'] = "finance_admin";
                $this->data['title'] ="Logs for Cashier Setup";
            }
            else{
                $this->data['page'] = "logs";
                $this->data['opentree'] = "admin";
                $this->data['title'] ="Logs";
            }
            
            
            $this->data['cat'] = $cat;
            $cat = urldecode($cat);
            $this->data['logs'] = $this->data_fetcher->fetch_logs($start,$end,$cat);
        
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/logs",$this->data);
            $this->load->view("common/footer",$this->data);   
        }
        else
            redirect(base_url()."unity");
    }
    
    public function transactions($start=null,$end=null)
    {
        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {
            $this->data['page'] = "transactions";
            $this->data['opentree'] = "accounting";
            $this->data['title'] ="Transactions";
            
            $this->data['start'] = $start;
            $this->data['end'] = $end;
            
            $this->data['transactions'] = $this->data_fetcher->fetch_transactions($start,$end);
            
            if($start == null || $end == null)
            {
                $this->data['dateF'] = "Select Date Range"; 
            }
            else
            {
                $this->data['dateF'] = date("M j, Y",strtotime($start))." to ".date("M j, Y",strtotime($end));
            }
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/transaction_view",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/transactions_conf",$this->data);
        }
        else
            redirect(base_url()."unity");
    }
    
    
    public function faculty_classlists($sem = null)
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            $this->data["faculty_data"] = $this->session->all_userdata();
            $this->data['faculty_logged_in'] = $this->faculty_logged_in();
            $this->data['title'] ="Add Classlist";
            $this->data['page'] = "add_classlist";
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['curriculum'] = $this->data_fetcher->fetch_table('tb_mas_curriculum');
       
            $active_sem = $this->data_fetcher->get_active_sem();
            
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
        
            
            $this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->data['faculty'] = $this->data_fetcher->fetch_table('tb_mas_faculty',array('strLastname','asc'));
    
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist_view",$this->data);
            $this->load->view("common/footer",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");
    }
    
    public function generate_classlists($id)
    {
        $userlevel = $this->session->userdata('intUserLevel');
        if($userlevel == 2 || $userlevel == 3)
        {
            $this->data["faculty_data"] = $this->session->all_userdata();
            $this->data['faculty_logged_in'] = $this->faculty_logged_in();
            $this->data['curriculum'] = $this->data_fetcher->getItem('tb_mas_curriculum',$id);
            $this->data['title'] ="Generate Classlists";
            $this->data['page'] = "generate_classlists";
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
       
            $active_sem = $this->data_fetcher->get_active_sem();
            
            $this->data['selected_ay'] = $active_sem['intID'];
        
            
    
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/generate_classlists",$this->data);
            $this->load->view("common/footer",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");
    }
    
    public function submit_generate_class()
    {
        
        $post = $this->input->post();
        //print_r($post);
        $curriculum = $this->data_fetcher->getItem('tb_mas_curriculum',$post['curriculum']);
        $program = $this->data_fetcher->getItem('tb_mas_programs',$curriculum['intProgramID'],'intProgramID');
        $sem = $this->data_fetcher->getItem('tb_mas_sy',$post['strAcademicYear']);
        $wcurr['intSem'] = switch_num_rev($sem['enumSem']);
        
        $subjects = $this->data_fetcher->getSubjectsCurriculumSem($curriculum['intID'],$wcurr['intSem'],$post['year']);
        
        for($i=0;$i<$post['num_sections'];$i++)
            {  
            foreach($subjects as $subj)
            {                                    
                $cl = $this->data_fetcher->checkClasslistExistsGen($subj['intID'],$post['strAcademicYear'],$program['short_name']);
                //echo $subj['strCode']." ".$cl."<br />";                
                $data['intCurriculumID'] = $post['curriculum'];
                $data['intFacultyID'] = 999;
                $data['intSubjectID'] = $subj['intID'];
                $data['strClassName'] = $program['short_name'];
                $data['strAcademicYear'] = $post['strAcademicYear'];
                $data['strUnits'] = $subj['strUnits'];
                $data['strSection'] = $cl;       
                $data['year'] = $post['year'];         
                $this->data_poster->post_data('tb_mas_classlist',$data);                
            }
        }
        
        
        redirect(base_url()."unity/view_classlist_curriculum/".$post['curriculum']."/".$post['strAcademicYear']);
            
            
       
    }

    public function view_classlist_curriculum($id, $sem){
        $active_sem = $this->data_fetcher->get_active_sem();

        if($sem!=null)
            $data['selected_ay'] = $sem;
        else
            $data['selected_ay'] = $active_sem['intID'];
        
        $data['id'] =  $id;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/classlist_curriculum_view",$data);
        $this->load->view("common/footer",$this->data); 
    }
    
    public function edit_classlist($id)
    {
        $clist = $this->data_fetcher->fetch_classlist_by_id(null,$id);
        if($this->is_registrar() || $this->is_super_admin())
        {
            $this->data["faculty_data"] = $this->session->all_userdata();
            $this->data['faculty_logged_in'] = $this->faculty_logged_in();
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');            
            $this->data['classlist'] = $clist;
            
            //print_r($this->data['classlist']);
            $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
            $sIn = $this->data_fetcher->getClassListStudents($id);
            $st = array();
            foreach($sIn as $s)
            {
                $st[] = $s['intID'];
            }
            $this->data['students_in'] = $st;
            $this->data['admin'] = $this->is_admin();
            //print_r($this->data['classlist']);
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist_edit",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/edit_classlist_conf",$this->data); 
            $this->load->view("common/edit_classlist_foot",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function reassign_classlist($id)
    {
        
        if($this->is_admin())
        {
            $this->data["faculty_data"] = $this->session->all_userdata();
            $this->data['faculty_logged_in'] = $this->faculty_logged_in();
            $this->data['classlist'] = $this->data_fetcher->fetch_classlist_by_id(null,$id);
            $this->data['teacher'] = $this->data_fetcher->fetch_table('tb_mas_faculty',array('strLastName','asc'), null, array('teaching'=>1));
            
          
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist_reassign",$this->data);
            $this->load->view("common/footer",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    public function registered_students_report($sem = null)
    {
        if($this->is_admin() || $this->is_registrar())
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
            //$total_resident = 0;
            $total_freehe = 0;
            $total_7th_district =0;
            $total_paying = 0; 
            $total_dilg = 0;
            
            foreach($programs as $prog)
            {
                $r['program'] = $prog['strProgramCode'];
                
                //$r['resident_scholars'] = $this->data_fetcher->getScholars($prog['intProgramID'],'resident scholar',$this->data['selected_ay']);
                //$total_resident+=$r['resident_scholars'];
                
                $r['free_he'] = $this->data_fetcher->getScholars($prog['intProgramID'],'FREE HIGHER EDUCATION PROGRAM (R.A. 10931)',$this->data['selected_ay']);
                $total_freehe+=$r['free_he'];
                
                $r['paying'] = $this->data_fetcher->getScholars($prog['intProgramID'],'paying',$this->data['selected_ay']);
                $total_paying+= $r['paying'];
                
                $r['seventh_district'] = $this->data_fetcher->getScholars($prog['intProgramID'],'7th district',$this->data['selected_ay']);
                $total_7th_district+= $r['seventh_district'];
                
                $r['dilg_scholar'] = $this->data_fetcher->getScholars($prog['intProgramID'],'DILG scholar',$this->data['selected_ay']);
                $total_dilg+= $r['dilg_scholar'];
                
                $r['total_row'] = $r['dilg_scholar'] + $r['seventh_district'] + $r['paying'] + $r['free_he'];
                $report[] = $r;
            }
            
            
            //$this->data['total_resident'] = $total_resident;
            $this->data['total_freehe'] = $total_freehe;
            $this->data['total_paying'] = $total_paying;
            $this->data['total_seventh_district'] = $total_7th_district;
            $this->data['total_dilg'] = $total_dilg;
            $this->data['total_all'] = $total_freehe + $total_paying + $total_7th_district +$total_dilg;
            $this->data['report'] = $report;
            
            $this->data['page'] = "registered_students";
            $this->data['opentree'] = "reports";
            //print_r($this->data['classlist']);
            $this->load->view("common/print_header",$this->data);
            $this->load->view("admin/registered_students_report",$this->data);
            $this->load->view("common/footer",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");   
    }
    
    public function edit_registration($id,$sem = null)
    {
        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['scholarships'] = $this->data_fetcher->fetch_table('tb_mas_scholarships');

            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];

            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);

            $this->data['student'] = $this->data_fetcher->getStudent($id);
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_registration",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/edit_registration_conf",$this->data); 
        }
        else
            redirect(base_url()."unity");
    }

    public function tag_loa(){
        $post =  $this->input->post();        
        $auth_data = $this->db->get_where('tb_mas_faculty', array('strUsername'=>$this->session->userdata('strUsername')))->first_row();
        if(password_verify($post['password'],$auth_data->strPass))
        {
            $registration = $this->db->get_where('tb_mas_registration',array('intAYID'=>$post['term_id'],'intStudentID'=>$post['student_id']))->first_row();
            if(!$registration){
                $reg_data = 
                [
                    'intStudentID'=> $post['student_id'],
                    'enlisted_by' => $this->data['user']['intID'],
                    'intAYID' => $post['term_id'],
                    'enumRegistrationStatus' => 'regular',
                    'enumStudentType' => 'continuing',
                    'intYearLevel' => 1,
                    'intROG' => 4,
                    'loa_remarks' => $post['loa_remarks'],
                    'loa_date' => $post['loa_date']
                ];

                $this->db->insert('tb_mas_registration',$reg_data);
            }
            else{
                $reg_data = [
                    'intROG' => 4,
                    'loa_remarks' => $post['loa_remarks'],
                    'loa_date' => $post['loa_date']
                ];
                $this->db->where(array('intAYID'=>$post['term_id'],'intStudentID'=>$post['student_id']))
                        ->update('tb_mas_registration',$reg_data);

            }
            if($registration->intROG == 1){
                $records = $this->data_fetcher->getClassListStudentsSt($post['student_id'],$post['term_id']);
            
                foreach($records as $record){
                    $data =[
                        'floatMidtermGrade' => "OW",
                        'floatFinalGrade' => "OW",
                        'strRemarks' => "Officaly Withdrawn"
                    ];
                
                    $this->db->where(array('intStudentID'=>$post['student_id'],'intClassListID'=>$record['classlistID']))->update('tb_mas_classlist_student',$data);
                }     
            }           
        
            $data['message'] = "successfully updated";
            $data['success'] = true;
            $active_sem = $this->data_fetcher->get_sem_by_id($post['term_id']);

            $this->data_poster->log_action('Registrar','Tagged Student '.$post['student_name'].' For LOA: '.$active_sem['term_student_type']." ".$active_sem['enumSem']." ".$active_sem['term_label']." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd'],'green');
        }
        else{
            $data['message'] = "Invalid password";
            $data['success'] = false;
        }

        echo json_encode($data);
    }

    public function sync_tuition()
    {
        if($this->is_super_admin()){
            $registrations = $this->db->get('tb_mas_registration')->result_array();
            foreach($registrations as $reg){
                $rec = $this->db->get_where('tb_mas_user_tuition',array('syid'=>$reg['intAYID'],'student_id'=>$reg['intStudentID']))->first();
                if(!$rec){
                    $tuition =  $this->data_fetcher->getTuition($reg['intStudentID'],$reg['intAYID']);
                    $other = $tuition['new_student'] + $tuition['total_foreign'];
                    $data = [
                        'student_id' => $reg['intStudentID'],
                        'syid' => $reg['intAYID'],
                        'tuition' => $tuition['tuition_before_discount'],
                        'tuition_installment' => $tuition['tuition_installment_before_discount'],
                        'misc' => $tuition['misc_before_discount'],
                        'misc_installment' => $tuition['misc_before_discount'],
                        'laboratory' => $tuition['lab_before_discount'],
                        'laboratory_installment' => $tuition['lab_installment_before_discount'],
                        'other' => $other,
                        'other_installment' => $other,
                    ];
                }
            }
        }
    }    
    public function registration_viewer_data($id,$sem){
        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {
            $active_sem = $this->data_fetcher->get_active_sem();
            $ret['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');

            $ret['ledger'] = $this->db->select('tb_mas_student_ledger.*')        
            ->from('tb_mas_student_ledger')            
            ->where(array('student_id'=>$id,'tb_mas_student_ledger.type'=>'tuition','syid' => $sem))                    
            ->get()
            ->result_array();

            if($sem!=null)
                $ret['selected_ay'] = $sem;
            else
                $ret['selected_ay'] = $active_sem['intID'];

            $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
            if($ret['registration']){
                $data['tuition'] = $this->data_fetcher->getTuition($id,$ret['selected_ay']);
                $ret['tuition_data'] = $data['tuition'];
                $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);
                $ret['discounts'] = $this->db->get_where('tb_mas_registration_discount',array('registration_id'=>$ret['registration']['intRegistrationID']))->result_array();
            }
            else
                $data['tuition'] = "";

            

            $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
            $ret['tuition_years'] = $this->db->get_where('tb_mas_tuition_year')->result_array();
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);      
            $ret['cashier'] = $this->db->get_where('tb_mas_cashier',array('user_id'=>$this->data['user']['intID']))->first_row();      
            $ret['user_logged'] = $this->data['user']['intID'];
            $ret['user_level'] = $this->data['user']['intUserLevel'];
            $ret['user'] = $this->data['user'];
            $ret['student'] = $this->data_fetcher->getStudent($id);
            //$ret['transactions'] = $this->data_fetcher->getTransactions($ret['registration']['intRegistrationID'],$ret['selected_ay']);
            //$payment = $this->data_fetcher->getTransactionsPayment($ret['registration']['intRegistrationID'],$ret['selected_ay']);
            // $pay =  array();
            // foreach($payment as $p){
            //     if(isset($pay[$p['strTransactionType']]))
            //         $pay[$p['strTransactionType']] += $p['intAmountPaid'];
            //     else
            //         $pay[$p['strTransactionType']] = $p['intAmountPaid'];

            // }
            // $ret['payment'] = $pay;
            $role = $this->session->userdata('special_role');
            $ret['advanced_privilages'] = (in_array($role,array(1,2)) )?true:false;            
            $ret['finance_manager_privilages'] = ($role == 2)?true:false;    
               
            
            //--------TUITION-------------------------------------------------------------------
            $registrations =  $this->db->select('tb_mas_sy.*, paymentType')
                ->join('tb_mas_sy', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                ->where(array('intStudentID'=>$ret['student']['intID']))
                ->order_by("strYearStart asc, enumSem asc")
                ->get('tb_mas_registration')
                ->result_array();
            
            $term_balances = [];
            
            foreach($registrations as $reg){            
                $tuition = $this->data_fetcher->getTuition($ret['student']['intID'],$reg['intID']);                                                    
                $term_payments = $this->db->query("SELECT subtotal_order from payment_details WHERE student_number = '".$ret['student']['slug']."' AND sy_reference=".$reg['intID']." AND status = 'Paid' AND ( description LIKE 'Tuition%' OR description LIKE 'Reservation%')")
                                         ->result_array();   
                                         
                $ledger_payments = $this->db                                                
                ->where(array('student_id'=>$ret['student']['intID'],'tb_mas_student_ledger.type'=>'tuition','syid' => $reg['intID'],'amount <'=>0))                                        
                ->get('tb_mas_student_ledger')
                ->result_array();                                         

                $paid = 0;
                foreach($term_payments as $payment){
                    $paid += $payment['subtotal_order'];
                }       
                foreach($ledger_payments as $payment){
                    $paid -= $payment['amount'];
                }       
                if($reg['paymentType'] == "full")
                    $balance = $tuition['total'] - $paid;
                else
                    $balance = $tuition['total_installment'] - $paid;

                $term_balances[] = [
                    'formatted_balance'=> number_format($balance,2),
                    'balance'=>$balance,
                    'payment_type'=>$reg['paymentType'],
                    'term'=>$reg['enumSem']." ".$reg['term_label']." S.Y.".$reg['strYearStart']."-".$reg['strYearEnd']
                ];
            }

            $ret['term_balances'] = $term_balances;

            
            $ret['success']= true;
        }
        else{
            $ret['message'] = 'denied';
            $ret['success']= false;
        }
        echo json_encode($ret);

    }

    public function online_payment_data($id,$sem){
        

        $ret['student'] = $this->data_fetcher->getStudent($id);

        if(get_stype($ret['student']['level']) == "college")
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_active_sem_shs();

        if($sem!=null)
            $ret['selected_ay'] = $sem;
        else
            $ret['selected_ay'] = $active_sem['intID'];

        $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
        $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
        $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);            

        
        $ret['transactions'] = $this->data_fetcher->getTransactions($ret['registration']['intRegistrationID'],$ret['selected_ay']);
        $payment = $this->data_fetcher->getTransactionsPayment($ret['registration']['intRegistrationID'],$ret['selected_ay']);
        $pay =  array();
        foreach($payment as $p){
            if(isset($pay[$p['strTransactionType']]))
                $pay[$p['strTransactionType']] += $p['intAmountPaid'];
            else
                $pay[$p['strTransactionType']] = $p['intAmountPaid'];

        }
        $ret['payment'] = $pay;        
        //--------TUITION-------------------------------------------------------------------
        $data['tuition'] = $this->data_fetcher->getTuition($id,$ret['selected_ay']);
        $ret['tuition_data'] = $data['tuition'];
        $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);
        $ret['success']= true;
        
        echo json_encode($ret);

    }

    public function registration_viewer($id,$sem = null)
    {
        
        $active_sem = $this->data_fetcher->get_active_sem();        

        $data['campus'] =  $this->data['campus'];

        if($sem!=null)
            $data['selected_ay'] = $sem;
        else
            $data['selected_ay'] = $active_sem['intID'];
        
        $data['id'] =  $id;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/registration_viewer",$data);
        $this->load->view("common/footer",$this->data);         
    }

    public function adjustments($id,$sem = null)
    {
        
        $active_sem = $this->data_fetcher->get_active_sem();

        if($sem!=null)
            $data['selected_ay'] = $sem;
        else
            $data['selected_ay'] = $active_sem['intID'];
        
        $data['id'] =  $id;

        $this->load->view("common/header",$this->data);
        $this->load->view("admin/adjustments",$data);
        $this->load->view("common/footer",$this->data);         
    }

    public function adjustments_data($id,$sem){
        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {
            $active_sem = $this->data_fetcher->get_active_sem();

            if($sem!=null)
                $ret['selected_ay'] = $sem;
            else
                $ret['selected_ay'] = $active_sem['intID'];

            $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
            if($ret['registration']){
                $data['tuition'] = $this->data_fetcher->getTuition($id,$ret['selected_ay']);
                $ret['tuition_data'] = $data['tuition'];
                $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);
            }
            else
                $data['tuition'] = "";
            $ret['records'] = [];
            $records = $this->data_fetcher->getClassListStudentsSt($id,$ret['selected_ay']);
            foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
                $ret['records'][] = $record;
            }
            //tb_mas_classlist_student_adjustment_log
            $ret['adjustments'] = $this->db
                                       ->select('tb_mas_classlist_student_adjustment_log.*, strCode, strFirstname, strLastname')
                                       ->from('tb_mas_classlist_student_adjustment_log')  
                                       ->join('tb_mas_subjects', 'tb_mas_classlist_student_adjustment_log.classlist_student_id = tb_mas_subjects.intID')                                     
                                       ->join('tb_mas_faculty', 'tb_mas_classlist_student_adjustment_log.adjusted_by = tb_mas_faculty.intID')                                     
                                       ->where(array('student_id'=>$id,'syid'=>$sem))
                                       ->order_by('tb_mas_classlist_student_adjustment_log.date','asc')
                                       ->get()
                                       ->result_array();

            $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);      
            $ret['user_logged'] = $this->data['user']['intID'];
            $ret['student'] = $this->data_fetcher->getStudent($id);
            $ret['subjects_available'] = $this->data_fetcher->getOfferedSubjects($ret['student']['intID'],$ret['student']['intCurriculumID'],$sem);           
            $ret['advanced_privilages'] = (in_array($this->data["user"]['intUserLevel'],array(2,3)) )?true:false;
            //--------TUITION-------------------------------------------------------------------
            
            
            $ret['success']= true;
        }
        else{
            $ret['message'] = 'denied';
            $ret['success']= false;
        }
        echo json_encode($ret);
    }

    public function student_tuition_payment($id,$sem = 0)
    {
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        $student = $this->data_fetcher->getStudent($id, 'slug');            
        $data['selected_ay'] = $active_sem['intID'];
        $ret['active_sem'] = $active_sem;      
        $data['id'] = $student['intID'];
        

        $this->load->view("public/header",$this->data);
        $this->load->view("public/payment_online_tuition",$data);
        $this->load->view("public/footer",$this->data);         
    }  

    public function test_student_tuition_payment($id,$sem = 0)
    {
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        $student = $this->data_fetcher->getStudent($id, 'slug');            
        $data['selected_ay'] = $active_sem['intID'];
        $data['id'] = $student['intID'];
        

        $this->load->view("public/header",$this->data);
        $this->load->view("public/mock_payment_tuition",$data);
        $this->load->view("public/footer",$this->data);         
    }

    public function student_tuition_payment_bdo($id,$sem = 0)
    {
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        $student = $this->data_fetcher->getStudent($id, 'slug');            
        $data['selected_ay'] = $active_sem['intID'];
        $data['id'] = $student['intID'];
        $data['slug'] = $id;
        
        $this->load->view("public/header",$this->data);
        $this->load->view("public/payment_online_tuition_bdo",$data);
        $this->load->view("public/footer",$this->data); 
        
    }  
    
    public function confirm_program($slug) {                
        
        $student = $this->data_fetcher->getStudent($slug, 'slug');                    
        $data['id'] = $student['intID'];
        
           
        $this->load->view('public/header',$this->data);        
		$this->load->view('public/confirm_program',$data);
		$this->load->view('public/footer',$this->data);
    }

     public function student_exam($slug,$exam_id) {                
        
        // $student = $this->data_fetcher->getStudent($slug, 'slug');
        $answers = array();
        $studentExamQuestion = $this->data_fetcher->getStudentExamQuestion($slug, 'student_id');
        foreach($studentExamQuestion as $question){
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

            $answerArray = array(
                'question' => $question['strTitle'],
                'image' => $question['questionImage'] ? base_url() . 'assets/photos/exam/' .$question['questionImage'] : '',
                'choice_selected' => $question['choice_selected'],
                'is_correct' => $question['is_correct'],
                'choices' => $choice_array
                
            );
            $answers[] = $answerArray;
        }

        // $data['id'] = $student['intID'];
        $data['answers'] = $answers;
           
        $this->load->view('public/header',$this->data);        
		$this->load->view('public/student_exam',$data);
		$this->load->view('public/footer',$this->data);
    }

    public function schedule_viewer($id) {                
        
        $data['id'] = $id;        
        $data['sched_table'] = $this->load->view('sched_table', $this->data, true);        
           
        $this->load->view('public/header',$this->data);        
		$this->load->view('public/schedule_viewer',$data);
		$this->load->view('public/footer',$this->data);
    }

    public function program_confirmation_section($sectionID){
        $active_sem = $this->data_fetcher->get_active_sem();
        $section = $this->data_fetcher->fetch_single_entry('tb_mas_block_sections',$sectionID);                
        $ret['section'] = $section;
        $ret['success']= true;
        
        echo json_encode($ret);
    }

    public function program_confirmation_sub_data($programId){
        $active_sem = $this->data_fetcher->get_active_sem();
        $sections = $this->data_fetcher->getBlockSectionsPerProgram($programId,$active_sem['intID']);                      
        $ret['sections'] = $sections;
        $ret['success']= true;
        
        echo json_encode($ret);
    }

    public function program_confirmation_data($id){
        $active_sem = $this->data_fetcher->get_active_sem();        
        $ret['student'] = $this->data_fetcher->getStudent($id);        
        $sections = $this->data_fetcher->getBlockSectionsPerProgram($ret['student']['intProgramID'],$active_sem['intID']);              
        $ret['sched_table'] = $this->load->view('sched_table', $this->data, true); 
        $ret['sections'] = $sections;
        $selected = $this->db->get_where('tb_mas_programs',array('intProgramID'=>$ret['student']['intProgramID']))->first_row();
        $ret['selected'] = $selected->strProgramDescription;
        $ret['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
        $ret['success']= true;
        
        echo json_encode($ret);
    }
    
    public function student_confirm_program(){
        
        $post = $this->input->post();
        $id = $post['id'];
        unset($post['id']);
        
        $post['intCurriculumID'] = $this->data_fetcher->getCurriculumIDByCourse($post['intProgramID']);
        
        if($this->db
             ->where('intID',$id)
             ->update('tb_mas_users',$post)){
        
                $ret['sched_table'] = $this->load->view('sched_table', $this->data, true); 
                $ret['success'] = true;
                $ret['message'] = "Updated Successfully";
        }
        else{
            $ret['success'] = false;
            $ret['message'] = "Failed to update";
        }
        
        echo json_encode($ret);
    }

    function accounting($id,$sem=null)
    {
        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {                
            //print_r($this->data['transactions']);
            $this->data['id'] = $id;
            $this->data['sem'] = $sem;
            $this->data['student'] = $this->data_fetcher->getStudent($id);
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/accounting_viewer",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/registration_viewer_conf",$this->data); 
        }
        else
            redirect(base_url()."unity");
        
    }

    function accounting_viewer_data($id,$sem=null){

        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {
            if($sem == null)
                $sy = $this->data_fetcher->get_active_sem();
            else
                $sy = $this->data_fetcher->get_sem_by_id($sem);
                        
            $sdata['student'] = $this->data_fetcher->getStudent($id);            
            if(!$sdata['student'])
                $sdata['student'] = $this->data_fetcher->getStudent($id, 'slug');
            
            $id = $sdata['student']['intID'];

            $sdata['selected_ay'] = $sy['intID'];
            $sdata['registration'] = $this->data_fetcher->getRegistrationInfo($id,$sdata['selected_ay']);            
            $sdata['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$sdata['selected_ay']);
            

            

            $reg = $this->data_fetcher->getRegistrationInfo($id,$sy['intID']);
            if(!empty($reg)){ 
                $sdata['sy'] = $sy;
                $pdata['tuition'] = $this->data_fetcher->getTuition($id,$sy['intID']);                      
                $sdata['tuition'] = $pdata['tuition'];
                $sdata['tuition_view'] = $this->load->view('tuition/tuition_view', $pdata, true);
                $sdata['tuition_view_table'] = $this->load->view('tuition/tuition_view_table', $pdata, true);
            }
            
            $sdata['advanced_privilages'] = (in_array($this->data["user"]['intUserLevel'],array(2,4)) )?true:false;

            $data['data'] = $sdata;
            $data['success'] = true;
            $data['message'] = "Success";

        }
        else{
            $data['data'] = null;
            $data['success'] = false;
            $data['message'] = "Invalid Access";
        }
            
        echo json_encode($data);
    }

    function get_active_sem($id){
        
        $data['active_sem'] = $this->data_fetcher->get_sem_by_id($id);
        echo json_encode($data);

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
    function get_tuition_ajax(){
        
        $post = $this->input->post();              
        $post['subjects_loaded'] =  explode(',', $post['subjects_loaded']);        
        $student = $this->db->get_where('tb_mas_users',array("intID"=>$post['studentID']))->first_row('array');
        $data['tuition'] = $this->data_fetcher->getTuitionSubjects($post['stype'],0,0,$post['subjects_loaded'],$post['studentID'],$post['type_of_class'],$post['sem'],$student['intTuitionYear']);
        $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);                
        $ret['full_data'] = $data['tuition'];
        
        echo json_encode($ret);
    }

    
    
    public function execute_sync()
    {
        if($this->is_super_admin())
        {
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/execute_sync_validate",$this->data);
            $this->load->view("common/footer",$this->data); 
        }
        else
            redirect(base_url()."unity");
            
    }
    
    public function delete_registration($id,$sem)
    {
        $role = $this->session->userdata('special_role');
        if($this->is_super_admin() || ($this->is_registrar() && $role == 2))
        {
            $this->data['sem'] = $sem;
            $this->data['student'] = $this->data_fetcher->getStudent($id);
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/execute_delete_registration",$this->data);
            $this->load->view("common/footer",$this->data); 
        }
        else
            redirect(base_url()."unity");
            
    }

    public function add_credit(){
        $post = $this->input->post();
        $post['added_by'] = $this->data['user']['strFirstname']." ".$this->data['user']['strLastname'];
        $post['date_added'] = date("Y-m-d");
        if($this->db->insert('tb_mas_credited',$post)){
            $data['success'] = true;
            $data['message'] = "Successfully credited subject";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Oops something went wrong.";
        }

        echo json_encode($data);
    }

    public function update_academic_status(){
        $post = $this->input->post();

        if($this->db->where('intRegistrationID',$post['intRegistrationID'])
            ->update('tb_mas_registration',$post)){
            $data['success'] = true;
            $data['message'] = "Successfully updated Status";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Oops something went wrong.";
        }

        echo json_encode($data);
    }

    public function delete_credited(){
        $post = $this->input->post();
        $credited = $this->db->get_where('tb_mas_credited',array('id'=>$post['id']))->first_row();
        if($this->db->where('id',$post['id'])->delete('tb_mas_credited')){
            $data['success'] = true;
            $data['message'] = "Successfully deleted credited subject";
            $this->data_poster->log_action('Credited','Deleted a Credited Subject '.$credited->course_code.' for student with id '.$credited->student_id,'red');
        }
        else{
            $data['success'] = false;
            $data['message'] = "Oops something went wrong.";
        }

        echo json_encode($data);
    }

    public function get_student_records($id,$term){
        $records = $this->data_fetcher->getClassListStudentsSt($id,$term);
        $units_enlisted = 0;
        foreach($records as $item){
            $units_enlisted += $item['strUnits'];
        }
        $data['total_units'] = $units_enlisted;
        $data['success'] = true;
        echo json_encode($data);
    }

    public function student_records($id){
        
        $this->data['id'] = $id;
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/student_records",$this->data);
        $this->load->view("common/footer",$this->data); 
    }

    public function student_records_data($id,$curriculum = 0){

        $data['student'] = $this->data_fetcher->getStudent($id);
        
        $all_terms =  $this->db->select('tb_mas_sy.*')  
                ->join('tb_mas_sy','tb_mas_registration.intAYID = tb_mas_sy.intID')                
                ->where(array('intStudentID'=>$id))
                ->order_by("strYearStart asc, enumSem asc")
                ->get('tb_mas_registration')
                ->result_array();                          

        if(get_stype($data['student']['level']) == "college")
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_active_sem_shs();

        $data['current_records'] = $this->data_fetcher->getClassListStudentsSt($id,$active_sem['intID']);        

        $registrations = [];
        foreach($all_terms as $trm){
            $registration = $this->db->select('tb_mas_registration.*,tb_mas_sy.enumSem,tb_mas_sy.strYearStart,tb_mas_sy.strYearEnd, tb_mas_sy.term_label,tb_mas_sy.intID as term_id, strProgramCode')
                                  ->join('tb_mas_sy','tb_mas_registration.intAYID = tb_mas_sy.intID')  
                                  ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_registration.current_program','left')
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
        $credited_units = 0;
        $curriculum_units = 0;     
        $curriculum_units_na = 0;   

        $change_grade = $this->db->select('tb_mas_student_grade_change.*,strClassName,year,strSection,sub_section,strCode,enumSem,term_label,term_student_type,strYearStart,strYearEnd')
            ->join('tb_mas_classlist','tb_mas_student_grade_change.classlist_id = tb_mas_classlist.intID')  
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->join('tb_mas_sy','tb_mas_classlist.strAcademicYear = tb_mas_sy.intID')
            ->where(array('tb_mas_student_grade_change.student_id'=>$id))            
            ->order_by('strYearStart asc, enumSem asc')
            ->get('tb_mas_student_grade_change')
            ->result_array();
        
        foreach($curicculum as $cs){
            if($cs['include_gwa'])
                $curriculum_units += $cs['strUnits'];
            else
                $curriculum_units_na += $cs['strUnits'];

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
                if($temp_rec && $temp_rec['intFinalized'] == 2){                
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
                        case '5.00':
                            $grade = 5;
                            $temp_rec['bg'] = "#990000";
                            $temp_rec['color'] = "#f2f2f2";
                        break;
                        default:
                            $grade = $temp_rec['floatFinalGrade'];
                            $temp_rec['bg'] = "#009000";
                            $temp_rec['color'] = "#f2f2f2";
                    }                             
    
                    if($temp_rec['include_gwa'] && $grade != "OW"){                        
                        $assessment_units += $temp_rec['strUnits'];   
                        $assessment_sum += $grade * $temp_rec['strUnits'];         
                    }
                }
                elseif($current){
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
            
            foreach($credited as $cr)
                $credited_units += $cr['units'];

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
                if($record['intFinalized'] == 2 && $record['strRemarks'] == "Passed")
                    $units_earned += $record['strUnits'];
                if($record['intFinalized'] == 2 && $record['include_gwa'] && $record['strRemarks'] != "Officially Withdrawn"){
                    switch($record['v3']){
                        case 'FA':
                            $v3 = 5;
                        break;
                        case 'UD':
                            $v3 = 5;
                        break;
                        case '5.00':
                            $v3 = 5;
                        break;
                        default:
                            $v3 = $record['v3'];
                    }                  
                    if($v3 != "OW"){ 
                        $sum_grades += $v3 * $record['strUnits'];                
                        $total += $record['strUnits'];
                    }
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
        $data['credited_units'] = $credited_units;
        $data['curriculum_units'] = $curriculum_units;
        $data['curriculum_units_na'] = $curriculum_units_na;        
        $data['units_left'] =  $curriculum_units - $assessment_units_earned - $credited_units;
        $data['data'] = $terms;

        echo json_encode($data);

    }
    
    public function delete_registration_confirm()
    {
        $post = $this->input->post();
        $id = $post['studentid'];
        $this->data_poster->removeRegistration($id,$post['sem']);
        redirect(base_url()."unity/student_viewer/".$id);
    }
    
    public function sync_users()
    {
        $post = $this->input->post();
        if(isset($post['execute'])){
            $this->data_fetcher->executeAcademicSync();
            
        }
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

            $data['student_link'] = base_url()."unity/student_viewer/".$ret['student']['intID'];
            $ret['tuition_payment_link'] = base_url()."unity/student_tuition_payment/".$ret['student']['slug'];
            $ret['notif_message'] = "Congratulations, you have been registered for ".$ret['active_sem']['enumSem']." ".$ret['active_sem']['']." S.Y. ".$ret['active_sem']['strYearStart']."-".$ret['active_sem']['strYearEnd'];
            $sem_id = $ret['active_sem']['intID'];

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
                        $product = intval($record['strUnits']) * (float)$record['v3']; 
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
                        
                        $sgpa += (float)$grades[$i]['floatFinalGrade']*$grades[$i]['strUnits'];
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
            
            
            $registrations =  $this->db->select('tb_mas_sy.*, paymentType')
                ->join('tb_mas_sy', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                ->where(array('intStudentID'=>$ret['student']['intID']))
                ->order_by("strYearStart asc, enumSem asc")
                ->get('tb_mas_registration')
                ->result_array();
            
            $term_balances = [];
            foreach($registrations as $reg){     
                if($reg['intID'] != $sem_id){
                    $tuition = $this->data_fetcher->getTuition($ret['student']['intID'],$reg['intID']);                                                    
                    $term_payments = $this->db->query("SELECT subtotal_order from payment_details WHERE student_number = '".$ret['student']['slug']."' AND sy_reference=".$reg['intID']." AND status = 'Paid' AND ( description LIKE 'Tuition%' OR description LIKE 'Reservation%')")
                                                ->result_array();   
                                                
                    $ledger_payments = $this->db                                                
                    ->where(array('student_id'=>$ret['student']['intID'],'tb_mas_student_ledger.type'=>'tuition','syid' => $reg['intID'],'amount <'=>0))                                        
                    ->get('tb_mas_student_ledger')
                    ->result_array();                                         

                    $paid = 0;
                    foreach($term_payments as $payment){
                        $paid += $payment['subtotal_order'];
                    }       
                    foreach($ledger_payments as $payment){
                        $paid -= $payment['amount'];
                    }       
                    if($reg['paymentType'] == "full")
                        $balance = $tuition['total'] - $paid;
                    else
                        $balance = $tuition['total_installment'] - $paid;

                    $term_balances[] = [
                        'formatted_balance'=> number_format($balance,2),
                        'balance'=>$balance,
                        'payment_type'=>$reg['paymentType'],
                        'term'=>$reg['enumSem']." ".$reg['term_label']." S.Y.".$reg['strYearStart']."-".$reg['strYearEnd']
                    ];
                }
            }
            $ret['term_balances'] = $term_balances;
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
       
        $user_level = $this->session->userdata('intUserLevel');
        
        if($user_level == 6)
            redirect(base_url().'unity/registration_viewer/'.$id.'/'.$sem);
        if($user_level != 2 && $user_level != 3 && $user_level != 7)
            redirect(base_url().'unity');
        
        $max_id = $this->db->select('updated_at')
            ->order_by('updated_at', 'DESC')
            ->limit(1)
            ->get('payment_details')
            ->first_row();

        // Data to be sent in the POST request
        if($max_id)
            $this->data['max_id'] = $max_id->updated_at;
        else
            $this->data['max_id'] = 0;
        

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

    public function compute_gwa($id){
        
        $data['student'] = $this->data_fetcher->getStudent($id);
        
        if(!$this->data['student'])
            $data['student'] = $this->data_fetcher->getStudent($id, 'slug');

        // $totalUnitsEarned = $this->data_fetcher->unitsEarned($ret['student']['intID'],$ret['student']['intCurriculumID']);
        
        // //array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
        // //$ret['grades'] = $grades;
        
        // $ret['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculum($ret['student']['intCurriculumID'],$ret['selected_ay'],$id);
        
        // $ret['other_data']['units_in_curriculum'] = $this->data_fetcher->countUnitsInCurriculum($ret['student']['intCurriculumID']);
        
        // if($ret['other_data']['totalUnitsEarned'] != 0)
        //     $ret['other_data']['gpa_curriculum'] = round($this->data_fetcher->getGPA($ret['student']['intID'],$ret['student']['intCurriculumID'])/$ret['other_data']['totalUnitsEarned'],2);
        // else
        //     $ret['other_data']['gpa_curriculum'] = 0;

        echo json_encode($data);
    
    }
    
    public function edit_sections($id=0)
    {
        if($this->is_department_head() || $this->is_super_admin())
        {
            $post = $this->input->post();
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            if(!empty($post))
               $id = $post['studentID'];
			
            //$this->data['sy'] = $this->data_fetcher->getSemStudent($id);
            
            $this->data['upload_errors'] = $this->session->flashdata('upload_errors');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
                
        
            
            $this->data['selected_ay'] = $this->data['active_sem']['intID'];
           
            $registration = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
            $reg_status = $this->data_fetcher->getRegistrationStatus($id,$this->data['selected_ay']);
            
            $this->data['student'] = $this->data_fetcher->getStudent($id);
			$records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);
            
            
            foreach($records as $record)
            {
                $record['sections'] = $this->data_fetcher->getSectionsSubject($record['subjectID'],$this->data['selected_ay']);
                //print_r($record['schedule']);
                $this->data['records'][] = $record;
            }
            
            $sm = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
            
            $term = switch_num_rev($sm['enumSem']);
        
            if(!empty($this->data['curriculum_subjects']))
                $this->data['sections'] = $this->data_fetcher->fetch_classlist_by_subject($this->data['curriculum_subjects'][0]['intSubjectID'],$sm['intID']);
            
            //for total units
            $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
           // print_r($this->data['records']);
            
            $this->load->view("common/header",$this->data);
            if(!$registration)
                $this->load->view("admin/update_sections",$this->data);
            else
                $this->load->view("admin/update_sections_registered",$this->data);
            
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/update_sections_conf",$this->data);
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }
    
    
    function add_to_classlist_ajax()
    {
        $post = $this->input->post();
        
        $classlist = $this->data_fetcher->fetch_classlist_by_id(null,$post['section']);
        
        $subject = $this->data_fetcher->getSubjectNoCurr($classlist['intSubjectID']);
        
        $send['strUnits'] = $subject['strUnits'];
        $send['intStudentID'] = $post['studentID'];
        $send['intClassListID'] = $classlist['intID'];
        
        $student = $this->data_fetcher->fetch_table('tb_mas_classlist_student',null,null,array('intStudentID'=>$post['studentID'],'intClassListID'=>$classlist['intID'],'floatFinalGrade <'=>3.5));
        $active_sem = $this->data_fetcher->get_active_sem();
        
       
        $enlisted = $this->data_fetcher->checkStudentSubject($active_sem['intID'],$classlist['intSubjectID'],$post['studentID']);
        
        $taken = $this->data_fetcher->checkStudentSubjectTaken($classlist['intSubjectID'],$post['studentID']);
        
       if(!$this->is_super_admin() && !$this->is_registrar()){
            $data['message'] = "failed2";
        }
        elseif($classlist['intFinalized'] == 1)
            $data['message'] = "failed";
        elseif(!empty($enlisted))
            $data['message'] = "failed3";
        elseif(!empty($taken))
            $data['message'] = "failed4";
        elseif(empty($student)){
            $this->data_poster->addStudentClasslist($send,$this->data["user"]["intID"]);
            $data['message'] = "success";
        }
       
        echo json_encode($data);
    }
    
    function generate_student_number()
    {
        
        $post = $this->input->post();
        $data['studentNumber'] = $this->data_fetcher->generateStudentNumber(substr($post['year'],-2));
        
        echo json_encode($data);   
    }
    
    function generate_password()
    {
        
        $post = $this->input->post();
        $data['password'] = $this->data_fetcher->generatePassword();
        
        echo json_encode($data);   
    }
    
    function load_advised_subjects($status = "regular")
    {
        
        $post = $this->input->post();
        $isOld = $this->data_fetcher->checkIfStudentNew($post['sid']);
        
        if($isOld)
            $subjects = $this->data_fetcher->getRequiredSubjects($post['sid'],$post['cid'],$post['sem'],$post['year']);
        else
            $subjects = $this->data_fetcher->getRequiredSubjects($post['sid'],$post['cid'],1,1);
       
        $data = $subjects;
        echo json_encode($data);  
    }
    
    function generate_or()
    {  
        $data['orNumber'] = $this->data_fetcher->generateOR();
        echo json_encode($data);
        
    }
    
    public function edit_curriculum($id)
    {
        
        if($this->is_registrar() || $this->is_super_admin())
        {
          
            $this->data['item'] = $this->data_fetcher->getItem('tb_mas_curriculum',$id);
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            $this->data['subjects'] = $this->data_fetcher->getSubjectsNotInCurriculum($id);
            $curriculum = $this->data_fetcher->getSubjectsInCurriculum($id);
            $this->data['curriculum_subjects'] = [];
             
            foreach($curriculum as $subject){
                $prereq_array = 
                        $this->db->select('tb_mas_subjects.*,tb_mas_prerequisites.program')
                         ->from('tb_mas_prerequisites')
                         ->join('tb_mas_subjects', 'tb_mas_prerequisites.intPrerequisiteID = tb_mas_subjects.intID')
                         ->where('intSubjectID',$subject['intSubjectID'])
                         ->get()
                         ->result_array();
                $subject['prereq'] = [];    
                foreach($prereq_array as $prereq){
                        if(isset($prereq['program']) && ($prereq['program'] == 0  || $prereq['program'] == $this->data['item']['intID'] || $prereq['program'] == NULL))
                            $subject['prereq'][] =  $prereq;     
                }       
    
                $this->data['curriculum_subjects'][] = $subject;
            }   

            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_curriculum",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/edit_curriculum_conf",$this->data); 
            $this->load->view("curriculum_validation_js",$this->data); 
           // print_r($this->data['classlists']);
            
        }
        else
            redirect(base_url()."unity");    
        
        
    }


   
    
    public function add_subjects_curriculum()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {
            $post = $this->input->post();
            //print_r($post);
            
            foreach($post['subject'] as $subject)
            {
                $data = array('intSubjectID'=>$subject,'intCurriculumID'=>$post['intCurriculumID'],'intYearLevel'=>$post['intYearLevel'],'intSem'=>$post['intSem']);
                $this->data_poster->post_data('tb_mas_curriculum_subject',$data);
            }
            
        }
        redirect(base_url()."unity/view_all_curriculum");
    }
    
    public function submit_class()
    {
        $post = $this->input->post();
        //print_r($post);
        $subject = $this->data_fetcher->getSubjectNoCurr($post['intSubjectID']);
        $post['strUnits'] = $subject['strUnits'];                                     
        $this->data_poster->log_action('Classlist','Added a new Classlist '.$post['strClassName'],'green');
        $this->data_poster->post_data('tb_mas_classlist',$post);
        redirect(base_url()."unity/faculty_classlists");
            
    }

    
    public function submit_schedule_ajax()
    {
        $post = $this->input->post();
        $subject = $this->data_fetcher->getSubject($post['subject']);
        unset($post['subject']);
        
        $post['intSem'] = $subject['intSem'];
        
        $post['strScheduleCode'] = $subject['strCode'].'-'.$post['section'];
        
        $section = $post['section'];
        unset($post['section']);
        
        //print_r($post);
        $conflict = $this->data_fetcher->schedule_conflict($post,null,$subject['intSem']);
        $sconflict = $this->data_fetcher->section_conflict($post,null,$section,$subject['intSem']);
        
        if(!empty($conflict)){
            
            $data['message'] = "conflict in room schedule";
        }
        else if(!empty($sconflict)){
            
            $data['message'] = "conflict in section schedule";
        }
        else
        {
            $this->data_poster->post_data('tb_mas_room_schedule',$post);
            $this->data_poster->log_action('Schedule','Added a new Schedule Room Sched ID: '.$this->db->insert_id(),'green');
            //redirect(base_url()."unity/view_schedules");
            $data['message'] = "success";
        }
        
        echo json_encode($data);
            
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
               
                
                if($data['intAmountPaid']!= "")
                {
                    $this->data_poster->post_data('tb_mas_transactions',$data);
                    $this->data_poster->log_action('Transaction','Added a new Transaction ID: '.$this->db->insert_id(),'green');
                }
                //redirect(base_url()."unity/view_schedules");
                
            }
            $s['message'] = "success";
        }
        else 
            $s['message'] = "Please log in as admin or registrar";
        
        
        echo json_encode($s);
            
    }
    
    public function submit_curriculum()
    {
        if($this->is_registrar() || $this->is_super_admin()){
            $post = $this->input->post();
            //print_r($post);
            $this->data_poster->log_action('Subject','Added a new Curriculum '.$post['strName'],'blue');
            $this->data_poster->post_data('tb_mas_curriculum',$post);
            redirect(base_url()."unity/view_all_curriculum");
            
        }
    }
    
    public function get_subjects_ajax()
    {
        $post = $this->input->post();
        echo json_encode($this->data_fetcher->get_subjects_by_course($post['curriculum']));
    }
    
    public function get_curriculum_ajax()
    {
        $post = $this->input->post();
        echo json_encode($this->data_fetcher->get_curriculum_by_course($post['course']));
    }
    
    function get_sections_ajax()
    {
        $post = $this->input->post();
        $sections = $this->data_fetcher->fetch_classlist_by_subject_no_count($post['subject_id'],$post['sem']);
        echo json_encode($sections);
    }
    
    public function submit_subject_ajax()
    {
        $post = $this->input->post();
        //print_r($post);
        $this->data_poster->log_action('Subject','Added a new Subject '.$post['strCode'],'yellow');
        $this->data_poster->post_data('tb_mas_subjects',$post);
        $data['newid'] = $this->db->insert_id();
        $data['code'] = $this->input->post('strCode');
        echo json_encode($data);    
    }
    
    public function submit_edit_curriculum()
    {
        if($this->is_registrar() || $this->is_super_admin()){
            $post = $this->input->post();
            //print_r($post);
            $this->data_poster->post_data('tb_mas_curriculum',$post,$post['intID']);
            $this->data_poster->log_action('Curriculum','Updated Curriculum Info: '.$post['strName'],'green');
        }
        redirect(base_url()."unity/view_all_curriculum");


    }
    
    public function submit_edit_registration()
    {
        $post = $this->input->post();
       
        $this->data_poster->post_data('tb_mas_registration',$post,$post['intRegistrationID'],'intRegistrationID');
        $this->data_poster->log_action('Registration','Updated Registration Info: '.$post['intRegistrationID'],'green');
        redirect(base_url()."unity/edit_registration/".$post['intStudentID']);
    }
    
    
    public function edit_class()
    {
        $post = $this->input->post();
        $date = date("Y-m-d H:i:s");
        
        unset($post['student-chooser_length']);
        if($post['r1'] == "student"){
            if(isset($post['students'])){
                $st = $post['students'];
                
                foreach($st as $s)
                {
                    $p['intStudentID'] = $s;
                    $p['intClassLIstID'] = $post['intID'];
                    $p['date_added'] = $date;
                    $p['enlisted_user'] = $this->data["user"]["intID"];
                   
                    $t = $this->data_fetcher->getCS($s,$post['intID']);
                    //for units
                   $p['strUnits'] = $post['strUnits'];
                    if(empty($t))
                        $this->data_poster->post_data('tb_mas_classlist_student',$p);
                }
            }
        }
        else
        {
            $section = $this->data_fetcher->getStudentSection($post['sec_sel_course'],$post['sec_sel_year'],$post['sec_sel_section']);
            //print_r($section);
            
            foreach($section as $st)
                {

                    $p['intStudentID'] = $st['intID'];
                    $p['intClassLIstID'] = $post['intID'];
                    $p['date_added'] = $date;
                    $p['enlisted_user'] = $this->data["user"]["intID"];
                    $t = $this->data_fetcher->getCS($st['intID'],$post['intID']);
                
                    $p['strUnits'] = $post['strUnits'];
                    if(empty($t))
                        $this->data_poster->post_data('tb_mas_classlist_student',$p);

                }
        }
        
        unset($post['students']);
        unset($post['sec_sel_course']);
        unset($post['sec_sel_year']);
        unset($post['sec_sel_section']);
        unset($post['r1']);
        if(isset($post['intSubjectID']))
        {
            $subject = $this->data_fetcher->getSubjectPlain($post['intSubjectID']);
            $post['strClassName'] = $subject['strCode'];
        }
        $this->data_poster->post_data('tb_mas_classlist',$post,$post['intID']);
        //redirect(base_url()."unity/view_classlist");
        redirect(base_url()."unity/classlist_viewer/". $post['intID']);
            
    }
    public function reassign_class()
    {
        $post = $this->input->post();
        //print_r($post);
        
        unset($post['student-chooser_length']);
        if(isset($post['students'])){
            $st = $post['students'];
            unset($post['students']);
            foreach($st as $s)
            {

                $p['intStudentID'] = $s;
                $p['intClassLIstID'] = $post['intID'];
                $p['enlisted_user'] = $this->data["user"]["intID"];
                $t = $this->data_fetcher->getCS($s,$post['intID']);
                if(empty($t))
                    $this->data_poster->post_data('tb_mas_classlist_student',$p);

            }
        }
        $this->data_poster->post_data('tb_mas_classlist',$post,$post['intID']);
        redirect(base_url()."unity/view_classlist_archive_admin");
            
    }
    public function view_classlist()
    {
        if($this->faculty_logged_in())
        {
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['classlist'] = $this->data_fetcher->fetch_classlists(null,false,$active_sem['intID']);
            $this->data['page'] = "view_classlist";
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/my_classlist_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");   
    
    }
    
    
    
    public function view_classlist_archive($sem = null)
    {
        if($this->faculty_logged_in())
        {
            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            
            $active_sem = $this->data_fetcher->get_active_sem();
            
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
            //$this->data['classlists'] = $this->data_fetcher->fetch_classlists(null,false,$this->data['selected_ay']);
            
            $this->data['page'] = "view_classlist";
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist_archive",$this->data);
            
            $this->load->view("common/footer",$this->data); 
            $this->load->view("faculty/classlist_archive_conf",$this->data);
            //print_r($this->data['selected_ay']);
            
        }
        else
            redirect(base_url()."unity");   
    
    }
    
    public function view_classlist_archive_admin($sem = null, $program = 0, $dissolved = 0, $has_faculty = 0)
    {
        if($this->is_admin() || $this->is_registrar())
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
           
            //$this->data['classlists'] = $this->data_fetcher->fetch_classlists_all(null,$this->data['selected_ay']);
            $this->data['page'] = "classlist_archive";
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
    
     public function view_classlist_archive_dept($sem = null)
    {
        if($this->is_admin() || $this->is_department_head())
        {
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            
            $active_sem = $this->data_fetcher->get_active_sem();
            if($sem!=null)
                $this->data['selected_ay'] = $sem;
            else
                $this->data['selected_ay'] = $active_sem['intID'];
            
           
            $this->data['classlists'] = $this->data_fetcher->fetch_classlists_dept($this->session->userdata('strDepartment'),$this->is_super_admin(),null,$this->data['selected_ay']);
            
            $this->data['page'] = "classlist_archive";
            $this->data['opentree'] = "department";
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist_archive_dept",$this->data);
            $this->load->view("common/footer",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");   
    
    }
    
    
    public function duplicate_classlist($id)
    {
        if($this->is_super_admin() || $this->is_registrar())
        {
            $date = date("Y-m-d H:i:s");
            $classlist = current($this->data_fetcher->fetch_table('tb_mas_classlist',null,null,array('intID'=>$id)));
            if(!$classlist['intFinalized']){
                $st = $this->data_fetcher->fetch_table('tb_mas_classlist_student',null,null,array('intClassListID'=>$id));

                unset($classlist['intID']);
                $this->data_poster->post_data('tb_mas_classlist',$classlist);

                $new_id = $this->db->insert_id();


                foreach($st as $s)
                {

                    $s['intClassListID'] = $new_id;
                    $s['date_added'] = $date;
                    $s['enlisted_user'] = $this->data["user"]["intID"];
                    unset($s['intCSID']);
                    $this->data_poster->post_data('tb_mas_classlist_student',$s);

                }
            }
        }
        
        redirect(base_url()."unity/view_classlist_archive_admin");
    }
    public function update_registration_payment_type(){
        $post = $this->input->post();
        $this->db->where('intRegistrationID',$post['intRegistrationID'])
                 ->update('tb_mas_registration',$post);

        $registration = $this->db->get_where('tb_mas_registration',array('intRegistrationID' => $post['intRegistrationID']))->first_row('array');
        $tuition_data = $this->data_fetcher->getTuition($registration['intStudentID'],$registration['intAYID']);                            
        //remove from Ledger
         $this->db->where(array('name'=>'tuition','syid'=>$registration['intAYID'],'student_id'=>$registration['intStudentID']))
            ->delete('tb_mas_student_ledger');

        $data['tuition_data'] = $tuition_data;
        $amount = 0;
        if($post['paymentType'] == "full")
            $amount = $tuition_data['total_before_deductions'];
        else
            $amount = $tuition_data['ti_before_deductions'];


        $data['success'] = true;
        echo json_encode($data);
    }

    public function update_tuition_year(){
        $post = $this->input->post();
        $this->db->where('intRegistrationID',$post['intRegistrationID'])
                 ->update('tb_mas_registration',$post);        

        $data['success'] = true;
        $data['message'] = "Updated Tuition Year";
        echo json_encode($data);

    }

    public function classlist_viewer_data($id,$showAll = 0, $sid){

        $clist = $this->data_fetcher->fetch_classlist_by_id(null,$id);
        $clist_sy_id = $clist['strAcademicYear'];

        $active_sem = $this->data_fetcher->get_sem_by_id($clist_sy_id);
        
        
        if($this->is_super_admin())
                $active_sem['enumGradingPeriod'] = "active";
        
        $data['active_sem'] = $active_sem;

        if($this->is_super_admin() || ($this->session->userdata('intID') == $clist['intFacultyID']) || ($this->is_department_head() && $clist['strDepartment'] == $this->session->userdata['strDepartment']) || $this->is_registrar())
        {            
            
            $data['classlist'] = $clist;
            
            if(!$data['classlist']['grading_system'])
                $data['classlist']['grading_system'] = 1;
            
            
            $data['is_admin'] = $this->is_super_admin();
            
            $cl_ay = $data['classlist']['strAcademicYear'];
            $cl_subj = $data['classlist']['intSubjectID'];

            //GET EXTENSIONS FOR MIDTERM AND FINAL
            $mx = $this->db->where(array('syid'=>$active_sem['intID'],'type'=>'midterm'))
                                                 ->order_by('date','DESC')
                                                 ->get('tb_mas_sy_grading_extension')
                                                 ->first_row('array');
            
            $fx = $this->db->where(array('syid'=>$active_sem['intID'],'type'=>'final'))
                                                 ->order_by('date','DESC')
                                                 ->get('tb_mas_sy_grading_extension')
                                                 ->first_row('array');

            if($mx){
                $ext = $this->db->get_where('tb_mas_sy_grading_extension_faculty',array('classlist_id'=>$clist['intID'],'grading_extension_id'=>$mx['id']))
                                                        ->first_row('array');            
                
                if($ext && $mx['date'] > $data['classlist']['midterm_end'])                                                        
                    $data['classlist']['midterm_end']  = $mx['date'];
            }
            
                
            if($fx){
                $ext = $this->db->get_where('tb_mas_sy_grading_extension_faculty',array('classlist_id'=>$clist['intID'],'grading_extension_id'=>$fx['id']))
                                                        ->first_row('array');
                if($ext && $fx['date'] > $data['classlist']['final_end'])                                                        
                    $data['classlist']['final_end']  = $fx['date'];
            }

            
            $data['cl'] = $this->data_fetcher->fetch_table('tb_mas_classlist',null,null,array('strAcademicYear'=>$cl_ay,'intSubjectID'=>$cl_subj,'intID !='=>$id,'intFinalized !='=>1));
            
            
            
            $data['is_super_admin'] = $this->is_super_admin();
            $data['is_registrar'] = $this->is_registrar();
            
            if($showAll > 0 && ($this->session->userdata('intUserLevel') == 2 || $this->session->userdata('intUserLevel') == 3))
                $data['showall'] = true;
            else
                $data['showall'] = false;

            $students = $this->data_fetcher->getClassListStudents($id,0,$sid);
            

            $data['subject'] = $this->data_fetcher->getSubjectNoCurr($data['classlist']['intSubjectID']);
            //Check for override
            $override_final = $this->db->where(array('subject_id'=>$data['classlist']['intSubjectID'],'period'=>'final','syid'=>$cl_ay))
                                       ->get('tb_mas_sy_grading_override')
                                       ->first_row('array');
            
            $grading_system = $override_final?$override_final['grading_system_id']:$data['subject']['grading_system_id'];
            
                
            $data['grading_items'] = $this->db->where(array("grading_id"=>$grading_system))
                                                    ->order_by('value','ASC')
                                                    ->get('tb_mas_grading_item')
                                                    ->result_array();

            if($clist['intMajor'] == 0)
                $data['legend'] = $this->load->view('faculty/grade_college',$this->data,true);
            else
                $data['legend'] = $this->load->view('faculty/grade_shs',$this->data,true);
            
            if(!$data['subject']['grading_system_id_midterm']) 
                $data['grading_items_midterm'] = $data['grading_items'];                                                
            else{
                //Check for override midterm
                $override_midterm = $this->db->where(array('subject_id'=>$data['classlist']['intSubjectID'],'period'=>'midterm','syid'=>$cl_ay))
                                       ->get('tb_mas_sy_grading_override')
                                       ->first_row('array');
                
                $grading_system_midterm = $override_midterm?$override_midterm['grading_system_id']:$data['subject']['grading_system_id_midterm'];

                $data['grading_items_midterm'] = $this->db->where(array("grading_id"=>$grading_system_midterm))
                                                ->order_by('value','ASC')
                                                ->get('tb_mas_grading_item')
                                                ->result_array();                  
            }            

                            
            $st = [];
            
            $data['all_students'] = $this->db->select("tb_mas_users.intID, strFirstname, strMiddlename, strLastname")
                                         ->from('tb_mas_users')
                                         ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                                         ->where(array("tb_mas_registration.intAYID"=>$clist_sy_id,'intROG <'=>1))
                                         ->order_by('strLastname','asc')
                                         ->get()
                                         ->result_array();

            $pre_req = [];
            $prereq_array = $this->db->get_where('tb_mas_prerequisites',array('intSubjectID'=>$clist['intSubjectID']))->result_array();
            
            $passed_pre_req = true;
            
            foreach($students as $student)
            { 
                $pre_req = [];
                $currc = $this->data_fetcher->getItem('tb_mas_curriculum',$student['intCurriculumID']);

                foreach($prereq_array as $prereq_item){
                    if(!isset($prereq_item['program']) || $prereq_item['program'] == 0 || $prereq_item['program'] == $student['intCurriculumID'])
                        $pre_req[] =  $prereq_item;
                }

                foreach($pre_req as $req){
                    $passed = $this->db->select('tb_mas_classlist_student.intCSID')
                    ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')         
                    ->where(array('intSubjectID'=>$req['intPrerequisiteID'],'strRemarks'=>'Passed'))
                    ->get('tb_mas_classlist_student')
                    ->result_array();
                    if(empty($passed))
                        $passed_pre_req = false;
                             
                }
                $student['pre_req_passed'] = $passed_pre_req;
                $student['registered'] = $this->data_fetcher->checkRegistered($student['intID'],$data['classlist']['strAcademicYear']);
                $st[] = $student;                    
                
            }
            
            $data['students'] = $st;
            $data['pre_req'] = $pre_req;
            
            $data['label'] = "Submit"; 
            if ($data['classlist']['intFinalized'] == 0) {
                $data['label'] = "Submit Midterm Grades";
                if(($data['classlist']['midterm_start'] <= date("Y-m-d") && $data['classlist']['midterm_end'] >= date("Y-m-d")) || $this->is_registrar() || $this->is_super_admin()){
                    $data['disable_submit'] =  false;                                                    
                }
                else
                    $data['disable_submit'] =  true;

            }
            else if ($data['classlist']['intFinalized'] == 1) {
                $data['label'] = "Submit Final Grades";
                if(($data['classlist']['final_start'] <= date("Y-m-d") && $data['classlist']['final_end'] >= date("Y-m-d"))  || $this->is_registrar() || $this->is_super_admin()){
                        $data['disable_submit'] =  false;                                                
                }
                    else
                        $data['disable_submit'] =  true;

            }                                        
                                 
            $data['success'] = true;                       
               
        }
        else
            $data['success'] = false;
        echo json_encode($data);                    

    }

    public function classlist_viewer($id,$showAll = 0, $student = 0)
    {

        
        $this->data['id'] = $id;
        $this->data['sid'] = $student;
        $this->data['showAll'] = $showAll;
        $this->load->view("common/header",$this->data);
        $this->load->view("faculty/classlist_viewer_v",$this->data);
        $this->load->view("common/footer",$this->data); 

            //print_r($this->data['classlist']);
    
    
    }
    
    function transfer_classlist()
    {
        if($this->is_admin() || $this->is_department_head() || $this->is_registrar())
        {
            $post = $this->input->post();
            //print_r($post);
            foreach($post['students'] as $st)
            {
                $this->data_poster->deleteStudentFromClassList($post['classlistFrom'],$st);
                $d['intStudentID'] = $st;
                $d['intClassListID'] = $post['transferTo'];
                $this->data_poster->addStudentClasslist($d,$this->data["user"]["intID"]);
            }
            $data['message'] = "success";
        }
        else
        {
            $data['message'] = "failed";            
        }
        echo json_encode($data);
    }
    //<!--  newly added ^_^ 4-22-2016-->
    public function update_studNum()
    {
        $post = $this->input->post();
        $this->data_poster->post_data('tb_mas_users',$post,$post['intID']);
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    
    public function update_section_ajax()
    {
        if($this->is_admin() || $this->is_department_head())
        {
            $post = $this->input->post();
            $post['date_added'] = date("Y-m-d H:i:s");
            $post['enlisted_user'] = $this->data["user"]["intID"];
            $cs = $this->data_fetcher->getItem('tb_mas_classlist_student',$post['intCSID'],'intCSID');
            $this->data_poster->post_data('tb_mas_classlist_student',$post,$post['intCSID'],'intCSID');
            $this->data_poster->log_action('Change student section','Updated Section of Student ID:  '.$cs['intStudentID']." to ClasslistID: ".$cs['intClassListID'],'red');
            $data['message'] = "success";
            echo json_encode($data);
        }
        
    }

    public function completionRequest(){
        
        $post = $this->input->post();
        $post['dteDateOfCompletion'] = date("Y-m-d h:i:sa");
        $post['enumStatus'] = 0;
        $this->data_poster->post_data('tb_mas_completion',$post);

        echo json_encode($post);

    }

    public function comply($csid){


        $cs = $this->data_fetcher->getClasslistStudent($csid);
        $st = $this->data_fetcher->getCompletion($csid);
        $this->data['cs'] = $cs;
        $this->data['st'] = $st;

        $this->load->view("common/header",$this->data);
        $this->load->view("faculty/comply",$this->data);
        $this->load->view("common/footer_classlist",$this->data); 
        $this->load->view("common/comply_conf",$this->data); 

    }
    
    public function duplicate_curriculum(){
        if($this->is_admin() || $this->is_registrar())
        {
            $post = $this->input->post();
            $id = $post['id'];
            $curriculum = $this->db->get_where('tb_mas_curriculum',array('intID'=>$id))->first_row();
            $subjects = $this->db->get_where('tb_mas_curriculum_subject',array('intCurriculumID'=>$id))->result_array();

            $curriculum_new = [
                'strName' => $curriculum->strName."(1)",
                'intProgramID' => $curriculum->intProgramID,
                'active' => 1
            ];

            $this->db->insert('tb_mas_curriculum',$curriculum_new);
            $new_id = $this->db->insert_id();
            foreach($subjects as $subject){
                $subject_insert = [
                    'intSubjectID' => $subject['intSubjectID'],
                    'intCurriculumID' => $new_id,
                    'intYearLevel' => $subject['intYearLevel'],
                    'intSem'=> $subject['intSem'],
                ];
                $this->db->insert('tb_mas_curriculum_subject',$subject_insert);
            }

            $data['success'] = true;
            $data['message'] = "Duplicated";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Access Denied";
        }
        echo json_encode($data);
    }
    public function remove_student_classlist(){
        $post = $this->input->post();
        $this->db->where('intCSID',$post['intCSID'])
                 ->delete('tb_mas_classlist_student');
        $data['success'] = true;
        $data['message'] = "deleted student";

        $this->data_poster->log_action('Registration','Removed Enlisted student From Classlist:'.$post['section'].' Student:'.$post['name'],'green');

        echo json_encode($data);
    }
    public function update_grade($term = 1)
    {
         $active_sem = $this->data_fetcher->get_active_sem();
         $post = $this->input->post();
         $item = $this->data_fetcher->getItem('tb_mas_classlist_student',$post['intCSID'],'intCSID');
         $clist = $this->data_fetcher->fetch_classlist_by_id(null,$item['intClassListID']);
        
        //if($this->is_super_admin() || $active_sem['enumGradingPeriod'] == "active"){   
        if($this->is_super_admin() || ($this->session->userdata('intID') == $clist['intFacultyID'])){                
           
            if($term == 3)
                $data['eq'] = $post['floatFinalGrade'];                                                            
            elseif($term == 2){
                $data['eq'] = $post['floatMidtermGrade'];                                
                unset($post['strRemarks']);
            }
                                    
           
            $post['date_added'] = date("Y-m-d H:i:s");
            $this->data_poster->update_classlist('tb_mas_classlist_student',$post,$post['intCSID']);

            if($clist['intFinalized'] == 1 && $term == 2 && ($this->is_registrar() || $this->is_super_admin())){
                $cg['student_id'] = $item['intStudentID'];
                $cg['from_grade'] = $item['floatMidtermGrade']?"MIDTERM: ".$item['floatMidtermGrade']:"MIDTERM: NGS";
                $cg['to_grade'] = $post['floatMidtermGrade']; 
                $cg['changed_by'] = $this->data["user"]["strFirstname"]." ".$this->data["user"]["strLastname"];
                $cg['date'] = date("Y-m-d H:i:s");
                $cg['classlist_id'] = $item['intClassListID'];

                $this->db->insert('tb_mas_student_grade_change',$cg);
            }
            if($clist['intFinalized'] == 2 && $term == 3 && ($this->is_registrar() || $this->is_super_admin())){
                $cg['student_id'] = $item['intStudentID'];
                $cg['from_grade'] = $item['floatFinalGrade']?"FINAL: ".$item['floatFinalGrade']:"FINAL: NGS";
                $cg['to_grade'] = $post['floatFinalGrade']; 
                $cg['changed_by'] = $this->data["user"]["strFirstname"]." ".$this->data["user"]["strLastname"];
                $cg['date'] = date("Y-m-d H:i:s");
                $cg['classlist_id'] = $item['intClassListID'];

                $this->db->insert('tb_mas_student_grade_change',$cg);
            }
            $data['message'] = "success";            
        }
        else{
            $data['message'] = "failed";
        }
        echo json_encode($data);
    }
    
    public function update_finalized()
    {
        if($this->is_registrar() || $this->is_super_admin() || $this->is_accounting() || $this->is_department_head())
        {
            $post = $this->input->post();
            $cs = $this->data_fetcher->getItem('tb_mas_classlist',$post['intID']);
            if($cs["intFinalized"] == 0)
                $post['intFinalized'] = 1;
            else
                $post['intFinalized'] = 0;
		    $this->data_poster->post_data('tb_mas_classlist',$post,$post['intID']);
        }
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    public function finalize_term()
    {
        if($this->faculty_logged_in())
        {
            $post = $this->input->post();
            
            if($post['intFinalized'] == 0)
                $post['date_midterm_submitted'] = date("Y-m-d H:i:s");
            if($post['intFinalized'] == 1)
                $post['date_final_submitted'] = date("Y-m-d H:i:s");
            
            $post['intFinalized'] += 1;
            
		    $this->data_poster->post_data('tb_mas_classlist',$post,$post['intID']);
        }
        $data['message'] = "success";
        echo json_encode($data);
    }

    public function unfinalize_term(){
        if($this->is_registrar() || $this->is_super_admin())
        {
            $post = $this->input->post();
            $post['intFinalized'] -= 1;            
		    $this->data_poster->post_data('tb_mas_classlist',$post,$post['intID']);
        
            $data['message'] = "success";
            echo json_encode($data);
        }

    }
    
    public function update_rog_status()
    {
        if($this->is_registrar() || $this->is_super_admin() || $this->is_accounting())
        {
            
            $post = $this->input->post();
            
            $registration = $this->db->get_where('tb_mas_registration',array('intRegistrationID' => $post['intRegistrationID']))->first_row('array');
            $student = $this->db->get_where('tb_mas_users',array('intID'=>$registration['intStudentID']))->first_row('array');
            
            if($post['intROG'] == 1){
                $post['dteRegistered'] = date("Y-m-d H:i:s");
                if($student['strStudentNumber'][0] == "T"){
                    $temp['strStudentNumber'] = $this->data_fetcher->generateNewStudentNumber($this->data['campus'],$registration['intAYID'],get_stype($student['level']));
                    $this->db
                        ->where('intID',$student['intID'])
                        ->update('tb_mas_users',$temp);
                }
            }
                            

            $this->data_poster->post_data('tb_mas_registration',$post,$post['intRegistrationID']);
            
            $st = $this->db
                 ->query("SELECT * FROM tb_mas_registration JOIN tb_mas_users ON tb_mas_registration.intStudentID = tb_mas_users.intID WHERE tb_mas_registration.intRegistrationID =".$post['intRegistrationID'])
                 ->first_row();

            
            
            $this->data_poster->log_action('Registration Status','Updated Registration Status of:  '.$st->strFirstname." ".$st->strLastname." to ".$st->intROG,'green');
            $data['message'] = "Success";
            $data['success'] = true;
        }
        else{
            $data['message'] = "Failed";
            $data['success'] = false;
        }
        echo json_encode($data);
    }

    public function update_allow_enroll(){
        if($this->is_registrar() || $this->is_super_admin() || $this->is_accounting())
        {
            
            $post = $this->input->post();
                        

            $this->data_poster->post_data('tb_mas_registration',$post,$post['intRegistrationID']);
            
            $st = $this->db
                 ->query("SELECT * FROM tb_mas_registration JOIN tb_mas_users ON tb_mas_registration.intStudentID = tb_mas_users.intID WHERE tb_mas_registration.intRegistrationID =".$post['intRegistrationID'])
                 ->first_row();
            
            $this->data_poster->log_action('Allow Enroll','Updated Allow Enroll:  '.$st->strFirstname." ".$st->strLastname." to ".$post['allow_enroll'],'green');
            $data['message'] = "Success";
            $data['success'] = true;
        }
        else{
            $data['message'] = "Failed";
            $data['success'] = false;
        }
        echo json_encode($data);
    }
    
    public function update_graduate_status()
    {
        $post = $this->input->post();
		$this->data_poster->post_data('tb_mas_users',$post,$post['intID']);
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    public function update_student_status()
    {
        $post = $this->input->post();
        $item = $this->data_fetcher->getItem('tb_mas_classlist_student',$post['intCSID'],'intCSID');
        if($post['enumStatus'] == "drp")
        {
            $post['floatFinalGrade'] = "5.00";
            $data['eq_raw'] ="UD";
            $data["eq"] = $post['floatFinalGrade'];
            $post['strRemarks'] = $data['remarks'] = "Failed(U.D.)";
        }
        else if($post['enumStatus'] == "odrp")
        {
            $post['floatFinalGrade'] = "0";
            $data['eq_raw'] ="OD";
            $data["eq"] = $post['floatFinalGrade'];
            $post['strRemarks'] = $data['remarks'] = "Officially Dropped";
        }
        else if($post['enumStatus'] == "inc")
        {
            $post['floatFinalGrade'] = "3.50";
            $data['eq'] = $post['floatFinalGrade'];
            $data['eq_raw'] ="inc";
            $post['strRemarks'] = $data['remarks'] = "lack of reqts.";

        }        
        else
        {  
            $ave = getAve($item['floatPrelimGrade'],$item['floatMidtermGrade'],$item['floatFinalsGrade']);
            $data['eq_raw'] = getAve($item['floatPrelimGrade'],$item['floatMidtermGrade'],$item['floatFinalsGrade']);
            $data["eq"] = getEquivalent($ave);
            $post['floatFinalGrade'] = $data["eq"];
            
            //$data["eq"] = $post['floatFinalGrade'];
            //$data["eq"] = $item['floatFinalGrade'];
            
            if($post['enumStatus'] == "passed")
                $post['strRemarks'] = $data['remarks'] = "Passed";
            elseif($post['enumStatus'] == "failed")
                $post['strRemarks'] = $data['remarks'] = "Failed";
            elseif($post['enumStatus'] == "act")
                $post['strRemarks'] = $data['remarks'] = "";
            else
                $post['strRemarks'] = $data['remarks'] = getRemarks($post["floatFinalGrade"]);
            
        }
        
        $this->data_poster->update_classlist('tb_mas_classlist_student',$post,$post['intCSID']);
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    public function update_remarks()
    {
        $post = $this->input->post();
        $this->data_poster->update_classlist('tb_mas_classlist_student',$post,$post['intCSID']);
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    public function update_section()
    {
        $post = $this->input->post();
        $this->data_poster->post_data('tb_mas_users',$post,$post['intID']);
        $this->data_poster->log_action('Change student section','Updated Section of Student id:  '.$post['intID'],'red');
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    public function userToken($id = null)
    {
        $get = $this->input->get();
        # Perform the query
        $query = "SELECT intID, strStudentNumber, strFirstname, strLastname from tb_mas_users WHERE strFirstname LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%' OR strLastname LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%' OR strStudentNumber LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%' ORDER BY strLastname DESC LIMIT 10";
        $arr = array();
        $rs = $this->db->query($query);

        if($id!=null)
            foreach ($rs->result() as $obj){
                $arr[] = array('id'=>$obj->intID,'name'=>$obj->strFirstname." ".$obj->strLastname." ".$obj->strStudentNumber);
            }
        else
            # Collect the results
            foreach ($rs->result() as $obj){
                $arr[] = array('id'=>$obj->intID,'name'=>$obj->strFirstname." ".$obj->strLastname." ".$obj->strStudentNumber);
            }

        # JSON-encode the response
        $json_response = json_encode($arr);

        # Optionally: Wrap the response in a callback function for JSONP cross-domain support
        if(isset($get["callback"]) && $get["callback"]) {
            $json_response = $get["callback"] . "(" . $json_response . ")";
        }

        # Return the response
        echo $json_response;

    }
    
    public function userTokenFaculty($id = null)
    {
        $get = $this->input->get();
        # Perform the query
        $query = "SELECT intID, strFirstname, strLastname from tb_mas_faculty WHERE teaching = 1 AND (strFirstname LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%' OR strLastname LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%') ORDER BY strLastname DESC LIMIT 10";
        $arr = array();
        $rs = $this->db->query($query);

        if($id!=null)
            foreach ($rs->result() as $obj){
                $arr[] = array('id'=>$obj->intID,'name'=>$obj->strFirstname." ".$obj->strLastname);
            }
        else
            # Collect the results
            foreach ($rs->result() as $obj){
                $arr[] = array('id'=>$obj->strStudentNumber,'name'=>$obj->strFirstname." ".$obj->strLastname);
            }

        # JSON-encode the response
        $json_response = json_encode($arr);

        # Optionally: Wrap the response in a callback function for JSONP cross-domain support
        if(isset($get["callback"]) && $get["callback"]) {
            $json_response = $get["callback"] . "(" . $json_response . ")";
        }

        # Return the response
        echo $json_response;

    }
    
    public function add_curriculum()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {
            $this->data['page'] = "add_curriculum";
            $this->data['opentree'] = "curriculum";
            
            $this->data['programs'] = $this->data_fetcher->fetch_table('tb_mas_programs');
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_curriculum",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("curriculum_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    
    public function view_all_curriculum()
    {
        if($this->is_registrar() || $this->is_super_admin())
        {
            $this->data['page'] = "view_curriculum";
            $this->data['opentree'] = "curriculum";
            //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/curriculum_view",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/curriculum_conf",$this->data); 
            //print_r($this->data['classlist']);
        }
        else
            redirect(base_url()."unity");  
    }

    public function enhanced_list($term = 0)
    {
        
        if($this->is_registrar() || $this->is_super_admin())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term); 
                 
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
          
                                   
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_enhanced_list",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/enhanced_list_conf",$this->data); 
            
        }
        else
            redirect(base_url()."unity");                
    }

    public function regular_list($term = 0)
    {
        
        if($this->is_registrar() || $this->is_super_admin())
        {
            if($term == 0)
                $term = $this->data_fetcher->get_processing_sem();        
            else
                $term = $this->data_fetcher->get_sem_by_id($term); 
                 
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['current_sem'] = $term['intID'];
          
                                   
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_regular_list",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/regular_list_conf",$this->data); 
            
        }
        else
            redirect(base_url()."unity");                
    }


    public function enhanced_list_data($sem)
    {
        $students_array = array();

        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $sy['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_registration.current_curriculum')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_curriculum.isEnhanced' => '1'))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        for($index = 0; $index < count($students); $index++){
            $course = $this->data_fetcher->getProgramDetails($students[$index]['intProgramID']);
            // $students[$index]['course'] = $course;

            $student['studentNumber'] = $students[$index]['strStudentNumber'];
            $student['name'] = ucfirst($students[$index]['strLastname']) . ', ' . ucfirst($students[$index]['strFirstname']) . ' ' . ucfirst($students[$index]['strMiddlename']);
            $student['course'] = $course['strProgramCode'];
            $students_array[] = $student;
        }
      
    

        $data['data'] = $students_array;

        echo json_encode($data);
    }
    
    
    public function regular_list_data($sem)
    {
        $students_array = array();
        
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $sy['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_registration.current_curriculum')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_curriculum.isEnhanced' => '0'))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        for($index = 0; $index < count($students); $index++){
            $course = $this->data_fetcher->getProgramDetails($students[$index]['intProgramID']);
            // $students[$index]['course'] = $course;

            $student['studentNumber'] = $students[$index]['strStudentNumber'];
            $student['name'] = ucfirst($students[$index]['strLastname']) . ', ' . ucfirst($students[$index]['strFirstname']) . ' ' . ucfirst($students[$index]['strMiddlename']);
            $student['course'] = $course['strProgramCode'];
            $students_array[] = $student;
        }

        $data['data'] = $students_array;

        echo json_encode($data);
    }
    
    public function delete_classlist()
    {
        $post = $this->input->post();
        $classlist = current($this->data_fetcher->fetch_classlist_delete($post['id']));
        
        if($classlist['intFinalized'] != 1 && ($classlist['intFacultyID']==$this->session->userdata("intID") || $this->is_super_admin() || $this->is_registrar()))
        {
            $this->data_poster->deleteClassList($post['id']);
            $data['message'] = "success";
            $this->data_poster->log_action('Classlist','Deleted a Classlist '.$post['id'],'green');
        }
        else
        {
            $data['message'] = "failed";
        }
        echo json_encode($data);
    }

    public function dissolve_classlist()
    {
        $post = $this->input->post();
        $classlist = current($this->data_fetcher->fetch_classlist_delete($post['id']));
        
        $slots_taken = $this->db
        ->select('tb_mas_classlist_student.intCSID')                                
        ->from('tb_mas_classlist_student')
        ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
        ->where(array('intClassListID'=>$post['id']))
        ->get()
        ->num_rows();
        
        if($classlist['intFinalized'] != 1 && $slots_taken == 0 && ($classlist['intFacultyID']==$this->session->userdata("intID") || $this->is_super_admin() || $this->is_registrar()))
        {
            $data['success'] = true;
            $this->data_poster->dissolveClassList($post['id'],$post['fn']);
            $data['message'] = "success";
            $this->data_poster->log_action('Classlist','Dissolved a Classlist '.$post['id'],'green');
        }
        else
        {
            $data['success'] = false;
            $data['message'] = "failed please check if classlist still has students enlisted or enrolled";
        }
        echo json_encode($data);
    }
    
    
    public function add_to_classlist()
    {
        $post = $this->input->post();
        
        $classlist = $this->data_fetcher->fetch_classlist_by_id(null,$post['intClassListID']);
        
        $subject = $this->data_fetcher->getSubjectNoCurr($classlist['intSubjectID']);
        $post['strUnits'] = $subject['strUnits'];
        
        $send['strUnits'] = $subject['strUnits'];
        $send['intStudentID'] = $post['intStudentID'];
        $send['intClassListID'] = $classlist['intID'];
        
        $student = $this->data_fetcher->fetch_table('tb_mas_classlist_student',null,null,array('intStudentID'=>$post['intStudentID'],'intClassListID'=>$post['intClassListID']));
        
        $active_sem = $this->data_fetcher->get_active_sem();
        
        $enlisted = $this->data_fetcher->checkStudentSubject($active_sem['intID'],$classlist['intSubjectID'],$post['intStudentID']);
       
        
        
        if(!$this->is_super_admin() && !$this->is_registrar())
        {
            $data['message'] = "failed3";
        }
        elseif($classlist['intFinalized'] == 1)
            $data['message'] = "failed2";
        elseif(!empty($enlisted))
            $data['message'] = "enlisted in different classlist section: ".$enlisted['strSection'];
        elseif(empty($student)){
            $this->data_poster->addStudentClasslist($send,$this->data["user"]["intID"]);
            $data['message'] = "success";
        }
        echo json_encode($data);
    }
    
    public function delete_curriculum()
    {
        $data['message'] = "failed";
        if($this->is_admin()){
            $post = $this->input->post();
            $deleted =  $this->data_poster->deleteCurriculum($post['id']);
            if($deleted)
            {
                $data['message'] = "success";
                $this->data_poster->log_action('Curriculum','Deleted a Curriculum '.$post['code'],'red');
            }
            else
                $data['message'] = "failed";
            
            echo json_encode($data);
            
        }
    }
    
    public function delete_subject_curriculum()
    {
        $data['message'] = "failed";
        if($this->is_registrar() || $this->is_super_admin()){
            $post = $this->input->post();
            $this->data_poster->deleteItem('tb_mas_curriculum_subject',$post['id'],'intID');
            $data['message'] = "success";
            $this->data_poster->log_action('Curriculum','Deleted a Subject from Curriculum '.$post['code'],'red');
        }
        echo json_encode($data);
    }
    
    public function delete_transaction()
    {
        $data['message'] = "failed";
        if($this->is_super_admin() || $this->is_accounting()){
            $post = $this->input->post();
            $this->data_poster->deleteItem('tb_mas_transactions',$post['id'],'intTransactionID');
            $data['message'] = "success";
            $this->data_poster->log_action('Transaction','Deleted a Transaction: '.$post['id'],'red');
        }
        echo json_encode($data);
    }
    
    public function delete_transaction_or()
    {
        $data['message'] = "failed";
        if($this->is_super_admin() || $this->is_accounting()){
            $post = $this->input->post();
            $this->data_poster->deleteItem('tb_mas_transactions',$post['id'],'intORNumber');
            $data['message'] = "success";
            $this->data_poster->log_action('Transaction','Deleted OR: '.$post['id'],'red');
        }
        echo json_encode($data);
    }
    
    public function delete_student_cs()
    {
        if($this->is_admin() || $this->is_registrar()){
            $post = $this->input->post();
            $cs = $this->data_fetcher->getClasslistStudent($post['intCSID']);
            if($this->data_poster->deleteStudentCS('tb_mas_classlist_student',$post['intCSID'])){
                $data['message'] = "success";
                $data['success'] = true;
                $active_sem = $this->data_fetcher->get_active_sem();
                $this->data_poster->log_action('Sectioning','Deleted From Classlist: '.$cs['strStudentNumber']." ".$cs['strFirstname']." ".$cs['strLastname'],'red',$cs['studentId'],$active_sem['intID']);
            }
            else{
                $data['message'] = "failed";
                $data['success'] = false;
            }
            
            
        }
        else
            $data['message'] = "failed";

        
        echo json_encode($data);
    }
    
    public function load_subjects()
    {
        $post = $this->input->post();
        $ret = array();
        
        $subjects = $this->data_fetcher->getAdvisedSubjectsReg($post);
        $active_sem = $this->data_fetcher->get_sem_by_id($post['sem']);
        
        foreach($subjects as $subj)
        {
            $cst = [];
            $classlists = $this->data_fetcher->fetch_classlist_by_subject($subj['intID'],$active_sem['intID']);            
            foreach($classlists as $classlist){
                $classlist_temp = $classlist;
                $classlist_temp['numCount'] = $this->data_fetcher->countRemainingSlotsClasslist($classlist['intID']);                
                if($classlist_temp['numCount'] != 0)
                    $cst[] = $classlist_temp;
            }
            $subj['classlists'] = $cst;
            $ret[] = $subj;
        }
        
        $data['subjects'] = $ret;
        
        echo json_encode($data);
    }
    
    public function load_subjects2()
    {
        $post = $this->input->post();
    
        $ret = array();
        
       
        $subjects = $this->data_fetcher->getClassListStudentsSt($post['intStudentID'],$post['sem']);
        
        $data['subjects'] = $subjects;
        
        echo json_encode($data);
    }
    
    public function add_single_subject()
    {
        $post = $this->input->post();
        $data['subject'] = $this->data_fetcher->getSubjectNoCurr($post['intID']);
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