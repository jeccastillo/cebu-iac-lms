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
      .when("/finance/ledger", {
        templateUrl: "features/finance/ledger.html",
        controller: "FinanceLedgerController",
        controllerAs: "vm",
        requiredRoles: ["finance", "admin"],
      })
      .when("/scholarship/students", {
        templateUrl: "features/scholarship/students.html",
        controller: "ScholarshipStudentsController",
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
      .when("/admissions/apply", {
        templateUrl: "features/admissions/apply.html",
        controller: "AdmissionsApplyController",
        controllerAs: "vm",
      })
      .when("/admissions/success", {
        templateUrl: "features/admissions/success.html",
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

      /* Classrooms CRUD */
      .when("/classrooms", {
        templateUrl: "features/classrooms/classrooms.html",
        controller: "ClassroomsController",
        controllerAs: "vm",
        requiredRoles: ["building_admin", "admin"],
      })
      .when("/classrooms/add", {
        templateUrl: "features/classrooms/classroom-edit.html",
        controller: "ClassroomEditController",
        controllerAs: "vm",
        requiredRoles: ["building_admin", "admin"],
      })
      .when("/classrooms/:id/edit", {
        templateUrl: "features/classrooms/classroom-edit.html",
        controller: "ClassroomEditController",
        controllerAs: "vm",
        requiredRoles: ["building_admin", "admin"],
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
      .otherwise({ redirectTo: "/login" });

    // Keep hashbang routing for simple static hosting
    $locationProvider.hashPrefix('');
  }

})();
