<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Finance extends CI_Controller {

	
    function __construct() {
        parent::__construct();

        
        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2 && $userlevel != 6)
		  redirect(base_url()."unity");
        
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
        $this->data['term_type'] = $this->config->item('term_type');
        $this->data["campus"] = $this->config->item('campus');
        $this->data["user"] = $this->session->all_userdata();
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Unity";
        $this->data['api_url'] = $this->config->item('api_url');
        
        $this->load->library("email");
        
                
        
        $this->data['page'] = "finance";
        
        //$this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        //$this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
                
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		

    }
    public function other_payment_data(){
        $data['cashier'] = $this->db->get_where('tb_mas_cashier',array('user_id'=>$this->data['user']['intID']))->first_row();
        $sem = $this->data_fetcher->get_active_sem();        
        $data['current_sem'] = $sem['intID'];
        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $data['sem_year'] = $sem['strYearStart'];
        $data['payees'] = $this->db->get('tb_mas_ns_payee')->result_array();
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);
    }

    public function temp_admin(){
        $post = $this->input->post();
        $this->db->where(array('intID'=>$post['intID']))
                 ->update('tb_mas_cashier',$post);

        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);

        
    }
    public function manualPayData($slug){
        $data['data'] = $this->data_fetcher->fetch_single_entry('tb_mas_users',$slug,'slug');        
        $data['cashier'] = $this->db->get_where('tb_mas_cashier',array('user_id'=>$this->data['user']['intID']))->first_row();
        $data['user'] = $this->data['user'];

        $data['particulars'] = $this->db
                    ->get_where('tb_mas_particulars',array('type'=>'particular'))
                    ->result_array();

        $sem = $this->data_fetcher->get_active_sem();    
        $role = $this->session->userdata('special_role');
        $data['advanced_privilages'] = (in_array($role,array(1,2)) )?true:false;            
        $data['finance_manager_privilages'] = ($role == 2)?true:false;    
        
        $data['current_sem'] = $sem['intID'];
        $data['sem_year'] = $sem['strYearStart'];
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);
    }

    public function edit_submit_ay()
    {
        $post = $this->input->post();                
       // $this->data_poster->set
        $this->data_poster->post_data('tb_mas_sy',$post,$post['intID']);
        $this->data_poster->log_action('Finance Details Updated','Updated Term Info: '.$post['enumSem']." ".$post['strYearStart']." - ".$post['strYearEnd'],'aqua');
        redirect(base_url()."finance/edit_ay/".$post['intID']);
            
    }

    public function edit_ay($id = 0){

        if($id == 0)
            $this->data['item'] = $this->data_fetcher->get_active_sem();
        else
            $this->data['item'] = $this->data_fetcher->getAy($id);  
        
        $this->data['page'] = "installment_dates";
        $this->data['opentree'] = "finance_admin";
            
        
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        
        $this->load->view("common/header",$this->data);
        $this->load->view("edit_ay_cashier",$this->data);
        $this->load->view("common/footer",$this->data);         
        $this->load->view("common/edit_ay_conf",$this->data); 
    }


    public function student_account_report($term = 0){

        if($term == 0)
            $term = $this->data_fetcher->get_processing_sem();        
        else
            $term = $this->data_fetcher->get_sem_by_id($term); 
         

        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $this->data['current_sem'] = $term['intID'];
        $this->data['page'] = "student_account_report";
        $this->data['opentree'] = "finance_student_account";
        
        $this->load->view("common/header",$this->data);
        $this->load->view("student_account_report",$this->data);
        $this->load->view("common/footer",$this->data);         
        $this->load->view("common/edit_ay_conf",$this->data); 
    }

    public function payee($id = 0){
        
        $this->data['page'] = "payee_setup";
        $this->data['opentree'] = "finance_admin";  
        $this->data['id'] = $id;                          
        
        $this->load->view("common/header",$this->data);
        $this->load->view("add_payee",$this->data);
        $this->load->view("common/footer",$this->data);                 
    }

    public function submit_payee(){
        
        $post = $this->input->post();
        if($post['id'] != "undefined"){
            $this->db->where('id',$post['id'])
                    ->update('tb_mas_ns_payee',$post);
            $id = $post['id'];
        }
        else{
            unset($post['id']);
            $this->db->insert('tb_mas_ns_payee',$post);
            $id = $this->db->insert_id();
        }

        $data['success'] = true;
        $data['id'] = $id;
        echo json_encode($data);
    }

    public function payee_data($id){
        if($id != 0)
            $data['payee'] = $this->db->get_where('tb_mas_ns_payee',array('id'=>$id))->first_row();
        else
            $data['payee'] = null;

        echo json_encode($data);
    }

    public function get_payee_details(){
        $post =  $this->input->post();        
        $data = json_decode($post['data']);
        $ret = [];
        $ret['data'] = [];
        foreach($data as $item){
            $details = null;
            if($item->student_information_id != 0){
                $details = $this->db->get_where('tb_mas_users',array('slug'=>$item->slug))->first_row();                 
                if($details && $details->strStudentNumber[0] != "T"){
                    $item->student_number = preg_replace("/[^a-zA-Z0-9]+/", "", $details->strStudentNumber);
                }
                else{
                    $sem = $this->db->get_where('tb_mas_sy',array('intID'=>$item->sy_reference))->first_row(); 
                    $item->student_number = "A".$sem->strYearStart.str_pad($item->student_information_id, 4, '0', STR_PAD_LEFT);
                }
            }
            else{
                $details = $this->db->get_where('tb_mas_ns_payee',array('lastname'=>$item->lastname,'firstname'=>$item->firstname))->first_row(); 
                $item->student_number = $details->id_number;
            }
            
            
            $ret['data'][] = $item;

        }

        $ret['success'] =  true;        
        echo json_encode($ret);
    }

    public function view_payees(){
        
        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        
        if($userlevel == 2 || ($userlevel == 6 && $role == 2)){
            $this->data['page'] = "payee_setup";
            $this->data['opentree'] = "cashier_admin";

            $this->load->view("common/header",$this->data);
            $this->load->view("view_payees",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/payee_conf",$this->data);
        }

    }

    public function view_payees_cashier(){
        
        $this->data['page'] = "view_payees_cashier";
        $this->data['opentree'] = "cashier";

        $this->load->view("common/header",$this->data);
        $this->load->view("payees_cashier_view",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/payee_conf2",$this->data);

    }

    public function update_payment(){
                

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        
        if($role == 0 && $userlevel != 2)
            redirect(base_url()."unity");

        $this->data['page'] = "update_payment";
        $this->data['opentree'] = "finance_admin";

        $this->load->view("common/header",$this->data);
        $this->load->view("update_payment_details",$this->data);
        $this->load->view("common/footer",$this->data);        

    }

    public function override_payment(){
                

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        
        if($role == 0 && $userlevel != 2)
            redirect(base_url()."unity");

        $this->data['page'] = "override_payment";
        $this->data['opentree'] = "finance_admin";

        $this->load->view("common/header",$this->data);
        $this->load->view("override_payment",$this->data);
        $this->load->view("common/footer",$this->data);        

    }

    public function override_payment_data(){
        $data['user'] = $this->data["user"];
        $data['campus'] = $this->data["campus"];
        echo json_encode($data);        
    }

    public function view_all_students_ledger($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$sem=0,$scholarship=0,$registered=0)
    {
        if($this->faculty_logged_in())
        {            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();
            $this->data['page'] = "view_all_students";
            $this->data['opentree'] = "finance_student_account";

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
            
            $this->load->view("common/header",$this->data);
            $this->load->view("payment_search",$this->data);
            $this->load->view("common/footer",$this->data);
            
            
            $this->load->view("common/ledger_table_conf",$this->data);

            
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
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
        $this->data['page'] = "view_all_students";
        $this->data['opentree'] = "finance_student_account";

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
        $this->load->view("student_ledger",$this->data);
        $this->load->view("common/footer",$this->data);

    }

    public function transfer_ledger_update(){
        $post = $this->input->post();
        $payments = explode(",",$post['payments']);
        $data = array('syid' => $post['sy_reference']);        
        $sy_to = $this->db->get_where('tb_mas_sy',array('intID'=>$post['sy_reference']))->first_row(); 
        foreach($payments as $payment){                        
            $payment_data = $this->db->get_where('tb_mas_student_ledger',array('or_number'=>$payment))->first_row();
            $sy_from = $this->db->get_where('tb_mas_sy',array('intID'=>$payment_data->syid))->first_row(); 
            $this->data_poster->log_action('Payment Term Forwarded','Forwarded OR #'.$payment_data->or_number." from  Term ".$sy_from->term_student_type." ".$sy_from->enumSem." ".$sy_from->term_label." ".$sy_from->strYearStart."-".$sy_from->strYearEnd." to Term ".$sy_to->term_student_type." ".$sy_to->enumSem." ".$sy_to->term_label." ".$sy_to->strYearStart."-".$sy_to->strYearEnd,'green');
            $this->db->where('or_number',$payment)
                     ->update('tb_mas_student_ledger',$data);
        }

        $ret['success'] = true;
        $ret['message'] = "Updated";

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
        
        $this->data['module'] = "finance";        
        $this->data['page'] = "scholarship_view_students";
        $this->data['opentree'] = "cashier";
                                                                

        $this->load->view("common/header",$this->data);
        $this->load->view("scholarship_view_students",$this->data);
        $this->load->view("common/footer",$this->data);        
    }
    

    public function sync_payment_details_data(){

        $response = $this->input->post();
        $response = json_decode($response['data']);
            
        foreach($response as $data){
            $item = $this->db->get_where('payment_details',array('id'=>$data->id))->first_row();
            if(isset($item))
                $this->data_poster->post_data('payment_details',$data,$data->id,'id');
            else
                $this->data_poster->post_data('payment_details',$data);
            
        }
        
        $ret['success'] = true;
           
        echo json_encode($ret);
    }
    
    public function submit_ledger_item(){
        $post =  $this->input->post();
        $post['added_by'] = $this->session->userdata('intID');
        $this->db->insert('tb_mas_student_ledger',$post);

        $data['success'] =  true;
        $data['message'] = "Successfully added to ledger";

        echo json_encode($data);

    }

    public function delete_ledger_item(){
        $post =  $this->input->post();        
        $this->db->where('id',$post['id'])
                 ->delete('tb_mas_student_ledger');

        //$this->data_poster->log_action('Ledger','Deleted'.$post['or_number']." for ".$post['description']." with the amount of ".$amount,'red');

        $data['success'] =  true;
        $data['message'] = "Successfully deleted item";

        echo json_encode($data);

    }

    public function update_ledger_item($term = 0){
        $post =  $this->input->post();        
        
        if($this->db
            ->where('id',$post['id'])
            ->update('tb_mas_student_ledger',$post)){
            
            $data['success'] =  true;
            $data['message'] = "Successfully updated ledger";
        }
        else{
            $data['success'] =  false;
            $data['message'] = "Something went wrong";
        }

        echo json_encode($data);

    }

    public function apply_to_term(){
        $post =  $this->input->post();
        $transfer_data = json_decode($post['transfer_data']);
        $amount_from = 0;
        $sy_from = $this->data_fetcher->get_sem_by_id($post['sy_from']);                

        foreach($transfer_data as $item){
            $amount_to = 0 - floatval($item->amount);
            $to = [
                'student_id' => $post['student_id'],
                'date' => date("Y-m-d H:i:s"),
                'name' => $item->description,
                'syid' => $item->term_to,
                'amount' => $amount_to, 
                'type' => $item->type,   
                'remarks' => "APPLIED FROM ".strtoupper($sy_from['enumSem']." ".$sy_from['term_label']." ".$sy_from['strYearStart']." - ".$sy_from['strYearEnd']),
                'added_by' => $this->session->userdata('intID'),
            ];        
            $this->db->insert('tb_mas_student_ledger',$to);
            $sy_to = $this->data_fetcher->get_sem_by_id($item->term_to);
            $amount_from += floatval($item->amount);
        }
        
        $from = [
            'student_id' => $post['student_id'],
            'date' => date("Y-m-d H:i:s"),
            'name' => "Term Balance Adjustment",
            'syid' => $post['sy_from'],
            'amount' => $amount_from, 
            'type' => 'tuition',   
            'remarks' => "APPLIED TO ".strtoupper($sy_to['enumSem']." ".$sy_to['term_label']." ".$sy_to['strYearStart']." - ".$sy_to['strYearEnd']),
            'added_by' => $this->session->userdata('intID'),
        ];        
        $this->db->insert('tb_mas_student_ledger',$from);

        

        $data['success'] =  true;
        $data['message'] = "Successfully updated ledger";
        echo json_encode($data);
        
    }

    public function update_ledger_item_status(){
        $post = $this->input->post();

        $data['is_disabled'] = $post['type'];
        
        $this->db
            ->where('id',$post['id'])
            ->update('tb_mas_student_ledger',$data);

        $data['success'] =  true;
        $data['message'] = "Successfully updated ledger";

        echo json_encode($data);
    }

    public function manualPay($slug,$type="Reservation Payment"){
                
        $this->data['type'] = $type;
        $this->data['slug'] = $slug;
        

        $this->load->view("common/header",$this->data);
        $this->load->view("manual_pay",$this->data);
        $this->load->view("common/footer",$this->data);
    }

    public function new_cashier(){
        $post = $this->input->post();
        $cashier = $this->db->get_where('tb_mas_cashier',array('user_id'=>$post['user_id']))->num_rows();
        if($cashier > 0){
            $data['message'] = "Failed Cashier with selected ID already exists";
            $data['success'] = false;    
        }
        else{
            $this->db->insert('tb_mas_cashier',$post);
            $data['message'] = "Success";
            $data['success'] = true;
        }
        echo json_encode($data);
    }
    public function next_or_other(){
        $post = $this->input->post();
        $data = $post;
        $current_or = $post['or_current'];        
        
        
        $cashier = $this->db->get_where('tb_mas_cashier',array('intID'=>$data['intID']))->row();
        
        if($data['or_current'] >= $cashier->or_end)
            $data['or_current'] = null;
        else
            $data['or_current'] += 1;

        $this->db
            ->where('intID',$data['intID'])
            ->update('tb_mas_cashier',$data);
        
            $ret['message'] = "Payments";
        

        echo json_encode($ret);
    }    
    
    public function next_or(){
        $post = $this->input->post();
        $data = $post;
        unset($data['sy']);
        $current_or = $post['or_current'];
        if(isset($post['registration_id'])){
            unset($data['payments']);
            unset($data['description']);
            unset($data['registration_id']);
            unset($data['student_id']);
            unset($data['total_amount']);            
            unset($data['or_number']);            
            unset($data['installment']);
            unset($data['payment_type']);
            unset($data['description_other']);            
            
        }

        if(isset($post['sy']))
            $sem = $this->data_fetcher->get_sem_by_id($post['sy']);  
        else
            $sem = $this->data_fetcher->get_active_sem();  
        
        unset($post['sy']);
        
        $cashier = $this->db->get_where('tb_mas_cashier',array('intID'=>$data['intID']))->row();        
        
        if($post['or_used'] == $data['or_current']){
            if($data['or_current'] >= $cashier->or_end)
                $data['or_current'] = null;
            else
                $data['or_current'] += 1;
        }

        unset($data['or_used']);
        
        $this->db
            ->where('intID',$data['intID'])
            ->update('tb_mas_cashier',$data);

        

        if(isset($post['registration_id'])){
            

            if($post['description_other'] == "full"){                                
                $update['fullpayment'] = 1;
                $update['paymentType'] = $post['payment_type'];
            }
            if($post['description_other'] == "down"){                
                
                $update['downpayment'] = 1;
                $update['paymentType'] = $post['payment_type'];
                
                $this->db
                     ->where(array('name'=>'tuition','syid'=>$sem['intID']))
                     ->update('tb_mas_student_ledger',array('amount'=>$post['installment']));
            }
            
           
            $registration = $this->db->get_where('tb_mas_registration',array('intRegistrationID' => $post['registration_id']))->first_row('array');
            $student = $this->db->get_where('tb_mas_users',array('intID'=>$registration['intStudentID']))->first_row('array');
           
            if(!empty($update)){
                $this->db
                        ->where(array('intRegistrationID'=>$post['registration_id']))
                        ->update('tb_mas_registration',$update);

                
                $tuition_data = $this->data_fetcher->getTuition($registration['intStudentID'],$registration['intAYID']);                            
               
        
                $amount = 0;
                if($post['payment_type'] == "full")
                    $amount = $tuition_data['total_before_deductions'];
                else
                    $amount = $tuition_data['ti_before_deductions'];
            
            }


                   

            if($post['description'] == "Tuition Fee" && $registration['intROG'] == 0){
                $ret['message'] = "First Tuition Payment";
                $ret['send_notif'] = true;
                if($student['strStudentNumber'][0] == "T"){
                    $temp['strStudentNumber'] = $this->data_fetcher->generateNewStudentNumber($this->data['campus'],$registration['intAYID'],get_stype($student['level']));
                    $this->db
                        ->where('intID',$student['intID'])
                        ->update('tb_mas_users',$temp);
                }
                $reg_update = [
                    "dteRegistered" => date("Y-m-d H:i:s"),
                    "intROG" => 1,                    
                ];
                $this->db
                    ->where('intRegistrationID',$post['registration_id'])
                    ->update('tb_mas_registration',$reg_update);                

            }
            else{
                $ret['message'] = "Payments";
            }
        }
        else
            $ret['message'] = "Payments";
        

        echo json_encode($ret);
    }

    public function view_all_students($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$sem=0,$scholarship=0,$registered=0)
    {
        if($this->faculty_logged_in())
        {            
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();

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
            
            $this->load->view("common/header",$this->data);
            $this->load->view("payment_search",$this->data);
            $this->load->view("common/footer",$this->data);
            
            
            $this->load->view("common/users_table_conf",$this->data);

            
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }

    
    public function check_or_printed($or){
        
        $printed = $this->db->where(array('or_number'=>(string)$request['or_number'],'campus'=>$this->data['campus']))
                        ->get('tb_mas_printed_or')
                        ->first_row();

        if($printed)
            return true;
        else
            return false;
    }

    public function remove_or_print($or){
        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        if($userlevel == 2 || ($userlevel == 6 && $role == 2)){
            $this->data_poster->delete_or_print($or,$this->data['campus']);
        }
        redirect(base_url()."finance/payments");
    }

    public function remove_from_ledger(){        
        
        $post = $this->input->post();        
        $amount =  -1 *  floatval($post['total_amount_due']);  
                
        $this->data_poster->delete_or_print($post['or_number'],$this->data['campus']);
        $this->db->where(array('name'=>$post['description'],'syid'=>$post['sy_reference'], 'amount'=> $amount, 'student_id'=> $post['student_id']))
            ->limit(1)    
            // ->get('tb_mas_student_ledger')            
            // ->result_array();
            ->delete('tb_mas_student_ledger');

        $this->data_poster->log_action('Cashier','Retracted OR number '.$post['or_number']." for ".$post['description']." with the amount of ".$amount,'red');

        $ret['message'] = "Successfully updated";
        $ret['test'] =  $test;
        $ret['success'] =  true;
        echo json_encode($ret);
    }

    public function view_particulars($type)
    {
                       
        $this->data['type'] = $type;
        $this->data['page'] = ($type=='particular')?"view_particulars":"view_payment_types";
        $this->data['opentree'] = "finance_student_account";

        $this->load->view("common/header",$this->data);
        $this->load->view("view_particulars",$this->data);
        $this->load->view("common/footer",$this->data);
    }

    public function view_particulars_data($type){
                
       $ret['particular'] = $this->db
                    ->get_where('tb_mas_particulars',array('type'=>$type))
                    ->result_array();
                    
        echo json_encode($ret);
    }

    public function add_particular()
    {
        $post = $this->input->post();
        $this->data_poster->post_data('tb_mas_particulars',$post);
        $this->data_poster->log_action('Particular','Added a new particular '.$post['name'],'green');        
        
        $ret['message'] = "success";
        $ret['success'] = true;

        echo json_encode($ret);
     }

     public function delete_particular($id)
     {
        $post = $this->input->post();            
        $particular = $this->db->get_where('tb_mas_particulars',array('id'=>$id))->first_row('array');            
        $this->data_poster->deleteItem('tb_mas_particulars',$id,'id');
        $this->data_poster->log_action('Particular','Deleted a particular: '.$particular['name'],'red');
        $data['message'] = "success";
        $data['success'] = true;
        echo json_encode($data);
     }

    
    public function update_cashier(){
        $post = $this->input->post();                     
        $valid = true; 
        $data['reload'] = false;
        
        $cashier = $this->db->get_where('tb_mas_cashier',array('intID'=>$post['intID']))->row();
        
        
        $cashier_validation_start = $this->db->get_where('tb_mas_cashier',array('intID !='=>$post['intID'],'or_start <='=>$post['start'],'or_end >=' => $post['start']))->row();
        $cashier_validation_end = $this->db->get_where('tb_mas_cashier',array('intID !='=>$post['intID'],'or_start <='=>$post['end'],'or_end >=' => $post['end']))->row();        
        $cashier_validation_both = $this->db->get_where('tb_mas_cashier',array('intID !='=>$post['intID'],'or_start >='=>$post['start'],'or_end <=' => $post['end']))->row();
        if($cashier_validation_start || $cashier_validation_end || $cashier_validation_both){            
            $data['message'] = "Conflict with one of the cashiers";
            $data['success'] = false;                
        }        
        else{            
            
            
            if($post['start'] <= $post['end']){
                $update = array(
                    "or_start" => $post['start'],
                    "or_end" => $post['end'],
                    "or_current" => $post['start']
                );
                $this->db
                        ->where('intID',$post['intID'])
                        ->update('tb_mas_cashier',$update);

                $cashier_up = $this->db->get_where('tb_mas_cashier',array('intID'=>$post['intID']))->row();

                $message = $this->data["user"]["strFirstname"]." ".$this->data["user"]["strLastname"]."Updated OR Series for Cashier #".$post['intID']." ";
                $message .= "from ".$cashier->or_start." to ".$cashier_up->or_start." and ".$cashier->or_end." to ".$cashier_up->or_end." ";
                $message .= "current OR updated from ".$cashier->or_current." to ".$cashier_up->or_current;

                $this->data_poster->log_action('Cashier',$message,'orange');
                $data['message'] = "Successfully Updated";
                $data['success'] = true;
            }
            else{
                $data['message'] = "OR Start can not be greater than OR End";
                $data['success'] = false;
            }
        }
        
    
        echo json_encode($data);
    }
    public function payments_no_or(){                             

        $this->data['page'] = "no_or";
        $this->data['opentree'] = "cashier";
        $this->load->view("common/header",$this->data);
        $this->load->view("no_or_list",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/list_conf",$this->data); 
    }

    public function ns_transactions($payee_id,$sem = 0){        
        $payee = $this->db->get_where('tb_mas_ns_payee',array('id'=>$payee_id))->first_row('array');
        $this->data['first_name'] = $payee['firstname'];
        $this->data['last_name'] = $payee['lastname'];
        $this->data['payee_id'] = $payee_id;
        if($sem != 0)
            $this->data['sem'] = $sem;
        else{
            $active_sem = $this->data_fetcher->get_active_sem();
            $this->data['sem'] = $active_sem['intID'];
        }

        $this->load->view("common/header",$this->data);
        $this->load->view("ns_transactions",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function ns_transactions_data($payee,$sem){   
        $data['sy'] = $this->db->get('tb_mas_sy')->result_array();            
        $data['cashier'] = $this->db->get_where('tb_mas_cashier',array('user_id'=>$this->data['user']['intID']))->first_row();
        $data['user'] = $this->data['user'];
        $sem = $this->data_fetcher->get_sem_by_id($sem);        
        $data['payee'] = $this->db->get_where('tb_mas_ns_payee',array('id'=>$payee))->first_row('array');
        $data['current_sem'] = $sem['intID'];
        $data['sem_year'] = $sem['strYearStart'];
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);
    }

    public function payments($date = 0, $other = 0, $type = "all"){                        

        if($date == 0)
            $date = date("Y-m-d");

        $sem = $this->data_fetcher->get_active_sem();        
        $this->data['type'] = $type;
        $this->data['current_sem'] = $sem['intID'];
        $this->data['date'] = $date;
        $this->data['other'] = $other;

        if($other == 0)
            $this->data['page'] = "transactions";
        else
            $this->data['page'] = "other_payments_report";

        $this->data['opentree'] = "cashier";
        $this->load->view("common/header",$this->data);
        $this->load->view("payments_report",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/list2_conf",$this->data); 
    }
    

    public function cashier(){                                     

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        
        if($role == 0 && $userlevel != 2)
            redirect(base_url()."unity");

        $this->data['page'] = "cashier";
        $this->data['opentree'] = "cashier_admin";
        $this->load->view("common/header",$this->data);
        $this->load->view("cashier",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function reset_cashier(){
        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        
        if($role == 0 && $userlevel != 2){
            $post = $this->input->post();
            $data['or_start'] = null;
            $data['or_end'] = null;
            $data['or_current'] = null;
        
            $this->db
                ->where('id',$post['id'])
                ->update('tb_mas_cashier',$data);
        }

    }

    public function add_discount(){

        $post = $this->input->post();
        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        $reg = $this->db->get_where('tb_mas_registration',array('intRegistrationID'=>$post['registration_id']))->first_row('array');
        $sem = $this->data_fetcher->get_active_sem();        
        

        if($role == 0 && $userlevel != 2){
            $data['message'] = "You don't have permission to add discount";
            $data['success'] = false;
        }
        else{
            $this->db->insert('tb_mas_registration_discount',$post);
            $tuition = $this->data_fetcher->getTuition($reg['intStudentID'], $sem['intID'], $reg['enumScholarship']);
            if($post['type'] == "fixed")
                $amount = $post['discount'];
            else{
                $down = $this->db->get_where('tb_mas_student_ledger',array('name'=>'Tuition Down Payment','syid'=>$sem['intID']))->first_row();
                if($down)
                    $amount =  ($tuition['total_installment'] * ($post['discount']/100));
                else
                    $amount = ($tuition['total'] * ($post['discount']/100));
            }

            $data['message'] = "Successfully Added Discount";
            $data['success'] = true;
        }
            


        echo json_encode($data);
    }

    public function delete_discount(){

        $post = $this->input->post();
        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        $discount = $this->db->get_where('tb_mas_registration_discount',array('id'=>$post['id']))->first_row('array');
        $reg = $this->db->get_where('tb_mas_registration',array('intRegistrationID'=>$post['registration_id']))->first_row('array');
        $sem = $this->data_fetcher->get_active_sem();        
        

        if($role == 0 && $userlevel != 2){
            $data['message'] = "You don't have permission to add discount";
            $data['success'] = false;
        }
        else{
            
            $tuition = $this->data_fetcher->getTuition($reg['intStudentID'], $sem['intID'], $reg['enumScholarship']);
            if($discount['type'] == "fixed")
                $amount = $discount['discount'];
            else{
                $down = $this->db->get_where('tb_mas_student_ledger',array('name'=>'Tuition Down Payment','syid'=>$sem['intID']))->first_row();
                if($down)
                    $amount = $tuition['total_installment'] * ($discount['discount']/100);
                else
                    $amount = $tuition['total'] * ($discount['discount']/100);
            }          

            $this->db->where(array('id'=>$post['id']))            
            ->delete('tb_mas_registration_discount');

            $data['message'] = "Successfully Removed Discount";
            $data['success'] = true;
        }
            


        echo json_encode($data);
    }

    public function other_payments(){                                     

        $this->data['page'] = "other_payments";
        $this->data['opentree'] = "cashier";
        $this->load->view("common/header",$this->data);
        $this->load->view("other_payments",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function cashier_data(){                             

        $data['cashiers'] = [];
        $cashiers = $this->data_fetcher->getCashiers();   
        foreach($cashiers as $cashier){
            $cashier['temporary_admin'] = $cashier['temporary_admin']?true:false;
            $data['cashiers'][] = $cashier;
        }     
        $data['finance_users'] = $this->data_fetcher->getFinanceList();
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);       
    }

    public function cashier_details($id){                             

        $data['cashier_data'] = $this->data_fetcher->getUserData($id);                
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);       
    }
		
    // public function get_other_payments($slug){

    //     $sem = $this->data_fetcher->get_active_sem();
    //     $sdata['student'] = $this->data_fetcher->fetch_single_entry('tb_mas_users',$slug,'slug');
    //     $where = array('intStudentID' => $sdata['student']['intID'], 'intSYID' => $sem['intID']);
    //     $sdata['other_payments'] =  $this->data_fetcher->fetch_table('tb_mas_other_payments', array('dateIssued','desc'), null, $where);        
    //     $sdata['current_sem'] = $sem['intID'];
        
        
    //     $data['data'] = $sdata;
    //     $data['message'] = "Success";
    //     $data['success'] = true;
    //     echo json_encode($data);
    // }

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

