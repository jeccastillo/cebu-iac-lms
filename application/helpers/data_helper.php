<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('load_ci')) {

    function load_ci() {
        return get_instance();
    }

}
function getGradeAverages($sem)
{
    $ci = load_ci();
    $data = $ci->db
                 ->select('COUNT(floatFinalGrade) as ctr, floatFinalGrade')
                 ->from('tb_mas_classlist_student')
                 ->join('tb_mas_classlist','tb_mas_classlist_student.intClasslistID = tb_mas_classlist.intID')
                 ->where(array('strAcademicYear'=>$sem))
                 ->group_by('tb_mas_classlist_student.floatFinalGrade')
                 ->get()
                 ->result_array();
    
    
    $ret = array();
    
    foreach($data as $d)
    {
        $index = str_replace('.','',$d['floatFinalGrade']);
        $ret[$index] = $d['ctr'];    
    }
    
    return $ret;   
    
}
function getGradeAveragesSubject($sem,$subject)
{
    $ci = load_ci();
    $data = $ci->db
                 ->select('COUNT(floatFinalGrade) as ctr, floatFinalGrade')
                 ->from('tb_mas_classlist_student')
                 ->join('tb_mas_classlist','tb_mas_classlist_student.intClasslistID = tb_mas_classlist.intID')
                 ->where(array('strAcademicYear'=>$sem,'intSubjectID'=>$subject))
                 ->group_by('tb_mas_classlist_student.floatFinalGrade')
                 ->get()
                 ->result_array();
    
    
    $ret = array();
    
    foreach($data as $d)
    {
        $index = str_replace('.','',$d['floatFinalGrade']);
        $ret[$index] = $d['ctr'];    
    }
    
    return $ret;    
    
}
if (!function_exists('getUnitPrice')) {
    function getUnitPrice($ty,$type){
        switch($type){
            case 'regular':
                $ret = $ty['pricePerUnit'];
                break;
            case 'online':
                $ret = $ty['pricePerUnitOnline'];
                break;
            case 'hyflex':
                $ret = $ty['pricePerUnitHyflex'];
                break;
            case 'hybrid':
                $ret = $ty['pricePerUnitHybrid'];
                break;
            default:
                $ret = $ty['pricePerUnit'];

        }
        return $ret;
    }
}
if (!function_exists('getExtraFee')) {
    function getExtraFee($entry, $type, $pretext){        
        switch($type){
            case 'regular':
                $ret = $entry[$pretext.'Regular'];
                break;
            case 'online':
                $ret = $entry[$pretext.'Online'];
                break;
            case 'hyflex':
                $ret = $entry[$pretext.'Hyflex'];
                break;
            case 'hybrid':
                $ret = $entry[$pretext.'Hybrid'];
                break;
            default:
                $ret = $entry[$pretext.'Regular'];
        }
        return $ret;
    }
}

function getRegistrationDataCourse($sem,$course)
{
    $ci = load_ci();
    $data['enrolled'] = $ci->db
                             ->select('intROG')
                             ->from('tb_mas_users')
                             ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                             ->where(array('intAYID'=>$sem,'intROG'=>1,'intProgramID'=>$course))
                             ->get()
                             ->num_rows();
    $data['registered'] = $ci->db
                             ->select('intROG')
                             ->from('tb_mas_users')
                             ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                             ->where(array('intAYID'=>$sem,'intROG'=>0,'intProgramID'=>$course))
                             ->get()
                             ->num_rows();
    $data['cleared'] = $ci->db
                             ->select('intROG')
                             ->from('tb_mas_users')
                             ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                             ->where(array('intAYID'=>$sem,'intROG'=>2,'intProgramID'=>$course))
                             ->get()
                             ->num_rows();

    return $data;
}

if (!function_exists('get_comments')) {

    function get_comments($id) {
        $ci = load_ci();		
		return $ci->db->get_where('mcs_comments',array('announcement_id'=>$id))->result_array();
    }

}
if (!function_exists('get_enum_values')) {
    function get_enum_values( $table, $field )
    {
        $ci = load_ci();
        $type = $ci->db->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )->row( 0 )->Type;
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $enum = explode("','", $matches[1]);
        return $enum;
    }
}

