<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//require_once('src/facebook.php');

class Pdf extends CI_Controller {

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
        //$this->load->model("user_model");
        //$this->config->load('courses');
        $this->data["user"] = $this->session->all_userdata();
        $this->load->helper("cms_form");
        $this->load->helper('pdf');
        $this->load->helper('text');
        $this->config->load('courses');
        
        $this->data['campus'] = $this->config->item('campus');
        $this->data["user"] = $this->session->all_userdata();
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
        $this->data['img_dir'] = base_url()."assets/themes/".$theme."/images/";	
        $this->data['student_pics'] = base_url()."assets/photos/";
        
		
	}
    
    function save_registration_file($id, $dir, $sem = null)
    {
       
        $data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $data['selected_ay'] = $sem;
        else
            $data['selected_ay'] = $active_sem['intID'];
        
        $data['terms'] = $this->data['terms'];
        $data['term_type'] = $this->data['term_type'];
        $data['unit_fee'] = $this->data['unit_fee'];
        $data['misc_fee'] = $this->data['misc_fee'];
        $data['lab_fee'] = $this->data['lab_fee'];
        $data['id_fee']  = $this->data['id_fee'];
        $data['athletic'] = $this->data['athletic'];
        $data['srf'] = $this->data['srf'];
        $data['sfdf'] = $this->data['sfdf'];
        $data['csg'] = $this->data['csg'];
        $data['img_dir'] = $this->data['img_dir'];
        $data['student_pics'] = $this->data['student_pics'];

        $data['active_sem'] = $this->data_fetcher->get_sem_by_id($data['selected_ay']);
        $data['student'] = $this->data_fetcher->getStudent($id);
        $data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$data['selected_ay']);
        
        $data['academic_standing'] = $this->data_fetcher->getAcademicStanding($data['student']['intID'],$data['student']['intCurriculumID']);

        $data['transactions'] = $this->data_fetcher->getTransactions($data['registration']['intRegistrationID'],$data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $data['tuition'] = $this->data_fetcher->getTuition($id,$data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$data['registration']['enumScholarship']);
        
        $student['has_nstp'] = true;
            
            $records = $this->data_fetcher->checkClasslistStudentNSTP($data['student']['intID'],$sem);
            if(empty($records))
                $student['has_nstp'] = false;
            
            $s_array[] = $student;

        $registration = $data['registration'];
        $tuition = $data['tuition'];
        //$total = $data['total'];
        $units = $tuition['tuition']/$data['unit_fee'];

        if($student['has_nstp']) {
            $units -= 3;
            $nstp_units = 3;
            $nstp_fee = $data['unit_fee'] * 3;
            $tuition['tuition'] -= $nstp_fee;
            $data['tuition'] = $tuition;
            $data['nstp_fee'] = $nstp_fee; 
        }
        else {
                $nstp_units = 0;
                $nstp_fee = 0;
                $data['nstp_units'] = $nstp_units;
                $data['nstp_fee'] = $nstp_fee;
        }
        
        switch($data['student']['strProgramCode'])
        {
            case 'BSCS':
                $data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSIT':
                $data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSBA-MM':
                $data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSBA-HRDM':
                $data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSOA':
                $data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSE-E':
                $data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-F':
                $data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-M':
                $data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-SS':
                $data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSHM':
                $data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            case 'BSTM':
                $data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            default:
                $data['deanSignature'] = 'signat-SCS-Dean2.png';
        }
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $data['records'][] = $record;
        }
        
        
        
       $data['dirname'] = $dir;

        //for total units
        $data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("save_pdf_reg2",$data);
    
    }

    
    function zipAndDownload($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0)
    {
        set_time_limit(0);
        
        
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem == 0)            
            $sem = $active_sem['intID'];

        
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem);
                
        
        $fname = date('ymdhis');
                
        $dirName = FCPATH.'assets/temp/'.$fname;          
        mkdir($dirName);
        
        $filename = FCPATH.'assets/temp/'.$fname.".zip";
        
        foreach($students as $student){
            $this->save_registration_file($student['intID'],$dirName,$sem);
        }
        
        $zip = new ZipArchive();        
        
        if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
            exit("cannot open <$filename>\n");
        }
                
        if (is_dir($dirName)){
            if ($dh = opendir($dirName)){
               while (($file = readdir($dh)) !== false){               
                 // If file
                 if (is_file($dirName.'/'.$file)) {
                     
                    if($file != '' && $file != '.' && $file != '..'){               
                       $zip->addFile($dirName.'/'.$file,$file);                        
                       
                    }
                 }           

               }
               closedir($dh);
             }
        }        
        $zip->close();
        
        $this->delete_directory($dirName);
        $this->downloadZip($filename);
        echo "done";
    }
    
    function delete_directory($dirname) {
        if (is_dir($dirname))
               $dir_handle = opendir($dirname);
         if (!$dir_handle)
              return false;
         while($file = readdir($dir_handle)) {
               if ($file != "." && $file != "..") {
                    if (!is_dir($dirname."/".$file))
                         unlink($dirname."/".$file);
                    else
                         delete_directory($dirname.'/'.$file);
               }
         }
         closedir($dir_handle);
         rmdir($dirname);
         return true;
    }
    
    function downloadZip($filename){
        
      if (file_exists($filename)) {
         header('Content-Type: application/zip');
         header('Content-Disposition: attachment; filename="'.basename($filename).'"');
         header('Content-Length: ' . filesize($filename));

         flush();
         readfile($filename);
         // delete file
         unlink($filename);

       }
        
    }
  
    
    function student_viewer_advising_blank_print()
    {
       $this->load->view("print_advising_blank",$this->data);
    }

    function student_viewer_advising_print($id,$sem= null)
    {
        $active_sem = $this->data_fetcher->get_processing_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];
        
        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        $this->data['prev_sem'] = $this->data_fetcher->get_prev_sem($active_sem['intID'],$id);
            

        if(!empty($this->data['prev_sem']))
        {

            $this->data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($this->data['student']['intID'],$this->data['prev_sem']['intID']);

            while(empty($this->data['prev_records']))
            {
                if(empty($this->data['prev_sem']))
                    break;

                $this->data['prev_sem'] = $this->data_fetcher->get_prev_sem($this->data['prev_sem']['intID'],$id);
                $this->data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($this->data['student']['intID'],$this->data['prev_sem']['intID']);
            }

        }
        else
            $this->data['prev_records'] = null;
        
        $subjects = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);
        $ret = array();
        foreach($subjects as $subj)
        {
            $classlists = $this->data_fetcher->fetch_classlist_by_subject($subj['subjectID'],$active_sem['intID']);
            $subj['classlists'] = $classlists;
            $ret[] = $subj;
        }
        $this->data['advised'] = $ret;
        
       $this->load->view("print_advising",$this->data);
        
        
    }
    function student_viewer_advising_print_data($id,$sem= null)
    {
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];
        
        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
         $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        $this->data['prev_sem'] = $this->data_fetcher->get_prev_sem();
            

        if(!empty($this->data['prev_sem']))
        {

            $this->data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($this->data['student']['intID'],$this->data['prev_sem']['intID']);

            while(empty($this->data['prev_records']))
            {
                if(empty($this->data['prev_sem']))
                    break;

                $this->data['prev_sem'] = $this->data_fetcher->get_prev_sem($this->data['prev_sem']['intID']);
                $this->data['prev_records'] = $this->data_fetcher->getClassListStudentsSt($this->data['student']['intID'],$this->data['prev_sem']['intID']);
            }

        }
        else
            $this->data['prev_records'] = null;
        
        $subjects = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);
        
        foreach($subjects as $subj)
        {
            $classlists = $this->data_fetcher->fetch_classlist_by_subject($subj['subjectID'],$active_sem['intID']);
            $subj['classlists'] = $classlists;
            $ret[] = $subj;
        }
        $this->data['advised'] = $ret;
        
       $this->load->view("print_advising_data",$this->data);
        
        
    }
    
    function student_viewer_rog_data_print($id, $sem = null)
    {
       
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
        
        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay']);
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("print_view_student_data_rog",$this->data);
    
    }

    function enrollment_summary($sem){
        
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
        $data['programs'] = $programs;
        $ret = [];        

        foreach($programs as $program){
            $st = [];
            $program['enrolled_transferee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,2));
            $program['enrolled_freshman'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,1));
            $program['enrolled_foreign'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,3));
            $program['enrolled_second'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,4));
             
            $ret[] = $program; 
        }

        $this->data['enrollment'] = $ret;
        $this->data['sem'] = $this->data_fetcher->get_sem_by_id($sem);

        $this->load->view("enrollment_summary",$this->data);

    }

    function grading_sheet($id){
        
        $sem = $this->data_fetcher->get_active_sem();
        $this->data['classlist'] = $this->data_fetcher->getClasslistById($id);
        $this->data['faculty'] = $this->db->get_where('tb_mas_faculty',array('intID'=>$id))->first_row('array');
        $this->data['user'] =  $this->session->all_userdata();
        $this->data['sem'] = $sem;
        $this->data['students'] = $this->data_fetcher->getClassListStudents($id);

        tcpdf();        
        // create new PDF document
        $pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Faculty Load Form");
        

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, .25, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);        
        //$pdf->SetAutoPageBreak(TRUE, 6);

    //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
                
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);         
         
        $html = $this->load->view("grading_sheet",$this->data,true); 
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');            
        $pdf->Output("grading_sheet.pdf", 'I');
    }

    function faculty_load_form($id){
        $sem = $this->data_fetcher->get_active_sem();
        $this->data['classlists'] = $this->data_fetcher->getClasslistsByFaculty($sem['intID'],$id);
        $this->data['faculty'] = $this->db->get_where('tb_mas_faculty',array('intID'=>$id))->first_row('array');
        $this->data['user'] =  $this->session->all_userdata();
        $this->data['sem'] = $sem;
        
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Faculty Load Form");
        

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, .25, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);        
        //$pdf->SetAutoPageBreak(TRUE, 6);

    //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
                
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);         
         
        $html = $this->load->view("faculty_load_form",$this->data,true); 
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');            
        $pdf->Output("faculty_load_form.pdf", 'I');
        
    }
    
    function daily_enrollment_report($sem){
        $post = $this->input->post();
        $this->data['dates'] = json_decode($post['dates']);
        $this->data['totals'] = json_decode($post['totals']);
        $this->data['full_total'] = json_decode($post['full_total']);
        
        $this->data['sem'] = $this->data_fetcher->get_sem_by_id($sem);
        $html = $this->load->view("daily_enrollment",$this->data);        
    }

    public function generate_tor(){
        $post = $this->input->post();
        print_r($post);
        $student = $this->data_fetcher->getStudent($post['student_id']);
        $num_terms = count($post['included_terms']);
        switch($student['level']){
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
        if($stype == "shs")
            $sem = $this->data_fetcher->get_active_sem_shs();
        else
            $sem = $this->data_fetcher->get_active_sem();

        $rec =
        array(
            'generated_by'=>$this->data['user']['strFirstname']." ".$this->data['user']['strLastname'],
            'term_id' => $sem['intID'],
            'date_generated' => $post['date_issued'],
            'prepared_by' => $post['prepared_by'],
            'verified_by' => $post['verified_by'],
            'registrar' => $post['registrar'],
            'included_terms' => implode(",", $post['included_terms']),
            'student_id' => $post['student_id']            
        );

        $this->db->insert('tb_mas_tor_generated',$rec);
        $units_overall = 0;
        $gwa_overall = 0;
        
        

        foreach($post['included_terms'] as $term){
            $records = $this->data_fetcher->getClassListStudentsSt($post['student_id'],$term);                
                    
            $sc_ret = [];
            $gwa = 0;
            $sum = 0;       
            $total = 0; 
            $total_units = 0;
            
            
            foreach($records as $record)
            {
                
                if($record['include_gwa'] && $record['v3'] && $record['intFinalized'] > 1 && ($record['strRemarks'] == "Passed" || $record['strRemarks'] == "Failed")){
                    $sum += (float)$record['v3'] * $record['strUnits'];
                    $total += $record['strUnits'];                
                }

                if($record['include_gwa'] && $record['strRemarks'] == "Passed" && $record['intFinalized'] > 1){
                    $total_units += $record['strUnits'];
                }

                $schedule = $this->data_fetcher->getScheduleByCodeNew($record['classlistID']);                                                  
                $record['schedule'] = $schedule;
                $sc_ret[] = $record;
            }                 
            if($total > 0)
                $gwa =  round(($sum/$total),2);

            $units_overall += $total_units;
            $gwa_overall += $gwa;
            $other_data = 
            array(
                'academic_standing' => null,
                'total_units' => $total_units,
                'gwa' => $gwa,
                'academic_standing' => null,

            );
            

            $this->data['records'][] = array('records'=>$sc_ret,'other_data'=>$other_data);                            
        }
        $this->data['gwa_overall'] = round(($gwa_overall/$num_terms),2);
        $this->data['units_overall'] = $units_overall;
        echo $this->data['gwa_overall']."<br />";
        echo $units_overall;
        print_r($this->data['records']);

        //$html = $this->load->view("tor",$this->data);
    }
    public function student_grade_slip($id,$sem,$period = "midterm"){
                        
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['student']['strStudentNumber'] = preg_replace("/[^a-zA-Z0-9]+/", "", $this->data['student']['strStudentNumber']);
        switch($this->data['student']['level']){
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
        
        if($sem != 0)
            $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($sem);
        elseif($stype == 'shs')
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem_shs();
        else
            $this->data['active_sem'] = $this->data_fetcher->get_active_sem();

            $this->data['selected_ay'] = $this->data['active_sem']['intID'];

        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);                
                
        $sc_ret = [];
        $gwa = 0;
        $sum = 0;       
        $total = 0; 
        $total_units = 0;
        $this->data['period'] = $period;
        if($period == "final"){
            $this->data['period_label'] = "Final Grade";
        }
        else{
            $this->data['period_label'] = "Midterm Grade";
        }
        
        foreach($records as $record)
        {
            
            if($record['include_gwa'] && $record['v3'] && $period == "final" && $record['intFinalized'] > 1 && ($record['strRemarks'] == "Passed" || $record['strRemarks'] == "Failed")){
                $sum += (float)$record['v3'] * $record['strUnits'];
                $total += $record['strUnits'];                
            }
            if($record['include_gwa'] && $record['v2'] && $period == "midterm" && $record['intFinalized'] >= 1 && ($record['strRemarks'] == "Passed" || $record['strRemarks'] == "Failed")){
                $sum += (float)$record['v2'] * $record['strUnits'];                
                $total += $record['strUnits'];
            }

            if($record['include_gwa'] && $record['strRemarks'] == "Passed" && $period == "final" && $record['intFinalized'] > 1){
                $total_units += $record['strUnits'];
            }

            $schedule = $this->data_fetcher->getScheduleByCodeNew($record['classlistID']);                                                  
            $record['schedule'] = $schedule;
            $sc_ret[] = $record;
        }                 
        if($total > 0)
            $gwa =  round(($sum/$total),2);


        $this->data['other_data'] = 
        array(
            'academic_standing' => null,
            'total_units' => $total_units,
            'gwa' => $gwa,
            'academic_standing' => null,

        );
        
        $this->data['records'] = $sc_ret;
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        $this->data['reg_status'] = $this->data_fetcher->getRegistrationStatus($id,$this->data['selected_ay']);                
        

        $html = $this->load->view("grade_slip",$this->data);

    }

    function reservation_summary($sem){
        
        $post = $this->input->post();
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
        $data['programs'] = $programs;        
        $ret = [];        

        $res = $post['reservation']?$post['reservation']:$ret;
        $res = json_decode($res);

        $totals = [];
        $r_fresh = [];
        $r_trans = [];
        $r_foreign = [];
        $r_sd = [];
        
        $all_reserved = 0;        

        $reserved = (array)$res->reserved;
            
        foreach($reserved as $res){   
            $i =  $res[0]->type_id;
            $r_fresh[$i] = false;
            $r_trans[$i] = false;
            $r_foreign[$i] = false;
            $r_sd[$i] = false;
            $totals[$res[0]->type_id] = 0;                                         
            for($j = 0; $j < count($res); $j++){     
                if($res[$j]->student_type == "freshman")
                    $r_fresh[$i] = true;
                if($res[$j]->student_type == "transferee")
                    $r_trans[$i] = true;
                if($res[$j]->student_type == "foreign")
                    $r_foreign[$i] = true;
                if($res[$j]->student_type == "second degree")
                    $r_sd[$i] = true;

                $totals[$res[$j]->type_id] += (int)$res[$j]->reserved_count;
                $all_reserved += (int)$res[$j]->reserved_count;
            }                           
        }
        $data = [
            'totals'=>$totals,
            'r_fresh'=>$r_fresh,
            'r_trans'=>$r_trans,
            'r_foreign'=>$r_foreign,
            'r_sd'=>$r_sd,            
            'all_reserved'=>$all_reserved,            
            'reserved'=>$reserved,
        ];

        $this->data['reserved'] = $data;
        $this->data['sem'] = $this->data_fetcher->get_sem_by_id($sem);

        $html = $this->load->view("reservation_summary",$this->data);
        
    }
    
    function student_viewer_registration_print($id, $app_id, $sem = null, $mt = 6)
    {
       
        $this->data['mt'] = $mt;
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null && $sem != 0)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        if($this->data['registration']['enumStudentType'] == "new"){
            $this->data['snum_label'] = "APP NUMBER";
            $this->data['snum'] = "A".$this->data['active_sem']['strYearStart']."-".str_pad($app_id,4,"0",STR_PAD_LEFT);
        }
        else{
            $this->data['snum_label'] = "STUD NUMBER";
            $this->data['snum'] = $this->data['student']['strStudentNumber'];
        }
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);

        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id, $this->data['selected_ay']);
        
        $student['has_nstp'] = true;
            
        $records = $this->data_fetcher->checkClasslistStudentNSTP($this->data['student']['intID'],$sem);
        if(empty($records))
            $student['has_nstp'] = false;
        
        $s_array[] = $student;

        $registration = $this->data['registration'];
        $tuition = $this->data['tuition'];
        //$total = $data['total'];       
                        

        switch($this->data['student']['strProgramCode'])
        {
            case 'BSCS':
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSIT':
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSBA-MM':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSBA-HRDM':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSOA':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSE-E':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-F':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-M':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-SS':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSHM':
                $this->data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            case 'BSTM':
                $this->data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            default:
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
        }
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);            
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("print_view_student_reg2",$this->data);
        //$this->load->view("save_pdf_reg2",$this->data);
    
    }

    function student_viewer_registration_print2($id, $sem = null)
    {
       
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);

        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['registration']['enumScholarship']);
       
        $student['has_nstp'] = true;
            
            $records = $this->data_fetcher->checkClasslistStudentNSTP($this->data['student']['intID'],$sem);
            if(empty($records))
                $student['has_nstp'] = false;
            
            $s_array[] = $student;

        $registration = $this->data['registration'];
        $tuition = $this->data['tuition'];
        //$total = $data['total'];
        $units = $tuition['tuition']/$this->data['unit_fee'];

        if($student['has_nstp']) {
            $units -= 3;
            $nstp_units = 3;
            $nstp_fee = $this->data['unit_fee'] * 3;
            $tuition['tuition'] -= $nstp_fee;
            $this->data['tuition'] = $tuition;
            $this->data['nstp_fee'] = $nstp_fee; 
        }
        else {
                $nstp_units = 0;
                $nstp_fee = 0;
                $this->data['nstp_units'] = $nstp_units;
                $this->data['nstp_fee'] = $nstp_fee;
        }

        switch($this->data['student']['strProgramCode'])
        {
            case 'BSCS':
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSIT':
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSBA-MM':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSBA-HRDM':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSOA':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSE-E':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-F':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-M':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-SS':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSHM':
                $this->data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            case 'BSTM':
                $this->data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            default:
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
        }
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("print_view_student_reg_comp",$this->data);
        //$this->load->view("save_pdf_reg2",$this->data);
    
    }

    function student_completion_form_print($csid)
    {
       
        $cs = $this->data_fetcher->getClasslistStudent($csid);
        $st = $this->data_fetcher->getCompletion($csid);
        $this->data['cs'] = $cs;
        $this->data['st'] = $st;
        $ave = getAve($cs['floatPrelimGrade'],$cs['floatMidtermGrade'],$st['floatNewFinalTermGrade']);
        $eq = getEquivalent($ave);
        $this->data['ave'] = $ave;
        $this->data['eq'] = $eq;

        switch($this->data['cs']['strProgramCode'])
        {
            case 'BSCS':
                $this->data['school'] = 'Computer Studies';
            break;
            case 'BSIT':
                $this->data['school'] = 'Computer Studies';
            break;
            case 'BSBA-MM':
                $this->data['school'] = 'Business & Management';
            break;
            case 'BSBA-HRDM':
                $this->data['school'] = 'Business & Management';
            break;
            case 'BSOA':
                $this->data['school'] = 'Business & Management';
            break;
            case 'BSE-E':
                $this->data['school'] = 'Education';
            break;
            case 'BSE-F':
                $this->data['school'] = 'Education';
            break;
            case 'BSE-M':
                $this->data['school'] = 'Education';
            break;
            case 'BSE-SS':
                $this->data['school'] = 'Education';
            break;
            case 'BSHM':
                $this->data['school'] = 'Hospitality & Tourism Management';
            break;
            case 'BSTM':
                $this->data['school'] = 'Hospitality & Tourism Management';
            break;
            default:
                $this->data['school'] = 'n/a';
        }
   
        $this->load->view("print_view_student_completion",$this->data);
    
    }
    
    
    function student_viewer_registration_data_print_legacy($id, $sem = null)
    {
       
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);

        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['registration']['enumScholarship']);
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("print_reg_legacy",$this->data);
    
    }
    
    function student_viewer_registration_data_print($id, $sem = null)
    {
       
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        $this->data['discounts'] = $this->db->get_where('tb_mas_registration_discount',array('registration_id'=>$this->data['registration']['intRegistrationID']))->result_array();
        
        $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);

        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['registration']['enumScholarship']);
        foreach($this->data['discounts'] as $discount){
            $this->data['tuition']['total'] -= $discount['discount'];
            $this->data['tuition']['total_installment'] -= $discount['discount'];
        }
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);
        
        switch($this->data['student']['strProgramCode'])
        {
            case 'BSCS':
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSIT':
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
            break;
            case 'BSBA-MM':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSBA-HRDM':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSOA':
                $this->data['deanSignature'] = 'signat-SBM-Dean2.png';
            break;
            case 'BSE-E':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-F':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-M':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSE-SS':
                $this->data['deanSignature'] = 'signat-SOE-Dean2.png';
            break;
            case 'BSHM':
                $this->data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            case 'BSTM':
                $this->data['deanSignature'] = 'signat-SHTM-Dean2.png';
            break;
            default:
                $this->data['deanSignature'] = 'signat-SCS-Dean2.png';
        }

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("print_view_student_reg_data",$this->data);
    
    }
     function student_viewer_registration_blank_print()
    {
        $this->load->view("print_view_student_reg_blank",$this->data);
    }

      function old_reg($id, $sem = null)
    {
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        
          $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);

        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['registration']['enumScholarship']);
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        $this->load->view("print_view_student_reg_data",$this->data);
    }

    function get_curriculum_for_printing($slug){

        $data['student'] = $this->data_fetcher->getItem('tb_mas_users',$slug,'slug');
        $data['curriculum'] = $this->data_fetcher->getItem('tb_mas_curriculum',$data['student']['intCurriculumID']);
        $grades = $this->data_fetcher->assessCurriculum($data['student']['intID'],$data['student']['intCurriculumID']);
        array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
        $data['grades'] = $grades;
        $data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculumMain($data['student']['intCurriculumID']);
        $data['equivalent_subjects'] = $this->data_fetcher->getSubjectsInCurriculumEqu($data['student']['intCurriculumID']);

        echo json_encode($data);

    }

    public function ched_enrollment_list($course = 0, $year=0,$gender = 0,$sem=0)
    {                
        
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
                
        $this->data['sy'] = $active_sem;
        

        //print_r($this->data['spouse']);
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Ched Enrollment");
        

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, .25, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);        
        //$pdf->SetAutoPageBreak(TRUE, 6);

    //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);         
        if($course == 0)
            $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
        else
            $programs = $this->data_fetcher->fetch_table('tb_mas_programs',null,null,array('intProgramID'=>$course));
        


        foreach($programs as $program){
            $st = [];
            $students = $this->data_fetcher->getStudents($program['intProgramID'],0,$year,$gender,0,0,2,$sem);
            if(!empty($students)){
                
                $this->data['year_level_total'] = count($students);
                $male = 0;
                $female = 0;
                $this->data['last_page'] = false;
                foreach($students as $student)
                {
                    $cl = $this->data_fetcher->getClassListStudentsSt($student['intID'],$sem);                                            
                    $student['classes'] = $cl;                
                    $st[] = $student;
                    if($student['enumGender'] == "male")
                        $male++;
                    else
                        $female++;
                }

                $this->data['male'] = $male;
                $this->data['female'] = $female;

                $per_page = array_chunk($st, 2);
                $this->data['count_start'] = 1;
                $chunks_count = 1;
                foreach($per_page as $chunk){
                    $this->data['students'] = $chunk;                
                    $pdf->AddPage();
                    if(count($per_page) == $chunks_count)
                        $this->data['last_page'] = true;
                    $html = $this->load->view("ched_enrollment_list",$this->data,true);
                    $pdf->writeHTML($html, true, false, true, false, '');            
                    $this->data['count_start'] += 2;                    
                    $chunks_count++;
                }
            }
        }
         
         
          
         $pdf->Output("ched_enrollment.pdf", 'I');

    

    }
    
    function print_curriculum($id,$studentId)
    {
        $grades = $this->data_fetcher->assessCurriculum($studentId,$id);
        array_unshift($grades,array('strCode'=>'none','floatFinalGrade'=>'n/a','strRemarks'=>'n/a'));
        $this->data['grades'] = $grades;
        $this->data['curriculum'] = $this->data_fetcher->getItem('tb_mas_curriculum',$id);
        $this->data['student'] = $this->data_fetcher->getItem('tb_mas_users',$studentId);
        //$this->data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculum($id);
        $this->data['curriculum_subjects'] = $this->data_fetcher->getSubjectsInCurriculumMain($this->data['student']['intCurriculumID']);
        $this->data['equivalent_subjects'] = $this->data_fetcher->getSubjectsInCurriculumEqu($this->data['student']['intCurriculumID']);
            
         //print_r($this->data['spouse']);
        tcpdf();
        // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Curriculum");
       
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //$pdf->SetAutoPageBreak(TRUE, 6);

       //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // Add a page
        
        $pdf->AddPage();
        $html = $this->load->view('curriculum',$this->data,true);
        //$html = $pdf->unhtmlentities($html);

        $pdf->writeHTML($html, true, false, true, false, '');
            
            
       
        $pdf->Output("curriculum.pdf", 'I');
    }
    
    function student_viewer_rog_print($id, $sem = null)
    {
       
        $this->data['sy'] = $this->data_fetcher->fetch_table('tb_mas_sy');
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        $this->data['student'] = $this->data_fetcher->getStudent($id);
        $this->data['registration'] = $this->data_fetcher->getRegistrationInfo($id,$this->data['selected_ay']);
        
         $this->data['academic_standing'] = $this->data_fetcher->getAcademicStanding($this->data['student']['intID'],$this->data['student']['intCurriculumID']);
        
        $this->data['transactions'] = $this->data_fetcher->getTransactions($this->data['registration']['intRegistrationID'],$this->data['selected_ay']);
        //--------TUITION-------------------------------------------------------------------
        $this->data['tuition'] = $this->data_fetcher->getTuition($id,$this->data['selected_ay'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$this->data['registration']['enumScholarship']);
        
        $records = $this->data_fetcher->getClassListStudentsSt($id,$this->data['selected_ay']);

       
        foreach($records as $record)
        {
            $record['schedule'] = $this->data_fetcher->getScheduleByCode($record['classlistID']);
            //print_r($record['schedule']);
            $this->data['records'][] = $record;
        }

        //for total units
        $this->data['total_units'] = $this->data_fetcher->getTotalUnits($id);
        
        $this->load->view("print_view_student_rog",$this->data);
    
    }
    
    public function print_sched($sem = null)
    {
        $post  = $this->input->post();
        
        $active_sem = $this->data_fetcher->get_active_sem();
        //$this->data['active_sem'] = $this->data_fetcher->get_active_sem();
        if($sem!=null)
            $this->data['selected_ay'] = $sem;
        else
            $this->data['selected_ay'] = $active_sem['intID'];

        $this->data['active_sem'] = $this->data_fetcher->get_sem_by_id($this->data['selected_ay']);
        //$this->data['faculty'] = $this->data_fetcher->getFaculty($id);
        
        $this->data['sched'] = $post['sched-table'];
        $this->data['facultyName'] = $post['facultyName'];
        $this->data['facultyDept'] = $post['facultyDept'];
        
        $this->load->view("print_sched",$this->data);
    }

    public function print_enlisted_students($course,$year,$gender,$sem,$start,$end){

        //print_r($this->data['spouse']);
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Enlisted");
        
        if($sem == 0)      
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        $this->data['sy'] = $active_sem;
        $students = $this->data_fetcher->getStudentsEnlistedOnly(0,$active_sem['intID'],$course,$year,$gender,$start,$end);                                
                               
       
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(3, .25, 3);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //$pdf->SetAutoPageBreak(TRUE, 6);

       //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(false, PDF_MARGIN_FOOTER);
        $pdf->SetFont('pdfahelvetica','',10);
        
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // Add a page
        // This method has several options, check the source code documentation for more information.
        
        
        $pdf->AddPage();
        $ret = 0;
        $per_page = 40;       
        $st = [];
        $this->data['nothing_follows'] = true;
        if(count($students) > $per_page)
        {            
            $stdn = array_slice($students, 0, $per_page);
            foreach($stdn as $student){
                $student['reg_info'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$active_sem['intID']);
                $st[] = $student;
            }
            $ret = count($students) - $per_page;
            $this->data['students'] = $st;
            $this->data['nothing_follows'] = false;
        }
        else
        {
            foreach($students as $student){
                $student['reg_info'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$active_sem['intID']);
                $st[] = $student;
            }
            $this->data['students'] = $st;
            
        }

        $this->data['snum'] = 1;
        $html = $this->load->view('enlisted_students',$this->data,true);



        //$html = $pdf->unhtmlentities($html);

        $pdf->writeHTML($html, true, false, true, false, '');
        while($ret > 0)
        {
            
            $pdf->AddPage();
            $this->data['snum'] = $this->data['snum'] + $per_page;
            $start = $this->data['snum'] - 1;                       

            if($ret > $per_page)
                $count = $per_page;            
            else{
                $count = $ret;
            }

            $stdn = array_slice($students, $start , $count);   
            $st = [];          
            foreach($stdn as $student){
                $student['reg_info'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$active_sem['intID']);
                $st[] = $student;
            }
            $this->data['students'] = $st;
            
            $html = $this->load->view('enlisted_students',$this->data,true);
            $pdf->writeHTML($html, true, false, true, false, '');

            if($ret > $per_page){                
                $ret = $ret - $per_page;                            
                $this->data['nothing_follows'] = false;
            }
            else{
                $ret = 0;
                $this->data['nothing_follows'] = true;
            }
            
        }
            
        
        $pdf->Output("enlisted.pdf", 'I');

    }
    
    public function print_classlist_registrar($id,$page="front")
    {
        
        //print_r($this->data['spouse']);
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Classlist");
        
        $this->data['classlist'] = $this->data_fetcher->fetch_classlist_by_id(null,$id);
        $this->data['sy'] = $this->data_fetcher->get_sem_by_id($this->data['classlist']['strAcademicYear']);
        $all_students = $this->data_fetcher->getClassListStudents($id);
        $students = [];
        
        foreach($all_students as $std){
            $registered = $this->data_fetcher->checkRegistered($std['intID'],$this->data['classlist']['strAcademicYear']);
            if(!empty($registered))
                $students[] = $std;
        }

        $this->data['subject'] = $this->data_fetcher->getSubjectNoCurr($this->data['classlist']['intSubjectID']);
        $schedule = $this->data_fetcher->fetch_table('tb_mas_room_schedule',null,null,array('strScheduleCode'=>$id));
        $this->data['faculty'] =  current($this->data_fetcher->fetch_table('tb_mas_faculty',null,null,array('intID'=>$this->data['classlist']['intFacultyID'])));        
        $days = "";
        $added_days = array();
        $times = "";
        
        $schedule = $this->data_fetcher->getScheduleByCode($id);        
        $sched_text = '';

        
        
        if(isset($schedule[0]['strDay']))                                                
            $sched_text.= date('g:ia',strtotime($schedule[0]['dteStart'])).' - '.date('g:ia',strtotime($schedule[0]['dteEnd']));  
    
        $sched_text.= ' ';                                                            
        foreach($schedule as $sched) {
            if(isset($sched['strDay']))
                $sched_text.= $sched['strDayAbvr'];                    
                //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
        }
        
        $sched_text.= ' ';                                            
        if(isset($schedule[0]['strDay']))
            $sched_text.= $schedule[0]['strRoomCode'];

        $this->data['schedule'] = $sched_text;

        $this->data['program'] = current($this->data_fetcher->fetch_table('tb_mas_programs',null,null,array('intProgramID'=>$this->data['subject']['intProgramID'])));
       
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, .25, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //$pdf->SetAutoPageBreak(TRUE, 6);

       //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(false, PDF_MARGIN_FOOTER);
        
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // Add a page
        // This method has several options, check the source code documentation for more information.
        
        if($page == "front"){
            $pdf->AddPage();
            $ret = 0;
            $this->data['nothing_follows'] = true;
            if(count($students) > 40)
            {
                $ret = count($students) -40;
                $this->data['students'] = array_slice($students, 0, 40);
                $this->data['nothing_follows'] = false;
            }
            else
            {
                foreach($students as $student){
                    
                    $student['reg_info'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$this->data['classlist']['strAcademicYear']);
                    $st[] = $student;
                
                }
                $this->data['students'] = $st;
            }

            $this->data['snum'] = 1;
            $html = $this->load->view('classlist_view',$this->data,true);



            //$html = $pdf->unhtmlentities($html);

            $pdf->writeHTML($html, true, false, true, false, '');
            if($ret > 0)
            {
                $this->data['nothing_follows'] = true;
                $pdf->AddPage();
                $this->data['students'] = array_slice($students, -$ret);
                $this->data['snum'] = 41;
                $html = $this->load->view('classlist_view',$this->data,true);
                $pdf->writeHTML($html, true, false, true, false, '');
            }
            
        }
        else
        {
            $passing =0;
            $incomplete =0;
            $ud = 0;
            $od = 0;
            $failing =0;
            $lineOfOne = 0;
            $lifeOfTwo = 0;
            $lineOfThree = 0;
            $totalUD = 0;
            $totalFailed = 0;
            $st = array();
            
            foreach($students as $student)
            { 
                $student['registered'] = $this->data_fetcher->checkRegistered($student['intID'],$this->data['classlist']['strAcademicYear']);                
                $st[] = $student;
                $ave = getAve($student['floatPrelimGrade'],$student['floatMidtermGrade'],$student['floatFinalsGrade']);
                $eq = getEquivalent($ave);
                //$eq = getEquivalent($student['floatFinalGrade']);
                if($eq>=5 && $student['enumStatus'] == "drp")
                    $failing++;
                else if ($student['enumStatus'] == "drp")
                    $ud++;
                else if ($student['enumStatus'] == "odrp")
                    $od++;
                else if ($student['enumStatus'] == "inc")
                    $incomplete++;
                else
                    $passing++;
                    
                if($eq >=1.00 && $eq <= 1.75 && $student['enumStatus'] != "inc" && $student['enumStatus'] != "drp" && $student['enumStatus'] != "odrp")
                    $lineOfOne++;
                else if($eq >= 2.00 && $eq <= 2.75 && $student['enumStatus'] != "inc" && $student['enumStatus'] != "drp" && $student['enumStatus'] != "odrp")
                    $lifeOfTwo++;
                else if($eq == 3.00 && $student['enumStatus'] != "inc" && $student['enumStatus'] != "drp" && $student['enumStatus'] != "odrp")
                    $lineOfThree++;
                else if ($student['enumStatus'] == "act" && $student['strRemarks'] == "Failed")
                    $totalFailed++;
                else if ($student['enumStatus'] == "drp" && $student['strRemarks'] == "Failed(U.D.)")
                    $totalUD++;
                
                
            }
            $this->data['students'] = $st;
            $this->data['passing'] = $passing;
            $this->data['ud'] = $ud;
            $this->data['od'] = $od;
            $this->data['incomplete'] = $incomplete;
            $this->data['failing'] = $failing;
            $this->data['lineOfOne'] = $lineOfOne;
            $this->data['lineOfTwo'] = $lifeOfTwo;
            $this->data['lineOfThree'] = $lineOfThree;
            $this->data['totalFailed'] = $totalFailed;
            $this->data['totalUD'] = $totalUD;
            $this->data['total'] = $incomplete + $lineOfOne + $lifeOfTwo + $lineOfThree + $totalFailed + $totalUD + $od;
            
            $pdf->AddPage();
            $html = $this->load->view('classlist_view_back',$this->data,true);
            $pdf->writeHTML($html, true, false, true, false, '');

        }
        $pdf->Output("classlist.pdf", 'I');
    
    }

    public function print_classlist_grades($id)
    {
        
        //print_r($this->data['spouse']);
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       // create new PDF document
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array('A4'), true, 'UTF-8', false, true);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Classlist");
        
        $this->data['classlist'] = $this->data_fetcher->fetch_classlist_by_id(null,$id);
        $this->data['sy'] = $this->data_fetcher->get_sem_by_id($this->data['classlist']['strAcademicYear']);
        $all_students = $this->data_fetcher->getClassListStudents($id);
        $students = [];
        
        foreach($all_students as $std){
            $registered = $this->data_fetcher->checkRegistered($std['intID'],$this->data['classlist']['strAcademicYear']);
            if(!empty($registered))
                $students[] = $std;
        }

        $this->data['subject'] = $this->data_fetcher->getSubjectNoCurr($this->data['classlist']['intSubjectID']);
        $schedule = $this->data_fetcher->fetch_table('tb_mas_room_schedule',null,null,array('strScheduleCode'=>$id));
        $this->data['faculty'] =  current($this->data_fetcher->fetch_table('tb_mas_faculty',null,null,array('intID'=>$this->data['classlist']['intFacultyID'])));        
        $days = "";
        $added_days = array();
        $times = "";
        
        $schedule = $this->data_fetcher->getScheduleByCode($id);        
        $sched_text = '';

        
        
        if(isset($schedule[0]['strDay']))                                                
            $sched_text.= date('g:ia',strtotime($schedule[0]['dteStart'])).' - '.date('g:ia',strtotime($schedule[0]['dteEnd']));  
    
        $sched_text.= ' ';                                                            
        foreach($schedule as $sched) {
            if(isset($sched['strDay']))
                $sched_text.= $sched['strDayAbvr'];                    
                //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
        }
        
        $sched_text.= ' ';                                            
        if(isset($schedule[0]['strDay']))
            $sched_text.= $schedule[0]['strRoomCode'];

        $this->data['schedule'] = $sched_text;

        $this->data['program'] = current($this->data_fetcher->fetch_table('tb_mas_programs',null,null,array('intProgramID'=>$this->data['subject']['intProgramID'])));
       
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, .25, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //$pdf->SetAutoPageBreak(TRUE, 6);

       //font setting
        //$pdf->SetFont('calibril_0', '', 15, '', 'false');
        // set default font subsetting mode
        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        
        $pdf->SetAutoPageBreak(false, PDF_MARGIN_FOOTER);
        
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // Add a page
        // This method has several options, check the source code documentation for more information.
        
        
            $pdf->AddPage();
            $ret = 0;
            $this->data['nothing_follows'] = true;
            if(count($students) > 40)
            {
                $ret = count($students) -40;
                $this->data['students'] = array_slice($students, 0, 40);
                $this->data['nothing_follows'] = false;
            }
            else
            {
                foreach($students as $student){
                    
                    $student['reg_info'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$this->data['classlist']['strAcademicYear']);
                    $st[] = $student;
                
                }
                $this->data['students'] = $st;
            }

            $this->data['snum'] = 1;
            $html = $this->load->view('classlist_view_grades',$this->data,true);



            //$html = $pdf->unhtmlentities($html);

            $pdf->writeHTML($html, true, false, true, false, '');
            if($ret > 0)
            {
                $this->data['nothing_follows'] = true;
                $pdf->AddPage();
                $this->data['students'] = array_slice($students, -$ret);
                $this->data['snum'] = 41;
                $html = $this->load->view('classlist_view_grades',$this->data,true);
                $pdf->writeHTML($html, true, false, true, false, '');
            }
            
        
        
            $this->data['students'] = $st;                                    

        
        $pdf->Output("classlist.pdf", 'I');
    
    }

    function registration_viewer_account_data_print($orNumber,$studID)
    {
        if($this->is_admin() || $this->is_accounting()){
            
            $this->data['transactions'] = $this->data_fetcher->getTransactionsOR($orNumber);
            $transactions = $this->data['transactions'];
            $this->data['student'] = $this->data_fetcher->getStudent($studID);
            $student = $this->data['student'];
            //print_r($this->data['spouse']);
            tcpdf();
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(4.5,8.5), true, 'UTF-8', false, true);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($transactions[0]['intORNumber'] . "-" . $student['strLastname'].', '.$student['strFirstname'].' '.$student['strMiddlename']);
    
    
        // set margins
        $pdf->SetMargins(.3, .1, .25);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //$pdf->SetAutoPageBreak(TRUE, 6);

       //font setting
        //$pdf->SetFont('calibril_0', '', 10, '', 'false');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
            

// Add a page
            // This method has several options, check the source code documentation for more information.

            $pdf->AddPage();

            $html = $this->load->view("print_or",$this->data,true);
            //$html = $pdf->unhtmlentities($html);

            $pdf->writeHTML($html, true, false, true, false, '');


            $pdf->Output("request-form.pdf", 'I');
        }
    }

    function print_or()
    {
        $request = $this->input->post();

        $printed = $this->db->where(array('or_number'=>(string)$request['or_number'],'campus'=>$this->data['campus']))
                        ->get('tb_mas_printed_or')
                        ->first_row();

        if($printed){
            echo "This OR has already been printed";
            return;
        }
        
                
        tcpdf();
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(260.5,180.5), true, 'UTF-8', false, true);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("OR");
    
    
        // set margins
        $pdf->SetMargins(8, 10, 25);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //$pdf->SetAutoPageBreak(TRUE, 6);

       //font setting
        //$pdf->SetFont('calibril_0', '', 10, '', 'false');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
            

        // Add a page
        // This method has several options, check the source code documentation for more information.

        $cashier = $this->db->get_where('tb_mas_faculty',array('intID'=>$request['cashier_id']))->row();
        $this->data['term'] = $this->db->get_where('tb_mas_sy',array('intID'=>$request['sem']))->first_row('array');
        
        if(isset($request['payee_id']))
            $payee = $this->db->get_where('tb_mas_ns_payee',array('id'=>$request['payee_id']))->first_row('array');
        else
            $payee = null;

        $type = "";
        if(isset($request['type'])){
            switch($request['type']){
                case 'college':
                    $type = "UG ".$request['description'];
                    break;
                case 'shs':
                    $type = "SHS ".$request['description'];
                    break;
                default:
                    $type = "SHS ".$request['description'];                    
            }
        }
                
        $pdf->AddPage();        
        $this->data['student_name'] = strtoupper($request['student_name']);        
        $this->data['cashier_name'] = strtoupper($cashier->strFirstname." ".$cashier->strLastname);        
        $this->data['student_id'] = $request['student_id'];        
        $this->data['student_address'] = strtoupper($request['student_address']);
        $this->data['is_cash'] = $request['is_cash'];        
        $this->data['check_number'] = $request['check_number'];        
        $this->data['remarks'] = $request['remarks'];
        $this->data['or_number'] = (string)$request['or_number'];
        $this->data['or_number'] = str_pad($this->data['or_number'],5,'0', STR_PAD_LEFT);
        $this->data['description'] = $request['description'];
        $this->data['total_amount_due'] = $request['total_amount_due'];
        $this->data['decimal'] = ($this->data['total_amount_due'] - floor( $this->data['total_amount_due'] )) * 100;
        $this->data['decimal'] = round($this->data['decimal']);        
        $this->data['transaction_date'] =  $request['transaction_date'];          
        $this->data['tin'] = $payee?$payee['tin']:'';
        $this->data['type'] = $type;
        $pdf->SetTextColor(0,0,0);




        if(isset($payee))
            $html = $this->load->view("print_or_ns_payment",$this->data,true);
        elseif($this->data['campus'] == "Cebu")
            $html = $this->load->view("print_or",$this->data,true);
        else            
            $html = $this->load->view("print_or_makati",$this->data,true);
        
        //$html = $pdf->unhtmlentities($html);

        $this->data_poster->insert_or_print(
            array(
                "or_number"=>(string)$request['or_number'],
                "date_printed"=>date("Y-m-d"),
                "campus"=>$this->data['campus'],
                "printed_by"=>$this->session->userdata('strFirstname')." ".$this->session->userdata('strLastname'),
            )
        );

        $pdf->writeHTML($html, true, false, true, false, '');


        $pdf->Output("official-receipt.pdf", 'I');
        
    }
    
    
    
    function student_viewer_blank_rog_print($id, $sem = null)
    {
        $this->load->view("print_view_student_blank_rog",$this->data);
    }
   
    function portal_login_data($id)
    {
        if($this->is_admin()){
            $student = $this->data_fetcher->getStudent($id);
            $this->data['student'] = $student;

            //print_r($this->data['spouse']);
            tcpdf();
            // create new PDF document
            //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
           // create new PDF document
            $pdf = new TCPDF("P", PDF_UNIT, array(8.5, 11), true, 'UTF-8', false);
            //$pdf = new TCPDF("P", PDF_UNIT, array(8.5, 13), false, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetTitle("Student Letter");
            $this->data['request_form'] = "";

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            //$pdf->SetAutoPageBreak(TRUE, 6);

           //font setting
            //$pdf->SetFont('calibril_0', '', 15, '', 'false');
            // set default font subsetting mode
            // Set font
            // dejavusans is a UTF-8 Unicode font, if you only need to
            // print standard ASCII chars, you can use core fonts like
            // helvetica or times to reduce file size.

            $pdf->SetAutoPageBreak(false, PDF_MARGIN_FOOTER);


            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // Add a page
            // This method has several options, check the source code documentation for more information.

            $pdf->AddPage();

            $html = $this->load->view("student_letter",$this->data,true);
            //$html = $pdf->unhtmlentities($html);

            $pdf->writeHTML($html, true, false, true, false, '');


            $pdf->Output("request-form.pdf", 'I');
        }
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

    public function is_accounting()
    {
        $admin = $this->session->userdata('intUserLevel');
        if($admin == 6)
            return true;
        else
            return false;
        
    }

}