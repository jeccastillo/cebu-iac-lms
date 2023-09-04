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

    public function ched_enrollment_list($course = 0, $year=0,$gender = 0,$sem=0){

        if($sem == 0 )
        {
            $s = $this->data_fetcher->get_active_sem();
            $sem = $s['intID'];
        }
        
        $active_sem = $this->data_fetcher->get_sem_by_id($sem);
                
        $this->data['sy'] = $active_sem;

        
        $program = $this->db->get_where('tb_mas_programs',array('intProgramID'=>$course))->first_row('array');

       

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

        

        //--------------------------------HEADER----------------------------------------------
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'Name of Institution:');
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C1', 'iACADEMY Cebu');
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A2', 'Address:');
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C2', 'Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City');                                        
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A3', "Institutional Identifier");
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C3', '07XXX');
        
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', $active_sem['term_label']);
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C4', $active_sem['enumSem'].' '.$active_sem['term_label'].', AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']);                
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A5', "Course / Program:");
        
        $major = ($program['strMajor'] != "None" && $program['strMajor'] != "")?"Major in ".$program['strMajor']:'';

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('C5', $program['strProgramDescription']." ".$major);     
                
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A6', "Year Level:");
        $objPHPExcel->setActiveSheetIndex(0)
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

        $objPHPExcel->setActiveSheetIndex(0)->getStyle("C6")->applyFromArray($style2);



        //$active_sem['enumSem'].' Term, AY '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:B2');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:B3');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:B4');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:B5');      
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:B6');  
        //--------------------------------HEADER----------------------------------------------

        $term_label = ($active_sem['term_label'] == "Sem")?'Semester':'Term';
        // Add some datat
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A7', $term_label)
                    ->setCellValue('B7', 'Student No.')
                    ->setCellValue('C7', 'Student Name')
                    ->setCellValue('F7', 'Course')
                    ->setCellValue('G7', 'Gender')
                    ->setCellValue('H7', 'Bdate')                                        
                    ->setCellValue('I7', 'Current Year')     
                    ->setCellValue('J7', 'Subjects Enrolled')
                    ->setCellValue('K7', 'No. of Units');


        $objPHPExcel->setActiveSheetIndex(0)->getStyle("I7")->applyFromArray($style);                    
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("K7")->applyFromArray($style);                    
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("C7")->applyFromArray($style);

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A7:A8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B7:B8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('F7:F8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G7:G8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('H7:H8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('I7:I8');                    
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('J7:J8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K7:K8');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C7:E7');

        $objPHPExcel->setActiveSheetIndex(0)                                        
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
                 $objPHPExcel->setActiveSheetIndex(0)
                 ->setCellValue('A'.$i, $active_sem['enumSem'])
                 ->setCellValue('B'.$i, $student['strStudentNumber'])
                 ->setCellValue('C'.$i, $student['strLastname'])
                 ->setCellValue('D'.$i, $student['strFirstname'])
                 ->setCellValue('E'.$i, $student['strMiddlename'])
                 ->setCellValue('F'.$i, $student['strProgramCode'])
                 ->setCellValue('G'.$i, $student['enumGender'])
                 ->setCellValue('H'.$i, date("m/d/Y", strtotime($student['dteBirthDate'])))
                 ->setCellValue('I'.$i, $student['intYearLevel'])
                 ->setCellValue('J'.$i, $classes)
                 ->setCellValue('K'.$i, $total_units);
                 
         
                $objPHPExcel->setActiveSheetIndex(0)->getStyle("I".$i)->applyFromArray($style);
                $objPHPExcel->setActiveSheetIndex(0)->getStyle("K".$i)->applyFromArray($style);
                $i++;
            }
        }

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
        //$objPHPExcel->setActiveSheetIndex(0)
        //          ->setCellValue('A4', 'Miscellaneous glyphs')
        //          ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

       
       $objPHPExcel->getActiveSheet()->setTitle('Enrollment List');
       


       $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

       // Redirect output to a client’s web browser (Excel2007)
       header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');      
       header('Content-Disposition: attachment;filename="ched_enrollment_list'.strtolower($program['strProgramCode']).$active_sem['enumSem'].$active_sem['strYearStart'].$active_sem['strYearEnd'].'.xls"');
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

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:K2');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:K3');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A4:K4');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A5:K5');
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:K6');
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
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:K2")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:K5")->getFont()->setBold( true );
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A2:K2")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A3:K3")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A4:K4")->applyFromArray($style_right);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A5:K5")->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("A6:K6")->applyFromArray($style);

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
                    ->setCellValue('F8', 'Room')
                    ->setCellValue('G8', 'Day')
                    ->setCellValue('H8', 'Time')
                    ->setCellValue('I8', 'Grade')
                    ->setCellValue('J8', 'Professor')
                    ->setCellValue('K8', 'Date Enrolled');
                    
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
                    ->setCellValue('F'.$i, $cl['sched_room'])
                    ->setCellValue('G'.$i, $cl['sched_day'])
                    ->setCellValue('H'.$i, $cl['sched_time'])
                    ->setCellValue('I'.$i, $cl['v3'])
                    ->setCellValue('J'.$i, $cl['strLastname'].", ".$cl['strFirstname'])
                    ->setCellValue('K'.$i, date("M j, Y",strtotime($student['dteRegistered'])));                                                                                                                                                
                    
            
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle("F".$i)->applyFromArray($style_left);
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);        
        

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
    
    public function download_students($course = 0,$regular= 0, $year=0,$gender = 0,$graduate=0,$scholarship=0,$registered=0,$sem = 0)
    {
        
        $students = $this->data_fetcher->getStudents($course,$regular,$year,$gender,$graduate,$scholarship,$registered,$sem);
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
    //    // $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    //     //$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
    //     $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);        
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
         header('Content-Disposition: attachment;filename="student_data'.$date.'.xls"');
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
        //print_r($students);
        //die();
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

    public function enlisted_students($course,$year,$gender,$sem){
        
        if($sem == 0)      
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);

        $this->data['sy'] = $active_sem;
        $students = $this->data_fetcher->getClassListStudentsEnlistedOnly(0,$active_sem['intID'],$course,$year,$gender);
        
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
        $programs = $this->data_fetcher->fetch_table('tb_mas_programs');
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
        
        foreach($enrollment as $item){
            $major = ($item['strMajor'] != "None" && $item['strMajor'] != "")?'Major in '.$item['strMajor']:''; 
            $all_enrolled +=  $item['enrolled_freshman'] + $item['enrolled_transferee'] + $item['enrolled_foreign'] + $item['enrolled_second'];
                    
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, trim($item['strProgramDescription']))
                    ->setCellValue('B'.$i, $item['enrolled_freshman'])
                    ->setCellValue('C'.$i, $item['enrolled_transferee'])
                    ->setCellValue('D'.$i, $item['enrolled_foreign'])
                    ->setCellValue('E'.$i, $item['enrolled_second'])
                    ->setCellValue('F'.$i, '=SUM(B'.$i.':E'.$i.')');                                                
                             
        
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

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:F1');

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('A3', 'Program')
                    ->setCellValue('B3', 'Freshman')
                    ->setCellValue('C3', 'Transferee')
                    ->setCellValue('D3', 'Second Degree')                    
                    ->setCellValue('E3', 'Total Enrollment');
                            
        $i = 4;
        
        
        foreach($dates as $item){  
            
                    
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $item->date)
                    ->setCellValue('B'.$i, $item->freshman)
                    ->setCellValue('C'.$i, $item->transferee)                    
                    ->setCellValue('D'.$i, $item->second)
                    ->setCellValue('E'.$i, '=SUM(B'.$i.':D'.$i.')');                                                
                             
        
            $i++;
         
        }

        $objPHPExcel->setActiveSheetIndex(0)                    
                    ->setCellValue('B'.$i, '=SUM(B4:B'.($i-1).')')
                    ->setCellValue('C'.$i, '=SUM(C4:C'.($i-1).')')                    
                    ->setCellValue('D'.$i, '=SUM(D4:D'.($i-1).')')
                    ->setCellValue('E'.$i, '=SUM(E4:E'.($i-1).')');                    
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('F'.$i)->getFont()->setBold( true );                    
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A3:E3')->getFont()->setBold( true );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        
                
         
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
                    ->setCellValue('N1', 'Mother')
                    ->setCellValue('O1', 'Contact #')
                    ->setCellValue('P1', 'Guardian')
                    ->setCellValue('Q1', 'Contact #')
                    ->setCellValue('R1', 'Date');
                    
        
        $i = 2;
        
        foreach($data as $d){                         

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
                    ->setCellValue('N'.$i, $d->mother_name)
                    ->setCellValue('O'.$i, $d->mother_contact)
                    ->setCellValue('P'.$i, $d->guardian_name)
                    ->setCellValue('Q'.$i, $d->guardian_contact)
                    ->setCellValue('R'.$i, $d->datestamp);
                                                       
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
                    ->setCellValue('A1', 'Cashier')
                    ->setCellValue('B1', 'Date')
                    ->setCellValue('C1', 'OR Number')
                    ->setCellValue('D1', 'Applicant Number')
                    ->setCellValue('E1', 'Name')
                    ->setCellValue('F1', 'Payment Mode')
                    ->setCellValue('G1', 'Check/CC/Debit #')
                    ->setCellValue('H1', 'Amount Paid')
                    ->setCellValue('I1', 'Payment For')
                    ->setCellValue('J1', 'Remarks');
                    
        
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
            switch($d->is_cash){
                case 0:
                    $mode = "Check";
                    break;
                case 1:
                    $mode = "Cash";
                    break;
                case 2:
                    $mode = "Credit Card";
                    break;
                case 3:
                    $mode = "Debit Card";
                    break;  
                case 4:     
                    $mode = "Online";
                    break;                   

            }

            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'.$i, $cashier_name)
                    ->setCellValue('B'.$i, $d->updated_at)
                    ->setCellValue('C'.$i, $or_number)
                    ->setCellValue('D'.$i, $d->student_information_id)
                    ->setCellValue('E'.$i, strtoupper($d->student_name))
                    ->setCellValue('F'.$i, $mode)
                    ->setCellValue('G'.$i, $d->check_number)
                    ->setCellValue('H'.$i, $d->subtotal_order)
                    ->setCellValue('I'.$i, $d->description)
                    ->setCellValue('J'.$i, $d->remarks);
                                                       
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
                
         
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
    
}