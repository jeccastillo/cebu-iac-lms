<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProgramController;
use App\Http\Controllers\Api\V1\PortalController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\TuitionYearController;
use App\Http\Controllers\Api\V1\CurriculumController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\RegistrarController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\ScholarshipController;
use App\Http\Controllers\Api\V1\UnityController;
use App\Http\Controllers\Api\V1\GenericApiController;
use App\Http\Controllers\Api\V1\AdmissionsController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SystemLogController;
use App\Http\Controllers\Api\V1\ClasslistController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\StudentChecklistController;
use App\Http\Controllers\Api\V1\GradingSystemController;

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

    // RESTful facade routes mapping to parity handlers
    Route::post('/subjects', [SubjectController::class, 'submit'])->middleware('role:registrar,admin');
    Route::put('/subjects/{id}', function (Request $request, $id) {
        $request->merge(['intID' => (int) $id]);
        return app(\App\Http\Controllers\Api\V1\SubjectController::class)->edit($request);
    })->middleware('role:registrar,admin');
    Route::delete('/subjects/{id}', function (Request $request, $id) {
        $request->merge(['id' => (int) $id]);
        return app(\App\Http\Controllers\Api\V1\SubjectController::class)->delete($request);
    })->middleware('role:registrar,admin');

    // Tuition Year endpoints (read + write parity)
    Route::get('/tuition-years', [TuitionYearController::class, 'index']);
    Route::get('/tuition-years/{id}', [TuitionYearController::class, 'show']);
    Route::get('/tuition-years/{id}/misc', [TuitionYearController::class, 'misc']);
    Route::get('/tuition-years/{id}/lab-fees', [TuitionYearController::class, 'labFees']);
    Route::get('/tuition-years/{id}/tracks', [TuitionYearController::class, 'tracks']);
    Route::get('/tuition-years/{id}/programs', [TuitionYearController::class, 'programs']);
    Route::get('/tuition-years/{id}/electives', [TuitionYearController::class, 'electives']);

    // write operations
    Route::post('/tuition-years/add', [TuitionYearController::class, 'add'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/finalize', [TuitionYearController::class, 'finalize'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/submit-extra', [TuitionYearController::class, 'submitExtra'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/delete-type', [TuitionYearController::class, 'deleteType'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/delete', [TuitionYearController::class, 'delete'])->middleware('role:registrar,admin');
    Route::post('/tuition-years/duplicate', [TuitionYearController::class, 'duplicate'])->middleware('role:registrar,admin');

    // Curriculum endpoints (read + write)
    Route::get('/curriculum', [CurriculumController::class, 'index']);
    Route::get('/curriculum/{id}', [CurriculumController::class, 'show']);
    Route::get('/curriculum/{id}/subjects', [CurriculumController::class, 'subjects']);
    Route::post('/curriculum', [CurriculumController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/curriculum/{id}', [CurriculumController::class, 'update'])->middleware('role:registrar,admin');
    Route::delete('/curriculum/{id}', [CurriculumController::class, 'destroy'])->middleware('role:registrar,admin');
    Route::post('/curriculum/{id}/subjects', [CurriculumController::class, 'addSubject'])->middleware('role:registrar,admin');
    Route::delete('/curriculum/{id}/subjects/{subjectId}', [CurriculumController::class, 'removeSubject'])->middleware('role:registrar,admin');

    // Student endpoints (baseline)
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::post('/student/viewer', [StudentController::class, 'viewer']);
    Route::post('/student/balances', [StudentController::class, 'balances']);
    Route::post('/student/records', [StudentController::class, 'records']);
    Route::post('/student/records-by-term', [StudentController::class, 'recordsByTerm']);
    Route::post('/student/ledger', [StudentController::class, 'ledger']);

    // Student Checklist endpoints
    Route::get('/students/{student}/checklist', [StudentChecklistController::class, 'index']);
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

    // Scholarship endpoints (read-only baseline)
    Route::get('/scholarships', [ScholarshipController::class, 'index']);
    Route::get('/scholarships/assigned', [ScholarshipController::class, 'assigned']);
    Route::get('/scholarships/enrolled', [ScholarshipController::class, 'enrolled']);
    // write stubs for parity (return 501 Not Implemented)
    Route::post('/scholarships/upsert', [ScholarshipController::class, 'upsert'])->middleware('role:scholarship,admin');
    Route::delete('/scholarships/{id}', [ScholarshipController::class, 'delete'])->middleware('role:scholarship,admin');

    // Unity endpoints (baseline scaffold)
    Route::post('/unity/advising', [UnityController::class, 'advising']);
    Route::post('/unity/enlist', [UnityController::class, 'enlist'])->middleware('role:registrar,admin');
    Route::post('/unity/reset-registration', [UnityController::class, 'resetRegistration'])->middleware('role:registrar,admin');
    Route::post('/unity/tag-status', [UnityController::class, 'tagStatus']);
    Route::post('/unity/tuition-preview', [UnityController::class, 'tuitionPreview']);

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

    // Classlists CRUD
    Route::get('/classlists', [ClasslistController::class, 'index']);
    Route::get('/classlists/{id}', [ClasslistController::class, 'show']);
    Route::post('/classlists', [ClasslistController::class, 'store'])->middleware('role:registrar,admin');
    Route::put('/classlists/{id}', [ClasslistController::class, 'update'])->middleware('role:registrar,admin');
    Route::delete('/classlists/{id}', [ClasslistController::class, 'destroy'])->middleware('role:registrar,admin');

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
});
