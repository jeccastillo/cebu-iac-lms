<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Scholarship extends CI_Controller {

	
    function __construct() {
        parent::__construct();
        $group = $this->db->get_where('tb_mas_user_group',array('group_name'=>'Scholarship'))->first_row();
        $group_members = $this->db->get_where('tb_mas_user_access',array('group_id'=>$group->id))->result_array();

        if(!$this->is_osas() && !$this->is_super_admin() && !$this->allowed_user($group_members))
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
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['campus'] = $this->config->item('campus');
        $this->data['title'] = "CCT Unity";
        $this->load->library("email");	       
        
                
        
        $this->data['page'] = "finance";
        
        //$this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        //$this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		

    }
    
    
    public function scholarships(){
        $this->data['page'] = "scholarships";
        $this->data['opentree'] = "scholarship";
        $this->load->view("common/header",$this->data);
        $this->load->view("scholarships",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/scholarship_list_conf",$this->data);
    }

    public function select_student(){

        $this->data['error_message'] = $this->session->flashdata('error_message');
        $this->data['page'] = "assign_scholarship";
        $this->data['opentree'] = "scholarship";
                                                               

        $this->load->view("common/header",$this->data);
        $this->load->view("select_student",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/assign_scholarship_conf",$this->data);
    }

    public function assign_scholarship($sem = 0,$student = 0){
        
        if($student == 0){
            $post =  $this->input->post();
            $student = $post['student'];
        }

        if($sem != 0)
            $this->data['sem'] = $sem;
        else{
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }
        
            
        $this->data['student'] = $student;
        $this->data['page'] = "assign_scholarship";
        $this->data['opentree'] = "scholarship";                                                                    

        $this->load->view("common/header",$this->data);
        $this->load->view("assign_scholarship",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function student_ledger_data($id,$sem){
                                
        $data['student'] = $this->data_fetcher->getStudent($id);
        $data['student']['strStudentNumber'] = preg_replace("/[^a-zA-Z0-9]+/", "", $data['student']['strStudentNumber']);
        $registrations =  $this->db->select('tb_mas_sy.*, paymentType, enumStudentType')
                                    ->join('tb_mas_sy', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                                    ->where(array('intStudentID'=>$id))
                                    ->order_by("strYearStart desc, enumSem desc")
                                    ->get('tb_mas_registration')
                                    ->result_array();
        $tuition = [];
        
        $data['current_type'] = $registrations[0]['enumStudentType'];

        foreach($registrations as $reg){            
            $temp = $this->data_fetcher->getTuition($id,$reg['intID']);                            
            $temp['term'] = $reg;     
            
            //TUITION PAYMENTS
            $sql = "SELECT * FROM payment_details WHERE student_number = '".$data['student']['slug']."' AND sy_reference = ".$reg['intID']." AND (description LIKE 'Tuition%' || description LIKE 'Reservation%') AND (status = 'Paid' || status = 'Void' ) ORDER BY or_date ASC";
            $tuition_payments =  $this->db->query($sql)
                                          ->result_array();                  

            $temp['payments_tuition']  = [];                                  
            foreach($tuition_payments as $tuition_payment){
                $tuition_payment['or_date'] = date('M j, Y',strtotime($tuition_payment['or_date']));
                $temp['payments_tuition'][] = $tuition_payment;
            }   
            
            //OTHER PAYMENTS
            $sql = "SELECT * FROM payment_details WHERE student_number = '".$data['student']['slug']."' AND sy_reference = ".$reg['intID']." AND description NOT LIKE 'Reservation%' AND description NOT LIKE 'Tuition%' AND (status = 'Paid' || status = 'Void') ORDER BY or_date ASC";
            $other_payments =  $this->db->query($sql)
                                          ->result_array();                  

            $temp['payments_other']  = [];                                  
            foreach($other_payments as $other_payment){
                $other_payment['or_date'] = date('M j, Y',strtotime($other_payment['or_date']));
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

            $temp['balance'] = $this->db->get_where('tb_mas_prev_balance',array('term'=>$reg['intID'],'student_number'=> $data['student']['strStudentNumber']))
                                ->result_array();

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
        $data['user'] = $this->data["user"];
        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $sem = $this->data_fetcher->get_active_sem();  
        $data['active_sem'] = $sem['intID'];

        echo json_encode($data);
    }

    public function student_ledger($id,$sem = 0){

        $this->data['id'] = $id;        
        $this->data['sem'] = $sem;
        $this->data['page'] = "student_ledger_osas";
        $this->data['opentree'] = "scholarship";

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

        $this->load->view("common/header",$this->data);
        $this->load->view("student_ledger_osas",$this->data);
        $this->load->view("common/footer",$this->data);

    }

    public function assign_scholarship_data($student,$sem){
                
        $ret['terms'] = $this->db->get('tb_mas_sy')->result_array();
        $ret['student'] = $this->db->get_where('tb_mas_users',array('intID'=>$student))->first_row('array');
        $ret['students'] = $this->data_fetcher->getStudentsNotInReferral($sem);
        $ret['registration'] = $this->data_fetcher->getRegistrationInfo($student,$sem);
        $has_inhouse = false;
        $has_external = false; 

        
        if($ret['registration']){
            $data['tuition'] = $this->data_fetcher->getTuition($student,$sem);
            $ret['tuition_data'] = $data['tuition'];
            $ret['tuition'] = $this->load->view('tuition/tuition_view', $data, true);         
        }
        else
            $data['tuition'] = "";


        $ret['student_scholarships'] = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.deduction_type,tb_mas_scholarships.name,tb_mas_scholarships.description,tb_mas_scholarships.deduction_from')
                                    ->where(array('syid'=>$sem,'student_id'=>$student,'deduction_type'=>'scholarship'))
                                    ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                                    ->get('tb_mas_student_discount')
                                     ->result_array();
        
        foreach($ret['student_scholarships'] as $scho){
            if($scho['deduction_from'] == "in-house")
                $has_inhouse = true;
            if($scho['deduction_from'] == "external")
                $has_external = true;
        }
        $ret['has_inhouse_discount'] = $has_inhouse;
        $ret['has_external_discount'] = $has_external;
        
        if($has_inhouse && $has_external){
            $ret['scholarships'] = [];
        }
        elseif($has_inhouse)
            $ret['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'scholarship','deduction_from !='=>'in-house'))->result_array();
        elseif($has_external)
            $ret['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'scholarship','deduction_from !='=>'external'))->result_array();
        else
            $ret['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'scholarship'))->result_array();

        //RESET VARS
        $has_inhouse = false;
        $has_external = false; 

        $student_discounts = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.deduction_type,tb_mas_scholarships.name,tb_mas_scholarships.description,tb_mas_scholarships.deduction_from')
                                     ->where(array('syid'=>$sem,'student_id'=>$student,'deduction_type'=>'discount','name NOT LIKE'=>'%Referral%'))
                                     ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                                     ->get('tb_mas_student_discount')
                                      ->result_array();   
                                      
        $referral_discounts = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.deduction_type,tb_mas_scholarships.name,tb_mas_scholarships.description,tb_mas_scholarships.deduction_from')
                                     ->where(array('syid'=>$sem,'student_id'=>$student,'deduction_type'=>'discount','name LIKE'=>'%Referral%'))
                                     ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                                     ->get('tb_mas_student_discount')
                                      ->result_array();   
        
        $num_ref_disc = count($referral_discounts);                                                                              
                                      
        foreach($student_discounts as $scho){
            if($scho['deduction_from'] == "in-house")
                $has_inhouse = true;
            if($scho['deduction_from'] == "external")
                $has_external = true;
        }
        if($has_inhouse && $has_external){
            $discounts = [];
        }
        elseif($has_inhouse)
            $discounts = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discount','deduction_from !='=>'in-house','name NOT LIKE'=>'%Referral%'))->result_array();
        elseif($has_external)
            $discounts = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discount','deduction_from !='=>'external','name NOT LIKE'=>'%Referral%'))->result_array();                                      
        else
            $discounts = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discount','name NOT LIKE'=>'%Referral%'))->result_array();                                      
        
                             
        if($num_ref_disc < 10)
            $ref_discounts = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discount','name LIKE'=>'%Referral%'))->result_array();
        else
            $ref_discounts = [];
        
        $ret['discounts'] = array_merge($discounts,$ref_discounts);
        $ret['student_discounts'] = $student_discounts;
        $ret['has_inhouse_discount'] = $has_inhouse;
        $ret['has_external_discount'] = $has_external;

        echo json_encode($ret);

    }

    public function scholarship_view_data($sem){

        // $ret['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'scholarship'))->result_array();
        // $ret['discounts'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discount'))->result_array();
        $ret['terms'] = $this->db->get('tb_mas_sy')->result_array();        
        
                
        $ret['students'] = $this->db->select('tb_mas_users.*')
                                    ->where(array('syid'=>$sem))
                                    ->join('tb_mas_users','tb_mas_users.intID = tb_mas_student_discount.student_id')
                                    ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                                    ->group_by('student_id')
                                    ->get('tb_mas_student_discount')
                                     ->result_array();
                            

        echo json_encode($ret);

    }

    public function scholarship_view($sem = 0){
                
        if($sem != 0)
            $this->data['sem'] = $sem;
        else{
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }
        
        $this->data['module'] = "scholarship";
        $this->data['page'] = "scholarship_view_students";
        $this->data['opentree'] = "scholarship";
                                                                

        $this->load->view("common/header",$this->data);
        $this->load->view("scholarship_view_students",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function view($id){
        $this->data['page'] = "add_scholarship";
        $this->data['opentree'] = "scholarship";
        $this->data['id'] = $id;
        $this->load->view("common/header",$this->data);
        $this->load->view("scholarship_view",$this->data);
        $this->load->view("common/footer",$this->data);
    }

    public function add_scholarship(){
        $post = $this->input->post();
        $post['status'] = "pending";

        // Specify the conditions
        $conditions = array(
            'discount_id' => $post['discount_id'],
            'student_id' => $post['student_id'],
            //'referrer_id' => $post['referrer_id'],
            'syid' => $post['syid'],
        );

        $this->db->where($conditions);
        //check if exist in db
        $query = $this->db->get('tb_mas_student_discount');

        if ($query->num_rows() > 0) {
            // Record exists
            $data['success'] = "error";
            $data['message'] = "Record Exist";
        } else {
            $this->db->insert('tb_mas_student_discount',$post);
            $scholarship = $this->db->get_where('tb_mas_scholarships',array('intID'=>$post['discount_id']))->first_row('array');
            $student = $this->db->get_where('tb_mas_users',array('intID'=>$post['student_id']))->first_row('array');
            $this->data_poster->log_action('Scholarships','Added a Scholarship '.$scholarship['name'].' for student '.$student['strLastname'].' '.$student['strFirstname'],'green');

            $data['success'] = "success";
            $data['message'] = "Added Successfully";
        }
        echo json_encode($data);
    }

    public function update_scholarship_status(){
        $post = $this->input->post();
        
        $st_scholarship = $this->db->get_where('tb_mas_student_discount',array('id'=>$post['id']))->first_row('array');
        $scholarship = $this->db->get_where('tb_mas_scholarships',array('intID'=>$st_scholarship['discount_id']))->first_row('array');
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$st_scholarship['student_id']))->first_row('array');
        
        if($post['status'] == "applied")
            $post['date_applied'] = date("Y-m-d H:i:s");

        $deductions = 0;
        //Get deduction amount
        
        $reg = $this->db->get_where('tb_mas_registration',array('intStudentID'=>$student['intID'],'intAYID'=>$st_scholarship['syid']))->first_row('array');
        //First Check if for installment or full payment
        //Discount still getting 0 on ledger
        if($scholarship['deduction_type'] == "scholarship"){            
            $tuition_data = $this->data_fetcher->getTuition($student['intID'],$st_scholarship['syid'],$scholarship['intID']);                    
            if($reg && $reg['paymentType'] == "full")            
                $deductions = $tuition_data['scholarship_deductions'];            
            else
                $deductions = $tuition_data['scholarship_deductions_installment'];            
        }
        else{
            $tuition_data = $this->data_fetcher->getTuition($student['intID'],$st_scholarship['syid'],0,$scholarship['intID']);                                
            if($reg && $reg['paymentType'] == "full")            
                $deductions = $tuition_data['scholarship_deductions_dc'];            
            else
                $deductions = $tuition_data['scholarship_deductions_installment_dc'];
        }

        if($this->db
        ->where(array('id'=>$post['id']))
        ->update('tb_mas_student_discount',$post)){
            $data['success'] = "success";
            $data['message'] = "Updated Successfully";
            $this->data_poster->log_action('Scholarships','Updated Scholarship '.$scholarship['name'].' for student '.$student['strLastname'].' '.$student['strFirstname'],'green');
        }
        else{
            $data['success'] = "error";
            $data['message'] = "Failed to update";
        }
        
        echo json_encode($data);
    }

    public function delete_scholarship(){
        $post = $this->input->post();

        $disc = $this->db->get_where('tb_mas_student_discount',array('id'=>$post['id']))->first_row('array');
        $scholarship = $this->db->get_where('tb_mas_scholarships',array('intID'=>$disc['discount_id']))->first_row('array');
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$disc['student_id']))->first_row('array');

        $this->db->where(array('id'=>$post['id']))
                 ->delete('tb_mas_student_discount');

        $this->data_poster->log_action('Scholarships','Removed a Scholarship '.$scholarship['name'].' for student '.$student['strLastname'].' '.$student['strFirstname'],'green');

        $data['success'] = "success";
        $data['message'] = "Deleted Successfully";

        echo json_encode($data);

    }

    //get enrolled students based on search result by name
    public function enrolled_student_data(){
        $search = $this->input->get('search') ?  $this->input->get('search') : ''; 
        $students = $this->data_fetcher->getListEnrolledStudent($search);

        $data['referees'] =  $students;
        echo json_encode($data);
    }

    public function data($id){
        $data['scholarship'] = $this->db->get_where('tb_mas_scholarships',array('intID'=>$id))->row();        
        $data['status_options'] = get_enum_values('tb_mas_scholarships','status');
        $data['type_options'] = get_enum_values('tb_mas_scholarships','type');
        echo json_encode($data);
    }

    public function submit_form(){
        $post = $this->input->post();
        if($post['intID'] == 0){
            unset($post['intID']);
            $post['created_by_id'] =  $this->data["user"]["intID"];
            $this->db->insert('tb_mas_scholarships',$post);
            $data['id'] = $this->db->insert_id();
        }
        else{
            $this->db
				 ->where('intID',$post['intID'])
				 ->update('tb_mas_scholarships',$post);
            
            $data['id'] = $post['intID'];
        }
        $data['message'] = "Success";
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

    public function allowed_user($users)
    {
        $user_id = $this->session->userdata('intID');
        foreach($users as $user)
            if($user['user_id'] == $user_id)
                return true;
    
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

    public function is_osas()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 7)
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

