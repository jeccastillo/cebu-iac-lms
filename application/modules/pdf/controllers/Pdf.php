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

    function enrollment_summary($sem)
    {
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        $type = $active_sem['term_student_type'];
        $programs = $this->db->get_where('tb_mas_programs',array('type'=>$type))->result_array();
        $data['programs'] = $programs;
        $ret = [];        

        foreach($programs as $program){
            $st = [];
            $program['enrolled_transferee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,2));
            $program['enrolled_freshman'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,1));
            $program['enrolled_foreign'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,3));
            $program['enrolled_second'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,4));
            $program['enrolled_continuing'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,5));
            $program['enrolled_shiftee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,6));
            $program['enrolled_returnee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,7));
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

    function faculty_load_form($id,$sem){
        
        $sem = $this->data_fetcher->get_sem_by_id($sem);

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
            'student_id' => $post['student_id'],
            'remarks' => $post['remarks'],         
            'signatory_label' => $post['signatory_label'], 
            'type' => $post['type'],
        );

        $this->db->insert('tb_mas_tor_generated',$rec);

        $units_overall = 0;
        $gwa_overall = 0;
        $total_records = 0;
        $rec['admission_date'] = date("M j, Y",strtotime($post['admission_date']));
        $rec['picture'] = $post['picture'];
        $credited_subjects = [];

        $terms_in_credited = $this->db->where(array('student_id'=>$post['student_id']))
                                      ->order_by('school_year asc, term asc')
                                      ->group_by(array('school_year','term','completion'))
                                      ->get('tb_mas_credited')
                                      ->result_array();
                    
        foreach($terms_in_credited as $term_credited){

            $credited = $this->db->where(array('student_id'=>$post['student_id'],'term'=>$term_credited['term'],'school_year'=>$term_credited['school_year'],'completion'=>$term_credited['completion']))
                                ->order_by('course_code','asc')                                
                                ->get('tb_mas_credited')
                                ->result_array();
            
            $credited_data = array(
                'term' => $term_credited['term'],
                'school' => $term_credited['completion'],
                'school_year' => $term_credited['school_year'],
            );     

            $credited_subjects[] = array('records'=>$credited,'other_data'=>$credited_data);

        }   
        
        $this->data['credited_subjects'] = $credited_subjects;
        

        foreach($post['included_terms'] as $term){
            $records = $this->data_fetcher->getClassListStudentsSt($post['student_id'],$term);                
            $sem = $this->data_fetcher->get_sem_by_id($term);                    
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
                
                $sc_ret[] = $record;
                
                if($record['intFinalized'] > 1)
                    $total_records++;
            }                 
            if($total > 0)
                $gwa =  number_format(round(($sum/$total),3),3);

            $units_overall += $total_units;
            $gwa_overall += $gwa;
            $other_data = 
            array(
                'term' => $sem,
                'total_units' => $total_units,
                'gwa' => $gwa,
                

            );
            

            $this->data['records'][] = array('records'=>$sc_ret,'other_data'=>$other_data);                            
        }
        
        $rec['total_records'] = $total_records;

        $this->data['other_details'] = $rec;
        $this->data['gwa_overall'] = number_format(round(($gwa_overall/$num_terms),3),3);
        $this->data['units_overall'] = $units_overall;        
        $this->data['student'] = $student;        

        if($post['type'] == 'tor')
            $html = $this->load->view("tor",$this->data);
        else
            $html = $this->load->view("tcg",$this->data);
    }

    public function reprint_tor($id){
        $data_post = $this->input->get();
        if($data_post['picture'] == "undefined")
            unset($data_post['picture']);
        $post = $this->db->get_where('tb_mas_tor_generated',array('id'=>$id))->first_row('array');
        $post['included_terms'] = explode(",",$post['included_terms']);
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
            'date_generated' => $post['date_generated'],
            'prepared_by' => $post['prepared_by'],
            'verified_by' => $post['verified_by'],
            'registrar' => $post['registrar'],
            'included_terms' => implode(",", $post['included_terms']),
            'student_id' => $post['student_id'],
            'remarks' => $post['remarks'],         
            'signatory_label'=> $post['signatory_label'],
            'type' => $post['type'],
        );
        $units_overall = 0;
        $gwa_overall = 0;
        $total_records = 0;        
        $rec['picture'] = $data_post['picture'];
        $rec['admission_date'] = $data_post['admission_date'];
        $credited_subjects = [];

        $terms_in_credited = $this->db->where(array('student_id'=>$post['student_id']))
                                      ->order_by('school_year asc, term asc')
                                      ->group_by(array('school_year','term','completion'))
                                      ->get('tb_mas_credited')
                                      ->result_array();
                    
        foreach($terms_in_credited as $term_credited){

            $credited = $this->db->where(array('student_id'=>$post['student_id'],'term'=>$term_credited['term'],'school_year'=>$term_credited['school_year'],'completion'=>$term_credited['completion']))
                                ->order_by('course_code','asc')                                
                                ->get('tb_mas_credited')
                                ->result_array();
            
            $credited_data = array(
                'term' => $term_credited['term'],
                'school' => $term_credited['completion'],
                'school_year' => $term_credited['school_year'],
            );     

            $credited_subjects[] = array('records'=>$credited,'other_data'=>$credited_data);

        }   
        
        $this->data['credited_subjects'] = $credited_subjects;
        

        foreach($post['included_terms'] as $term){
            $records = $this->data_fetcher->getClassListStudentsSt($post['student_id'],$term);                
            $sem = $this->data_fetcher->get_sem_by_id($term);                    
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
                
                $sc_ret[] = $record;
                
                if($record['intFinalized'] > 1)
                    $total_records++;
            }                 
            if($total > 0)
                $gwa =  number_format(round(($sum/$total),3),3);

            $units_overall += $total_units;
            $gwa_overall += $gwa;
            $other_data = 
            array(
                'term' => $sem,
                'total_units' => $total_units,
                'gwa' => $gwa,
                

            );
            

            $this->data['records'][] = array('records'=>$sc_ret,'other_data'=>$other_data);                            
        }
        
        $rec['total_records'] = $total_records;

        $this->data['other_details'] = $rec;
        $this->data['gwa_overall'] = number_format(round(($gwa_overall/$num_terms),3),3);
        $this->data['units_overall'] = $units_overall;        
        $this->data['student'] = $student;        

        if($post['type'] == 'tor')
            $html = $this->load->view("tor",$this->data);
        else
            $html = $this->load->view("tcg",$this->data);
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
        //pass fail do not include in gwa
        foreach($records as $record)
        {
            
            if($record['include_gwa'] && $record['v3'] && $period == "final" && $record['intFinalized'] > 1 && ($record['strRemarks'] == "Passed" || $record['strRemarks'] == "Failed")){
                $sum += (float)$record['v3'] * $record['strUnits'];
                $total += $record['strUnits'];                
            }
            if($record['include_gwa'] && $record['v2'] && $period == "midterm" && $record['intFinalized'] >= 1 && $record['v2'] != 50 && isset($record['v2'])){
                $sum += (float)$record['v2'] * $record['strUnits'];                
                $total += $record['strUnits'];
            }

            if($record['strRemarks'] == "Passed" && $period == "final" && $record['intFinalized'] > 1){
                $total_units += $record['strUnits'];
            }

            $schedule = $this->data_fetcher->getScheduleByCodeNew($record['classlistID']);                                                  
            $record['schedule'] = $schedule;
            $sc_ret[] = $record;
        }                 
        if($total > 0)
            $gwa =  number_format(round(($sum/$total),3),3);


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
        
        if($this->data['campus'] == "Cebu")
            $this->load->view("print_view_student_reg2",$this->data);
        else
            $this->load->view("print_view_student_reg2_makati",$this->data);
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

                $page_c = ($this->data['campus'] == 'Cebu')?"ched_enrollment_list":"ched_enrollment_list_makati";
                $per_page = array_chunk($st, 2);
                $this->data['count_start'] = 1;
                $chunks_count = 1;
                foreach($per_page as $chunk){
                    $this->data['students'] = $chunk;                
                    $pdf->AddPage();
                    if(count($per_page) == $chunks_count)
                        $this->data['last_page'] = true;
                    $html = $this->load->view($page_c,$this->data,true);
                    $pdf->writeHTML($html, true, false, true, false, '');            
                    $this->data['count_start'] += 2;                    
                    $chunks_count++;
                }
            }
        }
         
         
          
         $pdf->Output("ched_enrollment.pdf", 'I');

    

    }

    function adjustments($id,$sem){
        
        $this->data['adjustments'] = $this->db
                                    ->select('tb_mas_classlist_student_adjustment_log.*, strCode, strFirstname, strLastname')
                                    ->from('tb_mas_classlist_student_adjustment_log')  
                                    ->join('tb_mas_subjects', 'tb_mas_classlist_student_adjustment_log.classlist_student_id = tb_mas_subjects.intID')                                     
                                    ->join('tb_mas_faculty', 'tb_mas_classlist_student_adjustment_log.adjusted_by = tb_mas_faculty.intID')                                     
                                    ->where(array('student_id'=>$id,'syid'=>$sem))
                                    ->order_by('tb_mas_classlist_student_adjustment_log.date','asc')
                                    ->get()
                                    ->result_array();

        $this->data['student'] = $this->data_fetcher->getStudent($id);   
        
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
        $html = $this->load->view('adjustments',$this->data,true);
        //$html = $pdf->unhtmlentities($html);

        $pdf->writeHTML($html, true, false, true, false, '');
            
            
        
        $pdf->Output("adjustments.pdf", 'I');

    }

    function print_curriculum_subjects($id){        

        $this->data['item'] = $this->data_fetcher->getItem('tb_mas_curriculum',$id);   
        $this->data['curriculum_subjects'] =  [];     
        $curriculum = $this->data_fetcher->getSubjectsInCurriculum($id);

        foreach($curriculum as $subject){
            $prereq_array = 
                    $this->db->select('tb_mas_subjects.*,tb_mas_prerequisites.program')
                     ->from('tb_mas_prerequisites')
                     ->join('tb_mas_subjects', 'tb_mas_prerequisites.intPrerequisiteID = tb_mas_subjects.intID')
                     ->where('intSubjectID',$subject['intSubjectID'])
                     ->get()
                     ->result_array();
            $subject['prereq'] = [];    
            foreach($prereq_array as $prereq){
                    if(isset($prereq['program']) && ($prereq['program'] == 0  || $prereq['program'] == $this->data['item']['intID'] || $prereq['program'] == NULL))
                        $subject['prereq'][] =  $prereq;     
            }       

            $this->data['curriculum_subjects'][] = $subject;
        }
        
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
        $html = $this->load->view('print_curriculum_subjects',$this->data,true);
        //$html = $pdf->unhtmlentities($html);

        $pdf->writeHTML($html, true, false, true, false, '');
            
            
       
        $pdf->Output("curriculum.pdf", 'I');
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

    function print_invoice()
    {
        $request = $this->input->post();

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');

        if($userlevel != 2 && $userlevel != 6)
		  redirect(base_url()."unity");

        $cashier = $this->db->get_where('tb_mas_faculty',array('intID'=>$request['cashier_id']))->row();
        //$student = $this->db->get_where('tb_mas_users',array('slug'=> 'c9316f71-8991-4c93-a8d8-fd20f776aea1'))->first_row('array');
        $student = $this->db->get_where('tb_mas_users',array('slug'=>$request['slug']))->first_row('array');
        $term = $this->db->get_where('tb_mas_sy',array('intID'=>$request['sem']))->first_row('array');
        print_r($request);
        
        $reg = $this->db->get_where('tb_mas_registration',array('intStudentID'=>$student['intID'],'intAYID'=>$request['sem'], 'date_enlisted !=' => NULL))->first_row('array');
        
        // $request['slug']
        $reservationDescription = $reservationAmount = $fullAssessment = $totalAssessment = '';
        
        $reservationPayment = $this->db->get_where('payment_details',array('student_number'=> $request['slug'],'description' => 'Reservation Payment', 'payment_details.sy_reference' => $request['sem'], 'payment_details.status' => 'Paid'))->first_row('array');
        
        if($reservationPayment){
            $reservationAmount = $reservationPayment['subtotal_order'];
            if($reservationPayment['invoice_number'])
            $reservationDescription = 'Inv ' . $reservationPayment['invoice_number'] . ' - ';
            
            $reservationDescription .= 'Reservation Fee ';
        }else{
            $reservationAmount = 0;
        }

        $tuition = $this->data_fetcher->getTuition($student['intID'], $request['sem']);
        if($tuition && $request['description'] == "Tuition Fee"){
            if($reg['paymentType'] == 'partial'){
                $fullAssessment = $tuition['total_installment'];
                $totalAssessment = $tuition['total_installment'] - $reservationAmount;
            }else{
                $fullAssessment = $tuition['total'];
                $totalAssessment = $tuition['total'] - $reservationAmount;
            }
        }else{
            $fullAssessment = $request['total_amount_due'];
            $totalAssessment = $request['total_amount_due'];
        }
         
        $description = $request['description'] == "Tuition Fee" || $request['description'] == "Reservation Payment"  ? "Total Assessment " . $term['enumSem']." ".$term['term_label'] . " AY ".$term['strYearStart']."-".$term['strYearEnd']." ": $request['description'];

        $this->data['term'] = $term;
        $this->data['student_name'] = strtoupper($request['student_name']);        
        $this->data['cashier_name'] = strtoupper($cashier->strFirstname." ".$cashier->strLastname);        
        $this->data['student_id'] = $request['student_id'];        
        $this->data['student_address'] = strtoupper($request['student_address']);
        $this->data['is_cash'] = $request['is_cash'];        
        $this->data['check_number'] = $request['check_number'];        
        $this->data['remarks'] = $request['remarks'];
        $this->data['invoice_number'] = (string)$request['invoice_number'];
        $this->data['invoice_number'] = str_pad($this->data['invoice_number'],5,'0', STR_PAD_LEFT);
        $this->data['description'] = $description;
        // $this->data['total_amount_due'] = number_format($request['total_amount_due'],2,'.',',');
        $this->data['total_amount_due'] = number_format($request['total_amount_due'],2,'.',',');
        $this->data['decimal'] = ($this->data['total_amount_due'] - floor( $this->data['total_amount_due'] )) * 100;
        $this->data['decimal'] = round($this->data['decimal']);        
        $this->data['transaction_date'] =  date("m/d/Y",strtotime($request['transaction_date']));  
        $this->data['request'] = $request;
        $this->data['reservation_description'] = $reservationDescription;
        $this->data['reservation_amount'] = number_format($reservationAmount,2,'.',',');
        $this->data['payment_type'] = $reg['paymentType'];
        $this->data['full_assessment'] = number_format($fullAssessment,2,'.',',');
        $this->data['total_assessment'] = number_format($totalAssessment,2,'.',',');

        $this->load->view("print_invoice",$this->data);
    }

    function print_updated_or(){
        $request = $this->input->post();

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');

        if($userlevel != 2 && $userlevel != 6)
		  redirect(base_url()."unity");

        $printed = $this->db->where(array('or_number'=>(string)$request['or_number'],'campus'=>$this->data['campus']))
                        ->get('tb_mas_printed_or')
                        ->first_row();

        if($printed && $role <= 1){
            echo "This OR has already been printed";
            return;
        }

        $cashier = $this->db->get_where('tb_mas_faculty',array('intID'=>$request['cashier_id']))->row();
        $this->data['term'] = $this->db->get_where('tb_mas_sy',array('intID'=>$request['sem']))->first_row('array');
        $term = $this->data['term'];
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
                case 'other':
                        $type = "UG ".$request['description'];
                        break;                    
                case 'shs':
                    $type = "SHS ".$request['description'];
                    break;
                default:
                    $type = "SHS ".$request['description'];                    
            }
        }

        $decimal = ($request['total_amount_due'] - floor( $request['total_amount_due'] )) * 100;
        $decimal = round($decimal);
        $totalAmountDueText = convert_number($request['total_amount_due'])." ";
        $totalAmountDueText .= $decimal ? 'and '.convert_number($decimal).' cents':'only';

        $description = $request['description'] == "Reservation Payment" ? "NON REFUNDABLE AND NON TRANSFERABLE":"";
        $sem = "SY ".$term['strYearStart']."-".$term['strYearEnd']." ".$term['enumSem']." ".$term['term_label'];

        $this->data['student_name'] = strtoupper($request['student_name']);   
        $this->data['cashier_name'] = strtoupper($cashier->strFirstname." ".$cashier->strLastname);        
        $this->data['student_id'] = $request['student_id'];        
        $this->data['student_address'] = strtoupper($request['student_address']);
        $this->data['is_cash'] = $request['is_cash'];        
        $this->data['check_number'] = $request['check_number'];        
        $this->data['remarks'] = $request['remarks'];
        $this->data['or_number'] = (string)$request['or_number'];
        $this->data['or_number'] = str_pad($this->data['or_number'],5,'0', STR_PAD_LEFT);
        $this->data['invoice_number'] = strtoupper($request['invoice_number']);
        $this->data['invoice_number'] = str_pad($this->data['or_number'],5,'0', STR_PAD_LEFT);
        // $this->data['description'] = $request['description'];
        // $this->data['total_amount_due'] = $request['total_amount_due'];
        // $this->data['decimal'] = ($this->data['total_amount_due'] - floor( $this->data['total_amount_due'] )) * 100;
        // $this->data['decimal'] = round($this->data['decimal']);
        $this->data['transaction_date'] =  $request['transaction_date'];          
        $this->data['tin'] = $payee?$payee['tin']:'';
        $this->data['type'] = $type == "Tuition Fee" ? "Total Assessment" : $type;
        $this->data['sem'] = $sem;
        $this->data['decimal'] = $decimal;
        $this->data['description'] = $description;
        $this->data['total_amount_due_text'] = $totalAmountDueText;
        $this->data['total_amount_due'] = number_format($request['total_amount_due'],2,'.',',');
                          
        $this->load->view("print_or_latest",$this->data);

    }

    function print_or_new(){
        $request = $this->input->post();

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');

        if($userlevel != 2 && $userlevel != 6)
		  redirect(base_url()."unity");

        $printed = $this->db->where(array('or_number'=>(string)$request['or_number'],'campus'=>$this->data['campus']))
                        ->get('tb_mas_printed_or')
                        ->first_row();

        if($printed && $role <= 1){
            echo "This OR has already been printed";
            return;
        }

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
                case 'other':
                        $type = "UG ".$request['description'];
                        break;                    
                case 'shs':
                    $type = "SHS ".$request['description'];
                    break;
                default:
                    $type = "SHS ".$request['description'];                    
            }
        }

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

        if(isset($payee))
            $this->load->view("print_or_ns_payment",$this->data);
        elseif($this->data['campus'] == "Cebu")
            $this->load->view("print_or_new",$this->data);
        else            
            $this->load->view("print_or_new_makati",$this->data);

    }


    function print_or()
    {
        $request = $this->input->post();

        $role = $this->session->userdata('special_role');
        $userlevel = $this->session->userdata('intUserLevel');

        if($userlevel != 2 && $userlevel != 6)
		  redirect(base_url()."unity");

        $printed = $this->db->where(array('or_number'=>(string)$request['or_number'],'campus'=>$this->data['campus']))
                        ->get('tb_mas_printed_or')
                        ->first_row();

        if($printed && $role <= 1){
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
        $pdf->SetMargins(8, 10, 10);
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
                case 'other':
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
    
    function print_or_test(){
        $this->load->view('test_print_or');
    }
    function print_invoice_test(){
        $this->load->view('test_print_invoice');
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

    public function ched_report($sem = 0, $campus)
    {                
        $students_array = array();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();

        $students = $this->db->select('tb_mas_users.intID, tb_mas_users.intProgramID, tb_mas_users.strStudentNumber, tb_mas_users.strLastname, tb_mas_users.strFirstname, tb_mas_users.strMiddlename, tb_mas_users.enumGender, tb_mas_users.intStudentYear')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        foreach($students as $student){
            $student_data = array();
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);  
            $subjects = $this->db->select('tb_mas_subjects.strCode, tb_mas_subjects.strDescription, tb_mas_subjects.strUnits, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
            ->get()
            ->result_array();

            if($subjects){
                $student['course'] = $course['strProgramCode'];
                $student['subjects'] = $subjects;
                $student['student'] = $student;
                $students_array[] = $student;
            }
        }

        $this->data['students'] = $students_array;
        $this->data['sy'] = $sy;
        $this->data['campus'] = $campus;
                
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Ched Promotional Report");
        

        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        
        $html = $this->load->view("ched_report",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("ched_promotional_report.pdf", 'I');
    }
    
    public function ched_enrollment_report($sem = 0, $campus)
    {
        $students_array = array();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        foreach($students as $index => $student){
            $student_data = array();
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);
            $subjects = $this->db->select('tb_mas_subjects.strCode, tb_mas_subjects.strDescription, tb_mas_subjects.strUnits, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
            ->get()
            ->result_array();


            $totalUnits = 0;
            $subjectsEnrolled = '';

            foreach($subjects as $subjectIndex => $subject){
                $totalUnits += $subject['strUnits'];
                $subjectsEnrolled .= $subject['strDescription'] . ' (' . $subject['strUnits'] . ')';
                if($subjectIndex < count($subjects) - 1){
                    $subjectsEnrolled .= ', ';
                }
            }
            
            $suffixList = ['Jr.', 'Jr', 'Sr.', 'Sr', 'II', 'III', 'IV'];
            $student['nameExtension'] = '';
            $lastName = $student['strLastname'];

            foreach($suffixList as $suffix){
                // check if last name contains a suffix 
                if(strpos($student['strLastname'], $suffix) !== false){
                    $student['nameExtension'] = $suffix;
                    $student['strLastname'] = str_replace($suffix, '', $student['strLastname']);
                    break;
                }
            }

            if($subjects){
                $student['totalUnits'] = $totalUnits;
                $student['subjectsEnrolled'] = $subjectsEnrolled;
                $student['course'] = $course;
                $student['index'] = $index + 1;
                $students_array[] = $student;
            }
        }

        $this->data['students'] = $students_array;
        $this->data['sy'] = $sy;
        $this->data['campus'] = $campus;
                
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Ched Enrollment Report");
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("ched_enrollment_report",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("CHED_Enrollment_Report_" . $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function ched_tes_report($sem = 0, $campus)
    {
        $students_array = array();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        foreach($students as $index => $student){
            $suffixList = ['Jr.', 'Jr', 'Sr.', 'Sr', 'II', 'III', 'IV'];
            $nameExtension = '';
            $lastName = $student['strLastname'];

            foreach($suffixList as $suffix){
                // check if last name contains a suffix 
                if(strpos($student['strLastname'], $suffix) !== false){
                    $nameExtension = $suffix;
                    $lastName = str_replace($suffix, '', $student['strLastname']);
                    $lastName = trim($lastName, ' ');
                    break;
                }
            }
            
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);  
            $fatherLastName = $fatherFirstName = $fatherMiddleName = $motherLastName = $motherFirstName = $motherMiddleName = '';

            //format name to capital then compare to student name to get first and last name of parents
            $student['mother'] = ucwords($student['mother']);
            $student['father'] = ucwords($student['father']);
            $lastName = ucwords($lastName);
            $student['strMiddlename'] = ucwords(trim($student['strMiddlename'], ' '));

            if($student['father']){
                if($student['father'] != 'n/a' && $student['father'] != 'no info'){

                    if(strpos($student['father'], $lastName) !== false){
                        $fatherLastName = $lastName;
                        $fatherFirstName = str_replace($fatherLastName, '', $student['father']);
                        $father = explode(" ", trim($fatherFirstName));

                        if(count($father) > 1){
                            $checkFatherMiddle = $father[count($father) - 1];
                            if($checkFatherMiddle[1] == '.'){
                                $fatherMiddleName = $checkFatherMiddle;
                                $fatherFirstName = str_replace($checkFatherMiddle, '', $fatherFirstName);
                            }
                        }
                    }
                }
            }

            if($student['mother']){
                if($student['mother'] != 'n/a' && $student['mother'] != 'no info'){
                    //check if mother used maiden name
                    if($student['strMiddlename']){
                        if(strpos($student['mother'], $student['strMiddlename']) !== false){
                            $motherLastName = $student['strMiddlename'];
                            $motherFirstName = str_replace($motherLastName, '', $student['mother']);
                            $mother = explode(" ", trim($motherFirstName));

                            if(count($mother) > 1){
                                $checkMotherMiddle = $mother[count($mother) - 1];
                                if($checkMotherMiddle[1] == '.'){
                                    $motherMiddleName = $checkMotherMiddle;
                                    $motherFirstName = str_replace($checkMotherMiddle, '', $motherFirstName);
                                }
                            }
                        }
                    }
                }
            }
            
            $address = explode(",", $student['strAddress']);
            $students[$index]['address'] = $address;
            $students[$index]['fatherLastName'] = $fatherLastName;
            $students[$index]['fatherFirstName'] = $fatherFirstName;
            $students[$index]['fatherMiddleName'] = $fatherMiddleName;
            $students[$index]['motherLastName'] = $motherLastName;
            $students[$index]['motherFirstName'] = $motherFirstName;
            $students[$index]['motherMiddleName'] = $motherMiddleName;
            $students[$index]['course'] = $course;
            $students[$index]['strLastname'] = $lastName;
            $students[$index]['nameExtension'] = $nameExtension;
            $students[$index]['index'] = $index + 1;
        }

        $this->data['students'] = $students;
        $this->data['sy'] = $sy;
        $this->data['campus'] = $campus;
                
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Ched TES Report");
        
        // set margins
        $pdf->SetMargins(0.15, .20, 0.05);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("ched_tes_report",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("CHED_TES_Report_" . $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    
    public function ched_nstp_report($sem = 0, $campus)
    {
        $studentsArray = array();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        
        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        foreach($students as $index => $student){
            $nstpEnrolled = false;
            $subjects = $this->db->select('tb_mas_subjects.isNSTP')
                ->from('tb_mas_classlist_student')
                ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
                ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
                ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
                ->get()
                ->result_array();
            
            foreach($subjects as $subject){
                if($subject['isNSTP'] == 1){
                    $nstpEnrolled = true;
                    break;
                }
            }

            if($nstpEnrolled){
                $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);  
                $address = explode(",", $student['strAddress']);
                $city = $province = '';
    
                if(count($address) > 1){
                    if(!is_numeric($address[1])){
                        $city = $address[1];
                    }
                    if(count($address) > 3){
                        if(is_numeric($address[count($address) - 1]) && !is_numeric($address[count($address) - 2])){
                            $province = $address[count($address) - 2];
                        }
                    }else if(count($address) > 2){
                        if(!is_numeric($address[2])){
                            $province = $address[2];
                        }
                    }
                }
                
                $address = explode(",", $student['strAddress']);
                $student['address'] = $address;
                $student['city'] = $city;
                $student['province'] = $province;
                $student['course'] = $course;
                $student['index'] = $index + 1;
                $studentsArray[] = $student;
            }
        }
        $this->data['students'] = $studentsArray;
        $this->data['sy'] = $sy;
        $this->data['campus'] = $campus;
                
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Ched NSTP Report");
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("ched_nstp_report",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("CHED_NSTP_Report_" . $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function deans_list($term,$period){
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $term))->first_row();
        $data['list_1st_honor'] = [];
        $data['list_2nd_honor'] = [];
        $data['gwa'] = [];
        $pr = ($period == 0)?"v2":"v3";
        $period = ($period == 0) ? 'Midterm' : "Final";
        $students = $this->data_fetcher->getStudents(0,0,0,0,0,0,2,$term,0);
        $data['students'] =  $students;
        foreach($students as $student){
            $records = $this->data_fetcher->getClassListStudentsSt($student['intID'],$term); 
            $units = 0;
            $sum_grades = 0;
            $units_earned = 0;
            $total = 0;
            foreach($records as $record){
                if($record['intFinalized'] == 2 && $record['strRemarks'] == "Passed" && $record['include_gwa'])
                    $units_earned += $record['strUnits'];
                if($record['intFinalized'] == 2 && $record['include_gwa'] && $record['strRemarks'] != "Officially Withdrawn"){
                    switch($record[$pr]){
                        case 'FA':
                            $v3 = 5;
                        break;
                        case 'UD':
                            $v3 = 5;
                        break;
                        default:
                            $v3 = $record['v3'];
                    }                    
                    $sum_grades += floatval($v3) * $record['strUnits'];
                    $total += $record['strUnits'];
                }
            }

            $term_gwa = 0;
            if($total > 0){
                $term_gwa = $sum_grades/$total;
                $term_gwa = number_format(round($term_gwa,3),3);
            }
            if($term_gwa != 0 && $term_gwa <= 1.5 && $term_gwa > 1.25){
                $student['gwa'] = $term_gwa;
                $data['list_2nd_honor'][] = $student;                    
            }
            if($term_gwa != 0 && $term_gwa <= 1.25){
                $student['gwa'] = $term_gwa;
                $data['list_1st_honor'][] = $student;                    
            }
        }

        //sort by GWA
        usort($data['list_1st_honor'], function($a, $b) {
            return $a['gwa'] > $b['gwa'];
        });

        usort($data['list_2nd_honor'], function($a, $b) {
            return $a['gwa'] > $b['gwa'];
        });

        $this->data['list_1st_honor'] = $data['list_1st_honor'];
        $this->data['list_2nd_honor'] = $data['list_2nd_honor'];
        $this->data['sy'] = $sy;

        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Dean's_Listers_" . $period . '_' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd);
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("deans_list",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("Dean's_Listers_" . $period . '_' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function enhanced_list($sem)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $sy['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_registration.current_curriculum')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_curriculum.isEnhanced' => '1'))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        for($index = 0; $index < count($students); $index++){
            $course = $this->data_fetcher->getProgramDetails($students[$index]['intProgramID']);
            $students[$index]['course'] = $course;
        }

        $this->data['students'] = $students;
        $this->data['sy'] = $sy;
                
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Enhanced List");
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("enhanced_list",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("List_of_Enhanced_" . $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function regular_list($sem)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $sy['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_curriculum','tb_mas_curriculum.intID = tb_mas_registration.current_curriculum')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_curriculum.isEnhanced' => '0'))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        for($index = 0; $index < count($students); $index++){
            $course = $this->data_fetcher->getProgramDetails($students[$index]['intProgramID']);
            $students[$index]['course'] = $course;
        }

        $this->data['students'] = $students;
        $this->data['sy'] = $sy;
                
        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("SHS List Grade");
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("regular_list",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output("List_of_Regular_" . $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }
    
    public function shs_by_grade_level($sem = 0, $year_level)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $sy['intID'];
        }
        $students = $this->db->select('tb_mas_users.*, tb_mas_registration.intYearLevel, tb_mas_programs.strProgramCode, tb_mas_classlist.strSection')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                    ->join('tb_mas_classlist_student','tb_mas_users.intID = tb_mas_classlist_student.intStudentID')
                    ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_programs.type'=>'shs', 'tb_mas_registration.intYearLevel'=>$year_level))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->group_by('tb_mas_users.intID')
                    ->get()
                    ->result_array();
        
        $this->data['students'] = $students;
        $this->data['year_level'] = $year_level;
        $this->data['sy'] = $sy;

        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("SHS List Grade " . $year_level);
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("shs_by_grade_level",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output('SHS List Grade  ' . $year_level . ' ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function student_track_and_course($sem = 0, $year_level = 0, $campus)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }

        $gradeLevel = 'All Year Level';
        $students = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        if($year_level != 0){
            $gradeLevel = 'Grade/Year Level:' . $year_level;
            $students = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode')
                        ->from('tb_mas_users')
                        ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                        ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                        ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_registration.intYearLevel'=>$year_level))
                        ->order_by('tb_mas_users.strLastname', 'ASC')
                        ->get()
                        ->result_array();
        }
        
        $this->data['students'] = $students;
        $this->data['year_level'] = $gradeLevel;
        $this->data['sy'] = $sy;

        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Student List with Track and College Course " . $gradeLevel);
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("student_track_and_course",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output('Student List with Track and College Course - ' . $gradeLevel . ' ' . $year_level . ' ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function shs_gwa_rank($sem = 0, $year_level = 0)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }

        $gradeLevel = 'All Grade Level';
        $students = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode, tb_mas_registration.intYearLevel')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_programs.type'=>'shs'))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        if($year_level != 0){
            $gradeLevel = 'Grade ' . $year_level;
            $students = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode, tb_mas_registration.intYearLevel')
                        ->from('tb_mas_users')
                        ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                        ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                        ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_programs.type'=>'shs', 'tb_mas_registration.intYearLevel'=>$year_level))
                        ->order_by('tb_mas_users.strLastname', 'ASC')
                        ->get()
                        ->result_array();
        }
        
        $gwa_ranks = array();
        foreach($students as $student){
            $totalGrades = 0;
            $subjects = $this->db->select('tb_mas_classlist_student.floatPrelimGrade, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalsGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem,'tb_mas_classlist_student.floatPrelimGrade !='=>null, 'tb_mas_classlist_student.floatMidtermGrade !='=>null, 'tb_mas_classlist_student.floatFinalsGrade !='=>null))
            ->get()
            ->result_array();

            foreach($subjects as $subject){
                $average = getAve($subject['floatPrelimGrade'], $subject['floatMidtermGrade'], $subject['floatFinalsGrade']);
                $totalGrades += $average;
            }

            if(count($subjects) > 0){
                $gwa = $totalGrades / count($subjects);
                
                $student_data = array();
                $student_data['student_number'] = $student['strStudentNumber'];
                $student_data['last_name'] = strtoupper($student['strLastname']);
                $student_data['first_name'] = strtoupper($student['strFirstname']);
                $student_data['middle_name'] = strtoupper($student['strMiddlename']);
                $student_data['track'] = $student['strProgramCode'];
                $student_data['gwa'] = $gwa;
                $student_data['year_level'] = $student['intYearLevel'];
                $gwa_ranks[] = $student_data;
            }
        }

        //sort by GWA
        usort($gwa_ranks, function($a, $b) {
            return $a['gwa'] < $b['gwa'];
        });

        $this->data['students'] = $gwa_ranks;
        $this->data['year_level'] = $gradeLevel;
        $this->data['sy'] = $sy;

        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("SHS GWA Rank " . $gradeLevel);
        
        // set margins
        $pdf->SetMargins(0.5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("shs_gwa_rank",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output('SHS GWA Rank - ' . $gradeLevel . ' ' . $year_level . ' ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');
    }

    public function print_soa()
    {
        $request = $this->input->post();
        $sem = $request['sem'];
        $user_id = $request['user_id'];

        $installments = explode(',', $request['installments']);

        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }

        $user = $this->db->select('intID, strFirstName, strMiddleName, strLastName, slug, intProgramID, strStudentNumber')
            ->from('tb_mas_users')
            ->where(array('intID'=>$user_id))
            ->get()
            ->first_row('array');

        $reg = $this->db->select('tb_mas_registration.*, tb_mas_scholarships.name as scholarshipName')
            ->from('tb_mas_registration')
            ->where(array('intStudentID'=>$user_id,'intAYID'=>$sem, 'date_enlisted !=' => NULL))
            ->join('tb_mas_scholarships', 'tb_mas_scholarships.intID = tb_mas_registration.enumScholarship', 'left')
            ->get()
            ->first_row('array');

        $tuition = $this->data_fetcher->getTuition($user_id, $sem);
        $applied_from = $applied_to = $other = $payments = array();
        $assessment_discount_rate = $assessment_discount_fixed = $tuition_discount_rate = 0;
        
        // $payment_details = $this->db->select('payment_details.*')
        //             ->from('payment_details')
        //             ->join('tb_mas_users', 'tb_mas_users.slug = payment_details.student_number')
        //             ->join('tb_mas_registration', 'tb_mas_registration.intStudentID = tb_mas_users.intID')
        //             ->where(array('payment_details.sy_reference' => $sem, 'payment_details.student_number' => $user['slug'], 'payment_details.status' => 'Paid'))
        //             ->order_by('payment_details.created_at', 'asc')
        //             ->group_by('payment_details.id')
        //             ->get()
        //             ->result_array();

        // $current_index = 0;
        // $payment_month = $payment_year = '';

        // if($payment_details){
        //     $payment = $user_payment = $date = $student_payment = array();
        //     foreach($payment_details as $payment_index => $payment_detail){
        //         if(strpos($payment_detail['description'], 'Tuition') !== false || strpos($payment_detail['description'], 'Reservation') !== false){
        //             //set date enrolled based on full or installment payment
        //             if(!isset($date_enrolled_array[$payment_detail['student_number']]) && strpos($payment_detail['description'], 'Tuition') !== false){
        //                 $date_enrolled_array[$payment_detail['student_number']] = $payment_detail['created_at'];
        //             }
        //             if($payments == null){
        //                 $payment['date'] = date("M d", strtotime($payment_detail['created_at']));
        //                 $payment['or_number'] = $payment_detail['or_number'];
        //                 $payment['amount'] = (float)number_format($payment_detail['subtotal_order'], 2, '.', '');
                        
        //                 $payment_month = date("m", strtotime($payment_detail['created_at']));
        //                 $payment_year = date("Y", strtotime($payment_detail['created_at']));
                        
        //                 $user_payment[$user['intID']] = $payment;

        //                 $date['month'] = $payment_month;
        //                 $date['month_name'] = date("F", strtotime($payment_detail['created_at']));
        //                 $date['year'] = $payment_year;
        //                 $date['data'] = $user_payment;

        //                 $payments[] = $date;
        //             }else{
        //                 if(isset($date['data'][$user['intID']]) && $payment_month == date("m", strtotime($payment_detail['created_at'])) && $payment_year == date("Y", strtotime($payment_detail['created_at']))){
        //                     $payments[$current_index]['data'][$user['intID']]['date'] .= ', ' . date("d", strtotime($payment_detail['created_at']));
        //                     $payments[$current_index]['data'][$user['intID']]['or_number'] .= ', ' . $payment_detail['or_number'];
        //                     $payments[$current_index]['data'][$user['intID']]['amount'] += (float)number_format($payment_detail['subtotal_order'], 2, '.', '');
        //                 }else{
        //                     $flag = $same_month_year = false;
        //                     $data = $date = array();
        //                     for($index = count($payments) - 1; $index >= 0; $index--){
        //                         if($payments[$index]['year'] == date("Y", strtotime($payment_detail['created_at'])) && $payments[$index]['month'] == date("m", strtotime($payment_detail['created_at']))){
                                    
                                
        //                             $same_month_year = true;
        //                             $current_index = $index;
        //                         }else if($payments[$index]['year'] == date("Y", strtotime($payment_detail['created_at']))){
        //                             if($payments[$index]['month'] > date("m", strtotime($payment_detail['created_at']))){
        //                                 $current_index = $index;
        //                                 $flag = true;
        //                             }
        //                         }else if($payments[$index]['year'] > date("Y", strtotime($payment_detail['created_at']))){
        //                             $current_index = $index;
        //                             $flag = true;
        //                         }
        //                     }

        //                     $payment['date'] = date("M d", strtotime($payment_detail['created_at']));
        //                     $payment['or_number'] = $payment_detail['or_number'];
        //                     $payment['amount'] = (float)number_format($payment_detail['subtotal_order'], 2, '.', '');
                            
        //                     $payment_month = date("m", strtotime($payment_detail['created_at']));
        //                     $payment_year = date("Y", strtotime($payment_detail['created_at']));
        //                     $user_payment[$user['intID']] = $payment;
    
        //                     $date['month'] = $payment_month;
        //                     $date['month_name'] = date("F", strtotime($payment_detail['created_at']));
        //                     $date['year'] = $payment_year;
        //                     $date['data'] = $user_payment;
        //                     $data[] = $date;
                            
        //                     if($same_month_year){
        //                         $payments[$current_index]['data'][$user['intID']] = $payment;
        //                     }else{
        //                         if($flag){
        //                             array_splice($payments, $current_index, 0, $data);
        //                         }
        //                         else{
        //                             $current_index = count($payments);
        //                             array_splice($payments, count($payments), 0, $data);
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

        // $ledger_data = $this->db->get_where('tb_mas_student_ledger', array('syid' => $sem, 'student_id' => $user_id))->result_array();

        // if($ledger_data){
        //     foreach($ledger_data as $ledger){
                
        //         if($ledger['type'] == 'other'){
        //             if(!$other){
        //                 $other[0] = date("M d,Y",strtotime($ledger['date']));
        //                 $other[1] = $ledger['name'];
        //                 $other[2] = $ledger['amount'];
        //             }else{
        //                 $other[0] = ', ' . date("M d,Y",strtotime($ledger['date']));
        //                 $other[1] = ', ' . $ledger['name'];
        //                 $other[2] += $ledger['amount'];
        //             }
        //         }else if(strpos($ledger['remarks'], 'APPLIED FROM') !== false){
        //             if(!$applied_from){
        //                 $applied_from[0] = date("M d,Y",strtotime($ledger['date']));
        //                 $applied_from[1] = $ledger['remarks'];
        //                 $applied_from[2] = $ledger['amount'] > 0 ? $ledger['amount'] : -1 * $ledger['amount'];
        //             }else{
        //                 $applied_from[0] .= ', ' . date("M d,Y",strtotime($ledger['date']));
        //                 $applied_from[1] .= ', ' . $ledger['remarks'];
        //                 $applied_from[2] += $ledger['amount'] > 0 ? $ledger['amount'] : -1 * $ledger['amount'];
        //             }
        //         }else if(strpos($ledger['remarks'], 'APPLIED TO') !== false){
        //             if(!$applied_from){
        //                 $applied_to[0] = date("M d,Y",strtotime($ledger['date']));
        //                 $applied_to[1] = $ledger['remarks'];
        //                 $applied_to[2] = $ledger['amount'] < 0 ? $ledger['amount'] : -1 * abs($ledger['amount']);
        //             }else{
        //                 $applied_to[0] = date("M d,Y",strtotime($ledger['date']));
        //                 $applied_to[1] = $ledger['remarks'];
        //                 $applied_to[2] = $ledger['amount'] < 0 ? $ledger['amount'] : -1 * abs($ledger['amount']);
        //             }
        //         }
        //     }
        // }

        // if($reg['paymentType'] == 'full'){
        //     if($tuition['scholarship_total_assessment_rate'] > 0){
        //         $assessment_discount_rate = $tuition['scholarship_total_assessment_rate'];
        //     }
        //     if($tuition['scholarship_total_assessment_fixed'] > 0){
        //         $assessment_discount_fixed = $tuition['scholarship_total_assessment_fixed'];
        //     }
        //     if($tuition['scholarship_tuition_fee_rate'] > 0){
        //         $tuition_discount_rate = $tuition['scholarship_tuition_fee_rate'];
        //     }
        // }else{ 
        //     if($tuition['scholarship_total_assessment_rate_installment'] > 0){
        //         $assessment_discount_rate = $tuition['scholarship_total_assessment_rate_installment'];
        //     }
        //     if($tuition['scholarship_total_assessment_fixed_installment'] > 0){
        //         $assessment_discount_fixed = $tuition['scholarship_total_assessment_fixed_installment'];
        //     }
        //     if($tuition['scholarship_tuition_fee_installment_rate'] > 0){
        //         $tuition_discount_rate = $tuition['scholarship_tuition_fee_installment_rate'];
        //     }
        // }

        // $balance = $tuition['tuition_before_discount'] + $tuition['lab_before_discount'] + $tuition['misc_before_discount'] + $tuition['thesis_fee'] + $tuition['new_student'] + $tuition['late_enrollment_fee'];
        
        // $installment_balance = $tuition['tuition_installment_before_discount'] + $tuition['lab_installment_before_discount'] + $tuition['misc_before_discount'] + $tuition['thesis_fee'] + $tuition['new_student'] + $tuition['late_enrollment_fee'];
        
        // $installment_balance -= $tuition['scholarship_tuition_fee_rate'];
        // $installment_balance -= $tuition['scholarship_tuition_fee_installment_rate'];
        // $installment_balance -= $tuition['scholarship_tuition_fee_fixed'] > 0 ? $tuition['scholarship_tuition_fee_fixed'] : 0;
        // $installment_balance -= $tuition['scholarship_lab_fee_rate'] > 0 ? $tuition['scholarship_lab_fee_rate'] : 0;
        // $installment_balance -= $tuition['scholarship_lab_fee_fixed'] > 0 ? $tuition['scholarship_lab_fee_fixed'] : 0;
        // $installment_balance -= $tuition['scholarship_misc_fee_rate'] > 0 ? $tuition['scholarship_misc_fee_rate'] : 0;
        // $installment_balance -= $tuition['scholarship_misc_fee_rate'] > 0 ? $tuition['scholarship_misc_fee_rate'] : 0;
        // $installment_balance -= $tuition['nsf'] > 0 ? $tuition['nsf'] : 0;
        // $installment_balance -= $assessment_discount_rate > 0 ? $assessment_discount_rate : 0;
        // $installment_balance -= $assessment_discount_fixed > 0 ? $assessment_discount_fixed : 0;
        // $installment_balance -= $applied_from ? $applied_from[2] : 0;
        // $installment_balance -= $applied_to ? $applied_to[2] : 0;
        
        // $payment_details = $this->db->select('payment_details.*')
        //     ->from('payment_details')
        //     ->join('tb_mas_users', 'tb_mas_users.slug = payment_details.student_number')
        //     ->join('tb_mas_registration', 'tb_mas_registration.intStudentID = tb_mas_users.intID')
        //     ->where(array('payment_details.sy_reference' => $sem, 'payment_details.student_number' => $user['slug'], 'payment_details.status' => 'Paid'))
        //     ->order_by('payment_details.created_at', 'asc')
        //     ->group_by('payment_details.id')
        //     ->get()
        //     ->result_array();

        // if($payment_details){
        //     // $payment = $user_payment = $date = $student_payment = array();
        //     foreach($payment_details as $payment_index => $payment_detail){
        //         if(strpos($payment_detail['description'], 'Tuition') !== false || strpos($payment_detail['description'], 'Reservation') !== false){
        //             $installment_balance -= $payment_detail['subtotal_order'];
        //             $balance -= $payment_detail['subtotal_order'];
        //         }
        //     }
        // }


        $installment = array();
        $total_installment = 0;

        // if($reg['paymentType'] == 'partial'){
        //     $installment = array( 
        //         $installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 5) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 5) > $installment_balance && ($tuition['installment_fee'] * 5) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee'] * 4) : 0) : 0,
        //         $installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 4) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 4) > $installment_balance && ($tuition['installment_fee'] * 4) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee'] * 3) : 0) : 0,
        //         $installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 3) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 3) > $installment_balance && ($tuition['installment_fee'] * 3) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee'] * 2) : 0) : 0,
        //         $installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 2) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 2) > $installment_balance && ($tuition['installment_fee'] * 2) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee']) : 0) : 0,
        //         $installment_balance > 0 ? $installment_balance - $tuition['installment_fee'] >= 0 ? $tuition['installment_fee'] : $installment_balance : 0
        //     );
    
        //     $total_installment = $installment[0] + $installment[1] + $installment[2] + $installment[3] + $installment[4];
        // }

        $this->data['user'] =  $this->session->all_userdata();
        $this->data['student'] = $user;
        $this->data['installments'] = $installments;
        // $this->data['total_installment'] = $total_installment;
        // $this->data['balance'] = $balance;
        $this->data['reg'] = $reg;
        $this->data['course'] = $this->data_fetcher->getProgramDetails($user['intProgramID']); 
        $this->data['sy'] = $sy;
        $this->data['request'] = $request;

        tcpdf();
        // create new PDF document
        $pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Statement of Account");
        
        // set margins
        $pdf->SetMargins(5, .25, 0.5);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);    
             
        $pdf->AddPage();
          
        $html = $this->load->view("print_soa",$this->data,true);
        $pdf->writeHTML($html, true, false, true, false, '');
          
        $pdf->Output('Statement of Account -' . $user['strLastName'] . ', ' . $user['strFirstName'] . ' ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . ".pdf", 'I');

        die();
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