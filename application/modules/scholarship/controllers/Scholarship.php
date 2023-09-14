<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Scholarship extends CI_Controller {

	
    function __construct() {
        parent::__construct();
        
        if(!$this->is_osas() && !$this->is_super_admin())
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

    public function assign_scholarship_data($student,$sem){

        $ret['scholarships'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'scholarship'))->result_array();
        $ret['discounts'] = $this->db->get_where('tb_mas_scholarships',array('status'=>'active','deduction_type'=>'discount'))->result_array();
        $ret['terms'] = $this->db->get('tb_mas_sy')->result_array();
        $ret['student'] = $this->db->get_where('tb_mas_users',array('intID'=>$student))->first_row('array');

        $ret['student_scholarships'] = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.deduction_type,tb_mas_scholarships.name,tb_mas_scholarships.description')
                                    ->where(array('syid'=>$sem,'student_id'=>$student,'deduction_type'=>'scholarship'))
                                    ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                                    ->get('tb_mas_student_discount')
                                     ->result_array();

        $ret['student_discounts'] = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.deduction_type,tb_mas_scholarships.name,tb_mas_scholarships.description')
                                     ->where(array('syid'=>$sem,'student_id'=>$student,'deduction_type'=>'discount'))
                                     ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                                     ->get('tb_mas_student_discount')
                                      ->result_array();                                     

        echo json_encode($ret);

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
        $this->db->insert('tb_mas_student_discount',$post);
        $scholarship = $this->db->get_where('tb_mas_scholarships',array('intID'=>$post['discount_id']))->first_row('array');
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$post['student_id']))->first_row('array');
        $this->data_poster->log_action('Scholarships','Added a Scholarship '.$scholarship['name'].' for student '.$student['strLastname'].' '.$student['strFirstname'],'green');

        $data['success'] = "success";
        $data['message'] = "Added Successfully";

        echo json_encode($data);
    }

    public function update_scholarship_status(){
        $post = $this->input->post();
        
        $st_scholarship = $this->db->get_where('tb_mas_student_discount',array('id'=>$post['id']))->first_row('array');
        $scholarship = $this->db->get_where('tb_mas_scholarships',array('intID'=>$st_scholarship['discount_id']))->first_row('array');
        $student = $this->db->get_where('tb_mas_users',array('intID'=>$st_scholarship['student_id']))->first_row('array');
        
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
            if($post['status'] ==  "applied"){
                //Add to Ledger            
                $ledger['student_id'] = $student['intID'];
                $ledger['name'] = "Scholarship/Discount";
                $ledger['amount'] = -1 * $deductions;                
                $ledger['date'] = date("Y-m-d H:i:s");
                $ledger['syid'] = $st_scholarship['syid'];
                $ledger['remarks'] = "Scholarship/Discount Deduction -OSAS Admin";
                $ledger['scholarship_id'] = $scholarship['intID'];
                $this->data_poster->post_data('tb_mas_student_ledger',$ledger);
            }            
            else{
                //remove from Ledger
                $this->db->where(array('scholarship_id'=>$scholarship['intID'],'syid'=>$st_scholarship['syid'],'student_id'=>$student['intID']))
                         ->delete('tb_mas_student_ledger');
            }
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

