<?php

use App\Http\Controllers\Api\V1\AdmissionsController;
use App\Http\Controllers\Api\V1\ApplicantAnalyticsController;
use App\Http\Controllers\Api\V1\ApplicantController;
use App\Http\Controllers\Api\V1\ApplicantInterviewController;
use App\Http\Controllers\Api\V1\ApplicantJourneyController;
use App\Http\Controllers\Api\V1\ApplicantTypeController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\CashierController;
use App\Http\Controllers\Api\V1\ClasslistController;
use App\Http\Controllers\Api\V1\ClasslistGradesController;
use App\Http\Controllers\Api\V1\ClasslistMergeController;
use App\Http\Controllers\Api\V1\ClassroomController;
use App\Http\Controllers\Api\V1\CurriculumController;
use App\Http\Controllers\Api\V1\CurriculumImportController;
use App\Http\Controllers\Api\V1\FacultyController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\GenericApiController;
use App\Http\Controllers\Api\V1\GradingSystemController;
use App\Http\Controllers\Api\V1\InitialRequirementsAdminController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentDescriptionController;
use App\Http\Controllers\Api\V1\PaymentDetailAdminController;
use App\Http\Controllers\Api\V1\PaymentModeController;
use App\Http\Controllers\Api\V1\PaymentJournalController;
use App\Http\Controllers\Api\V1\FinancePaymentActionsController;
use App\Http\Controllers\Api\V1\PortalController;
use App\Http\Controllers\Api\V1\PreviousSchoolController;
use App\Http\Controllers\Api\V1\ProgramController;
use App\Http\Controllers\Api\V1\PublicInitialRequirementsController;
use App\Http\Controllers\Api\V1\RegistrarController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\RequirementController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\ScholarshipController;
use App\Http\Controllers\Api\V1\ScholarshipMEController;
use App\Http\Controllers\Api\V1\SchoolYearController;
use App\Http\Controllers\Api\V1\SchoolYearImportController;
use App\Http\Controllers\Api\V1\StudentBillingController;
use App\Http\Controllers\Api\V1\StudentBillingExtrasController;
use App\Http\Controllers\Api\V1\StudentChecklistController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\SubjectImportController;
use App\Http\Controllers\Api\V1\ClasslistImportController;
use App\Http\Controllers\Api\V1\ClasslistStudentImportController;
use App\Http\Controllers\Api\V1\SystemAlertController;
use App\Http\Controllers\Api\V1\CreditedSubjectsController;
use App\Http\Controllers\Api\V1\SystemLogController;
use App\Http\Controllers\Api\V1\TuitionController;
use App\Http\Controllers\Api\V1\TuitionYearController;
use App\Http\Controllers\Api\V1\UnityController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Controllers\Api\V1\StudentImportController;
use App\Http\Controllers\Api\V1\ClassroomImportController;
use App\Http\Controllers\Api\V1\ScheduleImportController;
use App\Http\Controllers\Api\V1\ClinicHealthController;
use App\Http\Controllers\Api\V1\ClinicVisitController;
use App\Http\Controllers\Api\V1\ClinicAttachmentController;
use App\Services\ClasslistSlotsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'service' => 'laravel-api',
            'version' => 'v1'
        ]);
    });

    Route::get('/programs', [ProgramController::class, 'index']);
    Route::get('/programs/{id}', [ProgramController::class, 'show']);
    Route::post('/programs', [ProgramController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/programs/{id}', [ProgramController::class, 'update'])->middleware('role:registrar,admin');
    Route::delete('/programs/{id}', [ProgramController::class, 'destroy'])->middleware('role:registrar,admin');

    // Campus CRUD
    Route::get('/campuses', [CampusController::class, 'index']);
    Route::get('/campuses/{id}', [CampusController::class, 'show']);
    Route::post('/campuses', [CampusController::class, 'store'])->middleware('role:admin');
    Route::put('/campuses/{id}', [CampusController::class, 'update'])->middleware('role:admin');
    Route::delete('/campuses/{id}', [CampusController::class, 'destroy'])->middleware('role:admin');

    // Roles management
    Route::get('/roles', [RoleController::class, 'index'])->middleware('role:admin');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('role:admin');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('role:admin');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('role:admin');

    // Faculty role assignment
    Route::get('/faculty/{id}/roles', [RoleController::class, 'facultyRoles'])->middleware('role:admin');
    Route::post('/faculty/{id}/roles', [RoleController::class, 'assignFacultyRoles'])->middleware('role:admin');
    Route::delete('/faculty/{id}/roles/{roleId}', [RoleController::class, 'removeFacultyRole'])->middleware('role:admin');

    // Faculty CRUD (admin-only)
    Route::get('/faculty', [FacultyController::class, 'index'])->middleware('role:admin');
    // Place search route BEFORE parameterized /faculty/{id} to avoid route collision
    // Faculty search for cashier assignment (cashier_admin and admin)
    Route::get('/faculty/search', [FacultyController::class, 'index'])->middleware('role:cashier_admin,admin');
    Route::get('/faculty/{id}', [FacultyController::class, 'show'])->middleware('role:admin');
    Route::post('/faculty', [FacultyController::class, 'store'])->middleware('role:admin');
    Route::put('/faculty/{id}', [FacultyController::class, 'update'])->middleware('role:admin');
    Route::delete('/faculty/{id}', [FacultyController::class, 'destroy'])->middleware('role:admin');

    // Admissions - application submission
    Route::post('/admissions/student-info', [AdmissionsController::class, 'store']);

    // Portal endpoints (CI parity)
    Route::post('/portal/save-token', [PortalController::class, 'saveToken']);
    Route::get('/portal/active-programs', [PortalController::class, 'activePrograms']);
    Route::post('/portal/student-data', [PortalController::class, 'studentData']);

    // Users endpoints (CI parity)
    Route::post('/users/auth', [UsersController::class, 'auth']);
    Route::post('/users/auth-student', [UsersController::class, 'authStudent']);
    Route::post('/users/register', [UsersController::class, 'register']);
    Route::post('/users/forgot', [UsersController::class, 'forgot']);
    Route::post('/users/password-reset', [UsersController::class, 'passwordReset']);
    Route::post('/users/logout', [UsersController::class, 'logout']);
    // Diagnostics helper (non-production): verify existence and password format
    Route::get('/users/debug-auth', [UsersController::class, 'debugAuth']);

    // Subject endpoints (read + write parity)
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/by-curriculum', [SubjectController::class, 'byCurriculum']);
    Route::get('/subjects/{id}', [SubjectController::class, 'show']);
    Route::get('/subjects/{id}/prerequisites', [SubjectController::class, 'prerequisites']);
    Route::get('/subjects/{id}/corequisites', [SubjectController::class, 'corequisites']);
    Route::post('/subjects/{id}/check-prerequisites', [SubjectController::class, 'checkPrerequisites']);
    Route::post('/subjects/check-prerequisites-batch', [SubjectController::class, 'checkPrerequisitesBatch']);
    Route::post('/subjects/{id}/check-corequisites', [SubjectController::class, 'checkCorequisites']);
    Route::post('/subjects/check-corequisites-batch', [SubjectController::class, 'checkCorequisitesBatch']);

    // write operations (CI parity)
    Route::post('/subjects/submit', [SubjectController::class, 'submit'])->middleware('role:registrar,admin');
    Route::post('/subjects/edit', [SubjectController::class, 'edit'])->middleware('role:registrar,admin');
    Route::post('/subjects/submit-eq', [SubjectController::class, 'submitEq'])->middleware('role:registrar,admin');
    Route::post('/subjects/submit-days', [SubjectController::class, 'submitDays'])->middleware('role:registrar,admin');
    Route::post('/subjects/submit-room', [SubjectController::class, 'submitRoom'])->middleware('role:registrar,admin');
    Route::post('/subjects/submit-prereq', [SubjectController::class, 'submitPrereq'])->middleware('role:registrar,admin');
    Route::post('/subjects/submit-coreq', [SubjectController::class, 'submitCoreq'])->middleware('role:registrar,admin');
    Route::post('/subjects/delete-prereq', [SubjectController::class, 'deletePrereq'])->middleware('role:registrar,admin');
    Route::post('/subjects/delete-coreq', [SubjectController::class, 'deleteCoreq'])->middleware('role:registrar,admin');
    Route::post('/subjects/delete', [SubjectController::class, 'delete'])->middleware('role:registrar,admin');

    // Subjects Import
    Route::get('/subjects/import/template', [SubjectImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/subjects/import', [SubjectImportController::class, 'import'])->middleware('role:registrar,admin');

    // Classlists Import
    Route::get('/classlists/import/template', [ClasslistImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/classlists/import', [ClasslistImportController::class, 'import'])->middleware('role:registrar,admin');

    // Class Records (tb_mas_classlist_student) Import
    Route::get('/class-records/import/template', [ClasslistStudentImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/class-records/import', [ClasslistStudentImportController::class, 'import'])->middleware('role:registrar,admin');

    // RESTful facade routes mapping to parity handlers
    Route::post('/subjects', [SubjectController::class, 'submit'])->middleware('role:registrar,admin');
    Route::put('/subjects/{id}', function (Request $request, $id) {
        $request->merge(['intID' => (int) $id]);
        return app(SubjectController::class)->edit($request);
    })->middleware('role:registrar,admin');
    Route::delete('/subjects/{id}', function (Request $request, $id) {
        $request->merge(['id' => (int) $id]);
        return app(SubjectController::class)->delete($request);
    })->middleware('role:registrar,admin');

    // Tuition Year endpoints (read + write parity)
    Route::get('/tuition-years', [TuitionYearController::class, 'index']);
    Route::get('/tuition-years/{id}', [TuitionYearController::class, 'show']);
    Route::get('/tuition-years/{id}/misc', [TuitionYearController::class, 'misc']);
    Route::get('/tuition-years/{id}/lab-fees', [TuitionYearController::class, 'labFees']);
    Route::get('/tuition-years/{id}/tracks', [TuitionYearController::class, 'tracks']);
    Route::get('/tuition-years/{id}/programs', [TuitionYearController::class, 'programs']);
    Route::get('/tuition-years/{id}/electives', [TuitionYearController::class, 'electives']);
    Route::get('/tuition-years/{id}/installments', [TuitionYearController::class, 'installments']);

    // write operations
    Route::post('/tuition-years/add', [TuitionYearController::class, 'add'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/finalize', [TuitionYearController::class, 'finalize'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/submit-extra', [TuitionYearController::class, 'submitExtra'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/edit-type', [TuitionYearController::class, 'editType'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/delete-type', [TuitionYearController::class, 'deleteType'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/delete', [TuitionYearController::class, 'delete'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/duplicate', [TuitionYearController::class, 'duplicate'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/{id}/set-default', [TuitionYearController::class, 'setDefault'])->middleware('role:registrar,admin');

    // Curriculum endpoints (read + write)
    Route::get('/curriculum', [CurriculumController::class, 'index']);
    Route::get('/curriculum/{id}', [CurriculumController::class, 'show']);
    Route::get('/curriculum/{id}/subjects', [CurriculumController::class, 'subjects']);
    Route::post('/curriculum', [CurriculumController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/curriculum/{id}', [CurriculumController::class, 'update'])->middleware('role:registrar,admin');
    Route::delete('/curriculum/{id}', [CurriculumController::class, 'destroy'])->middleware('role:registrar,admin');
    Route::post('/curriculum/{id}/subjects', [CurriculumController::class, 'addSubject'])->middleware('role:registrar,admin');
    Route::post('/curriculum/{id}/subjects/bulk', [CurriculumController::class, 'addSubjectsBulk'])->middleware('role:registrar,admin');
    Route::delete('/curriculum/{id}/subjects/{subjectId}', [CurriculumController::class, 'removeSubject'])->middleware('role:registrar,admin');

    // Curriculum Import
    Route::get('/curriculum/import/template', [CurriculumImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/curriculum/import', [CurriculumImportController::class, 'import'])->middleware('role:registrar,admin');

    // Classroom endpoints (read + write)
    Route::get('/classroom', [ClassroomController::class, 'index']);
    Route::get('/classroom/{id}', [ClassroomController::class, 'show']);
    Route::post('/classroom', [ClassroomController::class, 'store'])->middleware('role:building_admin,admin');
    Route::put('/classroom/{id}', [ClassroomController::class, 'update'])->middleware('role:building_admin,admin');
    Route::delete('/classroom/{id}', [ClassroomController::class, 'destroy'])->middleware('role:building_admin,admin');

    // Classrooms Import
    Route::get('/classrooms/import/template', [ClassroomImportController::class, 'template'])->middleware('role:building_admin,admin');
    Route::post('/classrooms/import', [ClassroomImportController::class, 'import'])->middleware('role:building_admin,admin');

    // Schedule endpoints (read + write)
    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::get('/schedules/summary', [ScheduleController::class, 'summary']);
    Route::get('/schedules/academic-years', [ScheduleController::class, 'getAcademicYears']);
    Route::get('/schedules/available-classlists', [ScheduleController::class, 'getAvailableClasslists']);
    Route::get('/schedules/block-sections', [ScheduleController::class, 'getBlockSections']);
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    Route::post('/schedules', [ScheduleController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/schedules/{id}', [ScheduleController::class, 'update'])->middleware('role:registrar,admin');
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->middleware('role:registrar,admin');

    // Schedules Import
    Route::get('/schedules/import/template', [ScheduleImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/schedules/import', [ScheduleImportController::class, 'import'])->middleware('role:registrar,admin');

    // Student endpoints (baseline)
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);

    // Students Import
    Route::get('/students/import/template', [StudentImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/students/import', [StudentImportController::class, 'import'])->middleware('role:registrar,admin');
    Route::post('/student/viewer', [StudentController::class, 'viewer']);
    Route::post('/student/applicant', [StudentController::class, 'applicant']);
    // Student: Applicant Journey (read-only)
    Route::get('/student/applicant-journey/{applicantDataId}', [StudentController::class, 'applicantJourney']);
    Route::post('/student/balances', [StudentController::class, 'balances']);
    Route::post('/student/records', [StudentController::class, 'records']);
    Route::post('/student/records-by-term', [StudentController::class, 'recordsByTerm']);
    Route::post('/student/ledger', [StudentController::class, 'ledger']);

    // Student Checklist endpoints
    Route::get('/students/{student}/checklist', [StudentChecklistController::class, 'index']);

    // Credited Subjects (Registrar/Admin)
    Route::get('/students/{student_number}/credits', [CreditedSubjectsController::class, 'index'])->middleware('role:registrar,admin');
    Route::post('/students/{student_number}/credits', [CreditedSubjectsController::class, 'store'])->middleware('role:registrar,admin');
    Route::delete('/students/{student_number}/credits/{id}', [CreditedSubjectsController::class, 'destroy'])->middleware('role:registrar,admin');
    Route::post('/students/{student}/checklist/generate', [StudentChecklistController::class, 'generate'])->middleware('role:registrar,admin');
    Route::post('/students/{student}/checklist/items', [StudentChecklistController::class, 'addItem'])->middleware('role:registrar,admin');
    Route::put('/students/{student}/checklist/items/{item}', [StudentChecklistController::class, 'updateItem'])->middleware('role:registrar,admin');
    Route::delete('/students/{student}/checklist/items/{item}', [StudentChecklistController::class, 'deleteItem'])->middleware('role:registrar,admin');
    Route::get('/students/{student}/checklist/summary', [StudentChecklistController::class, 'summary']);

    // Registrar endpoints (baseline)
    Route::post('/registrar/daily-enrollment', [RegistrarController::class, 'dailyEnrollment']);
    Route::get('/registrar/grading/meta', [RegistrarController::class, 'gradingMeta']);
    Route::get('/registrar/grading/sections', [RegistrarController::class, 'gradingSections']);
    Route::post('/registrar/grading/results', [RegistrarController::class, 'gradingResults']);
    Route::get('/registrar/classlist/{id}/submitted', [RegistrarController::class, 'classlistSubmitted']);

    // Finance endpoints (baseline)
    Route::get('/finance/transactions', [FinanceController::class, 'transactions']);
    Route::get('/finance/or-lookup', [FinanceController::class, 'orLookup']);
    Route::get('/finance/payment-details', [FinanceController::class, 'paymentDetails']);
    Route::post('/finance/payment-details/debit', [PaymentJournalController::class, 'debit'])->middleware('role:finance,admin');
    Route::post('/finance/payment-details/credit', [PaymentJournalController::class, 'credit'])->middleware('role:finance,admin');
    Route::get('/finance/cashier/viewer-data', [FinanceController::class, 'viewerData'])->middleware('role:finance,admin');
    Route::get('/finance/student-ledger', [FinanceController::class, 'studentLedger'])->middleware('role:finance,admin');
    // Excess Payment Applications
    Route::post('/finance/ledger/excess/apply', [FinanceController::class, 'applyExcessPayment'])->middleware('role:finance,admin');
    Route::post('/finance/ledger/excess/revert', [FinanceController::class, 'revertExcessPayment'])->middleware('role:finance,admin');

    // Finance Payment Actions (finance_admin/admin)
    Route::get('/finance/payment-actions/search', [FinancePaymentActionsController::class, 'search'])->middleware('role:finance_admin,admin');
    Route::post('/finance/payment-actions/{id}/void', [FinancePaymentActionsController::class, 'void'])->middleware('role:finance_admin,admin');
    Route::delete('/finance/payment-actions/{id}/retract', [FinancePaymentActionsController::class, 'retract'])->middleware('role:finance_admin,admin');

    // Admin Payment Details management (admin-only)
    // Place 'admin' literal route BEFORE the parameterized {id} to avoid collision
    Route::get('/finance/payment-details/admin', [PaymentDetailAdminController::class, 'index'])->middleware('role:admin');
    Route::get('/finance/payment-details/{id}', [PaymentDetailAdminController::class, 'show'])->middleware('role:admin');
    Route::patch('/finance/payment-details/{id}', [PaymentDetailAdminController::class, 'update'])->middleware('role:admin');
    Route::delete('/finance/payment-details/{id}', [PaymentDetailAdminController::class, 'destroy'])->middleware('role:admin');

    // Student Billing (Finance/Admin)
    Route::get('/finance/student-billing', [StudentBillingController::class, 'index'])->middleware('role:finance,admin');
    Route::get('/finance/student-billing/{id}', [StudentBillingController::class, 'show'])->middleware('role:finance,admin');
    Route::post('/finance/student-billing', [StudentBillingController::class, 'store'])->middleware('role:finance,admin');
    Route::put('/finance/student-billing/{id}', [StudentBillingController::class, 'update'])->middleware('role:finance,admin');
    Route::delete('/finance/student-billing/{id}', [StudentBillingController::class, 'destroy'])->middleware('role:finance,admin');

    // Student Billing Extras (Finance/Admin)
    Route::get('/finance/student-billing/missing-invoices', [StudentBillingExtrasController::class, 'missingInvoices'])->middleware('role:finance,admin');
    Route::post('/finance/student-billing/{id}/generate-invoice', [StudentBillingExtrasController::class, 'generateInvoice'])->middleware('role:finance,admin');

    // Invoices (Finance/Admin)
    Route::get('/finance/invoices', [InvoiceController::class, 'index'])->middleware('role:finance,admin');
    Route::get('/finance/invoices/{id}', [InvoiceController::class, 'show'])->middleware('role:finance,admin');
    Route::get('/finance/invoices/{id}/pdf', [InvoiceController::class, 'pdf'])->middleware('role:finance,admin');
    Route::post('/finance/invoices/generate', [InvoiceController::class, 'generate'])->middleware('role:finance,admin');

    // Invoices Admin-only CRUD
    Route::post('/finance/invoices', [InvoiceController::class, 'store'])->middleware('role:admin');
    Route::put('/finance/invoices/{id}', [InvoiceController::class, 'update'])->middleware('role:admin');
    Route::delete('/finance/invoices/{id}', [InvoiceController::class, 'destroy'])->middleware('role:admin');

    // Scholarship endpoints (catalog CRUD + read)
    Route::get('/scholarships', [ScholarshipController::class, 'index']);
    // Place literal routes before parameterized to avoid collision
    Route::get('/scholarships/assigned', [ScholarshipController::class, 'assigned']);
    Route::get('/scholarships/enrolled', [ScholarshipController::class, 'enrolled']);

    // Assignment management (Scholarship/Admin)
    Route::get('/scholarships/assignments', [ScholarshipController::class, 'assignments'])->middleware('role:scholarship,admin');
    Route::post('/scholarships/assignments', [ScholarshipController::class, 'assignmentsStore'])->middleware('role:scholarship,admin');
    Route::patch('/scholarships/assignments/apply', [ScholarshipController::class, 'assignmentsApply'])->middleware('role:scholarship,admin');
    Route::delete('/scholarships/assignments/{id}', [ScholarshipController::class, 'assignmentsDelete'])->middleware('role:scholarship,admin');

    // Catalog CRUD (Scholarship/Discount definitions)
    Route::get('/scholarships/{id}', [ScholarshipController::class, 'show']);
    Route::post('/scholarships', [ScholarshipController::class, 'store'])->middleware('role:scholarship,admin');
    Route::put('/scholarships/{id}', [ScholarshipController::class, 'update'])->middleware('role:scholarship,admin');
    Route::delete('/scholarships/{id}', [ScholarshipController::class, 'delete'])->middleware('role:scholarship,admin');
    Route::post('/scholarships/{id}/restore', [ScholarshipController::class, 'restore'])->middleware('role:scholarship,admin');

    // Write stub retained for assignment parity
    Route::post('/scholarships/upsert', [ScholarshipController::class, 'upsert'])->middleware('role:scholarship,admin');

    // Scholarship Mutual-Exclusions management (Scholarship/Admin)
    Route::get('/scholarships/{id}/me', [ScholarshipMEController::class, 'list'])->middleware('role:scholarship,admin');
    Route::post('/scholarships/{id}/me', [ScholarshipMEController::class, 'add'])->middleware('role:scholarship,admin');
    Route::delete('/scholarships/{id}/me/{otherId}', [ScholarshipMEController::class, 'delete'])->middleware('role:scholarship,admin');

    // Unity endpoints (baseline scaffold)
    Route::post('/unity/advising', [UnityController::class, 'advising']);
    Route::post('/unity/enlist', [UnityController::class, 'enlist'])->middleware('role:registrar,admin');
    Route::post('/unity/reset-registration', [UnityController::class, 'resetRegistration'])->middleware('role:registrar,admin');
    // Registration read/update for existing rows only
    Route::get('/unity/registration', [UnityController::class, 'registration'])->middleware('role:registrar,finance,admin');
    Route::put('/unity/registration', [UnityController::class, 'updateRegistration'])->middleware('role:registrar,admin,finance');
    Route::post('/unity/tag-status', [UnityController::class, 'tagStatus']);
    Route::post('/unity/tuition-preview', [UnityController::class, 'tuitionPreview']);
    // Tuition save/fetch
    Route::post('/unity/tuition-save', [UnityController::class, 'tuitionSave'])->middleware('role:registrar,finance,admin');
    Route::get('/unity/tuition-saved', [UnityController::class, 'tuitionSaved'])->middleware('role:registrar,finance,admin');
    // Registration Form PDF (inline)
    Route::get('/unity/reg-form', [UnityController::class, 'regForm'])->middleware('role:registrar,admin');

    // Generic API endpoints
    Route::get('/generic/faculty', [GenericApiController::class, 'faculty']);
    Route::get('/generic/terms', [GenericApiController::class, 'terms']);
    Route::get('/generic/active-term', [GenericApiController::class, 'activeTerm']);

    // System logs (admin)
    Route::get('/system-logs/export', [SystemLogController::class, 'export'])->middleware('role:registrar,admin');
    Route::get('/system-logs', [SystemLogController::class, 'index'])->middleware('role:admin');
    // Registrar reports - Enrolled Students export
    Route::get('/reports/enrolled-students/export', [ReportsController::class, 'enrolledStudentsExport'])->middleware('role:registrar,admin');
    // Registrar reports - Daily Enrollment JSON
    Route::get('/reports/daily-enrollment', [ReportsController::class, 'dailyEnrollmentSummary'])->middleware('role:registrar,admin');
    // Registrar reports - Enrollment Statistics PDF
    Route::get('/reports/enrollment-statistics/pdf', [ReportsController::class, 'enrollmentStatisticsPdf'])->middleware('role:registrar,admin');
    // Grading Sheet PDF (Registrar / Faculty Admin / Admin)
    Route::get('/reports/grading-sheet/pdf', [ReportsController::class, 'gradingSheetPdf'])->middleware('role:registrar,faculty_admin,admin');
    // Student Transcript/Copy of Grades (Registrar/Admin)
    Route::post('/reports/students/{studentId}/transcript', [ReportsController::class, 'studentTranscriptPdf'])->middleware('role:registrar,admin');

    // Transcript fee, history, reprint, and billing (Registrar/Admin)
    Route::get('/reports/transcript-fee', [ReportsController::class, 'transcriptFee'])->middleware('role:registrar,admin');
    Route::get('/reports/students/{studentId}/transcripts', [ReportsController::class, 'listTranscriptRequests'])->middleware('role:registrar,admin');
    Route::post('/reports/students/{studentId}/transcripts/{requestId}/billing', [ReportsController::class, 'createTranscriptBilling'])->middleware('role:registrar,admin');
    Route::get('/reports/students/{studentId}/transcripts/{requestId}/reprint', [ReportsController::class, 'reprintTranscript'])->middleware('role:registrar,admin');

    // Classlists CRUD
    Route::post('/classlists/merge', [ClasslistMergeController::class, 'merge'])->middleware('role:registrar,admin');
    Route::get('/classlists', [ClasslistController::class, 'index']);
    // Place export BEFORE parameterized {id} to avoid route collision
    Route::get('/classlists/export-faculty-assignments', [ClasslistController::class, 'exportFacultyAssignments'])->middleware('role:registrar,faculty_admin,admin');
    Route::get('/classlists/{id}', [ClasslistController::class, 'show']);
    Route::post('/classlists', [ClasslistController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/classlists/{id}', [ClasslistController::class, 'update'])->middleware('role:registrar,faculty_admin,admin');
    Route::delete('/classlists/{id}', [ClasslistController::class, 'destroy'])->middleware('role:registrar,admin');
    // Bulk faculty assignment
    Route::post('/classlists/assign-faculty-bulk', [ClasslistController::class, 'assignFacultyBulk'])->middleware('role:registrar,faculty_admin,admin');

    // Classlist Grading Viewer + Operations
    Route::get('/classlists/{id}/viewer', [ClasslistGradesController::class, 'viewerData']);
    Route::post('/classlists/{id}/grades', [ClasslistGradesController::class, 'saveGrades']);
    Route::post('/classlists/{id}/finalize', [ClasslistGradesController::class, 'finalize']);
    Route::post('/classlists/{id}/unfinalize', [ClasslistGradesController::class, 'unfinalize'])->middleware('role:registrar,admin');

    // Grading Systems CRUD
    Route::get('/grading-systems', [GradingSystemController::class, 'index']);
    Route::get('/grading-systems/{id}', [GradingSystemController::class, 'show']);
    Route::post('/grading-systems', [GradingSystemController::class, 'store'])->middleware('role:admin,faculty_admin');
    Route::put('/grading-systems/{id}', [GradingSystemController::class, 'update'])->middleware('role:admin,faculty_admin');
    Route::delete('/grading-systems/{id}', [GradingSystemController::class, 'destroy'])->middleware('role:admin,faculty_admin');

    // Grading Items
    Route::post('/grading-systems/{id}/items/bulk', [GradingSystemController::class, 'addItemsBulk'])->middleware('role:admin,faculty_admin');
    Route::post('/grading-systems/{id}/items', [GradingSystemController::class, 'addItem'])->middleware('role:admin,faculty_admin');
    Route::delete('/grading-systems/items/{itemId}', [GradingSystemController::class, 'deleteItem'])->middleware('role:admin,faculty_admin');

    // School Years CRUD
    Route::get('/school-years', [SchoolYearController::class, 'index']);
    Route::get('/school-years/{id}', [SchoolYearController::class, 'show']);
    Route::post('/school-years', [SchoolYearController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/school-years/{id}', [SchoolYearController::class, 'update'])->middleware('role:registrar,admin');
    Route::delete('/school-years/{id}', [SchoolYearController::class, 'destroy'])->middleware('role:registrar,admin');

    // School Years Import
    Route::get('/school-years/import/template', [SchoolYearImportController::class, 'template'])->middleware('role:registrar,admin');
    Route::post('/school-years/import', [SchoolYearImportController::class, 'import'])->middleware('role:registrar,admin');

    // Tuition computation (parity with CI Data_fetcher::getTuition/getTuitionSubjects)
    Route::get('/tuition/compute', [TuitionController::class, 'compute']);

    // Cashier Administration (OR/Invoice Ranges & Stats)
    Route::get('/cashiers', [CashierController::class, 'index'])->middleware('role:cashier_admin,admin');
    Route::post('/cashiers', [CashierController::class, 'store'])->middleware('role:cashier_admin,admin');
    Route::patch('/cashiers/{id}', [CashierController::class, 'update'])->middleware('role:cashier_admin,admin');
    Route::post('/cashiers/{id}/ranges', [CashierController::class, 'updateRanges'])->middleware('role:cashier_admin,admin');
    Route::get('/cashiers/{id}/stats', [CashierController::class, 'stats'])->middleware('role:cashier_admin,admin');
    Route::patch('/cashiers/{id}/assign', [CashierController::class, 'assign'])->middleware('role:cashier_admin,admin');
    // Place stats route before the parameterized {id} routes to avoid matching "stats" as an ID
    Route::get('/cashiers/stats', [CashierController::class, 'statsAll'])->middleware('role:cashier_admin,admin');
    // Resolve acting cashier for current faculty context (place before parameterized {id} routes)
    Route::get('/cashiers/me', [CashierController::class, 'me'])->middleware('role:cashier_admin,finance,admin');
    Route::post('/cashiers/{id}/payments', [CashierController::class, 'createPayment'])->middleware('role:cashier_admin,finance,admin');
    Route::post('/cashiers/{cashier}/payments/{payment}/assign-number', [CashierController::class, 'assignNumber'])->middleware('role:cashier_admin,finance,admin');
    Route::get('/cashiers/{id}', [CashierController::class, 'show'])->middleware('role:cashier_admin,admin');
    Route::delete('/cashiers/{id}', [CashierController::class, 'destroy'])->middleware('role:cashier_admin,admin');

    // Payment Modes CRUD (Finance/Admin)
    Route::get('/payment-modes', [PaymentModeController::class, 'index'])->middleware('role:finance,admin');
    Route::get('/payment-modes/{id}', [PaymentModeController::class, 'show'])->middleware('role:finance,admin');
    Route::post('/payment-modes', [PaymentModeController::class, 'store'])->middleware('role:finance,admin');
    Route::put('/payment-modes/{id}', [PaymentModeController::class, 'update'])->middleware('role:finance,admin');
    Route::delete('/payment-modes/{id}', [PaymentModeController::class, 'destroy'])->middleware('role:finance,admin');
    Route::post('/payment-modes/{id}/restore', [PaymentModeController::class, 'restore'])->middleware('role:finance,admin');

    // Payment Descriptions CRUD (Finance/Admin)
    Route::get('/payment-descriptions', [PaymentDescriptionController::class, 'index'])->middleware('role:finance,admin');
    Route::get('/payment-descriptions/{id}', [PaymentDescriptionController::class, 'show'])->middleware('role:finance,admin');
    Route::post('/payment-descriptions', [PaymentDescriptionController::class, 'store'])->middleware('role:finance,admin');
    Route::put('/payment-descriptions/{id}', [PaymentDescriptionController::class, 'update'])->middleware('role:finance,admin');
    Route::delete('/payment-descriptions/{id}', [PaymentDescriptionController::class, 'destroy'])->middleware('role:finance,admin');

    // Requirements CRUD (Admissions/Admin)
    Route::get('/requirements', [RequirementController::class, 'index'])->middleware('role:admissions,admin');
    Route::get('/requirements/{id}', [RequirementController::class, 'show'])->middleware('role:admissions,admin');
    Route::post('/requirements', [RequirementController::class, 'store'])->middleware('role:admissions,admin');
    Route::put('/requirements/{id}', [RequirementController::class, 'update'])->middleware('role:admissions,admin');
    Route::delete('/requirements/{id}', [RequirementController::class, 'destroy'])->middleware('role:admissions,admin');

    // Previous Schools CRUD (Admissions/Admin)
    Route::get('/previous-schools', [PreviousSchoolController::class, 'index'])->middleware('role:admissions,admin');
    Route::get('/previous-schools/{id}', [PreviousSchoolController::class, 'show'])->middleware('role:admissions,admin');
    Route::post('/previous-schools', [PreviousSchoolController::class, 'store'])->middleware('role:admissions,admin');
    Route::put('/previous-schools/{id}', [PreviousSchoolController::class, 'update'])->middleware('role:admissions,admin');
    Route::delete('/previous-schools/{id}', [PreviousSchoolController::class, 'destroy'])->middleware('role:admissions,admin');

    // Applicant Types CRUD (Admissions/Admin)
    Route::get('/applicant-types', [ApplicantTypeController::class, 'index'])->middleware('role:admissions,admin');
    Route::get('/applicant-types/{id}', [ApplicantTypeController::class, 'show'])->middleware('role:admissions,admin');
    Route::post('/applicant-types', [ApplicantTypeController::class, 'store'])->middleware('role:admissions,admin');
    Route::put('/applicant-types/{id}', [ApplicantTypeController::class, 'update'])->middleware('role:admissions,admin');
    Route::delete('/applicant-types/{id}', [ApplicantTypeController::class, 'destroy'])->middleware('role:admissions,admin');

    // Applicants (Admissions/Admin)
    Route::get('/applicants', [ApplicantController::class, 'index'])->middleware('role:admissions,admin');
    Route::get('/applicants/{id}', [ApplicantController::class, 'show'])->middleware('role:admissions,admin');
    Route::put('/applicants/{id}', [ApplicantController::class, 'update'])->middleware('role:admissions,admin');
    // Admissions: Admin upload/replace initial requirements file for a student's requirement
    Route::post('/admissions/initial-requirements/{student}/upload/{appReqId}', [InitialRequirementsAdminController::class, 'upload'])->middleware('role:admissions,admin');

    // Enlistment Applicants (Registrar/Admissions/Admin)
    Route::get('/enlistment/applicants', [ApplicantController::class, 'eligibleForEnlistment'])->middleware('role:registrar,admissions,admin');

    // Applicants Analytics
    Route::get('/applicants/analytics/summary', [ApplicantAnalyticsController::class, 'summary'])->middleware('role:admissions,admin');

    // Admissions Interviews (Admissions/Admin)
    Route::post('/admissions/interviews', [ApplicantInterviewController::class, 'store'])->middleware('role:admissions,admin');
    Route::get('/admissions/interviews/{id}', [ApplicantInterviewController::class, 'show'])->middleware('role:admissions,admin');
    Route::get('/admissions/applicant-data/{applicantDataId}/interview', [ApplicantInterviewController::class, 'showByApplicantData'])->middleware('role:admissions,admin');
    Route::put('/admissions/interviews/{id}/result', [ApplicantInterviewController::class, 'submitResult'])->middleware('role:admissions,admin');

    // Public list for applicant forms (no auth)
    Route::get('/admissions/previous-schools', [PreviousSchoolController::class, 'index']);
    Route::get('/admissions/applicant-types', [ApplicantTypeController::class, 'index']);

    // Public Initial Requirements (hash-based access)
    Route::get('/public/initial-requirements/{hash}', [PublicInitialRequirementsController::class, 'index']);
    Route::post('/public/initial-requirements/{hash}/upload/{appReqId}', [PublicInitialRequirementsController::class, 'upload']);
    Route::get('/public/initial-requirements/{hash}/file/{appReqId}', [PublicInitialRequirementsController::class, 'file']);

    // Applicant Journey (Admissions/Admin/Registrar)
    Route::get('/admissions/applicant-data/{applicantDataId}/journey', [ApplicantJourneyController::class, 'index'])->middleware('role:admissions,registrar,admin');

    // TEMP: Debug route for ClasslistSlotsService testing (remove in production)
    Route::get('/debug/classlists/slots', function (Request $request) {
        $svc = app(ClasslistSlotsService::class);
        $params = $request->query();
        try {
            $result = $svc->listByTerm($params);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    })->middleware('role:registrar,admin');

    // System Alerts
    Route::get('/system-alerts', [SystemAlertController::class, 'index'])->middleware('role:admin');
    Route::post('/system-alerts', [SystemAlertController::class, 'store'])->middleware('role:admin');
    Route::put('/system-alerts/{id}', [SystemAlertController::class, 'update'])->middleware('role:admin');
    Route::delete('/system-alerts/{id}', [SystemAlertController::class, 'destroy'])->middleware('role:admin');
    Route::get('/system-alerts/active', [SystemAlertController::class, 'active']);
    Route::post('/system-alerts/{id}/dismiss', [SystemAlertController::class, 'dismiss']);

    // Clinic & Health Records
    Route::get('/clinic/records', [ClinicHealthController::class, 'index'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::post('/clinic/records', [ClinicHealthController::class, 'store'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::get('/clinic/records/{id}', [ClinicHealthController::class, 'show'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::put('/clinic/records/{id}', [ClinicHealthController::class, 'update'])->middleware('role:clinic_staff,clinic_admin,admin');

    Route::get('/clinic/visits', [ClinicVisitController::class, 'index'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::post('/clinic/visits', [ClinicVisitController::class, 'store'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::get('/clinic/visits/{id}', [ClinicVisitController::class, 'show'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::put('/clinic/visits/{id}', [ClinicVisitController::class, 'update'])->middleware('role:clinic_staff,clinic_admin,admin');

    Route::get('/clinic/attachments', [ClinicAttachmentController::class, 'index'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::post('/clinic/attachments', [ClinicAttachmentController::class, 'store'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::get('/clinic/attachments/{id}/download', [ClinicAttachmentController::class, 'download'])->middleware('role:clinic_staff,clinic_admin,admin');
    Route::delete('/clinic/attachments/{id}', [ClinicAttachmentController::class, 'destroy'])->middleware('role:clinic_admin,admin');
});
