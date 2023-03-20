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
                
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		

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

    public function next_or(){
        $post = $this->input->post();
        $data = $post;
        unset($data['payments']);
        
        $cashier = $this->db->get_where('tb_mas_cashier',array('intID'=>$data['intID']))->row();
        
        if($data['or_current'] >= $cashier->or_end)
            $data['or_current'] = null;
        else
            $data['or_current'] += 1;

        $this->db
            ->where('intID',$data['intID'])
            ->update('tb_mas_cashier',$data);

        $ret['message'] = "success";
        $ret['test'] = $post;
        
    }
    public function update_cashier(){
        $post = $this->input->post();                     
        $valid = true; 
        $data['reload'] = false;
        
        $type = $post['type'];
        unset($post['type']);
        $cashier = $this->db->get_where('tb_mas_cashier',array('intID'=>$post['intID']))->row();
        
        $cashier_validation = $this->db->get_where('tb_mas_cashier',array('intID !='=>$post['intID'],'or_start <='=>$post[$type],'or_end >=' => $post[$type]))->row();        
        if(!$cashier_validation){
            if($type == "or_start" && $cashier->or_end)                
                    $cashier_validation = $this->db->get_where('tb_mas_cashier',array('intID !='=>$post['intID'],'or_start >='=>$post['or_start'],'or_end <=' => $cashier->or_end))->row();
            elseif($cashier->or_start)
                    $cashier_validation = $this->db->get_where('tb_mas_cashier',array('intID !='=>$post['intID'],'or_start >='=>$cashier->or_start,'or_end <=' => $post['or_end']))->row();
        }
        

        if($cashier_validation)
        {
            $data['message'] = "Conflict with one of the cashiers";
            $data['success'] = false;
        }
        else{            
            if($type == "or_start"){

                if($cashier->or_end && $cashier->or_end < $post["or_start"] && $cashier->or_end != null){
                    $post['or_end'] = $post["or_start"];                    
                }
                $post['or_current'] = $post['or_start'];
                $data['reload'] = true;
                
            }
            if($type == "or_end")
                if($cashier->or_start && $cashier->or_start > $post["or_end"] && $cashier->or_start != null)
                        $valid = false;
            
            if($valid){
                $this->db
                        ->where('intID',$post['intID'])
                        ->update('tb_mas_cashier',$post);

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
        $this->load->view("common/header",$this->data);
        $this->load->view("no_or_list",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/list_conf",$this->data); 
    }

    public function payments($date = null){                        

        if($date == null)
            $date = date("Y-m-d");

        $sem = $this->data_fetcher->get_active_sem();        
        $this->data['current_sem'] = $sem['intID'];
        $this->data['date'] = $date;

        $this->data['page'] = "transactions";
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

        $this->data['page'] = "add_cashier";
        $this->data['opentree'] = "cashier";
        $this->load->view("common/header",$this->data);
        $this->load->view("cashier",$this->data);
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

