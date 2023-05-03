<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed

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
        $user = $this->db->get_where('tb_mas_users',array('strEmail'=>$post['email']))->first_row('array');

        if($user){
            
            $data['strGSuiteEmail'] = $post['token'];
            $this->db
             ->where('intID',$user['intID'])
             ->update('tb_mas_users',$data);

            $data['message'] = "Successfully saved token"; 
            $data['success'] = true;
        }
        else{
            $data['message'] = "Error email does not exist";
            $data['success'] = false;
        }
        
        echo json_encode($data);
             
    }

    public function view_active_programs(){
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs', null, null, array('enumEnabled'=>1));
        $ret = [];
        foreach($programs as $prog){
            
            $temp['id'] = $prog['intProgramID'];
            $temp['title'] = $prog['strProgramDescription'];
            $temp['type'] = $prog['type'];
            $temp['strMajor'] = $prog['strMajor'];
            $ret[] = $temp;
        }

        $data['data'] = $ret;

        echo json_encode($data);

    }

    public function student_data(){
        
        $post = $this->input->post();
        $ret['success'] = false;
        $user = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode')
                        ->from('tb_mas_users')
                        ->join('tb_mas_programs','tb_mas_users.intProgramID = tb_mas_programs.intProgramID')
                        ->where(array('strGSuiteEmail'=>$post['token']))
                        ->get()
                        ->first_row('array');
        if($user){            
            
            $registered = $this->db->select('tb_mas_registration.*, tb_mas_sy.enumSem, tb_mas_sy.strYearStart, tb_mas_sy.strYearEnd')
                    ->from('tb_mas_registration')
                    ->join('tb_mas_sy','tb_mas_registration.intAYID = tb_mas_sy.intID')
                    ->where('intStudentID',$user['intID'])
                    ->where('dteRegistered is NOT NULL', NULL, FALSE)
                    ->order_by('dteRegistered','desc')
                    ->get()
                    ->first_row('array');

            if($registered){
                $ret['data'] = array(
                    'first_name' => $user['strFirstname'],
                    'last_name' => $user['strLastname'],
                    'personal_email'=> $user['strEmail'],
                    'student_number'=> $user['strStudentNumber'],
                    'contact_number'=> $user['strMobileNumber'],
                    'course_id' => $user['intProgramID'],
                    'course_name'=>$user['strProgramCode'],                    
                    'last_term'=> $registered['enumSem']." Term",
                    'last_term_sy'=> $registered['strYearStart']."-".$registered['strYearEnd']                                  
                );

                $ret['success'] = true;
            }            
        }

        echo json_encode($ret);
    }
    


}