<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_fetcher extends CI_Model {
	
	
	function fetch_table($table,$order=null,$limit=null,$where=null)
	{				
		
		if($order!=null)
			$this->db->order_by($order[0],$order[1]);
		elseif($table == 'tb_mas_content')
			$this->db->order_by('dteStart','desc');
        elseif($table == 'tb_mas_programs')
			$this->db->where('enumEnabled !=',0);
        if($limit!=null)
			$this->db->limit($limit);
			
		if($where!=null)
			$this->db->where($where);
		
		$data =  $this->db
						->get($table)
						->result_array();
						
		return $data;
						
	}

    function fetch_single_entry($table,$id,$label = "intID"){
        return $this->db->where(array($label => $id))->get($table)->first_row('array');
    }
    
    function getCourseName($id)
    {
        $course = $this->db->where(array('intProgramID'=>$id))->get('tb_mas_programs')->first_row('array');
        
        $desc = $course['strProgramDescription'];
            
        if($course['strMajor']!="")
            $desc .=" ".$course['strMajor'];
        
        return $desc;
    }

    function getDefaultTuitionYearID()
    {
        $tuition = $this->db->where(array('isDefault'=>1))->get('tb_mas_tuition_year')->first_row('array');        
        return $tuition['intID'];
    }
    
    function getDefaultTuitionYear()
    {
        return $this->db->where(array('isDefault'=>1))->get('tb_mas_tuition_year')->first_row('array');        
        
    }

    function getDefaultTuitionYearIDShs()
    {
        $tuition = $this->db->where(array('isDefaultShs'=>1))->get('tb_mas_tuition_year')->first_row('array');        
        return $tuition['intID'];
    }
    
    function getDefaultTuitionYearShs()
    {
        return $this->db->where(array('isDefaultShs'=>1))->get('tb_mas_tuition_year')->first_row('array');        
        
    }
    
    function getCourseCode($id)
    {
        $course = $this->db->where(array('intProgramID'=>$id))->get('tb_mas_programs')->first_row('array');
        
        //$desc = $course['strProgramDescription'];
        $courseCode = $course['strProgramCode'];
//        
//        if($course['strMajor']!="")
//            $desc .=" ".$course['strMajor'];
//        
        return $courseCode;
    }
    
    
    function getGeneralDesc($table,$id,$key,$field)
    {
        $item = $this->db->where(array($key=>$id))->get($table)->first_row('array');
        $desc = $item[$field];
        return $desc;
    }
    
    function getFacultyOnlineUsername()
    {
        $ret = array();
        
        $users = $this->db
                    ->select('intID,strFirstname,strLastname,intIsOnline')
                    ->from('tb_mas_faculty')
                    ->where(array('intIsOnline !='=>'0000-00-00 00:00:00'))
                    ->get()
                    ->result_array();
        
        foreach($users as $user)
        {
            $datetime1 = strtotime($user['intIsOnline']);
            $datetime2 = strtotime(date("Y-m-d H:i:s"));
            $interval  = abs($datetime2 - $datetime1);
            $minutes   = round($interval / 60);
            
            if($minutes < 1)
                $ret[] = $user;
        }
        
        return $ret;
        
    }   
    
    function getClassListStudentsStPortal($id,$classlist) 
    {
               
        return  $this->db
                     ->select("tb_mas_classlist_student.intCSID,strCode,strSection , intSubjectID, year, sub_section, strClassName,intClasslistID, intLab, floatPrelimGrade, floatMidtermGrade, floatFinalsGrade, tb_mas_subjects.strDescription,tb_mas_classlist_student.floatFinalGrade as v3,intFinalized,enumStatus,strRemarks,tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname, tb_mas_subjects.strUnits, tb_mas_subjects.intBridging, tb_mas_classlist.intID as classlistID, tb_mas_subjects.intID as subjectID, tb_mas_classlist.intFinalized")
                     ->from("tb_mas_classlist_student")
            
                    ->where(array("intStudentID"=>$id,"strAcademicYear"=>$classlist))
                        
                        
                     ->join('tb_mas_classlist', 'tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->order_by('strCode','asc')   
                     ->get()
                     ->result_array();
        
    }
    function getSyStudentEnrolled($id,$enrolled=1) 
    {
               
        return  $this->db
                     ->select("tb_mas_sy.intID,tb_mas_sy.enumSem,tb_mas_sy.strYearStart , tb_mas_sy.strYearEnd, tb_mas_registration.intStudentID,tb_mas_registration.intROG")
                     ->from("tb_mas_sy")
                    ->where(array("intStudentID"=>$id,"intROG"=>$enrolled))

                     ->join('tb_mas_registration', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                     ->order_by('intID','asc')   
                     ->get()
                     ->result_array();
        
    }
    
    function count_classlist($submitted=1)
    {
        $sem  = $this->get_active_sem();
        return $this->db
                    ->get_where('tb_mas_classlist',array('strAcademicYear'=>$sem['intID'],'intFinalized'=>$submitted))
                    ->num_rows();
    }   

    function count_latest_block($course,$sem)
    {
        $term = switch_num_rev_search($sem['enumSem']);
        $year = $sem['strYearStart'];        
        $res = $this->db
        ->select('blockSection')
        ->where(array(
            'strStudentNumber LIKE' => 'C%'.$year.'-'.$term.'%',
            'intProgramID' => $course
        ))
        ->order_by('blockSection','desc')
        ->get('tb_mas_users')
        ->first_row('array');

        if($res)
            return $this->db
                    ->get_where('tb_mas_users',array('blockSection'=>$res['blockSection']))
                    ->num_rows();
        else
            return false;
    }
    
    function fetch_table_fields($fields,$table)
    {
        return $this->db
                    ->select($fields)
                    ->from($table)
                    ->get()
                    ->result_array();
    }
    
    function messageExists($messageID,$userID)
    {
        $array = $this->db
             ->get_where('tb_mas_message_user',array('intFacultyID'=>$userID,'intMessageID'=>$messageID))
             ->result_array();
        
        if(empty($array))
            return false;
        else
            return true;
    }
    
    function getMessage($id)
    {
      $data =  $this->db
             ->select('tb_mas_system_message.intID as intID, strMessage, strSubject, dteDate,intFacultyIDSender, strFirstname, strLastname, intMessageID')
            ->join('tb_mas_system_message','tb_mas_message_user.intMessageID = tb_mas_system_message.intID')
             ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_message_user.intFacultyIDSender')
             ->where(array('intMessageUserID'=>$id))
             ->get('tb_mas_message_user')
             ->result_array();
        
        return current($data);
                     
    }
    
    function getMessages($id)
    {
      $data =  $this->db
             ->select('tb_mas_system_message.intID as intID, strMessage, strSubject, dteDate,intFacultyIDSender, strFirstname, strLastname, intMessageID,intMessageUserID')
             ->join('tb_mas_system_message','tb_mas_message_user.intMessageID = tb_mas_system_message.intID')
             ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_message_user.intFacultyIDSender')
             ->where(array('intFacultyID'=>$id,'intRead'=>0,'intTrash'=>0))
             ->order_by('dteDate','desc ')
             ->limit(8)
             ->get('tb_mas_message_user')
             ->result_array();
        
        return $data;
                     
    }
    
    function getReplyThread($id)
    {
         $reply = $this->db
                     ->select('strReplyMessage, dteReplied, strFirstname, strLastname , tb_mas_faculty.intID as intFacultyID, intReplyThreadID')
                     ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_reply_thread.intFacultyID')
                     ->where(array('intMessageID'=>$id))
                     ->order_by('dteReplied','asc')
                     ->get('tb_mas_reply_thread')
                     ->result_array();
        return $reply;
    }
    
    function getItem($table,$id,$label ="intID")
    {
        if($table == "tb_mas_faculty")
            return  current($this->db->get_where($table,array('intEmpID'=>$id))->result_array());
        
        return  current($this->db->get_where($table,array($label=>$id))->result_array());
                     
    }
    
    
    function getSubjectsNotInCurriculum($id)
    {
        $bucket = "SELECT intID,strCode,strDescription FROM tb_mas_subjects WHERE intID NOT IN (SELECT intSubjectID from tb_mas_curriculum_subject WHERE intCurriculumID = ".$id.") ORDER BY strCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }
    
    function getRoomsNotSelected($id)
    {
        $bucket = "SELECT tb_mas_classrooms.* FROM tb_mas_classrooms WHERE intID NOT IN (SELECT intRoomID from tb_mas_room_subject WHERE intSubjectID = ".$id.") ORDER BY strRoomCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }
    
    function getRoomsSelected($id,$type=null)
    {
        $bucket = "SELECT tb_mas_classrooms.* FROM tb_mas_classrooms WHERE intID IN (SELECT intRoomID from tb_mas_room_subject WHERE intSubjectID = ".$id.")";  
        
        if($type!=null)
            $bucket .= " AND enumType = '".$type."' ";
        
        $bucket .= "ORDER BY strRoomCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }
    
    function getSelectedDays($id)
    {
        return $this->db
                    ->select('strDays')
                    ->from('tb_mas_days')
                    ->where(array('intSubjectID'=>$id))
                    ->get()
                    ->result_array();
    }   
    
    function getPrereq($id,$type=null)
    {
        $bucket = "SELECT tb_mas_subjects.*, program, tb_mas_prerequisites.intID as prereq_subject_id FROM tb_mas_subjects JOIN tb_mas_prerequisites ON tb_mas_subjects.intID = tb_mas_prerequisites.intPrerequisiteID  WHERE tb_mas_prerequisites.intSubjectID = ".$id." ";  
        
        if($type!=null)
            $bucket .= " AND enumType = '".$type."' ";
        
        $bucket .= "ORDER BY strCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }

    function getPrereqEq($id,$type=null)
    {
        $bucket = "SELECT tb_mas_subjects.* FROM tb_mas_subjects WHERE intID IN (SELECT intEquivalentID from tb_mas_equivalents WHERE intSubjectID = ".$id.")";  
        
        if($type!=null)
            $bucket .= " AND enumType = '".$type."' ";
        
        $bucket .= "ORDER BY strCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }
    
    function getSubjectsNotSelected($id,$type=null)
    {
        $bucket = "SELECT tb_mas_subjects.* FROM tb_mas_subjects WHERE intID NOT IN (SELECT intPrerequisiteID from tb_mas_prerequisites WHERE intSubjectID = ".$id.")";  
        
        if($type!=null)
            $bucket .= " AND enumType = '".$type."' ";
        
        $bucket .= "ORDER BY strCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }

    function getSubjectsNotSelectedEquivalent($id,$type=null)
    {
        $bucket = "SELECT tb_mas_subjects.* FROM tb_mas_subjects WHERE intID NOT IN (SELECT intEquivalentID from tb_mas_equivalents WHERE intSubjectID = ".$id.")";  
        
        if($type!=null)
            $bucket .= " AND enumType = '".$type."' ";
        
        $bucket .= "ORDER BY strCode ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        return $subjects;
    }

    function checkIfStudentNew($studentID){

        return $this->db
                    ->select('intCSID')
                    ->from('tb_mas_classlist_student')
                    ->where('intStudentID',$studentID)
                    ->get()
                    ->num_rows();
        
    }
    
    function getRequiredSubjects($studentID,$curriculumID,$sem=null,$year=null)
    {
        $bucket = "SELECT tb_mas_subjects.intID,strCode,strDescription FROM tb_mas_subjects JOIN tb_mas_curriculum_subject ON tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID JOIN tb_mas_curriculum on tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID JOIN tb_mas_classlist on tb_mas_classlist.intSubjectID = tb_mas_subjects.intID WHERE tb_mas_subjects.intID NOT IN (SELECT intSubjectID from tb_mas_classlist_student  JOIN tb_mas_classlist ON intClassListID = tb_mas_classlist.intID WHERE intStudentID = ".$studentID." AND strRemarks = 'Passed') AND tb_mas_subjects.intID NOT IN (SELECT intSubjectID from tb_mas_credited_grades WHERE intStudentID =".$studentID.") AND  tb_mas_curriculum.intID = '".$curriculumID."' ";
 
        if($sem!=null && $year!=null)
            $bucket .= "AND tb_mas_curriculum_subject.intYearLevel = ".$year." AND tb_mas_curriculum_subject.intSem = ".$sem." ";
        elseif($sem!=null)
            $bucket .= "AND tb_mas_classlist.strAcademicYear = ".$sem." ";

        
        
        $bucket .= "GROUP BY tb_mas_classlist.intSubjectID ORDER BY tb_mas_curriculum_subject.intYearLevel ASC, tb_mas_curriculum_subject.intSem ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        //print_r($subjects);
        $ret = array();
        
        //PREREQUISITES CODE----------------------------------------------------------------------------
        
        foreach($subjects as $subj)
        {
            // $add = true;
            
            // $r = $this->db
            //           ->get_where('tb_mas_prerequisites',array('intSubjectID'=>$subj['intID']))
            //           ->result_array();
            
            // if(!empty($r))
            // {
            //     foreach($r as $res){
            //         $s = $this->db
            //           ->select('tb_mas_classlist_student.intCSID')
            //           ->from('tb_mas_classlist_student')
            //           ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_classlist_student.intClassListID')
            //           ->where(array('intSubjectID'=>$res['intPrerequisiteID'],'strRemarks'=>'Passed','intStudentID'=>$studentID))
            //           ->get()
            //           ->result_array();
                    
            //         if(empty($s))
            //         {
            //             $add = false;
            //             break;
            //         }
                    
            //     }
                
                
            // }
            
            // if($add)
            $ret[] = $subj;
                    
        }
        return $ret;

    }

    function getOfferedSubjects($studentID,$curriculumID,$sem=null,$year=null)
    {
        $bucket = "SELECT tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,
                            tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription,tb_mas_subjects.intLab, tb_mas_subjects.intLectHours 
                            FROM tb_mas_subjects 
                            JOIN tb_mas_curriculum_subject ON tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID 
                            JOIN tb_mas_curriculum on tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID 
                            JOIN tb_mas_classlist on tb_mas_classlist.intSubjectID = tb_mas_subjects.intID 
                            WHERE tb_mas_subjects.intID 
                            NOT IN (SELECT intSubjectID from tb_mas_classlist_student  JOIN tb_mas_classlist ON intClassListID = tb_mas_classlist.intID WHERE intStudentID = ".$studentID." AND strRemarks = 'Passed') 
                            AND tb_mas_subjects.intID NOT IN (SELECT intSubjectID from tb_mas_credited_grades WHERE intStudentID =".$studentID.") AND  tb_mas_curriculum.intID = '".$curriculumID."' ";
 
        if($sem!=null && $year!=null)
            $bucket .= "AND tb_mas_curriculum_subject.intYearLevel = ".$year." AND tb_mas_curriculum_subject.intSem = ".$sem." ";
        elseif($sem!=null)
            $bucket .= "AND tb_mas_classlist.strAcademicYear = ".$sem." ";

        
        
        $bucket .= "GROUP BY tb_mas_classlist.intSubjectID ORDER BY tb_mas_curriculum_subject.intYearLevel ASC, tb_mas_curriculum_subject.intSem ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        //print_r($subjects);
       //PREREQUISITES CODE----------------------------------------------------------------------------
        
       foreach($subjects as $subj)
       {
           $add = true;
           
           $r = $this->db
                     ->get_where('tb_mas_prerequisites',array('intSubjectID'=>$subj['intID']))
                     ->result_array();
           
           if(!empty($r))
           {
               foreach($r as $res){
                   $s = $this->db
                     ->select('tb_mas_classlist_student.intCSID')
                     ->from('tb_mas_classlist_student')
                     ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_classlist_student.intClassListID')
                     ->where(array('intSubjectID'=>$res['intPrerequisiteID'],'strRemarks'=>'Passed','intStudentID'=>$studentID))
                     ->get()
                     ->result_array();
                   
                   if(empty($s))
                   {
                       $add = false;
                       break;
                   }
                   
               }
               
               
           }
           
           if($add)
               $ret[] = $subj;
                   
       }
       return $ret;

    }

    function countStudentsInClasslist($id)
    {
        return $this->db
                    ->select('intCSID')
                    ->from('tb_mas_classlist_student')
                    ->where('intClassListID',$id)
                    ->get()
                    ->num_rows();
    }
    
    function getSubjectsInCurriculum($id)
    {
        $subjects = $this->db
                         ->select('tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription,tb_mas_subjects.intLab, tb_mas_subjects.intLectHours,tb_mas_subjects.strUnits,include_gwa')
                         ->from('tb_mas_curriculum_subject')
                         ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                         ->where('tb_mas_curriculum_subject.intCurriculumID',$id)
                         ->order_by('intYearLevel asc, intSem asc, strCode asc')
                         ->get()
                         ->result_array();
        
        return $subjects;
    }

    function getSubjectsInCurriculumAlphabetical($id)
    {
        $subjects = $this->db
                         ->select( 'tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription,tb_mas_subjects.intLab, tb_mas_subjects.intLectHours')
                         ->from('tb_mas_curriculum_subject')
                         ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                         ->where('tb_mas_curriculum_subject.intCurriculumID',$id)
                         ->order_by('strCode asc')
                         ->get()
                         ->result_array();
        
        return $subjects;
    }

    function getSubjectsInCurriculumAvailable($id,$sem)
    {
        $subjects = $this->db
                         ->select( 'tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription,tb_mas_subjects.intLab, tb_mas_subjects.intLectHours')
                         ->from('tb_mas_curriculum_subject')
                         ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                         ->join('tb_mas_classlist','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                         ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$id,'tb_mas_classlist.strAcademicYear'=>$sem))
                         ->group_by('tb_mas_curriculum_subject.intSubjectID')
                         ->order_by('strCode asc')
                         ->get()
                         ->result_array();                     
        
        return $subjects;
    }

    function getSubjectsInCurriculumMain($id)
    {
        $subjects = $this->db
                        ->select( 'tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription')
                        ->from('tb_mas_curriculum_subject')
                        ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID')
                        ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$id, 'tb_mas_subjects.intBridging'=>0))
                        ->order_by('intYearLevel asc,intSem asc, strCode asc')
                        ->get()
                        ->result_array();        
        
        
        return $subjects;
    }

    function getSubjectsInCurriculumEqu($id){

        $equivalent = $this->db
                        ->select('tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intSubjectID as mainSubjectID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription')
                        ->from('tb_mas_curriculum_subject')
                        ->join('tb_mas_subjects','tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                        ->where('tb_mas_curriculum_subject.intCurriculumID',$id)
                        ->order_by('intYearLevel asc,intSem asc, strCode asc')
                        ->get()
                        ->result_array();

        return $equivalent;

    }
     
    function getSectionsSubject($subjid,$sem)
    {
        $sections = $this->db
                         ->select('strSection,intID')
                         ->from('tb_mas_classlist')
                         ->where(array('intSubjectID'=>$subjid,'strAcademicYear'=>$sem))
                         ->order_by('strSection asc')
                         ->get()
                         ->result_array();
        
        return $sections;
    }
    
    function getSubjectsInCurriculumWithSections($curriculumID,$sem,$studentID)
    {
        
        $bucket = "SELECT tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode,tb_mas_subjects.strUnits,tb_mas_subjects.intID as intSubjectID,tb_mas_subjects.strDescription FROM tb_mas_subjects JOIN tb_mas_curriculum_subject ON tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID JOIN tb_mas_curriculum on tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID JOIN tb_mas_classlist ON tb_mas_subjects.intID = tb_mas_classlist.intSubjectID WHERE tb_mas_subjects.intID NOT IN (SELECT intSubjectID from tb_mas_classlist_student  JOIN tb_mas_classlist ON intClassListID = tb_mas_classlist.intID WHERE intStudentID = ".$studentID." AND strRemarks = 'Passed') AND tb_mas_subjects.intID NOT IN (SELECT intSubjectID from tb_mas_credited_grades WHERE intStudentID =".$studentID.") AND  tb_mas_curriculum.intID = '".$curriculumID."' AND tb_mas_classlist.strAcademicYear = ".$sem." ";
            
        //for prerquisites
        //AND (tb_mas_subjects.intPrerequisiteID IN (SELECT `intSubjectID` from tb_mas_classlist_student  JOIN tb_mas_classlist ON intClassListID = tb_mas_classlist.intID WHERE intStudentID = ".$studentID." AND strRemarks = 'Passed') OR tb_mas_subjects.intPrerequisiteID = 0 )
        
        $bucket .= "GROUP BY tb_mas_classlist.intSubjectID ORDER BY tb_mas_curriculum_subject.intYearLevel ASC, tb_mas_curriculum_subject.intSem ASC"; 
        
        $subjects = $this->db
             ->query($bucket)
             ->result_array();
        
        //echo $this->db->last_query();
        //print_r($subjects);
        return $subjects;
    }
    
    function countUnitsInCurriculum($id)
    {
        $subjects = $this->db
                         ->select( 'SUM(tb_mas_subjects.strUnits) as totalUnits')
                         ->from('tb_mas_subjects')
                         ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                         ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$id, 'tb_mas_subjects.intBridging'=>'0'))
                         ->group_by('tb_mas_curriculum_subject.intCurriculumID')
                         ->get()
                         ->result_array();
        if($subjects)
            return $subjects[0]['totalUnits'];
        else
            return 0;
    }
    
    function count_table_contents($table,$category = null,$where=null,$group=null)
    {
            if($category!=null)
                $this->db
				     ->where('enumCat',$category);		
            if($where!=null)
                $this->db
				     ->where($where);		
            if($group!=null)
                $this->db->group_by($group); 
        
            return $this->db
                        ->count_all_results($table);
        
        
        
    }
    
    function count_sent_items($user)
    {
            	
            // $this->db->select("intMessageUserID")
            //          ->where(array("intTrash"=>"0","intFacultyIDSender"=>$user))
            //           ->group_by("intMessageID"); 
        
            // $result = $this->db
            //       ->get("tb_mas_message_user")->result_array();
        
            return 0;
        
        
        
    }
    
    function fetch_student_data($table,$order=null,$limit=null,$where=null)
	{				
		
        $this->db->select('intID,strFirstname,strMiddlename,strLastname,strCourse,dteCreated,strSection');
		if($order!=null)
			$this->db->order_by($order[0],$order[1]);
		elseif($table == 'tb_mas_content')
			$this->db->order_by('dteStart','desc');
		
		if($limit!=null)
			$this->db->limit($limit);
			
		if($where!=null)
			$this->db->where($where);
		
		$data =  $this->db
						->get($table)
						->result_array();
						
		return $data;
						
	}
    function search_for_students($search_string)
    {
        $this->db->where('strFirstname',$search_string)
                 ->or_where('strLastname',$search_string)
                 ->get('tb_mas_users');
        
        return $this->db->result_array();
        
        
    }
    
    function fetch_students($table,$order=null,$limit=20,$where=null,$offset)
	{				
		
		if($order!=null)
			$this->db->order_by($order[0],$order[1]);
		elseif($table == 'tb_mas_content')
			$this->db->order_by('dteStart','desc');
		
		if($limit!=null)
			$this->db->limit($limit,$offset);
			
		if($where!=null)
			$this->db->where($where);
		
		$data =  $this->db
						->get($table)
						->result_array();
						
		return $data;
						
	}
    
    
    function fetch_logs($start,$end,$cat)
    {
        $this->db
             ->select('strFirstname,strLastname,strAction,strCategory,dteLogDate,strColor')
             ->from('tb_mas_logs')
             ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_logs.intFacultyID');
           
        if($start == null || $start == 0)    
            $this->db->limit(20);
        else{
            $end .=" 23:59:59";
           $this->db->where(array('dteLogDate >='=>$start,'dteLogDate <='=>$end));
        }

        if($cat != null){
            $this->db->where(array('strCategory LIKE'=>$cat));
        }
        return    $this->db
                ->order_by('dteLogDate','desc')
                ->get()
                ->result_array();
    }
    
    function fetch_transactions($start,$end)
    {
        if($start != null)
        {
            $this->db
                 ->select('tb_mas_transactions.*,tb_mas_users.strFirstname,tb_mas_users.strLastname,tb_mas_users.intID as studentID')
                 ->from('tb_mas_transactions')
                 ->join('tb_mas_registration','tb_mas_registration.intRegistrationID = tb_mas_transactions.intRegistrationID')
                 ->join('tb_mas_users','tb_mas_users.intID = tb_mas_registration.intStudentID');
            
            
            
                $end .=" 23:59:59";
               $this->db->where(array('dtePaid >='=>$start,'dtePaid <='=>$end));
            
            return    $this->db
                    ->order_by('dtePaid desc,intORNumber asc')
                    ->get()
                    ->result_array();
        }
        else
            return array();
    }
    
    function get_active_sem()
    {
        $current_term = $this->db->get_where('tb_mas_system_settings',array('setting_name'=>'current_term'))->first_row();
        return current($this->db->get_where('tb_mas_sy',array('intID'=>$current_term->value))->result_array());
        
    }

    function get_all_past_terms($type,$year,$term){
        return $this->db->where(array('term_student_type'=>$type,'start_of_classes <=' => date("Y-m-d"),'enumSem >='=>$term,'strYearStart >='=>$year))
                        ->order_by("strYearStart ASC, enumSem ASC")
                        ->get('tb_mas_sy')
                        ->result_array();
    }

    function get_active_sem_shs()
    {
        $current_term = $this->db->get_where('tb_mas_system_settings',array('setting_name'=>'shs_default_term'))->first_row();
        return current($this->db->get_where('tb_mas_sy',array('intID'=>$current_term->value))->result_array());
        
    }
    
    function get_processing_sem()
    {
        $current_term = $this->db->get_where('tb_mas_system_settings',array('setting_name'=>'application_term'))->first_row();
        return current($this->db->get_where('tb_mas_sy',array('intID'=>$current_term->value))->result_array());
        
    }

    function get_processing_sem_shs()
    {
        $current_term = $this->db->get_where('tb_mas_system_settings',array('setting_name'=>'shs_default_application'))->first_row();
        return current($this->db->get_where('tb_mas_sy',array('intID'=>$current_term->value))->result_array());
        
    }

    function get_active_PrelimPeriod($id)
    {
        return current($this->db->get_where('tb_mas_sy',array('enumGradingPeriod'=>'active', 'intID'=>$id))->result_array());
    }
    function get_active_MidtermPeriod($id)
    {
        return current($this->db->get_where('tb_mas_sy',array('enumMGradingPeriod'=>'active', 'intID'=>$id))->result_array());
    }
    function get_active_FinalsPeriod($id)
    {
        return current($this->db->get_where('tb_mas_sy',array('enumFGradingPeriod'=>'active', 'intID'=>$id))->result_array());
    }
  
    function get_prev_sem($sem,$id)
    {
        
        $active = $this->db->get_where('tb_mas_sy',array('intID'=>$sem))->first_row('array');
        
        if($active['enumSem'] != "1st")
        {
            $sem = switch_num(switch_num_rev($active['enumSem']) - 1);
            $yearStart = $active['strYearStart'];
            $yearEnd = $active['strYearEnd'];
        }
        else
        {   
            if($active['term_student_type'] == "shs")         
                $sem = "2nd";
            else
                $sem = "3rd";
            $yearStart = $active['strYearStart'] - 1;
            $yearEnd = $active['strYearEnd'] - 1;
        }
        
        return $this->db
                ->select('tb_mas_sy.*')
                ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_registration.intAYID')
                ->where(array('intStudentID'=>$id,'enumSem'=>$sem,'strYearStart'=>$yearStart,'strYearEnd'=>$yearEnd,'term_student_type'=>$active['term_student_type']))
                ->get('tb_mas_registration')
                ->first_row('array');
        
        
    }
    
    function getClassroom($id)
    {
        return current($this->db->get_where('tb_mas_classrooms',array('intID'=>$id))->result_array());
        
    }
    
     function get_sem_by_id($id)
    {
        return current($this->db->get_where('tb_mas_sy',array('intID'=>$id))->result_array());
        
    }
    
    function fetch_classlists($limit=null,$sem = false,$sem_sel=null)
    {
        $faculty_id = $this->session->userdata("intID");
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, strSection, year, sub_section, intFacultyID,intSubjectID,strClassName,strCode,intFinalized,strAcademicYear,enumSem,strYearStart,strYearEnd,count(tb_mas_classlist_student.intCSID) as numStudents")
                     ->from("tb_mas_classlist")
                     ->where(array("intFacultyID"=>$faculty_id));
                    
                    if($sem_sel!=null)
                        $this->db->where(array('strAcademicYear'=>$sem_sel));
        
                     $this->db->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                    ->join('tb_mas_classlist_student','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID','left outer');
                if($limit != null)
                    $this->db->limit($limit);
                 return $this->db
                        ->group_by('tb_mas_classlist.intID')
                        ->get()
                        ->result_array();
        
    }
    
    
    
    function fetch_classlists_all($limit=null,$sem_sel=null)
    {
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, strSection, year, sub_section, intFacultyID,intSubjectID,strClassName,strCode,intFinalized,strAcademicYear,strFirstname,strLastname,strYearStart,strYearEnd,enumSem, COUNT(tb_mas_classlist_student.intStudentID) as students")
                     ->from("tb_mas_classlist")
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_classlist_student', 'tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID', 'Left')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear');
                    
                if($sem_sel!=null)
                        $this->db->where(array('strAcademicYear'=>$sem_sel));
                if($limit != null)
                    $this->db->limit($limit);
                 
                return $this->db
                        ->group_by('tb_mas_classlist.intID')
                        ->get()
                        ->result_array();
    }
    
    function fetch_classlists_dept($dept,$admin=false,$limit=null,$sem_sel=null)
    {
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, strSection, year, sub_section, intFacultyID,intSubjectID,strClassName,strCode,intFinalized,strAcademicYear,strFirstname,strLastname,strYearStart,strYearEnd,enumSem")
                     ->from("tb_mas_classlist")
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear');
                $where = array();
                if($sem_sel!=null)
                        $where['strAcademicYear'] = $sem_sel;
                if($limit != null)
                    $this->db->limit($limit);
                if(!$admin)
                    $where['tb_mas_subjects.strDepartment'] = $dept;
                 
                $this->db->where($where);
                return $this->db 
                        ->get()
                        ->result_array();
    }
    
    function fetch_classlists_unassigned($sem_sel=null,$limit=null,$dept)
    {
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, slots, strSection, year, sub_section, intFacultyID,intSubjectID,strClassName,strCode,strDescription,intFinalized,strAcademicYear,strFirstname,strLastname,strYearStart,strYearEnd,enumSem")
                     ->from("tb_mas_classlist")
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear');
               
                    //$this->db->where(array('strAcademicYear'=>$sem_sel,'intFacultyID'=>999,'tb_mas_subjects.strDepartment'=>$dept));
                    $this->db->where(array('strAcademicYear'=>$sem_sel,'intFacultyID'=>999));
                    $this->db->order_by('strCode','asc');
            if($limit != null)
                    $this->db->limit($limit);
                 
                return $this->db 
                        ->get()
                        ->result_array();
    }
    
    function fetch_classlist_by_id($limit=null,$id)
    {
        $faculty_id = $this->session->userdata("intID");
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, grading_system, midterm_start, midterm_end, final_start, final_end, grading_system_id, year, sub_section, strSection, slots, intFacultyID,intSubjectID,strClassName,strCode,intFinalized,strAcademicYear,strFirstname,strLastname,strYearStart,strYearEnd,enumSem,tb_mas_classlist.strUnits,strSignatory1Name,strSignatory2Name,strSignatory1Title,strSignatory2Title,tb_mas_subjects.strDepartment,intWithPayment,classType,term_label,intMajor")
                     ->from("tb_mas_classlist")
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear');
                
                $this->db->where(array('tb_mas_classlist.intID'=>$id));
                
                if($limit != null)
                    $this->db->limit($limit);
                 return current($this->db 
                        ->get()
                        ->result_array());
    }

    function get_classlist_remaining_slots($classlist_id){

        $classlist = $this->db->get_where('tb_mas_classlist',array('intID'=>$classlist_id))->first_row('array');
        $slots = $this->db
                ->select('tb_mas_classlist_student.intCSID')                                
                ->from('tb_mas_classlist_student')
                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                ->where(array('intClassListID'=>$classlist_id))
                ->get()
                ->num_rows();

        return  $classlist['slots'] - $slots;
    }
    
    function fetch_classlist_by_subject($subject_id,$sem)
    {
        $ret = [];
        $classlists = $this->db
                    ->select('tb_mas_classlist.*')
                    ->from('tb_mas_classlist')
                    
                    ->where(array('intSubjectID'=>$subject_id,'strSection !='=>'','strAcademicYear'=>$sem))
                   
                    ->get()
                    ->result_array();

        foreach($classlists as $classlist){
            $slots = $this->db
                ->select('tb_mas_classlist_student.intCSID')                                
                ->from('tb_mas_classlist_student')
                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                ->where(array('intClassListID'=>$classlist['intID']))
                ->get()
                ->num_rows();

            $classlist['slots_available'] = $classlist['slots'] - $slots;
            $ret[] = $classlist;
        }

        return $ret;
    }
    
    function fetch_classlist_by_subject_no_count($subject_id,$sem)
    {
        return $this->db
                    ->select('tb_mas_classlist.*')
                    ->from('tb_mas_classlist')
                    ->where(array('intSubjectID'=>$subject_id,'strSection !='=>'','strAcademicYear'=>$sem))
                    ->order_by('strSection','asc')
                    ->get()
                    
                    ->result_array();
    }
    
    
    function fetch_classlist_id($id)
    {
        return current($this->db
                    ->get_where('tb_mas_classlist',array('intID'=>$id))
                    ->result_array());
    }
    
    
    function getAverageGrade($id)
    {
        $score = $this->db->get_where('tb_mas_classlist_student',array('intCSID'=>$id))->first_row();
        $average = ($score->floatPrelimGrade+$score->floatMidtermGrade+$score->floatFinalGrade)/3;
        return $average;
    }
    
    function getCS($studentId,$classListId)
    {
        return  $this->db->get_where('tb_mas_classlist_student',array('intStudentID'=>$studentId,'intClassListID'=>$classListId))->result_array();
                     
    }   
    
    function getStudentSection($course,$year,$section)
    {
        return  $this->db->get_where('tb_mas_users',array('intProgramID'=>$course,'dteCreated'=>$year."-01-01",'strSection'=>$section))->result_array();
                     
    }

    function getStudentsByTypeOfClass($type,$start,$end){
            

    }
    
    function getStudents($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem=0,$type=0)
    {
        
        
        $select = "tb_mas_users.*,strProgramCode, strMajor, short_name, name as blockName, strProgramDescription,tb_mas_registration.intYearLevel,dteRegistered, tb_mas_curriculum.strName as curriculumName, type_of_class";

        $this->db
            ->select($select)
            ->from('tb_mas_users')
            ->join('tb_mas_programs','tb_mas_users.intProgramID = tb_mas_programs.intProgramID')
            ->join('tb_mas_block_sections','tb_mas_users.blockSection = tb_mas_block_sections.intID','left')
            ->join('tb_mas_curriculum','tb_mas_users.intCurriculumID = tb_mas_curriculum.intID','left')
            ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID','left')
            ->order_by('strLastname','asc');
        if($registered!=0 && $sem!=0){            
            switch($registered)
            {
                case 1:
                $this->db->where(array('tb_mas_registration.intAYID'=>$sem,'tb_mas_registration.intROG'=>0));
                break;
                case 2:
                $this->db->where(array('tb_mas_registration.intAYID'=>$sem,'tb_mas_registration.intROG'=>1));
                break;
                case 3:
                $this->db->where(array('tb_mas_registration.intAYID'=>$sem,'tb_mas_registration.intROG'=>2));
                break;
            }

            if($year!=0)
                $this->db->where('tb_mas_registration.intYearLevel',$year);
        }
        
        if($course!=0)
            $this->db->where('tb_mas_users.intProgramID',$course);
        if($type!=0){
            switch($type){
                case 1:
                    $this->db->where('tb_mas_users.student_type',"freshman");
                break;
                case 2:
                    $this->db->where('tb_mas_users.student_type',"transferee");
                break;
                case 3:
                    $this->db->where('tb_mas_users.student_type',"foreign");
                break;
                case 4:
                    $this->db->where('tb_mas_users.student_type',"second degree");
                break;                        

            }
        }
        if($regular!=0)
           if($regular == 1)
                $this->db->where('strAcademicStanding','regular');
            else if ($regular == 2)
                $this->db->where('strAcademicStanding','irregular');
            else
                $this->db->where('strAcademicStanding','new');
        
        if($gender!=0)
           if($gender == 1)
                $this->db->where('enumGender','male');
            else
                $this->db->where('enumGender','female');
            
        
        if($graduate!=0)
            if($graduate == 1)
                    $this->db->where('isGraduate',1);
                else
                    $this->db->where('isGraduate',0);
        
        
        
        if($scholarship!=0)
            if($scholarship == 1)
                $this->db->where('enumScholarship','paying');
            elseif($scholarship == 2)
                $this->db->where('enumScholarship','resident scholar');
            elseif($scholarship == 3)
                    $this->db->where('enumScholarship','7th district');
            elseif($scholarship == 4)
                    $this->db->where('enumScholarship','DILG scholar');
            elseif($scholarship == 5)
                    $this->db->where('enumScholarship','FREE HIGHER EDUCATION PROGRAM (R.A. 10931)');
        
        return $this->db
             ->get()
             ->result_array();
            
            
                     
    }
    function getStudentsNew($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem=0, $studNumStart=0, $studNumEnd=0)
    {
        
        $this->db
            ->select('tb_mas_users.*,strProgramCode')
            ->from('tb_mas_users')
            ->join('tb_mas_programs','tb_mas_users.intProgramID = tb_mas_programs.intProgramID')
            ->order_by('strLastname','asc');
        if($registered!=0 && $sem!=0){
            $this->db
                 ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID');
            switch($registered)
            {
                case 1:
                $this->db->where(array('tb_mas_registration.intAYID'=>$sem,'tb_mas_registration.intROG'=>0));
                break;
                case 2:
                $this->db->where(array('tb_mas_registration.intAYID'=>$sem,'tb_mas_registration.intROG'=>1));
                break;
                case 3:
                $this->db->where(array('tb_mas_registration.intAYID'=>$sem,'tb_mas_registration.intROG'=>2));
                break;
            }
        }
        
         if($course!=0)
            $this->db->where('tb_mas_users.intProgramID',$course);
        if($regular!=0)
           if($regular == 1)
                $this->db->where('strAcademicStanding','regular');
            else if ($regular == 2)
                $this->db->where('strAcademicStanding','irregular');
            else
                $this->db->where('strAcademicStanding','new');
        
        if($gender!=0)
           if($gender == 1)
                $this->db->where('enumGender','male');
            else
                $this->db->where('enumGender','female');
        
        if($year!=0)
            $this->db->where('intStudentYear',$year);
        
        if($graduate!=0)
            if($graduate == 1)
                    $this->db->where('isGraduate',1);
                else
                    $this->db->where('isGraduate',0);
        
        
        
        if($scholarship!=0)
            if($scholarship == 1)
                $this->db->where('enumScholarship','paying');
            elseif($scholarship == 2)
                $this->db->where('enumScholarship','resident scholar');
            elseif($scholarship == 3)
                    $this->db->where('enumScholarship','7th district');
            elseif($scholarship == 4)
                    $this->db->where('enumScholarship','DILG scholar');
            elseif($scholarship == 5)
                    $this->db->where('enumScholarship','FREE HIGHER EDUCATION PROGRAM (R.A. 10931)');
        
         if($studNumStart!=0 && $studNumEnd!=0)
        
            $this->db->where(array('tb_mas_users.strStudentNumber >='=>$studNumStart,'tb_mas_users.strStudentNumber <='=>$studNumEnd));
        
        return $this->db
             ->get()
             ->result_array();
            
            
                     
    }
    
    function getApplicantsExcel($course = 0,$appdate = 0,$gender = 0,$sem=0)
    {
        
        $this->db
            ->select('tb_mas_applications.*,refbrgy.brgyDesc,refcitymun.citymunDesc,refprovince.provDesc')
            ->from('tb_mas_applications')
            ->join('refbrgy','refbrgy.brgyCode = tb_mas_applications.strAppBrgy')
            ->join('refcitymun','refcitymun.citymunCode = tb_mas_applications.strAppCity')
            ->join('refprovince','refprovince.provCode = tb_mas_applications.strAppProvince')
                
            ->order_by('strLastname','asc');
        
         if($course!=0)
            $this->db->where('tb_mas_applications.enumCourse1',$course);

        if($gender!=0)
           if($gender == 1)
                $this->db->where('enumGender','male');
            else
                $this->db->where('enumGender','female');
        return $this->db
             ->get()
             ->result_array();
             
    }
    
    function getRegisteredStudents($ay)
    {
       
        return
        $this->db
             ->select('tb_mas_users.*,strProgramCode')
             ->from('tb_mas_users')
             ->join('tb_mas_programs','tb_mas_users.intProgramID = tb_mas_programs.intProgramID')
             ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
             ->where(array('intAYID'=>$ay))
             ->get()
             ->result_array();
            
    }
    
    function countStudentsByCourse($course)
    {
        return  count($this->db->get_where('tb_mas_users',array('intProgramID'=>$course,'isGraduate'=>0))->result_array());   
    }
    
    function getScholars($programID,$type,$ay)
    {
        return 
            count($this->db
             ->select('intRegistrationID')
             ->from('tb_mas_registration')
             ->where('intAYID = '.$ay.' AND tb_mas_registration.enumScholarship = \''.$type.'\' AND tb_mas_registration.intROG = 1 AND tb_mas_users.intProgramID = '.$programID)
             ->join('tb_mas_users','tb_mas_registration.intStudentID = tb_mas_users.intID')
             ->get()
             ->result_array());
    }
    
    function getSubjects($post = null)
    {
        if($post!=null){
            $courses =
                $this->db
                     ->select( 'tb_mas_subjects.intID,strCode,strDescription,strUnits,intLab,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem')
                     ->from('tb_mas_subjects')
                     ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                     ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID')
                     ->where(array('tb_mas_curriculum.intID'=>$post['intCurriculumID'],'tb_mas_curriculum_subject.intYearLevel'=>$post['intYearLevel'],'tb_mas_curriculum_subject.intSem'=>$post['intSem']))
                     ->get()
                     ->result_array();
                
                
               // $this->db->get_where('tb_mas_subjects',array('intYearLevel'=>$post['intYearLevel'],'intProgramID'=>$post['strCourse'],'intSem'=>$post['intSem']))->result_array();

            
        }
        else
        {
            $courses =  $this->db
                             ->get('tb_mas_subjects')
                             ->result_array();
        }
        return $courses;
        
    }
    
    
    
    function getSubjectsCurriculum($post = null)
    {
        if($post!=null){
            $courses =
                $this->db
                     ->select( 'tb_mas_subjects.intID,strCode,strDescription,strUnits,intLab,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem')
                     ->from('tb_mas_subjects')
                     ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                     ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID')
                     ->where(array('tb_mas_curriculum.intID'=>$post['intCurriculumID']))
                     ->get()
                     ->result_array();
                
                
               // $this->db->get_where('tb_mas_subjects',array('intYearLevel'=>$post['intYearLevel'],'intProgramID'=>$post['strCourse'],'intSem'=>$post['intSem']))->result_array();

            
        }
        return $courses;
        
    }
    
    function getSubjectsCurriculumSem($id,$sem,$year)
    {
            $courses =
                $this->db
                     ->select( 'tb_mas_subjects.intID,strCode,strDescription,strUnits,intLab,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem')
                     ->from('tb_mas_subjects')
                     ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                     ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID')
                     ->where(array('tb_mas_curriculum.intID'=>$id,'tb_mas_curriculum_subject.intSem'=>$sem,'tb_mas_curriculum_subject.intYearLevel'=>$year))
                     ->get()
                     ->result_array();
                
                
               // $this->db->get_where('tb_mas_subjects',array('intYearLevel'=>$post['intYearLevel'],'intProgramID'=>$post['strCourse'],'intSem'=>$post['intSem']))->result_array();

        return $courses;
        
    }
    
    function getFromSchedule($scode,$sem,$lab='lect',$day=null)
	{
        $this->db
		 	 ->where(array('strScheduleCode'=>$scode,'intSem'=>$sem,'enumClassType'=>$lab));
        
            if($day!=null)
                $this->db->where('strDay',$day);
        $d =
            $this->db
                 ->get('tb_mas_room_schedule')
                 ->first_row();
        
        if(empty($d))
            return 0;
        else
            return $d->intRoomSchedID;
	}
    
    function getSelectedSubjects($id)
    {
        
        $subjects =  $this->db
                         ->select('intSubjectID')
                         ->from('tb_mas_subjects_faculty')
                         ->where(array('intFacultyID'=>$id))   
                         ->get()
                         ->result_array();
        
        return $subjects;
        
    }
    
    function getStudent($id,$field = "intID")
    {
        $ret =  $this->db
                     ->select('tb_mas_users.*,tb_mas_programs.*,tb_mas_curriculum.strName, tb_mas_block_sections.name as block')
                     ->from('tb_mas_users')
                     ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID','left')
                     ->join('tb_mas_block_sections','tb_mas_block_sections.intID = tb_mas_users.preferedSection','left')   
                     ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_users.intCurriculumID','left')
                     ->where(array('tb_mas_users.'.$field => $id))
                     ->get()
                     ->first_row('array');

                     
                     if($ret){
                        $ret['dteBirthDate'] = date("M j, Y",strtotime($ret['dteBirthDate']));                             
                        $ret['dteCreated'] = isset($ret['dteCreated']) ? date("M j, Y",strtotime($ret['dteCreated'])): null;
                    }
                    return $ret;
                     
    }

    function getStudentExamQuestion($student_id, $field)
    {
        $ret =  $this->db
                     ->select('tb_mas_student_exam_answers.choice_selected, tb_mas_student_exam_answers.is_correct, tb_mas_questions.intID, tb_mas_questions.strTitle, tb_mas_questions.questionImage')
                     ->from('tb_mas_student_exam_answers')
                     ->join('tb_mas_student_exam','tb_mas_student_exam.student_id = tb_mas_student_exam_answers.student_id')
                     ->join('tb_mas_exam','tb_mas_exam.intID = tb_mas_student_exam.exam_id')
                     ->join('tb_mas_questions','tb_mas_questions.exam_id = tb_mas_exam.intID')
                     ->where(array('tb_mas_student_exam_answers.'.$field => $student_id))
                     ->get()
                     ->result_array();

        return $ret;
    }

    function getCurriculumIDByCourse($id){
        $course = $this->db->get_where('tb_mas_programs',array('intProgramID'=>$id))->row();        
        if($course->default_curriculum)
            return $course->default_curriculum;
        else{
            $temp = $this->db->get_where('tb_mas_curriculum',array('intProgramID'=>$id))->row();
            if($temp)
                return $temp->intID;
            else
                return 1;
        }
    }

    function getCurriculumByID($id){
        return $this->db->get_where('intID',$id)->get('tb_mas_curriculum')->first_row();        
    }
    
    function getApplicant($id)
    {
        return  current(
                $this->db
                     ->select('tb_mas_applications.*,refbrgy.brgyDesc,refcitymun.citymunDesc,refprovince.provDesc')
                     ->from('tb_mas_applications')
                     ->join('refbrgy','refbrgy.brgyCode = tb_mas_applications.strAppBrgy')
                     ->join('refcitymun','refcitymun.citymunCode = tb_mas_applications.strAppCity')
                     ->join('refprovince','refprovince.provCode = tb_mas_applications.strAppProvince')
                     ->where(array('tb_mas_applications.intApplicationID'=>$id))
                     ->get()
                     ->result_array());
                     
    }
    
    function getApplicationByCode($code)
    {
        
        $conf = $this->db->get_where('tb_mas_applications',array('strConfirmationCode LIKE'=>$code))->result_array();
        return current($conf);
        
    }
    
    function getExamInfo($id)
    {
        return  current(
                $this->db
                     ->select('*')
                     ->from('tb_mas_exam_info')
                     ->where(array('intApplicationID'=>$id))
                     ->get()
                     ->result_array());
                     
    }
    
    function assessCurriculum($studentID,$curriculumID)
    {
        $subjects =  $this->db
                    ->select('tb_mas_subjects.strCode,tb_mas_subjects.strDescription,tb_mas_classlist_student.floatPrelimGrade, tb_mas_classlist_student.floatMidtermGrade,tb_mas_classlist_student.floatFinalsGrade, min(floatFinalGrade) as floatFinalGrade,strRemarks,tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strUnits,tb_mas_classlist.strAcademicYear, tb_mas_sy.enumSem,tb_mas_sy.strYearStart,tb_mas_sy.strYearEnd,tb_mas_sy.intID as syID, tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname, tb_mas_classlist.intID as classListID')
                    ->from('tb_mas_curriculum_subject')
                    ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID')
                    ->join('tb_mas_classlist','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID','inner')
                    ->join('tb_mas_classlist_student','tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID','inner')
                    ->join('tb_mas_sy','tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                    ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                    ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID, 'tb_mas_subjects.intBridging'=>0))
                    ->order_by('strYearStart asc,enumSem asc, strCode asc')
                    ->group_by('tb_mas_subjects.strCode')
                    ->get()
                    ->result_array();

        $equivalent_grades =  $this->db
                        ->select('tb_mas_subjects.strCode,tb_mas_subjects.strDescription,tb_mas_classlist_student.floatPrelimGrade, tb_mas_classlist_student.floatMidtermGrade,tb_mas_classlist_student.floatFinalsGrade, min(floatFinalGrade) as floatFinalGrade,strRemarks,tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strUnits,tb_mas_sy.enumSem,tb_mas_sy.strYearStart,tb_mas_sy.strYearEnd,tb_mas_sy.intID as syID, tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname, tb_mas_classlist.intID as classListID')
                        ->from('tb_mas_curriculum_subject')
                        ->join('tb_mas_subjects','tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                        ->join('tb_mas_classlist','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID','inner')
                        ->join('tb_mas_classlist_student','tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID','inner')
                        ->join('tb_mas_sy','tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                        ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                        ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID, 'tb_mas_subjects.intBridging'=>0))
                        ->order_by('strYearStart asc,enumSem asc, strCode asc')
                        ->group_by('tb_mas_subjects.strCode')
                        ->get()
                        ->result_array();
        
        $credited = $this->db
                     ->select('tb_mas_subjects.strCode,tb_mas_credited_grades.floatFinalGrade,tb_mas_credited_grades.strRemarks,tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strUnits,tb_mas_credited_grades.intSYID as enumSem,tb_mas_credited_grades.intSYID as strYearStart,tb_mas_credited_grades.intSYID as strYearEnd,tb_mas_credited_grades.intSYID  as syID')
                     ->from('tb_mas_credited_grades')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_credited_grades.intSubjectID')
                     ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intCurriculumID = tb_mas_credited_grades.intCurriculumID','inner')
                     ->where(array('tb_mas_credited_grades.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID))
                     ->group_by('tb_mas_credited_grades.intID')
                     ->order_by('strYearStart asc,enumSem asc')
                     ->get()
                     ->result_array();
                     
        $merged = array_merge($subjects,$credited);
        $merged = array_merge($merged,$equivalent_grades);
        
        return $merged;
    }

    function assessCurriculumDept($studentID,$curriculumID)
    {
        $subjects =  $this->db
                     ->select('tb_mas_subjects.strCode,tb_mas_subjects.strDescription,tb_mas_subjects.intBridging,tb_mas_classlist_student.floatPrelimGrade, tb_mas_classlist_student.floatMidtermGrade,tb_mas_classlist_student.floatFinalsGrade, min(floatFinalGrade) as floatFinalGrade,strRemarks,tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strUnits,tb_mas_sy.enumSem,tb_mas_sy.strYearStart,tb_mas_sy.strYearEnd,tb_mas_sy.intID as syID, tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname, tb_mas_classlist.intID as classListID')
                     ->from('tb_mas_curriculum_subject')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                     ->join('tb_mas_classlist','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID','inner')
                     ->join('tb_mas_classlist_student','tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID','inner')
                     ->join('tb_mas_sy','tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
//                     ->join('tb_mas_registration','tb_mas_registration.intAYID = tb_mas_sy.intID and tb_mas_registration.intStudentID = tb_mas_classlist_student.intStudentID')
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'tb_mas_classlist_student.intStudentID'=>$studentID))
                     ->order_by('strYearStart asc,enumSem asc, strCode asc')
                     ->group_by('tb_mas_classlist.intID')
                     ->get()
                     ->result_array();
        
        $credited = $this->db
                     ->select('tb_mas_subjects.strCode,tb_mas_credited_grades.floatFinalGrade,tb_mas_credited_grades.strRemarks,tb_mas_curriculum_subject.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strUnits,tb_mas_credited_grades.intSYID as enumSem,tb_mas_credited_grades.intSYID as strYearStart,tb_mas_credited_grades.intSYID as strYearEnd,tb_mas_credited_grades.intSYID  as syID')
                     ->from('tb_mas_credited_grades')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_credited_grades.intSubjectID')
                     ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intCurriculumID = tb_mas_credited_grades.intCurriculumID','inner')
                     ->where(array('tb_mas_credited_grades.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID))
                     ->group_by('tb_mas_credited_grades.intID')
                     ->order_by('strYearStart asc,enumSem asc')
                     ->get()
                     ->result_array();
                     
        return array_merge($subjects,$credited);
    }
    
    function getCreditedSubjects($studentID,$curriculumID)
    {
        return $this->db
                     ->select('tb_mas_credited_grades.intID,tb_mas_subjects.strCode,tb_mas_credited_grades.floatFinalGrade,tb_mas_credited_grades.strRemarks,tb_mas_subjects.strUnits')
                     ->from('tb_mas_credited_grades')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_credited_grades.intSubjectID')
                     ->where(array('tb_mas_credited_grades.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID))
                     ->order_by('intYearLevel asc,intSem asc')
                     ->get()
                     ->result_array();
    }
    
    
    function unitsEarned($studentID,$curriculumID)
    {
        $ret =  $this->db
                     ->select('SUM(tb_mas_subjects.strUnits) AS TotalUnitsEarned')
                     ->from('tb_mas_curriculum_subject')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                     ->join('tb_mas_classlist','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID','inner')
                     ->join('tb_mas_classlist_student','tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID','inner')
                     ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID,'strRemarks'=>'Passed','floatFinalGrade !='=>'5','tb_mas_subjects.intBridging'=>'0'))
                     ->group_by('tb_mas_curriculum_subject.intCurriculumID')
                     ->get()
                     ->result_array();
        
        $credited = $this->db
                     ->select('SUM(tb_mas_subjects.strUnits) AS TotalUnitsEarned')
                     ->from('tb_mas_curriculum_subject')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID')
                     ->join('tb_mas_credited_grades','tb_mas_credited_grades.intSubjectID = tb_mas_subjects.intID','inner')
                     ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID,'strRemarks'=>'Passed','floatFinalGrade !='=>'5'))
                     ->group_by('tb_mas_curriculum_subject.intCurriculumID')
                     ->get()
                     ->result_array();
        
        $return = 0;
        if(!empty($ret))
            $return += $ret[0]['TotalUnitsEarned'];
        if(!empty($credited))
            $return +=$credited[0]['TotalUnitsEarned'];
       
        return $return;
    }
    
    function getGPA($studentID,$curriculumID)
    {
        $st =  $this->db
                     ->select('SUM(floatFinalGrade * tb_mas_subjects.strUnits) as gpa, SUM(tb_mas_subjects.strUnits) as num')
                     ->from('tb_mas_curriculum_subject')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID1 = tb_mas_curriculum_subject.intSubjectID OR tb_mas_subjects.intEquivalentID2 = tb_mas_curriculum_subject.intSubjectID')
                     ->join('tb_mas_classlist','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID','inner')
                     ->join('tb_mas_classlist_student','tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID','inner')
                     ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'tb_mas_subjects.intBridging'=>0,'intStudentID'=>$studentID,'floatFinalGrade !='=>'0','floatFinalGrade !='=>'3.5'))
                     ->group_by('tb_mas_curriculum_subject.intCurriculumID')
                     ->get()
                     ->first_row();
        
        $credited =  $this->db
                     ->select('SUM(floatFinalGrade * tb_mas_subjects.strUnits) as gpa, SUM(tb_mas_subjects.strUnits) as num')
                     ->from('tb_mas_curriculum_subject')
                     ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_curriculum_subject.intSubjectID')
                     ->join('tb_mas_credited_grades','tb_mas_credited_grades.intSubjectID = tb_mas_subjects.intID','inner')
                     ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$curriculumID,'intStudentID'=>$studentID,'floatFinalGrade !='=>'0','floatFinalGrade !='=>'3.5'))
                     ->group_by('tb_mas_curriculum_subject.intCurriculumID')
                     ->get()
                     ->first_row();
        
        $return = 0;
        $div = 0;
        if(!empty($st)){
            $return += $st->gpa;
            $div += $st->num;
        }
     
        
        if(!empty($credited))
        {
            $return += $credited->gpa;
            $div += $credited->num;
        }
        
        
        
        //if($div!=0)
          //  $return  = $return/$div;
        
        return $return;
    }
    
    function getUserData($id)
    {
        return $this->db
             ->select('intID,strFirstname,strLastname')
             ->from('tb_mas_faculty')
             ->where('intID',$id)
             ->get()
             ->first_row();
    }
    
    
    function getUnitsPerYear($id)
    {
        $subjects = $this->db
                         ->select( 'SUM(tb_mas_subjects.strUnits) as totalUnits, tb_mas_curriculum_subject.intYearLevel')
                         ->from('tb_mas_subjects')
                         ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                         ->where(array('tb_mas_curriculum_subject.intCurriculumID'=>$id, 'tb_mas_subjects.intBridging'=>'0'))
                         ->group_by('tb_mas_curriculum_subject.intYearLevel, tb_mas_curriculum_subject.intSem')
                         ->get()
                         ->result_array();
        
        return $subjects;
    }
    
    
    function executeAcademicSync()
    {
        $sem = $this->get_active_sem();
        $stud = $this->getRegisteredStudents($sem['intID']);
        
        /*$this->db
             ->where(array('isGraduate !='=>'1'))
             ->get('tb_mas_users')
             ->result_array();*/
        
        foreach($stud as $s)
        {
            $standing = $this->getAcademicStanding($s['intID'],$s['intCurriculumID']);
            $data['intStudentYear'] = $standing['year'];
            $data['strAcademicStanding'] = $standing['status'];
            $this->db
                 ->where('intID',$s['intID'])
                 ->update('tb_mas_users',$data);
            
        }
             
    }
    
    function getFailedSubject($studentID)
    {
        return $this->db
             ->select('intCSID')
             ->from('tb_mas_classlist_student')
             ->where(array('intStudentID'=>$studentID,'strRemarks != '=>'Passed','floatFinalGrade != '=>0))
             ->get()
             ->result_array();
    }
    
    function getAcademicStanding($studentID,$curriculumID)
    {
        $units = $this->unitsEarned($studentID,$curriculumID);
        $standing['year'] = 1;
        $standing['status'] = "regular";
        $t = 0;
        $i = 1;
        foreach($this->getUnitsPerYear($curriculumID) as $year_level)
        {
            $t += $year_level['totalUnits'];
            if($units >= $t && ($i % 2) == 0)
                $standing['year'] = $year_level['intYearLevel']+1;
            
            $i++;
        }
        
        if(!empty($this->getFailedSubject($studentID)))
            $standing['status'] = "irregular";
        elseif($units == 0)
            $standing['status'] = "new";
        
        
        return $standing;
    }
    
    
    function getStudentStudentNumber($id)
    {
        return  current(
                $this->db
                     ->select('*')
                     ->from('tb_mas_users')
                     ->where(array('strStudentNumber'=>$id))
                     ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')    
                     ->get()
                     ->result_array());
                     
    }
    function getStudentByName($lname,$fname,$code)
    {
        return  current(
                $this->db
                     ->select('*')
                     ->from('tb_mas_users')
                     ->where(array('strFirstname LIKE'=>$fname,'strLastname LIKE'=>$lname,'strProgramCode LIKE'=>$code))
                     ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')    
                     ->get()
                     ->result_array());
                     
    }
    function getFaculty($id)
    {
        return  current($this->db->get_where('tb_mas_faculty',array('intID'=>$id))->result_array());
                     
    }
    function getFacultyList()
    {
        return $this->db->get_where('tb_mas_faculty')->result_array();
                     
    }

    function getFinanceList()
    {
        return $this->db->get_where('tb_mas_faculty',array('intUserLevel'=>'6'))->result_array();
                     
    }
    
    function getAy($id)
    {
        return  current($this->db->get_where('tb_mas_sy',array('intID'=>$id))->result_array());
                     
    }
    
    function getSubject($id)
    {
        
            return  current(
                     $this->db
                         ->select( 'tb_mas_subjects.intID,strCode,strDescription,strUnits,intLab,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.intPrerequisiteID')
                         ->from('tb_mas_subjects')
                         ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                         ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID')
                         ->where(array('tb_mas_subjects.intID'=>$id))
                         ->get()
                         ->result_array());
                     
    }
    
    function getSubjectCurr($id,$program)
    {
        
            return  current(
                     $this->db
                         ->select( 'tb_mas_subjects.intID,strCode,strDescription,strUnits,intLab,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.intPrerequisiteID')
                         ->from('tb_mas_subjects')
                         ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                         ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_curriculum_subject.intCurriculumID')
                         ->where(array('tb_mas_subjects.intID'=>$id,'tb_mas_curriculum.intProgramID'=>$program))
                         ->get()
                         ->result_array());
                     
    }
    
    function getAdvisedStudentsCourse($sem,$course)
    {
        
            return  
                     $this->db
                         ->select( 'tb_mas_users.intID,tb_mas_users.strLastname,tb_mas_users.strFirstname')
                         ->from('tb_mas_users')
                         ->join('tb_mas_advised','tb_mas_advised.intStudentID = tb_mas_users.intID')
                         ->where(array('tb_mas_advised.intSYID'=>$sem,'tb_mas_users.intProgramID'=>$course))
                         ->order_by('strLastname asc, strFirstname asc')
                         ->get()
                         ->result_array();
                     
    }
    
    function getSubjectNoCurr($id)
    {
        
            return  current(
                     $this->db
                         ->select( 'tb_mas_subjects.intID,intProgramID,strCode,strDescription,strUnits,intLab,tb_mas_subjects.intPrerequisiteID, grading_system_id, grading_system_id_midterm')
                         ->from('tb_mas_subjects')
                         ->where(array('tb_mas_subjects.intID'=>$id))
                         ->get()
                         ->result_array());
                     
    }
    
    function getSubjectPlain($id)
    {
        return current($this->db->get_where('tb_mas_subjects',array('intID'=>$id))->result_array());
    }
   
    function getProgram($id)
    {
        return current($this->db->get_where('tb_mas_programs',array('intProgramID'=>$id))->result_array());
    }
    
    public function getExam($id)
    {
        return current($this->db->get_where('tb_mas_exam',array('intID'=>$id))->result_array());
    }

    public function getQuestion($id)
    {
        $query = "SELECT CONCAT( '". base_url()."assets/photos/exam/', tb_mas_questions.questionImage) AS image, 
            tb_mas_questions.intID, tb_mas_questions.strTitle, tb_mas_questions.exam_id, 
            tb_mas_questions.strSection, tb_mas_questions.questionImage from tb_mas_questions 
            INNER JOIN tb_mas_exam ON tb_mas_exam.intID = tb_mas_questions.exam_id  WHERE tb_mas_questions.intID = ".$id;
        
        return current($this->db
                    ->query($query)
                    ->result_array());
    }

    public function getChoice($id)
    {
        // return $this->db
        //      ->select('*')
        //      ->from('tb_mas_choices')
        //      ->where('question_id',$id)
        //      ->get()->result_array();
        $query = "SELECT CONCAT( '". base_url()."assets/photos/exam/', choiceImage) AS image, 
            intID, question_id, strChoice, is_correct from tb_mas_choices WHERE question_id = ".$id;
        
        return $this->db
                    ->query($query)
                    ->result_array();
    }

    public function getExamQuestion($id)
    {
        return $this->db->get_where('tb_mas_questions',array('exam_id'=>$id))->result_array();
    }

    public function getExamQuestionChoice($id)
    {
        return $this->db->get_where('tb_mas_choices',array('question_id'=>$id))->result_array();        
    }

    function checkSubjectTaken($studentID,$subjectID)
    {
        
        $arr = $this->db
             ->select('intStudentID')
             ->from('tb_mas_classlist_student')
             ->where(array("intStudentID"=>$studentID,"intSubjectID"=>$subjectID,'floatFinalGrade'=>'!= 5','enumStatus'=>'!= \'odrp\''))
             ->join('tb_mas_classlist', 'tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')             
             ->get()->result_array();
        if(empty($arr))
            return false;
        else
            return true;
    }
    
    function checkSubjectAdvised($studentID,$subjectID,$sem)
    {
        
        $arr = $this->db
             ->select('intStudentID')
             ->from('tb_mas_advised')
             ->where(array("intStudentID"=>$studentID,"intSubjectID"=>$subjectID,'intSYID'=>$sem))
             ->join('tb_mas_advised_subjects', 'tb_mas_advised_subjects.intAdvisedID = tb_mas_advised.intAdvisedID')             
             ->get()->result_array();        
        
             print_r($arr);
        if(empty($arr))
            return false;
        else
            return true;
    }
    
    function checkStudentAdvised($studentID,$sem)
    {
        
        $arr = $this->db
             ->select('intStudentID')
             ->from('tb_mas_advised')
             ->where(array("intStudentID"=>$studentID,'intSYID'=>$sem))
             ->get()->result_array();
        if(empty($arr))
            return false;
        else
            return true;
    }
    
    function getAdvisedID($studentID,$sem)
    {
        $arr = $this->db
             ->select('intAdvisedID')
             ->from('tb_mas_advised')
             ->where(array("intStudentID"=>$studentID,'intSYID'=>$sem))
             ->get()->first_row();
        
        return $arr->intAdvisedID;
        
    }
    
    function getAdvisedSubjects($studentID,$sem)
    {
        $arr = $this->db
             ->select('intSubjectID,tb_mas_subjects.intID,strCode')
             ->from('tb_mas_advised')
             ->join('tb_mas_advised_subjects','tb_mas_advised.intAdvisedID = tb_mas_advised_subjects.intAdvisedID')
             ->join('tb_mas_subjects','tb_mas_advised_subjects.intSubjectID = tb_mas_subjects.intID')
             ->where(array("intStudentID"=>$studentID,'intSYID'=>$sem))
             ->get()->result_array();
        
        return $arr;
    }
    
    function getAdvisedSubjectsReg($post)
    {
            $courses =
               $this->db
                    ->select( 'tb_mas_subjects.intID,strCode,strDescription,strUnits,intLab')
                    ->from('tb_mas_advised')
                    ->join('tb_mas_advised_subjects','tb_mas_advised.intAdvisedID = tb_mas_advised_subjects.intAdvisedID')
                    ->join('tb_mas_subjects','tb_mas_advised_subjects.intSubjectID = tb_mas_subjects.intID')
                    ->where(array("intStudentID"=>$post['intStudentID'],'intSYID'=>$post['sem']))
                    ->get()
                    ->result_array();
                
                
               // $this->db->get_where('tb_mas_subjects',array('intYearLevel'=>$post['intYearLevel'],'intProgramID'=>$post['strCourse'],'intSem'=>$post['intSem']))->result_array();

        return $courses;
        
    }
    
    
    function checkRegistered($studentID,$AYID)
    {
        
        $arr = $this->db
             ->select('intRegistrationID')
             ->from('tb_mas_registration')
             ->where(array("intStudentID"=>$studentID,"intAYID"=>$AYID,'intROG >='=>1))
             ->get()->result_array();
        if(empty($arr))
            return false;
        else
            return true;
    }
    function checkClasslistExists($subject,$ay,$course,$new=null)
    {
        
            
       $classlists = $this->db->where(array('intSubjectID'=>$subject,'strAcademicYear'=>$ay,'strSection LIKE '=>$course.'%'))
           ->order_by('strSection asc')
           ->get('tb_mas_classlist')
           ->result_array();
        
        $cl_ret = "";
        
        if(!empty($classlists))
        {
            
            if($new!=null)
            {
                return "new-".count($classlists);
            }
           
            $limit = 30;
            
            foreach($classlists as $cl)
            {
                 if( count($this->db
                             ->select('intCSID')
                             ->from('tb_mas_classlist_student')
                             ->where(array('intClassListID'=>$cl['intID']))
                             ->get()
                             ->result_array()) < $limit
                    )
                    {
                       $cl_ret = $cl;
                       break;
                    }
                    else
                    {
                        $cl_ret = "new-".count($classlists);
                    }
                
            }
            return $cl_ret;
        }
        else
        {
            return "1";
        }
       
    }
    function checkClasslistExistsGen($subject,$ay,$course)
    {
        
            
       $classlist = $this->db->where(array('intSubjectID'=>$subject,'strAcademicYear'=>$ay,'strClassName LIKE '=>$course))
           ->order_by('strSection desc')
           ->get('tb_mas_classlist')
           ->row();
        
        if($classlist)
            return $classlist->strSection + 1;
        else
            return 1;
        
       
    }
    function getRegistrationInfo($id,$sem)
    {
        return  $this->db
                    ->select('tb_mas_registration.*, tb_mas_scholarships.name as scholarshipName')
                    ->from('tb_mas_registration')
                    ->where(array('intStudentID'=>$id,'intAYID'=>$sem))
                    ->join('tb_mas_scholarships', 'tb_mas_scholarships.intID = tb_mas_registration.enumScholarship', 'left')
                    ->get()
                    ->first_row('array');
    }
    
    function getRegistrationData($sem)
    {
        $data['enrolled'] = $this->db->get_where('tb_mas_registration',array('intAYID'=>$sem,'intROG'=>1))->num_rows();
        $data['registered'] = $this->db->get_where('tb_mas_registration',array('intAYID'=>$sem,'intROG'=>0))->num_rows();
        $data['cleared'] = $this->db->get_where('tb_mas_registration',array('intAYID'=>$sem,'intROG'=>2))->num_rows();
        
        return $data;
    }
    
    
    
        
    function getRegistrationStatus($id,$sem)
    {
         
        if($this->db
             ->select('intRegistrationID')
             ->from('tb_mas_registration')
             ->where(array('intStudentID'=>$id,'intAYID'=>$sem))
             ->get()
             ->num_rows() > 0)
        {
            $r = $this->db
             ->select('intRegistrationID,intROG')
             ->from('tb_mas_registration')
             ->where(array('intStudentID'=>$id,'intAYID'=>$sem))
             ->get()
             ->first_row();
            
            if($r->intROG == 0)
                return "Registered";
            if($r->intROG == 1)
                return "Enrolled";
            if($r->intROG == 2)
                return "Cleared";
            if($r->intROG == 3)
                return "Officially Withdrawn";
        }
        elseif($this->db
             ->select('intID')
             ->from('tb_mas_classlist')
             ->join('tb_mas_classlist_student','tb_mas_classlist.intID = tb_mas_classlist_student.intClassListID')
             ->where(array('intStudentID'=>$id,'strAcademicYear'=>$sem))
             ->get()
             ->num_rows() > 0)
            return "For Registration";
        elseif($this->db
             ->select('intAdvisedID')
             ->from('tb_mas_advised')
             ->where(array('intStudentID'=>$id,'intSYID'=>$sem))
             ->get()
             ->num_rows() > 0)
            return "For Sectioning";
        else
            return "For Subject Enlistment";
    }

    function getMaxCurrentStudentNumber($sem){
        $term = switch_num_rev_search($sem['enumSem']);
        $year = $sem['strYearStart'];        
        $res = $this->db->where(array(
            'strStudentNumber LIKE' => 'C%'.$year.'-'.$term.'%'
        ))
        ->order_by('strStudentNumber','desc')
        ->get('tb_mas_users')        
        ->first_row('array');

        if($res)
            return $res['strStudentNumber'];
        else
            return "C".$year.'-'.$term.'-000';
    }

    function getMaxCurrentStudentNumberMain($sem){
        $term = switch_num_rev_search($sem['enumSem']);
        $year = $sem['strYearStart'];        
        $res = $this->db->where(array(
            'strStudentNumber LIKE' => $year.'-'.$term.'%'
        ))
        ->order_by('strStudentNumber','desc')
        ->get('tb_mas_users')        
        ->first_row('array');

        if($res)
            return $res['strStudentNumber'];
        else
            return $year.'-'.$term.'-000';
    }

    function getMaxCurrentTempNumber($sem){
        $term = switch_num_rev_search($sem['enumSem']);
        $year = $sem['strYearStart'];        
        $res = $this->db->where(array(
            'strStudentNumber LIKE' => 'T%'.$year.'-'.$term.'%'
        ))
        ->order_by('strStudentNumber','desc')
        ->get('tb_mas_users')        
        ->first_row('array');

        if($res)
            return $res['strStudentNumber'];
        else
            return "T".$year.'-'.$term.'-000';
    }
    

    public function generateNewStudentNumber($campus,$sem = 0){
        if($sem == 0)
            $sem = $this->get_processing_sem();
        else
            $sem = $this->get_sem_by_id($sem);
        
        if($campus == "Cebu")
            $studentNum = $this->getMaxCurrentStudentNumber($sem);
        else
            $studentNum = $this->getMaxCurrentStudentNumberMain($sem);

        $newStudentNumber =  preg_replace_callback( "|(\d+)(?!.*\d)|", "increment_student_number", $studentNum);
                
        return $newStudentNumber;        
    }

    public function generateNewTempNumber(){
        $sem = $this->get_processing_sem();
        $studentNum = $this->getMaxCurrentTempNumber($sem);
        $newStudentNumber =  preg_replace_callback( "|(\d+)(?!.*\d)|", "increment_student_number", $studentNum);
                
        return $newStudentNumber;        
    }

    function getTuitionExtra($type,$id){
        return $this->db->get_where('tb_mas_tuition_year_'.$type,array('tuitionYearID'=>$id))->result_array();
    }

    function getTuitionTrack($id,$label){
        return $this->db->select('tb_mas_programs.*,tb_mas_tuition_year_'.$label.'.id,tuition_amount,tuition_amount_online,tuition_amount_hybrid,tuition_amount_hyflex')
                        ->from('tb_mas_tuition_year_'.$label)
                        ->join('tb_mas_programs', 'tb_mas_programs.intProgramID = tb_mas_tuition_year_'.$label.'.track_id')
                        ->where(array('tuitionyear_id'=>$id))
                        ->get()
                        ->result_array();
    }

    function getLabTypesForDropdown(){
        $ret = [];
        $data = $this->db
                    ->select('name')
                    ->from('tb_mas_tuition_year_lab_fee')
                    ->group_by('name')
                    ->order_by('name')
                    ->get()
                    ->result_array();

        $ret['none'] = "None";
        foreach($data as $d)
            $ret[$d['name']] = $d['name'];

        return $ret;
    }

    function getTuition($id,$sem,$sch = 0,$discount = 0)
    {
        
        
        $registration =  $this->db->where(array('intStudentID'=>$id, 'intAYID' => $sem))->get('tb_mas_registration')->first_row('array');                          

        $classes =  $this->db
                            ->select("tb_mas_subjects.intID as subjectID")
                            ->from("tb_mas_classlist_student")
                            ->where(array("intStudentID"=>$id,"strAcademicYear"=>$sem,"tb_mas_classlist.intWithPayment"=>"0"))
                            ->join('tb_mas_classlist', 'tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID')
                            ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                            ->get()
                            ->result_array();

        $subjects = [];
        foreach($classes as $class)
        {                                         
            $subjects[] = $class['subjectID'];                            
        }

        return $this->getTuitionSubjects($registration['enumStudentType'],$sch,$discount,$subjects,$id,$registration['type_of_class'],$sem,$registration['tuition_year']);
        
    }

    function getTuitionSubject($sid,$student_id){

        $tuition = 0;

        $class =  current($this->db
                            ->select("*")
                            ->from("tb_mas_subjects")
                            ->where(array("intID"=>$sid))
                            ->get()
                            ->result_array());      
                            
        $student = $this->db->where('intID',$student_id)->get('tb_mas_users')->first_row('array');                             
        
        //Checks if subject is NSTP nstp fee is different from normal fee                                
        if($class['isNSTP']){
            $nstp_fee = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'nstp'))
            ->get('tb_mas_tuition_year_misc')->first_row('array');
            $nstp_fee = getExtraFee($nstp_fee, $class_type, 'misc');

            $tuition += intval($class['strTuitionUnits'])*$nstp_fee;
        }
        else
            $tuition += intval($class['strTuitionUnits'])*$unit_fee;
        
        if($class['strLabClassification'] != "none"){
            $tuition_year_lab = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'],'name' => $class['strLabClassification']))
                                        ->get('tb_mas_tuition_year_lab_fee')->first_row('array');
            $tuition += getExtraFee($tuition_year_lab, $class_type, 'lab') * $class['intLab'];
            
        }
        
        if($class['isThesisSubject']){                
            $thesis = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'thesis'))
            ->get('tb_mas_tuition_year_misc')->first_row('array');
            $tuition += getExtraFee($thesis, $class_type, 'misc');                                
        }
        
    }   

    function getTuitionSubjects($stype,$sch,$discount,$subjects,$id,$class_type="regular",$syid,$tuition_year_id)
    {

        $tuition = 0;
        $total_lab = 0;
        $total_misc = 0;
        $total_new_student = 0;
        $is_foreign = false;
        $total_foreign = 0;
        $total_other = 0;
        $afee = 0;
        $lab_list = [];
        $misc_list = [];    
        $new_student_list = [];    
        $foreign_fee_list = [];        
        $internship_fee_list = [];
        $nsf = 0;
        $thesis_fee = 0;    
        $total_internship_fee = 0;
        $hasInternship = false;
        $sem  = $this->get_active_sem();
        $scholarship_discount = 0;
        $discounted_price = 0;        
        $scholar = null;
        
        $student = $this->db->where('intID',$id)->get('tb_mas_users')->first_row('array'); 
        $level = get_stype($student['level']);
    
        $tuition_year = $this->db->where('intID',$tuition_year_id)->get('tb_mas_tuition_year')->first_row('array');
        
        

        $unit_rate = $this->db->where(array('tuitionyear_id'=>$tuition_year['intID'], 'track_id' => $student['intProgramID']))
            ->get('tb_mas_tuition_year_program')->first_row('array');
        
        if(!$unit_rate)
            $unit_fee = getUnitPrice($tuition_year,$class_type);        
        else{
            switch($class_type){
                case 'regular':
                    $unit_fee = $unit_rate['tuition_amount'];
                break;
                case 'online':
                    $unit_fee = $unit_rate['tuition_amount_online'];
                break;
                case 'hybrid':
                    $unit_fee = $unit_rate['tuition_amount_hybrid'];
                break;
                case 'hyflex':
                    $unit_fee = $unit_rate['tuition_amount_hyflex'];
                break;
                default:
                    $unit_fee = $unit_rate['tuition_amount'];
                
            }  
        }        
            


        if($discount == 0)
            $discounts = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.*')
                ->where(array('syid'=>$syid,'student_id'=>$student['intID'],'deduction_type'=>'discount','tb_mas_student_discount.status'=>'applied'))
                ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                ->get('tb_mas_student_discount')
                ->result(); 
        else{            
            $discounts = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.*')
                ->where(array('intID'=>$discount,'syid'=>$syid,'student_id'=>$student['intID']))
                ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                ->get('tb_mas_student_discount')
                ->result(); 
        }

        if($sch == 0){
            $scholarships = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.*')
                ->where(array('syid'=>$syid,'student_id'=>$student['intID'],'deduction_type'=>'scholarship','tb_mas_student_discount.status'=>'applied'))
                ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                ->get('tb_mas_student_discount')
                ->result();             
        }
        else{                                              
            $scholarships = $this->db->select('tb_mas_student_discount.*,tb_mas_scholarships.*')
                ->where(array('intID'=>$sch,'syid'=>$syid,'student_id'=>$student['intID']))
                ->join('tb_mas_scholarships','tb_mas_scholarships.intID = tb_mas_student_discount.discount_id')
                ->get('tb_mas_student_discount')
                ->result(); 
        }

        
        
        // if($scholarship != 0 && $scholarship != null)
        //     $scholar = $this->db->where('intID',$scholarship)->get('tb_mas_scholarships')->row();
        // elseif($student['enumScholarship'] != null && $student['enumScholarship'] != 0 )
        //     $scholar = $this->db->where('intID',$student['enumScholarship'])->get('tb_mas_scholarships')->row();


        // $discount = $this->db->where('intID',$student['enumDiscount'])->get('tb_mas_scholarships')->row();
        

        $misc = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'regular'))
                         ->get('tb_mas_tuition_year_misc')->result_array();  
                         
        if($stype == 'new'){
            $new_student_data = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'new_student'))
                         ->get('tb_mas_tuition_year_misc')->result_array();

            foreach($new_student_data as $nsd){
                $new_student_list[$nsd['name']] = getExtraFee($nsd, $class_type, 'misc');
                $total_new_student += $new_student_list[$nsd['name']];
            }
        }    
        
        if($student['strCitizenship'] != "Philippines"){
            $is_foreign = true;
            if($sem['pay_student_visa'] != 0){
                    $student_visa = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'svf'))
                    ->get('tb_mas_tuition_year_misc')->first_row('array');
                    if($student_visa){
                        $foreign_fee_list['Student Visa'] = getExtraFee($student_visa, $class_type, 'misc');
                        $total_foreign += $foreign_fee_list['Student Visa'];
                    }
            }

            $international_student_fee = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'isf'))
                    ->get('tb_mas_tuition_year_misc')->first_row('array');
            if($international_student_fee){
                $foreign_fee_list['International Student Fee'] = getExtraFee($international_student_fee, $class_type, 'misc');
                $total_foreign += $foreign_fee_list['International Student Fee'];
            }
        }

        if($level == "college"){
            foreach($subjects as $sid)
            {                  
                $class =  current($this->db
                                    ->select("*")
                                    ->from("tb_mas_subjects")
                                    ->where(array("intID"=>$sid))
                                    ->get()
                                    ->result_array());                       
                
                //Checks if subject is NSTP nstp fee is different from normal fee                                
                if($class['isNSTP']){
                    $nstp_fee = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'nstp'))
                    ->get('tb_mas_tuition_year_misc')->first_row('array');
                    $nstp_fee = getExtraFee($nstp_fee, $class_type, 'misc');

                    $tuition += intval($class['strTuitionUnits'])*$nstp_fee;
                }
                else
                    $tuition += intval($class['strTuitionUnits'])*$unit_fee;
                
                if($class['strLabClassification'] != "none"){
                    $tuition_year_lab = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'],'name' => $class['strLabClassification']))
                                                ->get('tb_mas_tuition_year_lab_fee')->first_row('array');
                    $lab_list[$class['strCode']] = getExtraFee($tuition_year_lab, $class_type, 'lab') * $class['intLab'];
                    $total_lab += $lab_list[$class['strCode']];
                }
                
                if($class['isThesisSubject']){                
                    $thesis = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'thesis'))
                    ->get('tb_mas_tuition_year_misc')->first_row('array');
                    $thesis_fee = getExtraFee($thesis, $class_type, 'misc');                                
                }

                if($class['isInternshipSubject']){                
                    $hasInternship = true;
                }
            
            }
        }
        else{
            //$tuition = $unit_fee;
            $shs_rate = $this->db->where(array('tuitionyear_id'=>$tuition_year['intID'], 'track_id' => $student['intProgramID']))
            ->get('tb_mas_tuition_year_track')->first_row('array');
            
            if($shs_rate)
                switch($class_type){
                    case 'regular':
                        $tuition = $shs_rate['tuition_amount'];
                    break;
                    case 'online':
                        $tuition = $shs_rate['tuition_amount_online'];
                    break;
                    case 'hybrid':
                        $tuition = $shs_rate['tuition_amount_hybrid'];
                    break;
                    case 'hyflex':
                        $tuition = $shs_rate['tuition_amount_hyflex'];
                    break;
                    default:
                        $tuition = $shs_rate['tuition_amount'];
                    
                }                                 
                    
        }
        

        foreach($misc as $m){            
            if($stype != 'new' || $m['name'] != 'ID Validation' ){
                $misc_list[$m['name']] = getExtraFee($m, $class_type, 'misc');
                $total_misc += $misc_list[$m['name']];
            }
        }
        
        if($hasInternship){
            $internship = $this->db->where(array('tuitionYearID'=>$tuition_year['intID'], 'type' => 'internship'))
            ->get('tb_mas_tuition_year_misc')->result_array();

            foreach($internship as $m){            
                $internship_fee_list[$m['name']] = getExtraFee($m, $class_type, 'misc');
                $total_internship_fee += $internship_fee_list[$m['name']];
            }                  
        }
        
        
        
        $scholarship_grand_total = 0;
        $scholarship_installment_grand_total = 0;
        $total_scholarship = [];
        $tuition_scholarship = 0;
        $tuition_scholarship_installment = 0;
        $total_scholarship_installment = [];
        $misc_scholarship = 0;        
        $lab_scholarship = 0;
        $lab_scholarship_installment = 0;
        $other_scholarship = 0;
        $ctr = 0;        
        $scholarships_for_ledger = [];
        
        if(!empty($scholarships)){
            foreach($scholarships as $scholar){
                
                $tuition_scholarship_installment_current = 0;
                $tuition_scholarship_current = 0;
                $lab_scholarship_installment_current = 0;
                $lab_scholarship_current = 0;
                $misc_scholarship_current = 0;
                $other_scholarship_current = 0;
                $total_scholarship_temp = 0;
                $total_scholarship_installment_temp = 0;

                if($scholar->total_assessment_rate > 0 || $scholar->total_assessment_fixed > 0){                
                    $total_scholarship_temp += $tuition + $total_lab + $total_misc + $thesis_fee + $total_new_student + $nsf + $total_internship_fee + $total_foreign;
                    $total_assessment_installment_temp += ($tuition  + ($tuition * ($tuition_year['installmentIncrease']/100)))   
                                                + ($total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100)))
                                                + $total_misc + $thesis_fee + $total_new_student + $nsf + $total_internship_fee + $total_foreign;

                    if($scholar->total_assessment_rate > 0){
                        $total_scholarship_temp += $total_assessment * ($scholar->total_assessment_rate/100);
                        $total_scholarship_installment_temp += $total_assessment_installment * ($scholar->total_assessment_rate/100);
                    }
                    elseif($scholar->total_assessment_fixed > 0){
                        if($scholar->total_assessment_fixed > $total_assessment){
                            $total_scholarship_temp += $total_assessment;
                            $total_scholarship_installment_temp += $total_assessment_installment;
                        }
                        else{
                            $total_scholarship_temp += $scholar->total_assessment_fixed;
                            $total_scholarship_installment_temp += $scholar->total_assessment_fixed;
                        }
                    }
                }
                else{
                    if($scholar->tuition_fee_rate > 0){
                        $tuition_scholarship_installment_current = ($tuition + ($tuition * ($tuition_year['installmentIncrease']/100))) * ($scholar->tuition_fee_rate/100);
                        $tuition_scholarship_installment += ($tuition + ($tuition * ($tuition_year['installmentIncrease']/100))) * ($scholar->tuition_fee_rate/100);
                        $tuition_scholarship_current = $tuition * ($scholar->tuition_fee_rate/100);
                        $tuition_scholarship += $tuition * ($scholar->tuition_fee_rate/100);

                    }
                    elseif($scholar->tuition_fee_fixed > 0){
                        if($scholar->tuition_fee_fixed > $tuition){
                            $tuition_scholarship_current = $tuition;
                            $tuition_scholarship += $tuition;
                        }
                        else{
                            $tuition_scholarship_current = $scholar->tuition_fee_fixed;
                            $tuition_scholarship += $scholar->tuition_fee_fixed;                                            
                        }

                        $tuition_scholarship_installment_current = $tuition_scholarship;
                        $tuition_scholarship_installment += $tuition_scholarship;
                    }

                    $total_scholarship_temp += $tuition_scholarship_current;
                    $total_scholarship_installment_temp += $tuition_scholarship_installment_current;

                    if($scholar->misc_fee_rate > 0){
                        $misc_scholarship_current = $total_misc * ($scholar->misc_fee_rate/100);
                        $misc_scholarship += $total_misc * ($scholar->misc_fee_rate/100);
                    }
                    elseif($scholar->misc_fee_fixed > 0){
                        if($scholar->misc_fee_fixed > $total_misc){
                            $misc_scholarship_current = $total_misc;
                            $misc_scholarship += $total_misc;
                        }
                        else{
                            $misc_scholarship_current = $scholar->misc_fee_fixed;
                            $misc_scholarship += $scholar->misc_fee_fixed;                    
                        }
                    }

                    $total_scholarship_installment_temp += $misc_scholarship_current;
                    $total_scholarship_temp += $misc_scholarship_current;

                    if($scholar->lab_fee_rate > 0){
                        $lab_scholarship_installment_current =  ($total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100))) * ($scholar->lab_fee_rate/100);
                        $lab_scholarship_installment += ($total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100))) * ($scholar->lab_fee_rate/100);
                        $lab_scholarship_current = $total_lab * ($scholar->lab_fee_rate/100);
                        $lab_scholarship += $total_lab * ($scholar->lab_fee_rate/100);
                    }
                    elseif($scholar->lab_fee_fixed > 0){
                        if($scholar->lab_fee_fixed > $total_lab){
                            $lab_scholarship_current = $total_lab;
                            $lab_scholarship += $total_lab;
                        }
                        else{
                            $lab_scholarship_current = $scholar->lab_fee_fixed;
                            $lab_scholarship += $scholar->lab_fee_fixed;
                        }

                        $lab_scholarship_installment_current = $lab_scholarship;
                        $lab_scholarship_installment += $lab_scholarship;
                    }

                    $total_scholarship_installment_temp += $lab_scholarship_installment_current;
                    $total_scholarship_temp += $lab_scholarship_current;

                    if($scholar->other_fees_rate > 0){
                        $total_other = $total_foreign + $total_new_student;
                        $other_scholarship_current = $total_other * ($scholar->other_fees_rate/100);
                        $other_scholarship += $total_other * ($scholar->other_fees_rate/100);
                    }
                    elseif($scholar->other_fees_fixed > 0){
                        if($scholar->other_fees_fixed > $total_lab){
                            $other_scholarship_current = $total_other;
                            $other_scholarship += $total_other;
                        }
                        else{
                            $other_scholarship_current = $scholar->other_fees_fixed;
                            $other_scholarship += $scholar->other_fees_fixed;
                        }
                    }

                    $total_scholarship_temp += $other_scholarship_current;
                    $total_scholarship_installment_temp += $other_scholarship_current;
                }

                $total_scholarship[] = $total_scholarship_temp;
                $total_scholarship_installment[] = $total_scholarship_installment_temp;

                $scholarship_installment_grand_total += $total_scholarship_installment_temp;
                $scholarship_grand_total += $total_scholarship_temp;                

                $ctr++;
            }
        }
        
        $discount_grand_total = 0;
        $discount_installment_grand_total = 0;
        $total_discount = [];
        $tuition_discount = 0;
        $tuition_discount_installment = 0;
        $total_discount_installment = [];
        $misc_discount = 0;        
        $lab_discount = 0;
        $lab_discount_installment = 0;
        $other_discount = 0;
        $discount = null;
        
        if(!empty($discounts)){
            foreach($discounts as $scholar){
                
                $tuition_scholarship_installment_current = 0;
                $tuition_scholarship_current = 0;
                $lab_scholarship_installment_current = 0;
                $lab_scholarship_current = 0;
                $misc_scholarship_current = 0;
                $other_scholarship_current = 0;
                $total_scholarship_temp = 0;
                $total_scholarship_installment_temp = 0;

                if($scholar->total_assessment_rate > 0 || $scholar->total_assessment_fixed > 0){                
                    $total_scholarship_temp += $tuition + $total_lab + $total_misc + $thesis_fee + $total_new_student + $nsf + $total_internship_fee + $total_foreign;
                    $total_assessment_installment_temp += ($tuition  + ($tuition * ($tuition_year['installmentIncrease']/100)))   
                                                + ($total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100)))
                                                + $total_misc + $thesis_fee + $total_new_student + $nsf + $total_internship_fee + $total_foreign;

                    if($scholar->total_assessment_rate > 0){
                        $total_scholarship_temp += $total_assessment * ($scholar->total_assessment_rate/100);
                        $total_scholarship_installment_temp += $total_assessment_installment * ($scholar->total_assessment_rate/100);
                    }
                    elseif($scholar->total_assessment_fixed > 0){
                        if($scholar->total_assessment_fixed > $total_assessment){
                            $total_scholarship_temp += $total_assessment;
                            $total_scholarship_installment_temp += $total_assessment_installment;
                        }
                        else{
                            $total_scholarship_temp += $scholar->total_assessment_fixed;
                            $total_scholarship_installment_temp += $scholar->total_assessment_fixed;
                        }
                    }
                }
                else{
                    if($scholar->tuition_fee_rate > 0){
                        $tuition_scholarship_installment_current = ($tuition + ($tuition * ($tuition_year['installmentIncrease']/100))) * ($scholar->tuition_fee_rate/100);
                        $tuition_discount_installment += ($tuition + ($tuition * ($tuition_year['installmentIncrease']/100))) * ($scholar->tuition_fee_rate/100);
                        $tuition_scholarship_current = $tuition * ($scholar->tuition_fee_rate/100);
                        $tuition_discount += $tuition * ($scholar->tuition_fee_rate/100);

                    }
                    elseif($scholar->tuition_fee_fixed > 0){
                        if($scholar->tuition_fee_fixed > $tuition){
                            $tuition_scholarship_current = $tuition;
                            $tuition_discount += $tuition;
                        }
                        else{
                            $tuition_scholarship_current = $scholar->tuition_fee_fixed;
                            $tuition_discount += $scholar->tuition_fee_fixed;                                            
                        }

                        $tuition_scholarship_installment_current = $tuition_scholarship;
                        $tuition_discount_installment += $tuition_scholarship;
                    }

                    $total_scholarship_temp += $tuition_scholarship_current;
                    $total_scholarship_installment_temp += $tuition_scholarship_installment_current;

                    if($scholar->misc_fee_rate > 0){
                        $misc_scholarship_current = $total_misc * ($scholar->misc_fee_rate/100);
                        $misc_discount += $total_misc * ($scholar->misc_fee_rate/100);
                    }
                    elseif($scholar->misc_fee_fixed > 0){
                        if($scholar->misc_fee_fixed > $total_misc){
                            $misc_scholarship_current = $total_misc;
                            $misc_discount += $total_misc;
                        }
                        else{
                            $misc_scholarship_current = $scholar->misc_fee_fixed;
                            $misc_discount += $scholar->misc_fee_fixed;                    
                        }
                    }

                    $total_scholarship_installment_temp += $misc_scholarship_current;
                    $total_scholarship_temp += $misc_scholarship_current;

                    if($scholar->lab_fee_rate > 0){
                        $lab_scholarship_installment_current =  ($total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100))) * ($scholar->lab_fee_rate/100);
                        $lab_discount_installment += ($total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100))) * ($scholar->lab_fee_rate/100);
                        $lab_scholarship_current = $total_lab * ($scholar->lab_fee_rate/100);
                        $lab_discount += $total_lab * ($scholar->lab_fee_rate/100);
                    }
                    elseif($scholar->lab_fee_fixed > 0){
                        if($scholar->lab_fee_fixed > $total_lab){
                            $lab_scholarship_current = $total_lab;
                            $lab_discount += $total_lab;
                        }
                        else{
                            $lab_scholarship_current = $scholar->lab_fee_fixed;
                            $lab_discount += $scholar->lab_fee_fixed;
                        }

                        $lab_scholarship_installment_current = $lab_scholarship;
                        $lab_discount_installment += $lab_discount;
                    }

                    $total_scholarship_installment_temp += $lab_scholarship_installment_current;
                    $total_scholarship_temp += $lab_scholarship_current;

                    if($scholar->other_fees_rate > 0){
                        $total_other = $total_foreign + $total_new_student;
                        $other_scholarship_current = $total_other * ($scholar->other_fees_rate/100);
                        $other_discount += $total_other * ($scholar->other_fees_rate/100);
                    }
                    elseif($scholar->other_fees_fixed > 0){
                        if($scholar->other_fees_fixed > $total_lab){
                            $other_scholarship_current = $total_other;
                            $other_discount += $total_other;
                        }
                        else{
                            $other_scholarship_current = $scholar->other_fees_fixed;
                            $other_discount += $scholar->other_fees_fixed;
                        }
                    }

                    $total_scholarship_temp += $other_scholarship_current;
                    $total_scholarship_installment_temp += $other_scholarship_current;
                }

                $total_discount[] = $total_scholarship_temp;
                $total_discount_installment[] = $total_scholarship_installment_temp;

                $discount_installment_grand_total += $total_scholarship_installment_temp;
                $discount_grand_total += $total_scholarship_temp;

                $ctr++;
            }
        }
                    
        
        $data['lab_discount'] = $lab_scholarship;
        $data['lab_discount_dc'] = $lab_discount;
        $data['total_discount'] = $scholarship_grand_total;
        $data['total_discount_dc'] = $discount_grand_total;
        $data['lab_before_discount'] = $total_lab;
        $data['lab'] = $total_lab - $lab_scholarship - $lab_discount;
        $data['lab_installment_before_discount'] = $total_lab + ($total_lab * ($tuition_year['installmentIncrease']/100));
        $data['lab_installment'] = $data['lab_installment_before_discount'] - $lab_scholarship - $lab_discount;
        $data['lab_list'] = $lab_list;
        $data['tuition_discount'] = $tuition_scholarship;        
        $data['tuition_discount_dc'] = $tuition_discount; 
        $data['tuition'] = $tuition;        
        $data['tuition_installment'] = $data['tuition'] + $data['tuition'] * ($tuition_year['installmentIncrease']/100);
        $data['installment_dp'] = $tuition_year['installmentDP'];
        $data['misc_discount'] = $misc_scholarship;
        $data['misc_discount_dc'] = $misc_discount;
        $data['misc'] = $total_misc;                
        $data['misc_list'] = $misc_list;
        $data['is_foreign'] = $is_foreign;
        $data['new_student'] = $total_new_student;
        $data['new_student_list'] = $new_student_list;
        $data['internship_fee_list'] = $internship_fee_list;
        $data['foreign_fee_list'] = $foreign_fee_list;
        $data['athletic'] = $afee;
        $data['thesis_fee'] = $thesis_fee;
        $data['nsf'] = $nsf;             
        $data['total_foreign'] = $total_foreign;        
        $data['internship_fee'] = $total_internship_fee;   
        $data['other_discount'] = $other_scholarship;        
        $data['other_discount_dc'] = $other_discount;
        $data['total_other_before_discount'] = $data['new_student'] + $data['total_foreign'];                
        $data['total_before_deductions'] = $data['tuition'] + $data['lab'] + $data['misc'] + $thesis_fee + $data['total_other_before_discount'] + $nsf + $total_internship_fee;
        $data['ti_before_deductions'] = $data['tuition_installment'] + $data['lab_installment'] + $data['misc'] + $thesis_fee + $data['total_other_before_discount'] + $nsf + $total_internship_fee;                
        $data['total_installment'] = $data['ti_before_deductions'];
        //deduct discounts/scholarships
        $data['total_other'] = $data['new_student'] + $data['total_foreign'] - $other_scholarship - $other_discount;
        $data['misc_before_discount'] = $total_misc;  
        $data['misc'] = $total_misc - $misc_scholarship - $misc_discount;  
        $data['tuition_before_discount'] =  $tuition; 
        $data['tuition_installment_before_discount'] =  $data['tuition_installment'];
        $data['tuition'] = $tuition - $tuition_scholarship - $tuition_discount;
        $data['tuition_installment'] = $data['tuition_installment'] - $tuition_scholarship - $tuition_discount;
        $data['scholarship'] = $scholarships;
        $data['discount'] = $discounts;
        $data['total'] = $data['total_before_deductions'] - $scholarship_grand_total - $discount_grand_total;                        
        $data['scholarship_deductions_array'] = $total_scholarship;        
        $data['scholarship_deductions'] = $scholarship_grand_total;        
        $data['discount_deductions'] = $discount_grand_total;
        $data['scholarship_deductions_installment_array'] = $total_scholarship_installment;
        $data['scholarship_deductions_installment'] = $scholarship_installment_grand_total;
        $data['scholarship_deductions_dc_array'] = $total_discount;
        $data['scholarship_deductions_dc'] = $discount_grand_total;
        $data['scholarship_deductions_installment_dc_array'] = $total_discount_installment; 
        $data['scholarship_deductions_installment_dc'] = $discount_installment_grand_total;

        $data['total_installment'] = $data['ti_before_deductions']  - $scholarship_installment_grand_total  - $discount_installment_grand_total;
        
        if($data['total'] < 0)
            $data['total'] = 0;

        if($data['total'] == 0)
            $data['total_installment'] = 0;

        
        $data['total_installment'] = round($data['total_installment'],2);   
        $data['ti_before_deductions'] = round($data['ti_before_deductions'],2);        
        
        if(!isset($tuition_year['installmentFixed']) || $tuition_year['installmentFixed'] == 0){
            $data['down_payment'] = $data['total_installment'] * ($tuition_year['installmentDP']/100);
            $data['down_payment'] = round($data['down_payment'],2);

            $data['dp_before_deductions'] = $data['ti_before_deductions'] * ($tuition_year['installmentDP']/100);
            $data['dp_before_deductions'] = round($data['dp_before_deductions'],2);
        }
        else{
            if($data['total_installment'] > $tuition_year['installmentFixed']){
                $data['down_payment'] = $tuition_year['installmentFixed'];
            }
            else{
                $data['down_payment'] = $data['total_installment'];
            }
            
            $data['down_payment'] = round($data['down_payment'],2);

            $data['dp_before_deductions'] = $data['down_payment'];
            $data['dp_before_deductions'] = round($data['dp_before_deductions'],2);
        }

        $data['installment_fee'] = ($data['total_installment'] - $data['down_payment'])/5;
        $data['installment_fee'] = round($data['installment_fee'],2);

        $data['if_before_deductions'] = ($data['ti_before_deductions'] - $data['dp_before_deductions'])/5;
        $data['if_before_deductions'] = round($data['if_before_deductions'],2);

        if($data['total'] == 0)
        {
            $data['down_payment'] = 0;
            $data['installment_fee'] = 0;
        }

        $data['class_type'] = $sem['classType'];
        
        
        
        
        // $data['discounted_price'] = $discounted_price;
        // $data['scholarship_discount'] = $scholarship_discount;
        
        return $data;

    }
        
    
    
    
    function getTransactions($id,$sem)
    {
        return  $this->db
                     ->select('intTransactionID, SUM(intAmountPaid) as totalAmountPaid, dtePaid, intORNumber')
                     ->from('tb_mas_transactions')
                     ->where(array('intRegistrationID'=>$id,'intAYID'=>$sem))
                     ->group_by('intORNumber')
                     ->get()
                     ->result_array();
    }
    
    function getTransactionsOR($or)
    {
        return  $this->db
                     ->select('intTransactionID, intAmountPaid, dtePaid, strTransactionType, intORNumber')
                     ->from('tb_mas_transactions')
                     ->where(array('intORNumber'=>$or))
                     ->get()
                     ->result_array();
    }
    
    function getTransactionsPayment($id,$sem)
    {
        return  $this->db
                     ->select('intTransactionID, intAmountPaid, strTransactionType')
                     ->from('tb_mas_transactions')
                     ->where(array('intRegistrationID'=>$id,'intAYID'=>$sem))
                     ->get()
                     ->result_array();
    }
    
    function getSemStudent($id)
    {
        return  $this->db
                     ->select("intID,enumSem,strYearStart,strYearEnd,enumStatus,enumFinalized")
                     ->from("tb_mas_sy")
                     //->group_by("intSubjectID")
                     ->where(array("intStudentID"=>$id))
                     ->join('tb_mas_registration', 'tb_mas_registration.intAYID = tb_mas_sy.intID')
                     ->get()
                     ->result_array();
    }
    
    function generateStudentNumber($year)
    {
        $st = true;
        $sem = current($this->db->get_where('tb_mas_sy',array('enumStatus'=>'active'))->result_array());
        while($st){       
            $snum = $year."0".$sem['enumSem'][0].rand(1000,9999);
            
            $array = $this->db->get_where('tb_mas_users',array('strStudentNumber'=>$snum))->result_array();
            if(empty($array))
                $st = false;
            
        }
        
        return $snum;
    }
    
    function generateAppNumber($year)
    {
        $st = true;
        $sem = current($this->db->get_where('tb_mas_sy',array('enumStatus'=>'active'))->result_array());
        while($st){       
            $snum = $year."0".$sem['enumSem'][0].rand(1000,9999);
            
            $array = $this->db->get_where('tb_mas_applications',array('strAppNumber'=>$snum))->result_array();
            if(empty($array))
                $st = false;
            
        }
        
        return $snum;
    }
    
    function generateConfirmationCode($year)
    {
        $st = true;
        $sem = current($this->db->get_where('tb_mas_sy',array('enumStatus'=>'active'))->result_array());
        while($st){       
            $snum = $year."0".$sem['enumSem'][0].rand(1000,9999);
            $snum = md5($snum);
            $snum = substr($snum,0,20);
            
            $array = $this->db->get_where('tb_mas_applications',array('strConfirmationCode'=>$snum))->result_array();
            if(empty($array))
                $st = false;
            
        }
        
        return $snum;
    }
    
    function generatePassword()
    {
        $snum = substr(pw_hash(date("hisY")),5,5).rand(1000,9999);
        return $snum;
    }
    
    function getProgramDetails($id)
    {
        return current($this->db->get_where('tb_mas_programs',array('intProgramID'=>$id))->result_array());
       /* return  $this->db
                     ->select("*")
                     ->from("tb_mas_programs")
                     //->group_by("intSubjectID")
                     ->where(array("intProgramID"=>$id))
                     ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                     ->order_by('strLastName','asc')
                     ->get()
                     ->result_array();*/
        
    }
     function getSectionDetails($id)
    {
        return current($this->db->get_where('tb_mas_classlist',array('strSection'=>$id))->result_array());
       /* return  $this->db
                     ->select("*")
                     ->from("tb_mas_programs")
                     //->group_by("intSubjectID")
                     ->where(array("intProgramID"=>$id))
                     ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                     ->order_by('strLastName','asc')
                     ->get()
                     ->result_array();*/
        
    }
    
    
    function generateOR()
    {
        $st = true;
        $date = date("njy");
        while($st){
            $or = $date.rand(1000,9999);
            $array = $this->db->get_where('tb_mas_transactions',array('intORNumber'=>$or))->result_array();
            if(empty($array))
                $st = false;
            
        }
        
        return $or;
    }

    function getCompletion($id){
        $query = "SELECT * from tb_mas_completion WHERE intClasslistStudentID = ".$id;
        
        return current($this->db
                    ->query($query)
                    ->result_array());
    }

    function getCompletionByID($id){
        $query = "SELECT * from tb_mas_completion WHERE intCompletionID = ".$id;
        
        return current($this->db
                    ->query($query)
                    ->result_array());
    }

    function getClasslistStudent($id){
        return  current($this->db
                     ->select("tb_mas_classlist_student.intCSID, tb_mas_classlist.intID, tb_mas_classlist.strSignatory1Name, tb_mas_classlist.strSignatory2Name, tb_mas_faculty.strFirstname as facFname,tb_mas_faculty.strLastname as facLname, tb_mas_users.strFirstname,tb_mas_users.strMiddlename,tb_mas_users.strLastname,tb_mas_users.intID as studentId,strStudentNumber, strCode, strDescription, tb_mas_classlist_student.floatFinalGrade,floatPrelimGrade,floatMidtermGrade,floatFinalsGrade,tb_mas_classlist_student.enumStatus,strRemarks, strProgramCode, strMajor, tb_mas_sy.enumSem, tb_mas_sy.strYearStart, tb_mas_sy.strYearEnd")
                     ->from("tb_mas_classlist_student")
                     //->group_by("intSubjectID")
                     ->where(array("intCSID"=>$id))
                     ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_classlist_student.intClassListID')
                     ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
                     ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                     ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')
                     ->join('tb_mas_sy','tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                     ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->get()
                     ->result_array());
    }
    
    function getStudentBalance($id){
        
        $today = date('Y-m-d');
        $ledger = $this->db->select('tb_mas_student_ledger.*,tb_mas_scholarships.name as scholarship_name, enumSem, strYearStart, strYearEnd, term_label, tb_mas_faculty.strFirstname, tb_mas_faculty.strLastname')        
        ->from('tb_mas_student_ledger')
        ->join('tb_mas_sy', 'tb_mas_student_ledger.syid = tb_mas_sy.intID')
        ->join('tb_mas_scholarships', 'tb_mas_student_ledger.scholarship_id = tb_mas_scholarships.intID','left')
        ->join('tb_mas_faculty', 'tb_mas_student_ledger.added_by = tb_mas_faculty.intID','left')
        ->where(array('student_id'=>$id,'start_of_classes <'=> $today, 'is_disabled' => 0 ))        
        ->get()
        ->result_array();

        $balance = 0;
        foreach($ledger as $item)
            $balance += floatval($item['amount']); 

        return $balance;

    }
    function getClassListStudents($id,$sem = 0)
    {
        $faculty_id = $this->session->userdata("intID");
        $classlist = $this->db->get_where('tb_mas_classlist',array('intID'=>$id))->first_row('array');        
        
        if($sem == 0){
            $where["intClassListID"] = $id;
            return  $this->db
                     ->select("tb_mas_classlist_student.intCSID,intID, intCurriculumID, strFirstname,strMiddlename,strLastname,strStudentNumber, strGSuiteEmail, tb_mas_classlist_student.floatFinalGrade,floatPrelimGrade,floatMidtermGrade,floatFinalsGrade,enumStatus,strRemarks, strUnits,strProgramCode,date_added")
                     ->from("tb_mas_classlist_student")
                     //->group_by("intSubjectID")
                     ->where($where)
                     ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                     ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')                                                               
                     ->order_by('strLastName asc, strFirstname asc')
                     ->get()
                     ->result_array();
        }
        else{
            $where["tb_mas_registration.intAYID"] = $sem;
            return  $this->db
                 ->select("tb_mas_classlist_student.intCSID,tb_mas_users.intID, tb_mas_users.strFirstname,tb_mas_users.strMiddlename,tb_mas_users.strLastname,strStudentNumber, strGSuiteEmail, tb_mas_classlist_student.floatFinalGrade,floatPrelimGrade,floatMidtermGrade,floatFinalsGrade,enumStatus,strRemarks, strUnits,strProgramCode,date_added,tb_mas_faculty.strUsername as fusername")
                 ->from("tb_mas_classlist_student")
                 //->group_by("intSubjectID")             
                 ->where($where)    
                 ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                 ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                 ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')
                 ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_registration.enlisted_by','left')        
                 ->group_by('tb_mas_users.intID')                                                       
                 ->order_by('strLastName asc, strFirstname asc')
                 ->get()
                 ->result_array();
        }
    }

    function getClassListStudentsEnrolled($id,$sem = 0)
    {
        $faculty_id = $this->session->userdata("intID");
        if($sem == 0)
            return  $this->db
                     ->select("tb_mas_classlist_student.intCSID,intID, strFirstname,strMiddlename,strLastname,strStudentNumber, strGSuiteEmail, tb_mas_classlist_student.floatFinalGrade,floatPrelimGrade,floatMidtermGrade,floatFinalsGrade,enumStatus,strRemarks, strUnits,strProgramCode,date_added")
                     ->from("tb_mas_classlist_student")
                     //->group_by("intSubjectID")
                     ->where(array("intClassListID"=>$id))                     
                     ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                     ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')                                                               
                     ->order_by('strLastName asc, strFirstname asc')
                     ->get()
                     ->result_array();
        else        
            return  $this->db
                 ->select("tb_mas_classlist_student.intCSID,tb_mas_users.intID, tb_mas_users.strFirstname,tb_mas_users.strMiddlename,tb_mas_users.strLastname,strStudentNumber, strGSuiteEmail, tb_mas_classlist_student.floatFinalGrade,floatPrelimGrade,floatMidtermGrade,floatFinalsGrade,enumStatus,strRemarks, strUnits,strProgramCode,date_added,tb_mas_faculty.strUsername as fusername")
                 ->from("tb_mas_classlist_student")
                 //->group_by("intSubjectID")             
                 ->where(array("tb_mas_registration.intAYID"=>$sem))    
                 ->where(array("tb_mas_registration.intROG >"=>0))
                 ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                 ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                 ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')
                 ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_registration.enlisted_by','left')        
                 ->group_by('tb_mas_users.intID')                                                       
                 ->order_by('strLastName asc, strFirstname asc')
                 ->get()
                 ->result_array();
    }

    function getClassListStudentsEnlistedOnly($id,$sem = 0,$course=0,$year=0,$gender=0,$start=0,$end=0)
    {
        $faculty_id = $this->session->userdata("intID");
        $where = [];        
        if($course != 0)
            $where['tb_mas_users.intProgramID'] = $course;
        if($gender != 0){
            if($gender == 1){
                $gender = "male";
            }
            else
                $gender = "female";
            $where['tb_mas_users.enumGender'] = $gender;
        }        
        if($sem != 0)
            $where['tb_mas_registration.intAYID'] = $sem;
        // if($year != 0)
        //     $where['tb_mas_registration.intYearLevel'] = $year;        
        return  $this->db
                ->select("tb_mas_classlist_student.intCSID,tb_mas_users.intID, tb_mas_users.strFirstname,tb_mas_users.strMiddlename,tb_mas_users.strLastname,strStudentNumber, strGSuiteEmail, tb_mas_classlist_student.floatFinalGrade,floatPrelimGrade,floatMidtermGrade,floatFinalsGrade,enumStatus,strRemarks, strUnits,strProgramCode,date_added,tb_mas_faculty.strUsername as fusername")
                ->from("tb_mas_classlist_student")
                //->group_by("intSubjectID")             
                ->where($where)    
                ->where(array("tb_mas_registration.intROG"=>0,"date_enlisted >="=>$start,"date_enlisted <="=>$end))                
                ->join('tb_mas_users', 'tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')
                ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_registration.enlisted_by','left')        
                ->group_by('tb_mas_users.intID')                                                       
                ->order_by('strLastName asc, strFirstname asc')
                ->get()
                ->result_array();
        
    }

    function getStudentsEnlistedOnly($id,$sem = 0,$course=0,$year=0,$gender=0,$start=0,$end=0)
    {
        $faculty_id = $this->session->userdata("intID");
        $where = [];        
        if($course != 0)
            $where['tb_mas_users.intProgramID'] = $course;
        if($gender != 0){
            if($gender == 1){
                $gender = "male";
            }
            else
                $gender = "female";
            $where['tb_mas_users.enumGender'] = $gender;
        }        
        if($sem != 0)
            $where['tb_mas_registration.intAYID'] = $sem;
        // if($year != 0)
        //     $where['tb_mas_registration.intYearLevel'] = $year;        
        return  $this->db
                ->select("tb_mas_users.intID, tb_mas_users.strFirstname,tb_mas_users.strMiddlename,tb_mas_users.strLastname,strProgramCode,strStudentNumber,date_enlisted as date_added,tb_mas_faculty.strUsername as fusername")
                ->from("tb_mas_users")
                //->group_by("intSubjectID")             
                ->where($where)    
                ->where(array("tb_mas_registration.intROG"=>0,"date_enlisted >="=>$start,"date_enlisted <="=>$end))                                
                ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                ->join('tb_mas_programs','tb_mas_programs.intProgramID = tb_mas_users.intProgramID')
                ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_registration.enlisted_by','left')        
                ->group_by('tb_mas_users.intID')                                                       
                ->order_by('strLastName asc, strFirstname asc')
                ->get()
                ->result_array();
        
    }
    
    function checkStudentSubject($sem,$subjectId,$studentId)
    {
        return  current($this->db
                     ->select("tb_mas_classlist.*,tb_mas_subjects.strCode")
                     ->from("tb_mas_classlist_student")
                     ->where(array("tb_mas_classlist_student.intStudentID"=>$studentId,"tb_mas_classlist.intSubjectID"=>$subjectId,"tb_mas_classlist.strAcademicYear"=>$sem))
                     ->join('tb_mas_classlist', 'tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
                     ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
                     ->get()
                     ->result_array());
    }
    function checkStudentSubjectTaken($subjectId,$studentId)
    {
        return  current($this->db
                     ->select("tb_mas_classlist.*,tb_mas_subjects.strCode")
                     ->from("tb_mas_classlist_student")
                     ->where(array("tb_mas_classlist_student.intStudentID"=>$studentId,"tb_mas_classlist.intSubjectID"=>$subjectId,))
                        ->where("tb_mas_classlist_student.floatFinalGrade !=",'5')
                     ->join('tb_mas_classlist', 'tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
                     ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
                     ->get()
                     ->result_array());
    }
    
    function checkClasslistStudentNSTP($id,$year) 
    {
               
        return  $this->db
                     ->select("strCode,strAcademicYear")
                     ->from("tb_mas_classlist_student")
                     ->where(array("intStudentID"=>$id,"strAcademicYear"=>$year,"strCode regexp"=>'NSTP'))                    
                     ->join('tb_mas_classlist', 'tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')                     
                     ->group_by('strAcademicYear')   
                     
                     ->get()
                     ->result_array();
        
    }
    
    
    function getClassListStudentsSt($id,$classlist) 
    {

        $ret = [];        
               
        $cl =  $this->db
                    ->select("tb_mas_classlist_student.intCSID,intClassListID,strCode,strSection,intSubjectID,year,sub_section, strClassName, intLab, intLectHours, tb_mas_subjects.strDescription,floatFinalGrade as v3,floatMidtermGrade as v2,intFinalized,enumStatus,strRemarks,tb_mas_faculty.intID as facID, tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname, tb_mas_subjects.strUnits, tb_mas_subjects.intBridging, tb_mas_classlist.intID as classlistID, tb_mas_subjects.intID as subjectID,include_gwa")                                        
                    ->from("tb_mas_classlist_student")            
                    ->where(array("intStudentID"=>$id,"strAcademicYear"=>$classlist,'isDissolved'=>0))                                            
                    ->join('tb_mas_classlist', 'tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID')
                    ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID')
                    ->join('tb_mas_faculty','tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
                    ->join('tb_mas_curriculum','tb_mas_classlist.intCurriculumID = tb_mas_curriculum.intID')
                    ->join('tb_mas_programs','tb_mas_curriculum.intProgramID = tb_mas_programs.intProgramID')
                    ->order_by('strCode','asc')   
                    ->get()
                    ->result_array();

        
        foreach($cl as $c){
                $c['adjustments'] = $this->db->where(array('classlist_student_id'=> $c['subjectID'],'syid'=>$classlist,'student_id'=>$id))                                          
                                          ->order_by('date','desc')
                                          ->get('tb_mas_classlist_student_adjustment_log')
                                          ->first_row('array');

                $schedule = $this->getScheduleByCode($c['intClassListID']);        
                $sched_day = '';
                $sched_time = '';
                $sched_room = '';                
                
                if(isset($schedule[0]['strDay']))                                                
                    $sched_time = date('g:ia',strtotime($schedule[0]['dteStart'])).' - '.date('g:ia',strtotime($schedule[0]['dteEnd']));  
                        
                foreach($schedule as $sched) {
                    if(isset($sched['strDay']))
                        $sched_day.= $sched['strDayAbvr'];                    
                        //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
                }
                                                                    
                if(isset($schedule[0]['strDay']))
                    $sched_room = $schedule[0]['strRoomCode'];
    
                $c['sched_day'] = $sched_day;
                $c['sched_time'] = $sched_time;
                $c['sched_room'] = $sched_room;                                                           
                    
                $ret[] =  $c;

        }

        return $ret;
    }
    
    function getClassListStudentsAndInfo($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem=0) 
    {
               
        return  $this->db
                     ->select("tb_mas_classlist_student.intCSID,strCode,strSection , intLab, tb_mas_subjects.strDescription,tb_mas_classlist_student.floatFinalGrade as v3,intFinalized,enumStatus,strRemarks,tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname, tb_mas_subjects.strUnits, tb_mas_classlist.intID as classlistID, tb_mas_subjects.intID as subjectID")
                     ->from("tb_mas_classlist_student")
                        
                     ->join('tb_mas_classlist', 'tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->order_by('strCode','asc')   
                     ->get()
                     ->result_array();
        
    }
    
    function getCashiers() 
    {
               
        return  $this->db
                     ->select("tb_mas_cashier.*,tb_mas_faculty.strFirstname, tb_mas_faculty.strLastname")
                     ->from("tb_mas_cashier")
                        
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_cashier.user_id')                     
                     ->order_by('strLastname','asc')   
                     ->get()
                     ->result_array();
        
    }

    function getClasslistDetails($id)
    {
        $d = $this->db
             ->select('strSection,strClassName,year,sub_section,intLab,intSubjectID,intLectHours,intFacultyID,strCode')
             ->from('tb_mas_classlist')
             ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID')
             ->where('tb_mas_classlist.intID',$id)
             ->get()
             ->first_row();
        
        return $d;
    }

    function getClasslists($sem , $program, $dissolved, $has_faculty)
    {
        $ret = [];
        $where = array('tb_mas_classlist.strAcademicYear'=>$sem);
        if($has_faculty != 0)
            $where['intFacultyID !='] = 999;
        if($dissolved != 0)
            $where['isDissolved'] = 1;

        $classlists = $this->db
        ->select('tb_mas_classlist.intID,strProgramCode,strCode,tb_mas_subjects.strDescription as subjectDescription,strClassName,year,strSection,sub_section,slots,strLastname,strFirstname,strMiddlename,intFinalized,tb_mas_subjects.strUnits')
        ->from('tb_mas_classlist')
        ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID')
        ->join('tb_mas_faculty','tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
        ->join('tb_mas_curriculum','tb_mas_classlist.intCurriculumID = tb_mas_curriculum.intID')
        ->join('tb_mas_programs','tb_mas_curriculum.intProgramID = tb_mas_programs.intProgramID')
        ->where($where)
        ->get()
        ->result_array(); 

        foreach($classlists as $classlist){
            $classlist['slots_taken_enrolled'] = $this->db
                ->select('tb_mas_classlist_student.intCSID')                                
                ->from('tb_mas_classlist_student')
                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                ->where(array('intClassListID'=>$classlist['intID'],'intROG >'=>0))
                ->get()
                ->num_rows();

            $schedule = $this->getScheduleByCode($classlist['intID']);        
            $sched_day = '';
            $sched_time = '';
            $sched_room = '';                
            
            if(isset($schedule[0]['strDay']))                                                
                $sched_time = date('g:ia',strtotime($schedule[0]['dteStart'])).' - '.date('g:ia',strtotime($schedule[0]['dteEnd']));  
                    
            foreach($schedule as $sched) {
                if(isset($sched['strDay']))
                    $sched_day.= $sched['strDayAbvr'];                    
                    //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
            }
                                                                
            if(isset($schedule[0]['strDay']))
                $sched_room = $schedule[0]['strRoomCode'];

            $classlist['sched_day'] = $sched_day;
            $classlist['sched_time'] = $sched_time;
            $classlist['sched_room'] = $sched_room;

            $ret[] = $classlist;
        }

        return $ret;
    }

    function getClasslistsByFaculty($sem , $id)
    {
        $ret = [];
        $where = array('tb_mas_classlist.strAcademicYear'=>$sem,'tb_mas_classlist.intFacultyID'=>$id);        

        $classlists = $this->db
        ->select('tb_mas_classlist.intID,strProgramCode,strCode,tb_mas_subjects.strDescription as subjectDescription,strClassName,year,strSection,sub_section,slots,strLastname,strFirstname,strMiddlename,intFinalized,tb_mas_subjects.strUnits')
        ->from('tb_mas_classlist')
        ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID')
        ->join('tb_mas_faculty','tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
        ->join('tb_mas_curriculum','tb_mas_classlist.intCurriculumID = tb_mas_curriculum.intID')
        ->join('tb_mas_programs','tb_mas_curriculum.intProgramID = tb_mas_programs.intProgramID')
        ->where($where)
        ->get()
        ->result_array(); 

        foreach($classlists as $classlist){
            $classlist['slots_taken_enrolled'] = $this->db
                ->select('tb_mas_classlist_student.intCSID')                                
                ->from('tb_mas_classlist_student')
                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                ->where(array('intClassListID'=>$classlist['intID'],'intROG >'=>0))
                ->get()
                ->num_rows();

            $schedule = $this->getScheduleByCode($classlist['intID']);        
            $sched_day = '';
            $sched_time = '';
            $sched_room = '';                
            
            if(isset($schedule[0]['strDay']))                                                
                $sched_time = date('g:ia',strtotime($schedule[0]['dteStart'])).' - '.date('g:ia',strtotime($schedule[0]['dteEnd']));  
                    
            foreach($schedule as $sched) {
                if(isset($sched['strDay']))
                    $sched_day.= $sched['strDayAbvr'];                    
                    //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
            }
                                                                
            if(isset($schedule[0]['strDay']))
                $sched_room = $schedule[0]['strRoomCode'];

            $classlist['sched_day'] = $sched_day;
            $classlist['sched_time'] = $sched_time;
            $classlist['sched_room'] = $sched_room;

            $ret[] = $classlist;
        }

        return $ret;
    }

    function getClasslistById($id)
    {
        $ret = [];
        $where = array('tb_mas_classlist.intID'=>$id);        

        $classlist = $this->db
        ->select('tb_mas_classlist.intID,strProgramCode,strCode,tb_mas_subjects.strDescription as subjectDescription,strClassName,year,strSection,sub_section,slots,strLastname,strFirstname,strMiddlename,intFinalized,tb_mas_subjects.strUnits')
        ->from('tb_mas_classlist')
        ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID')
        ->join('tb_mas_faculty','tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
        ->join('tb_mas_curriculum','tb_mas_classlist.intCurriculumID = tb_mas_curriculum.intID')
        ->join('tb_mas_programs','tb_mas_curriculum.intProgramID = tb_mas_programs.intProgramID')
        ->where($where)
        ->get()
        ->first_row('array'); 

        
            $classlist['slots_taken_enrolled'] = $this->db
                ->select('tb_mas_classlist_student.intCSID')                                
                ->from('tb_mas_classlist_student')
                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                ->where(array('intClassListID'=>$classlist['intID'],'intROG >'=>0))
                ->get()
                ->num_rows();

            $schedule = $this->getScheduleByCode($classlist['intID']);        
            $sched_day = '';
            $sched_time = '';
            $sched_room = '';                
            
            if(isset($schedule[0]['strDay']))                                                
                $sched_time = date('g:ia',strtotime($schedule[0]['dteStart'])).' - '.date('g:ia',strtotime($schedule[0]['dteEnd']));  
                    
            foreach($schedule as $sched) {
                if(isset($sched['strDay']))
                    $sched_day.= $sched['strDayAbvr'];                    
                    //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
            }
                                                                
            if(isset($schedule[0]['strDay']))
                $sched_room = $schedule[0]['strRoomCode'];

            $classlist['sched_day'] = $sched_day;
            $classlist['sched_time'] = $sched_time;
            $classlist['sched_room'] = $sched_room;

        return $classlist;
    }

    
    function getAllClasslist($sem,$dept = null,$admin=false)
    {
         $this->db
             ->select('tb_mas_classlist.*,tb_mas_subjects.strCode')
             ->from('tb_mas_classlist')
             ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID');
            
             $where['tb_mas_classlist.strAcademicYear'] = $sem;
             
            if($dept!=null && !$admin)
                 $where['tb_mas_subjects.strDepartment'] = $dept;
        
             $this->db->where($where);
            
           $d =
            $this->db
                 ->get()
                 ->result_array();
        
        return $d;
    }
    
    function getAllClasslistAssigned($sem,$dept = null,$admin=false)
    {
         $this->db
             ->select('tb_mas_classlist.*,tb_mas_subjects.strCode')
             ->from('tb_mas_classlist')
             ->join('tb_mas_subjects','intSubjectID = tb_mas_subjects.intID');
            
             $where['tb_mas_classlist.strAcademicYear'] = $sem;
             //$where['tb_mas_classlist.intFacultyID !='] = 999; filter for unassigned
            // if($dept!=null && !$admin)
            //      $where['tb_mas_subjects.strDepartment'] = $dept;
        
             $this->db->where($where);
            
           $d =
            $this->db
                 ->get()
                 ->result_array();
        
        return $d;
    }
    
    function getScheduleByCodeNew($schedCode){
        $ret = array();
        $sched = $this->db
                        ->select('intRoomSchedID,strDay, dteStart, dteEnd, strRoomCode,strSection,strCode')
                        ->from('tb_mas_room_schedule')
                        ->where(array('strScheduleCode'=>$schedCode))
                        ->join('tb_mas_classrooms','tb_mas_room_schedule.intRoomID = tb_mas_classrooms.intID')
                        ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode')
                        ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                        ->get()
                        ->result_array();
        
        $schedString = "";
        $timeString = "";
        foreach($sched as $s)
        {
            $s['strDay'] = $s['strDay'] + 1;
            $s['hourdiff'] = round((strtotime($s['dteEnd']) - strtotime($s['dteStart']))/3600, 1);
            $s['st'] = date('gia',strtotime($s['dteStart']));

            $ret[] = $s;
        }

        return $ret;
    }
    
    function getScheduleByCode($schedCode)
    {
       $ret = array();
       $sched = $this->db
                        ->select('intRoomSchedID,strDay, dteStart, dteEnd, strRoomCode,strSection,strCode')
                        ->from('tb_mas_room_schedule')
                        ->where(array('strScheduleCode'=>$schedCode))
                        ->join('tb_mas_classrooms','tb_mas_room_schedule.intRoomID = tb_mas_classrooms.intID')
                        ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode')
                        ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                        ->get()
                        ->result_array();
        
        $schedString = "";
        $timeString = "";
        foreach($sched as $s)
        {
            
            $s['strDayAbvr'] = get_day_abvr($s['strDay']);
            $s['strDay'] = get_day($s['strDay']);
            $s['hourdiff'] = round((strtotime($s['dteEnd']) - strtotime($s['dteStart']))/3600, 1);
            $s['dteStartF'] = date('gia',strtotime($s['dteStart']));
            
            if(!empty($s)){
                $schedString.= $s['strDayAbvr'];
                $timeString = date('g:ia',strtotime($s['dteStart'])).'  '.date('g:ia',strtotime($s['dteEnd']))." ";
                $timeString.= $s['strRoomCode'];
            }            
            $ret[] = $s;
        }
        
        $ret['schedString'] = $schedString." ".$timeString;
        
        return $ret;                
        
    }
    
    function getScheduleBySection($section,$sem)
    {
       $ret = array();
       $sched = $this->db
                        ->select('intRoomSchedID,strDay, dteStart, dteEnd, strRoomCode,strSection,strCode,strLastname,strFirstname')
                        ->from('tb_mas_room_schedule')
                        ->join('tb_mas_classrooms','tb_mas_room_schedule.intRoomID = tb_mas_classrooms.intID')
                        ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode')
                        ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                        ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                        ->where(array('tb_mas_classlist.strSection'=>$section,'tb_mas_room_schedule.intSem'=>$sem))
                        ->order_by('strDay ASC, dteStart ASC')
                        ->get()
                        ->result_array();
       foreach($sched as $s)
        {
            $s['strDay'] = get_day($s['strDay']);
            $ret[] = $s;
        }
        
        return $ret;                
        
    }

    function getBlockSectionsPerProgram($program, $sem, $year = 1){
        return $this->db->get_where('tb_mas_block_sections',array('intProgramID'=> $program, 'intSYID' => $sem, 'year' => $year))
                        ->result_array();
    }

    function getScheduleBySectionNew($section, $sem){

        $ret = array();
        $sched = $this->db
                        ->select('intRoomSchedID,strDay, dteStart, dteEnd, strRoomCode,strSection,strCode,strLastname,strFirstname')
                        ->from('tb_mas_room_schedule')
                        ->join('tb_mas_classrooms','tb_mas_room_schedule.intRoomID = tb_mas_classrooms.intID')
                        ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode')
                        ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                        ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                        ->where(array('tb_mas_room_schedule.blockSectionID'=>$section,'tb_mas_room_schedule.intSem'=>$sem))
                        ->order_by('strDay ASC, dteStart ASC')
                        ->get()
                        ->result_array();
        
        foreach($sched as $s)
        {
            $s['strDay'] = $s['strDay'] + 1;
            $s['hourdiff'] = round((strtotime($s['dteEnd']) - strtotime($s['dteStart']))/3600, 1);
            $s['st'] = date('gia',strtotime($s['dteStart']));
            $ret[] = $s;
        }
        
        return $ret;   
    }
    
    
    function getScheduleByRoomID($id,$sem)
    {
         $ret = array();
       $sched = $this->db
                        ->select('tb_mas_room_schedule.*,strSection,strCode,tb_mas_faculty.strLastname,tb_mas_faculty.strFirstname')
                        ->from('tb_mas_room_schedule')
                        ->where(array('intRoomID'=>$id,'tb_mas_room_schedule.intSem'=>$sem))
                        ->join('tb_mas_classrooms','tb_mas_room_schedule.intRoomID = tb_mas_classrooms.intID')
                        ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode')
                        ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                        ->join('tb_mas_faculty','tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                        ->order_by('strDay ASC, dteStart ASC')
                        ->get()
                        ->result_array();
       foreach($sched as $s)
        {
            $s['strDay'] = get_day($s['strDay']);
            $ret[] = $s;
        }
        
        return $ret;
    }
    
    function getSchedule($id,$dept=null,$admin=false)
    {
        
        $this->db
            ->select('tb_mas_room_schedule.*,strSection,strCode,tb_mas_subjects.strDepartment, tb_mas_classlist.strClassName, tb_mas_classlist.year')
            ->from('tb_mas_room_schedule');
            
        $where = array('intRoomSchedID'=>$id);
        
        if($dept!=null && !$admin)
            $where['tb_mas_subjects.strDepartment'] = $dept;
        
        $this->db->where($where);

        $sched =
            $this->db
            ->join('tb_mas_classrooms','tb_mas_room_schedule.intRoomID = tb_mas_classrooms.intID')
            ->join('tb_mas_classlist','tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode')
            ->join('tb_mas_subjects','tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
            ->get()
            ->result_array();

         $sched = current($sched);
        if(!empty($sched))
            $sched['strDay'] = get_day($sched['strDay']);
        return $sched;
        
        
        
    }
    
    function get_subjects_by_course($curriculum)
    {
       $subjects = $this->db
                         ->select( 'tb_mas_subjects.intID,tb_mas_curriculum_subject.intYearLevel,tb_mas_curriculum_subject.intSem,tb_mas_subjects.strCode, tb_mas_subjects.strDescription')
                         ->from('tb_mas_subjects')
                         ->join('tb_mas_curriculum_subject','tb_mas_curriculum_subject.intSubjectID = tb_mas_subjects.intID')
                         ->where('tb_mas_curriculum_subject.intCurriculumID',$curriculum)
                         ->order_by('intYearLevel asc,intSem asc')
                         ->get()
                         ->result_array();
        
        return $subjects;
    }
    
    function get_curriculum_by_course($course)
    {
        $query = "SELECT * from tb_mas_curriculum WHERE intProgramID = ".$course;
        
        return $this->db
                    ->query($query)
                    ->result_array();
    }
   
    function fetch_classlist($id)
    {
        $faculty_id = $this->session->userdata("intID");
        return  $this->db
                     ->select("tb_mas_classlist.intID as intID, slots, grading_system, strSection, intFacultyID,intSubjectID, strClassName,strCode,intFinalized,strAcademicYear,enumSem,strYearStart,strYearEnd, tb_mas_classlist.strUnits as strUnits")
                     ->from("tb_mas_classlist")
                     //->group_by("intSubjectID")
                     ->where(array("intFacultyID"=>$faculty_id,"tb_mas_classlist.intID"=>$id))
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                     ->get()
                     ->result_array();
    }
    
    function fetch_classlist_delete($id)
    {
        return  $this->db
                     ->select("intFinalized,intFacultyID")
                     ->from("tb_mas_classlist")
                     //->group_by("intSubjectID")
                     ->where(array("tb_mas_classlist.intID"=>$id))
                     ->get()
                     ->result_array();
    }
    
  
    /*
    newly added function - 10/23/2015 -
        fetching classlists by faculty
    */
    function fetch_classlist_by_faculty($id, $classlist)
    {
        $faculty_id = $id;
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, slots, intFacultyID,intSubjectID, strClassName,strCode,strDescription, strSection, year, sub_section, intFinalized,strAcademicYear,enumSem,strYearStart,strYearEnd, tb_mas_subjects.strUnits")
                     ->from("tb_mas_classlist")
                     ->where(array("intFacultyID"=>$faculty_id, "strAcademicYear"=>$classlist));

                     $this->db->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                     ->order_by('strSection','asc');
                 return $this->db 
                        ->get()
                        ->result_array();
    }
    
    function fetch_classlist_by_sujects($id,$sem=null)
    {
        $subject_id = $id;
                    $this->db
                     ->select("tb_mas_classlist.intID as intID, slots, year, sub_section, intFacultyID,intSubjectID,strClassName,strCode,strSection,intFinalized,strAcademicYear,enumSem,strYearStart,strYearEnd, tb_mas_faculty.strLastname, tb_mas_faculty.strFirstname")
                     ->from("tb_mas_classlist")
                     ->where(array("intSubjectID"=>$subject_id,'strAcademicYear'=>$sem));
//                    if($sem)
//                        $this->db->where(array('enumStatus'=>'active'));
//                    
//                    if($sem_sel!=null)
//                        $this->db->where(array('strAcademicYear'=>$sem_sel));
                     $this->db->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                    ->order_by('strSection','asc');
                   
//                if($limit != null)
//                    $this->db->limit($limit);
                 return $this->db 
                        ->get()
                        ->result_array();
    }
     function fetch_sections_by_program($id,$sem=null)
    {
         $program_id= $id;
                    $this->db
                     ->select("tb_mas_classlist.intID, tb_mas_classlist.strSection")
                     ->from("tb_mas_classlist");
         
                     $this->db->where(array("tb_mas_classlist.strSection LIKE"=>$program_id . "%",'strAcademicYear'=>$sem))
                    ->group_by("strSection")
                    ->order_by('strSection','asc');
                   
                 return $this->db 
                        ->get()
                        ->result_array();
    }
    
    function fetch_classlist_by_section($id,$sem=null)
    {
         $sectionID = $id;
                    $this->db
                     ->select("tb_mas_classlist.intID, slots, grading_system, tb_mas_classlist.strClassName,tb_mas_classlist.intSubjectID, tb_mas_subjects.strDescription, COUNT(tb_mas_classlist_student.intStudentID) as intNumOfStudents, strLastname, strFirstname")
                     ->from("tb_mas_classlist")
                     ->where(array("tb_mas_classlist.strSection"=>$sectionID,'strAcademicYear'=>$sem))
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_classlist_student', 'tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID', 'Left')
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->order_by('strClassName','asc');
         
                 return $this->db 
                        ->group_by('tb_mas_classlist.intID')
                        ->get()
                        ->result_array();
    }
     /**********************************************
     newly added function -11-04-2014 ^______^
    *********************************************/
    function getTotalUnits($id) 
    {
                    return $this->db->select_sum('tb_mas_classlist_student.strUnits')
                     ->from("tb_mas_classlist_student")
                     ->where(array("intStudentID"=>$id))
                     ->join('tb_mas_classlist', 'tb_mas_classlist.intID = tb_mas_classlist_student.intClasslistID')
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_faculty', 'tb_mas_faculty.intID = tb_mas_classlist.intFacultyID')
                     ->get()
                     ->result_array();

    }

    function fetch_classlist_all($id)
    {
        //$faculty_id = $this->session->userdata("intID");
        return  $this->db
                     ->select("tb_mas_classlist.intID as intID, grading_system, slots, intFacultyID,intSubjectID,strClassName,strCode,intFinalized,strAcademicYear,enumSem,strYearStart,strYearEnd")
                     ->from("tb_mas_classlist")
                     //->group_by("intSubjectID")
                     ->where(array("tb_mas_classlist.intID"=>$id))
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                     ->get();
                    
    }
    
    function fetch_classlist_section($subject,$section)
    {
        //$faculty_id = $this->session->userdata("intID");
        return  current($this->db
                     ->select("tb_mas_classlist.intID as intID, slots, grading_system, intFacultyID,intSubjectID,strClassName,strCode,intFinalized,strAcademicYear,enumSem,strYearStart,strYearEnd")
                     ->from("tb_mas_classlist")
                     //->group_by("intSubjectID")
                     ->where(array("tb_mas_classlist.strClassName"=>$subject,"tb_mas_classlist.intSubjectID"=>$section))
                     ->join('tb_mas_subjects', 'tb_mas_subjects.intID = tb_mas_classlist.intSubjectID')
                     ->join('tb_mas_sy', 'tb_mas_sy.intID = tb_mas_classlist.strAcademicYear')
                     ->get()
                     ->result_array()
                       );
                    
    }
    
    //Room Conflict
    function schedule_conflict($post,$id=null,$sem,$d=null)
    {
        $res = array();
        
        if($post['intRoomID'] != 99999){ //IF room is TBA
            $query ="SELECT intRoomSchedID,strCode,strSection,year,strClassName,sub_section
                    FROM tb_mas_room_schedule
                    JOIN tb_mas_classlist ON tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode
                    JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID
                    WHERE
                    (
                    (dteStart >= '".$post['dteStart']."' AND dteEnd <= '".$post['dteEnd']."') OR
                    (dteStart < '".$post['dteEnd']."' AND dteEnd >= '".$post['dteEnd']."') OR 
                    (dteStart <= '".$post['dteStart']."' AND dteEnd > '".$post['dteStart']."') 
                    ) AND tb_mas_room_schedule.intRoomID != 99999";
            if($id!=null)
            {
                $query .= " AND intRoomSchedID != ".$id;
            }

            if($d == null)
                $query .=" AND strDay = '".$post['strDay']."' AND intRoomID = '".$post['intRoomID']."' AND tb_mas_room_schedule.intSem = ".$sem."
                    ";
            else
            {
                $query .=" AND ( ";
                for($i=0;$i<count($d);$i++)
                {
                    if($i == count($d)-1)
                        $query .="strDay = ".$d[$i]." ) ";
                    else
                        $query .="strDay = ".$d[$i]." OR ";
                }

                $query .= "AND intRoomID = '".$post['intRoomID']."' AND tb_mas_room_schedule.intSem = ".$sem." ";
            }

            // echo $query."<br />";
            //print_r($this->db->query($query)->result_array());
            //die();
            $res = $this->db->query($query)->result_array();
        }
        
        return $res;
    }
    
    function section_conflict($post,$id=null,$section,$sem,$d=null)
    {
        $query ="SELECT intRoomSchedID,strCode,strSection,year,strClassName,sub_section
                FROM tb_mas_room_schedule
                JOIN tb_mas_classlist ON tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode
                JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID
                WHERE
                (
                (dteStart >= '".$post['dteStart']."' AND dteEnd <= '".$post['dteEnd']."') OR
                (dteStart < '".$post['dteEnd']."' AND dteEnd >= '".$post['dteEnd']."') OR 
                (dteStart <= '".$post['dteStart']."' AND dteEnd > '".$post['dteStart']."')
                )";
        if($id!=null)
        {
            $query .= " AND intRoomSchedID != ".$id;
        }
        if($d == null)
            $query .=" AND strDay = ".$post['strDay']." AND blockSectionID = '".$section."' AND tb_mas_room_schedule.intSem = ".$sem;
        else
        {
            $query .=" AND ( ";
            for($i=0;$i<count($d);$i++)
            {
                if($i == count($d)-1)
                    $query .="strDay = ".$d[$i]." ) ";
                else
                    $query .="strDay = ".$d[$i]." OR ";
            }
            $query .= "AND blockSectionID = '".$section."' AND tb_mas_room_schedule.intSem = ".$sem;
        }
        // echo $query."<br />";
        //print_r($this->db->query($query)->result_array());
        //die();
        return $this->db->query($query)->result_array();
    }
    
    function faculty_conflict($post,$id=null,$faculty_id,$sem,$d=null)
    {
        $query ="SELECT intRoomSchedID,strCode,strSection,year,strClassName,sub_section
                FROM tb_mas_room_schedule
                JOIN tb_mas_classlist ON tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode
                JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID
                WHERE
                (
                (dteStart >= '".$post['dteStart']."' AND dteEnd <= '".$post['dteEnd']."') OR
                (dteStart < '".$post['dteEnd']."' AND dteEnd >= '".$post['dteEnd']."') OR 
                (dteStart <= '".$post['dteStart']."' AND dteEnd > '".$post['dteStart']."')
                )";
        if($id!=null)
        {
            $query .= " AND intRoomSchedID != ".$id;
        }
        if($d == null)
            $query .=" AND strDay = '".$post['strDay']."' AND `intFacultyID` = ".$faculty_id." AND tb_mas_room_schedule.intSem = ".$sem." ";
        else
        {
            $query .=" AND ( ";
            for($i=0;$i<count($d);$i++)
            {
                if($i == count($d)-1)
                    $query .="strDay = ".$d[$i]." ) ";
                else
                    $query .="strDay = ".$d[$i]." OR ";
            }
            
            $query .= "AND `intFacultyID` = ".$faculty_id." AND tb_mas_room_schedule.intSem = ".$sem." ";
        }
        // echo $query."<br />";
        //print_r($this->db->query($query)->result_array());
        //die();
        return $this->db->query($query)->result_array();
    }

    function student_conflict($csid,$record,$sem)
    {
        $classlist_sched = $this->db->get_where('tb_mas_room_schedule',array('strScheduleCode'=>$record['intClassListID']))->result_array();
        $results = [];
        if(!empty($classlist_sched)){
            foreach($classlist_sched as $sched){
                $query ="SELECT intRoomSchedID,strCode,strSection,strClassName,year,sub_section
                        FROM tb_mas_room_schedule
                        JOIN tb_mas_classlist ON tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode                
                        JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID                
                        WHERE
                        (
                        (dteStart >= '".$sched['dteStart']."' AND dteEnd <= '".$sched['dteEnd']."') OR
                        (dteStart < '".$sched['dteEnd']."' AND dteEnd >= '".$sched['dteEnd']."') OR 
                        (dteStart <= '".$sched['dteStart']."' AND dteEnd > '".$sched['dteStart']."')
                        )";
            
                
                $query .=" AND strDay = '".$sched['strDay']."' AND tb_mas_classlist.intID = ".$csid." AND tb_mas_room_schedule.intSem = ".$sem." ";
            
                // echo $query."<br />";
                //print_r($this->db->query($query)->result_array());
                //die();
                 $ret = $this->db->query($query)->first_row();
                 
                 if($ret)
                    $ret->conflict = $record;
                
                $results[] = $ret;
            }

            
        }
        return $results;
        
    }
}