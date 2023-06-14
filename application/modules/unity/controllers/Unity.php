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
            $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
            $this->data['pwd'] = $this->session->userdata('strPass');
            $this->data["faculty_data"] = $this->session->all_userdata();
            $students = $this->data_fetcher->count_table_contents('tb_mas_users',null,array('isGraduate'=>0));
            $resident_scholars = $this->data_fetcher->count_table_contents('tb_mas_users',null,array('enumScholarship'=>'resident scholar','isGraduate'=>0));
            $seventh_district = $this->data_fetcher->count_table_contents('tb_mas_users',null,array('enumScholarship'=>'7th district scholar','isGraduate'=>0));
            $registered_students = $this->data_fetcher->count_table_contents('tb_mas_registration',null,array('intAYID'=>$this->data['active_sem']['intID']));
           
            $this->data['registration_data_all'] = $this->data_fetcher->getRegistrationData($this->data['active_sem']['intID']);
            $sem_temp = $this->data['active_sem'];
            for($i=0;$i<3;$i++){
                $this->data['grades_charts'][$i] = getGradeAverages($sem_temp['intID']); 
                $this->data['grades_charts'][$i]['label'] = $sem_temp['enumSem']." ".$this->data['term_type']." ".$sem_temp['strYearStart']."-".$sem_temp['strYearEnd'];
                $sem_temp = $this->data_fetcher->get_prev_sem($sem_temp['intID']);
                if(empty($sem_temp))
                    break;
            }
            
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
        if($this->is_super_admin() || ($this->is_accounting() && $cat == "Cashier") || $this->is_registrar())
        {
            $this->data['page'] = "logs";
            $this->data['opentree'] = "admin";
            $this->data['title'] ="Logs";
            
            $this->data['cat'] = $cat;
            
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
        if($this->is_admin() || ($this->session->userdata('intID') == $clist['intFacultyID']) || ($this->is_department_head() && $clist['strDepartment'] == $this->session->userdata['strDepartment']) || $this->is_registrar())
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
            $this->data['teacher'] = $this->data_fetcher->fetch_table('tb_mas_faculty',array('strLastName','asc'));
            
          
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
    
    public function registration_viewer_data($id,$sem){
        if($this->is_super_admin() || $this->is_accounting() || $this->is_registrar())
        {
            $active_sem = $this->data_fetcher->get_active_sem();

            if($sem!=null)
                $ret['selected_ay'] = $sem;
            else
                $ret['selected_ay'] = $active_sem['intID'];

            $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
            if($ret['registration']){
                $data['tuition'] = $this->data_fetcher->getTuition($id,$ret['selected_ay'],$ret['registration']['enumScholarship']);
                $ret['tuition_data'] = $data['tuition'];
                $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);
            }
            else
                $data['tuition'] = "";

            $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);      
            $ret['cashier'] = $this->db->get_where('tb_mas_cashier',array('user_id'=>$this->data['user']['intID']))->first_row();      
            $ret['user_logged'] = $this->data['user']['intID'];
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

    public function online_payment_data($id,$sem){
        
        $active_sem = $this->data_fetcher->get_active_sem();

        if($sem!=null)
            $ret['selected_ay'] = $sem;
        else
            $ret['selected_ay'] = $active_sem['intID'];

        $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
        $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
        $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);            

        $ret['student'] = $this->data_fetcher->getStudent($id);
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
        $data['tuition'] = $this->data_fetcher->getTuition($id,$ret['selected_ay'],$ret['registration']['enumScholarship']);
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
                $data['tuition'] = $this->data_fetcher->getTuition($id,$ret['selected_ay'],$ret['registration']['enumScholarship']);
                $ret['tuition_data'] = $data['tuition'];
                $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);
            }
            else
                $data['tuition'] = "";

            $ret['records'] = $this->data_fetcher->getClassListStudentsSt($id,$ret['selected_ay']);
            

            $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($ret['selected_ay']);      
            $ret['user_logged'] = $this->data['user']['intID'];
            $ret['student'] = $this->data_fetcher->getStudent($id);
            $ret['subjects_available'] = $this->data_fetcher->getSubjectsInCurriculum($ret['student']['intCurriculumID'],$sem,$id);
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

    public function student_tuition_payment($id)
    {
        $active_sem = $this->data_fetcher->get_active_sem();

        $student = $this->data_fetcher->getStudent($id, 'slug');            
        $data['selected_ay'] = $active_sem['intID'];
        $data['id'] = $student['intID'];

        $this->load->view("public/header",$this->data);
        $this->load->view("public/payment_online_tuition",$data);
        $this->load->view("public/footer",$this->data);         
    }  
    
    public function confirm_program($slug) {                
        
        $student = $this->data_fetcher->getStudent($slug, 'slug');                    
        $data['id'] = $student['intID'];
        
           
        $this->load->view('public/header',$this->data);        
		$this->load->view('public/confirm_program',$data);
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
        $this->data_poster->post_data('tb_mas_users',$post,$id);
        $ret['sched_table'] = $this->load->view('sched_table', $this->data, true); 
        $ret['success'] = true;
        $ret['message'] = "Updated Successfully";
        
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
                $pdata['tuition'] = $this->data_fetcher->getTuition($id,$sy['intID'],$reg['enumScholarship']);                      
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

    function get_active_sem(){
        
        $data['active_sem'] = $this->data_fetcher->get_active_sem();
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
        $data['tuition'] = $this->data_fetcher->getTuitionSubjects($post['stype'],$post['scholarship'],$post['subjects_loaded'],$post['studentID'],$post['type_of_class']);
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
        if($this->is_super_admin())
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
            $ret['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
             
            if(!empty($post))
               $id = $post['studentID'];
			
            //$this->data['sy'] = $this->data_fetcher->getSemStudent($id);
            
            $this->data['upload_errors'] = $this->session->flashdata('upload_errors');
            
            if($sem!=null){
                 $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
            }
            else
            {
                $ret['active_sem'] = $this->data_fetcher->get_active_sem();
                
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
            
            
            $ret['registration'] = $this->data_fetcher->getRegistrationInfo($id,$ret['selected_ay']);
            $ret['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$ret['selected_ay']);
            
            
            $ret['student'] = $this->data_fetcher->getStudent($id);
            if(!$ret['student'])
                $ret['student'] = $this->data_fetcher->getStudent($id, 'slug');
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
                if($record['intFinalized']  <= 2)
                    $record['strRemarks'] = "-";
                    
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

                
                if($record['intFinalized'] > 2){
                    if($record['v3'] != 5.00){
                        $record['v3Display'] =  ($record['v3']==3.50) ? 'inc' : number_format($record['v3'], 2, '.' ,',');
                    }  
                }                
                else
                    $record['v3Display'] = "-";

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
                                <th>P</th>
                                <th>M</th>
                                <th>F</th>
                                <th>FG</th>
                                <th>Num. Rating</th>
                                <th>Units</th>
                                <th>Remarks</th>
                                <th>Faculty</th>
                            </tr>
                        </thead>
                        <tbody>';                
                        
                }

                $prev_year_sem = $grades[$i]['syID'];
                $remarks = (strtoupper($grades[$i]['strRemarks'])=='PASSED')?'green-bg':''; ?> <?php echo ($grades[$i]['strRemarks']=='Failed' || $grades[$i]['strRemarks']=='Failed(U.D.)')?'red-bg':'';
                $ave = number_format(getAve($grades[$i]['floatPrelimGrade'],$grades[$i]['floatMidtermGrade'],$grades[$i]['floatFinalsGrade']), 2);
                $student_grade_table .='    
                    <tr class="'.$remarks.'">
                        <td><a href="'.base_url().'unity/classlist_viewer/'.$grades[$i]['classListID'].'">'.$grades[$i]['strCode'].'</a></td>
                        <td>'.$grades[$i]['strDescription'].'</td>
                        <td>'.$grades[$i]['floatPrelimGrade'].'</td>
                        <td>'.$grades[$i]['floatMidtermGrade'].'</td>
                        <td>'.$grades[$i]['floatFinalsGrade'].'</td>                        
                        <td>'.$ave.'</td>
                        <td>'.number_format($grades[$i]['floatFinalGrade'], 2, '.' ,',').'</td>
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
       
        $user_level = $this->session->userdata('intUserLevel');
        
        if($user_level == 6)
            redirect(base_url().'unity/registration_viewer/'.$id.'/'.$sem);
        if($user_level != 2 && $user_level != 3)
            redirect(base_url().'unity');
        
        

        $post = $this->input->post();
        $this->data['id'] = $id;
        $this->data['sem'] = $sem;

        if($sem!=null){
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $ret['active_sem'] = $this->data_fetcher->get_active_sem();
            
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
            $this->data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculum($id);
            
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
    
    public function view_classlist_archive_admin($sem = null, $program = null)
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
           
            //$this->data['classlists'] = $this->data_fetcher->fetch_classlists_all(null,$this->data['selected_ay']);
            $this->data['page'] = "classlist_archive";
            // $this->data['opentree'] = "registrar";
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/classlist_view_admin",$this->data);
            $this->load->view("common/footer",$this->data); 
            //ajax
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
    
    public function classlist_viewer($id)
    {
        $clist = $this->data_fetcher->fetch_classlist_by_id(null,$id);
        $clist_sy_id = $clist['strAcademicYear'];

        $active_sem = $this->data_fetcher->get_active_sem();
        
        $active_prelim_grading = $this->data_fetcher->get_active_PrelimPeriod($clist_sy_id);
        $active_midterm_grading = $this->data_fetcher->get_active_MidtermPeriod($clist_sy_id);
        $active_finals_grading = $this->data_fetcher->get_active_FinalsPeriod($clist_sy_id);
        
        if($this->is_super_admin())
                $active_sem['enumGradingPeriod'] = "active";
            
        $this->data['active_sem'] = $active_sem;
        $this->data['active_prelim_grading'] = $active_prelim_grading;
        $this->data['active_midterm_grading'] = $active_midterm_grading;
        $this->data['active_finals_grading'] = $active_finals_grading;

        if($this->is_admin() || ($this->session->userdata('intID') == $clist['intFacultyID']) || ($this->is_department_head() && $clist['strDepartment'] == $this->session->userdata['strDepartment']) || $this->is_registrar())
        {
            $this->data['alert'] = $this->session->flashdata('message');
            $this->data['classlist'] = $this->data_fetcher->fetch_classlist_by_id(null,$id);
            $this->data['is_admin'] = $this->is_super_admin();
            
            $cl_ay = $this->data['classlist']['strAcademicYear'];
            $cl_subj = $this->data['classlist']['intSubjectID'];
            
            $this->data['cl'] = $this->data_fetcher->fetch_table('tb_mas_classlist',null,null,array('strAcademicYear'=>$cl_ay,'intSubjectID'=>$cl_subj,'intID !='=>$id,'intFinalized !='=>1));
            
            
            
            $this->data['is_super_admin'] = $this->is_super_admin();
            $students = $this->data_fetcher->getClassListStudents($id);
            $this->data['subject'] = $this->data_fetcher->getSubjectNoCurr($this->data['classlist']['intSubjectID']);
            $passing =0;
            $incomplete =0;
            $ud = 0;
            $od = 0;
            $failing =0;
            $lineOfOne = 0;
            $lifeOfTwo = 0;
            $lineOfThree = 0;
            $totalUD = 0;
            $totalFailed = 0;
            $st = array();
            
            foreach($students as $student)
            { 
                $student['registered'] = $this->data_fetcher->checkRegistered($student['intID'],$this->data['classlist']['strAcademicYear']);
                $st[] = $student;
                $ave = getAve($student['floatPrelimGrade'],$student['floatMidtermGrade'],$student['floatFinalsGrade']);
                $eq = getEquivalent($ave);
                //$eq = getEquivalent($student['floatFinalGrade']);
                if($eq>=5 && $student['enumStatus'] == "drp")
                    $failing++;
                else if ($student['enumStatus'] == "drp")
                    $ud++;
                else if ($student['enumStatus'] == "odrp")
                    $od++;
                else if ($student['enumStatus'] == "inc")
                    $incomplete++;
                else
                    $passing++;
                    
                if($eq >=1.00 && $eq <= 1.75 && $student['enumStatus'] != "inc" && $student['enumStatus'] != "drp" && $student['enumStatus'] != "odrp")
                    $lineOfOne++;
                else if($eq >= 2.00 && $eq <= 2.75 && $student['enumStatus'] != "inc" && $student['enumStatus'] != "drp" && $student['enumStatus'] != "odrp")
                    $lifeOfTwo++;
                else if($eq == 3.00 && $student['enumStatus'] != "inc" && $student['enumStatus'] != "drp" && $student['enumStatus'] != "odrp")
                    $lineOfThree++;
                else if ($student['enumStatus'] == "act" && $student['strRemarks'] == "Failed")
                    $totalFailed++;
                else if ($student['enumStatus'] == "drp" && $student['strRemarks'] == "Failed(U.D.)")
                    $totalUD++;
                
                
            }
            $this->data['students'] = $st;
            $this->data['passing'] = $passing;
            $this->data['ud'] = $ud;
            $this->data['od'] = $od;
            $this->data['incomplete'] = $incomplete;
            $this->data['failing'] = $failing;
            $this->data['lineOfOne'] = $lineOfOne;
            $this->data['lineOfTwo'] = $lifeOfTwo;
            $this->data['lineOfThree'] = $lineOfThree;
            $this->data['totalFailed'] = $totalFailed;
            $this->data['totalUD'] = $totalUD;
            $this->data['total'] = $incomplete + $lineOfOne + $lifeOfTwo + $lineOfThree + $totalFailed + $totalUD + $od;
            
            
            $this->data['schedule'] = $this->data_fetcher->getScheduleBySection($this->data['classlist']['strSection'],$this->data['classlist']['strAcademicYear']);
            
            $this->load->view("common/header",$this->data);
            $this->load->view("faculty/classlist_viewer",$this->data);
            $this->load->view("common/footer_classlist",$this->data); 
            $this->load->view("common/classlist_viewer_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");   
    
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
    public function update_grade($term = 1)
    {
         $active_sem = $this->data_fetcher->get_active_sem();
         $post = $this->input->post();
         $item = $this->data_fetcher->getItem('tb_mas_classlist_student',$post['intCSID'],'intCSID');
        
        //if($this->is_super_admin() || $active_sem['enumGradingPeriod'] == "active"){   
        if($this->is_super_admin() || $this->is_admin() || $active_sem['enumGradingPeriod'] == "active"){                
           
            $data['eq'] = $post['floatFinalsGrade'];
            $data['remarks'] = '--';
            $post['floatFinalGrade'] = $data['eq'];
            $post['strRemarks'] = $data['remarks'];
           
            $post['date_added'] = date("Y-m-d H:i:s");
            $this->data_poster->update_classlist('tb_mas_classlist_student',$post,$post['intCSID']);

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
            
            $post['intFinalized'] += 1;
            
		    $this->data_poster->post_data('tb_mas_classlist',$post,$post['intID']);
        }
        $data['message'] = "success";
        echo json_encode($data);
    }
    
    public function update_rog_status()
    {
        if($this->is_registrar() || $this->is_super_admin() || $this->is_accounting())
        {
            
            $post = $this->input->post();
            
            if($post['intROG'] == 2)
                $post['dteRegistered'] = date("Y-m-d H:i:s");

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
        $query = "SELECT intID, strFirstname, strLastname from tb_mas_faculty WHERE strFirstname LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%' OR strLastname LIKE '%%%".mysqli_real_escape_string($this->db->conn_id,$get["q"])."%%' ORDER BY strLastname DESC LIMIT 10";
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
            $this->data_poster->deleteStudentCS('tb_mas_classlist_student',$post['intCSID']);
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data_poster->log_action('Sectioning','Deleted From Classlist: '.$cs['strStudentNumber']." ".$cs['strFirstname']." ".$cs['strLastname'],'red',$cs['studentId'],$active_sem['intID']);
            $data['message'] = "success";
            $data['success'] = true;
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
        $active_sem = $this->data_fetcher->get_processing_sem();
        
        foreach($subjects as $subj)
        {
            $cst = [];
            $classlists = $this->data_fetcher->fetch_classlist_by_subject($subj['intID'],$active_sem['intID']);            
            foreach($classlists as $classlist){
                $classlist_temp = $classlist;
                $classlist_temp['numCount'] = $this->data_fetcher->countStudentsInClasslist($classlist['intID']);                
                if($classlist_temp['numCount'] < $classlist['slots'])
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