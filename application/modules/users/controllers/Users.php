<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// require_once('src/facebook.php');
	
class Users extends CI_Controller {

	
    function __construct() {

        parent::__construct();
		/*--------------THEMES-----------------------*/
		$this->config->load('themes');
		$theme = $this->config->item('users');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
			
		$this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
		$this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";		
		$this->theme = $theme;
		$this->data['logged_in'] = $this->session->userdata('user_logged');
		//$this->data['ptmenu'] = $this->load->view('common/pt_menu',$this->data,true);
		//------------------------------------------------------------------------------
        $this->load->library("email");		
		$this->load->model("google_login");	
		$this->load->model("facebook_login");	
		$this->load->model("user_model");
		$this->data['title'] = "Login/Signup";
		
    }
	
	public function index()
	{
	
	}

    // Login and Authentication
    public function auth() {

        if (isset($_POST)) {
            //print_r($this->input->post());  
            $table = 'tb_mas_faculty';
            switch($this->input->post('loginType'))
            {
                case 'faculty':
                $table = 'tb_mas_faculty';
                break;
                case 'student':
                $table = 'tb_mas_users';
                break;
            }
            
            $authentication = $this->user_model->authenticate($this->input->post('strUser'),$this->input->post('strPass'),$table);
           
            // Create User Object If Logged In Correctly
        } else {

            $authentication = 0;
        }

        // This can be simplified a LOOOT!
        if ($authentication == 1) {
            $data['success'] = true;
            $data['message'] =  "Success";
        } elseif($authentication == 2) {
            $data['success'] = false;
            $data['message'] = "Too many failed login attempts for this user account locked please contact SMS admin";            
        }
        else{
            $data['success'] = false;
            $data['message'] = "Wrong username or password";
        }
        
        echo json_encode($data);
    }
    
    public function auth_student() {

        if (isset($_POST)) {
            //print_r($this->input->post());  
            
          
            $table = 'tb_mas_users';
            
            if($this->input->post('strPass') == "" || $this->input->post('strPass') == null)
                $authentication =  false;
            else
                $authentication = $this->user_model->authenticate_student($this->input->post('strUser'),$this->input->post('strPass'),$table);
           
            // Create User Object If Logged In Correctly
        } else {

            $authentication = false;
        }

        // This can be simplified a LOOOT!
        if ($authentication) {
            $data['success'] = true;
            $data['message'] =  "Success";
            $as = $this->data_fetcher->get_active_sem();
            $this->session->set_userdata('active_sem',$as['intID']);
        } else {
            $data['success'] = false;
            $data['message'] = "Invalid Login";
        }
        
        echo json_encode($data);
    }

    public function logout() {
        
        $data = array('intIsOnline'=>'00:00:00');
        
        $this->db
             ->where('intID',$this->session->userdata('intID'))
             ->update('tb_mas_faculty',$data);
        
        $this->session->sess_destroy(); 
        $this->load->library('user_agent');
        
        redirect(base_url()."users/login");
    }
    
    public function logout_student() {

        $this->session->sess_destroy();
        $this->load->library('user_agent');
        redirect(base_url()."portal");
    }

    function register() {        
        $post = $this->input->post();
        $result = $this->user_model->register_user($post);
        $response['success'] = $result;

        if ($result == 1)
            $response['message'] = "Your username has been registered check your email for confirmation";
        else if ($result == 0)
            $response['message'] = "The email address you entered may already be in use";
        else if($result == 2)
            $response['message'] = "The username you entered may already be in use";
        else
            $response['message'] = "Something went wrong";
        echo json_encode($response);
    }

    function forgot() {
        $email = $this->input->post("email");        

        $data = array(
            'strEmail' => $email
        );

        $result = $this->user_model->create_reset_request($data);

        // Check if email exists
        if ($result) {

            echo "1";
        } else {

            echo "0";
        }
    }

    function password_reset($hash) {        

        $data['hash'] = $hash;

        if ($_POST) {

            $data = array(
                'hash' => $hash,
                'password' => $this->input->post('password')
            );

            $this->user_model->reset_password($data);						
            $this->data['message'] = "Your password has been reset!.";
        }
		
		
		$this->data['hash'] = $data['hash'];
        $this->data['title'] = "Pinoytuner - Reset Your Password ";

        //assigns all userdata from session if exists
        foreach ($this->session->userdata as $key => $value) {
            $data[$key] = $value;
        }
				

        $this->load->view('common/header', $this->data);		
        $this->load->view('password_reset', $this->data);
		$this->load->view('common/footer',$this->data);			
        
    }

    function confirm_email($hash) {        
        $this->user_model->validate_hash($hash);
    }

    function google_signin() {        

        if (isset($_GET['openid_sig'])) {

            $user_data = array(
                'strEmail' => $_GET['openid_ext1_value_email'],
                'strFirstname' => $_GET['openid_ext1_value_firstname'],
                'strLastname' => $_GET['openid_ext1_value_lastname'],
				'strUsername' => $_GET['openid_ext1_value_email']
            );

            $result = $this->user_model->register_user($user_data);


            if ($result == 1) {
                // New User
                // Bypass reset confirm email, reset pass etc
                $user_data['strConfirmed'] = 1;
                $user_data['strReset'] = 1;
                $user_data['strUsername'] = $user_data['strEmail'];
            } else {
                // User Exists
                // Bypass reset confirm email, reset pass etc
                $user_data['strConfirmed'] = 1;
                $user_data['strReset'] = 1;	
				$this->data['redirect'] = base_url();
				$this->load->view('refresh_parent',$this->data);	
            }
            
            $this->user_model->update_user($user_data['strEmail'], $user_data);
            $this->user_model->authenticate($user_data['strEmail'],NULL);			

        } else {

            redirect(base_url() . "users/google_signup_fail");
        }
    }

