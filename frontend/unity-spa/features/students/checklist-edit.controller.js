(function() {
  'use strict';

  angular.module('unityApp')
    .controller('ChecklistEditController', ChecklistEditController);

  ChecklistEditController.$inject = [
    '$routeParams', '$location', '$http', '$scope', 'APP_CONFIG', 'StorageService', 'ChecklistService'
  ];

  function ChecklistEditController($routeParams, $location, $http, $scope, APP_CONFIG, StorageService, ChecklistService) {
    var vm = this;

    // Route parameters
    vm.id = normalizeId($routeParams.student_id);

    // Navigation helpers
    vm.sn = $routeParams.sn || null; // Student number (optional)

    // UI state
    vm.loading = { checklist: false, checklistAction: false, subjects: false };
    vm.error = { checklist: null, checklistAction: null, subjects: null };
    vm.groupedChecklist = [];

    // Data
    vm.checklist = null;
    vm.checklistSummary = null;
    vm.student = null;
    vm.availableSubjects = [];

    // Edit state
    vm.editingItem = {};
    vm.addSubjectId = null;
    vm.newItem = {
      intYearLevel: '1',
      intSem: '1',
      strStatus: 'planned',
      isRequired: true
    };

    // API endpoints
    vm.api = {
      records: APP_CONFIG.API_BASE + '/students/records',
      subjects: APP_CONFIG.API_BASE + '/subjects'
    };

    // Helper functions
    function normalizeId(id) {
      if (id === null || id === undefined) return null;
      if (typeof id === 'number') return id;
      var n = parseInt(id, 10);
      if (!isNaN(n) && ('' + n) === ('' + id).replace(/^0+/, '')) return n;
      return ('' + id).trim();
    }

    function _adminHeaders() {
      var token = StorageService.get('session_token');
      return token ? { headers: { 'Authorization': 'Bearer ' + token } } : {};
    }

    // Date helpers for input[type=date] binding compatibility
    function parseDateYMD(s) {
      if (!s) return null;
      if (s instanceof Date) return s;
      try {
        var d = new Date(s);
        return isNaN(d.getTime()) ? null : d;
      } catch (e) {
        return null;
      }
    }

    function toYMD(d) {
      if (!d) return null;
      var dt = d instanceof Date ? d : new Date(d);
      if (isNaN(dt.getTime())) return null;
      var mm = ('0' + (dt.getMonth() + 1)).slice(-2);
      var dd = ('0' + dt.getDate()).slice(-2);
      return dt.getFullYear() + '-' + mm + '-' + dd;
    }

    // Helper function for semester label formatting
    vm.getSemesterLabel = function(semester) {
      if (semester == 1) return '1st Sem';
      else if (semester == 2) return '2nd Sem';
      else if (semester == 3) return '3rd Sem';
      else if (semester == 4) return 'Summer';
      else return semester || '';
    };

    // Helper function for status label formatting with color classes
    vm.getStatusInfo = function(status) {
      switch (status) {
        case 'passed':
          return { label: 'Passed', class: 'bg-green-100 text-green-800' };
        case 'failed':
          return { label: 'Failed', class: 'bg-red-100 text-red-800' };
        case 'enrolled':
          return { label: 'Currently Enrolled', class: 'bg-yellow-100 text-yellow-800' };
        case 'planned':
          return { label: 'Not Yet Taken', class: 'bg-blue-100 text-blue-800' };
        default:
          return { label: status || '-', class: 'bg-gray-100 text-gray-800' };
      }
    };

    // Group checklist items by term and year
    vm.groupChecklistByTerm = function() {
      if (!vm.checklist || !vm.checklist.items) {
        vm.groupedChecklist = [];
        return;
      }

      var grouped = {};
      
      vm.checklist.items.forEach(function(item) {
        var yearLevel = item.intYearLevel || 0;
        var semester = item.intSem || 0;
        var key = yearLevel + '-' + semester;
        
        // Helper function to get year level ordinal
        function getYearOrdinal(year) {
          if (year == 1) return '1st Year';
          else if (year == 2) return '2nd Year';
          else if (year == 3) return '3rd Year';
          else if (year == 4) return '4th Year';
          else if (year == 5) return '5th Year';
          else return year + ' Year';
        }
        
        if (!grouped[key]) {
          grouped[key] = {
            yearLevel: yearLevel,
            semester: semester,
            label: vm.getSemesterLabel(semester) + ' ' + getYearOrdinal(yearLevel),
            items: []
          };
        }
        grouped[key].items.push(item);
      });

      // Convert to array and sort
      vm.groupedChecklist = Object.keys(grouped).map(function(key) {
        return grouped[key];
      }).sort(function(a, b) {
        // Sort by year level first, then by semester
        if (a.yearLevel !== b.yearLevel) {
          return a.yearLevel - b.yearLevel;
        }
        return a.semester - b.semester;
      });
    };

    // Fetch checklist data
    vm.fetchChecklist = function () {
      vm.loading.checklist = true;
      vm.error.checklist = null;
      
      return ChecklistService.get(vm.id, {}).then(function(resp) {
        var data = resp && resp.data ? resp.data : (resp || null);
        vm.checklist = data;
        vm.groupChecklistByTerm();
        return ChecklistService.summary(vm.id, {});
      })
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : (resp || null);
          vm.checklistSummary = data;
        })
        .catch(function () {
          vm.error.checklist = 'Failed to load checklist.';
        })
        .finally(function () {
          vm.loading.checklist = false;
        });
    };

    // Generate new checklist
    vm.generateChecklist = function () {
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      var payload = {
        // intCurriculumID optional; backend falls back to tb_mas_users.intCurriculumID
      };
      return ChecklistService.generate(vm.id, payload)
        .then(function (resp) {
          return vm.fetchChecklist();
        })
        .catch(function (error) {
          vm.error.checklistAction = 'Failed to generate checklist.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    // Edit checklist item
    vm.editChecklistItem = function (item) {
      vm.editingItem = angular.copy(item);
      vm.editingItem.dteCompleted = vm.editingItem.dteCompleted ? parseDateYMD(vm.editingItem.dteCompleted) : null;
      
      // Ensure subject data is available
      if (!vm.editingItem.subject) {
        vm.editingItem.subject = {};
      }
      
      // Convert to strings for proper dropdown selection (AngularJS needs string values)
      vm.editingItem.intYearLevel = String(vm.editingItem.intYearLevel || 1);
      vm.editingItem.intSem = String(vm.editingItem.intSem || 1);
      vm.editingItem.strStatus = vm.editingItem.strStatus || 'planned';
      vm.editingItem.isRequired = vm.editingItem.isRequired !== undefined ? vm.editingItem.isRequired : true;
    };

    // Cancel edit
    vm.cancelEdit = function () {
      vm.editingItem = {};
    };

    // Save checklist item
    vm.saveChecklistItem = function () {
      if (!vm.editingItem || !vm.editingItem.id) return;
      var payload = {
        strStatus: vm.editingItem.strStatus,
        isRequired: vm.editingItem.isRequired ? 1 : 0,
        dteCompleted: toYMD(vm.editingItem.dteCompleted),
        intYearLevel: vm.editingItem.intYearLevel != null ? parseInt(vm.editingItem.intYearLevel, 10) : null,
        intSem: vm.editingItem.intSem != null ? parseInt(vm.editingItem.intSem, 10) : null
      };
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      ChecklistService.updateItem(vm.id, vm.editingItem.id, payload)
        .then(function () {
          vm.editingItem = {};
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to update item.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    // Remove checklist item
    vm.removeChecklistItem = function (item) {
      if (!item || !item.id) return;
      if (!confirm('Are you sure you want to remove this subject from the checklist?')) return;
      
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      ChecklistService.deleteItem(vm.id, item.id)
        .then(function () {
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to remove item.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    // Add new checklist item
    vm.addChecklistItem = function () {
      var sid = parseInt(vm.addSubjectId, 10);
      if (isNaN(sid) || !vm.checklist || !vm.checklist.id) return;
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      var payload = {
        intChecklistID: vm.checklist.id,
        intSubjectID: sid,
        strStatus: vm.newItem.strStatus,
        isRequired: vm.newItem.isRequired ? 1 : 0,
        intYearLevel: vm.newItem.intYearLevel != null ? parseInt(vm.newItem.intYearLevel, 10) : null,
        intSem: vm.newItem.intSem != null ? parseInt(vm.newItem.intSem, 10) : null
      };
      ChecklistService.addItem(vm.id, payload)
        .then(function () {
          vm.addSubjectId = null;
          // Reset form
          vm.newItem = {
            intYearLevel: '1',
            intSem: '1',
            strStatus: 'planned',
            isRequired: true
          };
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to add subject.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    // Fetch available subjects
    vm.fetchSubjects = function () {
      vm.loading.subjects = true;
      vm.error.subjects = null;
      return $http.get(vm.api.subjects, _adminHeaders())
        .then(function (resp) {
          var data = resp && resp.data ? (resp.data.data || resp.data) : [];
          vm.availableSubjects = data || [];
        })
        .catch(function () {
          vm.error.subjects = 'Failed to load available subjects.';
          vm.availableSubjects = [];
        })
        .finally(function () {
          vm.loading.subjects = false;
        });
    };

    // Fetch student meta data
    vm.fetchStudentMeta = function () {
      return $http.get(APP_CONFIG.API_BASE + '/students/' + encodeURIComponent(vm.id), _adminHeaders())
        .then(function (resp) {
          var data = (resp && resp.data && (resp.data.data || resp.data)) ? (resp.data.data || resp.data) : null;
          if (data) {
            var first = data.first_name || data.strFirstname || null;
            var last = data.last_name || data.strLastname || null;
            var sn = data.student_number || data.strStudentNumber || null;
            vm.student = {
              first_name: first,
              last_name: last,
              student_number: sn,
              program: data.program || data.strProgram || null,
              full_name: (last || first) ? ((last || '') + (last && first ? ', ' : '') + (first || '')) : null
            };
            if (!vm.sn && sn) {
              vm.sn = ('' + sn).trim();
            }
          }
        })
        .catch(function () { 
          /* ignore meta fetch errors */ 
        });
    };

    // Initialize the controller
    vm.init = function () {
      // Fetch student information first
      vm.fetchStudentMeta();
      
      // Fetch checklist data
      vm.fetchChecklist();
      
      // Fetch available subjects for the add form
      vm.fetchSubjects();
    };

    vm.init();
  }
})();