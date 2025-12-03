(function () {
  'use strict';

  angular
    .module('unityApp')
    .config(configure);

  configure.$inject = ['$routeProvider', '$locationProvider'];
  function configure($routeProvider, $locationProvider) {
    $routeProvider
      .when("/login", {
        templateUrl: "features/auth/login.html",
        controller: "LoginController",
        controllerAs: "vm",
      })
      .when("/dashboard", {
        templateUrl: "features/dashboard/dashboard.html",
        controller: "DashboardController",
        controllerAs: "vm",
      })
      .when("/faculty/profile", {
        templateUrl: "features/faculty/profile.html",
        controller: "FacultyProfileController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "admin"],
      })
      .when("/faculty/settings", {
        templateUrl: "features/faculty/settings.html",
        controller: "FacultySettingsController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "admin"],
      })
      .when("/faculty/classes", {
        templateUrl: "features/faculty/classes.html",
        controller: "FacultyClassesController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "admin"],
      })
      .when("/registrar/reports", {
        templateUrl: "features/registrar/reports.html",
        controller: "ReportsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/transcripts", {
        templateUrl: "features/registrar/transcripts/transcripts.html",
        controller: "RegistrarTranscriptsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/daily-enrollment", {
        templateUrl: "features/registrar/daily-enrollment.html",
        controller: "DailyEnrollmentController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/enlistment", {
        templateUrl: "features/registrar/enlistment/enlistment.html",
        controller: "EnlistmentController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/enlistment-applicants", {
        templateUrl: "features/registrar/enlistment/applicants.html",
        controller: "EnlistmentApplicantsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/sections-slots", {
        templateUrl: "features/registrar/sections-slots/sections-slots.html",
        controller: "SectionsSlotsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/credit-subjects", {
        templateUrl: "features/registrar/credit-subjects/credit-subjects.html",
        controller: "CreditSubjectsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/change-password", {
        templateUrl: "features/registrar/change-password/change-password.html",
        controller: "RegistrarChangePasswordController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/registrar/shifting", {
        templateUrl: "features/registrar/shifting/shifting.html",
        controller: "ShiftingController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/finance/cashier/:id", {
        templateUrl: "features/finance/cashier-viewer/cashier-viewer.html",
        controller: "CashierViewerController",
        controllerAs: "vm",
        requiredRoles: ["finance", "registrar", "admin"],
      })
      .when("/finance/ledger", {
        templateUrl: "features/finance/ledger.html",
        controller: "FinanceLedgerController",
        controllerAs: "vm",
        requiredRoles: ["finance", "admin"],
      })
      .when("/finance/debit-credit", {
        templateUrl: "features/finance/debit-credit/debit-credit.html",
        controller: "DebitCreditController",
        controllerAs: "vm",
        requiredRoles: ["finance", "admin"],
      })
      .when("/scholarship/students", {
        templateUrl: "features/scholarship/students.html",
        controller: "ScholarshipStudentsController",
        controllerAs: "vm",
        requiredRoles: ["scholarship", "admin"],
      })
      .when("/scholarship/scholarships", {
        templateUrl: "features/scholarship/scholarships/list.html",
        controller: "ScholarshipsController",
        controllerAs: "vm",
        requiredRoles: ["scholarship", "admin"],
      })
      .when("/scholarship/assignments", {
        templateUrl: "features/scholarship/assignments/assignments.html",
        controller: "ScholarshipAssignmentsController",
        controllerAs: "vm",
        requiredRoles: ["scholarship", "admin"],
      })
      .when("/students", {
        templateUrl: "features/students/students.html",
        controller: "StudentsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "scholarship", "finance", "admin"],
      })
      .when("/students/:id", {
        templateUrl: "features/students/viewer.html",
        controller: "StudentViewerController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "scholarship", "finance", "admin"],
      })
      .when("/students/:id/records", {
        templateUrl: "features/students/records.html",
        controller: "StudentRecordsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/students/:student_id/checklist/edit", {
        templateUrl: "features/students/checklist-edit.html",
        controller: "ChecklistEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/admissions/apply", {
        templateUrl: "features/admissions/apply.html",
        controller: "AdmissionsApplyController",
        controllerAs: "vm",
      })
      .when("/admissions/success", {
        templateUrl: "features/admissions/success.html",
        controller: "AdmissionsSuccessController",
        controllerAs: "vm",
      })
      .when("/admissions/requirements", {
        templateUrl: "features/admissions/requirements/list.html",
        controller: "RequirementsController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/requirements/new", {
        templateUrl: "features/admissions/requirements/edit.html",
        controller: "RequirementEditController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/requirements/:id/edit", {
        templateUrl: "features/admissions/requirements/edit.html",
        controller: "RequirementEditController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/previous-schools", {
        templateUrl: "features/admissions/previous-schools/list.html",
        controller: "PreviousSchoolsController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/previous-schools/new", {
        templateUrl: "features/admissions/previous-schools/edit.html",
        controller: "PreviousSchoolEditController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/previous-schools/:id/edit", {
        templateUrl: "features/admissions/previous-schools/edit.html",
        controller: "PreviousSchoolEditController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/applicant-types", {
        templateUrl: "features/admissions/applicant-types/list.html",
        controller: "ApplicantTypesController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/applicant-types/new", {
        templateUrl: "features/admissions/applicant-types/edit.html",
        controller: "ApplicantTypeEditController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/applicant-types/:id/edit", {
        templateUrl: "features/admissions/applicant-types/edit.html",
        controller: "ApplicantTypeEditController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      // Place analytics before the dynamic :id route to avoid "analytics" being treated as an id
      .when("/admissions/applicants/analytics", {
        templateUrl: "features/admissions/applicants/analytics.html",
        controller: "ApplicantsAnalyticsController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/applicants", {
        templateUrl: "features/admissions/applicants/list.html",
        controller: "ApplicantsListController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })
      .when("/admissions/applicants/:id", {
        templateUrl: "features/admissions/applicants/view.html",
        controller: "ApplicantViewController",
        controllerAs: "vm",
        requiredRoles: ["admissions", "admin"],
      })

      /* Campuses CRUD */
      .when("/campuses", {
        templateUrl: "features/campuses/campuses.html",
        controller: "CampusesController",
        controllerAs: "vm",
        requiredRoles: ["campus_admin", "admin"],
      })
      .when("/campuses/add", {
        templateUrl: "features/campuses/campus-edit.html",
        controller: "CampusEditController",
        controllerAs: "vm",
        requiredRoles: ["campus_admin", "admin"],
      })
      .when("/campuses/:id/edit", {
        templateUrl: "features/campuses/campus-edit.html",
        controller: "CampusEditController",
        controllerAs: "vm",
        requiredRoles: ["campus_admin", "admin"],
      })

      /* Programs CRUD */
      .when("/programs", {
        templateUrl: "features/programs/list.html",
        controller: "ProgramsListController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/programs/add", {
        templateUrl: "features/programs/edit.html",
        controller: "ProgramEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/programs/:id/edit", {
        templateUrl: "features/programs/edit.html",
        controller: "ProgramEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })

      /* Subjects CRUD */
      .when("/subjects", {
        templateUrl: "features/subjects/list.html",
        controller: "SubjectsListController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/subjects/add", {
        templateUrl: "features/subjects/edit.html",
        controller: "SubjectEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/subjects/:id/edit", {
        templateUrl: "features/subjects/edit.html",
        controller: "SubjectEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })

      /* Curricula CRUD */
      .when("/curricula", {
        templateUrl: "features/curricula/list.html",
        controller: "CurriculaListController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/curricula/add", {
        templateUrl: "features/curricula/edit.html",
        controller: "CurriculumEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/curricula/:id/edit", {
        templateUrl: "features/curricula/edit.html",
        controller: "CurriculumEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })

      /* School Years CRUD */
      .when("/school-years", {
        templateUrl: "features/school-years/list.html",
        controller: "SchoolYearsListController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/school-years/new", {
        templateUrl: "features/school-years/edit.html",
        controller: "SchoolYearEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/school-years/:id/edit", {
        templateUrl: "features/school-years/edit.html",
        controller: "SchoolYearEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })

      /* Faculty CRUD */
      .when("/faculty", {
        templateUrl: "features/faculty/list.html",
        controller: "FacultyController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/faculty/add", {
        templateUrl: "features/faculty/edit.html",
        controller: "FacultyEditController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/faculty/:id/edit", {
        templateUrl: "features/faculty/edit.html",
        controller: "FacultyEditController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      /* Classlists CRUD */
      .when("/classlists", {
        templateUrl: "features/classlists/list.html",
        controller: "ClasslistsController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/classlists/add", {
        templateUrl: "features/classlists/edit.html",
        controller: "ClasslistEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/classlists/:id/edit", {
        templateUrl: "features/classlists/edit.html",
        controller: "ClasslistEditController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/classlists/:id/viewer", {
        templateUrl: "features/classlists/viewer.html",
        controller: "ClasslistViewerController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "registrar", "admin"],
      })
      .when("/classlists/:id/attendance", {
        templateUrl: "features/classlists/attendance.html",
        controller: "ClasslistAttendanceController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "admin"],
      })

      /* Classrooms CRUD */
      .when("/classrooms", {
        templateUrl: "features/classrooms/classrooms.html",
        controller: "ClassroomsController",
        controllerAs: "vm",
        requiredRoles: ["building_admin", "admin"],
      })
      .when("/classrooms/add", {
        templateUrl: "features/classrooms/classroom-edit.html",
        controller: "ClassroomsController",
        controllerAs: "vm",
        requiredRoles: ["building_admin", "admin"],
      })
      .when("/classrooms/:id/edit", {
        templateUrl: "features/classrooms/classroom-edit.html",
        controller: "ClassroomsController",
        controllerAs: "vm",
        requiredRoles: ["building_admin", "admin"],
      })

      /* Schedules CRUD */
      .when("/schedules", {
        templateUrl: "features/schedules/schedules.html",
        controller: "SchedulesController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/schedules/add", {
        templateUrl: "features/schedules/schedule-edit.html",
        controller: "SchedulesController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })
      .when("/schedules/:id/edit", {
        templateUrl: "features/schedules/schedule-edit.html",
        controller: "SchedulesController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "admin"],
      })

      /* Grading Systems CRUD */
      .when("/grading-systems", {
        templateUrl: "features/grading/list.html",
        controller: "GradingListController",
        controllerAs: "vm",
        requiredRoles: ["faculty_admin", "admin"],
      })
      .when("/grading-systems/new", {
        templateUrl: "features/grading/edit.html",
        controller: "GradingEditController",
        controllerAs: "vm",
        requiredRoles: ["faculty_admin", "admin"],
      })
      .when("/grading-systems/:id/edit", {
        templateUrl: "features/grading/edit.html",
        controller: "GradingEditController",
        controllerAs: "vm",
        requiredRoles: ["faculty_admin", "admin"],
      })
      .when("/faculty-loading", {
        templateUrl: "features/academics/faculty-loading/faculty-loading.html",
        controller: "FacultyLoadingController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "faculty_admin", "admin"],
      })
      .when("/faculty-loading/by-faculty", {
        templateUrl: "features/academics/faculty-loading/by-faculty.html",
        controller: "FacultyLoadingByFacultyController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "faculty_admin", "admin"],
      })
      .when("/academics/grading-sheet", {
        templateUrl: "features/academics/grading-sheet/grading-sheet.html",
        controller: "GradingSheetController",
        controllerAs: "vm",
        requiredRoles: ["registrar", "faculty_admin", "admin"],
      })
      .when("/advisors", {
        templateUrl: "features/advisors/advisors.html",
        controller: "AdvisorsController",
        controllerAs: "vm",
        requiredRoles: ["faculty_admin", "admin"],
      })
      .when("/advisors/quick-view", {
        templateUrl: "features/advisors/quick-view.html",
        controller: "AdvisorsQuickViewController",
        controllerAs: "vm",
        requiredRoles: ["faculty_admin", "admin"],
      })

      /* Tuition Years (Finance) */
      .when('/finance/tuition-years', {
        templateUrl: 'features/tuition-years/list.html',
        controller: 'TuitionYearsListController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'registrar', 'admin']
      })
      .when('/finance/tuition-years/:id', {
        templateUrl: 'features/tuition-years/edit.html',
        controller: 'TuitionYearEditController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'registrar', 'admin']
      })

      // Payment Descriptions (Finance)
      .when('/finance/payment-descriptions', {
        templateUrl: 'features/finance/payment-descriptions/list.html',
        controller: 'PaymentDescriptionsController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/payment-descriptions/new', {
        templateUrl: 'features/finance/payment-descriptions/edit.html',
        controller: 'PaymentDescriptionEditController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/payment-descriptions/:id/edit', {
        templateUrl: 'features/finance/payment-descriptions/edit.html',
        controller: 'PaymentDescriptionEditController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/payment-modes', {
        templateUrl: 'features/finance/payment-modes/list.html',
        controller: 'PaymentModesController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/payment-modes/new', {
        templateUrl: 'features/finance/payment-modes/edit.html',
        controller: 'PaymentModeEditController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/payment-modes/:id/edit', {
        templateUrl: 'features/finance/payment-modes/edit.html',
        controller: 'PaymentModeEditController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })

      // Student Billing (Finance)
      .when('/finance/student-billing', {
        templateUrl: 'features/finance/student-billing/list.html',
        controller: 'FinanceStudentBillingController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/payment-actions', {
        templateUrl: 'features/finance/payment-actions/payment-actions.html',
        controller: 'FinancePaymentActionsController',
        controllerAs: 'vm',
        requiredRoles: ['finance_admin', 'admin'],
      })
      .when('/finance/invoice-reports', {
        templateUrl: 'features/finance/invoice-reports/invoice-reports.html',
        controller: 'InvoiceReportsController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/or-reports', {
        templateUrl: 'features/finance/or-reports/or-reports.html',
        controller: 'OrReportsController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/daily-collections', {
        templateUrl: 'features/finance/daily-collections/daily-collections.html',
        controller: 'DailyCollectionsController',
        controllerAs: 'vm',
        requiredRoles: ['finance', 'admin'],
      })
      .when('/finance/non-student-payments', {
        templateUrl: 'features/finance/non-student-payments/non-student-payments.html',
        controller: 'NonStudentPaymentsController',
        controllerAs: 'vm',
        requiredRoles: ['finance','cashier_admin','admin'],
      })

      .when("/cashier-admin", {
        templateUrl: "features/cashiers/list.html",
        controller: "CashiersController",
        controllerAs: "vm",
        requiredRoles: ["cashier_admin","admin"],
      })
      // Admin: System Alerts
      .when("/admin/system-alerts", {
        templateUrl: "features/admin/system-alerts/system-alerts.html",
        controller: "AdminSystemAlertsController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Users Import
      .when("/admin/users-import", {
        templateUrl: "features/admin/users-import/users-import.html",
        controller: "AdminUsersImportController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Subjects Import
      .when("/admin/subjects-import", {
        templateUrl: "features/admin/subjects-import/subjects-import.html",
        controller: "AdminSubjectsImportController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Programs Import
      .when("/admin/programs-import", {
        templateUrl: "features/admin/programs-import/programs-import.html",
        controller: "AdminProgramsImportController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Curricula Import
      .when("/admin/curricula-import", {
        templateUrl: "features/admin/curricula-import/curricula-import.html",
        controller: "AdminCurriculaImportController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Invoices CRUD
      .when("/admin/invoices", {
        templateUrl: "features/admin/invoices/list.html",
        controller: "AdminInvoicesController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/admin/invoices/new", {
        templateUrl: "features/admin/invoices/edit.html",
        controller: "AdminInvoiceEditController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/admin/invoices/:id/edit", {
        templateUrl: "features/admin/invoices/edit.html",
        controller: "AdminInvoiceEditController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Student Editor (Prompt)
      .when("/admin/students/prompt", {
        templateUrl: "features/admin/students/prompt.html",
        controller: "AdminStudentPromptController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      // Admin: Student Editor
      .when("/admin/students/:id/edit", {
        templateUrl: "features/admin/students/edit.html",
        controller: "AdminStudentEditController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })

      .when("/admin/payment-details/import", {
        templateUrl: "features/admin/payment-details/import/payment-details-import.html",
        controller: "PaymentDetailsImportController",
        controllerAs: "vm",
        requiredRoles: ["finance_admin","admin"],
      })
      .when("/admin/payment-details", {
        templateUrl: "features/admin/payment-details/edit.html",
        controller: "AdminPaymentDetailsController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/roles", {
        templateUrl: "features/roles/roles.html",
        controller: "RolesController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/logs", {
        templateUrl: "features/logs/logs.html",
        controller: "SystemLogsController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
      .when("/student/dashboard", {
        templateUrl: "features/student-dashboard/student-dashboard.html",
        controller: "StudentDashboardController",
        controllerAs: "vm",
        requiredRoles: ["student_view", "admin"],
      })
      .when("/student/finances", {
        templateUrl: "features/student/finances/finances.html",
        controller: "StudentFinancesController",
        controllerAs: "vm",
        requiredRoles: ["student_view", "admin"],
      })
      .when("/student/applicant", {
        templateUrl: "features/student/applicant-viewer/student-applicant-viewer.html",
        controller: "StudentApplicantViewerController",
        controllerAs: "vm",
        requiredRoles: ["student_view", "admin"],
      })
      .when("/student/change-program-request", {
        templateUrl: "features/student/change-program-request/change-program-request.html",
        controller: "StudentChangeProgramRequestController",
        controllerAs: "vm",
        requiredRoles: ["student_view", "admin"],
      })
      .when("/public/initial-requirements/:hash", {
        templateUrl: "features/admissions/initial-requirements/initial-requirements.html",
        controller: "InitialRequirementsController",
        controllerAs: "vm"
      })
      // Clinic &amp; Health Records
      .when("/clinic", {
        templateUrl: "features/clinic/clinic.html",
        controller: "ClinicController",
        controllerAs: "vm",
        requiredRoles: ["clinic_staff", "clinic_admin", "admin"]
      })
      // Place 'new' route before parameterized :id to avoid collision
      .when("/clinic/records/new", {
        templateUrl: "features/clinic/record-new.html",
        controller: "ClinicRecordNewController",
        controllerAs: "vm",
        requiredRoles: ["clinic_staff", "clinic_admin", "admin"]
      })
      .when("/clinic/records/:id/edit", {
        templateUrl: "features/clinic/record-edit.html",
        controller: "ClinicRecordEditController",
        controllerAs: "vm",
        requiredRoles: ["clinic_staff", "clinic_admin", "admin"]
      })
      .when("/clinic/records/:id", {
        templateUrl: "features/clinic/record-view.html",
        controller: "ClinicRecordViewController",
        controllerAs: "vm",
        requiredRoles: ["clinic_staff", "clinic_admin", "admin"]
      })
      // Classlists
      .when("/classlists", {
        templateUrl: "features/classlists/list.html",
        controller: "ClasslistsController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "registrar", "admin"]
      })
      .when("/classlists/:id/viewer", {
        templateUrl: "features/classlists/viewer.html",
        controller: "ClasslistViewerController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "registrar", "admin"]
      })
      .when("/classlists/:id/attendance", {
        templateUrl: "features/classlists/attendance.html",
        controller: "ClasslistAttendanceController",
        controllerAs: "vm",
        requiredRoles: ["faculty", "admin"]
      })

      // Department Admin — Deficiencies
      .when('/department/deficiencies', {
        templateUrl: 'features/department/deficiencies/deficiencies.html',
        controller: 'DepartmentDeficienciesController',
        controllerAs: 'vm',
        requiredRoles: ['department_admin','admin'],
      })
      // Payments Checkout and Results
      .when("/payments/checkout", {
        templateUrl: "features/payments/checkout.html",
        controller: "PaymentsCheckoutController",
        controllerAs: "vm"
      })
      .when("/payments/success", {
        templateUrl: "features/payments/success.html",
        controller: "PaymentsResultController",
        controllerAs: "vm"
      })
      .when("/payments/failure", {
        templateUrl: "features/payments/failure.html",
        controller: "PaymentsResultController",
        controllerAs: "vm"
      })
      .when("/payments/cancel", {
        templateUrl: "features/payments/cancel.html",
        controller: "PaymentsResultController",
        controllerAs: "vm"
      })
      // Help / Docs (internal)
      .when("/docs", {
        templateUrl: "features/help/docs/docs.html",
        controller: "DocsController",
        controllerAs: "vm",
        requiredRoles: ["registrar","admissions","scholarship","finance","faculty_admin","finance_admin","admin"]
      })
      .when("/docs/:category/:page?", {
        templateUrl: "features/help/docs/docs.html",
        controller: "DocsController",
        controllerAs: "vm",
        requiredRoles: ["registrar","admissions","scholarship","finance","faculty_admin","finance_admin","admin"]
      })
      .otherwise({ redirectTo: "/login" });

    // Keep hashbang routing for simple static hosting
    $locationProvider.hashPrefix('');
  }

})();
