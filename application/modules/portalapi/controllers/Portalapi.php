<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PortalApi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->config->load('themes');		
		$theme = $this->config->item('unity');        

		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
		
        $settings = $this->data_fetcher->fetch_table('su-tb_sys_settings');
		foreach($settings as $setting)
		{
			$this->settings[$setting['strSettingName']] = $setting['strSettingValue'];
		}
        
        $this->data['title'] = "iACADEMY";
        $this->load->library("email");	
        $this->load->helper("cms_form");	
		$this->load->model("user_model");
        $this->config->load('courses');
       
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
    }
    
    public function save_token()
    {
       
        $post = $this->input->post();
        $user = $this->db->get_where('tb_mas_users',array('strEmail'=>$post['email']))->first();

        if($user){
            
            $data['strGSuiteEmail'] = $post['token'];
            $this->db
             ->where('intID',$user->intD)
             ->update('tb_mas_message_user',$data);

            $data['message'] = "Successfully saved token"; 
            $data['success'] = true;
        }
        else{
            $data['message'] = "Error email does not exist";
            $data['success'] = false;
        }
        
             
    }
    


}