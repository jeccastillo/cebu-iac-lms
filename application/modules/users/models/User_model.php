<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class User_model extends CI_Model {

		function __construct() 
		{   
            //$this->load->model('customers');       
		}
		
		public function authenticate($username,$password,$table)
		{
                       
			$user = $this->db->get_where($table,array('strUsername'=>$username,'isActive'=>1))->result_array();
			
			$user = current($user);
            
			if(empty($user))
			{
				return false;
			}
			else
			{			
				$auth_data = $this->db->get_where($table, array('strUsername'=>$username), 1)->first_row();
				if($auth_data->login_attempts >= 3){
					return false;
				}

				//if($user['strCMSUserPassword'] == md5($password))
				if(password_verify($password,$user['strPass']))
				{		
                    
                    $data = array('intIsOnline'=>date("H:i:s"));
					$data['login_attempts'] = 0;
        
                    $this->db
                         ->where('intID',$user['intID'])
                         ->update('tb_mas_faculty',$data);
                    
					foreach($auth_data as $key => $value):
						$this->session->set_userdata($key, $value);			
					endforeach;
                    if($table == "tb_mas_users")					
                        $this->session->set_userdata('student_logged', true);	
                    else
                        $this->session->set_userdata('faculty_logged', true);    
					
					return true;									
				}
				else
				{
					$data['login_attempts'] = $auth_data->login_attempts + 1;
					$this->db
                         ->where('intID',$user['intID'])
                         ->update('tb_mas_faculty',$data);

					return false;
				}
			}
			
			
		
		}
        
        public function authenticate_student($username,$password,$table)
		{
           
            $sql = "SELECT * FROM ".$table." WHERE  REPLACE(strStudentNumber, '-', '') = '".$username."'";
			
			$user = $this->db->query($sql)->result_array();
			
			
            
			if(empty($user))
			{
				$sql = "SELECT * FROM ".$table." WHERE  strEmail = '".$username."'";
				$user = $this->db->query($sql)->result_array();
				if(empty($user))
					return false;
			}
			
			$user = current($user);

			//$sql = "SELECT * FROM ".$table." WHERE  REPLACE(strStudentNumber, '-', '') = '".$username."'";
			$auth_data = $this->db->query($sql)->first_row();				
			//$auth_data = $this->db->get_where($table, array('strStudentNumber'=>$username), 1)->first_row();
			//if($user['strCMSUserPassword'] == md5($password))
			if(password_verify($password,$user['strPass']))
			{											
				foreach($auth_data as $key => $value):
					$this->session->set_userdata($key, $value);			
				endforeach;
							
				$this->session->set_userdata('student_logged', true);	
				
				
				return true;									
			}
			else
			{
				return false;
			}						
			
		
		}

		function register_user($data)
		{
			$email_check = array('strEmail'=>$data['strEmail']);
			$auth_data = $this->db->get_where('tb_mas_users', $email_check, 1)->first_row();			 
			if(isset($auth_data->strEmail))
			{
				return 0;
			}
			else
			{
				$uname_check = array('strUsername'=>$data['strUsername']);
				$auth_data = $this->db->get_where('tb_mas_users', $uname_check, 1)->first_row();				 
				if(isset($auth_data->strUsername))
				{
					return 2;
				}
				else
				{

					$data['dteCreated'] = date("Y-m-d"); //set date created					 
					$data['strConfirmed'] = $this->generate_hash();
					if(isset($data['strPass'])){
						$data['strPass'] = pw_hash($data['strPass']); //encrypt password
					}

					if($this->send_confirmation_email($data['strEmail'],$data['strConfirmed']))
                    {
						$this->db->insert('tb_mas_users', $data);
                        return 1;
                    }
					else
                    {
						echo $this->email->print_debugger();
                        return 3;
                    }	
					

				}
			}
		}
		
		function register_user_fb($data)
		{
			$email_check = array('strEmail'=>$data['strEmail']);
			$query = $this->db->get_where('tb_mas_users', $email_check, 1);
			$auth_data = $query->result_array();
			if(isset($auth_data['strEmail']))
			{
				return 0;
			}
			else
			{

					$data['dteCreated'] = date("Y-m-d"); //set date created					 
					$data['strConfirmed'] = $this->generate_hash();
					if(isset($data['password'])){
						$data['password'] = pw_hash($data['password']); //encrypt password
					}

					
						$this->db->insert('tb_mas_users', $data);
						
					return 1;

			}
		}
		

        function fetch_customer_data($user_id)
        {   
            //Check if customer data exists
            $customer = $this->customers->fetch_customer($user_id);
            if(isset($customer[0]['user_id'])){
                foreach($customer[0] as $key => $value)
                {   
                    $this->session->set_userdata($key, $value);
                }
                //$this->session->set_userdata('customer', $customer_array);
            }           

            return;
            
        }
				
	function generate_hash()
        {
	        $result = "";
	        $charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
	        for($p = 0; $p<15; $p++)
		    $result .= $charPool[mt_rand(0,strlen($charPool)-1)];
	        return sha1(md5(sha1($result)));
        }
	function generate_simple_hash()
        {
	        $result = "";
	        $charPool = '0123456789';
	        for($p = 0; $p<8; $p++)
		    $result .= $charPool[mt_rand(0,strlen($charPool)-1)];
		
	        return $result;
        }
        
        function validate_hash($hash)
        {
            $where = array('strConfirmed'=>$hash);
            $auth_data = $this->db->get_where('tb_mas_users',$where,1)->first_row();
			
	        if(isset($auth_data->strUsername))
	        {
	            $data = array('strConfirmed'=>'1');
	            $this->db->where('strConfirmed',$hash);
	            $this->db->update('tb_mas_users',$data);
                foreach($auth_data as $key => $value):
					$this->session->set_userdata($key, $value);			
				endforeach;
				$this->session->set_userdata('user_logged', true);
				header('location:'.base_url());
	        }
	        else
	        {
    			header('location:'.base_url());
	        }		
        
        }
		
		function reset_password($data)
		{
			$where = array(
				'strReset' => $data['hash']
			);
			
			$update = array(
				'strReset' => '',
				'strPass' => pw_hash($data['password'])
			);
		
			$this->db->where($where);
			$this->db->update('tb_mas_users', $update);
			
			return;
			
		}

        function update_user($email, $data)
		{
			$this->db->where(array('strEmail' => $email));
			$this->db->update('tb_mas_users', $data);
			
			return;
			
		}
		
		function create_reset_request($data)
		{
			// Check if user exists
			$email_check = array('strEmail'=>$data['strEmail']);
			$query = $this->db->get_where('tb_mas_users', $email_check, 1);
			$auth_data = $query->result_array();
			if(isset($auth_data[0]['strEmail']))
			{
				
				// Create Reset Request Hash
				$hash = $this->generate_hash();
				
				// Update Database
				$this->db->where('strEmail',$data['strEmail']);
	            $this->db->update('tb_mas_users',array('strReset' => $hash));
			
				// Send Email Reset
				$result = $this->send_reset_email($data['strEmail'], $hash);
				
				return $result;
				
			}
			else
			{
				return 0;
			}
		
		}

		function set_email_data($email, $subject, $content)
		{
		
			//init configuration for email------------------------------------------------
				
				$config['protocol'] = 'smtp';
				$config['smtp_host'] = "ssl://smtp.googlemail.com";
				$config['smtp_port'] = '465';
				$config['smtp_timeout'] = '5';
				$config['smtp_user'] = "pinoytunermailer@gmail.com";
				$config['smtp_pass'] = "bir31057";
				$config['mailtype'] = 'html';
				$config['charset'] = 'utf-8';
				$config['newline'] = "\r\n";
			
				$this->email->initialize($config);

			//-------------------------------------------------------------------------
			    $this->email->from("pinoytunermailer@gmail.com");
	         	    $this->email->to($email);
			    $this->email->subject($subject);
			    //$mail_content = $this->load->view("templates/email/confirmation", $data);
			    $this->email->message($content);
		}
		
		function send_confirmation_email($email, $hash)
		{
			
			//load library email
			$this->load->library('email');
			
			$data['hash'] = $hash;
			$subject = 'Pinoytuner Account Registration Confirmation';
			$content =  "<div style='padding:10px;background:#333;'><img src='".base_url()."images/pinoytuner-logo.png'></div><br>";			
			$content .= '<div style="padding:25px;height:300px;">To Confirm registration click the link. <br /><a href="'.base_url().'users/confirm_email/'.$hash.'">'.base_url().'users/confirm_email/'.$data['hash'] .'</a></div>';
			$content.=  "<div style='font-weight:bold;padding:10px;background:#333;color:#ccc;'>Contact Us:<br>Email:tech@stratuscast.com<br>Address: 7615 Guijo St. R&D Bldg. San Antonio Village, Makati City Philippines<br>Telephone: (02)728-6242</div>";		    
		    
			$this->set_email_data($email,$subject,$content);		    
			 if($this->email->send())
		    {
				$this->email->clear();
				return true;
		    }
		    else
		    {
				$this->email->clear();
		        return false;
		    }
		
		}
		

		function send_reset_email($email, $hash)
		{
			//load library email
			$this->load->library('email');
			
			$data['hash'] = $hash;
			$subject='PT-Store Password Reset';
			$content =  "<div style='padding:10px;background:#333;'><img src='".base_url()."images/pinoytuner-logo.png'></div><br>";
			$content .= '<div style="padding:25px;height:300px;">To Reset your password click the link. <br /><a href="'.base_url().'users/password_reset/'.$hash.'">'.base_url().'users/password_reset/'.$hash.'</a></div>';
			$content.=  "<div style='font-weight:bold;padding:10px;background:#333;color:#ccc;'>Contact Us:<br>Email:tech@stratuscast.com<br>Address: 7615 Guijo St. R&D Bldg. San Antonio Village, Makati City Philippines<br>Telephone: (02)728-6242</div>";
			$this->set_email_data($email,$subject,$content);		    		    		    
			//$mail_content = $this->load->view("templates/email/confirmation", $data);
			
		    
			 if($this->email->send())
		    {
				$this->email->clear();
                return true;
		    }
		    else
		    {
				$this->email->clear();
		        return false;
		    }
			
		}
		
		function get_user_info($userid)
		{
			$query = $this->db->get_where('users',array('user_id'=>$userid));
			return current($query->result_array());
		}

		function get_user_image($userid)
		{
			$query = $this->db->get_where('user_images',array('user_id'=>$userid));
			return current($query->result_array());
		}
		
		function get_user_info_field($userid,$field)
		{
			$this->db->select($field);
			$this->db->where(array('user_id'=>$userid));
			$query = $this->db->get('users');
			return current($query->result_array());
		}
		
	}
	

?>