if (!function_exists('getEquivalent')) {
        
    function getEquivalent($ave)
        {
            if (strval($ave) == 'inc')
                $eq = "3.50";
            else if (strval($ave) == 'UD')
                $eq = "5.00";
            else if ($ave >= 97.5 && $ave <= 100)
                $eq = "1.00";
                
            else if ($ave >= 94.5 && $ave <= 97.49)
                $eq = "1.25";
                
            else if ($ave >= 91.5 && $ave <= 94.49)
                $eq = "1.50";
                
            else if ($ave >= 88.5 && $ave <= 91.49)
                $eq = "1.75";
                
            else if ($ave >= 85.5 && $ave <= 88.49)
                $eq = "2.00";
                
            else if ($ave >= 82.5 && $ave <= 85.49)
                $eq = "2.25";
            
            else if ($ave >= 79.5 && $ave <= 82.49)
                $eq = "2.50";
                
            else if ($ave >= 76.5 && $ave <= 79.49)
                $eq = "2.75";
            else if ($ave >= 74.5 && $ave <= 76.49)
                $eq = "3.00";
            else if ($ave > 0 && $ave <= 74.49)
                $eq = "5.00";
            else if ($ave <= 0)
                $eq = "0.00";
            /*
        
            if ($ave <= 0)
                $eq = "0.00";
            else if($ave <= 74.44 && $ave > 0)  
                $eq = "5.00";
            else if($ave <= 76.44 && $ave > 74.44)
                $eq = "3.00";
            else if($ave <= 79.44 && $ave > 76.44)
                $eq = "2.75";
            else if($ave <= 82.44 && $ave > 79.44)
                $eq = "2.50";
            else if($ave <= 85.44 && $ave > 82.44)
                $eq = "2.25";
            else if($ave <= 88.44 && $ave > 85.44)
                $eq = "2.00";
            else if($ave <= 91.44 && $ave > 88.44)
                $eq = "1.75";
            else if($ave <= 94.44 && $ave > 91.44)
                $eq = "1.50";
            else if($ave <= 97.44 && $ave > 94.44)
                $eq = "1.25";
            else if($ave > 97.44)
                $eq = "1.00";
            /*else  if (strval($ave) == "INC")
                $eq = "inc";*/   
            return $eq;
        }
}
/*
if (!function_exists('getEquivalent')) {
        
    function getEquivalent($ave)
        {
            if($ave < 74.5) 
                $eq = "5.00";
            else if($ave >= 74.5 && $ave <= 76)
                $eq = "3.00";
            else if($ave >=77 && $ave <= 79)
                $eq = "2.75";
            else if($ave >=80 && $ave <=82)
                $eq = "2.50";
            else if($ave >= 83 && $ave <=85)
                $eq = "2.25";
            else if($ave >= 86 && $ave <= 88)
                $eq = "2.00";
            else if($ave >= 89 && $ave <= 91)
                $eq = "1.75";
            else if($ave >= 92 && $ave <= 94)
                $eq = "1.50";
            else if($ave >= 95 && $ave <= 97)
                $eq = "1.25";
            else if($ave >= 98 && $ave <= 100)
                $eq = "1.00";
            else
                $eq = "1.00";
                
            return $eq;
        }
}*/

if (!function_exists('getRemarks')) {
        
    function getRemarks($eq)
        {
		    if (intval($eq) == 3.50)
                $ret = "lack of reqts.";
            else if(intval($eq) >=1 && intval($eq) <= 3.0) 
                $ret = "Passed";
            else if (intval($eq) == 5.00)
				$ret = "Failed";
            else if (intval($eq) == 0)
				$ret = "Officially Dropped";
            else if (intval($eq) < 1)
                 $ret = "--";
            
            return $ret;
        }
}

if (!function_exists('get_stype')) {
        
    function get_stype($level)
    {   
        switch($level){
            case 'shs':
                $stype = 'shs';
            break;
            case 'drive':
                $stype = 'shs';
            break;
            case 'college':
                $stype = 'college';
            break;
            case 'other':
                $stype = 'college';
            break;
            default: 
                $stype = 'college';
        }
        return $stype;
    }
}


if (!function_exists('getAve')) {
        
    function getAve($v1, $v2, $v3)
        {   
            $ave = number_format(round(($v1 *.30) + ($v2 *.30) + ($v3 * .40), 2), 2);
            return $ave;
        }
}

