<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedule extends CI_Controller {

	public function __construct()
	{
        
		parent::__construct();
		
        if(!$this->is_super_admin() && !$this->is_registrar())
            redirect(base_url()."unity");
        
        $this->config->load('themes');		
		$theme = $this->config->item('unity');
		if($theme == "" || !isset($theme))
			$theme = $this->config->item('global_theme');
		
        $settings = $this->data_fetcher->fetch_table('su-tb_sys_settings');
		foreach($settings as $setting)
		{
			$this->settings[$setting['strSettingName']] = $setting['strSettingValue'];
		}
        
        
        
        $this->config->load('schedule');
        
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        $this->data['css_dir'] = base_url()."assets/themes/".$theme."/css/";
        $this->data['js_dir'] = base_url()."assets/themes/".$theme."/js/";
        $this->data['title'] = "CCT Unity";
        $this->load->library("email");	
        $this->load->helper("cms_form");	
		$this->load->model("user_model");
        $this->config->load('courses');
        $this->data["campus"] = $this->config->item('campus');
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
    }
    
    public function add_schedule($sem = 0)
    {
            if($sem == 0)
                $active_sem = $this->data_fetcher->get_active_sem();
            else
                $active_sem = $this->data_fetcher->get_sem_by_id($sem);

            $this->data['sy'] = $this->db->get('tb_mas_sy')->result_array();
            $this->data['alert'] = $this->session->flashdata('alert');
            $this->data['suggested'] = $this->session->flashdata('suggested_sched');
            $this->data['active_sem'] = $active_sem;
            
            $this->data['page'] = "add_schedule";
            $this->data['opentree'] = "schedule";
            $this->data['days'] = Array('1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat','7'=>'-');
            $this->data['schema'] = Array('0'=>'None','1 3 5'=>'M W F','2 4'=>'T TH','1 3'=>'M W','3 5'=>'W F','2 4 6'=>'T TH S', '1 4' =>'M TH','3 6'=> 'W S', '2 5'=>'T F', '3 6'=>'W S');
            $this->data['types'] = Array('lect','lab');
            $this->data['timeslots'] = Array('7:00','7:10','7:15','7:20','7:25','7:30','7:35','7:40','7:45','7:50','7:55',
                                            '8:00','8:10','8:15','8:20','8:25','8:30','8:35','8:40','8:45','8:50','8:55',        
                                            '9:00','9:10','9:15','9:20','9:25','9:30','9:35','9:40','9:45','9:50','9:55',        
                                            '10:00','10:10','10:15','10:20','10:25','10:30','10:35','10:40','10:45','10:50','10:55',        
                                            '11:00','11:10','11:15','11:20','11:25','11:30','11:35','11:40','11:45','11:50','11:55',        
                                            '12:00','12:10','12:15','12:20','12:25','12:30','12:35','12:40','12:45','12:50','12:55',        
                                            '13:00','13:10','13:15','13:20','13:25','13:30','13:35','13:40','13:45','13:50','13:55',        
                                            '14:00','14:10','14:15','14:20','14:25','14:30','14:35','14:40','14:45','14:50','14:55',        
                                            '15:00','15:10','15:15','15:20','15:25','15:30','15:35','15:40','15:45','15:50','15:55',        
                                            '16:00','16:10','16:15','8:20','16:25','16:30','16:35','16:40','16:45','16:50','16:55',        
                                            '17:00','17:10','17:15','17:20','17:25','17:30','17:35','17:40','17:45','17:50','17:55',        
                                            '18:00','18:10','18:15','18:20','18:25','18:30','18:35','18:40','18:45','18:50','18:55',        
                                            '19:00','19:10','19:15','19:20','19:25','19:30','19:35','19:40','19:45','19:50','19:55',        
                                            '20:00','20:10','20:15','20:20','20:25','20:30','20:35','20:40','20:45','20:50','20:55',        
                                            '21:00','21:10','21:15','21:20','21:25','21:30'        
                                            );
            
            $this->data['classlists'] = $this->data_fetcher->getAllClasslistAssigned($active_sem['intID'],$this->session->userdata('strDepartment'),$this->is_super_admin());
            
            $this->data['rooms'] = $this->data_fetcher->fetch_table('tb_mas_classrooms');
            $this->data['block_sections'] = $this->data_fetcher->fetch_table('tb_mas_block_sections');
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_schedule",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("schedule_validation_js",$this->data); 
            //print_r($this->data['classlist']);
            
    }
    
    public function edit_schedule($id)
    {
        $admin = ($this->is_super_admin() || $this->is_registrar())?true:false;
        $this->data['item'] = $this->data_fetcher->getSchedule($id,$this->session->userdata('strDepartment'),$admin);
        if(!empty($this->data['item'])){
            $this->load->library('user_agent');
            if ($this->agent->is_referral())
            {
                    $this->session->set_flashdata('ref',$this->agent->referrer());
            }
            $this->data['alert'] = $this->session->flashdata('alert');


            $this->data['days'] = Array('1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat','7'=>'-');

            $this->data['types'] = Array('lect','lab');

            $this->data['timeslots'] = Array('7:00','7:10','7:15','7:20','7:25','7:30','7:35','7:40','7:45','7:50','7:55',
                                            '8:00','8:10','8:15','8:20','8:25','8:30','8:35','8:40','8:45','8:50','8:55',        
                                            '9:00','9:10','9:15','9:20','9:25','9:30','9:35','9:40','9:45','9:50','9:55',        
                                            '10:00','10:10','10:15','10:20','10:25','10:30','10:35','10:40','10:45','10:50','10:55',        
                                            '11:00','11:10','11:15','11:20','11:25','11:30','11:35','11:40','11:45','11:50','11:55',        
                                            '12:00','12:10','12:15','12:20','12:25','12:30','12:35','12:40','12:45','12:50','12:55',        
                                            '13:00','13:10','13:15','13:20','13:25','13:30','13:35','13:40','13:45','13:50','13:55',        
                                            '14:00','14:10','14:15','14:20','14:25','14:30','14:35','14:40','14:45','14:50','14:55',        
                                            '15:00','15:10','15:15','15:20','15:25','15:30','15:35','15:40','15:45','15:50','15:55',        
                                            '16:00','16:10','16:15','8:20','16:25','16:30','16:35','16:40','16:45','16:50','16:55',        
                                            '17:00','17:10','17:15','17:20','17:25','17:30','17:35','17:40','17:45','17:50','17:55',        
                                            '18:00','18:10','18:15','18:20','18:25','18:30','18:35','18:40','18:45','18:50','18:55',        
                                            '19:00','19:10','19:15','19:20','19:25','19:30','19:35','19:40','19:45','19:50','19:55',        
                                            '20:00','20:10','20:15','20:20','20:25','20:30','20:35','20:40','20:45','20:50','20:55',        
                                            '21:00','21:10','21:15','21:20','21:25','21:30'
                                            );
            $active_sem = $this->data_fetcher->get_active_sem();
            
            $this->data['rooms'] = $this->data_fetcher->fetch_table('tb_mas_classrooms');
            $this->data['block_sections'] = $this->data_fetcher->fetch_table('tb_mas_block_sections');
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_schedule",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("schedule_validation_js",$this->data); 
            $this->load->view("common/edit_schedule_conf",$this->data); 
            // print_r($this->data['classlists']);
        }
        else
            redirect(base_url()."unity"); 
        
        
        
    }
    
    private function suggestSchedule($cs,$ay,$roomtype)
    {
        $classlist = $this->data_fetcher->getClasslistDetails($cs);
        $faculty = $classlist->intFacultyID;
        $post['days'] = array(1,2,3,4,5,6);
        shuffle($post['days']);

        $rooms = $this->data_fetcher->getRoomsSelected($classlist->intSubjectID,'lecture');
        $rooms_lab = $this->data_fetcher->getRoomsSelected($classlist->intSubjectID,'laboratory');
        $schema = $this->data_fetcher->getSelectedDays($classlist->intSubjectID);
        $suggested = array();
        

        if(!empty($schema))
        {
            
            $selected_sch = array();
            foreach($schema as $sch)
            {
                $sc = explode(' ',$sch['strDays']);
                $selected_sch[] = $sc;
                       
            
            }
            if(!empty($selected_sch))
            {
                foreach($selected_sch as $sel)
                {
                    
                    if(count($sel) == 3)
                        $hours = "1_hour";
                    else
                        $hours = "1_5_hour";

                    foreach($rooms as $room)
                    {
                        foreach($this->config->item($hours) as $key=>$value){
                            $d['dteStart'] = $key;
                            $d['dteEnd'] = $value;
                            $d['intRoomID'] = $room['intID'];
                            $d['roomCode'] = $room['strRoomCode'];

                            $conflict = $this->data_fetcher->schedule_conflict($d,null,$ay,$sel);
                            $sconflict = $this->data_fetcher->section_conflict($d,null,$classlist->strSection,$ay,$sel);
                            if($faculty == 999)
                                $fconclit = array();
                            else
                                $fconflict = $this->data_fetcher->faculty_conflict($d,null,$faculty,$ay,$sel);

                            if(empty($conflict) && empty($sconflict) && empty($fconflict))
                            {


                                    $d['strScheduleCode'] = $cs;
                                    $d['intSem'] = $ay;
                                    $d['enumClassType'] = "lect";
                                    $d['strDay'] = 0;
                                    $d['schema'] = implode(' ',$sel);

                                    $suggested[] =  $d;

                                
                            }

                        }
                    } 
                }

            }
        }
        else
        {
            if($roomtype == "lect")
            foreach($post['days'] as $dkey=>$dvalue)
                {
                    foreach($rooms as $room)
                    {
                        foreach($this->config->item($classlist->intLectHours.'_hour') as $key=>$value){
                            $d['dteStart'] = $key;
                            $d['dteEnd'] = $value;
                            $d['strDay'] = $dvalue;
                            $d['intRoomID'] = $room['intID'];
                            $d['roomCode'] = $room['strRoomCode'];
                            
                            $conflict = $this->data_fetcher->schedule_conflict($d,null,$ay);
                            $sconflict = $this->data_fetcher->section_conflict($d,null,$classlist->strSection,$ay);
                            if($faculty == 999)
                                $fconflict = array();
                            else
                                $fconflict = $this->data_fetcher->faculty_conflict($d,null,$faculty,$ay);


                            if(empty($conflict) && empty($sconflict) && empty($fconflict))
                            {
                                $d['strScheduleCode'] = $cs;
                                $d['intSem'] = $ay;
                                $d['enumClassType'] = "lect";

                                $suggested[] =  $d;

                                break;
                            }

                        }
                        
                    }
                    
                }

            else
            {
                foreach($post['days'] as $day)
                {
                    foreach($rooms_lab as $room)
                    {
                        foreach($this->config->item($classlist->intLab.'_hour') as $key=>$value){
                            $d['dteStart'] = $key;
                            $d['dteEnd'] = $value;
                            $d['strDay'] = $day;
                            $d['intRoomID'] = $room['intID'];
                            $d['roomCode'] = $room['strRoomCode'];
                            
                            $conflict = $this->data_fetcher->schedule_conflict($d,null,$ay);
                            $sconflict = $this->data_fetcher->section_conflict($d,null,$classlist->strSection,$ay);
                            $fconflict = $this->data_fetcher->faculty_conflict($d,null,$faculty,$ay);

                            if(empty($conflict) && empty($sconflict) && empty($fconflict))
                            {
                                $d['strScheduleCode'] = $cs;
                                $d['intSem'] = $ay;
                                $d['enumClassType'] = "lab";

                                
                                $suggested[] = $d;
                                 //   echo "new";

                                break;
                            }

                        }
                       
                    }
                    
                }

            }
        }
        
        return $suggested;
    }
    
    public function submit_schedule()
    {
        $post = $this->input->post();
        $sc = array();                
        $post['intEncoderID'] = $this->session->userdata('intID');
        $schema = $post['strSchema'];
        unset($post['strSchema']);
        
        $classlist = $this->data_fetcher->getClasslistDetails($post['strScheduleCode']);
        
        if($schema == 0)
        {
            //print_r($post);
            $conflict = $this->data_fetcher->schedule_conflict($post,null,$post['intSem']);
            $sconflict = $this->data_fetcher->section_conflict($post,null,$post['blockSectionID'],$post['intSem']);
            $fconflict = $this->data_fetcher->faculty_conflict($post,null,$classlist->intFacultyID,$post['intSem']);
        }
        else
        {
            $sc = explode(' ',$schema);
            $conflict = $this->data_fetcher->schedule_conflict($post,null,$post['intSem'],$sc);
            $sconflict = $this->data_fetcher->section_conflict($post,null,$post['blockSectionID'],$post['intSem'],$sc);
            $fconflict = $this->data_fetcher->faculty_conflict($post,null,$classlist->intFacultyID,$post['intSem'],$sc);
        
        }
        
        
        
        if(!empty($conflict)){
             $this->data['suggested'] = $this->suggestSchedule($post['strScheduleCode'],$post['intSem'],$post['enumClassType']);
             $this->data['alert'] = 'Conflict schedule with '.$conflict[0]['strCode']." ".$conflict[0]['strClassName']." ".$conflict[0]['year'].$conflict[0]['strSection']." ".$conflict[0]['sub_section'];
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_schedule_suggestions",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/schedule_suggestions_conf",$this->data); 
        }
        else if(!empty($sconflict))
        {
            $this->data['suggested'] = $this->suggestSchedule($post['strScheduleCode'],$post['intSem'],$post['enumClassType']);
            $this->data['alert'] = 'Section conflict in section schedule with '.$sconflict[0]['strCode']." ".$sconflict[0]['strClassName']." ".$sconflict[0]['year'].$sconflict[0]['strSection']." ".$sconflict[0]['sub_section'];
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_schedule_suggestions",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/schedule_suggestions_conf",$this->data); 
        }
        else if(!empty($fconflict) && $classlist->intFacultyID!=999)
        {
            $this->data['suggested'] = $this->suggestSchedule($post['strScheduleCode'],$post['intSem'],$post['enumClassType']);
            $this->data['alert'] = 'Conflict with faculty Schedule';
            foreach($fconflict as $conf){
                $this->data['alert'] .= '<br />Conflict with section schedule with '.$conf['strCode']." ".$conf['strClassName']." ".$conf['year'].$conflict[0]['strSection']." ".$conf['sub_section'];
            }
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_schedule_suggestions",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/schedule_suggestions_conf",$this->data); 
        }
        else
        {
            
           if($schema == 0)
                $this->data_poster->post_data('tb_mas_room_schedule',$post);
            else
                foreach($sc as $dvalue){
                    $post['strDay'] = $dvalue;
                    if(!$this->data_fetcher->getFromSchedule($post['strScheduleCode'],$active_sem['intID'],'lect',$dvalue))
                        $this->data_poster->post_data('tb_mas_room_schedule',$post);


                }
            
                $this->data_poster->log_action('Schedule','Added a new Schedule Room Sched ID: '.$this->db->insert_id(),'green');
            redirect(base_url()."schedule/view_schedules");
        }
        
            
    }
    
    public function submit_edit_schedule()
    {
        $post = $this->input->post();
        if($post['intEncoderID'] == $this->session->userdata('intID') || $this->is_super_admin() || $this->is_registrar())
        {
            $referer = $this->session->flashdata('ref');

            $classlist = $this->data_fetcher->getClasslistDetails($post['strScheduleCode']);
            $conflict = $this->data_fetcher->schedule_conflict($post,$post['intRoomSchedID'],$post['intSem']);
            $sconflict = $this->data_fetcher->section_conflict($post,$post['intRoomSchedID'],$post['blockSectionID'],$post['intSem']);
            $fconflict = $this->data_fetcher->faculty_conflict($post,$post['intRoomSchedID'],$classlist->intFacultyID,$post['intSem']);

            if(!empty($conflict)){
                $this->session->set_flashdata('alert','conflict in  schedule with '.$conflict[0]['strCode']." ".$conflict[0]['strSection']);
                redirect(base_url()."schedule/edit_schedule/".$post['intRoomSchedID']);
            }
            else if(!empty($sconflict))
            {
                $this->session->set_flashdata('alert','conflict in section schedule with '.$sconflict[0]['strCode']." ".$sconflict[0]['strSection']);
                redirect(base_url()."schedule/edit_schedule/".$post['intRoomSchedID']);
            }
            else if(!empty($fconflict) && $classlist->intFacultyID!=999)
            {
                $this->session->set_flashdata('alert','conflict with faculty schedule with Faculty Sched');
                redirect(base_url()."schedule/edit_schedule/".$post['intRoomSchedID']);
            }
            else
            {
                $this->data_poster->post_data('tb_mas_room_schedule',$post,$post['intRoomSchedID']);
                $this->data_poster->log_action('Schedule','Updated Schedule Info: '.$post['intRoomSchedID'],'green');
                if($referer == "")
                    redirect(base_url()."schedule/view_schedules");
                else
                    redirect($referer);
            }
        }
        else
        {
            $this->session->set_flashdata('alert','Cannot edit schedule');
            redirect(base_url()."schedule/edit_schedule/".$post['intRoomSchedID']);
        }
        
            
    }
    
    public function view_schedules($sem = 0, $section = 0)
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_schedules";
            $this->data['opentree'] = "schedule";
            $this->data['section'] = $section;
            $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
            $this->data['block_sections'] = $this->data_fetcher->fetch_table('tb_mas_block_sections');
            if($sem == 0)
            {
                $active_sem = $this->data_fetcher->get_active_sem();
                $this->data['sem'] = $active_sem['intID'];
            }
            else
            {
                $this->data['sem'] = $sem;
            }
    
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/schedule_view",$this->data);
            $this->load->view("common/footer",$this->data);
            $this->load->view("common/schedule_conf.php",$this->data);
            //print_r($this->data['classlist']);
            
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function delete_schedule()
    {
       
        $post = $this->input->post();
        $info = $this->data_fetcher->getSchedule($post['id']);
        if($info['strDepartment'] == $this->session->userdata('strDepartment') || $this->is_super_admin() || $this->is_registrar())
        {
            $this->data_poster->deleteSchedule($post['id'],'intRoomSchedID');

            $data['message'] = "success";
            $this->data_poster->log_action('Schedule','Deleted a Schedule '.$info['strScheduleCode'],'red');
        }
        else
            $data['message'] = "You are unauthorized to delete this schedule"; 
    
    
        echo json_encode($data);
    }
    
    public function faculty_logged_in()
    {
        if($this->session->userdata('faculty_logged'))
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
    
    public function is_department_head()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 4)
            return true;
        else
            return false;
        
    }


}