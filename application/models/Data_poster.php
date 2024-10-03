<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_poster extends CI_Model {

	function post_data($table,$post,$update=null,$id='intID')
	{
		if($update==null)
			$this->db->insert($table,$post);
		else
		{
            if($table == 'tb_mas_room_schedule')
            {
            $this->db
				 ->where('intRoomSchedID',$update)
				 ->update($table,$post);
            }
            elseif($table == 'tb_mas_programs')
            {
            $this->db
				 ->where('intProgramID',$update)
				 ->update($table,$post);
                
            }
            elseif($table == 'tb_mas_registration')
            {
            $this->db
				 ->where('intRegistrationID',$update)
				 ->update($table,$post);
                
            }
            elseif($table == 'tb_mas_student_exam')
            {
                $this->db
                     ->where('student_id',$update)
                     ->update($table,$post);
            }
            elseif($table == 'tb_mas_classlist_student')
            {
                $this->db
                     ->where('intCSID',$update)
                     ->update($table,$post);
            }
            elseif($table == 'tb_mas_prev_balance')
            {
                $this->db
                     ->where('id',$update)
                     ->update($table,$post);
            }
            else
            {
            $this->db
				 ->where($id,$update)
				 ->update($table,$post);
            }
		}
	}
    
    function updateApplicationCode($id)
    {
        $this->db
             ->where('intApplicationID',$id)
             ->update('tb_mas_applications',array('strConfirmationCode'=>0));
    }

    function insert_or_print($data){
        $this->db->insert('tb_mas_printed_or',$data);
    }

    function delete_or_print($id,$campus){
        $this->db->where(array('or_number'=>$id,'campus'=>$campus))
            ->delete('tb_mas_printed_or');
    }
    
    function reset_tuition_year(){
        $this->db             
             ->update('tb_mas_tuition_year',array('isDefault'=>0));
    }

    function updateExamConfirmation($id)
    {
        $this->db
             ->where('intApplicationID',$id)
             ->update('tb_mas_exam_info',array('isConfirmed'=>1));
    }
    function sendMessage($message)
    {
        $post['strSubject'] = $message['strSubject'];
        $post['strMessage'] = $message['strMessage'];
        $post['dteDate'] = $message['dteDate'];
        $this->db->insert('tb_mas_system_message',$post);
        
        $messageId = $this->db->insert_id();
        
        $d['intTrash'] = 0;
        $d['intRead'] = 0;
        $d['intFacultyIDSender'] = $message['intFacultyIDSender'];
        
        foreach($message['intFacultyID'] as $employee)
        {
             $d['intMessageID'] = $messageId;
             $d['intFacultyID'] = $employee;
             $this->db->insert('tb_mas_message_user',$d);
        }
       
    }
    
    function setMessageRead($id)
	{
		
        $post['intRead'] = 1;
        $this->db
             ->where('intMessageUserID',$id)
             ->update('tb_mas_message_user',$post);

    }
    
    function setMessages($ids,$read)
	{
		
        $post['intRead'] = $read;
        for($i = 0;$i<count($ids);$i++){
            if($i == 0)
                $this->db->where('intMessageUserID',$ids[$i]);
            else
                $this->db->or_where('intMessageUserID',$ids[$i]);
        }
        $this->db
             ->update('tb_mas_message_user',$post);

    }
    
    function deleteMessages($ids)
	{
		
        for($i = 0;$i<count($ids);$i++){
            if($i == 0)
                $this->db->where('intMessageUserID',$ids[$i]);
            else
                $this->db->or_where('intMessageUserID',$ids[$i]);
        }
        $this->db
             ->delete('tb_mas_message_user');

    }
    
    function trashMessages($ids,$recover=1)
	{
		$post['intTrash'] = $recover;
        for($i = 0;$i<count($ids);$i++){
            if($i == 0)
                $this->db->where('intMessageUserID',$ids[$i]);
            else
                $this->db->or_where('intMessageUserID',$ids[$i]);
        }
        $this->db
             ->update('tb_mas_message_user',$post);

    }
    
    function trashMessage($id,$recover=1)
	{
		
        $post['intTrash'] = $recover;
        $this->db
             ->where('intMessageUserID',$id)
             ->update('tb_mas_message_user',$post);

    }
    
    function deleteItem($table,$id,$idLabel)
    {
       
        $this->db
        ->where($idLabel,$id)
        ->delete($table);

        return true;
    }
    
    function removeAdvisedSubjects($sid,$ay)
    {
       
        $sql = "DELETE tb_mas_advised_subjects FROM tb_mas_advised_subjects JOIN tb_mas_advised ON tb_mas_advised_subjects.intAdvisedID = tb_mas_advised.intAdvisedID WHERE tb_mas_advised.intStudentID = ".$sid." AND tb_mas_advised.intSYID = ".$ay;
        $this->db->query($sql);
        
    }
    
    function approve_completion($id){
        
        $this->db
        ->where('intCompletionID',$id)
        ->update('tb_mas_completion', array('enumStatus'=> 1));
        
        
    }
    
    function removeRegistration($sid,$ay)
    {
        $advised = $this->db
                      ->get_where('tb_mas_advised',array('intStudentID'=>$sid,'intSYID'=>$ay))
                      ->first_row();
         $this->db
            ->where('intAdvisedID',$advised->intAdvisedID)
            ->delete('tb_mas_advised_subjects');
        
        $this->db
            ->where('intAdvisedID',$advised->intAdvisedID)
            ->delete('tb_mas_advised');
        
        $this->db
            ->where(array('intStudentID'=>$sid,'intAYID'=>$ay))
            ->delete('tb_mas_registration');

        $this->db
            ->where(array('name'=>'tuition','syid'=>$ay,'student_id'=>$sid))
            ->delete('tb_mas_student_ledger');
        
         $sql = "DELETE tb_mas_classlist_student FROM tb_mas_classlist_student JOIN tb_mas_classlist ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID WHERE tb_mas_classlist_student.intStudentID = ".$sid." AND tb_mas_classlist.strAcademicYear = ".$ay;
        $this->db->query($sql);
    }
    
    function updateIncompleteSubjects($year,$sem)
    {
        $semID = $this->db
                      ->get_where('tb_mas_sy',array('strYearStart'=>$year,'enumSem'=>$sem))
                      ->first_row();
                      
        if(!empty($semID))
        {
            $update['floatFinalGrade'] = 5;
            $update['strRemarks'] = "Failed";
            $sql = "UPDATE tb_mas_classlist cl JOIN tb_mas_classlist_student cs ON cs.intClassListID = cl.intID SET cs.floatFinalGrade = 5, cs.strRemarks = 'failed' WHERE cl.strAcademicYear = ".$semID->intID." AND cs.floatFinalGrade = 3.5";
            /*
            $this->db
                 ->where(array('strAcademicYear'=>$semID->intID,'floatFinalGrade'=>3.5))
                 ->join('tb_mas_classlist_student','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
                 ->update('tb_mas_classlist',$update);
                 */
            
            $this->db->query($sql);
                
        }
                    
        
        
    }
    
    
    
    
    function setMessageUnRead($id)
	{
		
        $post['intRead'] = 0;
        $this->db
             ->where('intMessageUserID',$id)
             ->update('tb_mas_message_user',$post);

    }
    
    function setMessageUnReadByMID($id)
    {
        $post['intRead'] = 0;
        $this->db
             ->where('intMessageID',$id)
             ->update('tb_mas_message_user',$post);
    }
    
    
    function log_action($category,$action,$color,$student=null,$registration=null)
    {
        $data = array('strCategory'=>$category,'strAction'=>$action,'dteLogDate'=>date("Y-m-d H:i:s"),'intFacultyID'=>$this->session->userdata('intID'),
                    'strColor'=>$color,
                    'student_id'=>$student,
                    'registration_id'=>$registration);
                    
        $this->post_data('tb_mas_logs',$data);
        
    }
    
    function addStudentClasslist($post,$user)
    {        
        $post['date_added'] = date("Y-m-d H:i:s");
        $post['enlisted_user'] = $user;
        $this->db->insert('tb_mas_classlist_student',$post);
        
    }
    
    function update_classlist($table,$post,$update=null)
	{
		if($update==null)
			$this->db->insert($table,$post);
		else
		{
			$this->db
				 ->where('intCSID',$update)
				 ->update($table,$post);
		}
	}
    
    
    function removeFacultyLoad($id,$ay)
	{
        $post = array('intFacultyID'=>999);
		
        $this->db
             ->where(array('intFacultyID'=>$id,'strAcademicYear'=>$ay))
             ->update('tb_mas_classlist',$post);
		
	}
    
    function removeFacultyClasslist($classlistID)
    {
        $post = array('intFacultyID'=>999);
		
        $this->db
             ->where(array('intID'=>$classlistID))
             ->update('tb_mas_classlist',$post);
    }
    
    function set_ay_inactive()
    {
        $post['enumStatus'] = "inactive";
        $post['intProcessing'] = 0;
        $this->db->update('tb_mas_sy',$post);
    }
	
	function update_image($post,$update=null,$field = 'strFileName')
	{
		if($update!=null)
		{
			$this->db
				 ->where($field,$update)
				 ->update('tb_sys_media',$post);
			
			return 1;
		}
		return 0;
	}
		
	function delete_data($table,$id)
	{
		$this->db
			->where('intID',$id)
			->delete($table);
	}
    
    function delete_room_subject($subject)
	{
		$this->db
			->where('intSubjectID',$subject)
			->delete('tb_mas_room_subject');
	}
   
    function delete_prereq_subject($subject)
	{
		$this->db
			->where('intSubjectID',$subject)
			->delete('tb_mas_prerequisites');
	}
    function delete_eq_subject($subject){
        $this->db
			->where('intSubjectID',$subject)
			->delete('tb_mas_equivalents');
    }
    function delete_days_subject($subject)
	{
		$this->db
			->where('intSubjectID',$subject)
			->delete('tb_mas_days');
	}
    
    function deleteFromSchedule($scode,$sem)
	{
		$this->db
			->where(array('strScheduleCode'=>$scode,'intSem'=>$sem))
			->delete('tb_mas_room_schedule');
	}
    
    function deleteStudentCS($table,$id)
	{
		return $this->db
			->where('intCSID',$id)
			->delete($table);
	}
    
    function deleteClassroom($id)
	{
		$this->db
			->where('intID',$id)
			->delete('tb_mas_classrooms');
        
        $this->db
			->where('intRoomID',$id)
			->delete('tb_mas_room_schedule');
	}
    
    function deleteStudent($id)
	{
		$this->db
			->where('intID',$id)
			->delete('tb_mas_users');
        
        $this->db
			->where('intStudentID',$id)
			->delete('tb_mas_classlist_student');
        
        $registration = current($this->db->get_where('tb_mas_registration',array('intStudentID'=>$id))->result_array());
        
        $this->db
			->where('intStudentID',$id)
			->delete('tb_mas_registration');
        
        $this->db
			->where('intRegistrationID',$registration['intRegistrationID'])
			->delete('tb_mas_transactions');
        
        $advised = current($this->db->get_where('tb_mas_advised',array('intStudentID'=>$id))->result_array());
        
        $this->db
			->where('intStudentID',$id)
			->delete('tb_mas_advised');
        
        $this->db
			->where('intAdvisedID',$advised['intAdvisedID'])
			->delete('tb_mas_advised_subjects');
        
        
	}
    
    function deleteFaculty($id)
	{
        $post['intFacultyID'] = "999";
		
        $this->db
             ->where('intFacultyID',$id)
             ->update('tb_mas_classlist',$post);
        
        $this->db
			->where('intID',$id)
			->delete('tb_mas_faculty');
        
	}
    
    function deleteClassList($id)
    {
        $this->db
			->where('intClassListID',$id)
			->delete('tb_mas_classlist_student');
        
        $this->db
			->where('intID',$id)
			->delete('tb_mas_classlist');
    }

    function dissolveClassList($id,$fn){
        $post = array('isDissolved'=>$fn);
        
        $this->db
				 ->where('intID',$id)
				 ->update('tb_mas_classlist',$post);

    }
    
    function deleteFromClassList($id)
    {
        $this->db
			->where('intClassListID',$id)
			->delete('tb_mas_classlist_student');
    }
    
    function deleteStudentFromClassList($id,$sid)
    {
        $this->db
			->where(array('intClassListID'=>$id,'intStudentID'=>$sid))
			->delete('tb_mas_classlist_student');
    }
    
    function deleteSchedule($id,$sc = "strScheduleCode")
    {
        $this->db
			->where($sc,$id)
			->delete('tb_mas_room_schedule');
        
     
    }
    
    
    function deleteSubject($id)
    {
        
        $cl =$this->db
			->where('intSubjectID',$id)
            ->get('tb_mas_classlist')
            ->result_array();
        
        if(empty($cl)){
        
            $this->db
			->where('intID',$id)
			->delete('tb_mas_subjects');
        
            return true;
        }
        else
            return false;
    
    }
    
    function deleteCurriculum($id)
    {
        
        $cl =$this->db
			->where('intCurriculumID',$id)
            ->get('tb_mas_users')
            ->result_array();
        
        if(empty($cl)){
        
            $this->db
			->where('intID',$id)
			->delete('tb_mas_curriculum');
        
            return true;
        }
        else
            return false;
    
    }
    
    function deleteCS($id)
	{
		$this->db
			->where('intClassListID',$id)
			->delete('tb_mas_classlist_student');
	}
    
    function deleteProgram($id)
	{
		$this->db
			->where('intProgramID',$id)
			->delete('tb_mas_programs');
	}
	
	
}