if (!function_exists('get_label')) {

    function get_label($table,$field) {
        $ci = load_ci();		
		$label = $ci->db->get_where('su-tb_sys_validation',array('strTable'=>$table,'strField'=>$field))->result_array();				
		if(empty($label))
			return $field;
		else
		{			
			$return = $label[0]['strLabel'];
			
			if($label[0]['strRequired'])
				$return.=" *";
			
			return $return;
		}
    }

}

if (!function_exists('get_type_id')) {

    function get_type_id($type) {
        $ci = load_ci();		
		$type = $ci->db->get_where('tb_mas_content_types',array('strName'=>$type))->first_row();				
		if(empty($type))
			return 1;
		else
		{									
			return $type->intID;
		}
    }

}

if (!function_exists('get_community_id')) {

    function get_community_id($community) {
        $ci = load_ci();		
		$community = $ci->db->get_where('tb_mas_community',array('strName'=>$community))->first_row();				
		if(empty($community))
			return 1;
		else
		{									
			return $community->intID;
		}
    }

}

if (!function_exists('get_type_name')) {

    function get_type_name($id) {
        $ci = load_ci();		
		$type = $ci->db->get_where('tb_mas_content_types',array('intID'=>$id))->first_row();				
		if(empty($type))
			return 1;
		else
		{									
			return $type->strName;
		}
    }

}

if (!function_exists('get_name_from_id')) {

    function get_name_from_id($id,$table) {
        $ci = load_ci();		
		$type = $ci->db->get_where($table,array('intID'=>$id))->first_row();				
		if(empty($type))
			return 1;
		else
		{
			if(isset($type->strName))
				return $type->strName;
			else if(isset($type->strTitle))
				return $type->strTitle;
            else if(isset($type->strCode))
                return $type->strCode;    
            else if(isset($type->strUsername))
                return $type->strUsername;
		}
    }

}



if (!function_exists('get_enum_values')) {
	
	function get_enum_values( $table, $field )
	{
		
		$ci = load_ci();
		$type = $ci->db->query( "SHOW COLUMNS FROM `".$table."` WHERE Field = '{$field}'" )->row( 0 )->Type;		
		preg_match('/^enum\((.*)\)$/', $type, $matches);		
		
		foreach( explode(',', $matches[1]) as $value )
		{
			 $enum[] = trim( $value, "'" );
		}
		
		return $enum;
	}
}

if (!function_exists('pw_hash')) {

    function pw_hash($string) {
        $ci = load_ci();
		$ci->load->library("salting");

		$key = date('YmdHis');
		$key.= $ci->config->item("encryption_key");
				
		$ci->salting->set_hash(md5($key));
		//$ci->salting->set_hash(md5($ci->config->item("encryption_key")));
		$ci->salting->set_string($string);
		return $ci->salting->hash_string();
    }
}
if (!function_exists('pw_unhash')) {

    function pw_unhash($string) {
        $ci = load_ci();
		$ci->load->library("salting");

/* 		$key = date('YmdHis');
		$key.= $ci->config->item("encryption_key");
		
		$ci->salting->set_hash(md5($key)); */
		//$ci->salting->set_hash(md5($ci->config->item("encryption_key")));
		$ci->salting->set_string($string);
		return $ci->salting->unhash_string();;
    }
}

if (!function_exists('simplify_title')) {
	
	function simplify_title ($title) {

	    $title = strtolower($title);
	    $title = strip_tags($title);
	    $title = stripslashes($title);
	    $title = html_entity_decode($title);

	    $title = trim(str_replace('\'', '', $title));
		$title = trim(str_replace(' ','_', $title));
	    $title = trim(preg_replace('/[^A-Za-z0-9_]/', '_', $title));
	
		return $title;
	
	}
}

if(!function_exists('get_day'))
{
    function get_day($day)
    {
        switch($day)
            {
                case '1':
                    $day = "Mon";
                    break;
                case '2':
                    $day = "Tue";
                    break;
                case '3':
                    $day = "Wed";
                    break;
                case '4':
                    $day = "Thu";
                    break;
                case '5':
                    $day = "Fri";
                    break;
                case '6':
                    $day = "Sat";
                    break;
            }
        
            return $day;
    }
}

