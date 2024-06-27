<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// require_once('src/facebook.php');

class Datatables extends CI_Controller {

	public function __construct()
	{
		
        parent::__construct();
		
        
        //$this->load->model("user_model");
        //$this->config->load('courses');
        $this->data["user"] = $this->session->all_userdata();
        $this->load->helper("cms_form");
		
	}
    
    public function sentItemsDatasentItemsData()
    {
        
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        
		
        $aColumns = array('intMessageUserID','strFirstName','strLastName','strSubject', 'dteDate','intRead');
        $sIndexColumn = "intMessageUserID";
        $sTable = 'tb_mas_message_user';
        $table = $sTable;
        $user = $this->session->userdata('intID');
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart']).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength'] );
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "WHERE $table.intFacultyIDSender LIKE '".$user."' AND intTrash = 0 ";
        
        
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            
                $sWhere .= "AND (";
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }

        
        $join = "JOIN tb_mas_faculty ON tb_mas_faculty.intID = tb_mas_message_user.intFacultyID ";
        $join .= "JOIN tb_mas_system_message ON tb_mas_system_message.intID = tb_mas_message_user.intMessageID ";
        
        $groupBy = "GROUP BY intMessageID ";

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $join
            $sWhere
            $groupBy
            $sOrder
            $sLimit
            
        ";
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "strFirstName" && $table == 'tb_mas_message_user')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = '<label><input rel="'.$aRow->{$aColumns[0]}.'" type="checkbox" class="minimal message-check"/></label> '.$aRow->{$aColumns[$i]}." ".$aRow->{$aColumns[$i+1]};
                }
                else if ( $aColumns[$i] == "strLastName" && $table == 'tb_mas_message_user')
                {
                    
                }
                else if($aColumns[$i] == 'dteDate' && $table == 'tb_mas_message_user')
                {
                    $row[] = time_lapsed($aRow->{$aColumns[$i]});
                }
                else if($aColumns[$i] == 'strSubject' && $table == 'tb_mas_message_user')
                {
                    $row[] = "<a href='".base_url()."messages/view_message/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."</a>";
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte')
                {
                    $row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );
    }
        
    
    public function getStudentsTableRegistered()
    {
        
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        $aColumns = array("intID","intRegistrationID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding");
        //$aColumns = array("intID","strFullName","strCourse","strSection");
        $sIndexColumn = "intID";
        $sTable = "tb_mas_users";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
                $sWhere = "WHERE (";
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
        $active_sem = $this->data_fetcher->get_active_sem();
        
        $sJoin = " JOIN tb_mas_registration ON tb_mas_users.intID = tb_mas_registration.intStudentID AND tb_mas_registration.intAYID = ".$active_sem['intID']." ";
        
        $sJoin .= " JOIN tb_mas_programs ON tb_mas_users.intProgramID = tb_mas_programs.intProgramID ";
        
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "strLastname")
                {
                    /* Special output formatting for 'version' column */
                    $row[] = "<a href='".base_url()."unity/student_viewer/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."  ".$aRow->{$aColumns[$i+1]}." ".$aRow->{$aColumns[$i+2]};
                }
                else if ( $aColumns[$i] == "strFirstname" || $aColumns[$i] == "strMiddlename")
                {
                    
                }
                else if($aColumns[$i] == 'dteDate' && $table == 'tb_mas_message_user')
                {
                    $row[] = time_lapsed($aRow->{$aColumns[$i]});
                }
                else if($aColumns[$i] == 'strSubject' && $table == 'tb_mas_message_user')
                {
                    $row[] = "<a href='".base_url()."messages/view_message/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."</a>";
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte')
                {
                    $row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );
    }
    
     public function view_advised_students($program)
    {
        
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        //array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding");
        

        $aColumns = array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAdmissionStatus");
        // $aColumns = array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","intROG");
        //$aColumns = array("intID","strFullName","strCourse","strSection");
        $sIndexColumn = "intID";
        $sTable = "tb_mas_users";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        if($program != 0)
            $sWhere = "WHERE tb_mas_users.intProgramID = ".$program;
        else
            $sWhere = "";
         
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE (";
            }
            else
            {
                $sWhere .= " AND (";
            }
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
        $active_sem = $this->data_fetcher->get_active_sem();
        
        $sJoin = " JOIN tb_mas_advised ON tb_mas_advised.intStudentID = tb_mas_users.intID AND tb_mas_advised.intSYID = ".$active_sem['intID']." ";
        
        $sJoin .= " JOIN tb_mas_programs ON tb_mas_users.intProgramID = tb_mas_programs.intProgramID ";
        $sJoin .= " LEFT JOIN tb_mas_registration ON tb_mas_users.intID = tb_mas_registration.intStudentID AND tb_mas_registration.intAYID = ".$active_sem['intID']." ";
        $sJoin .= " LEFT JOIN tb_mas_statusadmission ON tb_mas_registration.intROG = tb_mas_statusadmission.intROG
        AND tb_mas_registration.intAYID = ".$active_sem['intID']." ";
         //CASE WHEN tb_mas_registration.intROG IS NULL THEN -1 ELSE tb_mas_registration.intROG END AS tb_mas_registration.intROG
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "strLastname")
                {
                    /* Special output formatting for 'version' column */
                    $row[] = "<a href='".base_url()."unity/student_viewer/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}." ".$aRow->{$aColumns[$i+1]}." ".$aRow->{$aColumns[$i+2]};
                }
                else if ( $aColumns[$i] == "strFirstname" || $aColumns[$i] == "strMiddlename")
                {
                    
                }
                else if($aColumns[$i] == 'dteDate' && $table == 'tb_mas_message_user')
                {
                    $row[] = time_lapsed($aRow->{$aColumns[$i]});
                }
                else if($aColumns[$i] == 'strSubject' && $table == 'tb_mas_message_user')
                {
                    $row[] = "<a href='".base_url()."messages/view_message/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."</a>";
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte')
                {
                    $row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );
    }
    
    public function data_tables_ajax_cs($sem = 0, $program = 0, $dissolved = 0, $has_faculty = 0)
    {
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        //array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding");
        

        $aColumns = array("tb_mas_classlist.intID","strProgramCode","strCode","strClassName","year","strSection","sub_section","slots","strLastname","strFirstname","intFinalized");
        // $aColumns = array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","intROG");
        //$aColumns = array("intID","strFullName","strCourse","strSection");
        $sIndexColumn = "intID";
        $sTable = "tb_mas_classlist";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
       
       $sWhere = "WHERE $sTable.strAcademicYear = ".$active_sem['intID']." ";
       $sWhere .= "AND $sTable.isDissolved = ".$dissolved." ";
       if($has_faculty > 0)
        $sWhere .= "AND $sTable.intFacultyID != 999 ";
       if($program != 0)
        $sWhere .= " AND tb_mas_programs.intProgramID = $program ";
            
        
       if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE (";
            }
            else
            {
                $sWhere .= " AND (";
            }            
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
       
        
        $sJoin = "JOIN tb_mas_faculty ON tb_mas_classlist.intFacultyID = tb_mas_faculty.intID ";
        //$sJoin .= "LEFT JOIN tb_mas_classlist_student ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        $sJoin .= "JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID ";
        $sJoin .= "JOIN tb_mas_curriculum ON tb_mas_classlist.intCurriculumID = tb_mas_curriculum.intID ";
        $sJoin .= "JOIN tb_mas_programs ON tb_mas_curriculum.intProgramID = tb_mas_programs.intProgramID ";
      
         //CASE WHEN tb_mas_registration.intROG IS NULL THEN -1 ELSE tb_mas_registration.intROG END AS tb_mas_registration.intROG
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
       
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult->result() as $aRow)
        {
            $slots_taken_enrolled = $this->db
                                ->select('tb_mas_classlist_student.intCSID')                                
                                ->from('tb_mas_classlist_student')
                                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                                ->where(array('intClassListID'=>$aRow->intID,'intROG >='=>1,'tb_mas_registration.intAYID'=>$active_sem['intID']))
                                ->get()
                                ->num_rows();
            $slots_taken_enlisted = $this->db
                                ->select('tb_mas_classlist_student.intCSID')                                
                                ->from('tb_mas_classlist_student')
                                ->join('tb_mas_registration','tb_mas_classlist_student.intStudentID = tb_mas_registration.intStudentID')                                                                
                                ->where(array('intClassListID'=>$aRow->intID,'intROG'=>0,'tb_mas_registration.intAYID'=>$active_sem['intID']))
                                ->get()
                                ->num_rows();

            
            
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                
              
                if($aColumns[$i] == 'dteDate' && $table == 'tb_mas_message_user')
                {
                    $row[] = time_lapsed($aRow->{$aColumns[$i]});
                }                
                else if($aColumns[$i] == 'strCode'){
                    $row[] = "<a href='".base_url()."unity/classlist_viewer/".$aRow->intID."'>".$aRow->{$aColumns[$i]}."</a>";
                }
                else if($aColumns[$i] == 'strLastname'){
                    $row[] = $aRow->{$aColumns[$i+1]}." ".$aRow->{$aColumns[$i]};
                }
                else if($aColumns[$i] == 'strFirstname'){

                }
                else if($aColumns[$i] == 'slots'){
                    $row[] = $slots_taken_enrolled;
                    $row[] = $slots_taken_enlisted;
                    $row[] = $aRow->{$aColumns[$i]} - $slots_taken_enlisted - $slots_taken_enrolled;
                }                
                else if(substr($aColumns[$i], 0, 3) == 'dte')
                {
                    $row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ')
                {
                    if(strpos($aColumns[$i],".") !== false)
                    {
                        $st = explode(".",$aColumns[$i]);
                        $row[] = $aRow->{$st[1]};
                    }
                    else
                        $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );
    }


    public function data_tables_ajax_teaching($sem = 0){

        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        //array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding");
        
        $faculty_id = $this->session->userdata("intID");
        //$aColumns = array("tb_mas_classlist.intID","strClassName","strSection","strLastname","strFirstname","intFinalized");
        $aColumns = array("intID","strFirstname","strLastname");
        $sIndexColumn = "intID";
        $sTable = "tb_mas_faculty";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
       $sWhere = "WHERE $sTable.teaching = 1 ";
       
        
       if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE (";
            }
            else
            {
                $sWhere .= " AND (";
            }
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
        $sJoin = "";           
        //$sJoin .= "LEFT JOIN tb_mas_classlist_student ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        
        
        //$groupBy = "GROUP BY intID ";
        
         //CASE WHEN tb_mas_registration.intROG IS NULL THEN -1 ELSE tb_mas_registration.intROG END AS tb_mas_registration.intROG
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
       
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        
        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {              
                
                $row[] = $aRow->{$aColumns[$i]};                                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );

    }

    public function data_tables_ajax_enlistment($course = 0,$yearlevel=0,$gender=0,$sem=0,$start=0,$end=0){

        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $this->config->load('data-tables');		
		
        $aColumns = array("intID","slug","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intYearLevel","date_enlisted");
        $sIndexColumn = $this->config->item('tb_mas_users_index');
        $sTable = 'tb_mas_users';
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        $sGroup = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart']).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i]) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
     
        
       
        if($gender!=0){           
                if($gender == 1)
                    $sWhere .= "WHERE enumGender = 'male' ";
                else
                    $sWhere .= "WHERE enumGender = 'female' ";
            
        }        
        
        if($yearlevel!=0)
            if($gender!=0 )
                $sWhere .= "AND tb_mas_registration.intYearLevel = ".$yearlevel." ";
            else
                $sWhere .= "WHERE tb_mas_registration.intYearLevel = ".$yearlevel." ";


      
        
        
        if($course!=0)
            if($gender!=0 || $yearlevel!=0 )
                $sWhere .= "AND tb_mas_users.intProgramID = '".$course."' ";
            else
                $sWhere .= "WHERE tb_mas_users.intProgramID = '".$course."' ";
       
        
        if($sem == 0){
            $active_sem = $this->data_fetcher->get_active_sem();
            $sem = $active_sem['intID'];
        }
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
            
        
        if($gender!=0 || $yearlevel!=0 || $course!=0 )
            $sWhere .= "AND level = 'college' ";    
        else
            $sWhere .= "WHERE level = 'college' ";

        
        $sWhere .= "AND tb_mas_registration.intAYID = ".$sem." AND tb_mas_registration.intROG = 0 ";
        if($start != 0){
            $sWhere .="AND date_enlisted >='".$start."' AND date_enlisted <='".$end."' ";
        }
                   
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if($user==null || ($trashed!=null && $table == 'tb_mas_message_user') )
                $sWhere = "WHERE (";
            elseif($table == 'tb_mas_applications')
            {
                $sWhere = "WHERE (";
            }
            else
                $sWhere .= "AND (";
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if(($aColumns[$i] != "strFirstname" && $aColumns[$i] != "strLastname") || $table != 'tb_mas_users')
                    $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
                else
                    $sWhere .= "CONCAT_WS(' ',strFirstname,strLastname,strMiddlename) LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'])."%' OR ";
                
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
            
        }
        
        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }                
               
                if($table == "tb_mas_users" && $i > 3)
                    $col = $i + 2;
                else
                    $col = $i;
                
                if($table == "tb_mas_users" && $i  == 2){                        
                    $st = "";
                    $ct = 0;
                    $str = str_split($_GET['sSearch_'.$i]);
                    foreach($str as $letter){
                        if($ct == 5 || $ct == 7)
                            $st .= "-";
                        $st .= $letter;
                        $ct++;
                    }                    
                    
                    $_GET['sSearch_'.$i] =  $st;                    
                }
                    
                $sWhere .= $aColumns[$col]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
                    
        
                    
            }
        }

        
        $join = " JOIN tb_mas_programs ON tb_mas_users.intProgramID = tb_mas_programs.intProgramID ";            
        $join .= "JOIN tb_mas_registration ON tb_mas_users.intID = tb_mas_registration.intStudentID ";
        
        
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)). 
            " FROM $sTable
            $join
            $sWhere
            $sGroup
            $sOrder
            $sLimit
            
            
        ";
        
        $rResult = $this->db->query($sQuery);
        

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;

        

        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $output['query'] = $sWhere;
        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "strLastname")
                {
                    /* Special output formatting for 'version' column */
                    $row[] = "<a href='".base_url()."unity/student_viewer/".$aRow->{$aColumns[0]}."'>".strtoupper($aRow->{$aColumns[$i]}."  ".$aRow->{$aColumns[$i+1]}." ".$aRow->{$aColumns[$i+2]})."</a>";
                }
                else if ( $aColumns[$i] == "strStudentNumber")
                {
                    /* Special output formatting for 'version' column */
                    $row[] = preg_replace("/[^a-zA-Z0-9]+/", "", $aRow->{$aColumns[$i]});
                }
                else if ( ($aColumns[$i] == "strFirstname" || $aColumns[$i] == "strMiddlename"))
                {
                    
                } 
                else if ( $aColumns[$i] == "date_enlisted")
                {
                    $row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                }               
                else if ( $aColumns[$i] != ' ' )
                {
                    if(strpos($aColumns[$i], ".") !== false){
                        $new = explode(".",$aColumns[$i]);
                        $aColumns[$i] = $new[1];
                    }
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
        
	   echo json_encode( $output );
    }
    
    public function data_tables_ajax_cs_faculty($sem = 0)
    {
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        //array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding");
        
        $faculty_id = $this->session->userdata("intID");
        //$aColumns = array("tb_mas_classlist.intID","strClassName","strSection","strLastname","strFirstname","intFinalized");
        $aColumns = array("tb_mas_classlist.intID","strSection","enumSem", "strYearStart", "strYearEnd", "strClassName");
        $sIndexColumn = "intID";
        $sTable = "tb_mas_classlist";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
       $sWhere = "WHERE $sTable.strAcademicYear = ".$active_sem['intID']." ";
       $sWhere .= "AND $sTable.intFacultyID = " . $faculty_id . " ";
        
       if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE (";
            }
            else
            {
                $sWhere .= " AND (";
            }
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
       
            $sJoin = "JOIN tb_mas_subjects ON tb_mas_subjects.intID = tb_mas_classlist.intSubjectID ";
            $sJoin .= "JOIN tb_mas_sy ON tb_mas_sy.intID = tb_mas_classlist.strAcademicYear ";
            //$sJoin .= "LEFT JOIN tb_mas_classlist_student ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        
        
              //$groupBy = "GROUP BY intID ";
        //$sJoin = "JOIN tb_mas_faculty ON tb_mas_classlist.intFacultyID = tb_mas_faculty.intID ";
        //$sJoin .= "LEFT JOIN tb_mas_classlist_student ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        //$sJoin .= "JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID ";
      
         //CASE WHEN tb_mas_registration.intROG IS NULL THEN -1 ELSE tb_mas_registration.intROG END AS tb_mas_registration.intROG
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
       
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        
        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                
                if ( $aColumns[$i] == 'strSection' && $sTable == 'tb_mas_classlist')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = '<label><input name="ids[]" value="'.$aRow->intID.'" type="checkbox" class="minimal message-check"/></label> '.$aRow->{$aColumns[$i]};
                }
                else if ($aColumns[$i] == 'enumSem') {
                    $row[] = $aRow->{$aColumns[$i]} . " sem " . $aRow->{$aColumns[$i+1]}  . "-" . $aRow->{$aColumns[$i+2]};
                }
                else if ($aColumns[$i] == 'strYearStart' || $aColumns[$i] == 'strYearEnd' )
                {
                    
                }
                else if ( $aColumns[$i] != '' )
                {
                    if(strpos($aColumns[$i],".") !== false)
                    {
                        $st = explode(".",$aColumns[$i]);
                        $row[] = $aRow->$st[1];
                    }
                    else
                        $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );
    }

    public function data_tables_ajax_cs_faculty_admin($id, $sem = 0)
    {
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        //array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding");
        
        $faculty_id = $id;
        //$aColumns = array("tb_mas_classlist.intID","strClassName","strSection","strLastname","strFirstname","intFinalized");
        $aColumns = array("tb_mas_classlist.intID","strSection","strClassName", "strDescription","tb_mas_classlist.strUnits", "intFinalized");
        $sIndexColumn = "intID";
        $sTable = "tb_mas_classlist";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
       $sWhere = "WHERE $sTable.strAcademicYear = ".$active_sem['intID']." ";
       $sWhere .= "AND $sTable.intFacultyID = " . $faculty_id . " ";
        
       if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE (";
            }
            else
            {
                $sWhere .= " AND (";
            }
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
       
            $sJoin = "JOIN tb_mas_subjects ON tb_mas_subjects.intID = tb_mas_classlist.intSubjectID ";
            
            $sJoin .= "JOIN tb_mas_sy ON tb_mas_sy.intID = tb_mas_classlist.strAcademicYear ";
            //$sJoin .= "LEFT JOIN tb_mas_classlist_student ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        
        
              //$groupBy = "GROUP BY intID ";
        //$sJoin = "JOIN tb_mas_faculty ON tb_mas_classlist.intFacultyID = tb_mas_faculty.intID ";
        //$sJoin .= "LEFT JOIN tb_mas_classlist_student ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        //$sJoin .= "JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID ";
      
         //CASE WHEN tb_mas_registration.intROG IS NULL THEN -1 ELSE tb_mas_registration.intROG END AS tb_mas_registration.intROG
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
       
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        
        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                
                if ( $aColumns[$i] == 'strSection' && $sTable == 'tb_mas_classlist')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = '<label><input name="ids[]" value="'.$aRow->intID.'" type="checkbox" class="minimal message-check"/></label> '.$aRow->{$aColumns[$i]};
                }
                // else if ($aColumns[$i] == 'enumSem') {
                //     $row[] = $aRow->{$aColumns[$i]} . " sem " . $aRow->{$aColumns[$i+1]}  . "-" . $aRow->{$aColumns[$i+2]};
                // }
                // else if ($aColumns[$i] == 'strYearStart' || $aColumns[$i] == 'strYearEnd' )
                // {
                    
                // }
                
                else if ( $aColumns[$i] != '' )
                {
                    if(strpos($aColumns[$i],".") !== false)
                    {
                        $st = explode(".",$aColumns[$i]);
                        $row[] = $aRow->$st[1];
                    }
                    else
                        $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );
    }

    public function data_tables_completion(){
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */		
		
        $aColumns = array("intCompletionID", "intClasslistStudentID","tb_mas_faculty.strFirstname as facultyFirstname","tb_mas_faculty.strLastname as facultyLastname","tb_mas_users.strFirstname as studentFirstname","tb_mas_users.strLastname as studentLastname","strCode","tb_mas_classlist.strSection","tb_mas_completion.floatNewFinalTermGrade","dteDateOfCompletion","tb_mas_completion.enumStatus","enumSem","strYearStart","strYearEnd");
        //$aColumns = array("intID","strFullName","strCourse","strSection");
        $sIndexColumn = "intCompletionID";
        $sTable = "tb_mas_completion";
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart'] ).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
                $sWhere = "WHERE (";
           
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
            }
        }
        
        $active_sem = $this->data_fetcher->get_active_sem();
        
        $sJoin = " JOIN tb_mas_classlist_student ON tb_mas_completion.intClasslistStudentID = tb_mas_classlist_student.intCSID ";
        
        $sJoin .= " JOIN tb_mas_classlist ON tb_mas_classlist_student.intClassListID = tb_mas_classlist.intID ";
        $sJoin .= " JOIN tb_mas_users ON tb_mas_classlist_student.intStudentID = tb_mas_users.intID ";
        $sJoin .= " JOIN tb_mas_faculty ON tb_mas_classlist.intFacultyID = tb_mas_faculty.intID ";
        $sJoin .= " JOIN tb_mas_subjects ON tb_mas_classlist.intSubjectID = tb_mas_subjects.intID ";
        $sJoin .= " JOIN tb_mas_sy ON tb_mas_classlist.strAcademicYear = tb_mas_sy.intID ";

        
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
            FROM $sTable
            $sJoin
            $sWhere
            $sOrder
            $sLimit
            
        ";
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );             
        foreach ($rResult->result() as $aRow)
        {
            for($i=0;$i<count($aColumns);$i++){
                if($aColumns[$i] ==  "tb_mas_faculty.strFirstname as facultyFirstname"){
                    $aColumns[$i] = "facultyFirstname";
                }
                if($aColumns[$i] ==  "tb_mas_faculty.strLastname as facultyLastname"){
                    $aColumns[$i] = "facultyLastname";
                }
                if($aColumns[$i] ==  "tb_mas_users.strFirstname as studentFirstname"){
                    $aColumns[$i] = "studentFirstname";
                }
                if($aColumns[$i] ==  "tb_mas_users.strLastname as studentLastname"){
                    $aColumns[$i] = "studentLastname";
                }
                if($aColumns[$i] ==  "tb_mas_completion.floatNewFinalTermGrade"){
                    $aColumns[$i] = "floatNewFinalTermGrade";
                }
                if($aColumns[$i] ==  "tb_mas_completion.enumStatus"){
                    $aColumns[$i] = "enumStatus";
                }       
                if($aColumns[$i] == "tb_mas_classlist.strSection"){
                    $aColumns[$i] = "strSection";
                }         
            }
        }
        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "facultyFirstname" || $aColumns[$i] == "studentFirstname")
                {
                    /* Special output formatting for 'version' column */
                    //$firstNameInitial = substr($record['strFirstname'], 0,1);
                    $row[] = substr($aRow->{$aColumns[$i]} , 0, 1) .". ".$aRow->{$aColumns[$i+1]};
                }          
                elseif($aColumns[$i] == "facultyLastname" || $aColumns[$i] == "studentLastname"){

                }
                elseif($aColumns[$i] == "enumSem"){
                    $row[] = $aRow->{$aColumns[$i]}."-".$aRow->{$aColumns[$i+1]}."-".$aRow->{$aColumns[$i+2]};
                }   
                else if(substr($aColumns[$i], 0, 3) == 'dte')
                {
                    //$row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                    $row[] = date("Y-m-d h:i:sa", strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output );        
    }
    
    public function data_tables_ajax($table,$user=null,$trashed=null,$course=0,$astatus=0,$yearlevel=0,$gender=0,$graduate=0,$scholarship=0,$registered=0,$sem=0,$filter_section=0,$level=0)
    {
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $this->config->load('data-tables');		
		
        $aColumns = $this->config->item($table.'_columns');
        $sIndexColumn = $this->config->item($table.'_index');
        $sTable = $table;
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        $sGroup = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart']).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i]) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if($user!=null && $table !='tb_mas_users' && $table!='tb_mas_room_schedule' && $table!='tb_mas_applications')
        //if($user!=null && $table !='tb_mas_registration' && $table!='tb_mas_room_schedule')
            $sWhere = "WHERE $table.intFacultyID LIKE '".$user."' ";

        if($table =='tb_mas_room_schedule' && $filter_section!=0)
        //if($user!=null && $table !='tb_mas_registration' && $table!='tb_mas_room_schedule')
            $sWhere = "WHERE $table.blockSectionID = '".$filter_section."' ";
        
        // if($scholarship!=0 && $table =='tb_mas_users')
        // //if($scholarship!=0 && $table =='tb_mas_registration')
        //     if($scholarship == 1)
        //         $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'paying' ";
        //     elseif($scholarship == 2)
        //         $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'resident scholar' ";
        //     elseif($scholarship == 3)
        //             $sWhere .= "WHERE tb_mas_registration.enumScholarship = '7th district' ";
        //     elseif($scholarship == 4)
        //             $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'DILG scholar' ";
        //     elseif($scholarship == 5) 
        //             $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'tagaytay resident' ";
        //     elseif($scholarship == 6)
        //             $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'FREE HIGHER EDUCATION PROGRAM (R.A. 10931)' ";
        
        // if($astatus!=0  && $table =='tb_mas_users'){
        //     if($scholarship!=0)
        //         if($astatus == 1)
        //             $sWhere .= "AND $table.strAcademicStanding = 'regular' ";
        //         elseif($astatus == 2)
        //             $sWhere .= "AND $table.strAcademicStanding = 'irregular' ";
        //         else
        //             $sWhere .= "AND $table.strAcademicStanding = 'new' ";
        //     else
        //         if($astatus == 1)
        //             $sWhere .= "WHERE $table.strAcademicStanding = 'regular' ";
        //         elseif($astatus == 2)
        //             $sWhere .= "WHERE $table.strAcademicStanding = 'irregular' ";
        //         else
        //             $sWhere .= "WHERE $table.strAcademicStanding = 'new' ";
        // }
        
        if($gender!=0  && $table =='tb_mas_users'){
            if($astatus != 0 || $scholarship!=0)
                if($gender == 1)
                    $sWhere .= "AND $table.enumGender = 'male' ";
                else
                    $sWhere .= "AND $table.enumGender = 'female' ";
            else
                if($gender == 1)
                    $sWhere .= "WHERE $table.enumGender = 'male' ";
                else
                    $sWhere .= "WHERE $table.enumGender = 'female' ";
            
        }
        if($graduate!=0  && $table =='tb_mas_users'){
            if($astatus != 0 || $gender != 0 || $scholarship!=0)
                if($graduate == 1)
                    $sWhere .= "AND $table.isGraduate = 1 ";
                else
                    $sWhere .= "AND $table.isGraduate = 0 ";
            else
                if($graduate == 1)
                    $sWhere .= "WHERE $table.isGraduate = 1 ";
                else
                    $sWhere .= "WHERE $table.isGraduate = 0 ";
            
        }
        
        if($yearlevel!=0 && $table =='tb_mas_users')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $scholarship!=0)
                $sWhere .= "AND tb_mas_registration.intYearLevel = ".$yearlevel." ";
            else
                $sWhere .= "WHERE tb_mas_registration.intYearLevel = ".$yearlevel." ";
        
        
        if($course!=0 && $table =='tb_mas_users')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $yearlevel!=0 || $scholarship!=0 )
                $sWhere .= "AND $table.intProgramID = '".$course."' ";
            else
                $sWhere .= "WHERE $table.intProgramID = '".$course."' ";
       
        
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        if($registered != 0 && $table =='tb_mas_users')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $yearlevel!=0 || $scholarship!=0 || $course!=0 || $filter_section != 0 )
            {
                switch($registered)
                {                   
                    case 1:
                    $sWhere .= "AND tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 0 ";
                    break;
                    case 2:
                    $sWhere .= "AND tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 1 ";
                    break;
                    case 3:
                    $sWhere .= "AND tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 2 ";
                    break;
                }
            }
            else
            {
                switch($registered){
                    case 1:
                        $sWhere .= "WHERE tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 0 ";
                    break;
                    case 2:
                        $sWhere .= "WHERE tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 1 ";
                    break;
                    case 3:
                        $sWhere .= "WHERE tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 2 ";
                    break;
                    
                }
            }
            
        if($level != 0 && $table =='tb_mas_users'){
            if($registered != 0 || $gender!=0 || $astatus!=0 || $graduate!=0 || $yearlevel!=0 || $scholarship!=0 || $course!=0 || $filter_section != 0 )
            {
                switch($level){
                    case 1:
                        $sWhere .= "AND ".$table.".level = 'shs' ";
                    break;
                    case 2:
                        $sWhere .= "AND ".$table.".level = 'college' ";
                    break;
                    case 3:
                        $sWhere .= "AND ".$table.".level = 'sd' ";
                    break;
                    case 4:
                        $sWhere .= "AND ".$table.".level = 'drive' ";
                    break;
                }
            }
            else
            {
                switch($level){
                    case 1:
                        $sWhere .= "WHERE ".$table.".level = 'shs' ";
                    break;
                    case 2:
                        $sWhere .= "WHERE ".$table.".level = 'college' ";
                    break;
                    case 3:
                        $sWhere .= "WHERE ".$table.".level = 'sd' ";
                    break;
                    case 4:
                        $sWhere .= "WHERE ".$table.".level = 'drive' ";
                    break;
                }
            }
            
        }
        
        
        if($student_type != 0 && $table =='tb_mas_users'){
            if($registered != 0 || $gender!=0 || $astatus!=0 || $graduate!=0 || $yearlevel!=0 || $scholarship!=0 || $course!=0 || $filter_section != 0 || $level != 0 )
            {
                switch($student_type){
                    case 1:
                        $sWhere .= "AND ".$table.".student_type = 'shs' ";
                    break;
                    case 2:
                        $sWhere .= "AND ".$table.".student_type = 'college' ";
                    break;                   
                }
            }
            else
            {
                switch($student_type){
                    case 1:
                        $sWhere .= "WHERE ".$table.".student_type = 'shs' ";
                    break;
                    case 2:
                        $sWhere .= "WHERE ".$table.".student_type = 'college' ";
                    break;                    
                }
            }
            
        }

        if($sem!=0 && $table =='tb_mas_room_schedule')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $registered != 0 || $yearlevel!=0 || $scholarship!=0 || $course!=0 || $filter_section != 0)
                $sWhere .= "AND tb_mas_room_schedule.intSem = ".$active_sem['intID']." ";
            else
                $sWhere .= "WHERE tb_mas_room_schedule.intSem = ".$active_sem['intID']." ";

        
            
        if($trashed!=null && $table == 'tb_mas_message_user')
        {
            if($user == null)
                $sWhere .="WHERE ";
            
            $sWhere .= "AND $table.intTrash ='".$trashed."' ";
        }
        
        
        
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if($user==null || ($trashed!=null && $table == 'tb_mas_message_user') )
                $sWhere = "WHERE (";
            elseif($table == 'tb_mas_applications')
            {
                $sWhere = "WHERE (";
            }
            else
                $sWhere .= "AND (";
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if(($aColumns[$i] != "strFirstname" && $aColumns[$i] != "strLastname") || $table != 'tb_mas_users')
                    $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
                else
                    $sWhere .= "CONCAT_WS(' ',strFirstname,strLastname,strMiddlename) LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'])."%' OR ";
                
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
            
        }
        
        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }                
               
                if($table == "tb_mas_users" && $i > 3)
                    $col = $i + 2;
                else
                    $col = $i;
                
                if($table == "tb_mas_users" && $i  == 2){                        
                    $st = "";
                    $ct = 0;
                    $str = str_split($_GET['sSearch_'.$i]);
                    foreach($str as $letter){
                        if($ct == 5 || $ct == 7)
                            $st .= "-";
                        $st .= $letter;
                        $ct++;
                    }                    
                    
                    $_GET['sSearch_'.$i] =  $st;                    
                }
                    
                $sWhere .= $aColumns[$col]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
                    
        
                    
            }
        }

        if($registered == -1){
            if($sWhere)
                $sWhere .= "AND tb_mas_advised.intStudentID  NOT IN (SELECT tb_mas_registration.`intStudentID` FROM tb_mas_registration WHERE tb_mas_registration.`intAYID`=".$active_sem['intID'].") ";
            else
                $sWhere = "WHERE tb_mas_advised.intStudentID  NOT IN (SELECT tb_mas_registration.`intStudentID` FROM tb_mas_registration WHERE tb_mas_registration.`intAYID`=".$active_sem['intID'].") ";
        }
        $join = "";
        
        if($table == 'tb_mas_users')
        {
            $join = " JOIN tb_mas_programs ON tb_mas_users.intProgramID = tb_mas_programs.intProgramID ";            
            
            $join .= "LEFT JOIN tb_mas_registration ON tb_mas_users.intID = tb_mas_registration.intStudentID ";
        }
        if($registered == -1){
            $join .= "JOIN tb_mas_advised ON (tb_mas_users.intID = tb_mas_advised.intStudentID AND tb_mas_advised.intSYID = ".$active_sem['intID'].") ";            
        }
        
        if($table == 'tb_mas_message_user')
        {
            $join = "JOIN tb_mas_faculty ON tb_mas_faculty.intID = tb_mas_message_user.intFacultyIDSender ";
            $join .= "JOIN tb_mas_system_message ON tb_mas_system_message.intID = tb_mas_message_user.intMessageID ";
        }        
        if($table == 'tb_mas_scholarships')
        {
            $join = "JOIN tb_mas_faculty ON tb_mas_faculty.intID = tb_mas_scholarships.created_by_id ";            
        }
        if($table == 'tb_mas_room_schedule')
        {
            $join = "JOIN tb_mas_classrooms ON tb_mas_classrooms.intID = tb_mas_room_schedule.intRoomID ";
            $join .= "JOIN tb_mas_classlist ON tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode ";
            $join .= "JOIN tb_mas_subjects ON tb_mas_subjects.intID = tb_mas_classlist.intSubjectID ";
            $join .= "JOIN tb_mas_block_sections ON tb_mas_block_sections.intID = tb_mas_room_schedule.blockSectionID ";
        }
        if($table == 'tb_mas_curriculum')
        {
            $join = "LEFT JOIN tb_mas_programs ON tb_mas_programs.intProgramID = tb_mas_curriculum.intProgramID ";
        }
        if($table == 'tb_mas_block_sections')
        {
            $join = "JOIN tb_mas_programs ON tb_mas_programs.intProgramID = tb_mas_block_sections.intProgramID ";
        }
        if($table == 'tb_mas_faculty')
        {
            if($sWhere == "")
            $sWhere = "WHERE intID != 999 ";
            else
            $sWhere .= "AND intID != 999 ";
        }
        if($table == 'tb_mas_questions')
        {
            $join = "JOIN tb_mas_exam ON tb_mas_questions.exam_id = tb_mas_exam.intID ";
            $join .= "LEFT JOIN tb_mas_choices ON (SELECT question_id WHERE is_correct = '1') = tb_mas_questions.intID";
        }
        if($table == 'tb_mas_student_exam')
        {
            $join = "JOIN tb_mas_sy ON tb_mas_sy.intID = tb_mas_student_exam.syid ";
            $join = "JOIN tb_mas_exam ON tb_mas_exam.intID = tb_mas_student_exam.exam_id ";
        }

        if($registered == -1)
            $sGroup = "GROUP BY tb_mas_advised.intStudentID ";

        if($table == "tb_mas_users")
            $sGroup = "GROUP BY tb_mas_users.intID ";
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)). 
            " FROM $sTable
            $join
            $sWhere
            $sGroup
            $sOrder
            $sLimit
            
            
        ";
        
        $rResult = $this->db->query($sQuery);
        

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;

        

        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $output['query'] = $sWhere;
        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "strLastname" && $table == 'tb_mas_users')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = strtoupper($aRow->{$aColumns[$i]}."  ".$aRow->{$aColumns[$i+1]}." ".$aRow->{$aColumns[$i+2]});
                }
                else if ( $aColumns[$i] == "strStudentNumber" && $table == 'tb_mas_users')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = preg_replace("/[^a-zA-Z0-9]+/", "", $aRow->{$aColumns[$i]});
                }
                else if ( ($aColumns[$i] == "strFirstname" || $aColumns[$i] == "strMiddlename") && $table == 'tb_mas_users')
                {
                    
                }
                else if ( $aColumns[$i] == "strLastname" && $table == 'tb_mas_applications')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = "<a href='".base_url()."admissions/view_applicant/".$aRow->{$aColumns[0]}."'>".strtoupper($aRow->{$aColumns[$i]}).",  ".strtoupper($aRow->{$aColumns[$i+1]})." ".strtoupper($aRow->{$aColumns[$i+2]});
                }
                else if ( ($aColumns[$i] == "strFirstname" || $aColumns[$i] == "strMiddlename") && $table == 'tb_mas_applications')
                {
                    
                }
                else if ( $aColumns[$i] == "strFirstName" && $table == 'tb_mas_message_user')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = '<label><input rel="'.$aRow->{$aColumns[0]}.'" type="checkbox" class="minimal message-check"/></label> '.$aRow->{$aColumns[$i]}." ".$aRow->{$aColumns[$i+1]};
                }
                else if ( $aColumns[$i] == "strLastName" && $table == 'tb_mas_message_user')
                {
                    
                }
                else if ( $aColumns[$i] == "intUserLevel" && $table == 'tb_mas_faculty')
                {
                    $row[] = switch_user_level($aRow->{$aColumns[$i]}); 
                }
                else if($aColumns[$i] == 'dteDate' && $table == 'tb_mas_message_user')
                {
                    $row[] = time_lapsed($aRow->{$aColumns[$i]});
                }
                else if($aColumns[$i] == 'strSubject' && $table == 'tb_mas_message_user')
                {
                    $row[] = "<a href='".base_url()."messages/view_message/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."</a>";
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte' && $table != 'tb_mas_room_schedule')
                {
                    $row[] = date("M j, Y",strtotime($aRow->{$aColumns[$i]}));
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte' && $table == 'tb_mas_room_schedule')
                {
                    $row[] = date("g:ia",strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    if(strpos($aColumns[$i], ".") !== false){
                        $new = explode(".",$aColumns[$i]);
                        $aColumns[$i] = $new[1];
                    }
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
        
	   echo json_encode( $output );
    }
    

  public function data_tables_ajax2($table,$user=null,$trashed=null,$course=0,$astatus=0,$yearlevel=0,$gender=0,$graduate=0,$scholarship=0,$registered=0,$sem=0)
    {
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * TABLE CONFIG
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $this->config->load('data-tables');		
		
        $aColumns = $this->config->item($table.'_columns2');
        $sIndexColumn = $this->config->item($table.'_index');
        $sTable = $table;
        
       
        
        /* 
         * Paging
         */
        $sLimit = "";
        $sOrder = "";
        
        
        
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayStart']).", ".
                mysqli_real_escape_string($this->db->conn_id,$_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                        ".mysqli_real_escape_string($this->db->conn_id,$_GET['sSortDir_'.$i]) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if($user!=null && $table !='tb_mas_users' && $table!='tb_mas_room_schedule')
        //if($user!=null && $table !='tb_mas_registration' && $table!='tb_mas_room_schedule')
            $sWhere = "WHERE $table.intFacultyID LIKE '".$user."' ";
        
        if($scholarship!=0 && $table =='tb_mas_users')
        //if($scholarship!=0 && $table =='tb_mas_registration')
            if($scholarship == 1)
                $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'paying' ";
            elseif($scholarship == 2)
                $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'resident scholar' ";
            elseif($scholarship == 3)
                    $sWhere .= "WHERE tb_mas_registration.enumScholarship = '7th district' ";
            elseif($scholarship == 4)
                    $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'DILG scholar' ";
            elseif($scholarship == 5) 
                    $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'tagaytay resident' ";
            elseif($scholarship == 6)
                    $sWhere .= "WHERE tb_mas_registration.enumScholarship = 'FREE HIGHER EDUCATION PROGRAM (R.A. 10931)' ";
        
        if($astatus!=0  && $table =='tb_mas_users'){
            if($scholarship!=0)
                if($astatus == 1)
                    $sWhere .= "AND $table.strAcademicStanding = 'regular' ";
                elseif($astatus == 2)
                    $sWhere .= "AND $table.strAcademicStanding = 'irregular' ";
                else
                    $sWhere .= "AND $table.strAcademicStanding = 'new' ";
            else
                if($astatus == 1)
                    $sWhere .= "WHERE $table.strAcademicStanding = 'regular' ";
                elseif($astatus == 2)
                    $sWhere .= "WHERE $table.strAcademicStanding = 'irregular' ";
                else
                    $sWhere .= "WHERE $table.strAcademicStanding = 'new' ";
        }
        
        if($gender!=0  && $table =='tb_mas_users'){
            if($astatus != 0 || $scholarship!=0)
                if($gender == 1)
                    $sWhere .= "AND $table.enumGender = 'male' ";
                else
                    $sWhere .= "AND $table.enumGender = 'female' ";
            else
                if($gender == 1)
                    $sWhere .= "WHERE $table.enumGender = 'male' ";
                else
                    $sWhere .= "WHERE $table.enumGender = 'female' ";
            
        }
        if($graduate!=0  && $table =='tb_mas_users'){
            if($astatus != 0 || $gender != 0 || $scholarship!=0)
                if($graduate == 1)
                    $sWhere .= "AND $table.isGraduate = 1 ";
                else
                    $sWhere .= "AND $table.isGraduate = 0 ";
            else
                if($graduate == 1)
                    $sWhere .= "WHERE $table.isGraduate = 1 ";
                else
                    $sWhere .= "WHERE $table.isGraduate = 0 ";
            
        }
        if($yearlevel!=0 && $table =='tb_mas_users')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $scholarship!=0)
                $sWhere .= "AND $table.intStudentYear = ".$yearlevel." ";
            else
                $sWhere .= "WHERE $table.intStudentYear = ".$yearlevel." ";
        
        
        if($course!=0 && $table =='tb_mas_users')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $yearlevel!=0 || $scholarship!=0 )
                $sWhere .= "AND $table.intProgramID = '".$course."' ";
            else
                $sWhere .= "WHERE $table.intProgramID = '".$course."' ";
       
        
        if($sem == 0)
            $active_sem = $this->data_fetcher->get_active_sem();
        else
            $active_sem = $this->data_fetcher->get_sem_by_id($sem);
        
        if($registered != 0 && $table =='tb_mas_users')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $yearlevel!=0 || $scholarship!=0 || $course!=0 )
            {
                switch($registered)
                {
                    case 1:
                    $sWhere .= "AND tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 0 ";
                    break;
                    case 2:
                    $sWhere .= "AND tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 1 ";
                    break;
                    case 3:
                    $sWhere .= "AND tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 2 ";
                    break;
                }
            }
            else
            {
                switch($registered){
                    case 1:
                        $sWhere .= "WHERE tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 0 ";
                    break;
                    case 2:
                        $sWhere .= "WHERE tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 1 ";
                    break;
                    case 3:
                        $sWhere .= "WHERE tb_mas_registration.intAYID = ".$active_sem['intID']." AND tb_mas_registration.intROG = 2 ";
                    break;
                    
                }
            }
        
        if($sem!=0 && $table =='tb_mas_room_schedule')
            if($gender!=0 || $astatus!=0 || $graduate!=0 || $registered != 0 || $yearlevel!=0 || $scholarship!=0 || $course!=0 )
                $sWhere .= "AND tb_mas_room_schedule.intSem = ".$active_sem['intID']." ";
            else
                $sWhere .= "WHERE tb_mas_room_schedule.intSem = ".$active_sem['intID']." ";
            
        if($trashed!=null && $table == 'tb_mas_message_user')
        {
            if($user == null)
                $sWhere .="WHERE ";
            
            $sWhere .= "AND $table.intTrash ='".$trashed."' ";
        }
        
        
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            if($user==null || ($trashed!=null && $table == 'tb_mas_message_user') )
                $sWhere = "WHERE (";
            else
                $sWhere .= "AND (";
            
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if(($aColumns[$i] != "strFirstname" && $aColumns[$i] != "strLastname") || $table != 'tb_mas_users')
                    $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'] )."%' OR ";
                else
                    $sWhere .= "CONCAT_WS(' ',strFirstname,strLastname,strMiddlename) LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch'])."%' OR ";
                
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
            
            
            
        }
        
        /* Individual column filtering */
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
               
                
                $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->db->conn_id,$_GET['sSearch_'.$i])."%' ";
        
                
                
            }
        }

        $join = "";
        
        if($table == 'tb_mas_users')
        {
            $join = " JOIN tb_mas_programs ON tb_mas_users.intProgramID = tb_mas_programs.intProgramID ";
            
           
        }
        
        if($registered!=0  && $table =='tb_mas_users'){
            
            $join .= "JOIN tb_mas_registration ON tb_mas_users.intID = tb_mas_registration.intStudentID ";
        }
        
        if($table == 'tb_mas_message_user')
        {
            $join = "JOIN tb_mas_faculty ON tb_mas_faculty.intID = tb_mas_message_user.intFacultyIDSender ";
            $join .= "JOIN tb_mas_system_message ON tb_mas_system_message.intID = tb_mas_message_user.intMessageID ";
        }
        if($table == 'tb_mas_room_schedule')
        {
            $join = "JOIN tb_mas_classrooms ON tb_mas_classrooms.intID = tb_mas_room_schedule.intRoomID ";
            $join .= "JOIN tb_mas_classlist ON tb_mas_classlist.intID = tb_mas_room_schedule.strScheduleCode ";
            $join .= "JOIN tb_mas_subjects ON tb_mas_subjects.intID = tb_mas_classlist.intSubjectID ";
        }
        if($table == 'tb_mas_curriculum')
        {
            $join = "JOIN tb_mas_programs ON tb_mas_programs.intProgramID = tb_mas_curriculum.intProgramID ";
        }
        if($table == 'tb_mas_faculty')
        {
            if($sWhere == "")
            $sWhere = "WHERE intID != 999 ";
            else
            $sWhere .= "AND intID != 999 ";
        }
        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)). 
            " FROM $sTable
            $join
            $sWhere
            $sOrder
            $sLimit
            
        ";
        
        
        $rResult = $this->db->query($sQuery);

        /* Data set length after filtering */
        $sQuery = "
            SELECT FOUND_ROWS() as frows
        ";
        $rResultFilterTotal = $this->db->query($sQuery);
        $aResultFilterTotal = $rResultFilterTotal->result();
        $iFilteredTotal = $aResultFilterTotal[0]->frows;

        /* Total data set length */
        $sQuery = "
            SELECT COUNT(".$sIndexColumn.") as cnt
            FROM   $sTable
        ";
        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->result();
        $iTotal = $aResultTotal[0]->cnt;

        

        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult->result() as $aRow)
        {
            $row = array();
            for ( $i=0 ; $i<count($aColumns) ; $i++ )
            {
                if ( $aColumns[$i] == "strLastname" && $table == 'tb_mas_users')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = "<a href='".base_url()."unity/student_viewer/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."  ".$aRow->{$aColumns[$i+1]}." ".$aRow->{$aColumns[$i+2]};
                }
                else if ( ($aColumns[$i] == "strFirstname" || $aColumns[$i] == "strMiddlename") && $table == 'tb_mas_users')
                {
                    
                }
                else if ( $aColumns[$i] == "strFirstName" && $table == 'tb_mas_message_user')
                {
                    /* Special output formatting for 'version' column */
                    $row[] = '<label><input rel="'.$aRow->{$aColumns[0]}.'" type="checkbox" class="minimal message-check"/></label> '.$aRow->{$aColumns[$i]}." ".$aRow->{$aColumns[$i+1]};
                }
                else if ( $aColumns[$i] == "strLastName" && $table == 'tb_mas_message_user')
                {
                    
                }
                
                else if ( $aColumns[$i] == "intUserLevel" && $table == 'tb_mas_faculty')
                {
                    $row[] = switch_user_level($aRow->{$aColumns[$i]}); 
                }
                else if($aColumns[$i] == 'dteDate' && $table == 'tb_mas_message_user')
                {
                    $row[] = time_lapsed($aRow->{$aColumns[$i]});
                }
                else if($aColumns[$i] == 'strSubject' && $table == 'tb_mas_message_user')
                {
                    $row[] = "<a href='".base_url()."messages/view_message/".$aRow->{$aColumns[0]}."'>".$aRow->{$aColumns[$i]}."</a>";
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte' && $table != 'tb_mas_room_schedule')
                {
                    $row[] = pw_hash(date("mdY",strtotime($aRow->{$aColumns[$i]})));
                    //$row[] = date("mdY",strtotime($aRow->{$aColumns[$i]}));
                }
                else if(substr($aColumns[$i], 0, 3) == 'dte' && $table == 'tb_mas_room_schedule')
                {
                    $row[] = date("g:ia",strtotime($aRow->{$aColumns[$i]}));
                }
                else if ( $aColumns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow->{$aColumns[$i]};
                }
                
            }
            $output['aaData'][] = $row;
        }
	   echo json_encode( $output ); 
    }
    
    
    
    
}