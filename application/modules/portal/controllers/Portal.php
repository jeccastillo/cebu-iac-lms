<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require_once('src/facebook.php');
	
class Portal extends CI_Controller {

	
    function __construct() {

        parent::__construct();
        $this->clear_cache();
        if(!$this->logged_in())         
            redirect(base_url()."users/student_login");
		/*--------------THEMES-----------------------*/
		$this->config->load('themes');
		$theme = $this->config->item('website');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
			
		$this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
		$this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";	
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";
        $this->data['student_pics'] = base_url()."assets/photos/";
		$this->theme = $theme;
		$this->data['logged_in'] = $this->session->userdata('student_logged');
        $this->data['first_login'] = $this->session->userdata('firstLogin');
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
        $this->data['photo_dir'] = "https://portal.citycollegeoftagaytay.edu.ph/assets/photos/";
        $this->data['csg'] = $this->config->item('csg');
		
		//------------------------------------------------------------------------------
        $this->load->library("email");	
		$this->data['title'] = "iACADEMY Student Portal";
        $this->data['home'] = false;
		$this->data["student"] = $this->session->all_userdata();
        $this->data['page']="default";
        
        
        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->session->userdata('active_sem'));
        $this->data['selected_ay'] = $this->session->userdata('active_sem');

        $this->data['deficiencies_count'] = $this->db
        ->get_where('tb_mas_student_deficiencies',array('student_id'=>$this->session->userdata('intID'),'status'=>'active','temporary_resolve_date <'=> date("Y-m-d")))->num_rows();                    
    }
    
    function clear_cache()
    {
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
    }
    
    public function index()
	{	
        if($this->logged_in()){            
            if($this->session->userdata('firstLogin')){                                
                redirect(base_url()."portal/change_password");            
            }
            else{
                redirect(base_url()."portal/dashboard");
            }
        }
        
        else
            redirect(base_url()."users/student_login");
	}

    public function student_ledger_data($id,$sem){
                                
        $data['student'] = $this->data_fetcher->getStudent($id);
        $registrations =  $this->db->select('tb_mas_sy.*, paymentType')
                                    ->join('tb_mas_sy', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                                    ->where(array('intStudentID'=>$id))
                                    ->order_by("strYearStart asc, enumSem asc")
                                    ->get('tb_mas_registration')
                                    ->result_array();
        $tuition = [];
        foreach($registrations as $reg){            
            $temp = $this->data_fetcher->getTuition($id,$reg['intID']);                            
            $temp['term'] = $reg;     
            
            //TUITION PAYMENTS
            $sql = "SELECT * FROM payment_details WHERE student_number = '".$data['student']['slug']."' AND sy_reference = ".$reg['intID']." AND (description LIKE 'Tuition%' || description LIKE 'Reservation%') AND status = 'Paid' ORDER BY created_at ASC";
            $tuition_payments =  $this->db->query($sql)
                                          ->result_array();                  

            $temp['payments_tuition']  = [];                                  
            foreach($tuition_payments as $tuition_payment){
                $tuition_payment['updated_at'] = date('M j, Y',strtotime($tuition_payment['updated_at']));
                $temp['payments_tuition'][] = $tuition_payment;
            }   
            
            //OTHER PAYMENTS
            $sql = "SELECT * FROM payment_details WHERE student_number = '".$data['student']['slug']."' AND sy_reference = ".$reg['intID']." AND description NOT LIKE 'Reservation%' AND description NOT LIKE 'Tuition%' AND status = 'Paid' ORDER BY created_at ASC";
            $other_payments =  $this->db->query($sql)
                                          ->result_array();                  

            $temp['payments_other']  = [];                                  
            foreach($other_payments as $other_payment){
                $other_payment['updated_at'] = date('M j, Y',strtotime($other_payment['updated_at']));
                $temp['payments_other'][] = $other_payment;
            } 

                                                      

            $ledger = $this->db->select('tb_mas_student_ledger.*,tb_mas_scholarships.name as scholarship_name, enumSem, strYearStart, strYearEnd, term_label, tb_mas_faculty.strFirstname, tb_mas_faculty.strLastname')        
            ->from('tb_mas_student_ledger')
            ->join('tb_mas_sy', 'tb_mas_student_ledger.syid = tb_mas_sy.intID')
            ->join('tb_mas_scholarships', 'tb_mas_student_ledger.scholarship_id = tb_mas_scholarships.intID','left')
            ->join('tb_mas_faculty', 'tb_mas_student_ledger.added_by = tb_mas_faculty.intID','left')                    
            ->where(array('student_id'=>$id,'tb_mas_student_ledger.type'=>'tuition','syid' => $reg['intID']))        
            ->order_by("strYearStart asc, enumSem asc")
            ->get()
            ->result_array();

            $temp['ledger'] = [];

            foreach($ledger as $item){
                $item['date'] = date('M j, Y',strtotime($item['date']));
                $temp['ledger'][] = $item;
            }

            $other = $this->db->select('tb_mas_student_ledger.*, enumSem, strYearStart, strYearEnd, term_label, tb_mas_faculty.strFirstname, tb_mas_faculty.strLastname')        
            ->from('tb_mas_student_ledger')
            ->join('tb_mas_sy', 'tb_mas_student_ledger.syid = tb_mas_sy.intID')
            ->join('tb_mas_faculty', 'tb_mas_student_ledger.added_by = tb_mas_faculty.intID','left')
            ->where(array('student_id'=>$id,'tb_mas_student_ledger.type'=>'other','syid' => $reg['intID']))        
            ->get()
            ->result_array();

            $temp['other'] = [];

            foreach($other as $item){
                $item['date'] = date('M j, Y',strtotime($item['date']));
                $temp['other'][] = $item;
            }

            $tuition[] =  $temp;
            
        }
        
        $data['tuition'] = $tuition;        
        
        $data['ledger_group_term'] =$this->db->select('tb_mas_sy.*, paymentType')
                                        ->join('tb_mas_sy', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                                        ->where(array('intStudentID'=>$id))
                                        ->order_by("strYearStart asc, enumSem asc")
                                        ->get('tb_mas_registration')
                                        ->result_array();

        echo json_encode($data);
    }

    public function ledger($sem = 0){

        $id = $this->session->userdata('intID');
        $this->data['id'] = $id;     
        $this->data['page']="ledger";   
        $this->data['sem'] = $sem;
        $this->data['page'] = "view_all_students";
        $this->data['opentree'] = "finance_student_account";

        $this->load->view("common/header",$this->data);
        $this->load->view("student_ledger",$this->data);
        $this->load->view("common/footer",$this->data);

    }

    public function submit_enlistment_form(){
        $post = $this->input->post();
        $sections_to_add = json_decode($post['sections_to_add']);   

        $insert = [
            'student_id' => $post['student'],
            'term_id' => $post['sem']
        ];

        $enlistment = $this->db->get_where('tb_mas_student_enlistment',$insert)->first_row();
        if(!$enlistment){
            $this->db->insert('tb_mas_student_enlistment',$insert);
            $id = $this->db->insert_id();
        }
        else
            $id = $enlistment['id'];

        foreach($sections_to_add as $cl){
            $insert = [
                'enlistment_id' => $id,
                'classlist_id' => $cl->intID,
            ];
            $this->db->insert('tb_mas_student_enlistment_subject',$insert);
        }

        $data['success'] = true;
        $data['message'] = "Form has been submitted";

        echo json_encode($data);

    }
    public function enlistment($sem = 0){
        $id = $this->session->userdata('intID');
        $this->data['id'] = $id;     
        $this->data['page']="enlistment form";   
        $this->data['sem'] = $sem;        

        $this->load->view("common/header",$this->data);
        $this->load->view("enlistment",$this->data);
        $this->load->view("common/footer",$this->data);
    }

    public function enlistment_data($id,$sem){
        $data['student'] = $this->data_fetcher->getStudent($id);  
        
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
        
        if($sem)
            $data['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        elseif($stype == "college")
            $data['active_sem'] = $this->data_fetcher->get_active_sem();
        else
            $data['active_sem'] = $this->data_fetcher->get_active_sem_shs();

        $data['subject_offerings'] = [];
        $offerings = $this->data_fetcher->getClasslists($data['active_sem']['intID'],0,0,0);
        $data['my_classlists'] = $this->data_fetcher->getClassListStudentsStPortal($id,$data['active_sem']['intID']);
        $data['total_units'] = 0;

        foreach($data['my_classlists'] as $cl){
            $data['total_units'] += intval($cl['strUnits']);
        }
        foreach($offerings as $offering){
            $accept = true;
            foreach($data['my_classlists'] as $cl){
                if($cl['strCode'] == $offering['strCode']){
                    $accept = false;
                    break;
                }
            }                        
            if($accept){
                $slots = $this->data_fetcher->countRemainingSlotsClasslist($offering['intID']);
                if($slots > 0)
                    $data['subject_offerings'][] = $offering;
            }

        }

        $data['sy'] = $this->db->get_where('tb_mas_sy',array('term_student_type'=>$stype))->result_array();    
        echo json_encode($data);
    }
	
    public function select_sem($sem,$page)
    {
        $this->session->set_userdata('active_sem',$sem);
        //redirect(base_url().'portal');
        
        switch($page)
        {
            case 'dashboard':
                redirect(base_url().'portal');
                break;
            case 'grades':
                redirect(base_url().'portal/grades');
                break;
            case 'profile':
                redirect(base_url().'portal/profile');
                break;
            case 'mycourses':
                    redirect(base_url().'portal/mycourses');
                    break;
            case 'accounting_summary':
                    redirect(base_url().'portal/accounting_summary');
                    break;
            case 'schedule':
                redirect(base_url().'portal/schedule');
                break;
            case 'enlistment':
                redirect(base_url().'portal/enlistment');
                break;
        }
    }
	public function dashboard()
	{
         
        if($this->logged_in()){
            $this->data['page']="dashboard";
            $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
    
            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
    
            $this->data['home'] = true;
            $this->data['body_class'] = "homepage";
            $this->load->view('common/header',$this->data);
            $this->load->view('student_dashboard',$this->data);            
            $this->load->view('common/footer',$this->data);
            $this->load->view('common/student_dashboard_conf',$this->data);
        }
        
        else
            redirect(base_url()."users/student_login");
        
       
	}

    public function check_conflict(){

        $post =  $this->input->post();

        $records = $this->data_fetcher->getClassListStudentsSt($post['student'],$post['sem']);
        $records2 = json_decode($post['sections_to_add']);        

        foreach($records as $record){                                
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
        if($records2)
            foreach($records2 as $record){                                
                $conflict = $this->data_fetcher->student_conflict_enlistment($post['section_to_add'],$record,$post['sem']);
                foreach($conflict as $c){
                    if($c){
                        $data['success'] = false;
                        $data['message'] = "There was a conflict with one of the schedules ".$c->conflict['strCode']." ".$c->conflict['strClassName'].$c->conflict['year'].$c->conflict['strSection']." ".$c->conflict['sub_section'];   
                        echo json_encode($data);                             
                        return;
                    }
                }                        
            }
        $data['success'] = true;
        echo json_encode($data);

    }

    public function profile()
	{
        if($this->logged_in()){
            $this->data['page']="profile";
            $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
            $this->data['tab'] = "tab_1";
            
            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
            $this->data['records'] = $this->data_fetcher->getClassListStudentsStPortal($this->session->userdata('intID'),$this->data['selected_ay']);
            
            //print_r($this->data['records']);
            //die();
           
           // $this->data['home'] = true;
            //$this->data['body_class'] = "homepage";
            $this->load->view('common/header',$this->data);
            $this->load->view('profile',$this->data);
            $this->load->view('common/footer',$this->data);
        }
    
    else
        redirect(base_url()."users/student_login");
       
	}

    public function mycourses()
	{
        if($this->logged_in()) {
            $this->data['page']="mycourses";
            $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
    
            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
            $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
    
            $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($this->data['student']['intID'],$this->data['selected_ay']);
            
    
            $this->data['records'] = $this->data_fetcher->getClassListStudentsStPortal($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['total_units'] = $this->data_fetcher->getTotalUnits($this->session->userdata('intID'));
            $this->load->view('common/header',$this->data);
            $this->load->view('mycourses',$this->data);
            $this->load->view('common/footer',$this->data);
        }
    
        else
        redirect(base_url()."users/student_login");
      
	}

    public function grades()
	{ 
        if($this->logged_in()) {
            $this->data['page']="grades";
            $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
    
            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
            $this->data['records'] = $this->data_fetcher->getClassListStudentsSt($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
            $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($this->data['student']['intID'],$this->data['selected_ay']);
            //$this->data['home'] = true;
            //$this->data['body_class'] = "homepage";

                        
            $this->data['subjects_not_taken'] = $this->data_fetcher->getRequiredSubjects($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
            $grades = $this->data_fetcher->assessCurriculum($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
            array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
            $this->data['grades'] = $grades;
            $this->data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculumMain($this->data['student']['intCurriculumID']);
            $this->data['equivalent_subjects'] = $this->data_fetcher->getSubjectsInCurriculumEqu($this->data['student']['intCurriculumID']);
            $this->data['deficiencies'] = $this->db
            ->get_where('tb_mas_student_deficiencies',array('student_id'=>$this->session->userdata('intID'),'status'=>'active','temporary_resolve_date <'=> date("Y-m-d")))->result_array();
            $this->load->view('common/header',$this->data);
            $this->load->view('content',$this->data);
            $this->load->view('common/footer',$this->data);
        }
           
        else
            redirect(base_url()."users/student_login");
        
       
	}

    public function deficiencies($id = 0,$sem = 0)
    {
        
        
        $this->data['id'] = $this->session->userdata('intID');
        $this->data['sem'] = $sem;
        $this->data['page'] = "deficiencies";
        $this->load->view("common/header",$this->data);
        $this->load->view("student_deficiencies",$this->data);
        $this->load->view("common/footer",$this->data);

    }

    public function student_deficiencies_data($sem,$id){
                
        $ret['student'] =  $this->data_fetcher->getStudent($id);  

        if($sem != 0)
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        else{
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
            if($stype == "college")
                $ret['active_sem'] = $this->data_fetcher->get_active_sem();
            else
                $ret['active_sem'] = $this->data_fetcher->get_active_sem_shs();
        }
        $ret['sy'] = $this->db->get('tb_mas_sy')->result_array();

              
        $ret['deficiencies'] = $this->db
                    ->select('tb_mas_student_deficiencies.*,enumSem,term_label,strYearStart,strYearEnd')
                    ->join('tb_mas_sy','tb_mas_student_deficiencies.syid = tb_mas_sy.intID')
                    ->where(array('student_id'=>$id))
                    ->get('tb_mas_student_deficiencies')
                    ->result_array();
                    
        echo json_encode($ret);
    }

    public function schedule()
	{
             
        if($this->logged_in())
        {
            $this->data['page']="schedule";
            $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
            $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($this->data['student']['intID'],$this->data['selected_ay']);
            $records = $this->data_fetcher->getClassListStudentsSt($this->session->userdata('intID'),$this->data['selected_ay']);

            foreach($records as $record)
            {
                $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
                //print_r($record['schedule']);
                $this->data['records'][] = $record;
            }
                        
            $this->data['home'] = true;
            $this->data['body_class'] = "homepage";
            $this->load->view('common/header',$this->data);
            $this->load->view('schedule',$this->data);
            $this->load->view('common/footer',$this->data);
        }
        else
            redirect(base_url().'portal');
	}
    
    public function accounting_summary()
	{
        if($this->logged_in()) {
            $this->data['page']="accounting_summary";
            $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
    
            $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);
            $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
            $this->data['records'] = $this->data_fetcher->getClassListStudentsStPortal($this->session->userdata('intID'),$this->data['selected_ay']);
            
            //print_r($this->data['records']);
            //die();
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
            $this->data['tuition'] = $this->data_fetcher->getTuition($this->session->userdata('intID'),$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['registration']['enumScholarship']);
            
            //$this->data['home'] = true;
            //$this->data['body_class'] = "homepage";
            $this->load->view('common/header',$this->data);
            $this->load->view('accounting_summary',$this->data);
            $this->load->view('common/footer',$this->data);
        }
    
        else
        redirect(base_url()."users/student_login");
        
       
	}

    public function change_password()
	{
        if($this->logged_in())
        {
        $this->data['page']="change_password";
        $this->data['sy'] = $this->data_fetcher->getSyStudentEnrolled($this->session->userdata('intID'), 1);
       
        $this->data['error_message'] = $this->session->flashdata('error_message');
        if($this->session->userdata('firstLogin'))
            $this->data['firstlog'] = "Please update password before you can proceed";



       
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($this->session->userdata('intID'),$this->data['selected_ay']);

        
        $this->data['student'] = $this->data_fetcher->getStudent($this->session->userdata('intID'));
        
        
        $this->data['home'] = true;
        $this->data['body_class'] = "homepage";
	    $this->load->view('common/header',$this->data);
        $this->load->view('change_password',$this->data);
        $this->load->view('common/footer',$this->data);
        $this->load->view('password_js',$this->data);
        }
        else
            redirect(base_url().'portal');
	}

    public function reset_password(){
        echo password_hash("fellowes", PASSWORD_DEFAULT);
    }
    
    public function change_password_submit()
    {
        $post = $this->input->post();
        $pwd = $post['current_password']; 
        $st = current($this->db->get_where('tb_mas_users',array('intID'=>$this->session->userdata('intID')))->result_array());
        if(empty($st))
        {
            $this->session->set_flashdata('error_message','You entered an invalid password');
        }
        else
        {
            if(password_verify($pwd,$st['strPass']))
            {
                $this->session->set_flashdata('error_message','Password Updated');
                $d['strPass'] = password_hash($post['password'], PASSWORD_DEFAULT);
                $d['firstLogin'] = 0;
                $this->session->set_userdata('firstLogin', 0);
                $this->data_poster->post_data('tb_mas_users',$d,$st['intID']);
                $this->data_poster->log_action('Changed Password',$st['strLastname'].' '.$st['strFirstname'].'  Student Number: '.$st['strStudentNumber']." Updated their password to ".$post['password'],'green');
                 
            }
            else
            {
                $this->session->set_flashdata('error_message','You entered an invalid password');
            }
            
        }
        redirect(base_url().'portal/change_password'); 
        
    }
    
   public function logged_in()
    {
        if($this->session->userdata('student_logged'))
            return true;
        else
            return false;
    }

   

}

?>