if(!function_exists('get_day_abvr'))
{
    function get_day_abvr($day)
    {
        switch($day)
            {
                case '1':
                    $day = "M";
                    break;
                case '2':
                    $day = "T";
                    break;
                case '3':
                    $day = "W";
                    break;
                case '4':
                    $day = "Th";
                    break;
                case '5':
                    $day = "F";
                    break;
                case '6':
                    $day = "S";
                    break;
            }
        
            return $day;
    }
}
if(!function_exists('switch_num'))
{
    function switch_num($num)
    {
        switch($num)
            {
                case '1':
                    $num = "1st";
                    break;
                case '2':
                    $num = "2nd";
                    break;
                case '3':
                    $num = "3rd";
                    break;
                case '4':
                    $num = "4th";
                    break;
                case '5':
                    $num = "5th";
                    break;
                case '6':
                    $num = "6th";
                    break;
            }
        
            return $num;
    }
}

if(!function_exists('switch_num_rev'))
{
    function switch_num_rev($num)
    {
        switch($num)
            {
                case '1st':
                    $num = "1";
                    break;
                case '2nd':
                    $num = "2";
                    break;
                case '3rd':
                    $num = "3";
                    break;
                case '4th':
                    $num = "4";
                    break;
                case '5th':
                    $num = "5";
                    break;
                case '6th':
                    $num = "6";
                    break;
            }
        
            return $num;
    }
}

if(!function_exists('switch_num_word'))
{
    function switch_num_word($num)
    {
        switch($num)
            {
                case '1st':
                    $num = "First";
                    break;
                case '2nd':
                    $num = "Second";
                    break;
                case '3rd':
                    $num = "Third";
                    break;
                case '4th':
                    $num = "Fourth";
                    break;
                case '5th':
                    $num = "Fifth";
                    break;
                case '6th':
                    $num = "Sixth";
                    break;
            }
        
            return $num;
    }
}
if(!function_exists('increment_student_number'))
{
    function increment_student_number($matches)
    {
        if(isset($matches[1]))
        {
            $length = strlen($matches[1]);
            return sprintf("%0".$length."d", ++$matches[1]);
        }    
    }
}
if(!function_exists('switch_num_rev_search'))
{
    function switch_num_rev_search($num)
    {
        switch($num)
            {
                case '1st':
                    $num = "01";
                    break;
                case '2nd':
                    $num = "02";
                    break;
                case '3rd':
                    $num = "03";
                    break;
                case '4th':
                    $num = "04";
                    break;
                case '5th':
                    $num = "05";
                    break;
                case '6th':
                    $num = "06";
                    break;
            }
        
            return $num;
    }
}

if(!function_exists('switch_day_schema'))
{
    function switch_day_schema($num)
    {
        switch($num)
            {
                case '1 3 5':
                    $num = "M W F";
                    break;
                case '1 3':
                    $num = "M W";
                    break;
                case '2 4 6':
                    $num = "T TH S";
                    break;
                case '2 4':
                    $num = "T TH";
                    break;
                case '1 2 3 4 5 6':
                    $num = "ALL";
                    break;
                case '3 5':
                    $num = "W F";
                    break;
            }
        
            return $num;
    }
}
if(!function_exists('switch_day'))
{
    function switch_day($num)
    {
        switch($num)
            {                
                case 1:
                    $num = "Mon";
                    break;
                case 2:
                    $num = "Tue";
                    break;
                case 3:
                    $num = "Wed";
                    break;
                case 4:
                    $num = "Thu";
                    break;
                case 5:
                    $num = "Fri";
                    break;
                case 6:
                    $num = "Sat";
                    break;
                case 7:
                    $num = "Sun";
                    break;
            }
        
            return $num;
    }
}

if(!function_exists('switch_user_level'))
{
    function switch_user_level($num)
    {
        switch($num)
            {
                case 0:
                    $num = "Academics";
                break;
                case 1:
                    $num = "Building Admin";
                break;
                case 2:
                    $num = "Super Admin";
                break;
                case 3:
                    $num = "Registrar";
                break;
                case 4:
                    $num = "Dean";
                break;
                case 5:
                    $num = "Admissions Officer";
                break;
                case 6:
                    $num = "Finance";
                break;
                case 7:
                    $num = "OSAS";
                break;
                case 8:
                    $num = "Library";
                break;
                case 9:
                    $num = "Discipline";
                break;
                case 10:
                    $num = "Clinic";
                break;
                case 11:
                    $num = "IT";
                break;
            }
        
            return $num;
    }
}