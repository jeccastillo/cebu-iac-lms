<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        //User Level Validation               
    }
    
   
    public function get_user(){
        
        $post = $this->input->post();
        $data['user'] = $this->db->get_where('tb_mas_faculty',array('email'=>$post['email']))->first_row();
        
        echo json_encode($data);
    }

}