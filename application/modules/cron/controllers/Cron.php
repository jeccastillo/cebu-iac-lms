<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    }
    
    public function index()
    {
        $data = array('intIsOnline'=>'00:00:00');
        $this->db
             ->update('tb_mas_faculty',$data);
    }


}