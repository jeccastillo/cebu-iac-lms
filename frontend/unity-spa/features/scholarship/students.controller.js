(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ScholarshipStudentsController', ScholarshipStudentsController);

  ScholarshipStudentsController.$inject = ['$http', '$q', '$scope', '$location', 'APP_CONFIG', 'LinkService', 'StorageService'];
  function ScholarshipStudentsController($http, $q, $scope, $location, APP_CONFIG, LinkService, StorageService) {
    var vm = this;

    vm.title = 'Students with Scholarships';
    vm.state = StorageService.getJSON('loginState');

    // extra guard (in addition to run.js)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Legacy CI links (used during migration) and SPA nav
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();

    // Data
    vm.loading = true;
    vm.error = null;
    vm.students = [];
    vm.terms = [];
    vm.selectedTerm = null;
    vm.searchQuery = '';
    vm.filteredStudents = [];

    // Methods
    vm.loadTerms = loadTerms;
    vm.loadStudents = loadStudents;
    vm.onTermChange = onTermChange;
    vm.filterStudents = filterStudents;
    vm.viewStudentScholarships = viewStudentScholarships;

    // Init
    init();

    function init() {
      loadTerms();
    }

    function loadTerms() {
      var BASE = APP_CONFIG.API_BASE;
      $http.get(BASE + '/generic/terms', getHeaders())
        .then(function (resp) {
          vm.terms = (resp.data && resp.data.data) ? resp.data.data : [];
          if (vm.terms.length > 0) {
            // Default to first term
            vm.selectedTerm = vm.terms[0].intID;
            loadStudents();
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load terms: ' + (err.data && err.data.message ? err.data.message : err.statusText);
          vm.loading = false;
        });
    }

    function loadStudents() {
      if (!vm.selectedTerm) {
        return;
      }

      vm.loading = true;
      vm.error = null;
      vm.students = [];
      vm.filteredStudents = [];

      var BASE = APP_CONFIG.API_BASE;
      var params = { syid: vm.selectedTerm };

      $http.get(BASE + '/scholarships/assignments', angular.extend({ params: params }, getHeaders()))
        .then(function (resp) {
          var items = (resp.data && resp.data.data && resp.data.data.items) ? resp.data.data.items : [];
          
          // Filter for active or pending assignments only
          items = items.filter(function(item) {
            return item.assignment_status === 'active' || item.assignment_status === 'pending';
          });

          // Group by student_id
          var studentMap = {};
          items.forEach(function(item) {
            if (!studentMap[item.student_id]) {
              // Initialize student with data from first assignment
              var student = item.student || {};
              studentMap[item.student_id] = {
                student_id: item.student_id,
                strStudentNumber: student.strStudentNumber || 'N/A',
                strFirstname: student.strFirstname || '',
                strLastname: student.strLastname || '',
                strMiddlename: student.strMiddlename || '',
                fullName: (student.strLastname || '') + ', ' + (student.strFirstname || '') + (student.strMiddlename ? ' ' + student.strMiddlename : ''),
                scholarships: [],
                discounts: []
              };
            }
            
            if (item.deduction_type === 'scholarship') {
              studentMap[item.student_id].scholarships.push(item);
            } else if (item.deduction_type === 'discount') {
              studentMap[item.student_id].discounts.push(item);
            }
          });

          // Convert map to array
          var students = [];
          for (var studentId in studentMap) {
            if (studentMap.hasOwnProperty(studentId)) {
              students.push(studentMap[studentId]);
            }
          }
          
          console.log('Final students array:', students);
          vm.students = students;
          vm.filteredStudents = students;
          vm.loading = false;
        })
        .catch(function (err) {
          vm.error = 'Failed to load students: ' + (err.data && err.data.message ? err.data.message : err.statusText);
          vm.loading = false;
        });
    }

    function onTermChange() {
      loadStudents();
    }

    function filterStudents() {
      if (!vm.searchQuery || vm.searchQuery.trim() === '') {
        vm.filteredStudents = vm.students;
        return;
      }

      var query = vm.searchQuery.toLowerCase();
      vm.filteredStudents = vm.students.filter(function(student) {
        return (student.strStudentNumber && student.strStudentNumber.toLowerCase().indexOf(query) !== -1) ||
               (student.strFirstname && student.strFirstname.toLowerCase().indexOf(query) !== -1) ||
               (student.strLastname && student.strLastname.toLowerCase().indexOf(query) !== -1) ||
               (student.fullName && student.fullName.toLowerCase().indexOf(query) !== -1);
      });
    }

    function viewStudentScholarships(studentId) {
      $location.path('/scholarship/assignments').search({ student_id: studentId, syid: vm.selectedTerm });
    }

    function getHeaders() {
      var headers = {};
      if (vm.state && vm.state.faculty_id) {
        headers['X-Faculty-ID'] = vm.state.faculty_id;
      }
      return { headers: headers };
    }
  }

})();
