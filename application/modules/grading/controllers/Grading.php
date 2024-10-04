<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Grading extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');

        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2 && $userlevel != 6 && $userlevel != 4 && $userlevel != 3) 
		  redirect(base_url()."unity");
		
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
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "subjects";
    }
    
    public function add_grading()
    {
        if($this->is_admin() || $this->is_registrar())
        {
            $dpt = array(); 
            foreach($this->data['department_config'] as $dept)
                $dpt[$dept] = $dept;

            $this->data['lab_types'] = $this->data_fetcher->getLabTypesForDropdown();
            
            $this->data['dpt'] = $dpt;
            $this->data['page'] = "add_grading_system";
            $this->data['opentree'] = "grading";
            
           
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_grading",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("subject_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function edit_grading($id)
    {
        $this->data['page'] = "add_grading_system";
        $this->data['opentree'] = "grading";
                        
        $this->data['userlevel'] = $this->session->userdata('intUserLevel');
        $this->data['grading'] = $this->db->get_where('tb_mas_grading',array('id'=>$id))->first_row('array');
        $this->data['grading_items'] = $this->db
                                            ->where(array('grading_id'=>$id))
                                            ->order_by('value','ASC')
                                            ->get('tb_mas_grading_item')
                                            ->result_array();
        
        $this->data['subjects_selected'] = $this->db->where(array('grading_system_id'=>$id))
                                                ->order_by('strCode','ASC')
                                                ->get('tb_mas_subjects')
                                                ->result_array();
        
        $this->data['subjects_selected_midterm'] = $this->db->where(array('grading_system_id_midterm'=>$id))
                                                            ->order_by('strCode','ASC')
                                                            ->get('tb_mas_subjects')                                                            
                                                            ->result_array();        
        
        $this->data['subjects_not_selected'] = $this->db->where(array('grading_system_id !='=>$id))
                                                        ->or_where('grading_system_id',NULL)
                                                        ->order_by('strCode','ASC')
                                                        ->get('tb_mas_subjects')
                                                        ->result_array();

        $this->data['subjects_not_selected_midterm'] = $this->db->where(array('grading_system_id_midterm !='=>$id))
                                                        ->or_where('grading_system_id_midterm',NULL)
                                                        ->order_by('strCode','ASC')
                                                        ->get('tb_mas_subjects')
                                                        ->result_array();        
        
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/edit_grading",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/edit_grading_conf",$this->data); 
        // print_r($this->data['classlists']);
                
        
    }

    public function add_selected($type = "final"){
        $post = $this->input->post();
        
        foreach($post['subjects'] as $subject){
            if($type == "final")
                $data = array(
                    "grading_system_id"=>$post['id'],                
                );

            else
                $data = array(
                    "grading_system_id_midterm"=>$post['id'],                
                );
            
            $this->data_poster->post_data('tb_mas_subjects',$data,$subject);

        }

        redirect(base_url()."grading/edit_grading/".$post['id']);
    }

    public function delete_grading_system(){
       
    
        $post = $this->input->post();
        $gs = $this->db->where('grading_system_id',$post['id'])
                        ->or_where('grading_system_id_midterm',$post['id'])
                        ->get('tb_mas_subjects')
                        ->result_array();
        if(empty($gs)){
            $for_deletion = $this->db->get_where('tb_mas_grading',array('id'=>$post['id']))->first_row('array');
            
            $this->db->where('id',$post['id'])
                    ->delete('tb_mas_grading');
            $this->db->where('grading_id',$post['id'])
                    ->delete('tb_mas_grading_item');

            $data['message'] = "success";
            $this->data_poster->log_action('Grading System','Deleted a Grading System | Name:'.$for_deletion['name'],'red');
        }
        else{
            $data['message'] = "Grading System is in use can not delete";
        }
        
    
        echo json_encode($data);
    
    }
    public function update_details(){
        if($this->is_admin() || $this->is_registrar()){
            $post = $this->input->post();
            $this->db->where(array('id'=>$post['id']))
                     ->update('tb_mas_grading',$post);

            redirect(base_url()."grading/edit_grading/".$post['id']);
        }
    }
    
    public function submit_grading()
    {
        if($this->is_admin() || $this->is_registrar()){
            $post = $this->input->post();
            if(isset($post['id'])){
                //print_r($post);
                for($i = 0; $i < count($post['item']); $i++){
                    $data = array(
                        "grading_id"=>$post['id'],
                        "value"=> $post['item'][$i],
                        "remarks"=> $post['remarks'][$i],
                    );

                    $this->data_poster->post_data('tb_mas_grading_item',$data);

                }
                $this->data_poster->log_action('Grading System','Updated Grading System with id '.$post['id'],'yellow');
                $insert_id = $post['id'];
            }
            else{

                //print_r($post);
                $this->data_poster->log_action('Grading System','Added a new Grading System ','yellow');
                $this->data_poster->post_data('tb_mas_grading',$post);
                $insert_id = $this->db->insert_id();
            }
            redirect(base_url()."grading/edit_grading/".$insert_id);
            
        }
    }
    
    
    public function view_all_grading()
    {
        
        $this->data['page'] = "view_grading_systems";
        $this->data['opentree'] = "grading";
        //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/grading_view",$this->data);
        $this->load->view("common/footer",$this->data); 
        $this->load->view("common/grading_conf",$this->data); 
        //print_r($this->data['classlist']);
        
    }

    public function term_override($sem = 0)
    {
        
        $this->data['sem'] = $sem;
        $this->data['page'] = "term_override";
        $this->data['opentree'] = "grading";
        //$this->data['subjects'] = $this->data_fetcher->fetch_table('tb_mas_subjects',array('strCode','asc'));
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/term_override",$this->data);
        $this->load->view("common/footer",$this->data);         
        //print_r($this->data['classlist']);
        
    }

    public function term_override_data($sem){
        if($sem != 0)
            $ret['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        else
            $ret['active_sem'] = $this->data_fetcher->get_active_sem();

        $ret['sy'] = $this->db->get('tb_mas_sy')->result_array();
        $ret['grading_systems'] = $this->db->get('tb_mas_grading')->result_array();
        $ret['subjects'] = $this->db->get('tb_mas_subjects')->result_array();
        $ret['overrides'] = $this->db->select('tb_mas_sy_grading_override.*,tb_mas_grading.name,tb_mas_subjects.strCode')
                                     ->join('tb_mas_grading', 'tb_mas_sy_grading_override.grading_system_id = tb_mas_grading.id')
                                     ->join('tb_mas_subjects', 'tb_mas_sy_grading_override.subject_id = tb_mas_subjects.intID')
                                     ->where(array('syid'=>$ret['active_sem']['intID']))
                                     ->get('tb_mas_sy_grading_override')
                                     ->result_array();
        echo json_encode($ret);

    }
    
    public function submit_override()
    {
        $post =  $this->input->post();
        $this->db->insert('tb_mas_sy_grading_override',$post);
        $data['sucess'] = true;
        $data['message'] = "Successfully added";
        echo json_encode($data);
    }

    public function delete_override(){
        $post =  $this->input->post();
        
        $this->db->where('id',$post['id'])
                ->delete('tb_mas_sy_grading_override');

        $data['sucess'] = true;
        $data['message'] = "Successfully deleted";
        echo json_encode($data);
    }
    
    
    public function delete_grading_item()
    {
        if($this->is_admin() || $this->is_registrar()){
            $post = $this->input->post();
            
            $this->db
			->where('id',$post['id'])
			->delete('tb_mas_grading_item');


            $data['message'] = "deleted";
            echo json_encode($data);
        }
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
    
    public function is_admissions()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 5)
            return true;
        else
            return false;
        
    }


}