<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

	
class Finance extends CI_Controller {

	
    function __construct() {
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
        
                
        
        $this->data['page'] = "finance";
        
        //$this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        //$this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
		

    }

    public function manualPayData($slug){
        $data['data'] = $this->data_fetcher->fetch_single_entry('tb_mas_users',$slug,'slug');        
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

    public function payments_no_or(){                             

        $this->data['page'] = "no_or";
        $this->load->view("common/header",$this->data);
        $this->load->view("no_or_list",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/list_conf",$this->data); 
    }

    public function cashier(){                             

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

