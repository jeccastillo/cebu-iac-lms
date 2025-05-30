<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');

        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2) 
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
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "subjects";
    }
    
   
    public function add_group($id = 0){

        $this->data['id'] = $id;        
        $this->data['opentree'] = "admin";
        $this->data['page'] = "group";              
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/group",$this->data);
        $this->load->view("common/footer",$this->data);
        
        

    }

    public function view_all_groups(){
          
        $this->data['opentree'] = "admin";
        $this->data['page'] = "view_groups";              
        $this->load->view("common/header",$this->data);
        $this->load->view("admin/group_view",$this->data);
        $this->load->view("common/footer",$this->data);                
    }

    public function group_view_data(){
        $ret['user_groups'] = $this->db->get('tb_mas_user_group')->result_array();
        echo json_encode($ret);
    }

    public function add_function(){
        $post = $this->input->post();
        if($this->db->insert('tb_mas_user_group_function',$post)){
            $data['success'] = true;
            $data['message'] = "Successfully added function";
        }
        else{
            $data['success'] = false;
            $data['message'] = "Oops something went wrong.";
        }

        echo json_encode($data);

    }

    public function group_data($id){                
        $ret['group'] = $this->db->get_where('tb_mas_user_group',array('id' => $id))->first_row();
        
        $fns = $this->db->select('tb_mas_user_group_function.*,tb_mas_user_group_access.rw')
        ->from('tb_mas_user_group_function')
        ->join('tb_mas_user_group_access','tb_mas_user_group_access.function_id = tb_mas_user_group_function.id AND AND tb_mas_user_group_access.group_id = '.$id,'left')        
        ->get()
        ->result_array();

        $ret['functions'] = [];
        foreach($fns as $fn){
            if($fn['rw'] == 1)
            {
                $fn['read'] = 1;
                $fn['write'] = 0;
            }
            elseif($fn['rw'] == 2){
                $fn['read'] = 0;
                $fn['write'] = 1;
            }
            elseif($fn['rw'] == 3){
                $fn['read'] = 1;
                $fn['write'] = 1;
            }
            else{
                $fn['read'] = 0;
                $fn['write'] = 0;
            }

            $ret['functions'][] = $fn;
        }

        $bucket = "SELECT tb_mas_faculty.* FROM tb_mas_faculty WHERE intID NOT IN (SELECT user_id from tb_mas_user_access WHERE group_id = ".$id.") AND intID != 999 ORDER BY strLastname ASC"; 
        
        $ret['faculty'] = $this->db
             ->query($bucket)
             ->result_array();

        $ret['group_users'] = $this->db->select('tb_mas_faculty.*,tb_mas_user_access.id as uaid')
                                       ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_user_access.user_id','left')
                                       ->where(array('group_id' => $id))
                                       ->get('tb_mas_user_access')
                                       ->result_array();
        echo json_encode($ret);
    }

    public function submit_user(){
        $post = $this->input->post();
        $this->db->insert('tb_mas_user_access',$post);

        $data['success'] = true;
        echo json_encode($data);
    }

    public function delete_user(){
        $post = $this->input->post();
        $this->db
            ->where(array('id'=>$post['id']))
            ->delete('tb_mas_user_access');

        $data['success'] = true;
        echo json_encode($data);
    }

    public function submit_group(){
        $post = $this->input->post();      
        $group_functions = json_decode($post['group_functions']);
        unset($post['group_functions']);    
        
        if($post['id'] == 0)
            if($this->db->insert('tb_mas_user_group',$post)){
                $data['success'] = true;
                $data['message'] = "Successfully Added Group";
                $data['group_id'] = $this->db->insert_id();
            }
            else{
                $data['success'] = false;
                $data['message'] = "Failed to Add";
            }
        else
            if($this->db
                    ->where('id',$post['id'])
                    ->update('tb_mas_user_group',$post)){
                foreach($group_functions as $gf){
                    if($gf->read || $gf->write){
                        $group_access = $this->db
                        ->get_where('tb_mas_user_group_access',array('group_id'=>$post['id'],'function_id'=>$gf->id))
                        ->first_row();

                        if($gf->read && $gf->write)
                            $rw = 3;                
                        elseif($gf->read)
                            $rw = 1;
                        elseif($gf->write)
                            $rw = 2;
        
                        $data = array(
                            'group_id' => $post['id'],
                            'function_id' => $gf->id,
                            'rw' => $rw
                        );
        
                        if($group_access)
                            $this->db->where('id',$group_access->id)->update('tb_mas_user_group_access',$data);
                        else
                            $this->db->insert('tb_mas_user_group_access',$data);
                    }
                }
                $data['success'] = true;
                $data['message'] = "Successfully Updated Group";
                $data['group_id'] = $post['id'];
            }
            else{
                $data['success'] = false;
                $data['message'] = "Failed to Update";
            }

        echo json_encode($data);
    }
    
    public function is_super_admin()
    {
         $admin = $this->session->userdata('intUserLevel');
        if($admin == 2)
            return true;
        else
            return false;
    }
    


}