<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends CI_Controller {

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
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Grading System";
        $this->load->library("email");	
        $this->load->helper("cms_form");
		$this->load->model("google_login");	
		$this->load->model("facebook_login");	
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
        $this->data['page'] = "messages";
	}
    
    public function index()
	{	
        
        if($this->faculty_logged_in())
            $this->view_messages();
        
        else
            redirect(base_url()."users/login");
        
        
	}
    public function get_hash($string)
    {
        echo pw_hash($string);
    }
    public function get_unhash($string){
        echo $string;
    }
    
    /* MESSAGING */
    
    public function compose_message($id = null)
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "compose_message";
            $this->data['opentree'] = "messages";
            
            
            $this->data['receiver'] = array();
            
            if($id!=null)
                $this->data['receiver'] = $this->data_fetcher->getUserData($id);
            
            //print_r($this->data['receiver']);
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/compose_message",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/compose_message_foot",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."/users/login");  
    }
    
    public function view_messages()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_messages";
            $this->data['opentree'] = "messages";
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/message_view",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/message_table_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."/users/login");  
    }
    
    public function sent_messages()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "sent_messages";
            $this->data['opentree'] = "messages";
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/sent_messages",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/sent_table_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."/users/login");  
    }
    
    public function send_new_message()
    {
        $message = $this->input->post();
        
        $users  = $message['user-message'];
        unset($message['user-message']);
        
        $message['dteDate'] = date("Y-m-d G:i:s");
        
        $message['intFacultyID'] = explode(",",$users);
        
        $this->send_system_message($message);
        
        redirect(base_url()."messages/view_messages");
    }
    
    public function view_message($id)
    {
        
        if($this->faculty_logged_in())
        {
            $this->data_poster->setMessageRead($id);
            redirect(base_url()."messages/message_view_re/".$id); 
            
           
            
        }
        else
            redirect(base_url()."/users/login");    
        
        
    }
    
    public function message_view_re($id)
    {
        if($this->faculty_logged_in())
        {
            $this->data['item'] = $this->data_fetcher->getMessage($id);
            $this->data['replies'] = $this->data_fetcher->getReplyThread($this->data['item']['intMessageID']);
            
            $this->data['opentree'] = "messages";
           
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/read_message",$this->data);
            $this->load->view("common/footer",$this->data);  
            $this->load->view("common/read_message_foot",$this->data);  
        }
        else
            redirect(base_url()."/users/login");  
            
    }
    
    private function send_system_message($post,$intThread=0)
    {   
        if($intThread!=0)
        {
            $post['intThread'] = $intThread;
        }
        $post['intFacultyIDSender'] = $this->session->userdata('intID');
        $this->data_poster->sendMessage($post);
    }
    
    
    public function trashMessage($idLabel="intID")
    {
        $data['message'] = "failed";
        $post = $this->input->post();
        $table = $post['table'];
        $this->data_poster->trashMessage($post['id']);
        $data['message'] = "success";
        
        echo json_encode($data);
    }
    
    public function deleteMessages($recover=1)
    {
        $post = $this->input->post();
        $this->data_poster->deleteMessages($post['ids']);
        
        echo json_encode(array("success"=>"1"));
        
    }
    
    public function trashMessages($recover=1)
    {
        $post = $this->input->post();
        $this->data_poster->trashMessages($post['ids'],$recover);
        
        echo json_encode(array("success"=>"1"));
        
    }
    

    
    public function forwardMessage()
    {
        $post = $this->input->post();
        //print_r($post);
        
        $users  = $post['user-message'];
        unset($post['user-message']);
        
       $users = explode(',',$users);
        foreach($users as $user)
        {
            $post['intFacultyID'] = $user;
            if(!$this->data_fetcher->messageExists($post['intMessageID'],$post['intFacultyID']))
                $this->data_poster->post_data('tb_mas_message_user',$post);
        }
       
        redirect(base_url()."messages/view_messages/");
    }
    
    public function markMessages($read)
    {
        $post = $this->input->post();
        $this->data_poster->setMessages($post['ids'],$read);
        
        echo json_encode(array("success"=>"1"));
        
    }
    
    public function mark_unread($id)
    {
        
        if($this->faculty_logged_in())
        {
          
            
            $this->data_poster->setMessageUnRead($id);
            redirect(base_url()."messages/view_messages");    
            
        }
        else
            redirect(base_url()."/users/login");    
        
        
    }
    
    public function view_trash()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "trash_messages";
            $this->data['opentree'] = "messages";
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/trashed_messages",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/trash_table_conf",$this->data); 
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."/users/login");  
    }
    
    public function post_reply()
    {
    
        $post = $this->input->post();
        
        
        $post['dteReplied'] = date("Y-m-d H:i:s");
        $post['intFacultyID'] = $this->session->userdata('intID');
        $this->data_poster->setMessageUnReadByMID($post['intMessageID']);
        
        if(!$this->data_fetcher->messageExists($post['intMessageID'],$post['intFacultyIDSender']))
                $this->data_poster->post_data('tb_mas_message_user',array("intMessageID"=>$post['intMessageID'],"intFacultyIDSender"=>$post['intFacultyIDSender'],"intFacultyID"=>$post['intFacultyIDSender']));
        
        unset($post['intFacultyIDSender']);
        $this->data_poster->post_data('tb_mas_reply_thread',$post);
        
        
        echo json_encode(array("success"=>"1"));
    }
    
    public function userToken()
    {
        $get = $this->input->get();
        # Perform the query
        $query = "SELECT intID, strFirstname, strLastname from tb_mas_faculty WHERE strFirstname LIKE '%%%".mysql_real_escape_string($get["q"])."%%' OR strLastname LIKE '%%%".mysql_real_escape_string($get["q"])."%%'  ORDER BY strLastname DESC LIMIT 10";
        $arr = array();
        $rs = $this->db->query($query);

        # Collect the results
        foreach ($rs->result() as $obj){
            $arr[] = array('id'=>$obj->intID,'name'=>$obj->strFirstname." ".$obj->strLastname);
        }

        # JSON-encode the response
        $json_response = json_encode($arr);

        # Optionally: Wrap the response in a callback function for JSONP cross-domain support
        if(isset($get["callback"]) && $get["callback"]) {
            $json_response = $get["callback"] . "(" . $json_response . ")";
        }

        # Return the response
        echo $json_response;

    }
    
    public function get_message_alert()
    {
        if($this->faculty_logged_in())
        {
        
        $messages = $this->data_fetcher->getMessages($this->session->userdata('intID'));
            
        for($i = 0; $i <count($messages);$i++)
        {
            $messages[$i]['dteDate'] = time_lapsed($messages[$i]['dteDate']);
        }
            
        $ret['messages'] = $messages;
            
        $ret['count'] = count($ret['messages']);
            
        $id = $this->session->userdata('intID');
            
        if(isset($id) && $id!="" && $id!=0)
            $this->db
				 ->where('intId',$id)
				 ->update('tb_mas_faculty',array('intIsOnline'=>date('Y-m-d H:i:s')));
            
        echo json_encode($ret);
        }
    }
    
    public function deleteItem($idLabel="intID")
    {
        $data['message'] = "failed";
        if($this->faculty_logged_in())
        {
        $post = $this->input->post();
        $table = $post['table'];
        $deleted = $this->data_poster->deleteItem($table,$post['id'],$idLabel);
            if($deleted)
                $data['message'] = "success";
        }
        
        echo json_encode($data);
    }
    
    /* END MESSAGING */
    
    
    public function faculty_logged_in()
    {
        if($this->session->userdata('faculty_logged'))
            return true;
        else
            return false;
    }
    
    public function student_logged_in()
    {
        if($this->session->userdata('student_logged'))
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
    
}