    function google_signup_fail() {        

        $data['page_title'] = "Development: Welcome to the Pinoytuner Online Store - Login with Google";
        $data['google_login'] = $this->google_login->google_signin();

        $this->load->view('common/header', $data);
        $this->load->view('templates/google_signup_fail');
        $this->load->view('common/header', $data);
    }
	
	public function fb_login()
	{
		$this->fb_connect();
		$this->data['redirect'] = $this->session->flashdata('referer'); 
		$this->load->view('refresh_parent',$this->data);	
	}
	
	public function fb_connect()
	{
		$this->load->model('data_fetcher');
		$this->load->model('data_poster');
		$this->config->load('fbconfig');
		$app_id = $this->config->item('app_id');
		$app_secret = $this->config->item('app_secret');
			
		$facebook = new Facebook(array(
			  'appId'  => $app_id,
			  'secret' => $app_secret
			  
		));
		
		$user = $facebook->getUser();
		$facebook->setExtendedAccessToken();
		
		if($user) {
		  try{
				// Proceed knowing you have a logged in user who's authenticated.
				$user = $facebook->api('/me');
				$params = array( 'next' => base_url().'users/logoutfb' );
				$logouturl = $facebook->getLogoutUrl($params); // $params is optional. 										
				$this->session->set_userdata('logouturl',$logouturl);
				$this->session->set_userdata('getAppID',$facebook->getAppID());;
				if($user['id']!=NULL){
					$salt = 'c9s1';
					$pass = substr(md5(uniqid()), 0, 8).$salt;
					strtoupper($user['gender'][0]);
					$userdata = Array(
									  'strFirstname' => $user['first_name'],
									  'strLastname' => $user['last_name'],
									  'strUsername' => $user['first_name'].$user['last_name'],									  		
									  'dteCreated' => date("Y-m-d H:i:s"),
                                      'strUsername'=>$user['first_name'].'.'.$user['last_name']
					);
                    if(!isset($user['email']))
                        $userdata['strEmail'] = $user['id'].'@facebook.com';
                    else
                    {
                        if($user['email'] == NULL)
                            $userdata['strEmail'] = $user['id'].'@facebook.com';
                        else
                            $userdata['strEmail'] = $user['email'];
                    }
					$test = current($this->data_fetcher->fetch_table('tb_mas_users',null,null,Array('strEmail' => $userdata['strEmail'])));
					if(isset($test) && !empty($test))
					{
					
					}else
					{
						$this->data_poster->post_data('tb_mas_users',$userdata,null);
					}
					$userdata = current($this->data_fetcher->fetch_table('tb_mas_users',null,null,Array('strEmail' => $userdata['strEmail'])));
                   
        
					foreach($userdata as $key=>$value)
					{
						$this->session->set_userdata($key,$value);
					}
					$this->session->set_userdata('user_logged',1);					
					
				}					
			}catch (FacebookApiException $e) {
			  error_log($e);
			  $user = null;
		  }
		}
		
		if (!$user) {
			
		}
		
	}
	
	function update_mobile($id,$mobile)
	{
		$data = array('mobile_number'=>$mobile);
		$this->db->where('user_id',$id);
		$this->db->update('users',$data);
	}
	
	function login() {        		
		
        $this->load->library('user_agent');
        $this->data['referer'] = $this->agent->referrer();
        $this->session->set_flashdata('referer',$this->data['referer']);
		/*get facebook login link*/	
		// $this->config->load('fbconfig');
		$app_id = $this->config->item('app_id');
		$app_secret = $this->config->item('app_secret');
		// $facebook = new Facebook(array(
		// 	  'appId'  => $app_id,
		// 	  'secret' => $app_secret
			  
		// ));
		$params = array(
			'scope' => 'user_status,publish_stream,user_photos,email,offline_access,user_photos,user_birthday,user_location,friends_likes,read_stream',
			'redirect_uri'=>site_url('users/fb_login/')
		);
		// $this->data['fb_login_link'] = $facebook->getLoginUrl($params);
		/*get facebook login link end*/       
		$this->load->view('common/header',$this->data);
        $this->load->view('login_form',$this->data);        
		$this->load->view('common/footer',$this->data);
    }
    
    function student_login() {        		
		
        $this->load->library('user_agent');
        $this->data['referer'] = $this->agent->referrer();
        $this->session->set_flashdata('referer',$this->data['referer']);
		/*get facebook login link*/	
		$this->config->load('fbconfig');
		$app_id = $this->config->item('app_id');
		$app_secret = $this->config->item('app_secret');
		
		$params = array(
			'scope' => 'user_status,publish_stream,user_photos,email,offline_access,user_photos,user_birthday,user_location,friends_likes,read_stream',
			'redirect_uri'=>site_url('users/fb_login/')
		);		
		/*get facebook login link end*/       
		$this->load->view('common/header',$this->data);
        $this->load->view('login_form_student',$this->data);        
		$this->load->view('common/footer',$this->data);
    }
	
	function forgot_pass() {         
        $this->data['title'] = "Forgot Password";
        $this->load->view('common/header',$this->data);        
		$this->load->view('login_header_body',$this->data);
        $this->load->view('forgot');  
		$this->load->view('common/footer');	
		$this->load->view('signup_js');
    }

    function signup() {      
		$this->load->view('login_header_body',$this->data);
        $this->load->view('signup',$this->data);
		$this->load->view('common/footer');	        
		$this->load->view('signup_js');
    }

}

?>