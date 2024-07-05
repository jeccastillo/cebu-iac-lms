<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tuitionyear extends CI_Controller {

    public $validation_config = [];
    
	public $docroot;
    
    public function __construct()
	{
		parent::__construct();

        //User Level Validation
        $userlevel = $this->session->userdata('intUserLevel');        
        if($userlevel != 2 && $userlevel != 6)
		  redirect(base_url()."unity");
          
		$this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
		
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";
               
                
        $this->data['temp_pics'] = base_url()."assets/temp/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "iACADEMY";
        $this->data['campus'] = $this->config->item('campus');	
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
        
        
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = array();
        
        $this->load->library('parser');
        
        $this->data['all_messages'] = array();
        
        $this->data['trashed_messages'] = array();
        
        $this->data['sent_messages'] = array();
        
        $this->config_all();
        
        
    }

    public function set_default($type,$id){
        $field = $type == 1 ? "isDefault" : "isDefaultShs";
        $data[$field] = 0;
        $this->db->update('tb_mas_tuition_year',$data);
        $data[$field] = 1;
        $this->db->where('intID',$id)
                 ->update('tb_mas_tuition_year',$data);

        redirect(base_url()."tuitionyear/view_tuition_years");
    }

    public function add_tuition_year()
    {        
        $special_role = $this->session->userdata('special_role');        
        if($special_role < 1)
            redirect(base_url()."unity/faculty_dashboard");

        $this->data['page'] = "tuitionyear";
        $this->data['opentree'] = "finance_admin";
        $this->data['defaultYear'] = $this->data_fetcher->getDefaultTuitionYearID();
        $this->data['formAction'] = base_url()."tuitionyear/submit_form";    
        $this->load->view("common/header",$this->data);
        $this->load->view("tuitionyear",$this->data);
        $this->load->view("common/footer",$this->data);                                  
       
    }

    public function tuition_info($id){

        if($id != 0){
            $data['data'] = $this->data_fetcher->fetch_single_entry('tb_mas_tuition_year',$id);
            $data['data']['misc'] = $this->data_fetcher->getTuitionExtra('misc',$id);
            $data['data']['lab_fees'] = $this->data_fetcher->getTuitionExtra('lab_fee',$id);
            $data['data']['track'] = $this->data_fetcher->getTuitionTrack($id,'track');
            $data['data']['program'] = $this->data_fetcher->getTuitionTrack($id,'program');
        }
        else{
            $data['data'] = [];
            $data['data']['misc'] = [];
            $data['data']['lab_fees'] = [];
        }

        $data['shs_programs'] = $this->db->where(array('type'=>'shs'))
                                         ->order_by('strProgramCode','asc')       
                                         ->get('tb_mas_programs')
                                         ->result_array();
        $data['college_programs'] = $this->db->where('type','college')
                                             ->or_where('type','other')    
                                             ->order_by('strProgramCode','asc')
                                             ->get('tb_mas_programs')
                                             ->result_array();
        $data['success'] = true;        
        $data['message'] ="Successfully Added";
        echo json_encode($data);

    }
    public function submit_extra($type){
        $post = $this->input->post();
        $this->data_poster->post_data('tb_mas_tuition_year_'.$type ,$post);

        $data['success'] = true;
        $data['message'] ="Successfully Added";
        echo json_encode($data);
    }

    public function delete_type(){
        $post = $this->input->post();
        if($post['type'] != "track" && $post['type'] != "program")
            $this->data_poster->deleteItem('tb_mas_tuition_year_'.$post['type'],$post['id'],'intID');
        else
            $this->data_poster->deleteItem('tb_mas_tuition_year_'.$post['type'],$post['id'],'id');
        
        $this->data_poster->log_action('Tuition Year '.$post['type'],'Deleted Tuition'.$post['type'].' : '.$post['id'],'red');

        $data['success'] = true;
        $data['message'] ="Successfully Deleted";
        echo json_encode($data);
    }

    public function delete_tuition_year(){
        $post = $this->input->post();
        $this->data_poster->deleteItem('tb_mas_tuition_year',$post['id'],'intID');
        $this->data_poster->log_action('Tuition Year','Deleted Tuition Year'.$post['id'],'red');
        $data['message'] = "success";
        $data['success'] = true;
        echo json_encode($data);
    }

    public function duplicate_tuition_year(){
        $post = $this->input->post();
        $id = $post['id'];
        $tuition_year = $this->data_fetcher->fetch_single_entry('tb_mas_tuition_year',$id);
        $misc = $this->data_fetcher->getTuitionExtra('misc',$id);
        $lab_fees = $this->data_fetcher->getTuitionExtra('lab_fee',$id);
        $ty_program = $this->data_fetcher->getTuitionExtraTrack('program',$id);
        $ty_track = $this->data_fetcher->getTuitionExtraTrack('track',$id);

        unset($tuition_year['intID']);
        $tuition_year['isDefault'] = 0;
        $tuition_year['isDefaultShs'] = 0;
        $tuition_year['year'] = $tuition_year['year']."Dup";
        $this->data_poster->post_data('tb_mas_tuition_year',$tuition_year);
        $t_id = $this->db->insert_id();
        foreach($misc as $item){
            unset($item['intID']);
            $item['tuitionYearID'] = $t_id;
            $this->data_poster->post_data('tb_mas_tuition_year_misc',$item);            
        }
        foreach($lab_fees as $item){
            unset($item['intID']);
            $item['tuitionYearID'] = $t_id;
            $this->data_poster->post_data('tb_mas_tuition_year_lab_fee',$item);
        }
        foreach($ty_program as $item){
            unset($item['id']);
            $item['tuitionyear_id'] = $t_id;
            $this->data_poster->post_data('tb_mas_tuition_year_program',$item);
        }
        foreach($ty_track as $item){
            unset($item['id']);
            $item['tuitionyear_id'] = $t_id;
            $this->data_poster->post_data('tb_mas_tuition_year_track',$item);
        }

        $data['message'] = "success";
        $data['success'] = true;
        echo json_encode($data);
    }

    public function submit_form($id = 0)
    {
        $post = $this->input->post();        
        // $config['upload_path'] = $this->docroot.'/assets/temp';
		// $config['allowed_types'] = 'gif|jpg|png|jpeg';
		// $config['max_size']	= '400';
        // $config['file_name'] = md5(date('Ymdhis'));
		// $config['max_width']  = '300';
        // $config['min_width']  = '300';
		// $config['max_height']  = '300';
        // $config['min_height']  = '300';

		// $this->load->library('upload', $config);

        
        
        
        
        if($id == 0)
            $this->data_poster->post_data('tb_mas_tuition_year',$post);
        else{
            if($post['isDefault'] == 1)
                $this->data_poster->reset_tuition_year();
                            
            $this->data_poster->post_data('tb_mas_tuition_year',$post,$id);
        }

        $data['success'] = true;
        $data['data'] = $post;
        $data['message'] ="Successfully Added";
        echo json_encode($data);
       
        
    }

    public function view_tuition_years(){

        $special_role = $this->session->userdata('special_role');        
        if($special_role < 1)
            redirect(base_url()."unity/faculty_dashboard");
        
        $this->data['page'] = "tuitionyear_view";
        $this->data['opentree'] = "finance_admin";
        $this->load->view("common/header",$this->data);
        $this->load->view("tuitionyearview",$this->data);
        $this->load->view("common/footer",$this->data);
        $this->load->view("common/tuitionyear_conf",$this->data);    
    }
    
    
    
  
    
    public function config_all()
    {
        $this->validation_config =  array(
                array(
                        'field' => 'year',
                        'label' => 'Tuition Year Label',
                        'rules' => 'required'                        
                ),
                array(
                        'field' => 'pricePerUnit',
                        'label' => 'Price Per Unit',
                        'rules' => 'required'
                ),                            
               
        );
    }
    
    public function email_check($email)
	{
		$this->form_validation->set_message('email_check', 'Domain must gmail id');
		return strpos($email, '@gmail.com') !== false;
	}
    
    public function validate_form()
    {
        $ret = [];
        $ret['message'] = "";
        $ret['errors'] = [];
        $this->load->library('form_validation');
        
        
        $this->form_validation->set_rules($this->validation_config);

        if ($this->form_validation->run() == FALSE)
        {
                foreach($this->validation_config as $conf){
                    if(form_error($conf['field']))
                        $ret['errors'][$conf['field']] = form_error($conf['field']);
                }
                $ret['message'] =  "failed";
        }
        else
        {                
                $ret['message'] =  "success";
        }
        
        echo json_encode($ret);
    }
    
    public function validate_field()
    {
        $post = $this->input->post();
        
        $this->load->library('form_validation');
        $config = [];
        $ret['errors'] = [];
        foreach($this->validation_config as $conf)
            foreach($post as $key=>$val)
            {
                if($conf['field'] == $key)
                    $config[] = $conf;
            }
        if(!empty($config)){
            $this->form_validation->set_rules($config);
        
            if ($this->form_validation->run() == FALSE)
            {
                    if(isset($config[0]) && form_error($config[0]['field']))
                        $ret['errors'][$config[0]['field']] = form_error($config[0]['field']);

                    $ret['message'] =  "failed";
            }
            else
            {                
                    $ret['message'] =  "success";
            }
        }
        else
            $ret['message'] =  "success";
        
        echo json_encode($ret);
        
    }
    
    
    public function faculty_logged_in()
    {
        if($this->session->userdata('faculty_logged'))
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
    
    public function is_super_admin()
    {
         $admin = $this->session->userdata('intUserLevel');
        if($admin == 2)
            return true;
        else
            return false;
    }
   


}