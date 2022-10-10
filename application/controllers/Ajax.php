<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller {

	public function index()
	{
	}
	
	public function remove_image()
	{
		$post = $this->input->post();
		
		$file_thumb = dirname($_SERVER['SCRIPT_FILENAME']).'/assets/uploaded_file/images/thumbnail/'.$post['filename'];
		$file = dirname($_SERVER['SCRIPT_FILENAME']).'/assets/uploaded_file/images/'.$post['filename'];
		if(file_exists($file))
		{
				unlink($file);
		}
		if(file_exists($file_thumb))
		{
				unlink($file_thumb);
		}
		$data = array('strPicture'=>'');
		$this->data_poster->post_data('tb_mas_candidates',$data,$post['intId']);
		
		echo json_encode($post);
		
	}
			
}