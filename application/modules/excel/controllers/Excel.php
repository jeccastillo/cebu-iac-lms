<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('phpexcel/PHPExcel.php');

class Excel extends CI_Controller {

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
        $this->data['title'] = "CCT Unity";
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
        $this->data["subjects"] = $this->data_fetcher->fetch_table('tb_mas_subjects');
        $this->data["students"] = $this->data_fetcher->fetch_table('tb_mas_users',array('strLastname','asc'));
        $this->data["user"] = $this->session->all_userdata();
        $this->data['unread_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intRead'=>'0','intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['all_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>0,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['trashed_messages'] = $this->data_fetcher->count_table_contents('tb_mas_message_user',null,array('intTrash'=>1,'intFacultyID'=>$this->session->userdata('intID')));
        
        $this->data['sent_messages'] = $this->data_fetcher->count_sent_items($this->session->userdata('intID'));
        $this->data['api_url'] = $this->config->item('api_url');
    }
    public function index(){
        echo "php excel module";
    }
    public function download_classlists_archive($all = 0)
    {
        $post = $this->input->post();
        $ids = $post['ids'];
        
        $sheet = 0;
        $title = date('Ymdhis').'-classlists';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle($title)
                                     ->setSubject("Classlist Download")
                                     ->setDescription("Classlist Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Classlist");
        foreach($ids as $id){
            $classlist = $this->data_fetcher->fetch_classlist_by_id(null,$id);
            $sy = $this->data_fetcher->get_sem_by_id($classlist['strAcademicYear']);
            $students = $this->data_fetcher->getClassListStudents($id);
            $subject = $this->data_fetcher->getSubjectNoCurr($classlist['intSubjectID']);

            error_reporting(E_ALL);
            ini_set('display_errors', TRUE);
            ini_set('display_startup_errors', TRUE);

            if (PHP_SAPI == 'cli')
                die('This example should only be run from a Web Browser');


            
            
            
            $objPHPExcel->createSheet($sheet);

            


            $objPHPExcel->setActiveSheetIndex($sheet)
                        ->setCellValue('B1', $subject['strCode']." ".$classlist['strSection'])
                        ->setCellValue('C1', $sy['enumSem']." Sem")
                        ->setCellValue('D1', "A.Y. " . $sy['strYearStart']."-".$sy['strYearEnd']);

             $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                );

            // Add some datat
            $objPHPExcel->setActiveSheetIndex($sheet)
                        //->setCellValue('A2', 'Name')
                        ->setCellValue('A3', 'No.')
                        ->setCellValue('B3', 'Last Name')
                        ->setCellValue('C3', 'First Name')
                        ->setCellValue('D3', 'Middle Name')
                        ->setCellValue('E3', 'Program')
                        ->setCellValue('F3', 'Student Number')
                        ->setCellValue('G3', 'Final Grade')
                        ->setCellValue('H3', 'Remarks');

            $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->applyFromArray($styleArray);
                unset($styleArray);

            $i = 4;
            $ctr = 1;
            foreach($students as $student)
            {
                if($all > 0 || !empty($student['registered'])){
                    // Add some datat
                    $objPHPExcel->setActiveSheetIndex($sheet)
                            //->setCellValue('A'.$i, $student['strLastname'].", ".$student['strFirstname'])
                            ->setCellValue('A'.$i,$ctr)
                            ->setCellValue('B'.$i, $student['strLastname'])
                            ->setCellValue('C'.$i, $student['strFirstname'])
                            ->setCellValue('D'.$i, $student['strMiddlename'])    
                            ->setCellValue('E'.$i, $student['strProgramCode'])
                            ->setCellValue('F'.$i, $student['strStudentNumber'])
                            ->setCellValue('G'.$i, $student['floatFinalGrade'])
                            ->setCellValue('H'.$i, $student['strRemarks']);
                        

                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':J'.$i)->applyFromArray($styleArray);
                    unset($styleArray);

        //            if($student['strRemarks'] == "Failed")
        //                $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':E'.$i)->applyFromArray(
        //                    array(
        //                        'fill' => array(
        //                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
        //                            'color' => array('rgb' => 'dd6666')
        //                        )
        //                    )
        //                );
                    $i++;
                    $ctr++;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(4);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                
                
                $objPHPExcel->getActiveSheet()->setTitle($subject['strCode']." ".$classlist['strSection']);

                
                
                $sheet++;
            }
        }
        
         // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
//        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
//        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        //$objWriter->save('assets/excel/'.date("Ymdhis").'-classlist.xlsx');
        //unlink('assets/excel/'.date("Ymdhis").'-classlist.xlsx');
        exit;
        
        
    }


    public function generate_excel_links()
    {
        $post = $this->input->post();
        $programType = '';

        $date = date("Y-m-d H:i:s");
        $exams = $this->db->where(array('token !='=>NULL))
                      ->order_by('student_name','ASC')
                      ->get('tb_mas_student_exam')
                      ->result_array();
        
        if(isset($post['exam_id']) && isset($post['programType'])){
            $programType = $post['programType'];
            $exams = $this->db->select('tb_mas_student_exam.*')
                    ->from('tb_mas_student_exam')
                    ->join('tb_mas_exam','tb_mas_student_exam.exam_id = tb_mas_exam.intID')
                    ->where(array('tb_mas_student_exam.exam_id'=>$post['exam_id'], 'tb_mas_student_exam.token !='=>NULL,'tb_mas_student_exam.token !='=>'', 'tb_mas_exam.programType'=> $programType))
                    ->get()
                    ->result_array();
        }
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                        ->setLastModifiedBy("Jec Castillo")
                                        ->setTitle("Generate Exam List")
                                        ->setSubject("Generate Exam List Download")
                                        ->setDescription("Generate Exam List Download.")
                                        ->setKeywords("office 2007 openxml php")
                                        ->setCategory("Generate Exam List");
    
            
          
            


        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('B1', 'Examinees');                    
        
         $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
            );
        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    //->setCellValue('A2', 'Name')
                    ->setCellValue('A3', 'No.')
                    ->setCellValue('B3', 'Student Name')                    
                    ->setCellValue('C3', 'Exam Link');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($styleArray);
            unset($styleArray);
        
        $i = 4;
        $ctr = 1;
        foreach($exams as $exam)
        {
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    //->setCellValue('A'.$i, $student['strLastname'].", ".$student['strFirstname'])
                    ->setCellValue('A'.$i,$ctr)
                    ->setCellValue('B'.$i, $exam['student_name'])
                    ->setCellValue('C'.$i, base_url()."unity/student_exam/".$exam['student_id']."/".$exam['exam_id']."/".$exam['token']);                                                                       

            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
            );
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':C'.$i)->applyFromArray($styleArray);
            unset($styleArray);
            

            $i++;
            $ctr++;
        }
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(4);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(150);    
        
        
        $objPHPExcel->getActiveSheet()->setTitle("Exam List");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="exam_list_'.$programType.''.$date.'.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;
    }

    public function download_classlist($id,$all = 0)
    {
        $date = date("Y-m-d H:i:s");
        $classlist = $this->data_fetcher->fetch_classlist_by_id(null,$id);
        $sy = $this->data_fetcher->get_sem_by_id($classlist['strAcademicYear']);
        $students = $this->data_fetcher->getClassListStudents($id);
        $subject = $this->data_fetcher->getSubjectNoCurr($classlist['intSubjectID']);
        
      
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                        ->setLastModifiedBy("Jec Castillo")
                                        ->setTitle("Export Classlist")
                                        ->setSubject("Classlist Download")
                                        ->setDescription("Export Leads Download.")
                                        ->setKeywords("office 2007 openxml php")
                                        ->setCategory("Classlist");
    
            
          
            


        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('B1', $subject['strCode']." ".$classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section'])
                    ->setCellValue('C1', $sy['enumSem']." Sem")
                    ->setCellValue('D1', "A.Y. " . $sy['strYearStart']."-".$sy['strYearEnd']);
        
         $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
            );
        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    //->setCellValue('A2', 'Name')
                    ->setCellValue('A3', 'No.')
                    ->setCellValue('B3', 'Student No.')
                    ->setCellValue('C3', 'Student Name')
                    ->setCellValue('D3', 'Course')
                    ->setCellValue('E3', 'Enrollment Status')
                    ->setCellValue('F3', 'Date Enrolled')
                    ->setCellValue('G3', 'Date Added');                    
        
        $objPHPExcel->getActiveSheet()->getStyle('A3:G3')->applyFromArray($styleArray);
            unset($styleArray);
        
        $i = 4;
        $ctr = 1;
        foreach($students as $student)
        {
            $registered = $this->data_fetcher->checkRegistered($student['intID'],$classlist['strAcademicYear']);
            $reg_info = $this->data_fetcher->getRegistrationInfo($student['intID'],$classlist['strAcademicYear']);
            if($all > 0 || !empty($registered)){
                // Add some datat
                $objPHPExcel->setActiveSheetIndex(0)
                        //->setCellValue('A'.$i, $student['strLastname'].", ".$student['strFirstname'])
                        ->setCellValue('A'.$i,$ctr)
                        ->setCellValue('B'.$i, $student['strStudentNumber'])
                        ->setCellValue('C'.$i, strtoupper($student['strLastname']." ".$student['strFirstname']." ".$student['strMiddlename']))                            
                        ->setCellValue('D'.$i, $student['strProgramCode'])
                        ->setCellValue('E'.$i, $reg_info['type_of_class']."-".$reg_info['enumStudentType'])
                        ->setCellValue('F'.$i, date("Y-m-d h:ia",strtotime($reg_info['dteRegistered'])))
                        ->setCellValue('G'.$i, date("Y-m-d h:ia",strtotime($student['date_added'])));                                                                       

                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                );
            
                $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':G'.$i)->applyFromArray($styleArray);
                unset($styleArray);
                
    //            if($student['strRemarks'] == "Failed")
    //                $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':E'.$i)->applyFromArray(
    //                    array(
    //                        'fill' => array(
    //                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
    //                            'color' => array('rgb' => 'dd6666')
    //                        )
    //                    )
    //                );
                $i++;
                $ctr++;
            }
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(4);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(70);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        
        
        $objPHPExcel->getActiveSheet()->setTitle($subject['strCode']." ".$classlist['strSection']);


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="classlist'.$date.'.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;
    }

    
    
    public function download_schedules($id)
    {
        $sched = 
            $this->db->select('tb_mas_room_schedule.*,tb_mas_faculty.intID as facultyID,tb_mas_faculty.strFirstname,tb_mas_faculty.strLastname')
                    ->from('tb_mas_room_schedule')
                    ->join('tb_mas_classlist','tb_mas_room_schedule.strScheduleCode = tb_mas_classlist.intID')
                    ->join('tb_mas_faculty','tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
                    ->where(array('intSem'=>$id))
                    ->get()
                    ->result_array();
            
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle('Schedules')
                                     ->setSubject("Schedule Download")
                                     ->setDescription("Schedule Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Classlist");


        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    //->setCellValue('A2', 'Name')
                    ->setCellValue('A1', 'Name')
                    ->setCellValue('B1', 'id')
                    ->setCellValue('C1', 'Day')
                    ->setCellValue('D1', 'Start')
                    ->setCellValue('E1', 'End');
        
        $i = 2;
        foreach($sched as $student)
        {
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    //->setCellValue('A'.$i, $student['strLastname'].", ".$student['strFirstname'])
                    ->setCellValue('A'.$i, $student['strFirstname']." ".$student['strLastname'])
                    ->setCellValue('B'.$i, $student['facultyID'])
                    ->setCellValue('C'.$i, $student['strDay'])
                    ->setCellValue('D'.$i, $student['dteStart'])
                    ->setCellValue('E'.$i, $student['dteEnd']);
            
            
//            if($student['strRemarks'] == "Failed")
//                $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':E'.$i)->applyFromArray(
//                    array(
//                        'fill' => array(
//                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
//                            'color' => array('rgb' => 'dd6666')
//                        )
//                    )
//                );
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        
        $objPHPExcel->getActiveSheet()->setTitle("Schedules");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="schedules.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function download_repeated_subject_per_student($id){
        //$reg_status = $this->data_fetcher->getRegistrationStatus($id,$this->data['selected_ay']);
        $student = $this->data_fetcher->getStudent($id);        
        $sy = $this->data_fetcher->fetch_table('tb_mas_sy');
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');
       
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'ID')
                    ->setCellValue('B1', 'Student Number')
                    ->setCellValue('C1', 'Last Name')
                    ->setCellValue('D1', 'Firstname Name')
                    ->setCellValue('E1', 'Program Code')                    
                    ->setCellValue('F1', 'Subject Code')
                    ->setCellValue('G1', 'Sem')
                    ->setCellValue('H1', 'Year Start')
                    ->setCellValue('I1', 'Year End')                    
                    ->setCellValue('J1', 'Units')
                    ->setCellValue('K1', 'amount');
        $i = 2;       

        
        foreach($sy as $s)
        {
            $reg = $this->data_fetcher->getRegistrationInfo($student['intID'],$s['intID']);
            $tuition = $this->data_fetcher->getTuition($student['intID'],$s['intID'],$this->data['unit_fee'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$reg['enumScholarship']);                
                    
            if(count($tuition['repeated'])){                  
                foreach($tuition['repeated'] as $tData){
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.$i, $student['intID'])
                            ->setCellValue('B'.$i, $student['strStudentNumber'])
                            ->setCellValue('C'.$i, $student['strLastname'])
                            ->setCellValue('D'.$i, $student['strFirstname'])
                            ->setCellValue('E'.$i, $student['strProgramCode'])
                            ->setCellValue('F'.$i, $tData['subjectCode'])
                            ->setCellValue('G'.$i, $s['enumSem'])
                            ->setCellValue('H'.$i, $s['strYearStart'])
                            ->setCellValue('I'.$i, $s['strYearEnd'])
                            ->setCellValue('J'.$i, $tData['strUnits'])
                            ->setCellValue('K'.$i, $tData['amount']);                            
                    
                    
                    $i++;
                }
            }
        }
        
        // $objPHPExcel->getActiveSheet()->getStyle('I2:I'.count($tuition))
        // ->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);        
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Repeated');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');       
        header('Content-Disposition: attachment;filename="repeated_subjects.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    public function download_repeated_subjects($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0){
        
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,1,$sem);
        $sy = $this->data_fetcher->fetch_table('tb_mas_sy');
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'ID')
                    ->setCellValue('B1', 'Student Number')
                    ->setCellValue('C1', 'Last Name')
                    ->setCellValue('D1', 'Firstname Name')
                    ->setCellValue('E1', 'Program Code')                    
                    ->setCellValue('F1', 'Subject Code')
                    ->setCellValue('G1', 'Sem')
                    ->setCellValue('H1', 'Year Start')
                    ->setCellValue('I1', 'Year End')                    
                    ->setCellValue('J1', 'Units')
                    ->setCellValue('K1', 'amount');
        $i = 2;
        foreach($students as $student)
        {
            // Add some datat            
            //$newPass = password_hash($oldPass_unhash, PASSWORD_DEFAULT);
            
            foreach($sy as $s)
            {
                $reg = $this->data_fetcher->getRegistrationInfo($student['intID'],$s['intID']);
                $tuition = $this->data_fetcher->getTuition($student['intID'],$s['intID'],$this->data['unit_fee'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$reg['enumScholarship']);                
            

                foreach($tuition['repeated'] as $tData){
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.$i, $student['intID'])
                            ->setCellValue('B'.$i, $student['strStudentNumber'])
                            ->setCellValue('C'.$i, $student['strLastname'])
                            ->setCellValue('D'.$i, $student['strFirstname'])
                            ->setCellValue('E'.$i, $student['strProgramCode'])
                            ->setCellValue('F'.$i, $tData['subjectCode'])
                            ->setCellValue('G'.$i, $s['enumSem'])
                            ->setCellValue('H'.$i, $s['strYearStart'])
                            ->setCellValue('I'.$i, $s['strYearEnd'])
                            ->setCellValue('J'.$i, $tData['strUnits'])
                            //->setCellValue('K'.$i, pw_unhash($student['strPass']))
                            //->setCellValue('L'.$i, password_hash($oldPass_unhash, PASSWORD_DEFAULT))
                            //->setCellValue('K'.$i, pw_hash(date("mdY",strtotime($student['dteBirthDate']))))
                            ->setCellValue('K'.$i, $tData['amount']);                            
                    
                    
                    $i++;
                }
            }
        }
        // $objPHPExcel->getActiveSheet()->getStyle('I2:I'.count($students))
        // ->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
       // $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);        
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        
        $objPHPExcel->getActiveSheet()->setTitle('Students Repeated');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="registered_students.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    public function deficiency_report_data($sem){

        if($sem != 0)
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        else
            $active_sem = $this->data_fetcher->get_active_sem();
        
       
        $students = $this->db
                    ->select('tb_mas_student_deficiencies.*,strFirstname,strLastname,strStudentNumber,strMiddlename')
                    ->join('tb_mas_users','tb_mas_student_deficiencies.student_id = tb_mas_users.intID')
                    ->where(array('syid'=>$active_sem['intID']))
                    ->order_by('strLastname asc,strFirstname asc')                    
                    ->get('tb_mas_student_deficiencies')
                    ->result_array();

        $date = date("M j, Y h:i a");  
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        //HEADER
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A3', '');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A4', $date);
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A5', $active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                                        
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A6', "LIST OF STUDENT DEFICIENCIES");

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:M2');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:M3');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:M4');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:M5');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:M6');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $style_right = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $style_left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:L2")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:L5")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:L2")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A3:L3")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A4:L4")->applyFromArray($style_right);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:L5")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A6:L6")->applyFromArray($style);

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student Deficiency List")
                                     ->setSubject("Student Deficiency List Download")
                                     ->setDescription("Student Deficiency List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student Deficiency List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A8', '#')
                    ->setCellValue('B8', 'Student Number')
                    ->setCellValue('C8', 'Student Name')
                    ->setCellValue('D8', 'Details')
                    ->setCellValue('E8', 'Department')
                    ->setCellValue('F8', 'Date Added')
                    ->setCellValue('G8', 'Status')
                    ->setCellValue('H8', 'Remarks')
                    ->setCellValue('I8', 'Added By')
                    ->setCellValue('J8', 'Date Resolved')
                    ->setCellValue('K8', 'Resolved By')
                    ->setCellValue('L8', 'Temporary Resolve Date');
                    
                    
        $i = 9;
        $count = 1;
        foreach($students as $student)
        {
            

            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $count.".")
                    ->setCellValue('B'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
                    ->setCellValue('C'.$i, strtoupper($student['strLastname'].", ".$student['strFirstname']." ".$student['strMiddlename']))
                    ->setCellValue('D'.$i, $student['details'])
                    ->setCellValue('E'.$i, $student['department'])
                    ->setCellValue('F'.$i, date("M j, Y",strtotime($student['date_added'])))
                    ->setCellValue('G'.$i, $student['status'])
                    ->setCellValue('H'.$i, $student['remarks'])
                    ->setCellValue('I'.$i, $student['added_by']);
                
                if($student['date_resolved'])
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('J'.$i, date("M j, Y",strtotime($student['date_resolved'])));
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('K'.$i, $student['resolved_by']);

                if($student['temporary_resolve_date'])
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('L'.$i, date("M j, Y",strtotime($student['temporary_resolve_date'])));
                                            
            $count++;
            $i++;
            
        }
        // $objPHPExcel->getActiveSheet()->getStyle('H9:I'.count($students))
        // ->getAlignment()->setWrapText(true);
        

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(70);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        
        

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Deficiencies');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="deficiencies'.date("ymdhis").'.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;          
        
        
    }

    public function ched_enrollment_list($course = 0, $year=0,$gender = 0,$sem=0, $type='college'){

        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
                
        $this->data['sy'] = $active_sem;

        if($course != 0)
            $programs = $this->db->get_where('tb_mas_programs',array('intProgramID'=>$course))->result_array();
        else
            $programs = $this->db->get_where('tb_mas_programs',array('type'=>$type))->result_array();

       

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        $active_sheet = 0;
        foreach($programs as $program){
            $objPHPExcel->createSheet($active_sheet);
            //--------------------------------HEADER----------------------------------------------
            $objPHPExcel->setActiveSheetIndex($active_sheet)
            ->setCellValue('A1', 'Name of Institution:');
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('C1', 'iACADEMY Cebu');
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('A2', 'Address:');
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('C2', 'Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City');                                        
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('A3', "Institutional Identifier");
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('C3', '');
            
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('A4', $active_sem['term_label']);
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('C4', $active_sem['enumSem'].' '.$active_sem['term_label'].', AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('A5', "Course / Program:");
            
            $major = ($program['strMajor'] != "None" && $program['strMajor'] != "")?"Major in ".$program['strMajor']:'';

            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('C5', $program['strProgramDescription']." ".$major);     
                    
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('A6', "Year Level:");
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('C6', $year);

            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            );

            $style2 = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                )
            );

            $objPHPExcel->setActiveSheetIndex($active_sheet)->getStyle("C6")->applyFromArray($style2);



            //$active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']

            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A1:B1');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A2:B2');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A3:B3');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A4:B4');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A5:B5');      
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A6:B6');  
            //--------------------------------HEADER----------------------------------------------

            $term_label = ($active_sem['term_label'] == "Sem")?'Semester':'Term';
            // Add some datat
            $objPHPExcel->setActiveSheetIndex($active_sheet)
                        ->setCellValue('A7', $term_label)
                        ->setCellValue('B7', 'Student No.')
                        ->setCellValue('C7', 'Student Name')
                        ->setCellValue('F7', 'Course')
                        ->setCellValue('G7', 'Gender')
                        ->setCellValue('H7', 'Bdate')                                        
                        ->setCellValue('I7', 'Current Year')     
                        ->setCellValue('J7', 'Subjects Enrolled')
                        ->setCellValue('K7', 'No. of Units');


            $objPHPExcel->setActiveSheetIndex($active_sheet)->getStyle("I7")->applyFromArray($style);                    
            $objPHPExcel->setActiveSheetIndex($active_sheet)->getStyle("K7")->applyFromArray($style);                    
            $objPHPExcel->setActiveSheetIndex($active_sheet)->getStyle("C7")->applyFromArray($style);

            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('A7:A8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('B7:B8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('F7:F8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('G7:G8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('H7:H8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('I7:I8');                    
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('J7:J8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('K7:K8');
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('C7:E7');

            $objPHPExcel->setActiveSheetIndex($active_sheet)                                        
                        ->setCellValue('C8', 'Surname')
                        ->setCellValue('D8', 'First Name')
                        ->setCellValue('E8', 'Middle Name');                    
                        

            $i = 9;

            $st = [];
            $students = $this->data_fetcher->getStudents($program['intProgramID'],0,$year,$gender,0,0,2,$sem);
            if(!empty($students)){        
                foreach($students as $student)
                {
                    $classes = "";
                    $total_units = 0;    
                    $cl = $this->data_fetcher->getClassListStudentsSt($student['intID'],$sem);
                    foreach($cl as $class){
                
                        $classes .=($classes=="")?$class['strCode']:",".$class['strCode'];                                                        
                        $total_units += $class['strUnits'];                   
                    }

                    // Add some datat
                    $objPHPExcel->setActiveSheetIndex($active_sheet)
                    ->setCellValue('A'.$i, $active_sem['enumSem'])
                    ->setCellValue('B'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
                    ->setCellValue('C'.$i, $student['strLastname'])
                    ->setCellValue('D'.$i, $student['strFirstname'])
                    ->setCellValue('E'.$i, strtoupper($student['strMiddlename']))
                    ->setCellValue('F'.$i, $student['strProgramCode'])
                    ->setCellValue('G'.$i, $student['enumGender'])
                    ->setCellValue('H'.$i, date("m/d/Y", strtotime($student['dteBirthDate'])))
                    ->setCellValue('I'.$i, $student['intYearLevel'])
                    ->setCellValue('J'.$i, $classes)
                    ->setCellValue('K'.$i, $total_units);
                    
            
                    $objPHPExcel->setActiveSheetIndex($active_sheet)->getStyle("I".$i)->applyFromArray($style);
                    $objPHPExcel->setActiveSheetIndex($active_sheet)->getStyle("K".$i)->applyFromArray($style);
                    $i++;
                }
            }

            $i += 2;

            $objPHPExcel->setActiveSheetIndex($active_sheet)
            ->setCellValue('B'.$i, "Prepared By: ____________________________")
            ->setCellValue('B'.($i + 1), "                                                         Registrar");

            $objPHPExcel->setActiveSheetIndex($active_sheet)
            ->setCellValue('I'.$i, "Certified Correct: ____________________________")
            ->setCellValue('I'.($i + 1), "                                                      College Dean");

            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('B'.$i.':E'.$i);
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('B'.($i+1).':E'.($i+1));
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('I'.$i.':J'.$i);
            $objPHPExcel->setActiveSheetIndex($active_sheet)->mergeCells('I'.($i+1).':J'.($i+1));

            $objPHPExcel->getActiveSheet()->getStyle('C7:K7')
            ->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('C8:K8')
            ->getAlignment()->setWrapText(true);

            

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(80);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
            // Miscellaneous glyphs, UTF-8
            //$objPHPExcel->setActiveSheetIndex($active_sheet)
            //          ->setCellValue('A4', 'Miscellaneous glyphs')
            //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        
            $objPHPExcel->getActiveSheet()->setTitle($program['strProgramCode']);
            $active_sheet++;            
        }
       


       $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

       // Redirect output to a client’s web browser (Excel2007)
       header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
       header('Content-Disposition: attachment;filename="ched_enrollment_list'.$active_sem['enumSem'].$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xls"');
       header('Cache-Control: max-age=0');
       // If you're serving to IE 9, then the following may be needed
       header('Cache-Control: max-age=1');

       // If you're serving to IE over SSL, then the following may be needed
       header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
       header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
       header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
       header ('Pragma: public'); // HTTP/1.0

       
       $objWriter->save('php://output');
       exit;


    }

    public function download_students_new($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0, $studNumStart, $studNumEnd)
    {
        
        $students = $this->data_fetcher->getStudentsNew($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem, $studNumStart, $studNumEnd);
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        if($sem!=0){
             $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Student Number')
                    ->setCellValue('B1', 'Last Name')
                    ->setCellValue('C1', 'First Name')
                    ->setCellValue('D1', 'Middle Name')
                    ->setCellValue('E1', 'Gender')
                    ->setCellValue('F1', 'Course')
                    ->setCellValue('G1', 'Scholarship')
                    ->setCellValue('H1', 'Birthdate')
                    ->setCellValue('I1', 'Address')
                    ->setCellValue('K1', 'GSuiteEmail')
                    ->setCellValue('L1', 'intID');
        $i = 2;
        foreach($students as $student)
        {
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $student['strStudentNumber'])
                    ->setCellValue('B'.$i, $student['strLastname'])
                    ->setCellValue('C'.$i, $student['strFirstname'])
                    ->setCellValue('D'.$i, $student['strMiddlename'])
                    ->setCellValue('E'.$i, $student['enumGender'])
                    ->setCellValue('F'.$i, $student['strProgramCode'])
                    ->setCellValue('G'.$i, strtoupper($student['enumScholarship']))
                    ->setCellValue('H'.$i, date("m-d-Y", strtotime($student['dteBirthDate'])))
                    ->setCellValue('I'.$i, $student['strAddress'])
                    ->setCellValue('J'.$i, pw_hash(date("mdY",strtotime($student['dteBirthDate']))))
                    ->setCellValue('K'.$i, $student['strGSuiteEmail'])
                    ->setCellValue('L'.$i, $student['intID']); 
            
            
            $i++;
        }
        $objPHPExcel->getActiveSheet()->getStyle('I2:I'.count($students))
        ->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        if($course!=0 && $year!=0)
            $objPHPExcel->getActiveSheet()->setTitle($student['strProgramCode'], "-", $student['intStudentYear']);
        else
            $objPHPExcel->getActiveSheet()->setTitle('Students');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($registered != 0)
            header('Content-Disposition: attachment;filename="registered_students'.$active_sem['enumSem'].'sem'.$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xlsx"');
        else
            header('Content-Disposition: attachment;filename="student_list.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function download_classlists($sem , $program, $dissolved, $has_faculty){
        $classlists = $this->data_fetcher->getClasslists($sem , $program, $dissolved, 0);
        $date = date("Y-m-d H:i:s");

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Faculty Loading")
                                     ->setSubject("Faculty Loading Download")
                                     ->setDescription("Faculty Loading Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Faculty Loading");

        // Add some datat
        if($sem!=0){
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        
        //HEADER
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A3', 'Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A4', date("M j, Y h:i a"));
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A5', $active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                                        
        if($dissolved)
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A6', "DISSOLVED CLASSES");
        else
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A6', "SCHEDULE OF CLASSES BY SUBJECT");

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:I2');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:I3');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:I4');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:I5');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:I6');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $style_right = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:I2")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:I5")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:I2")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A3:I3")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A4:I4")->applyFromArray($style_right);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:I5")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A6:I6")->applyFromArray($style);

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A8', 'Section')
                    ->setCellValue('B8', 'Subject Code')
                    ->setCellValue('C8', 'Subject Description')
                    ->setCellValue('D8', 'Units')
                    ->setCellValue('E8', 'Day')
                    ->setCellValue('F8', 'Time')
                    ->setCellValue('G8', 'Room')
                    ->setCellValue('H8', 'Enrolled')
                    ->setCellValue('I8', 'Instructor');
          
                    $objPHPExcel->getActiveSheet()->getStyle('A8:I8')->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'c2c2c2')
                            )
                        )
                    );           

        $i = 9;
        foreach($classlists as $classlist)
        {
            $objPHPExcel->setActiveSheetIndex(0)            
            ->setCellValue('A'.$i, $classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section'])
            ->setCellValue('B'.$i, $classlist['strCode'])
            ->setCellValue('C'.$i, $classlist['subjectDescription'])
            ->setCellValue('D'.$i, $classlist['strUnits'])
            ->setCellValue('E'.$i, $classlist['sched_day'])
            ->setCellValue('F'.$i, $classlist['sched_time'])
            ->setCellValue('G'.$i, $classlist['sched_room'])
            ->setCellValue('H'.$i, $classlist['slots_taken_enrolled'])
            ->setCellValue('I'.$i, strtoupper($classlist['strLastname'].", ".$classlist['strFirstname']." ".$classlist['strMiddlename']));

            $i++;
        }
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);
    //    // $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    //     //$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
    //     $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);        
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Classlists');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="faculty_loading_data'.$date.'.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;

        
    }

    public function download_student_grades($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0){
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem);
        $date = date("Y-m-d H:i:s");
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Add some data
        if($sem!=0){
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        
        //HEADER
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A3', 'Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A4', date("M j, Y h:i a"));
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A5', $active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                                        
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A6', "LIST OF STUDENT GRADES");

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:N2');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:N3');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:N4');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:N5');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:N6');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $style_right = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $style_left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:N2")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:N5")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:N2")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A3:N3")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A4:N4")->applyFromArray($style_right);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:N5")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A6:N6")->applyFromArray($style);

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A8', '#')
                    ->setCellValue('B8', 'Student Number')
                    ->setCellValue('C8', 'Student Name')
                    ->setCellValue('D8', 'Section')
                    ->setCellValue('E8', 'Subject')
                    ->setCellValue('F8', 'Units')
                    ->setCellValue('G8', 'Room')
                    ->setCellValue('H8', 'Day')
                    ->setCellValue('I8', 'Time')
                    ->setCellValue('J8', 'Midterm Grade')
                    ->setCellValue('K8', 'Final Grade')
                    ->setCellValue('L8', 'Remarks')
                    ->setCellValue('M8', 'Professor')
                    ->setCellValue('N8', 'Date Enrolled');
                    
                    
        $i = 9;
        $count = 1;
        foreach($students as $student)
        {
            $classlists = $this->data_fetcher->getClassListStudentsSt($student['intID'],$sem);

            // Add some datat
            $oldPass_unhash = pw_unhash($student['strPass']);
            //$newPass = password_hash($oldPass_unhash, PASSWORD_DEFAULT);

            foreach($classlists as $cl){

            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $count.".")
                    ->setCellValue('B'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
                    ->setCellValue('C'.$i, strtoupper($student['strLastname'].", ".$student['strFirstname']." ".$student['strMiddlename']))
                    ->setCellValue('D'.$i, $cl['strClassName'].$cl['year'].$cl['strSection']." ".$cl['sub_section'])
                    ->setCellValue('E'.$i, $cl['strCode'])
                    ->setCellValue('F'.$i, $cl['strUnits'])
                    ->setCellValue('G'.$i, $cl['sched_room'])
                    ->setCellValue('H'.$i, $cl['sched_day'])
                    ->setCellValue('I'.$i, $cl['sched_time'])
                    ->setCellValue('J'.$i, $cl['v2'])
                    ->setCellValue('K'.$i, $cl['v3'])
                    ->setCellValue('L'.$i, $cl['strRemarks'])
                    ->setCellValue('M'.$i, $cl['strLastname'].", ".$cl['strFirstname'])
                    ->setCellValue('N'.$i, date("M j, Y",strtotime($student['dteRegistered'])));
                    
            
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle("F".$i)->applyFromArray($style_left);
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle("I".$i)->applyFromArray($style);
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle("J".$i)->applyFromArray($style);
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle("N".$i)->applyFromArray($style);
                    $count++;
                    $i++;
            }
        }
        // $objPHPExcel->getActiveSheet()->getStyle('A2:I'.count($students))
        // ->getAlignment()->setWrapText(true);
        

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(30);        
                
        

        // Rename worksheet
        if($course!=0 && $year!=0)
            $objPHPExcel->getActiveSheet()->setTitle($student['strProgramCode'], "-", $student['intStudentYear']);
        else
            $objPHPExcel->getActiveSheet()->setTitle('Students');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="student_grades'.$date.'.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;
        
        
    }

    //enrolled students in neo
    public function download_enrolled_students_neo($course = 0,$regular= 0, $year=0,$gender = 0,$scholarship=0,$registered=0,$sem = 0, $isAll = 0, $level=0) {

        // echo $level;
        // exit;
        $students = $this->data_fetcher->getEnrolledStudents($course,$regular,$year,$gender,$scholarship,$registered,$sem);
        $date = date("Y-m-d H:i:s");
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Add some data
        if($sem!=0){
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        
        //HEADER
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A3', 'Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A4', date("M j, Y h:i a"));
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A5', $active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                                        
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A6', "LIST OF ENROLLED STUDENTS");

        if ($isAll) {
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:J2');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:J3');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:J4');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:J5');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:J6');
        } else {
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:F2');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:F3');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:F4');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:F5');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:F6');
        }
        
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $style_right = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $style_left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:N2")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:N5")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:N2")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A3:N3")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A4:N4")->applyFromArray($style_right);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:N5")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A6:N6")->applyFromArray($style);

         // Set document properties
         $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                    ->setLastModifiedBy("Jec Castillo")
                    ->setTitle("Enrolled Students in Neo")
                    ->setSubject("Enrolled Students in Neo")
                    ->setDescription("Enrolled Students in Neo Download.")
                    ->setKeywords("office 2007 openxml php")
                    ->setCategory("Enrolled Students in Neo");

        if ($isAll) {
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A8', '#')
            ->setCellValue('B8', 'First Name')
            ->setCellValue('C8', 'Last Name')
            ->setCellValue('D8', 'UserID')
            ->setCellValue('E8', 'StudentID')
            ->setCellValue('F8', 'Student IACADEMY Email')
            ->setCellValue('G8', 'Parent 1(Father  Name)') //father frist name not available
            // ->setCellValue('H8', 'Parent 1 (Lastname)') //not available only full name of father
            ->setCellValue('H8', 'Father Email')
            ->setCellValue('I8', 'Mother')
            ->setCellValue('J8', 'Mother Email');
        } else {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A8', '#')
                    ->setCellValue('B8', 'Course Code')
                    ->setCellValue('C8', 'Student iACADEMY Email');
        }
                    
        $i = 9;
        $count = 1;
        foreach($students as $student)
        {


            if ($isAll) {
                $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A'.$i, $count.".")
                                ->setCellValue('B'.$i, $student['strFirstname'])
                                ->setCellValue('C'.$i, $student['strLastname'])
                                ->setCellValue('D'.$i, $student['intID'])
                                ->setCellValue('E'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
                                ->setCellValue('F'.$i, $student['strEmail'])
                                ->setCellValue('G'.$i, $student['father'])
                                ->setCellValue('H'.$i, $student['father_email'])
                                ->setCellValue('I'.$i, $student['mother'])
                                ->setCellValue('J'.$i, $student['mother_email']);
            } else {
                $classlists = $this->data_fetcher->getClassListStudentsSt($student['intID'],$sem);
                foreach($classlists as $cl)
                {
                        $courseCode = '';
                        if ($level == '1' || $level == '2') {
                            $semDetails = $this->data_fetcher->get_sem_by_id($sem);

                            $startYear = substr($semDetails['strYearStart'], -2);
                            $endYear = substr($semDetails['strYearEnd'], -2);
                            $termYer = $semDetails['enumSem'].'Term-'.'SY'.$startYear.'-'.$endYear;
                            $subject = $this->data_fetcher->getClasslistDetails($cl['intClassListID']);
                            //check is UG = 2 or SHS = 1
                            if ($level == '1') {
                                $courseCode = 'SHS-CEBU-'.$subject->strClassName.'-'.$termYer;
                            } else if ($level == '2') {
                                $courseCode = 'UG-CEBU-'.$subject->strClassName.'-'.$termYer;
                            }
                        }
                        
                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$i, $count.".")
                                    ->setCellValue('B'.$i, $courseCode)
                                    // ->setCellValue('B'.$i, $courseCode)
                                    // ->setCellValue('B'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
                                    ->setCellValue('C'.$i, $student['strEmail']);

                        $objPHPExcel->setActiveSheetIndex(0)->getStyle("F".$i)->applyFromArray($style_left);
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle("I".$i)->applyFromArray($style);
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle("J".$i)->applyFromArray($style);
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle("N".$i)->applyFromArray($style);
                        $count++;
                        $i++;
                }
            }
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="enrolled_students.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;
        
    }

    function adjustments($id,$sem){
        
        $adjustments = $this->db
                            ->select('tb_mas_classlist_student_adjustment_log.*, strCode, strFirstname, strLastname')
                            ->from('tb_mas_classlist_student_adjustment_log')  
                            ->join('tb_mas_subjects', 'tb_mas_classlist_student_adjustment_log.classlist_student_id = tb_mas_subjects.intID')                                     
                            ->join('tb_mas_faculty', 'tb_mas_classlist_student_adjustment_log.adjusted_by = tb_mas_faculty.intID')                                     
                            ->where(array('student_id'=>$id,'syid'=>$sem))
                            ->order_by('tb_mas_classlist_student_adjustment_log.date','asc')
                            ->get()
                            ->result_array();

        $student = $this->data_fetcher->getStudent($id); 
        $date = date("Y-m-d H:i:s");

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Add some data
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
        
        //HEADER
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A3', 'SECTION ADJUSTMENTS');
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A4', date("M j, Y h:i a"));
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A5', $active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                                        
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A6', "STUDENT: ".strtoupper($student['strLastname']).", ".strtoupper($student['strFirstname']));

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:G2');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:G3');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:G4');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:G5');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:G6');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $style_right = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $style_left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:G2")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:G5")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:G2")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A3:G3")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A4:G4")->applyFromArray($style_right);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:G5")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A6:G6")->applyFromArray($style);        

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A8', 'Subject')
                    ->setCellValue('B8', 'Adjustment')
                    ->setCellValue('C8', 'Removed')
                    ->setCellValue('D8', 'Added')
                    ->setCellValue('E8', 'Adjusted By')
                    ->setCellValue('F8', 'Remarks')
                    ->setCellValue('G8', 'Date');
                    
        $i = 9;
        foreach($adjustments as $adj)
        {
            
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $adj['strCode'])
                    ->setCellValue('B'.$i, $adj['adjustment_type'])
                    ->setCellValue('C'.$i, $adj['from_subject'])
                    ->setCellValue('D'.$i, $adj['to_subject'])
                    ->setCellValue('E'.$i, $adj['strLastname']." ".$adj['strFirstname'])
                    ->setCellValue('F'.$i, $adj['remarks'])
                    ->setCellValue('G'.$i, $adj['date']);                                                                                                                                             
                                
            
            $i++;
        }
                

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);       
        
        $objPHPExcel->getActiveSheet()->getStyle('F9:F'.$i)
        ->getAlignment()->setWrapText(true);


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 
         // Redirect output to a client’s web browser (Excel2007)
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
         header('Content-Disposition: attachment;filename="adjustments'.$date.'.xls"');
         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;
    }
    
    public function download_students($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0, $neo = 0, $level = 0)
    {
        
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem,0,$level);
        $date = date("Y-m-d H:i:s");
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        if($sem!=0){
             $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        if($neo){
            // Add some datat
            // $objPHPExcel->setActiveSheetIndex(0)
            //             ->setCellValue('A1', 'First Name')
            //             ->setCellValue('B1', 'Last Name')
            //             ->setCellValue('C1', 'UserID')
            //             ->setCellValue('D1', 'StudentID')
            //             ->setCellValue('E1', 'Student iacademy Email')
            //             ->setCellValue('F1', 'Parent 1(Father First Name)')
            //             ->setCellValue('G1', 'Parent 1 (Lastname)')
            //             ->setCellValue('H1', 'Parent 1 (Father Email)')
            //             ->setCellValue('I1', 'Birthdate')
            //             ->setCellValue('J1', 'Gender')
            //             ->setCellValue('K1', 'Address')
            //             ->setCellValue('L1', 'Nationality')                    
            //             ->setCellValue('M1', 'Residential Address')
            //             ->setCellValue('N1', 'Cell #')
            //             ->setCellValue('O1', 'Email Address')
            //             ->setCellValue('P1', 'Father\'s Name')
            //             ->setCellValue('Q1', 'Father\'s Email')
            //             ->setCellValue('R1', 'Father\'s Mobile #')
            //             ->setCellValue('S1', 'Mother\'s Name')
            //             ->setCellValue('T1', 'Mother\'s Email')
            //             ->setCellValue('U1', 'Mother\'s Mobile #')
            //             ->setCellValue('V1', 'Guardian\'s Name')
            //             ->setCellValue('W1', 'Guardian\'s Email')
            //             ->setCellValue('X1', 'Guardian\'s Mobile #')
            //             ->setCellValue('Y1', 'High School')
            //             ->setCellValue('Z1', 'School Address')
            //             ->setCellValue('AA1', 'School Year')
            //             ->setCellValue('AB1', 'Senior High School')
            //             ->setCellValue('AC1', 'School Address')
            //             ->setCellValue('AD1', 'School Year')
            //             ->setCellValue('AE1', 'Strand')
            //             ->setCellValue('AF1', 'College')
            //             ->setCellValue('AG1', 'School Address')
            //             ->setCellValue('AH1', 'School Year From')
            //             ->setCellValue('AI1', 'School Year To')
            //             ->setCellValue('AJ1', 'Date Enrolled')
            //             ->setCellValue('AK1', 'Curriculum')
            //             ->setCellValue('AL1', 'Active GWA')
            //             ->setCellValue('AM1', 'Total Units Earned')
            //             ->setCellValue('AN1', 'Enrollment Status')
            //             ->setCellValue('AO1', 'Mode of Payment')
            //             ->setCellValue('AP1', 'Student Type');
            // $i = 2;
            // foreach($students as $student)
            // {
            //     // Add some datat
            //     $oldPass_unhash = pw_unhash($student['strPass']);
            //     //$newPass = password_hash($oldPass_unhash, PASSWORD_DEFAULT);

            //     $objPHPExcel->setActiveSheetIndex(0)
            //             ->setCellValue('A'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
            //             ->setCellValue('B'.$i, strtoupper($student['strLastname']))
            //             ->setCellValue('C'.$i, strtoupper($student['strFirstname']))
            //             ->setCellValue('D'.$i, strtoupper($student['strMiddlename']))
            //             ->setCellValue('E'.$i, $student['strProgramCode'])
            //             ->setCellValue('F'.$i, $student['strProgramDescription'])
            //             ->setCellValue('G'.$i, $student['intStudentYear'])
            //             ->setCellValue('H'.$i, strtoupper($student['blockName']))
            //             ->setCellValue('I'.$i, date("M j, Y", strtotime($student['dteBirthDate'])))
            //             ->setCellValue('J'.$i, strtoupper($student['enumGender']))
            //             ->setCellValue('K'.$i, $student['strAddress'])                    
            //             ->setCellValue('L'.$i, strtoupper($student['strCitizenship']))
            //             ->setCellValue('M'.$i, strtoupper($student['strAddress']))
            //             ->setCellValue('N'.$i, strtoupper($student['strMobileNumber']))
            //             ->setCellValue('O'.$i, $student['strEmail'])
            //             ->setCellValue('P'.$i, strtoupper($student['father']))
            //             ->setCellValue('Q'.$i, strtoupper($student['father_email']))
            //             ->setCellValue('R'.$i, strtoupper($student['father_contact']))
            //             ->setCellValue('S'.$i, strtoupper($student['mother']))
            //             ->setCellValue('T'.$i, strtoupper($student['mother_email']))
            //             ->setCellValue('U'.$i, strtoupper($student['mother_contact']))
            //             ->setCellValue('V'.$i, strtoupper($student['guardian']))
            //             ->setCellValue('W'.$i, strtoupper($student['guardian_email']))
            //             ->setCellValue('X'.$i, strtoupper($student['guardian_contact']))
            //             ->setCellValue('Y'.$i, strtoupper($student['high_school']))
            //             ->setCellValue('Z'.$i, strtoupper($student['high_school_address']))
            //             ->setCellValue('AA'.$i, strtoupper($student['high_school_attended']))
            //             ->setCellValue('AB'.$i, strtoupper($student['senior_high']))
            //             ->setCellValue('AC'.$i, strtoupper($student['senior_high_address']))
            //             ->setCellValue('AD'.$i, strtoupper($student['senior_high_attended']))
            //             ->setCellValue('AE'.$i, strtoupper($student['strand']))
            //             ->setCellValue('AF'.$i, strtoupper($student['college']))
            //             ->setCellValue('AG'.$i, strtoupper($student['college_address']))
            //             ->setCellValue('AH'.$i, strtoupper($student['college_attended_from']))
            //             ->setCellValue('AI'.$i, strtoupper($student['college_attended_to']))
            //             ->setCellValue('AJ'.$i, strtoupper($student['dteRegistered']))
            //             ->setCellValue('AK'.$i, strtoupper($student['curriculumName']))
            //             ->setCellValue('AL'.$i, "")
            //             ->setCellValue('AM'.$i, "")
            //             ->setCellValue('AN'.$i, strtoupper($student['type_of_class']))
            //             ->setCellValue('AO'.$i, "")
            //             ->setCellValue('AP'.$i, strtoupper($student['student_type']));
                        
                
                
            //     $i++;
            // }
            // // $objPHPExcel->getActiveSheet()->getStyle('A2:I'.count($students))
            // // ->getAlignment()->setWrapText(true);

            
            // $objPHPExcel->getActiveSheet()->freezePane('E2');

            // $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);        
            // $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(35);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(35);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(35);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(35);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(30);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(30);
        }
        else{
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'Student Number')
                        ->setCellValue('B1', 'Last Name')
                        ->setCellValue('C1', 'First Name')
                        ->setCellValue('D1', 'Middle Name')
                        ->setCellValue('E1', 'Course')
                        ->setCellValue('F1', 'Course Description')
                        ->setCellValue('G1', 'Year Level')
                        ->setCellValue('H1', 'Section')
                        ->setCellValue('I1', 'Birthdate')
                        ->setCellValue('J1', 'Gender')
                        ->setCellValue('K1', 'Address')
                        ->setCellValue('L1', 'Nationality')                    
                        ->setCellValue('M1', 'Residential Address')
                        ->setCellValue('N1', 'Cell #')
                        ->setCellValue('O1', 'Email Address')
                        ->setCellValue('P1', 'Father\'s Name')
                        ->setCellValue('Q1', 'Father\'s Email')
                        ->setCellValue('R1', 'Father\'s Mobile #')
                        ->setCellValue('S1', 'Mother\'s Name')
                        ->setCellValue('T1', 'Mother\'s Email')
                        ->setCellValue('U1', 'Mother\'s Mobile #')
                        ->setCellValue('V1', 'Guardian\'s Name')
                        ->setCellValue('W1', 'Guardian\'s Email')
                        ->setCellValue('X1', 'Guardian\'s Mobile #')
                        ->setCellValue('Y1', 'High School')
                        ->setCellValue('Z1', 'School Address')
                        ->setCellValue('AA1', 'School Year')
                        ->setCellValue('AB1', 'Senior High School')
                        ->setCellValue('AC1', 'School Address')
                        ->setCellValue('AD1', 'School Year')
                        ->setCellValue('AE1', 'Strand')
                        ->setCellValue('AF1', 'College')
                        ->setCellValue('AG1', 'School Address')
                        ->setCellValue('AH1', 'School Year From')
                        ->setCellValue('AI1', 'School Year To')
                        ->setCellValue('AJ1', 'Date Enrolled')
                        ->setCellValue('AK1', 'Curriculum')
                        ->setCellValue('AL1', 'Active GWA')
                        ->setCellValue('AM1', 'Total Units Earned')
                        ->setCellValue('AN1', 'Enrollment Status')
                        ->setCellValue('AO1', 'Mode of Payment')
                        ->setCellValue('AP1', 'Student Type');
            $i = 2;
            foreach($students as $student)
            {
                // Add some datat
                $oldPass_unhash = pw_unhash($student['strPass']);
                //$newPass = password_hash($oldPass_unhash, PASSWORD_DEFAULT);

                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']))
                        ->setCellValue('B'.$i, strtoupper($student['strLastname']))
                        ->setCellValue('C'.$i, strtoupper($student['strFirstname']))
                        ->setCellValue('D'.$i, strtoupper($student['strMiddlename']))
                        ->setCellValue('E'.$i, $student['strProgramCode'])
                        ->setCellValue('F'.$i, $student['strProgramDescription'])
                        ->setCellValue('G'.$i, $student['intStudentYear'])
                        ->setCellValue('H'.$i, strtoupper($student['blockName']))
                        ->setCellValue('I'.$i, date("M j, Y", strtotime($student['dteBirthDate'])))
                        ->setCellValue('J'.$i, strtoupper($student['enumGender']))
                        ->setCellValue('K'.$i, $student['strAddress'])                    
                        ->setCellValue('L'.$i, strtoupper($student['strCitizenship']))
                        ->setCellValue('M'.$i, strtoupper($student['strAddress']))
                        ->setCellValue('N'.$i, strtoupper($student['strMobileNumber']))
                        ->setCellValue('O'.$i, $student['strEmail'])
                        ->setCellValue('P'.$i, strtoupper($student['father']))
                        ->setCellValue('Q'.$i, strtoupper($student['father_email']))
                        ->setCellValue('R'.$i, strtoupper($student['father_contact']))
                        ->setCellValue('S'.$i, strtoupper($student['mother']))
                        ->setCellValue('T'.$i, strtoupper($student['mother_email']))
                        ->setCellValue('U'.$i, strtoupper($student['mother_contact']))
                        ->setCellValue('V'.$i, strtoupper($student['guardian']))
                        ->setCellValue('W'.$i, strtoupper($student['guardian_email']))
                        ->setCellValue('X'.$i, strtoupper($student['guardian_contact']))
                        ->setCellValue('Y'.$i, strtoupper($student['high_school']))
                        ->setCellValue('Z'.$i, strtoupper($student['high_school_address']))
                        ->setCellValue('AA'.$i, strtoupper($student['high_school_attended']))
                        ->setCellValue('AB'.$i, strtoupper($student['senior_high']))
                        ->setCellValue('AC'.$i, strtoupper($student['senior_high_address']))
                        ->setCellValue('AD'.$i, strtoupper($student['senior_high_attended']))
                        ->setCellValue('AE'.$i, strtoupper($student['strand']))
                        ->setCellValue('AF'.$i, strtoupper($student['college']))
                        ->setCellValue('AG'.$i, strtoupper($student['college_address']))
                        ->setCellValue('AH'.$i, strtoupper($student['college_attended_from']))
                        ->setCellValue('AI'.$i, strtoupper($student['college_attended_to']))
                        ->setCellValue('AJ'.$i, strtoupper($student['dteRegistered']))
                        ->setCellValue('AK'.$i, strtoupper($student['curriculumName']))
                        ->setCellValue('AL'.$i, "")
                        ->setCellValue('AM'.$i, "")
                        ->setCellValue('AN'.$i, strtoupper($student['type_of_class']))
                        ->setCellValue('AO'.$i, "")
                        ->setCellValue('AP'.$i, strtoupper($student['student_type']));
                        
                
                
                $i++;
            }
            // $objPHPExcel->getActiveSheet()->getStyle('A2:I'.count($students))
            // ->getAlignment()->setWrapText(true);

            
            $objPHPExcel->getActiveSheet()->freezePane('E2');

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);        
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(30);

        }
        // Rename worksheet
        if($course!=0 && $year!=0)
            $objPHPExcel->getActiveSheet()->setTitle($student['strProgramCode'], "-", $student['intStudentYear']);
        else
            $objPHPExcel->getActiveSheet()->setTitle('Students');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        if($neo){
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
            header('Content-type: text/csv');
            header('Content-Disposition: attachment;filename="student_data'.$date.'.csv"');
        }
        else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
            header('Content-Disposition: attachment;filename="student_data'.$date.'.xls"');
        }
 
                 

         header('Cache-Control: max-age=0');
         // If you're serving to IE 9, then the following may be needed
         header('Cache-Control: max-age=1');
 
         // If you're serving to IE over SSL, then the following may be needed
         header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
         header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
         header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
         header ('Pragma: public'); // HTTP/1.0
 
         
         $objWriter->save('php://output');
         exit;
    }

    public function download_faculty()
    {
        
        $facultyLists = $this->data_fetcher->getFacultyList();
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Faculty List")
                                     ->setSubject("Faculty List Download")
                                     ->setDescription("Faculty List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Faculty List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Faculty ID')
                    ->setCellValue('B1', 'Username')
                    ->setCellValue('C1', 'Last Name')
                    ->setCellValue('D1', 'First Name')
                    ->setCellValue('E1', 'Middle Name')
                    ->setCellValue('F1', 'Email')
                    ->setCellValue('G1', 'Mobile Number')
                    ->setCellValue('H1', 'Adderss')
                    ->setCellValue('I1', 'Date Created')
                    ->setCellValue('J1', 'User Level')
                    ->setCellValue('K1', 'Password-unhashed')
                    ->setCellValue('L1', 'Password')
                    ->setCellValue('M1', 'School')
                    ->setCellValue('N1', 'Department');
        $i = 2;
        foreach($facultyLists as $facultyList)
        {
            // Add some datat
            //$oldPass_unhash = pw_unhash($facultyList['strPass']);
            //$newPass = password_hash($oldPass_unhash, PASSWORD_DEFAULT);

            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $facultyList['intID'])
                    ->setCellValue('B'.$i, $facultyList['strUsername'])
                    ->setCellValue('C'.$i, $facultyList['strLastname'])
                    ->setCellValue('D'.$i, $facultyList['strFirstname'])
                    ->setCellValue('E'.$i, $facultyList['strMiddlename'])
                    ->setCellValue('F'.$i, $facultyList['strEmail'])
                    ->setCellValue('G'.$i, $facultyList['strMobileNumber'])
                    ->setCellValue('H'.$i, $facultyList['strAddress'])
                    ->setCellValue('I'.$i, date("m-d-Y", strtotime($facultyList['dteCreated'])))
                    ->setCellValue('J'.$i, $facultyList['intUserLevel'])
                    ->setCellValue('K'.$i, pw_unhash($facultyList['strPass']))
                    ->setCellValue('L'.$i, password_hash(pw_unhash($facultyList['strPass']), PASSWORD_DEFAULT))
                    //->setCellValue('K'.$i, pw_hash(date("mdY",strtotime($student['dteBirthDate']))))
                    ->setCellValue('M'.$i, $facultyList['strSchool'])
                    ->setCellValue('N'.$i, $facultyList['strDepartment']); 
            
            
            $i++;
        }
        $objPHPExcel->getActiveSheet()->getStyle('I2:I'.count($facultyList))
        ->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Faculty');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // if($registered != 0)
        //     header('Content-Disposition: attachment;filename="registered_students'.$active_sem['enumSem'].'sem'.$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xlsx"');
        //else
            header('Content-Disposition: attachment;filename="faculty_list.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function free_he_billing_details($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0)
    {
        
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem);
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        if($sem!=0){
             $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Free HE Billing Details")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        $s_array = array();
        
        foreach($students as $student)
        {
            
            $student['registration'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$sem);
            $tuition = $this->data_fetcher->getTuition($student['intID'],$sem,$this->data['unit_fee'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$student['registration']['enumScholarship']);
            
            //$student['total'] = $tuition['athletic'] + $tuition['srf'] + $tuition['sfdf'] + $tuition['misc_fee']['Guidance Fee'] + $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'] + $tuition['lab'] + $tuition['misc_fee']['Medical and Dental Fee'] + $tuition['misc_fee']['Entrance Exam Fee'] + $tuition['misc_fee']['Registration'] + $tuition['id_fee'] + $tuition['misc_fee']['Library Fee'];
            
            $student['total'] = $tuition['athletic'] + $tuition['srf'] + $tuition['sfdf'] + $tuition['misc_fee']['Guidance Fee'] + $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'] + $tuition['lab'] + $tuition['misc_fee']['Medical and Dental Fee'] + $tuition['misc_fee']['Registration'] + $tuition['id_fee'] + $tuition['misc_fee']['Library Fee'];
            
            $exam_fee =$tuition['misc_fee']['Entrance Exam Fee'];
            $student['efee'] = $exam_fee;
            $student['tuition'] = $tuition;
            $student['has_nstp'] = true;
            
            $records = $this->data_fetcher->checkClasslistStudentNSTP($student['intID'],$sem);
            if(empty($records))
                $student['has_nstp'] = false;
            
            $s_array[] = $student;
        }                
        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', '5-digit Control Number')
                    ->setCellValue('B1', 'Student Number')
                    ->setCellValue('C1', 'Learner\'s Reference Number')
                    ->setCellValue('D1', 'Last Name')
                    ->setCellValue('E1', 'First Name')
                    ->setCellValue('F1', 'Middle Initial')
                    ->setCellValue('G1', 'Sex at Birth (M/F)')
                    ->setCellValue('H1', 'Degree/Program')
                    ->setCellValue('I1', 'Year Level')
                    ->setCellValue('J1', 'Scholarship')
                    ->setCellValue('K1', 'Birthdate')
                    ->setCellValue('L1', 'Zip Code')
                    ->setCellValue('M1', 'Email Address')
                    ->setCellValue('N1', 'Phone Number')
                    ->setCellValue('O1', 'Academic Units Enrolled (credit and non-credit courses)')
                    ->setCellValue('P1', 'Academic Units of NSTP Enrolled (credit and non-credit courses)')
                    ->setCellValue('Q1', '')
                    ->setCellValue('R1', 'Tuition Fee Based on enrolled academic units')
                    ->setCellValue('S1', 'Tuition Fee Based on enrolled NSTP units')
                    ->setCellValue('T1', '')
                    ->setCellValue('U1', 'Address');
        
        $i = 2;
        
        foreach($s_array as $student)
        {
            $registration = $student['registration'];
            $tuition = $student['tuition'];
            $total = $student['total'];
            $units = $tuition['tuition']/$this->data['unit_fee'];
            
            if($student['has_nstp']){
                $units -= 3;
                $nstp_units = 3;
                $nstp_fee = $this->data['unit_fee'] * 3;
                $tuition['tuition'] -= $nstp_fee; 
            }
            else{
                $nstp_units = 0;
                $nstp_fee = 0;
            }
            
            $middle_initial = isset($student['strMiddlename'][0])?strtoupper($student['strMiddlename'][0]).".":'';
            
            $sex = isset($student['enumGender'][0])?strtoupper($student['enumGender'][0]):'';
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, '')
                    ->setCellValue('B'.$i, $student['strStudentNumber'])
                    ->setCellValue('C'.$i, $student['strLRN'])
                    ->setCellValue('D'.$i, $student['strLastname'])
                    ->setCellValue('E'.$i, $student['strFirstname'])
                    ->setCellValue('F'.$i, $middle_initial)
                    ->setCellValue('G'.$i, $sex)
                    ->setCellValue('H'.$i, $student['strProgramCode'])
                    ->setCellValue('I'.$i, $student['intStudentYear'])
                    ->setCellValue('J'.$i, strtoupper($student['enumScholarship']))
                    ->setCellValue('K'.$i, date("m/d/Y",strtotime($student['dteBirthDate'])))
                    ->setCellValue('L'.$i, $student['strZipCode'])
                    ->setCellValue('M'.$i, $student['strEmail'])
                    ->setCellValue('N'.$i, $student['strMobileNumber'])
                    ->setCellValue('O'.$i, $units)
                    ->setCellValue('P'.$i, $nstp_units)
                    ->setCellValue('Q'.$i, '')
                    ->setCellValue('R'.$i, $tuition['tuition'])
                    ->setCellValue('S'.$i, $nstp_fee)
                    ->setCellValue('T'.$i, '')
                    ->setCellValue('U'.$i, $student['strAddress']);
                    
            
            
            $i++;
        }
       

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(35);
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Form 2');
        
        //SHEET 2
        $objPHPExcel->createSheet(1);
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(1)
                    ->setCellValue('A1', '5-digit Control Number')
                    ->setCellValue('B1', 'Student Number')
                    ->setCellValue('C1', 'Learner\'s Reference Number')
                    ->setCellValue('D1', 'Last Name')
                    ->setCellValue('E1', 'First Name')
                    ->setCellValue('F1', 'Middle Initial')
                    ->setCellValue('G1', 'Degree Program')
                    ->setCellValue('H1', 'Athletic Fees')
                    ->setCellValue('I1', 'Computer Fees')
                    ->setCellValue('J1', 'Cultural Fees')
                    ->setCellValue('K1', 'Development Fees')
                    ->setCellValue('L1', 'Guidance Fees')
                    ->setCellValue('M1', 'Handbook Publication Fees')
                    ->setCellValue('N1', 'Publication Fees')
                    ->setCellValue('O1', 'Laboratory Fees')
                    ->setCellValue('P1', 'Library Fee')
                    ->setCellValue('Q1', 'Medical and Dental Fees')
                    ->setCellValue('R1', 'Registration Fees')
                    ->setCellValue('S1', 'School ID Fees')
                    ->setCellValue('T1', 'TOTAL OSF (A)');
        
        $i = 2;
        foreach($s_array as $student)
        {
            $registration = $student['registration'];
            $tuition = $student['tuition'];
            $total = $student['total'];
                
            $middle_initial = isset($student['strMiddlename'][0])?strtoupper($student['strMiddlename'][0])."":'';
            $sex = isset($student['enumGender'][0])?strtoupper($student['enumGender'][0]):'';
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(1)
                    ->setCellValue('A'.$i, '')
                    ->setCellValue('B'.$i, $student['strStudentNumber'])
                    ->setCellValue('C'.$i, $student['strLRN'])
                    ->setCellValue('D'.$i, $student['strLastname'])
                    ->setCellValue('E'.$i, $student['strFirstname'])
                    ->setCellValue('F'.$i, $middle_initial)
                    ->setCellValue('G'.$i, $student['strProgramCode'])
                    ->setCellValue('H'.$i, $tuition['athletic'])
                    ->setCellValue('I'.$i, '')
                    ->setCellValue('J'.$i, $tuition['srf'])
                    ->setCellValue('K'.$i, $tuition['sfdf'])
                    ->setCellValue('L'.$i, $tuition['misc_fee']['Guidance Fee'])
                    ->setCellValue('M'.$i, $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'])
                    ->setCellValue('N'.$i, '')
                    ->setCellValue('O'.$i, $tuition['lab'])
                    ->setCellValue('P'.$i, $tuition['misc_fee']['Library Fee'])
                    ->setCellValue('Q'.$i, $tuition['misc_fee']['Medical and Dental Fee'])
                    ->setCellValue('R'.$i, $tuition['misc_fee']['Registration'])
                    ->setCellValue('S'.$i, $tuition['id_fee'])
                    ->setCellValue('T'.$i, $total);
                    
            
            
            $i++;
        }
       

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Form 2a');
        
        
        //SHEET 2
        $objPHPExcel->createSheet(2);
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(2)
                    ->setCellValue('A1', '5-digit Control Number')
                    ->setCellValue('B1', 'Student Number')
                    ->setCellValue('C1', 'Learner\'s Reference Number')
                    ->setCellValue('D1', 'Last Name')
                    ->setCellValue('E1', 'First Name')
                    ->setCellValue('F1', 'Middle Initial')
                    ->setCellValue('G1', 'Sex at Birth (M/F)')
                    ->setCellValue('H1', 'Birthdate')
                    ->setCellValue('I1', 'Degree/Program')
                    ->setCellValue('J1', 'Year Level')
                    ->setCellValue('K1', 'Zip Code')
                    ->setCellValue('L1', 'Email Address')
                    ->setCellValue('M1', 'Phone Number')
                    ->setCellValue('N1', 'Admission Fee')
                    ->setCellValue('O1', 'Entrance Fee')
                    ->setCellValue('P1', 'TOTAL OSF (B)');
        
        $i = 2;
        foreach($s_array as $student)
        {
            $registration = $student['registration'];
            $tuition = $student['tuition'];
            $total = $student['total'];
                
            $middle_initial = isset($student['strMiddlename'][0])?strtoupper($student['strMiddlename'][0]).".":'';
            $sex = isset($student['enumGender'][0])?strtoupper($student['enumGender'][0]):'';
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(2)
                    ->setCellValue('A'.$i, '')
                    ->setCellValue('B'.$i, $student['strStudentNumber'])
                    ->setCellValue('C'.$i, $student['strLRN'])
                    ->setCellValue('D'.$i, $student['strLastname'])
                    ->setCellValue('E'.$i, $student['strFirstname'])
                    ->setCellValue('F'.$i, $middle_initial)
                    ->setCellValue('G'.$i, $sex)
                    ->setCellValue('H'.$i, date("m/d/Y",strtotime($student['dteBirthDate'])))
                    ->setCellValue('I'.$i, $student['strProgramCode'])
                    ->setCellValue('J'.$i, $student['intStudentYear'])
                    ->setCellValue('K'.$i, $student['strZipCode'])
                    ->setCellValue('L'.$i, $student['strEmail'])
                    ->setCellValue('M'.$i, $student['strMobileNumber'])
                    ->setCellValue('N'.$i, '')
                    ->setCellValue('O'.$i, $student['efee'])
                    ->setCellValue('P'.$i, $student['efee']);
                    
            
            
            $i++;
        }
       

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Form 2b');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($registered != 0)
            header('Content-Disposition: attachment;filename="free-he-billing-details'.$active_sem['enumSem'].'sem'.$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xls"');
        else
            header('Content-Disposition: attachment;filename="student_list.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        
        $objWriter->save('php://output');
        exit;
    }
    
     public function download_cor_data($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0)
    {
        
       $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem);
       
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("COR Data Elements")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some data
        
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
            
        $objPHPExcel->setActiveSheetIndex(0)
            
                    ->setCellValue('A1', '5-digit Control Number')
                    ->setCellValue('B1', 'Student ID Number')
                    ->setCellValue('C1', 'Learner\'s Reference Number')
                    ->setCellValue('D1', 'Last Name')
                    ->setCellValue('E1', 'First Name')
                    ->setCellValue('F1', 'Middle Name')
                    ->setCellValue('G1', 'Degree Program / Course')
                    ->setCellValue('H1', 'Year Level')
                    ->setCellValue('I1', 'Subject Code')
                    ->setCellValue('J1', 'Subject Name')
                    ->setCellValue('K1', 'Number of Units')
                    ->setCellValue('L1', 'Subject Cost per unit')
                    ->setCellValue('M1', 'Tuition Cost per subject')
                    ->setCellValue('N1', 'Entrance Exam Fee')
                    ->setCellValue('O1', 'Medical Screening')
                    ->setCellValue('P1', 'Documentary Fee')
                    ->setCellValue('Q1', 'Personality/Psychological Test')
                    ->setCellValue('R1', 'Total Admission Fee')
                    ->setCellValue('S1', 'Use of Sports Facilities and Equipment')
                    ->setCellValue('T1', 'Participation')
                    ->setCellValue('U1', 'College and Universities')
                    ->setCellValue('V1', 'Total Athletic Fees')
                    ->setCellValue('W1', 'Access and Use of ICT Services')
                    ->setCellValue('X1', 'Computer Laboratory Fee')
                    ->setCellValue('Y1', 'Total Computer Fees')
                    ->setCellValue('Z1', 'Socio-cultural Activities')
                    ->setCellValue('AA1', 'Leadership Training')
                    ->setCellValue('AB1', 'Off-campus experiental Learning')
                    ->setCellValue('AC1', 'Students\' participation')
                    ->setCellValue('AD1', 'Student Publication/newsletter')
                    ->setCellValue('AE1', 'Life-long Learning Activities')
                    ->setCellValue('AF1', 'Spiritual, Social')
                    ->setCellValue('AG1', 'Bridging remedial programs')
                    ->setCellValue('AH1', 'Total Development Fees')
                    ->setCellValue('AI1', 'Entrance Fee')
                    ->setCellValue('AJ1', 'Student training and seminars')
                    ->setCellValue('AK1', 'Career guidance and counseling')
                    ->setCellValue('AL1', 'General student counseling')
                    ->setCellValue('AM1', 'Psychological Testing')
                    ->setCellValue('AN1', 'Career Assessment')
                    ->setCellValue('AO1', 'Career Development')
                    ->setCellValue('AP1', 'Employment Placement Services')
                    ->setCellValue('AQ1', 'Total Guidance Fee')
                    ->setCellValue('AR1', 'Handbook Fees')
                    ->setCellValue('AS1', 'Laboratory Fees')
                    ->setCellValue('AT1', 'Use of library services')
                    ->setCellValue('AU1', 'License Fee to cover')
                    ->setCellValue('AV1', 'Total Library Fees')
                    ->setCellValue('AW1', 'Mental Health')
                    ->setCellValue('AX1', 'Dental Health')
                    ->setCellValue('AY1', 'Student Insurance')
                    ->setCellValue('AZ1', 'Total Medical and Dental Fees')
                    ->setCellValue('BA1', 'Registration Fees')
                    ->setCellValue('BB1', 'School ID Fees')
                    ->setCellValue('BC1', 'Total Tuition')
                    ->setCellValue('BD1', 'Total Tuition and Other School Fees (TOSF 1)')
                    ->setCellValue('BE1', 'All Other School Fees (AOSF)')
                    ->setCellValue('BF1', 'Total Amount of Fees (TOSF 2 = (TOSF 1 + AOSF))');   
        $i = 2;

        foreach($students as $student)
        {
            
            $student['registration'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$sem);
            $tuition = $this->data_fetcher->getTuition($student['intID'],$sem,$this->data['unit_fee'],$this->data['misc_fee'],$this->data['lab_fee'],$this->data['athletic'],$this->data['id_fee'],$this->data['srf'],$this->data['sfdf'],$this->data['csg'],$student['registration']['enumScholarship']);
            
            //$student['total'] = $tuition['athletic'] + $tuition['srf'] + $tuition['sfdf'] + $tuition['misc_fee']['Guidance Fee'] + $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'] + $tuition['lab'] + $tuition['misc_fee']['Medical and Dental Fee'] + $tuition['misc_fee']['Entrance Exam Fee'] + $tuition['misc_fee']['Registration'] + $tuition['id_fee'] + $tuition['misc_fee']['Library Fee'];
            
            $student['total'] = $tuition['athletic'] + $tuition['srf'] + $tuition['sfdf'] + $tuition['misc_fee']['Guidance Fee'] + $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'] + $tuition['lab'] + $tuition['misc_fee']['Medical and Dental Fee'] + $tuition['misc_fee']['Registration'] + $tuition['id_fee'] + $tuition['misc_fee']['Library Fee'] + $tuition['misc_fee']['Entrance Exam Fee'];
            
            $exam_fee =$tuition['misc_fee']['Entrance Exam Fee'];
            
            $cl2 = $this->data_fetcher->getClassListStudentsSt($student['intID'],$sem);
            
            $middle_initial = isset($student['strMiddlename'][0])?strtoupper($student['strMiddlename'][0])."":'';
            // Add some datat
            $k = 0;
            foreach ($cl2 as $classlists) 
            {
                
            
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, '');
                
            if($k==0){    
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('B'.$i, $student['strStudentNumber'])
                    ->setCellValue('C'.$i, $student['strLRN'])
                    ->setCellValue('D'.$i, $student['strLastname'])
                    ->setCellValue('E'.$i, $student['strFirstname'])
                    ->setCellValue('F'.$i, $middle_initial)
                    ->setCellValue('G'.$i, $student['strProgramCode'])
                    ->setCellValue('H'.$i, $student['intStudentYear']);
            }
            else
            {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('B'.$i, '')
                    ->setCellValue('C'.$i, '')
                    ->setCellValue('D'.$i, '')
                    ->setCellValue('E'.$i, '')
                    ->setCellValue('F'.$i, '')
                    ->setCellValue('G'.$i, '')
                    ->setCellValue('H'.$i, '');
            }
                
                
//                    ->setCellValue('H'.$i, $tuition['athletic'])
//                    ->setCellValue('I'.$i, '')
//                    ->setCellValue('J'.$i, $tuition['srf'])
//                    ->setCellValue('K'.$i, $tuition['sfdf'])
//                    ->setCellValue('L'.$i, $tuition['misc_fee']['Guidance Fee'])
//                    ->setCellValue('M'.$i, $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'])
//                    ->setCellValue('N'.$i, $tuition['lab'])
//                    ->setCellValue('O'.$i, $tuition['misc_fee']['Library Fee'])
//                    ->setCellValue('P'.$i, $tuition['misc_fee']['Medical and Dental Fee'])
//                    ->setCellValue('Q'.$i, $tuition['misc_fee']['Registration'])
//                    ->setCellValue('R'.$i, $tuition['id_fee'])
//                    ->setCellValue('S'.$i, $total);
            $objPHPExcel->setActiveSheetIndex(0)
                    
                    ->setCellValue('I'.$i, $classlists['strCode'])
                    ->setCellValue('J'.$i, $classlists['strDescription'])
                    ->setCellValue('K'.$i, $classlists['strUnits'])
                    ->setCellValue('L'.$i, '175')
                    ->setCellValue('M'.$i, $classlists['strUnits'] * 175)
                    ->setCellValue('N'.$i, '')
                    ->setCellValue('O'.$i, '')
                    ->setCellValue('P'.$i, '')
                    ->setCellValue('Q'.$i, '');
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('R'.$i, $exam_fee);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('R'.$i, '');
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('S'.$i, '')
                    ->setCellValue('T'.$i, '')
                    ->setCellValue('U'.$i, '');
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('V'.$i, $tuition['athletic']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('V'.$i, '');
                
                $objPHPExcel->setActiveSheetIndex(0)                
                    ->setCellValue('W'.$i, '')
                    ->setCellValue('X'.$i, '');
            
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('Y'.$i, $tuition['lab']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('Y'.$i, '');
            
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('Z'.$i, $tuition['srf']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('Z'.$i, '');
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AA'.$i, '')
                    ->setCellValue('AB'.$i, '')
                    ->setCellValue('AC'.$i, '')
                    ->setCellValue('AD'.$i, '')
                    ->setCellValue('AE'.$i, '')
                    ->setCellValue('AF'.$i, '')
                    ->setCellValue('AG'.$i, '');
                
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0) 
                    ->setCellValue('AH'.$i, $tuition['sfdf']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AH'.$i, '');
                
                 $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AI'.$i, '')
                    ->setCellValue('AJ'.$i, '')
                    ->setCellValue('AK'.$i, '')
                    ->setCellValue('AL'.$i, '')
                    ->setCellValue('AM'.$i, '')
                    ->setCellValue('AN'.$i, '')
                    ->setCellValue('AO'.$i, '')
                    ->setCellValue('AP'.$i, '');
                     
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AQ'.$i, $tuition['misc_fee']['Guidance Fee']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AQ'.$i, '');
            
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AR'.$i, $tuition['csg']['student_handbook']+$tuition['csg']['student_publication']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AR'.$i, '');
                
                 $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AS'.$i, '')
                    ->setCellValue('AT'.$i, '')
                    ->setCellValue('AU'.$i, '');
                
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AV'.$i, $tuition['misc_fee']['Library Fee']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AV'.$i, '');
                
                 $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AW'.$i, '')
                    ->setCellValue('AX'.$i, '')
                    ->setCellValue('AY'.$i, '');
                
            if($k==0)
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AZ'.$i, $tuition['misc_fee']['Medical and Dental Fee'])
                    ->setCellValue('BA'.$i, $tuition['misc_fee']['Registration'])
                    ->setCellValue('BB'.$i, $tuition['id_fee'])
                    ->setCellValue('BC'.$i, $tuition['tuition'])
                    ->setCellValue('BD'.$i, $tuition['tuition']+$student['total'])
                    ->setCellValue('BE'.$i, '')
                    ->setCellValue('BF'.$i, $tuition['tuition']+$student['total']);
            else
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('AZ'.$i, '')
                    ->setCellValue('BA'.$i, '')
                    ->setCellValue('BB'.$i, '')
                    ->setCellValue('BC'.$i, '')
                    ->setCellValue('BD'.$i, '')
                    ->setCellValue('BE'.$i, '')
                    ->setCellValue('BF'.$i, '');
                
                $i++;
                $k++;
            }
            
            
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        
        
        
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

         // Rename worksheet
       
            $objPHPExcel->getActiveSheet()->setTitle('COR Data Elements');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($registered != 0)
            header('Content-Disposition: attachment;filename="COR_Data_Elements'.$active_sem['enumSem'].'sem'.$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xlsx"');
        else
            header('Content-Disposition: attachment;filename="COR_Data_Elements.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    
    public function download_students_with_grades($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0)
    {
        
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem);
       //$student['intStudentYear'])
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', switch_num($year)." Year ".$active_sem['enumSem']." Sem ".$active_sem['strYearStart']." - ".$active_sem['strYearEnd']);
        
            
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'Student Number')
                    ->setCellValue('B2', 'Last Name')
                    ->setCellValue('C2', 'First Name')
                    ->setCellValue('D2', 'Middle Name')
                    ->setCellValue('E2', 'Gender')
                    ->setCellValue('F2', 'Year Level')
                    ->setCellValue('G2', 'Program')
                    ->setCellValue('H2', 'GSuite Email');
                    
        
        $i = 3;
        
        foreach($students as $student)
        {
            $cl = $this->data_fetcher->getClassListStudentsSt($student['intID'],$sem);
            
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $student['strStudentNumber'])
                    ->setCellValue('B'.$i, $student['strLastname'])
                    ->setCellValue('C'.$i, $student['strFirstname'])
                    ->setCellValue('D'.$i, $student['strMiddlename'])
                    ->setCellValue('E'.$i, $student['enumGender'])
                    ->setCellValue('F'.$i, $student['intStudentYear'])
                    ->setCellValue('G'.$i, $student['strProgramCode'])
                    ->setCellValue('H'.$i, $student['strGSuiteEmail']);
                    
            $col = 'I';
            foreach($cl as $c)
            {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col."2", "Course Code");
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col.$i, $c['strCode']);
                
                $col++;
                
                 $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col."2", "Course Title");
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col.$i, $c['strDescription']);
                
                $col++;
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col.$i, $c['strUnits']);
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col."2", "Units");
                
                $col++;

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col."2", "FacultyID");
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col.$i, $c['facID']);
                
                $col++;
            }
            
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        
        
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

         // Rename worksheet
        if($course!=0 && $year!=0)
            $objPHPExcel->getActiveSheet()->setTitle($student['strProgramCode'], "-", $student['intStudentYear']);
        else
            $objPHPExcel->getActiveSheet()->setTitle('Students');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($registered != 0)
            header('Content-Disposition: attachment;filename="enrolment-list-'.$active_sem['enumSem'].'sem'."-".$active_sem['strYearStart']."-".$active_sem['strYearEnd'].'.xls"');
        else
            header('Content-Disposition: attachment;filename="student_list.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;
    }

    public function enlisted_students($course,$year,$gender,$sem,$start,$end){
        
        if($sem == 0)      
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        $this->data['sy'] = $active_sem;
        $students = $this->data_fetcher->getStudentsEnlistedOnly(0,$active_sem['intID'],$course,$year,$gender,$start,$end);
        
        foreach($students as $student){
            $student['reg_info'] = $this->data_fetcher->getRegistrationInfo($student['intID'],$active_sem['intID']);
            $st[] = $student;
        }
        $student_data = $st;

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Enlisted Students Report")
                                     ->setSubject("Enlisted Students Download")
                                     ->setDescription("Enlisted Students Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Enlisted Students Report");

        
      
        
            
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', '#')
                    ->setCellValue('B1', 'Student No.')
                    ->setCellValue('C1', 'Student Name')
                    ->setCellValue('D1', 'Course')
                    ->setCellValue('E1', 'Enrollment Status')
                    ->setCellValue('F1', 'Date Enlisted')
                    ->setCellValue('G1', 'Enlisted By');
                            
        $i = 2;
        $ctr = 1;
        foreach($student_data as $st){
            $name = $st['strLastname'].", ".$st['strFirstname']; 
            $name .= isset($st['strMiddlename'])?", ".$st['strMiddlename']:'';                
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $ctr)
                    ->setCellValue('B'.$i, preg_replace("/[^a-zA-Z0-9]+/", "", $st['strStudentNumber']))
                    ->setCellValue('C'.$i, strtoupper($name))
                    ->setCellValue('D'.$i, $st['strProgramCode'])
                    ->setCellValue('E'.$i, $st['reg_info']['type_of_class']."-".$st['reg_info']['enumStudentType'])
                    ->setCellValue('F'.$i, $st['date_added'])
                    ->setCellValue('G'.$i, $st['fusername']);
        
            $i++;
            $ctr++;
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                
         
        $objPHPExcel->getActiveSheet()->setTitle('Enlisted Students');

        $date = date("ymdhis");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="enlisted_students'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;
        
    }

    public function enrollment_summary($sem){
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        $type = $active_sem['term_student_type'];
        $programs = $this->db->get_where('tb_mas_programs',array('type'=>$type))->result_array();
        $data['programs'] = $programs;
        $enrollment = [];        

        if($sem == 0)      
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        foreach($programs as $program){
            $st = [];
            $program['enrolled_transferee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,2));
            $program['enrolled_freshman'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,1));
            $program['enrolled_foreign'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,3));
            $program['enrolled_second'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,4));
            $program['enrolled_continuing'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,5));
            $program['enrolled_shiftee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,6));
            $program['enrolled_returnee'] = count($this->data_fetcher->getStudents($program['intProgramID'],0,0,0,0,0,2,$sem,7));
             
            $enrollment[] = $program; 
        }


        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Enrollment Summary Report")
                                     ->setSubject("Enrollment Summary Report Download")
                                     ->setDescription("Enrollment Summary Report Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Enrollment Summary Report");

        
      
        $title = 'Enrollment Summary for '.$active_sem['enumSem'].' Term SY'.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'];
        
        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A1', $title);

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:G1');

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A3', 'Program')
                    ->setCellValue('B3', 'Freshman')
                    ->setCellValue('C3', 'Transferee')
                    ->setCellValue('D3', 'Foreign')
                    ->setCellValue('E3', 'Second Degree')
                    ->setCellValue('F3', 'Continuing')
                    ->setCellValue('G3', 'Shiftee')
                    ->setCellValue('H3', 'Returning')
                    ->setCellValue('I3', 'Total');
                            
        $i = 4;

        $all_enrolled = 0;
        
        foreach($enrollment as $item){
            $major = ($item['strMajor'] != "None" && $item['strMajor'] != "")?'Major in '.$item['strMajor']:''; 
            $all_enrolled +=  $item['enrolled_freshman'] + $item['enrolled_transferee'] + $item['enrolled_foreign'] + $item['enrolled_second'] + $item['enrolled_continuing'] + $item['enrolled_shiftee'];
                    
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, trim($item['strProgramDescription']))
                    ->setCellValue('B'.$i, $item['enrolled_freshman'])
                    ->setCellValue('C'.$i, $item['enrolled_transferee'])
                    ->setCellValue('D'.$i, $item['enrolled_foreign'])
                    ->setCellValue('E'.$i, $item['enrolled_second'])
                    ->setCellValue('F'.$i, $item['enrolled_continuing'])
                    ->setCellValue('G'.$i, $item['enrolled_shiftee'])
                    ->setCellValue('H'.$i, $item['enrolled_returnee'])
                    ->setCellValue('I'.$i, '=SUM(B'.$i.':H'.$i.')');                                                
                             
        
            $i++;
         
        }

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('H'.$i, "TOTAL")
                    ->setCellValue('I'.$i, '=SUM(I4:I'.($i-1).')');                    
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('I'.$i)->getFont()->setBold( true );                    
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:I3')->getFont()->setBold( true );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);        
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                
         
        $objPHPExcel->getActiveSheet()->setTitle('Enrollment Summary');

        $date = date("ymdhis");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="enrollment_summary'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;


    }

    function daily_enrollment_report($sem){
        $post = $this->input->post();
        $dates = json_decode($post['dates']);
        $totals = json_decode($post['totals']);
        $full_total = json_decode($post['full_total']);
        
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Total Daily Enrollment Summary Report")
                                     ->setSubject("Total Daily Enrollment Summary Report Download")
                                     ->setDescription("Total Daily Enrollment Summary Report Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Total Daily Enrollment Summary Report");

        
      
        $title = 'Total Daily Enrollment for '.$active_sem['enumSem'].' Term SY'.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'];
        
        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A1', $title);

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:G1');

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A3', 'Program')
                    ->setCellValue('B3', 'Freshman')
                    ->setCellValue('C3', 'Transferee')
                    ->setCellValue('D3', 'Second Degree')                    
                    ->setCellValue('E3', 'Continuing')
                    ->setCellValue('F3', 'Shiftee')
                    ->setCellValue('G3', 'Returning')
                    ->setCellValue('H3', 'Total Enrollment');
                            
        $i = 4;
        
        
        foreach($dates as $item){  
            
                    
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $item->date)
                    ->setCellValue('B'.$i, $item->freshman)
                    ->setCellValue('C'.$i, $item->transferee)                    
                    ->setCellValue('D'.$i, $item->second)
                    ->setCellValue('E'.$i, $item->continuing)
                    ->setCellValue('F'.$i, $item->shiftee)
                    ->setCellValue('G'.$i, $item->returning)
                    ->setCellValue('H'.$i, '=SUM(B'.$i.':G'.$i.')');                                                
                             
        
            $i++;
         
        }

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('B'.$i, '=SUM(B4:B'.($i-1).')')
                    ->setCellValue('C'.$i, '=SUM(C4:C'.($i-1).')')                    
                    ->setCellValue('D'.$i, '=SUM(D4:D'.($i-1).')')
                    ->setCellValue('E'.$i, '=SUM(E4:E'.($i-1).')')                    
                    ->setCellValue('F'.$i, '=SUM(F4:F'.($i-1).')')
                    ->setCellValue('G'.$i, '=SUM(G4:G'.($i-1).')')
                    ->setCellValue('H'.$i, '=SUM(H4:H'.($i-1).')');
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('H'.$i)->getFont()->setBold( true );                    
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:H3')->getFont()->setBold( true );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        
                
         
        $objPHPExcel->getActiveSheet()->setTitle('Total Daily Enrollment Students');

        $date = date("ymdhis");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="daily_enrollment'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;
            
    }

    public function reservation_summary($sem){
        
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

        $reserved = $data;
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);


        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Reservation Summary Report")
                                     ->setSubject("Reservation Summary Report Download")
                                     ->setDescription("Reservation Summary Report Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Reservation Summary Report");

        
      
        $title = 'Reservation Summary for '.$active_sem['enumSem'].' Term SY'.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'];
        
        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A1', $title);

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:F1');

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A3', 'Program')
                    ->setCellValue('B3', 'Freshman')
                    ->setCellValue('C3', 'Transferee')
                    ->setCellValue('D3', 'Foreign')
                    ->setCellValue('E3', 'Second Degree')
                    ->setCellValue('F3', 'Total');
                            
        $i = 4;

        $all_enrolled = 0;
        
        foreach($reserved['reserved'] as $item){  
            
                    
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, trim($item[0]->program));

            foreach($item as $type){      
                if($type->student_type == "freshman"){                                    
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('B'.$i, $type->reserved_count);                                                               
                }
                if(!$reserved['r_fresh'][$item[0]->type_id]){
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('B'.$i, 0);                                                               
                }
                if($type->student_type == "transferee"){            
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('C'.$i, $type->reserved_count);                                                                             
                }
                if(!$reserved['r_trans'][$item[0]->type_id]){
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('C'.$i, 0); 
                }
                if($type->student_type == "foreign"){            
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('D'.$i, $type->reserved_count);                                                                       
                }
                if(!$reserved['r_foreign'][$item[0]->type_id]){
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('D'.$i, 0); 
                }
                if($type->student_type == "second degree"){            
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('E'.$i, $type->reserved_count);                                                                             
                }
                if(!$reserved['r_sd'][$item[0]->type_id]){
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('E'.$i, 0); 
                }
                
                $objPHPExcel->setActiveSheetIndex(0)                                        
                    ->setCellValue('F'.$i, '=SUM(B'.$i.':E'.$i.')');                                                
            
            }
                                               
        
            $i++;
         
        }

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('E'.$i, "TOTAL")
                    ->setCellValue('F'.$i, '=SUM(F4:F'.($i-1).')');                    
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('F'.$i)->getFont()->setBold( true );                    
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:F3')->getFont()->setBold( true );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        
                
         
        $objPHPExcel->getActiveSheet()->setTitle('Reservation Summary');

        $date = date("ymdhis");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="reservation_summary'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;


    }

    public function export_leads()
    {
        
        $data = $this->input->post();
        $date = date("Y-m-d");
        $data = json_decode($data['data']);
        
        
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Export Leads")
                                     ->setSubject("Export Leads Download")
                                     ->setDescription("Export Leads Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Export Leads");

        
      
        
            
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Last Name')
                    ->setCellValue('B1', 'First Name')
                    ->setCellValue('C1', 'Middle Name')
                    ->setCellValue('D1', 'Mobile Number')
                    ->setCellValue('E1', 'Address')
                    ->setCellValue('F1', '1st Choice')
                    ->setCellValue('G1', '2nd Choice')
                    ->setCellValue('H1', '3rd Choice')
                    ->setCellValue('I1', 'Email')
                    ->setCellValue('J1', 'Status')
                    ->setCellValue('K1', 'School')
                    ->setCellValue('L1', 'Father')
                    ->setCellValue('M1', 'Contact #')
                    ->setCellValue('N1', 'Email')
                    ->setCellValue('O1', 'Mother')
                    ->setCellValue('P1', 'Contact #')
                    ->setCellValue('Q1', 'Email')                    
                    ->setCellValue('R1', 'Guardian')
                    ->setCellValue('S1', 'Contact #')
                    ->setCellValue('T1', 'Email')
                    ->setCellValue('U1', 'Date')
                    ->setCellValue('V1', 'Source')
                    ->setCellValue('W1', 'Company(SD)')
                    ->setCellValue('X1', 'Position(SD)')
                    ->setCellValue('Y1', 'Degree(SD)')
                    ->setCellValue('Z1', 'Student Type')
                    ->setCellValue('AA1', 'Country of Citizenship')
                    ->setCellValue('AB1', 'Date of Birth')
                    ->setCellValue('AC1', 'Date Enrolled')
                    ->setCellValue('AD1', 'Student Number');
                    
                    
        
        $i = 2;
        
        foreach($data as $d){      
            $student = $this->db->get_where('tb_mas_users',array('slug'=> $d->slug))->first_row('array');                 
            $studnum = isset($student)?preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']):'';
            
            
            $d->mobile_number = str_replace('(+63)', '0', $d->mobile_number);
            $d->mobile_number = str_replace('-', '', $d->mobile_number);
            $d->mobile_number = str_replace(' ', '', $d->mobile_number);

            $d->father_contact = str_replace('(+63)', '0', $d->father_contact);
            $d->father_contact = str_replace('-', '', $d->father_contact);
            $d->father_contact = str_replace(' ', '', $d->father_contact);


            $d->mother_contact = str_replace('(+63)', '0', $d->mother_contact);
            $d->mother_contact = str_replace('-', '', $d->mother_contact);
            $d->mother_contact = str_replace(' ', '', $d->mother_contact);


            $d->guardian_contact = str_replace('(+63)', '0', $d->guardian_contact);
            $d->guardian_contact = str_replace('-', '', $d->guardian_contact);
            $d->guardian_contact = str_replace(' ', '', $d->guardian_contact);

            $objPHPExcel->setActiveSheetIndex(0)                                
                    ->setCellValue('A'.$i, strtoupper($d->last_name))
                    ->setCellValue('B'.$i, strtoupper($d->first_name))
                    ->setCellValue('C'.$i, strtoupper($d->middle_name))
                    ->setCellValue('D'.$i, $d->mobile_number)
                    ->setCellValue('E'.$i, $d->address)
                    ->setCellValue('F'.$i, $d->program)
                    ->setCellValue('G'.$i, $d->program2)
                    ->setCellValue('H'.$i, $d->program3)                    
                    ->setCellValue('I'.$i, $d->email)
                    ->setCellValue('J'.$i, $d->status)
                    ->setCellValue('K'.$i, $d->school)
                    ->setCellValue('L'.$i, $d->father_name)
                    ->setCellValue('M'.$i, $d->father_contact)
                    ->setCellValue('N'.$i, $d->father_email)
                    ->setCellValue('O'.$i, $d->mother_name)
                    ->setCellValue('P'.$i, $d->mother_contact)
                    ->setCellValue('Q'.$i, $d->mother_email)
                    ->setCellValue('R'.$i, $d->guardian_name)
                    ->setCellValue('S'.$i, $d->guardian_contact)
                    ->setCellValue('T'.$i, $d->guardian_email)                    
                    ->setCellValue('U'.$i, $d->datestamp)
                    ->setCellValue('V'.$i, $d->source)
                    ->setCellValue('W'.$i, $d->sd_company)
                    ->setCellValue('X'.$i, $d->sd_position)
                    ->setCellValue('Y'.$i, $d->sd_degree)
                    ->setCellValue('Z'.$i, $d->tos)
                    ->setCellValue('AA'.$i, $d->citizenship)
                    ->setCellValue('AB'.$i, $d->date_of_birth)
                    ->setCellValue('AC'.$i, $d->date_enrolled)
                    ->setCellValue('AD'.$i, $studnum);
                    ;
                    
                    
                    
                                                       
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(50);
        
                
         
        $objPHPExcel->getActiveSheet()->setTitle('Leads');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="export_leads'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;

    }
    public function daily_collection_report()
    {
        

        $data = $this->input->post();
        $date = $data['date'];
        $data = json_decode($data['data']);
        
        
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Daily Collection Report")
                                     ->setSubject("Daily Collection Report Download")
                                     ->setDescription("Daily Collection Report Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Daily Collection Report");

        
      
        
            
        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A1', 'Date')
                    ->setCellValue('B1', 'OR Number')
                    ->setCellValue('C1', 'Applicant Number')
                    ->setCellValue('D1', 'Name')
                    ->setCellValue('E1', 'Payment Particulars')
                    ->setCellValue('F1', 'Term/Sem')                                                            
                    ->setCellValue('G1', 'Remarks')
                    ->setCellValue('H1', 'Cash')
                    ->setCellValue('I1', 'Check')
                    ->setCellValue('J1', 'Credit')
                    ->setCellValue('K1', 'Debit')
                    ->setCellValue('L1', 'Online')
                    ->setCellValue('M1', 'Total');
                    
        
        $i = 2;
        
        foreach($data as $d){             
            $or_number = str_pad($d->or_number, 5, '0', STR_PAD_LEFT);           
            // Add some datat
            $cashier = $this->data_fetcher->fetch_single_entry('tb_mas_faculty',$d->cashier_id);
            if($cashier)
                $cashier_name = $cashier['strLastname']." ".$cashier['strFirstname']." ".$cashier['strMiddlename'];
            else
                $cashier_name = "N/A";

            $mode = "";
            
            $term = $this->data_fetcher->get_sem_by_id($d->sy_reference);
            
            switch($d->is_cash){
                case 0:                    
                    $objPHPExcel->setActiveSheetIndex(0)       
                        ->setCellValue('I'.$i, $d->subtotal_order);
                    break;
                case 1:
                    $objPHPExcel->setActiveSheetIndex(0)       
                        ->setCellValue('H'.$i, $d->subtotal_order);
                    break;
                case 2:
                    $objPHPExcel->setActiveSheetIndex(0)       
                        ->setCellValue('J'.$i, $d->subtotal_order);
                    break;
                case 3:
                    $objPHPExcel->setActiveSheetIndex(0)       
                        ->setCellValue('K'.$i, $d->subtotal_order);
                    break;  
                case 4:     
                    $objPHPExcel->setActiveSheetIndex(0)       
                        ->setCellValue('L'.$i, $d->subtotal_order);
                    break;                   

            }
            $remarks = $d->remarks == "Paynamics" ? $d->request_id : $d->remarks;

            $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A'.$i, $d->or_date)
                    ->setCellValue('B'.$i, $or_number)
                    ->setCellValue('C'.$i, $d->student_number)
                    ->setCellValue('D'.$i, strtoupper($d->student_name))
                    ->setCellValue('E'.$i, $d->description)
                    ->setCellValue('F'.$i, $term['enumSem']." ".$term['term_label']." SY".$term['strYearStart']."-".$term['strYearEnd'])                                        
                    ->setCellValue('G'.$i, $remarks)
                    ->setCellValue('M'.$i, '=SUM(H'.$i.':L'.$i.')');
                                                       
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)       
            ->setCellValue('H'.$i, '=SUM(H2:H'.($i-1).')');
        $objPHPExcel->setActiveSheetIndex(0)       
            ->setCellValue('I'.$i, '=SUM(I2:I'.($i-1).')');
        $objPHPExcel->setActiveSheetIndex(0)       
            ->setCellValue('J'.$i, '=SUM(J2:J'.($i-1).')');
        $objPHPExcel->setActiveSheetIndex(0)       
            ->setCellValue('K'.$i, '=SUM(K2:K'.($i-1).')');
        $objPHPExcel->setActiveSheetIndex(0)       
            ->setCellValue('L'.$i, '=SUM(L2:L'.($i-1).')');
        $objPHPExcel->setActiveSheetIndex(0)       
            ->setCellValue('M'.$i, '=SUM(M2:M'.($i-1).')');

        $objPHPExcel->setActiveSheetIndex(0)->getStyle("H".$i.":M".$i)->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("M2:M".($i-1))->getFont()->setBold( true );



        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
                
         
        $objPHPExcel->getActiveSheet()->setTitle('Collection');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="daily_collection_'.$date.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter->save('php://output');
        exit;
    }
    
    
    
    public function download_transactions($start=null,$end=null)
    {
        $trans = $this->data_fetcher->fetch_transactions($start,$end);
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Transactions Table")
                                     ->setSubject("Transactions Table Download")
                                     ->setDescription("Transactions Table Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Transactions Table");

        
        
        
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'transactions '.$start."-".$end);
        
        
         // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'ORNumber')
                    ->setCellValue('B1', 'Transaction Type')
                    ->setCellValue('C1', 'Date Paid')
                    ->setCellValue('D1', 'Payee')
                    ->setCellValue('E1', 'Amount Paid');
        
        $i = 2;
        foreach($trans as $tran)
        {
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $tran['intORNumber'])
                    ->setCellValue('B'.$i, $tran['strTransactionType'])
                    ->setCellValue('C'.$i, date("M j,Y",strtotime($tran['dtePaid'])))
                    ->setCellValue('D'.$i, $tran['strLastname'].", ".$tran['strFirstname'])
                    ->setCellValue('E'.$i, "P".$tran['intAmountPaid']);
                    
            $i++;
        }
        
        
          // Rename worksheet
        
        $objPHPExcel->getActiveSheet()->setTitle('Transactions');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
       
        header('Content-Disposition: attachment;filename="transactions.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    
    
    }
    
    function upload_classlist()
    {
       
        $post = $this->input->post();
    
        $config['upload_path'] = './assets/excel';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size']	= '4096';
        $config['file_name'] = 'temp';

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload("excelupload"))
        {
         $this->session->set_flashdata('message',$this->upload->display_errors());
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $inputFileName = $file['full_path'];

            //  Read your Excel workbook
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch(Exception $e) {
                die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
            }

            //  Get worksheet dimensions
            $sheet = $objPHPExcel->getSheet(0); 
            $highestRow = $sheet->getHighestRow(); 
            $highestColumn = $sheet->getHighestColumn();
            
            
             $this->data_poster->deleteFromClassList($post['intClasslistID']);
            //  Loop through each row of the worksheet in turn
            for ($row = 3; $row <= $highestRow; $row++){ 
                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                                                NULL,
                                                TRUE,
                                                FALSE);
                
               
                //  Insert row data array into your database of choice here
                foreach($rowData as $d)
                {
                    if($d[0] == "" || $d[1] == "" || $d[2] == "" || $d[4] == "" || $d[5] == "")
                        break;
                    
                    $student = $this->data_fetcher->getStudentStudentNumber($d[3]);
                    
                    if(empty($student))
                    {
                        $student = $this->data_fetcher->getStudentByName($d[0],$d[1],$d[2]);   
                    }
                    
                    if(!empty($student)){
                        $data_s['intStudentID'] = $student['intID'];
                        $data_s['intClasslistID'] = $post['intClasslistID'];
                        $data_s['strUnits'] = $post['strUnits'];
                        $data_s['floatFinalGrade'] = $d[4];
                        $data_s['strRemarks'] = $d[5];
                        $this->data_poster->post_data('tb_mas_classlist_student',$data_s);
                    }
                }
                   
            }
            unlink($inputFileName);
            
            
        }
        redirect(base_url().'unity/classlist_viewer/'.$post['intClasslistID']);
    }
    public function download_applicants($course = 0,$appdate=0,$gender = 0,$sem = 0)
    {
        
        $applicants = $this->data_fetcher->getApplicantsExcel($course,$appdate,$gender,$sem);
        
//        $applicants['courseCode1'] = $this->data_fetcher->getCourseCode($applicants['enumCourse1']);
//        $applicants['courseCode2'] = $this->data_fetcher->getCourseCode($applicants['enumCourse2']);
//        $course3 = $this->data_fetcher->getCourseCode($applicants['enumCourse3']);
//        $applicants['courseCode3'] = ($course3=="")?'None':$course3;

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        if($sem!=0){
             $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        }
        else
        {
            $active_sem = $this->data_fetcher->get_active_sem();

        }
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Jec Castillo")
                                     ->setLastModifiedBy("Jec Castillo")
                                     ->setTitle("Student List")
                                     ->setSubject("Student List Download")
                                     ->setDescription("Student List Download.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Student List");

        
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Applicant Number')
                    ->setCellValue('B1', 'Last Name')
                    ->setCellValue('C1', 'First Name')
                    ->setCellValue('D1', 'Middle Name')
                    ->setCellValue('E1', 'LRN')
                    ->setCellValue('F1', 'Email Address')
                    ->setCellValue('G1', 'Phone Number')
                    ->setCellValue('H1', '1st Choice Course')
                    ->setCellValue('I1', '2nd Choice Course')
                    ->setCellValue('J1', '3rd Choice Course')
                    ->setCellValue('K1', 'Province')
                    ->setCellValue('L1', 'City/Municipality')
                    ->setCellValue('M1', 'Barangay')
                    ->setCellValue('N1', 'Home Address')
                    ->setCellValue('O1', 'Last School Attended')
                    ->setCellValue('P1', 'Birthdate')
                    ->setCellValue('Q1', 'Gender')
                    ->setCellValue('R1', 'Civil Status')
                    ->setCellValue('S1', 'Father\'s Name')
                    ->setCellValue('T1', 'Mother\'s Name')
                    ->setCellValue('U1', 'Spouse')
                    ->setCellValue('V1', 'Religion')
                    ->setCellValue('W1', 'Application Date/Time')
                    ->setCellValue('X1', 'Date of Exam');
            
            
                    
        $i = 2;
        foreach($applicants as $applicant)
        {
            // Add some datat
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $applicant['strAppNumber'])
                    ->setCellValue('B'.$i, strtoupper($applicant['strLastname']))
                    ->setCellValue('C'.$i, strtoupper($applicant['strFirstname']))
                    ->setCellValue('D'.$i, strtoupper($applicant['strMiddlename']))
                    ->setCellValue('E'.$i, $applicant['strAppLRN'])
                    ->setCellValue('F'.$i, $applicant['strAppEmail'])
                    ->setCellValue('G'.$i, $applicant['strAppPhoneNumber'])
                    ->setCellValue('H'.$i, $applicant['enumCourse1'])
                    ->setCellValue('I'.$i, $applicant['enumCourse2'])
                    ->setCellValue('J'.$i, $applicant['enumCourse3'])
//                    ->setCellValue('H'.$i, $applicant['courseCode1'])
//                    ->setCellValue('I'.$i, $applicant['courseCode2'])
//                    ->setCellValue('J'.$i, $applicant['courseCode3'])
                    ->setCellValue('K'.$i, ucwords(strtolower($applicant['provDesc'])))
                    ->setCellValue('L'.$i, ucwords(strtolower($applicant['citymunDesc'])))
                    ->setCellValue('M'.$i, ucwords(strtolower($applicant['brgyDesc'])))
                    ->setCellValue('N'.$i, $applicant['strAppAdress'])
                    ->setCellValue('O'.$i, $applicant['strAppLastSchool'])
                    ->setCellValue('P'.$i, date("m-d-Y", strtotime($applicant['dteAppBirthdate'])))
                    ->setCellValue('Q'.$i, $applicant['strAppGender'])
                    ->setCellValue('R'.$i, $applicant['strAppCivilStatus'])
                    ->setCellValue('S'.$i, $applicant['strAppFather'])
                    ->setCellValue('T'.$i, $applicant['strAppMother'])  
                    ->setCellValue('U'.$i, $applicant['strAppSpouse'])
                    ->setCellValue('V'.$i, $applicant['strAppReligion'])
                    ->setCellValue('W'.$i, $applicant['strAppDate'])
                    ->setCellValue('X'.$i, $applicant['dteScheduleExam']);
            
            
            $i++;
        }
        $objPHPExcel->getActiveSheet()->getStyle('I2:I'.count($applicant))
        ->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(15);
        
        // Miscellaneous glyphs, UTF-8
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

        // Rename worksheet
