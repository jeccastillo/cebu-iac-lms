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
        
        $this->data['campus'] = $this->config->item('campus');
        $this->data["user"] = $this->session->all_userdata();
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Unity";
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
        $data['sem_year'] = $sem['strYearStart'];
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);
    }
    public function manualPayData($slug){
        $data['data'] = $this->data_fetcher->fetch_single_entry('tb_mas_users',$slug,'slug');        
        $data['cashier'] = $this->db->get_where('tb_mas_cashier',array('user_id'=>$this->data['user']['intID']))->first_row();
        $sem = $this->data_fetcher->get_active_sem();        
        $data['current_sem'] = $sem['intID'];
        $data['sem_year'] = $sem['strYearStart'];
        $data['message'] = "Success";
        $data['success'] = true;
        echo json_encode($data);
    }

    public function student_ledger($id,$sem = 0){

        $this->data['id'] = $id;        
        $this->data['sem'] = $sem;

        $this->load->view("common/header",$this->data);
        $this->load->view("student_ledger",$this->data);
        $this->load->view("common/footer",$this->data);

    }

    public function student_ledger_data($id,$sem){
                
        $where = array('student_id'=>$id);
        
        if($sem != 0)
            $where['syid'] = $sem;

        $data['ledger'] = $this->db->select('tb_mas_student_ledger.*, enumSem, strYearStart, strYearEnd, tb_mas_faculty.strFirstname, tb_mas_faculty.strLastname')        
                    ->from('tb_mas_student_ledger')
                    ->join('tb_mas_sy', 'tb_mas_student_ledger.syid = tb_mas_sy.intID')
                    ->join('tb_mas_faculty', 'tb_mas_student_ledger.added_by = tb_mas_faculty.intID','left')
                    ->where($where)        
                    ->get()
                    ->result_array();

        $data['student'] = $this->data_fetcher->getStudent($id);
        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $sem = $this->data_fetcher->get_active_sem();  
        $data['active_sem'] = $sem['intID'];

        echo json_encode($data);
    }
    
    public function submit_ledger_item(){
        $post =  $this->input->post();
        $post['added_by'] = $this->session->userdata('intID');
        $this->db->insert('tb_mas_student_ledger',$post);

        $data['success'] =  true;
        $data['message'] = "Successfully added to ledger";

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
        if(isset($post['registration_id'])){
            unset($data['payments']);
            unset($data['description']);
            unset($data['registration_id']);
            unset($data['student_id']);
            unset($data['total_amount']);            
            unset($data['or_number']);
            unset($data['installment']);
        }

        $sem = $this->data_fetcher->get_active_sem();  
        
        
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
        $current_or = $post['or_current'];
        if(isset($post['registration_id'])){
            unset($data['payments']);
            unset($data['description']);
            unset($data['registration_id']);
            unset($data['student_id']);
            unset($data['total_amount']);            
            unset($data['or_number']);            
            unset($data['installment']);
        }

        $sem = $this->data_fetcher->get_active_sem();  
        
        
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
            
            $ledger['student_id'] = $post['student_id'];
            $ledger['name'] = $post['description'];
            $ledger['amount'] = -1 * $post['total_amount'];
            $ledger['date'] = date("Y-m-d H:i:s");
            $ledger['syid'] = $sem['intID'];
            $ledger['or_number'] = $current_or;
            $this->data_poster->post_data('tb_mas_student_ledger',$ledger);            
            

            if($post['description'] == "Tuition Down Payment"){                
                $this->db
                    ->where(array('name'=>'tuition','syid'=>$sem['intID']))
                    ->update('tb_mas_student_ledger',array('amount'=>$post['installment']));
            }            

            if(substr( $post['description'], 0, 7 ) === "Tuition" && $post['payments'] == 0){
                $ret['message'] = "First Tuition Payment";
                $ret['send_notif'] = true;
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


    public function remove_from_ledger(){        
        
        $post = $this->input->post();        
        $amount =  -1 *  floatval($post['total_amount_due']);                
        
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

    public function payments($date = null, $other = 0){                        

        if($date == null)
            $date = date("Y-m-d");

        $sem = $this->data_fetcher->get_active_sem();        
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
        $this->data['opentree'] = "cashier";
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

            $ledger['student_id'] = $reg['intStudentID'];
            $ledger['name'] = $post['name'];
            $ledger['amount'] = -1 * $post['discount'];
            $ledger['date'] = date("Y-m-d H:i:s");
            $ledger['syid'] = $sem['intID'];
            $ledger['added_by'] =  $this->session->userdata('intID');
            $this->data_poster->post_data('tb_mas_student_ledger',$ledger);

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
            

            $ledger['student_id'] = $reg['intStudentID'];
            $ledger['name'] = "Removed Discount:".$discount['name'];
            $ledger['amount'] = $post['discount'];
            $ledger['date'] = date("Y-m-d H:i:s");
            $ledger['syid'] = $sem['intID'];
            $ledger['added_by'] =  $this->session->userdata('intID');
            $this->data_poster->post_data('tb_mas_student_ledger',$ledger);            

            $this->db->where(array('id'=>$post['id']))            
            ->delete('tb_mas_registration_discount');

            $data['message'] = "Successfully Removed Discount";
            $data['success'] = true;
        }
            


        echo json_encode($data);
    }

    public function other_payments(){                                     

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');
        
        if($role == 0 && $userlevel != 2)
            redirect(base_url()."unity");

        $this->data['page'] = "other_payments";
        $this->data['opentree'] = "cashier";
        $this->load->view("common/header",$this->data);
        $this->load->view("other_payments",$this->data);
        $this->load->view("common/footer",$this->data);        
    }

    public function cashier_data(){                             

        $data['cashiers'] = $this->data_fetcher->getCashiers();        
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

