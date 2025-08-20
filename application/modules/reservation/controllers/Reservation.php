<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reservation extends CI_Controller {

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
        $this->data['title'] = "CCT Unity - Room Reservations";
        $this->load->library("email");	
        $this->load->helper("cms_form");	
        $this->load->model("user_model");
        $this->config->load('courses');
        $this->data['department_config'] = $this->config->item('department');
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        $this->data['campus'] = $this->config->item('campus');
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['page'] = "reservation";
    }
    
    public function index()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "reservation_dashboard";
            $this->data['opentree'] = "reservation";
            
            // Get today's reservations
            $this->data['todays_reservations'] = $this->data_fetcher->getTodaysReservations();
            
            // Get pending reservations for approval (admin/registrar only)
            if($this->is_admin() || $this->is_registrar())
            {
                $this->data['pending_reservations'] = $this->data_fetcher->getPendingReservations();
            }
            
            // Get user's reservations
            $this->data['my_reservations'] = $this->data_fetcher->getReservationsByFaculty($this->session->userdata('intID'));
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/reservation_dashboard",$this->data);
            $this->load->view("common/footer",$this->data);
        }
        else
            redirect(base_url()."unity");
    }
    
    public function add_reservation()
    {
        if($this->faculty_logged_in())
        {   
            $this->data['classrooms'] = $this->data_fetcher->fetch_table('tb_mas_classrooms');
            $this->data['page'] = "add_reservation";
            $this->data['opentree'] = "reservation";
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/add_reservation",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("reservation_validation_js",$this->data); 
        }
        else
            redirect(base_url()."unity");  
    }
    
    public function edit_reservation($id)
    {
        if($this->faculty_logged_in())
        {
            $this->data['item'] = $this->data_fetcher->getReservationById($id);
            
            // Check if user can edit this reservation
            if(!$this->can_edit_reservation($this->data['item']))
            {
                redirect(base_url()."reservation");
            }
            
            $this->data['classrooms'] = $this->data_fetcher->fetch_table('tb_mas_classrooms');
            $this->data['page'] = "edit_reservation";
            $this->data['opentree'] = "reservation";
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/edit_reservation",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("reservation_validation_js",$this->data); 
        }
        else
            redirect(base_url()."unity");    
    }
    
    public function submit_reservation()
    {
        if($this->faculty_logged_in())
        {   
            $post = $this->input->post();
            $post['intFacultyID'] = $this->session->userdata('intID');
            $post['intCreatedBy'] = $this->session->userdata('intID');
            $post['dteCreated'] = date('Y-m-d H:i:s');
            
            // Check for conflicts
            $conflicts = $this->data_fetcher->checkReservationConflicts($post);
            
            if(!empty($conflicts))
            {
                $this->session->set_flashdata('error', 'Room is not available at the selected time. Conflict detected.');
                redirect(base_url()."reservation/add_reservation");
            }
            
            // Auto-approve if user is admin/registrar, otherwise set as pending
            if($this->is_admin() || $this->is_registrar())
            {
                $post['enumStatus'] = 'approved';
                $post['intApprovedBy'] = $this->session->userdata('intID');
                $post['dteApproved'] = date('Y-m-d H:i:s');
            }
            
            $this->data_poster->post_data('tb_mas_room_reservations',$post);
            $this->data_poster->log_action('Reservation','Added a new Room Reservation for '.$post['dteReservationDate'],'green');
            
            $this->session->set_flashdata('success', 'Reservation submitted successfully.');
        }
        redirect(base_url()."reservation");
    }
    
    public function submit_edit_reservation()
    {
        if($this->faculty_logged_in())
        {   
            $post = $this->input->post();
            $reservation = $this->data_fetcher->getReservationById($post['intReservationID']);
            
            // Check if user can edit this reservation
            if(!$this->can_edit_reservation($reservation))
            {
                redirect(base_url()."reservation");
            }
            
            // Check for conflicts (excluding current reservation)
            $conflicts = $this->data_fetcher->checkReservationConflicts($post, $post['intReservationID']);
            
            if(!empty($conflicts))
            {
                $this->session->set_flashdata('error', 'Room is not available at the selected time. Conflict detected.');
                redirect(base_url()."reservation/edit_reservation/".$post['intReservationID']);
            }
            
            $post['dteUpdated'] = date('Y-m-d H:i:s');
            
            $this->data_poster->post_data('tb_mas_room_reservations',$post,$post['intReservationID'],'intReservationID');
            $this->data_poster->log_action('Reservation','Updated Room Reservation ID: '.$post['intReservationID'],'green');
            
            $this->session->set_flashdata('success', 'Reservation updated successfully.');
        }
        redirect(base_url()."reservation");
    }
    
    public function view_reservations()
    {
        if($this->faculty_logged_in())
        {
            $this->data['page'] = "view_reservations";
            $this->data['opentree'] = "reservation";
            
            // Get reservations based on user role
            if($this->is_admin() || $this->is_registrar())
            {
                $this->data['reservations'] = $this->data_fetcher->getAllReservations();
            }
            else
            {
                $this->data['reservations'] = $this->data_fetcher->getReservationsByFaculty($this->session->userdata('intID'));
            }
            
            $this->load->view("common/header",$this->data);
            $this->load->view("admin/view_reservations",$this->data);
            $this->load->view("common/footer",$this->data); 
            $this->load->view("common/reservation_conf",$this->data);
        }
        else
            redirect(base_url()."unity");   
    }
    
    public function approve_reservation()
    {
        $data['message'] = "failed";
        if($this->is_admin() || $this->is_registrar())
        {
            $post = $this->input->post();
            $reservation = $this->data_fetcher->getReservationById($post['id']);
            
            $update_data = array(
                'enumStatus' => $post['status'],
                'intApprovedBy' => $this->session->userdata('intID'),
                'dteApproved' => date('Y-m-d H:i:s'),
                'dteUpdated' => date('Y-m-d H:i:s')
            );
            
            if(isset($post['remarks']))
            {
                $update_data['strRemarks'] = $post['remarks'];
            }
            
            $this->data_poster->post_data('tb_mas_room_reservations', $update_data, $post['id'], 'intReservationID');
            $this->data_poster->log_action('Reservation', ucfirst($post['status']).' Room Reservation ID: '.$post['id'], 'blue');
            
            $data['message'] = "success";
        }
        echo json_encode($data);
    }
    
    public function delete_reservation()
    {
        $data['message'] = "failed";
        if($this->faculty_logged_in())
        {
            $post = $this->input->post();
            $reservation = $this->data_fetcher->getReservationById($post['id']);
            
            // Check if user can delete this reservation
            if($this->can_delete_reservation($reservation))
            {
                $this->data_poster->deleteItem('tb_mas_room_reservations', $post['id'], 'intReservationID');
                $this->data_poster->log_action('Reservation','Deleted Room Reservation ID: '.$post['id'],'red');
                $data['message'] = "success";
            }
        }
        echo json_encode($data);
    }
    
    public function check_availability()
    {
        $post = $this->input->post();
        $conflicts = $this->data_fetcher->checkReservationConflicts($post, isset($post['exclude_id']) ? $post['exclude_id'] : null);
        
        $data['available'] = empty($conflicts);
        $data['conflicts'] = $conflicts;
        
        echo json_encode($data);
    }
    
    public function get_available_rooms()
    {
        $post = $this->input->post();
        $available_rooms = $this->data_fetcher->getAvailableRooms($post['date'], $post['start_time'], $post['end_time']);
        
        echo json_encode($available_rooms);
    }
    
    public function get_schedule_data()
    {
        $post = $this->input->post();
        $start_date = $post['start_date'] ?? date('Y-m-d');
        $end_date = $post['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $room_id = $post['room_id'] ?? null;
        
        $schedule_data = array();
        
        // Get existing reservations
        $reservations = $this->data_fetcher->getReservationsByDateRange($start_date, $end_date, $room_id);
        foreach($reservations as $reservation) {
            $schedule_data[] = array(
                'id' => 'reservation_' . $reservation['intReservationID'],
                'title' => 'Reserved: ' . $reservation['strPurpose'],
                'start' => $reservation['dteReservationDate'] . 'T' . $reservation['dteStartTime'],
                'end' => $reservation['dteReservationDate'] . 'T' . $reservation['dteEndTime'],
                'backgroundColor' => '#f39c12',
                'borderColor' => '#e67e22',
                'type' => 'reservation',
                'room' => $reservation['strRoomCode'],
                'faculty' => $reservation['strFirstname'] . ' ' . $reservation['strLastname'],
                'status' => $reservation['enumStatus']
            );
        }
        
        // Get scheduled classes
        $active_sem = $this->data_fetcher->get_active_sem();
        if($active_sem) {
            $current_date = new DateTime($start_date);
            $end_date_obj = new DateTime($end_date);
            
            while($current_date <= $end_date_obj) {
                $day_of_week = $current_date->format('N'); // 1=Monday, 7=Sunday
                $date_str = $current_date->format('Y-m-d');
                
                // Get room schedules for this day
                $schedules = $this->data_fetcher->getScheduleByDay($day_of_week, $active_sem['intID'], $room_id);
                
                foreach($schedules as $schedule) {
                    $schedule_data[] = array(
                        'id' => 'class_' . $schedule['intRoomSchedID'] . '_' . $date_str,
                        'title' => $schedule['strCode'] . ' - ' . $schedule['strSection'],
                        'start' => $date_str . 'T' . $schedule['dteStart'],
                        'end' => $date_str . 'T' . $schedule['dteEnd'],
                        'backgroundColor' => '#3c8dbc',
                        'borderColor' => '#2c689c',
                        'type' => 'class',
                        'room' => $schedule['strRoomCode'],
                        'faculty' => $schedule['strFirstname'] . ' ' . $schedule['strLastname'],
                        'subject' => $schedule['strDescription']
                    );
                }
                
                $current_date->add(new DateInterval('P1D'));
            }
        }
        
        echo json_encode($schedule_data);
    }
    
    public function get_room_schedule()
    {
        $post = $this->input->post();
        $room_id = $post['room_id'];
        $date = $post['date'] ?? date('Y-m-d');
        
        $schedule_data = array();
        
        // Get reservations for this room and date
        $reservations = $this->data_fetcher->getReservationsByDateRange($date, $date, $room_id);
        foreach($reservations as $reservation) {
            $schedule_data[] = array(
                'type' => 'reservation',
                'start_time' => $reservation['dteStartTime'],
                'end_time' => $reservation['dteEndTime'],
                'title' => 'Reserved: ' . $reservation['strPurpose'],
                'faculty' => $reservation['strFirstname'] . ' ' . $reservation['strLastname'],
                'status' => $reservation['enumStatus']
            );
        }
        
        // Get scheduled classes for this room and day
        $active_sem = $this->data_fetcher->get_active_sem();
        if($active_sem) {
            $day_of_week = date('N', strtotime($date)); // 1=Monday, 7=Sunday
            $schedules = $this->data_fetcher->getScheduleByDay($day_of_week, $active_sem['intID'], $room_id);
            
            foreach($schedules as $schedule) {
                $schedule_data[] = array(
                    'type' => 'class',
                    'start_time' => $schedule['dteStart'],
                    'end_time' => $schedule['dteEnd'],
                    'title' => $schedule['strCode'] . ' - ' . $schedule['strSection'],
                    'faculty' => $schedule['strFirstname'] . ' ' . $schedule['strLastname'],
                    'subject' => $schedule['strDescription']
                );
            }
        }
        
        // Sort by start time
        usort($schedule_data, function($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });
        
        echo json_encode($schedule_data);
    }
    
    public function get_calendar_data()
    {
        $post = $this->input->post();
        $start_date = $post['start_date'] ?? date('Y-m-d');
        $end_date = $post['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $room_id = $post['room_id'] ?? null;
        $faculty_id = $post['faculty_id'] ?? null;
        
        $calendar_data = $this->data_fetcher->getCalendarData($start_date, $end_date, $room_id, $faculty_id);
        
        echo json_encode($calendar_data);
    }
    
    // Helper methods
    private function can_edit_reservation($reservation)
    {
        if($this->is_admin() || $this->is_registrar())
            return true;
            
        if($reservation['intFacultyID'] == $this->session->userdata('intID') && $reservation['enumStatus'] == 'pending')
            return true;
            
        return false;
    }
    
    private function can_delete_reservation($reservation)
    {
        if($this->is_admin() || $this->is_registrar())
            return true;
            
        if($reservation['intFacultyID'] == $this->session->userdata('intID') && $reservation['enumStatus'] == 'pending')
            return true;
            
        return false;
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
}