//        if($course!=0 && $year!=0)
//            $objPHPExcel->getActiveSheet()->setTitle(applicant['strProgramCode'], "-", $student['intStudentYear']);
        //else
        $objPHPExcel->getActiveSheet()->setTitle('Applicants');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //if($registered != 0)
            header('Content-Disposition: attachment;filename="list_of_applicants_'.$active_sem['enumSem'].'sem'.$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xlsx"');
        //else
           // header('Content-Disposition: attachment;filename="applicants.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function student_account_report($sem, $campus, $report_date)
    {
        $post = $this->input->post();
        $ar_students = json_decode($post['ar_students']);

        $enrolledSlugs = $notEnrolledSlugs = array();

        // $ar_students = "<script> </script>"
        // $ch = curl_init();
        // // Step 2: Set cURL options
        // // Specify the URL to fetch
        // // $url = $this->data['api_url'] . 'admissions/student-info/view-students/' . $sem;
        // $url = 'https://smsapi.iacademy.edu.ph/sms/admissions/student-info/view-students/' . $sem;

        // curl_setopt($ch, CURLOPT_URL, $url); // Set the URL to fetch
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string

        // // Step 3: Execute the cURL session
        // $response = curl_exec($ch);
        // $data = array();
        // // Step 4: Check for errors
        // if (curl_errno($ch)) {
        //     print 'cURL error: ' . curl_error($ch);
        // } else {
        //     // Decode the response if it's JSON
        //     $data = json_decode($response, true);
        // }
        
        // foreach($data['data'] as $studentInformation){
        //     array_push($enrolledSlugs, $studentInformation['slug']);
        // }
        // curl_close($ch);

        foreach($ar_students as $studentInformation){
            if($studentInformation->status != 'Enrolled')
                array_push($notEnrolledSlugs, $studentInformation->slug);
            // array_push($enrolledSlugs, $studentInformation->slug);
        }
        print_r($ar_students);
        die();

        $users = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->where_not_in('slug', $notEnrolledSlugs)
                    ->order_by('strStudentNumber', 'ASC')
                    ->get()
                    ->result_array();

        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
      
        $title = 'AR Report';

        $i = 4;
        $count = 1;
        $payments = $students = $date_enrolled_array = array();

        foreach($users as $index => $user){
            
            // if(isset($data['data']['status'])){
            //     if($data['data']['status'] == 'Enrolled'){
                    $payment_details = $this->db->select('payment_details.*')
                            ->from('payment_details')
                            ->join('tb_mas_users', 'tb_mas_users.slug = payment_details.student_number')
                            ->join('tb_mas_registration', 'tb_mas_registration.intStudentID = tb_mas_users.intID')
                            ->where(array('payment_details.sy_reference' => $sem, 'payment_details.student_campus' => $campus, 'payment_details.student_number' => $user['slug'], 'payment_details.status' => 'Paid', 'payment_details.updated_at <=' => $report_date . ' 23:59:59'))
                            ->order_by('payment_details.created_at', 'asc')
                            ->group_by('payment_details.id')
                            ->get()
                            ->result_array();
        
                    $payment_month = $payment_year = '';
                    $current_index = 0;
                    if($payment_details){
                        $payment = $user_payment = $date = $student_payment = array();
                        foreach($payment_details as $payment_index => $payment_detail){
                            if(strpos($payment_detail['description'], 'Tuition') !== false || strpos($payment_detail['description'], 'Reservation') !== false){
                                //set date enrolled based on full or installment payment
                                if(!isset($date_enrolled_array[$payment_detail['student_number']]) && strpos($payment_detail['description'], 'Tuition') !== false){
                                    $date_enrolled_array[$payment_detail['student_number']] = $payment_detail['created_at'];
                                }
                                if($payments == null){
                                    $payment['date'] = date("M d", strtotime($payment_detail['created_at']));
                                    $payment['or_number'] = $payment_detail['or_number'];
                                    $payment['amount'] = (float)number_format($payment_detail['subtotal_order'], 2, '.', '');
                                    
                                    $payment_month = date("m", strtotime($payment_detail['created_at']));
                                    $payment_year = date("Y", strtotime($payment_detail['created_at']));
                                    
                                    $user_payment[$user['intID']] = $payment;
            
                                    $date['month'] = $payment_month;
                                    $date['month_name'] = date("F", strtotime($payment_detail['created_at']));
                                    $date['year'] = $payment_year;
                                    $date['data'] = $user_payment;
            
                                    $payments[] = $date;
                                }else{
                                    if(isset($date['data'][$user['intID']]) && $payment_month == date("m", strtotime($payment_detail['created_at'])) && $payment_year == date("Y", strtotime($payment_detail['created_at']))){
                                        $payments[$current_index]['data'][$user['intID']]['date'] .= ', ' . date("d", strtotime($payment_detail['created_at']));
                                        $payments[$current_index]['data'][$user['intID']]['or_number'] .= ', ' . $payment_detail['or_number'];
                                        $payments[$current_index]['data'][$user['intID']]['amount'] += (float)number_format($payment_detail['subtotal_order'], 2, '.', '');
                                    }else{
                                        $flag = $same_month_year = false;
                                        $data = $date = array();
                                        for($index = count($payments) - 1; $index >= 0; $index--){
                                            if($payments[$index]['year'] == date("Y", strtotime($payment_detail['created_at'])) && $payments[$index]['month'] == date("m", strtotime($payment_detail['created_at']))){
                                                
                                            
                                                $same_month_year = true;
                                                $current_index = $index;
                                            }else if($payments[$index]['year'] == date("Y", strtotime($payment_detail['created_at']))){
                                                if($payments[$index]['month'] > date("m", strtotime($payment_detail['created_at']))){
                                                    $current_index = $index;
                                                    $flag = true;
                                                }
                                            }else if($payments[$index]['year'] > date("Y", strtotime($payment_detail['created_at']))){
                                                $current_index = $index;
                                                $flag = true;
                                            }
                                        }
        
                                        $payment['date'] = date("M d", strtotime($payment_detail['created_at']));
                                        $payment['or_number'] = $payment_detail['or_number'];
                                        $payment['amount'] = (float)number_format($payment_detail['subtotal_order'], 2, '.', '');
                                        
                                        $payment_month = date("m", strtotime($payment_detail['created_at']));
                                        $payment_year = date("Y", strtotime($payment_detail['created_at']));
                                        $user_payment[$user['intID']] = $payment;
                
                                        $date['month'] = $payment_month;
                                        $date['month_name'] = date("F", strtotime($payment_detail['created_at']));
                                        $date['year'] = $payment_year;
                                        $date['data'] = $user_payment;
                                        $data[] = $date;
                                        
                                        if($same_month_year){
                                            $payments[$current_index]['data'][$user['intID']] = $payment;
                                        }else{
                                            if($flag){
                                                array_splice($payments, $current_index, 0, $data);
                                            }
                                            else{
                                                $current_index = count($payments);
                                                array_splice($payments, count($payments), 0, $data);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
            //     }
            // }
        }

        $studentsEnrolled = false;

        foreach($users as $index => $user)
        {
            $applied_from = $applied_to = $other = array();
            // $paymentExist = false;
            // foreach($payments as $index_payment => $payment){
            //     if(isset($date_enrolled_array[$user['slug']])){
            //         $paymentExist = true;
            //         break;
            //     }
            // }

            $reg = $this->db->select('tb_mas_registration.*, tb_mas_scholarships.name as scholarshipName')
                    ->from('tb_mas_registration')
                    ->where(array('intStudentID'=>$user['intID'],'intAYID'=>$sem, 'date_enlisted !=' => NULL))
                    // ->or_where(array('intStudentID'=>$user['intID'],'intAYID'=>$sem, 'dteRegistered !=' => NULL))
                    ->join('tb_mas_scholarships', 'tb_mas_scholarships.intID = tb_mas_registration.enumScholarship', 'left')
                    ->get()
                    ->first_row('array');
            $tuition = $this->data_fetcher->getTuition($user['intID'], $sem);

            if($reg && substr($user['strStudentNumber'], 0, 1) != 'T'){

                $ledger_data = $this->db->get_where('tb_mas_student_ledger', array('syid' => $sem, 'student_id' => $user['intID'], 'date <=' => $report_date . ' 23:59:59'))->result_array();

                if($ledger_data){
                    foreach($ledger_data as $ledger){
                        
                        if($ledger['type'] == 'other'){
                            if(!$other){
                                $other[0] = date("M d,Y",strtotime($ledger['date']));
                                $other[1] = $ledger['name'];
                                $other[2] = $ledger['amount'];
                            }else{
                                $other[0] = ', ' . date("M d,Y",strtotime($ledger['date']));
                                $other[1] = ', ' . $ledger['name'];
                                $other[2] += $ledger['amount'];
                            }
                        }else if(strpos($ledger['remarks'], 'APPLIED FROM') !== false){
                            if(!$applied_from){
                                $applied_from[0] = date("M d,Y",strtotime($ledger['date']));
                                $applied_from[1] = $ledger['remarks'];
                                $applied_from[2] = $ledger['amount'] > 0 ? $ledger['amount'] : -1 * $ledger['amount'];
                            }else{
                                $applied_from[0] .= ', ' . date("M d,Y",strtotime($ledger['date']));
                                $applied_from[1] .= ', ' . $ledger['remarks'];
                                $applied_from[2] += $ledger['amount'] > 0 ? $ledger['amount'] : -1 * $ledger['amount'];
                            }
                        }else if(strpos($ledger['remarks'], 'APPLIED TO') !== false){
                            if(!$applied_from){
                                $applied_to[0] = date("M d,Y",strtotime($ledger['date']));
                                $applied_to[1] = $ledger['remarks'];
                                $applied_to[2] = $ledger['amount'] < 0 ? $ledger['amount'] : -1 * abs($ledger['amount']);
                            }else{
                                $applied_to[0] = date("M d,Y",strtotime($ledger['date']));
                                $applied_to[1] = $ledger['remarks'];
                                $applied_to[2] = $ledger['amount'] < 0 ? $ledger['amount'] : -1 * abs($ledger['amount']);
                            }
                        }
                    }
                }

                $studentsEnrolled = true;
                $course = $this->data_fetcher->getProgramDetails($user['intProgramID']);          
                $assessment_discount_rate = $assessment_discount_fixed = $tuition_discount_rate = 0;
                // if($tuition){
                    if($reg['paymentType'] == 'full'){
                        if($tuition['scholarship_total_assessment_rate'] > 0){
                            $assessment_discount_rate = $tuition['scholarship_total_assessment_rate'];
                        }
                        if($tuition['scholarship_total_assessment_fixed'] > 0){
                            $assessment_discount_fixed = $tuition['scholarship_total_assessment_fixed'];
                        }
                        if($tuition['scholarship_tuition_fee_rate'] > 0){
                            $tuition_discount_rate = $tuition['scholarship_tuition_fee_rate'];
                        }
                    }else{ 
                        if($tuition['scholarship_total_assessment_rate_installment'] > 0){
                            $assessment_discount_rate = $tuition['scholarship_total_assessment_rate_installment'];
                        }
                        if($tuition['scholarship_total_assessment_fixed_installment'] > 0){
                            $assessment_discount_fixed = $tuition['scholarship_total_assessment_fixed_installment'];
                        }
                        if($tuition['scholarship_tuition_fee_installment_rate'] > 0){
                            $tuition_discount_rate = $tuition['scholarship_tuition_fee_installment_rate'];
                        }
                    }
                // }else{
                //     print_r($user);
                //     die();
                // }

                $date_enrolled = date("Y-m-d",strtotime($reg['date_enlisted']));
                if(isset($date_enrolled_array[$user['slug']])){
                    $date_enrolled = date("M d,Y",strtotime($date_enrolled_array[$user['slug']]));
                }
                $tuition_discount = $total_discount = 0;

                if($date_enrolled < $sy->ar_report_date_generation){
                    if($reg['paymentType'] == 'full' && $tuition['scholarship_tuition_fee_rate'] > 0)
                    $tuition_discount = $tuition['scholarship_tuition_fee_rate'];
                    if($reg['paymentType'] == 'partial' && $tuition['scholarship_tuition_fee_installment_rate'] > 0)
                    $tuition_discount = $tuition['scholarship_tuition_fee_installment_rate'];
                }else{
                    $total_discount = $tuition_discount_rate + $tuition['scholarship_tuition_fee_fixed'] + $tuition['scholarship_lab_fee_rate'] + $tuition['scholarship_lab_fee_fixed'] + $tuition['scholarship_misc_fee_rate'] + 
                                        $tuition['scholarship_misc_fee_fixed'] + $tuition['nsf'] + $tuition['scholarship_misc_fee_fixed'] + $assessment_discount_rate + $assessment_discount_fixed;
                }
                
                $tuition_payments = $this->db->select('payment_details.*')
                    ->from('payment_details')
                    ->join('tb_mas_users', 'tb_mas_users.slug = payment_details.student_number')
                    ->join('tb_mas_registration', 'tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('payment_details.sy_reference' => $sem, 'payment_details.student_campus' => $campus, 'payment_details.student_number' => $user['slug'], 'payment_details.status' => 'Paid', 'payment_details.updated_at <=' => $report_date . ' 23:59:59'))
                    ->order_by('payment_details.created_at', 'asc')
                    ->group_by('payment_details.id')
                    ->get()
                    ->result_array();

                // Add some data
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $count)
                    // ->setCellValue('B'.$i, str_replace(str_split('T-'), "",$user['strStudentNumber']))
                    ->setCellValue('B'.$i, str_replace("-", "",$user['strStudentNumber']))
                    ->setCellValue('C'.$i, strtoupper($user['strLastname']) . ', ' . strtoupper($user['strFirstname']) . ' ' . strtoupper($user['strMiddlename']))
                    ->setCellValue('D'.$i, isset($date_enrolled_array[$user['slug']]) ? date("M d,Y",strtotime($date_enrolled_array[$user['slug']])) : date("M d, Y",strtotime($reg['date_enlisted'])))
                    // ->setCellValue('D'.$i, date("M d,Y",strtotime($reg['date_enlisted'])))
                    ->setCellValue('E'.$i, $reg['paymentType'] == 'full' ? 'FULL PAYMENT' : 'INSTALLMENT')
                    ->setCellValue('F'.$i, $course['strProgramCode'])
                    ->setCellValue('G'.$i, $reg['paymentType'] == 'full' && $tuition['tuition_before_discount'] > 0 ? (float)$tuition['tuition_before_discount'] : '')
                    ->setCellValue('H'.$i, $reg['paymentType'] == 'full' && $tuition['lab_before_discount'] > 0 ? (float)$tuition['lab_before_discount'] : '')
                    ->setCellValue('I'.$i, $reg['paymentType'] == 'full' && $tuition['misc_before_discount'] > 0 ? (float)$tuition['misc_before_discount'] : '')
                    ->setCellValue('J'.$i, $reg['paymentType'] == 'full' && $tuition['thesis_fee'] > 0 ? (float)$tuition['thesis_fee'] : '')
                    ->setCellValue('K'.$i, $reg['paymentType'] == 'full' && $tuition['new_student'] > 0 ? (float)$tuition['new_student'] : '')
                    ->setCellValue('L'.$i, $reg['paymentType'] == 'full' && $tuition['late_enrollment_fee'] > 0 ? (float)$tuition['late_enrollment_fee'] : '')
                    ->setCellValue('M'.$i, '=SUM(G' . $i . ':L' . $i . ')')

                    ->setCellValue('N'.$i, $reg['paymentType'] == 'partial' && $tuition['tuition_installment_before_discount'] > 0 ? (float)$tuition['tuition_installment_before_discount'] : '')
                    ->setCellValue('O'.$i, $reg['paymentType'] == 'partial' && $tuition['lab_installment_before_discount'] > 0 ? (float)$tuition['lab_installment_before_discount'] : '')
                    ->setCellValue('P'.$i, $reg['paymentType'] == 'partial' && $tuition['misc_before_discount'] > 0 ? (float)$tuition['misc_before_discount'] : '')
                    ->setCellValue('Q'.$i, $reg['paymentType'] == 'partial' && $tuition['thesis_fee'] > 0 ? (float)$tuition['thesis_fee'] : '')
                    ->setCellValue('R'.$i, $reg['paymentType'] == 'partial' && $tuition['new_student'] > 0 ? (float)$tuition['new_student'] : '')
                    ->setCellValue('S'.$i, $reg['paymentType'] == 'partial' && $tuition['late_enrollment_fee'] > 0 ? (float)$tuition['late_enrollment_fee'] : '')
                    ->setCellValue('T'.$i, '=SUM(N' . $i . ':S' . $i . ')')
                    ->setCellValue('U'.$i, '=M' . $i . '+T' . $i . ')')
                    ->setCellValue('V'.$i, $date_enrolled < $sy->ar_report_date_generation ? $tuition['scholar_type'] : '')
                    ->setCellValue('W'.$i, $tuition_discount )
                    ->setCellValue('X'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['scholarship_tuition_fee_fixed'] > 0 ? $tuition['scholarship_tuition_fee_fixed'] : '')
                    ->setCellValue('Y'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['scholarship_lab_fee_rate'] > 0 ? $tuition['scholarship_lab_fee_rate'] : '')
                    ->setCellValue('Z'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['scholarship_lab_fee_fixed'] > 0 ? $tuition['scholarship_lab_fee_fixed'] : '')
                    ->setCellValue('AA'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['scholarship_misc_fee_rate'] > 0 ? $tuition['scholarship_misc_fee_rate'] : '')
                    ->setCellValue('AB'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['scholarship_misc_fee_fixed'] > 0 ? $tuition['scholarship_misc_fee_fixed'] : '')
                    ->setCellValue('AC'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['nsf'] > 0 ? $tuition['nsf'] : '')
                    ->setCellValue('AD'.$i, $date_enrolled < $sy->ar_report_date_generation && $tuition['nsf'] > 0 ? $tuition['nsf'] : '')
                    ->setCellValue('AE'.$i, $date_enrolled < $sy->ar_report_date_generation && $assessment_discount_rate > 0 ? $assessment_discount_rate : '')
                    ->setCellValue('AF'.$i, $date_enrolled < $sy->ar_report_date_generation && $assessment_discount_fixed > 0 ? $assessment_discount_fixed : '')
                    ->setCellValue('AG'.$i, '=SUM(W' . $i . ':AF' . $i . ')')
                    ->setCellValue('AH'.$i, '=U' . $i . '-AG' . $i . ')');

                $total_amount = '=' . $this->columnIndexToLetter(36) . '' . $i;
                $total_payment = 0;

                if(count($payments) > 0){
                    foreach($payments as $index_payment => $payment){
                        $total_payment += isset($payment['data'][$user['intID']]) ? $payment['data'][$user['intID']]['amount'] : 0;

                        $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow(34 + ($index_payment * 3), 1)
                            ->setValue($payment['month_name'] . ' ' . $payment['year']);
    
                        $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow(34 + ($index_payment * 3), $i)
                            ->setValue(isset($payment['data'][$user['intID']]) ? $payment['data'][$user['intID']]['date'] . ', ' . $payment['year'] : '');
                        
                        $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow(35 + ($index_payment * 3), $i)
                            ->setValue(isset($payment['data'][$user['intID']]) ? $payment['data'][$user['intID']]['or_number'] : '');
                        
                        $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow(36 + ($index_payment * 3), $i)
                            ->setValue(isset($payment['data'][$user['intID']]) ? $payment['data'][$user['intID']]['amount'] : '');  
                            
                        $column_letter = $this->columnIndexToLetter(36 + ($index_payment * 3));
                        
                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue($this->columnIndexToLetter(34 + ($index_payment * 3)) . '2', 'DATE')
                            ->setCellValue($this->columnIndexToLetter(35 + ($index_payment * 3)) . '2', 'OR NUMBER')
                            ->setCellValue($this->columnIndexToLetter(36 + ($index_payment * 3)) . '2', 'AMOUNT');
                        
                        $objPHPExcel->getActiveSheet()->getStyle($column_letter . '4:' . $column_letter . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                        
                        if($index_payment > 0){
                            $total_amount .= '+' . $column_letter . '' . $i;
                        }

                        $sheet = $objPHPExcel->getActiveSheet();
                        $sheet->mergeCells($this->columnIndexToLetter(34 + ($index_payment * 3)) . '1:' . $this->columnIndexToLetter(36 + ($index_payment * 3)) . '1');
                        $sheet->mergeCells($this->columnIndexToLetter(34 + ($index_payment * 3)) . '2:' . $this->columnIndexToLetter(34 + ($index_payment * 3)) . '3');
                        $sheet->mergeCells($this->columnIndexToLetter(35 + ($index_payment * 3)) . '2:' . $this->columnIndexToLetter(35 + ($index_payment * 3)) . '3');
                        $sheet->mergeCells($this->columnIndexToLetter(36 + ($index_payment * 3)) . '2:' . $this->columnIndexToLetter(36 + ($index_payment * 3)) . '3');
    
                        $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter(34 + ($index_payment * 3)) . '4:' . $this->columnIndexToLetter(35 + ($index_payment * 3)) . '' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter(34 + ($index_payment * 3)))->setWidth(20);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter(35 + ($index_payment * 3)))->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter(36 + ($index_payment * 3)))->setWidth(15);
                    }
                }else{
                    $column_letter = $this->columnIndexToLetter(36);
                        
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($this->columnIndexToLetter(34) . '2', 'DATE')
                        ->setCellValue($this->columnIndexToLetter(35) . '2', 'OR NUMBER')
                        ->setCellValue($this->columnIndexToLetter(36) . '2', 'AMOUNT');
                    
                    $objPHPExcel->getActiveSheet()->getStyle($column_letter . '4:' . $column_letter . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    
                    $sheet = $objPHPExcel->getActiveSheet();
                    $sheet->mergeCells($this->columnIndexToLetter(34) . '1:' . $this->columnIndexToLetter(36) . '1');
                    $sheet->mergeCells($this->columnIndexToLetter(34) . '2:' . $this->columnIndexToLetter(34) . '3');
                    $sheet->mergeCells($this->columnIndexToLetter(35) . '2:' . $this->columnIndexToLetter(35) . '3');
                    $sheet->mergeCells($this->columnIndexToLetter(36) . '2:' . $this->columnIndexToLetter(36) . '3');
                    $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter(34) . '4:' . $this->columnIndexToLetter(35) . '' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    
                    $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter(34))->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter(35))->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter(36))->setWidth(15);
                }

                $last_index = 37;
                if(count($payments) > 0){
                    $last_index = 34 + (count($payments) * 3);
                }

                $balance_after_payment = '=AH' . $i . '-' . $this->columnIndexToLetter($last_index) . '' . $i;
                $total_adjustment = '=' . $this->columnIndexToLetter($last_index + 4) . '' . $i . '+' . $this->columnIndexToLetter($last_index + 7) . '' . $i . '+' . $this->columnIndexToLetter($last_index + 10) . '' . $i . 
                                    '+' . $this->columnIndexToLetter($last_index + 13) . '' . $i . '+' . $this->columnIndexToLetter($last_index + 16) . '' . $i;
                                    
                $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index, $i)->setValue($total_amount);
                $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 1, $i)->setValue($balance_after_payment);
                
                //applied to
                if($applied_from){
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 2, $i)->setValue($applied_from[0]);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 3, $i)->setValue($applied_from[1]);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 4, $i)->setValue($applied_from[2]);
                }
                //applied from
                if($applied_to){
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 5, $i)->setValue($applied_to[0]);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 6, $i)->setValue($applied_to[1]);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 7, $i)->setValue($applied_to[2]);
                }

                //late tagging
                if($date_enrolled >= $sy->reconf_start){
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 8, $i)->setValue($total_discount > 0 ? $date_enrolled : '');
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 9, $i)->setValue($total_discount > 0 ? $tuition['scholar_type'] : '');
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 10, $i)->setValue($total_discount > 0 ? $total_discount : '');
                }

                // if($date_enrolled >= $sy->reconf_start){
                //     $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 14, $i)->setValue($total_discount > 0 ? $date_enrolled : '');
                //     $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 15, $i)->setValue($total_discount > 0 ? $tuition['scholar_type'] : '');
                //     $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 16, $i)->setValue($total_discount > 0 ? $total_discount : '');
                // }

                $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 17, $i)->setValue($total_adjustment);
                $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 18, $i)->setValue('=' . $this->columnIndexToLetter($last_index + 1) . '' . $i . '-' . $this->columnIndexToLetter($last_index + 17) . '' . $i);

                $installment_balance = 0;
                if($reg['paymentType'] == 'partial'){
                    $installment_balance = $tuition['tuition_installment_before_discount'] + $tuition['lab_installment_before_discount'] + $tuition['misc_before_discount'] + $tuition['thesis_fee'] + $tuition['new_student'] + $tuition['late_enrollment_fee'];
                    
                    if($date_enrolled < $sy->ar_report_date_generation){
                        $installment_balance -= $tuition_discount;
                        $installment_balance -= $tuition['scholarship_tuition_fee_fixed'] > 0 ? $tuition['scholarship_tuition_fee_fixed'] : 0;
                        $installment_balance -= $tuition['scholarship_lab_fee_rate'] > 0 ? $tuition['scholarship_lab_fee_rate'] : 0;
                        $installment_balance -= $tuition['scholarship_lab_fee_fixed'] > 0 ? $tuition['scholarship_lab_fee_fixed'] : 0;
                        $installment_balance -= $tuition['scholarship_misc_fee_rate'] > 0 ? $tuition['scholarship_misc_fee_rate'] : 0;
                        $installment_balance -= $tuition['scholarship_misc_fee_rate'] > 0 ? $tuition['scholarship_misc_fee_rate'] : 0;
                        $installment_balance -= $tuition['nsf'] > 0 ? $tuition['nsf'] : 0;
                        $installment_balance -= $assessment_discount_rate > 0 ? $assessment_discount_rate : 0;
                        $installment_balance -= $assessment_discount_fixed > 0 ? $assessment_discount_fixed : 0;
                        $installment_balance -= $applied_from ? $applied_from[2] : 0;
                        $installment_balance -= $applied_to ? $applied_to[2] : 0;
                    }else{
                        $installment_balance -= $applied_from ? $applied_from[2] : 0;
                        $installment_balance -= $applied_to ? $applied_to[2] : 0;
                        $installment_balance -= $total_discount;
                    }

                    $installment_balance -= $total_payment;
                    
                    //Aging
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 20, $i)->setValue($installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 5) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 5) > $installment_balance && ($tuition['installment_fee'] * 5) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee'] * 4) : 0) : 0);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 21, $i)->setValue($installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 4) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 4) > $installment_balance && ($tuition['installment_fee'] * 4) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee'] * 3) : 0) : 0);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 22, $i)->setValue($installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 3) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 3) > $installment_balance && ($tuition['installment_fee'] * 3) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee'] * 2) : 0) : 0);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 23, $i)->setValue($installment_balance > 0 ? $installment_balance - ($tuition['installment_fee'] * 2) >= 0 ? $tuition['installment_fee'] : (($tuition['installment_fee'] * 2) > $installment_balance && ($tuition['installment_fee'] * 2) - $installment_balance < $tuition['installment_fee'] ? $installment_balance - ($tuition['installment_fee']) : 0) : 0);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 24, $i)->setValue($installment_balance > 0 ? $installment_balance - $tuition['installment_fee'] >= 0 ? $tuition['installment_fee'] : $installment_balance : 0);
                    $objPHPExcel->setActiveSheetIndex(0)->getCellByColumnAndRow($last_index + 25, $i)->setValue('=SUM(' . $this->columnIndexToLetter($last_index + 20) . '' . $i . ':' . $this->columnIndexToLetter($last_index + 24) . '' . $i . ')');
                }

                $i++;
                $count++;
            }
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'NO.')
                    ->setCellValue('B1', 'STUDENT NUMBER')
                    ->setCellValue('C1', 'STUDENT NAME')
                    ->setCellValue('D1', 'DATE ENROLLED')
                    ->setCellValue('E1', 'MOP')
                    ->setCellValue('F1', 'COURSE')
                    ->setCellValue('G1', 'FULL PAYMENT')
                    ->setCellValue('G2', 'TF')
                    ->setCellValue('H2', 'LABORATORY')
                    ->setCellValue('I2', 'MISC FEES')
                    ->setCellValue('J2', 'THESIS FEE')
                    ->setCellValue('K2', 'NSF')
                    ->setCellValue('L2', 'LEF')
                    ->setCellValue('M2', 'TOTAL FP')
                    ->setCellValue('N1', 'INSTALLMENT')
                    ->setCellValue('N2', 'TF')
                    ->setCellValue('O2', 'LABORATORY')
                    ->setCellValue('P2', 'MISC FEES')
                    ->setCellValue('Q2', 'THESIS FEE')
                    ->setCellValue('R2', 'NSF')
                    ->setCellValue('S2', 'LEF')
                    ->setCellValue('T2', 'TOTAL INSTALLMENT')
                    ->setCellValue('U1', 'TUITION FEE GRAND TOTAL')
                    ->setCellValue('V1', 'SCHOLARSHIPS/ DISCOUNTS RATE')
                    ->setCellValue('V2', 'TYPE')
                    ->setCellValue('W2', 'TUITION')
                    ->setCellValue('Y2', 'LAB')
                    ->setCellValue('AA2', 'MISC')
                    ->setCellValue('AC2', 'NSF')
                    ->setCellValue('AE2', 'REFERRAL DISCOUNT')
                    ->setCellValue('W3', 'RATE')
                    ->setCellValue('X3', 'FIX')
                    ->setCellValue('Y3', 'RATE')
                    ->setCellValue('Z3', 'FIX')
                    ->setCellValue('AA3', 'RATE')
                    ->setCellValue('AB3', 'FIX')
                    ->setCellValue('AC3', 'RATE')
                    ->setCellValue('AD3', 'FIX')
                    ->setCellValue('AE3', 'RATE')
                    ->setCellValue('AF3', 'FIX')
                    ->setCellValue('AG1', 'TOTAL SCHOLARSHIP / DISCOUNT')
                    ->setCellValue('AH1', 'AR TERM & YEAR')
                    ->setCellValue($this->columnIndexToLetter($last_index) . '1', 'TOTAL PAYMENT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 1) . '1', 'BALANCE AFTER PAYMENT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 2) . '1', 'ADJUSTMENTS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 2) . '2', 'APPLIED FROM')
                    ->setCellValue($this->columnIndexToLetter($last_index + 5) . '2', 'APPLIED TO')
                    ->setCellValue($this->columnIndexToLetter($last_index + 8) . '2', 'LATE TAGGING')
                    ->setCellValue($this->columnIndexToLetter($last_index + 11) . '2', 'REFUND')
                    ->setCellValue($this->columnIndexToLetter($last_index + 14) . '2', 'OTHERS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 2) . '3', 'DATE')
                    ->setCellValue($this->columnIndexToLetter($last_index + 3) . '3', 'REMARKS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 4) . '3', 'AMOUNT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 5) . '3', 'DATE')
                    ->setCellValue($this->columnIndexToLetter($last_index + 6) . '3', 'REMARKS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 7) . '3', 'AMOUNT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 8) . '3', 'DATE')
                    ->setCellValue($this->columnIndexToLetter($last_index + 9) . '3', 'REMARKS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 10) . '3', 'AMOUNT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 11) . '3', 'DATE')
                    ->setCellValue($this->columnIndexToLetter($last_index + 12) . '3', 'REMARKS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 13) . '3', 'AMOUNT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 14) . '3', 'DATE')
                    ->setCellValue($this->columnIndexToLetter($last_index + 15) . '3', 'REMARKS')
                    ->setCellValue($this->columnIndexToLetter($last_index + 16) . '3', 'AMOUNT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 17) . '1', 'TOTAL ADJUSTMENT')
                    ->setCellValue($this->columnIndexToLetter($last_index + 18) . '1', 'BALANCE AS OF (' . date("M d, Y", strtotime($report_date)) . ')')
                    ->setCellValue($this->columnIndexToLetter($last_index + 20) . '1', '1ST')
                    ->setCellValue($this->columnIndexToLetter($last_index + 21) . '1', '2ND')
                    ->setCellValue($this->columnIndexToLetter($last_index + 22) . '1', '3RD')
                    ->setCellValue($this->columnIndexToLetter($last_index + 23) . '1', '4TH')
                    ->setCellValue($this->columnIndexToLetter($last_index + 24) . '1', '5TH')
                    ->setCellValue($this->columnIndexToLetter($last_index + 20) . '3', date("d-M-Y", strtotime($sy->installment1)))
                    ->setCellValue($this->columnIndexToLetter($last_index + 21) . '3', date("d-M-Y", strtotime($sy->installment2)))
                    ->setCellValue($this->columnIndexToLetter($last_index + 22) . '3', date("d-M-Y", strtotime($sy->installment3)))
                    ->setCellValue($this->columnIndexToLetter($last_index + 23) . '3', date("d-M-Y", strtotime($sy->installment4)))
                    ->setCellValue($this->columnIndexToLetter($last_index + 24) . '3', date("d-M-Y", strtotime($sy->installment5)))
                    ->setCellValue($this->columnIndexToLetter($last_index + 25) . '1', 'ENDING BALANCE AS OF (' . date("M d, Y", strtotime($report_date)) . ')');

        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $this->columnIndexToLetter($last_index + 18) .  '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B4:F' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        if($studentsEnrolled){
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$i, 'TOTAL')
                        ->setCellValue('G'.$i, '=SUM(G4:G' . ($i-1) . ')')
                        ->setCellValue('H'.$i, '=SUM(H4:H' . ($i-1) . ')')
                        ->setCellValue('I'.$i, '=SUM(I4:I' . ($i-1) . ')')
                        ->setCellValue('J'.$i, '=SUM(J4:J' . ($i-1) . ')')
                        ->setCellValue('K'.$i, '=SUM(K4:K' . ($i-1) . ')')
                        ->setCellValue('L'.$i, '=SUM(L4:L' . ($i-1) . ')')
                        ->setCellValue('M'.$i, '=SUM(M4:M' . ($i-1) . ')')
                        ->setCellValue('N'.$i, '=SUM(N4:N' . ($i-1) . ')')
                        ->setCellValue('O'.$i, '=SUM(O4:O' . ($i-1) . ')')
                        ->setCellValue('P'.$i, '=SUM(P4:P' . ($i-1) . ')')
                        ->setCellValue('Q'.$i, '=SUM(Q4:Q' . ($i-1) . ')')
                        ->setCellValue('R'.$i, '=SUM(R4:R' . ($i-1) . ')')
                        ->setCellValue('S'.$i, '=SUM(S4:S' . ($i-1) . ')')
                        ->setCellValue('T'.$i, '=SUM(T4:T' . ($i-1) . ')')
                        ->setCellValue('U'.$i, '=SUM(U4:U' . ($i-1) . ')')
                        ->setCellValue('W'.$i, '=SUM(W4:W' . ($i-1) . ')')
                        ->setCellValue('X'.$i, '=SUM(X4:X' . ($i-1) . ')')
                        ->setCellValue('Y'.$i, '=SUM(Y4:Y' . ($i-1) . ')')
                        ->setCellValue('Z'.$i, '=SUM(Z4:Z' . ($i-1) . ')')
                        ->setCellValue('AA'.$i, '=SUM(AA4:AA' . ($i-1) . ')')
                        ->setCellValue('AB'.$i, '=SUM(AB4:AB' . ($i-1) . ')')
                        ->setCellValue('AC'.$i, '=SUM(AC4:AC' . ($i-1) . ')')
                        ->setCellValue('AD'.$i, '=SUM(AD4:AD' . ($i-1) . ')')
                        ->setCellValue('AE'.$i, '=SUM(AE4:AE' . ($i-1) . ')')
                        ->setCellValue('AF'.$i, '=SUM(AF4:AF' . ($i-1) . ')')
                        ->setCellValue('AG'.$i, '=SUM(AG4:AG' . ($i-1) . ')')
                        ->setCellValue('AH'.$i, '=SUM(AH4:AH' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index) .'4:' . $this->columnIndexToLetter($last_index) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 1) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 1) .'4:' . $this->columnIndexToLetter($last_index + 1) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 4) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 4) .'4:' . $this->columnIndexToLetter($last_index + 4) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 7) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 7) .'4:' . $this->columnIndexToLetter($last_index + 7) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 10) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 10) .'4:' . $this->columnIndexToLetter($last_index + 10) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 13) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 13) .'4:' . $this->columnIndexToLetter($last_index + 13) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 16) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 16) .'4:' . $this->columnIndexToLetter($last_index + 16) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 17) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 17) .'4:' . $this->columnIndexToLetter($last_index + 17) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 18) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 18) .'4:' . $this->columnIndexToLetter($last_index + 18) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 20) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 20) .'4:' . $this->columnIndexToLetter($last_index + 20) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 21) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 21) .'4:' . $this->columnIndexToLetter($last_index + 21) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 22) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 22) .'4:' . $this->columnIndexToLetter($last_index + 22) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 23) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 23) .'4:' . $this->columnIndexToLetter($last_index + 23) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 24) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 24) .'4:' . $this->columnIndexToLetter($last_index + 24) . '' . ($i-1) . ')')
                        ->setCellValue($this->columnIndexToLetter($last_index + 25) . '' . $i, '=SUM('. $this->columnIndexToLetter($last_index + 25) .'4:' . $this->columnIndexToLetter($last_index + 25) . '' . ($i-1) . ')');
            
            for($index = $last_index - 1; $index >= 34; $index-=3){
                $objPHPExcel->setActiveSheetIndex(0)   
                    ->setCellValue($this->columnIndexToLetter($index) . '' . $i, '=SUM('. $this->columnIndexToLetter($index) .'4:' . $this->columnIndexToLetter($index) . '' . ($i-1) . ')');
    
                $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($index) . '4:' . $this->columnIndexToLetter($index) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            }
    
            $objPHPExcel->getActiveSheet()->getStyle('G4:AH' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index) . '4:' . $this->columnIndexToLetter($last_index) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 1) . '4:' . $this->columnIndexToLetter($last_index + 1) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 4) . '4:' . $this->columnIndexToLetter($last_index + 4) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 7) . '4:' . $this->columnIndexToLetter($last_index + 7) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 10) . '4:' . $this->columnIndexToLetter($last_index + 10) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 13) . '4:' . $this->columnIndexToLetter($last_index + 13) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 16) . '4:' . $this->columnIndexToLetter($last_index + 16) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 17) . '4:' . $this->columnIndexToLetter($last_index + 17) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 18) . '4:' . $this->columnIndexToLetter($last_index + 18) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 20) . '4:' . $this->columnIndexToLetter($last_index + 20) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 21) . '4:' . $this->columnIndexToLetter($last_index + 21) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 22) . '4:' . $this->columnIndexToLetter($last_index + 22) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 23) . '4:' . $this->columnIndexToLetter($last_index + 23) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 24) . '4:' . $this->columnIndexToLetter($last_index + 24) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 25) . '4:' . $this->columnIndexToLetter($last_index + 25) . '' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
        }


        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $this->columnIndexToLetter($last_index + 18) . '3')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size'  => 12,
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '101D6B')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'FFFFFF'),
                        )
                    )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 20) . '1:' . $this->columnIndexToLetter($last_index + 25) . '3')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size'  => 12,
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '101D6B')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'FFFFFF'),
                        )
                    )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':' . $this->columnIndexToLetter($last_index + 25) . $i)->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                ),
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    )
                ),
            )
        );

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 20) . '1:' . $this->columnIndexToLetter($last_index + 25) . '3')->applyFromArray($style);
        $objPHPExcel->getActiveSheet()->getStyle($this->columnIndexToLetter($last_index + 25) . '1')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(20);
        
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 1))->setWidth(25);

        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 2))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 3))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 4))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 5))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 6))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 7))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 8))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 9))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 10))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 11))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 12))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 13))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 14))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 15))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 16))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 17))->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 18))->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 19))->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 20))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 21))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 22))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 23))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 24))->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->columnIndexToLetter($last_index + 25))->setWidth(20);

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:B3');
        $sheet->mergeCells('C1:C3');
        $sheet->mergeCells('D1:D3');
        $sheet->mergeCells('E1:E3');
        $sheet->mergeCells('F1:F3');
        $sheet->mergeCells('G1:M1');
        $sheet->mergeCells('G2:G3');
        $sheet->mergeCells('H2:H3');
        $sheet->mergeCells('I2:I3');
        $sheet->mergeCells('J2:J3');
        $sheet->mergeCells('K2:K3');
        $sheet->mergeCells('L2:L3');
        $sheet->mergeCells('M2:M3');
        $sheet->mergeCells('N1:T1');
        $sheet->mergeCells('N2:N3');
        $sheet->mergeCells('O2:O3');
        $sheet->mergeCells('P2:P3');
        $sheet->mergeCells('Q2:Q3');
        $sheet->mergeCells('R2:R3');
        $sheet->mergeCells('S2:S3');
        $sheet->mergeCells('T2:T3');
        $sheet->mergeCells('U1:U3');
        $sheet->mergeCells('V1:AF1');
        $sheet->mergeCells('V2:V3');
        $sheet->mergeCells('W2:X2');
        $sheet->mergeCells('Y2:Z2');
        $sheet->mergeCells('AA2:AB2');
        $sheet->mergeCells('AC2:AD2');
        $sheet->mergeCells('AE2:AF2');
        $sheet->mergeCells('AG1:AG3');
        $sheet->mergeCells('AH1:AH3');

        $sheet->mergeCells($this->columnIndexToLetter($last_index) . '1:' . $this->columnIndexToLetter($last_index) .'3');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 1) . '1:' . $this->columnIndexToLetter($last_index + 1) . '3');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 2) . '1:' . $this->columnIndexToLetter($last_index + 16) . '1');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 2) . '2:' . $this->columnIndexToLetter($last_index + 4) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 5) . '2:' . $this->columnIndexToLetter($last_index + 7) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 8) . '2:' . $this->columnIndexToLetter($last_index + 10) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 11) . '2:' . $this->columnIndexToLetter($last_index + 13) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 14) . '2:' . $this->columnIndexToLetter($last_index + 16) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 17) . '1:' . $this->columnIndexToLetter($last_index + 17) . '3');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 18) . '1:' . $this->columnIndexToLetter($last_index + 18) . '3');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 20) . '1:' . $this->columnIndexToLetter($last_index + 20) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 21) . '1:' . $this->columnIndexToLetter($last_index + 21) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 22) . '1:' . $this->columnIndexToLetter($last_index + 22) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 23) . '1:' . $this->columnIndexToLetter($last_index + 23) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 24) . '1:' . $this->columnIndexToLetter($last_index + 24) . '2');
        $sheet->mergeCells($this->columnIndexToLetter($last_index + 25) . '1:' . $this->columnIndexToLetter($last_index + 25) . '3');
         
        $objPHPExcel->getActiveSheet()->setTitle('AR Report');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="AR Report ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ched_report($sem = 0, $campus)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sy = $s['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
      
        $title = 'Ched Report';

        $i = 8;

        foreach($students as $student){
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);  
            $subjects = $this->db->select('tb_mas_subjects.strCode, tb_mas_subjects.strDescription, tb_mas_subjects.strUnits, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
            ->get()
            ->result_array();

            if($subjects){
                // Add some data
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, str_replace("-", "", $student['strStudentNumber']))
                    ->setCellValue('B'.$i, strtoupper($student['strLastname']))
                    ->setCellValue('C'.$i, strtoupper($student['strFirstname']))
                    ->setCellValue('D'.$i, strtoupper($student['strMiddlename']))
                    ->setCellValue('E'.$i, strtoupper($student['enumGender']))
                    ->setCellValue('F'.$i, $course['strProgramCode'])
                    ->setCellValue('G'.$i, $student['intStudentYear']);

                foreach($subjects as $subject){
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('H'.$i, strtoupper($subject['strCode']))
                        ->setCellValue('I'.$i, strtoupper($subject['strDescription']))
                        ->setCellValue('J'.$i, $subject['strUnits'])
                        ->setCellValueExplicit('K'.$i, number_format((float)$subject['floatMidtermGrade'], 2, '.', ''))
                        ->setCellValueExplicit('L'.$i, number_format((float)$subject['floatFinalGrade'], 2, '.', ''));
                    $i++;
                }

                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray(
                    array(
                        'borders' => array(
                            'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                    )
                );
            }
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'INFORMATION AND COMMUNICATIONS TECHNOLOGY ACADEMY INC., (iACADEMY ' . ucfirst(strtolower($campus))  . ' )')
                    ->setCellValue('A3', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : '7434 Yakal Street Brgy. San Antonio, Makati City')
                    ->setCellValue('B5', 'CHED FORM XIX FOR')
                    ->setCellValue('C5', $sy->enumSem . ' ' . $this->data["term_type"])
                    ->setCellValue('H5', 'SCHOOL YEAR')
                    ->setCellValue('I5', $sy->strYearStart . '-' . $sy->strYearEnd)
                    ->setCellValue('A7', 'STUDENT NO.')
                    ->setCellValue('B7', 'LAST NAME')
                    ->setCellValue('C7', 'FIRST NAME')
                    ->setCellValue('D7', 'MIDDLE NAME')
                    ->setCellValue('E7', 'GENDER')
                    ->setCellValue('F7', 'COURSE')
                    ->setCellValue('G7', 'YEAR')
                    ->setCellValue('H7', 'SUBJECTS')
                    ->setCellValue('I7', 'SUBJECT DESCRIPTIONS')
                    ->setCellValue('J7', 'UNIT')
                    ->setCellValue('K7', 'MG')
                    ->setCellValue('L7', 'FG');
                    
        $objPHPExcel->getActiveSheet()->getStyle('C5')->getFont()->setUnderline(true);
        $objPHPExcel->getActiveSheet()->getStyle('I5')->getFont()->setUnderline(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A7:L7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('G7:G' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('J7:L' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 15,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A3:I5')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 13,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:L7')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                )
            )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A7:L7')->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        // $sheet->mergeCells('B5:C5');
        // $sheet->mergeCells('H5:I5');

        $objPHPExcel->getActiveSheet()->setTitle('AR Report');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="Ched Promotional Report ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ched_enrollment_report($sem = 0, $campus)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sy = $s['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $title = 'Ched Enrollment Report';

        $i = 8;

        foreach($students as $index => $student){
            
            $suffixList = ['Jr.', 'Jr', 'Sr.', 'Sr', 'II', 'III', 'IV'];
            $nameExtension = '';
            $lastName = $student['strLastname'];

            foreach($suffixList as $suffix){
                // check if last name contains a suffix 
                if(strpos($student['strLastname'], $suffix) !== false){
                    $nameExtension = $suffix;
                    $lastName = str_replace($suffix, '', $student['strLastname']);
                    break;
                }
            }
            
            $totalUnits = 0;
            $subjectsEnrolled = '';
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);  
            
            $subjects = $this->db->select('tb_mas_subjects.strCode, tb_mas_subjects.strDescription, tb_mas_subjects.strUnits, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
            ->get()
            ->result_array();

            if($subjects){
                // Add some data
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $index + 1)
                    ->setCellValue('B'.$i, $course['strProgramDescription'])
                    ->setCellValue('C'.$i, $course['strMajor'] != 'None' ? $course['strMajor'] : null)
                    ->setCellValue('D'.$i, str_replace("-", "", $student['strStudentNumber']))
                    ->setCellValue('E'.$i, ucfirst($student['strFirstname']))
                    ->setCellValue('F'.$i, ucfirst($student['strMiddlename']))
                    ->setCellValue('G'.$i, ucfirst($lastName))
                    ->setCellValue('H'.$i, ucfirst($nameExtension))
                    ->setCellValue('I'.$i, ucfirst($student['strCitizenship']))
                    ->setCellValue('J'.$i, substr(ucfirst($student['enumGender']), 0, 1))
                    ->setCellValue('K'.$i, strtoupper($student['intStudentYear']))
                    ->setCellValue('L'.$i, $subjectsEnrolled)
                    ->setCellValue('M'.$i, $totalUnits);

                $i++;

                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray(
                    array(
                        'borders' => array(
                            'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                    )
                );
            }
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'School Name :')
                    ->setCellValue('B1', 'iACADEMY')
                    ->setCellValue('A2', 'Address :')
                    ->setCellValue('B2', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : 'iACADEMY Nexus 7434 Yakal Street Brgy. San Antonio, Makati City')
                    ->setCellValue('A3', 'Term & AY :')
                    ->setCellValue('B3', $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd)
                    ->setCellValue('A4', 'Note:')
                    ->setCellValue('B4', 'This template is the official template for Enrollment List submission released by CHED NCR Office, You may add rows for student lists but not the columns')
                    ->setCellValue('A5', 'NO.')
                    ->setCellValue('B5', 'PROGRAM')
                    ->setCellValue('C5', 'MAJOR')
                    ->setCellValue('D5', 'STUDENT NUMBER')
                    ->setCellValue('E5', 'FIRST NAME')
                    ->setCellValue('F5', 'MIDDLE NAME')
                    ->setCellValue('G5', 'SURNAME')
                    ->setCellValue('H5', 'NAME EXTENSION')
                    ->setCellValue('I5', 'CITIZENSHIP')
                    ->setCellValue('J5', 'GENDER')
                    ->setCellValue('K5', 'YEAR LEVEL')
                    ->setCellValue('L5', 'SUBJECTS ENROLLED FOLLOWED BY UNITS')
                    ->setCellValue('M5', 'NO. OF UNITS')
                    ->setCellValue('N5', 'REMARKS (if any)')
                    ->setCellValue('B6', 'Ex. Bachelor of Science in Business Administration')
                    ->setCellValue('B7', 'Please do not abbreviate')
                    ->setCellValue('C6', 'Ex. Marketing')
                    ->setCellValue('C7', 'Do not insert N/A')
                    ->setCellValue('D7', 'Do not insert N/A')
                    ->setCellValue('E6', 'Juan III')
                    ->setCellValue('F6', 'Santos')
                    ->setCellValue('G6', 'Dela Cruz')
                    ->setCellValue('E7', 'Do not insert N/A')
                    ->setCellValue('H6', 'Ex. Jr., II, III Do not insert N/A')
                    ->setCellValue('I6', 'Ex. Filipino')
                    ->setCellValue('J6', 'M/F')
                    ->setCellValue('K6', '1 / 2 / 3 / 4 / 5')
                    ->setCellValue('L6', 'Ex. On the Job Trainee (3), Communication Arts (3)')
                    ->setCellValue('L7', 'Please do not abbreviate');
                    
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getFont()->setItalic(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B5:N7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1:A3')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size'  => 12,
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '101D6B')
                ),
            )
        );
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A1'), 'A5:N7');
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A1'), 'A8:A' . $i);

        $objPHPExcel->getActiveSheet()->getStyle('B1:B3')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 15,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:A3')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5:N5')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('B6:L7')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => false,
                    'italic' => true,
                    'size'  => 10,
                )
            )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A5:N' . $i)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );
        
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A5:N'.$i)->applyFromArray($style);
        $objPHPExcel->getActiveSheet()->getStyle('B8:N'.$i)->getAlignment()->setWrapText(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('B1:H1');
        $sheet->mergeCells('B2:H2');
        $sheet->mergeCells('B3:H3');
        $sheet->mergeCells('A5:A7');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('H6:H7');
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('M5:M7');
        $sheet->mergeCells('N5:N7');
        $sheet->mergeCells('E7:G7');

        $objPHPExcel->getActiveSheet()->setTitle('CHED - Enrollment Report');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="CHED Enrollment Report ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ched_tes_report($sem = 0, $campus)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sy = $s['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $title = 'Ched TES Report';

        $i = 10;

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
            
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $index + 1)
                ->setCellValue('B'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('C'.$i, ucfirst($lastName))
                ->setCellValue('D'.$i, ucfirst($student['strFirstname']))
                ->setCellValue('E'.$i, ucfirst($nameExtension))
                ->setCellValue('F'.$i, ucfirst($student['strMiddlename']))
                ->setCellValue('G'.$i, ucfirst($student['enumGender']))
                ->setCellValue('H'.$i, date("d/m/Y", strtotime($student['dteBirthDate'])))
                ->setCellValue('I'.$i, $course['strProgramDescription'])
                ->setCellValue('J'.$i, $student['intStudentYear'])
                ->setCellValue('K'.$i, $fatherLastName)
                ->setCellValue('L'.$i, $fatherFirstName)
                ->setCellValue('M'.$i, $fatherMiddleName)
                ->setCellValue('N'.$i, $motherLastName)
                ->setCellValue('O'.$i, $motherFirstName)
                ->setCellValue('P'.$i, $motherMiddleName)
                ->setCellValue('Q'.$i, $address[0])
                ->setCellValue('R'.$i, is_numeric($address[count($address) - 1]) ? $address[count($address) - 1] : '')
                ->setCellValue('S'.$i, '')
                ->setCellValue('T'.$i, $student['strMobileNumber'])
                ->setCellValue('U'.$i, $student['strEmail'])
                ->setCellValue('V'.$i, '');

            $i++;

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray(
                array(
                    'borders' => array(
                        'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => '000000'),
                        ),
                    ),
                )
            );
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'HEI NAME :')
                    ->setCellValue('B2', 'iACADEMY')
                    ->setCellValue('A3', 'HEI UII :')
                    ->setCellValue('B3', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : 'iACADEMY Nexus 7434 Yakal Street Brgy. San Antonio, Makati City')
                    ->setCellValue('A4', 'Acad Year :')
                    ->setCellValue('B4', $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd)
                    ->setCellValue('B7', 'STUDENT INFORMATION')
                    ->setCellValue('K7', 'FAMILY BACKGROUND')
                    ->setCellValue('A8', 'SEQ')
                    ->setCellValue('B8', 'STUDENT ID')
                    ->setCellValue('C8', 'STUDENT\'S NAME')
                    ->setCellValue('G8', 'STUDENT\'S PROFILE')
                    ->setCellValue('K8', 'FATHER\'S NAME')
                    ->setCellValue('N8', 'MOTHER\'S MAIDEN NAME')
                    ->setCellValue('Q8', 'PERMANENT ADDRESS	')
                    ->setCellValue('S8', 'DISABILITY (leave blank if NOT Applicable)')
                    ->setCellValue('T8', 'CONTACT NUMBER')
                    ->setCellValue('U8', 'EMAIL ADDRESS')
                    ->setCellValue('V8', 'INDIGENOUS PEOPLE GROUP (leave blank if NOT Applicable)')
                    ->setCellValue('C9', 'LAST NAME')
                    ->setCellValue('D9', 'GIVEN NAME')
                    ->setCellValue('E9', 'EXT. NAME')
                    ->setCellValue('F9', 'MIDDLE NAME')
                    ->setCellValue('G9', 'SEX (Male or Female)')
                    ->setCellValue('H9', 'BIRTHDATE (dd/mm/yyyy)')
                    ->setCellValue('I9', 'COMPLETE PROGRAM NAME (Should be consistent with your HEI Registry)')
                    ->setCellValue('J9', 'YEAR LEVEL (1,2,3,4,5)')
                    ->setCellValue('K9', 'LAST NAME')
                    ->setCellValue('L9', 'GIVEN NAME')
                    ->setCellValue('M9', 'MIDDLE NAME')
                    ->setCellValue('N9', 'LAST NAME')
                    ->setCellValue('O9', 'GIVEN NAME')
                    ->setCellValue('P9', 'MIDDLE NAME')
                    ->setCellValue('Q9', 'STREET & BARANGAY')
                    ->setCellValue('R9', 'ZIPCODE (TES Applicant)');
                    
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getFont()->setItalic(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:K7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A2:A4')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 11,
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '0D6ED0')
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A8'), 'C8:S8');
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A8'), 'V8');
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('A8'), 'E9');

        $objPHPExcel->getActiveSheet()->getStyle('B8')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 11,
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFAD56')
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B8'), 'T8:U8');
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B8'), 'C9:D9');
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B8'), 'F9:R9');
        
        $objPHPExcel->getActiveSheet()->getStyle('A8:V' . $i)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A8:V'.$i)->applyFromArray($style);
        $objPHPExcel->getActiveSheet()->getStyle('A8:V'.$i)->getAlignment()->setWrapText(true);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(6);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(15);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('B2:H2');
        $sheet->mergeCells('B3:H3');
        $sheet->mergeCells('B4:H4');
        $sheet->mergeCells('B7:J7');
        $sheet->mergeCells('K7:R7');
        $sheet->mergeCells('A8:A9');
        $sheet->mergeCells('B8:B9');
        $sheet->mergeCells('C8:F8');
        $sheet->mergeCells('G8:J8');
        $sheet->mergeCells('K8:M8');
        $sheet->mergeCells('N8:P8');
        $sheet->mergeCells('Q8:R8');
        $sheet->mergeCells('S8:S9');
        $sheet->mergeCells('T8:T9');
        $sheet->mergeCells('U8:U9');
        $sheet->mergeCells('V8:V9');

        $objPHPExcel->getActiveSheet()->setTitle('CHED - TES');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="CHED TES Report ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ched_nstp_report($sem = 0, $campus)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sy = $s['intID'];
        }

        $students = $this->db->select('tb_mas_users.*')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $title = 'Ched NSTP Report';

        $i = 8;

        foreach($students as $index => $student){
            
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

            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $index + 1)
                ->setCellValue('B'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('C'.$i, ucfirst($student['strLastname']))
                ->setCellValue('D'.$i, ucfirst($student['strFirstname']))
                ->setCellValue('E'.$i, ucfirst($student['strMiddlename']))
                ->setCellValue('F'.$i, $course['strProgramDescription'])
                ->setCellValue('G'.$i, ucfirst($student['enumGender']))
                ->setCellValue('H'.$i, date("m/d/Y", strtotime($student['dteBirthDate'])))
                ->setCellValue('I'.$i, $address[0])
                ->setCellValue('J'.$i, $city)
                ->setCellValue('K'.$i, $province)
                ->setCellValue('L'.$i, $student['strMobileNumber'])
                ->setCellValue('M'.$i, $student['strEmail']);

            $i++;

        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'iACADEMY')
                    ->setCellValue('A2', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : 'iACADEMY Nexus 7434 Yakal Street Brgy. San Antonio, Makati City Contact No. 889-5555')
                    ->setCellValue('A3', 'Institutional Identifier No.: 13315')
                    ->setCellValue('A4', 'Term/SY: ' . $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd)
                    ->setCellValue('A5', 'List of NSTP CWTS/LTS Enrollees')
                    ->setCellValue('A7', 'No.')
                    ->setCellValue('B7', 'Student No.')
                    ->setCellValue('C7', 'Surname')
                    ->setCellValue('D7', 'First Name')
                    ->setCellValue('E7', 'Middle Name')
                    ->setCellValue('F7', 'Course/Program (Write in Full)')
                    ->setCellValue('G7', 'Gender')
                    ->setCellValue('H7', 'Birthdate (ex. 11/25/1992)')
                    ->setCellValue('I7', 'Street/Barangay Address')
                    ->setCellValue('J7', 'Town/City Address')
                    ->setCellValue('K7', 'Provincial Address')
                    ->setCellValue('L7', 'Contact Number Telephone/Mobile')
                    ->setCellValue('M7', 'Email address');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A1:A5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1:M5')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:M7')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 11,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A7:M' . $i)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A7:M'.$i)->applyFromArray($style);
        $objPHPExcel->getActiveSheet()->getStyle('A7:M'.$i)->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:M3');
        $sheet->mergeCells('A4:M4');
        $sheet->mergeCells('A5:M5');

        $objPHPExcel->getActiveSheet()->setTitle('CHED - NSTP');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="CHED NSTP Report ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function deans_list($term,$period)
    {
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

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $title = 'Dean\'s Listers';

        $i = 2;

        //1ST HONORS Sheet
        foreach($data['list_1st_honor'] as $student){
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('B'.$i, ucfirst($student['strLastname']))
                ->setCellValue('C'.$i, ucfirst($student['strFirstname']))
                ->setCellValue('D'.$i, ucfirst($student['strMiddlename']))
                ->setCellValue('E'.$i, $student['strProgramCode'])
                ->setCellValue('F'.$i, $student['gwa']);

            $i++;

        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Student Number')
                    ->setCellValue('B1', 'Last Name')
                    ->setCellValue('C1', 'First Name')
                    ->setCellValue('D1', 'Middle Name')
                    ->setCellValue('E1', 'Course')
                    ->setCellValue('F1', 'GWA');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('F2:F' . $i)->getNumberFormat()->setFormatCode('#,##0.000');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        $objPHPExcel->getActiveSheet()->setTitle('1st Honors');


        //2ND HONORS Sheet
        $objPHPExcel->createSheet(1);
        $i = 2;

        foreach($data['list_2nd_honor'] as $student){
            // Add some data
            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('B'.$i, ucfirst($student['strLastname']))
                ->setCellValue('C'.$i, ucfirst($student['strFirstname']))
                ->setCellValue('D'.$i, ucfirst($student['strMiddlename']))
                ->setCellValue('E'.$i, $student['strProgramCode'])
                ->setCellValue('F'.$i, $student['gwa']);

            $i++;

        }

        $objPHPExcel->setActiveSheetIndex(1)
                    ->setCellValue('A1', 'Student Number')
                    ->setCellValue('B1', 'Last Name')
                    ->setCellValue('C1', 'First Name')
                    ->setCellValue('D1', 'Middle Name')
                    ->setCellValue('E1', 'Course')
                    ->setCellValue('F1', 'GWA');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('F2:F' . $i)->getNumberFormat()->setFormatCode('#,##0.000');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        $objPHPExcel->getActiveSheet()->setTitle('2nd Honors');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="Dean\'s Listers_ ' . $period . '_' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function enhanced_list($sem = 0)
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

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $i = 3;
        
        foreach($students as $student){
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('B'.$i, ucfirst($student['strLastname']) . ', ' . ucfirst($student['strFirstname']) . ' ' . ucfirst($student['strMiddlename']))
                ->setCellValue('C'.$i, $course['strProgramCode']);

            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'LIST OF ENHANCED')
                    ->setCellValue('A2', 'Student Number')
                    ->setCellValue('B2', 'Student Name')
                    ->setCellValue('C2', 'Course');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A1:C2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 14,
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A2:C2')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);

        $objPHPExcel->getActiveSheet()->setTitle('List of Enhanced');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="List_of_Enhanced_ ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function regular_list($sem = 0)
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

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $i = 3;
        
        foreach($students as $student){
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('B'.$i, ucfirst($student['strLastname'] . ', ' . ucfirst($student['strFirstname']) . ' ' . ucfirst($student['strMiddlename'])))
                ->setCellValue('C'.$i, $course['strProgramCode']);

            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'LIST OF REGULAR')
                    ->setCellValue('A2', 'Student Number')
                    ->setCellValue('B2', 'Student Name')
                    ->setCellValue('C2', 'Course');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A1:C2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 14,
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A2:C2')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);

        $objPHPExcel->getActiveSheet()->setTitle('List of Regular');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="List_of_Regular_ ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
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

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $i = 2;
        
        foreach($students as $student){
            $course = $this->data_fetcher->getProgramDetails($student['intProgramID']);
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('B'.$i, ucfirst($student['strLastname']))
                ->setCellValue('C'.$i, ucfirst($student['strFirstname']))
                ->setCellValue('D'.$i, ucfirst($student['strMiddlename']))
                ->setCellValue('E'.$i, $student['strProgramCode'])
                ->setCellValue('F'.$i, $student['intYearLevel'])
                ->setCellValue('G'.$i, $student['strSection']);
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Student Number')
                    ->setCellValue('B1', 'Last Name')
                    ->setCellValue('C1', 'First Name')
                    ->setCellValue('D1', 'Middle Name')
                    ->setCellValue('E1', 'Course')
                    ->setCellValue('F1', 'Year Level')
                    ->setCellValue('G1', 'Section');
                    
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);

        $objPHPExcel->getActiveSheet()->setTitle('SHS List');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="SHS List Grade  ' . $year_level . ' ' .  $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function shs_student_grades($sem = 0, $year_level = 0, $campus)
    {
        $sy = $this->db->get_where('tb_mas_sy', array('intID' => $sem))->first_row();
        if($sem == 0 )
        {
            $sy = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }

        $gradeLevel = 'All Year Level';
        $students = $this->db->select('tb_mas_users.*, tb_mas_registration.dteRegistered')
                    ->from('tb_mas_users')
                    ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                    ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                    ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_programs.type'=>'shs'))
                    ->order_by('tb_mas_users.strLastname', 'ASC')
                    ->get()
                    ->result_array();

        if($year_level != 0){
            $gradeLevel = $year_level;
            $students = $this->db->select('tb_mas_users.*, tb_mas_registration.dteRegistered')
                        ->from('tb_mas_users')
                        ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                        ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                        ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_programs.type'=>'shs', 'tb_mas_registration.intYearLevel'=>$year_level))
                        ->order_by('tb_mas_users.strLastname', 'ASC')
                        ->get()
                        ->result_array();
        }
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        $title = 'SHS List of Student Grades';

        $i = 10;
        $count = 1;
        

        foreach($students as $student){
            $subjects = $this->db->select('tb_mas_subjects.strCode, tb_mas_subjects.strDescription, tb_mas_subjects.strUnits, tb_mas_classlist.strSection, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalGrade, 
                                            tb_mas_room_schedule.strDay, tb_mas_room_schedule.dteStart, tb_mas_room_schedule.dteEnd, tb_mas_room_schedule.strDay, tb_mas_room_schedule.dteStart, tb_mas_room_schedule.dteEnd,tb_mas_faculty.strLastname, tb_mas_faculty.strFirstname')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->join('tb_mas_subjects','tb_mas_classlist.intSubjectID = tb_mas_subjects.intID')
            ->join('tb_mas_room_subject', 'tb_mas_subjects.intID = tb_mas_room_subject.intSubjectID')
            ->join('tb_mas_classrooms', 'tb_mas_room_subject.intRoomID = tb_mas_classrooms.intID')
            ->join('tb_mas_room_schedule', 'tb_mas_classrooms.intID = tb_mas_room_schedule.intRoomID')
            ->join('tb_mas_faculty', 'tb_mas_classlist.intFacultyID = tb_mas_faculty.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem))
            ->get()
            ->result_array();

            if($subjects){
                // Add some data
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $count)
                    ->setCellValue('B'.$i, str_replace("-", "", $student['strStudentNumber']))
                    ->setCellValue('C'.$i, strtoupper($student['strLastname']) . ', ' . strtoupper($student['strFirstname']) . ' ' . strtoupper($student['strMiddlename']))
                    ->setCellValue('D'.$i, date("M j, Y", strtotime($student['dteRegistered'])));

                foreach($subjects as $subject){
                    $days = [ 1 => 'S', 2 => 'M', 3 => 'T', 4 => 'W', 5 => 'TH', 6 => 'F', 7 => 'S'];
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('E'.$i, $subject['strSection'])
                        ->setCellValue('F'.$i, $subject['strCode'])
                        ->setCellValue('G'.$i, $days[$subject['strDay']])
                        ->setCellValue('H'.$i, date('h:i A', strtotime($subject['dteStart'])) . '-' . date('h:i A', strtotime($subject['dteEnd'])))
                        ->setCellValue('I'.$i, $subject['floatFinalGrade'])
                        ->setCellValue('J'.$i, $subject['strLastname'] . ', ' . $subject['strFirstname']);
                    $i++;
                }
                $count++;
            }
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY')
                    ->setCellValue('A3', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : 'iACADEMY Nexus 7434 Yakal Street Brgy. San Antonio, Makati City')
                    ->setCellValue('A5', 'LIST OF STUDENT GRADES')
                    ->setCellValue('A6', $sy->enumSem . ' ' . $this->data["term_type"] . ', AY ' . $sy->strYearStart . '-' . $sy->strYearEnd)
                    ->setCellValue('A8', 'Grade/Year Level: ' . $gradeLevel)
                    ->setCellValue('A9', '#')
                    ->setCellValue('B9', 'Student #')
                    ->setCellValue('C9', 'Student Name')
                    ->setCellValue('D9', 'Date Enrolled')
                    ->setCellValue('E9', 'Section')
                    ->setCellValue('F9', 'Subject')
                    ->setCellValue('G9', 'Day')
                    ->setCellValue('H9', 'Time')
                    ->setCellValue('I9', 'Grade')
                    ->setCellValue('J9', 'Professor');

        $objPHPExcel->getActiveSheet()->getStyle('A2:A7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A10:J'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 14,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                )
            )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A9:J9')->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');
        $sheet->mergeCells('A4:J4');
        $sheet->mergeCells('A5:J5');
        $sheet->mergeCells('A6:J6');
        $sheet->mergeCells('A7:J7');
        $sheet->mergeCells('A8:C8');

        $objPHPExcel->getActiveSheet()->setTitle('SHS Student Grades');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="SHS List of Student Grades - ' . $gradeLevel . ' ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function shs_gwa_rank($sem = 0, $year_level = 0, $campus)
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
            $gradeLevel = 'Grade_' . $year_level;
            $students = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode, tb_mas_registration.intYearLevel')
                        ->from('tb_mas_users')
                        ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                        ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                        ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_programs.type'=>'shs', 'tb_mas_registration.intYearLevel'=>$year_level))
                        ->order_by('tb_mas_users.strLastname', 'ASC')
                        ->get()
                        ->result_array();
        }
        
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        $title = 'SHS GWA Rank';

        $i = 9;
        $count = 1;
        $gwa_ranks = array();

        foreach($students as $student){
            $totalGrades = 0;
            $subjects = $this->db->select('tb_mas_classlist_student.floatPrelimGrade, tb_mas_classlist_student.floatMidtermGrade, tb_mas_classlist_student.floatFinalsGrade')
            ->from('tb_mas_classlist_student')
            ->join('tb_mas_classlist','tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID')
            ->where(array('tb_mas_classlist_student.intStudentID'=>$student['intID'],'tb_mas_classlist.strAcademicYear'=>$sem,'tb_mas_classlist_student.floatPrelimGrade !='=>null, 'tb_mas_classlist_student.floatMidtermGrade !='=>null, 'tb_mas_classlist_student.floatFinalsGrade !='=>null))
            ->get()
            ->result_array();

            if(count($subjects) >  0){
                foreach($subjects as $subject){
                    $average = getAve($subject['floatPrelimGrade'], $subject['floatMidtermGrade'], $subject['floatFinalsGrade']);
                    $totalGrades += $average;
                }
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

        foreach($gwa_ranks as $student){
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B'.$i, $count)
                ->setCellValue('C'.$i, str_replace("-", "", $student['student_number']))
                ->setCellValue('D'.$i, $student['last_name'])
                ->setCellValue('E'.$i, $student['first_name'])
                ->setCellValue('F'.$i, $student['middle_name'])
                ->setCellValue('G'.$i, $student['track'])
                ->setCellValue('H'.$i, number_format(round($student['gwa'],2),2))
                ->setCellValue('I'.$i, $student['year_level']);

            $count++;
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B2', 'iACADEMY')
            ->setCellValue('B3', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : 'iACADEMY Nexus 7434 Yakal Street Brgy. San Antonio, Makati City')
            ->setCellValue('B5', 'LIST of SHS GWA RANK')
            ->setCellValue('B6', $sy->enumSem . ' ' . $this->data["term_type"] . ', AY ' . $sy->strYearStart . '-' . $sy->strYearEnd)
            ->setCellValue('B8', 'Rank')
            ->setCellValue('C8', 'Student No.')
            ->setCellValue('D8', 'Last Name')
            ->setCellValue('E8', 'First Name')
            ->setCellValue('F8', 'Middle Name')
            ->setCellValue('G8', 'Track/Strand')
            ->setCellValue('H8', 'GWA')
            ->setCellValue('I8', 'GL');

        $objPHPExcel->getActiveSheet()->getStyle('B2:I8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B9:J'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('B2')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 14,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('B8:I'. ($i-1))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('B2:I2');
        $sheet->mergeCells('B3:I3');
        $sheet->mergeCells('B4:I4');
        $sheet->mergeCells('B5:I5');
        $sheet->mergeCells('B6:I6');

        $objPHPExcel->getActiveSheet()->setTitle('SHS GWA RANK');

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="SHS List of GWA Rank - ' . $gradeLevel . ' ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
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
            $gradeLevel = 'Grade_' . $year_level;
            if($sy->term_student_type == 'college'){
                $gradeLevel = 'Year_' . $year_level;
            }
            $students = $this->db->select('tb_mas_users.*, tb_mas_programs.strProgramCode')
                        ->from('tb_mas_users')
                        ->join('tb_mas_registration','tb_mas_registration.intStudentID = tb_mas_users.intID')
                        ->join('tb_mas_programs','tb_mas_registration.current_program = tb_mas_programs.intProgramID')
                        ->where(array('tb_mas_registration.intAYID'=>$sem, 'tb_mas_registration.intYearLevel'=>$year_level))
                        ->order_by('tb_mas_users.strLastname', 'ASC')
                        ->get()
                        ->result_array();
        }

        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        $title = 'SHS GWA Rank';

        $i = 9;
        $count = 1;
        

        foreach($students as $student){
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $count)
                ->setCellValue('B'.$i, str_replace("-", "", $student['strStudentNumber']))
                ->setCellValue('C'.$i, strtoupper($student['strLastname']))
                ->setCellValue('D'.$i, strtoupper($student['strFirstname']))
                ->setCellValue('E'.$i, strtoupper($student['strMiddlename']))
                ->setCellValue('F'.$i, $student['strProgramCode']);

            $i++;
            $count++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A2', 'iACADEMY')
                    ->setCellValue('A3', $campus == 'Cebu' ? '5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City' : 'iACADEMY Nexus 7434 Yakal Street Brgy. San Antonio, Makati City')
                    ->setCellValue('A5', 'List of Students with Track and College Course')
                    ->setCellValue('A6', $sy->enumSem . ' ' . $this->data["term_type"] . ', AY ' . $sy->strYearStart . '-' . $sy->strYearEnd)
                    ->setCellValue('A8', 'No.')
                    ->setCellValue('B8', 'Student No.')
                    ->setCellValue('C8', 'Last Name')
                    ->setCellValue('D8', 'First Name')
                    ->setCellValue('E8', 'Middle Name')
                    ->setCellValue('F8', 'Enrolled Course');

        $objPHPExcel->getActiveSheet()->getStyle('A2:A7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A9:J'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 14,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                )
            )
        );
        
        $objPHPExcel->getActiveSheet()->getStyle('A8:F8')->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => '000000'),
                    'size'  => 12,
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        $sheet->mergeCells('A5:F5');
        $sheet->mergeCells('A6:F6');
        $sheet->mergeCells('A7:F7');

        $objPHPExcel->getActiveSheet()->setTitle('Track and Course - ' . $gradeLevel);

        $date = date("ymdhis");

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
        header('Content-Disposition: attachment;filename="Student List with Track and College Course - ' . $gradeLevel . ' ' . $sy->enumSem . '_' . $this->data["term_type"] . '_' . $sy->strYearStart . '-' . $sy->strYearEnd . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    private function columnIndexToLetter($columnIndex)
    {
        $letter = '';
        while ($columnIndex >= 0) {
            $remainder = $columnIndex % 26;
            $letter = chr(65 + $remainder) . $letter;
            $columnIndex = intval($columnIndex / 26) - 1;
        }
        return $letter;
    }
}