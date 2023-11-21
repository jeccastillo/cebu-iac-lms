<?php

$config['tb_mas_message_user_columns'] = array('intMessageUserID','strFirstName','strLastName','strSubject', 'dteDate','intRead');
$config['tb_mas_message_user_index'] = "intMessageUserID";

$config['tb_mas_scholarships_columns'] = array('tb_mas_scholarships.intID','name','description','type','deduction_type','status','strUsername');
$config['tb_mas_scholarships_index'] = "intID";


$config['tb_mas_tuition_year_columns'] = array('intID','year','pricePerUnit','pricePerUnitOnline','pricePerUnitHybrid','pricePerUnitHyflex','isDefault','isDefaultShs');
$config['tb_mas_tuition_year_index'] = "intID";

$config['tb_mas_classrooms_columns'] = array("intID","strRoomCode","description","enumType");
$config['tb_mas_classrooms_index'] = "intID";

$config['tb_mas_grading_columns'] = array("id","name");
$config['tb_mas_grading_index'] = "id";

$config['tb_mas_users_columns'] = array("intID","slug","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intYearLevel","strAcademicStanding","level");

$config['tb_mas_users_columns2'] = array("intID","strStudentNumber","strLastname","strFirstname","strMiddlename","strProgramCode","intStudentYear","strAcademicStanding", "dteBirthDate", "strPass");

$config['tb_mas_users_index'] = "intID";


$config['tb_mas_ns_payee_columns'] = array("id","id_number","lastname","firstname","middlename","tin","address","contact_number");
$config['tb_mas_ns_payee_index'] = "id";

$config['tb_mas_room_schedule_columns'] = array("intRoomSchedID","strCode","strClassName","tb_mas_classlist.year","strSection","sub_section","name","strDay","dteStart","dteEnd","enumClassType","strRoomCode");
$config['tb_mas_room_schedule_index'] = "intRoomSchedID";


$config['tb_mas_programs_columns'] = array("intProgramID","strProgramCode","strProgramDescription","strMajor","type","enumEnabled");
$config['tb_mas_programs_index'] = "intProgramID";

$config['tb_mas_subjects_columns'] = array("intID","strCode","strDescription","strUnits");
$config['tb_mas_subjects_index'] = "intID";

$config['tb_mas_faculty_columns'] = array("intID","strFirstname","strLastname","intUserLevel","strUsername","isActive", 'strSchool');
$config['tb_mas_faculty_index'] = "intID";

$config['tb_mas_curriculum_columns'] = array("intID","strName","strProgramCode");
$config['tb_mas_curriculum_index'] = "intID";

$config['tb_mas_applications_columns'] = array("intApplicationID","strAppNumber","strLastname","strFirstname","strMiddlename","enumCourse1","strAppDate","strConfirmationCode");
$config['tb_mas_applications_index'] = "intApplicationID";

$config['tb_mas_block_sections_columns'] = array("intID","name","strProgramCode");
$config['tb_mas_block_sections_index'] = "intID";

$config['tb_mas_exam_columns'] = array("intID","strName","type","programType");
$config['tb_mas_exam_index'] = "intID";

$config['tb_mas_questions_columns'] = array("tb_mas_questions.intID","strTitle","strName","strChoice","strSection");
$config['tb_mas_questions_index'] = "intID";

$config['tb_mas_student_exam_columns'] = array("tb_mas_student_exam.intID","student_name","date_taken","strName","score","student_id");
$config['tb_mas_student_exam_index'] = "intID";
?>