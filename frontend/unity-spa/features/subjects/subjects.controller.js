
(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SubjectsListController', SubjectsListController)
    .controller('SubjectEditController', SubjectEditController);

  SubjectsListController.$inject = ['$location', '$window', 'StorageService', 'SubjectsService'];
  function SubjectsListController($location, $window, StorageService, SubjectsService) {
    var vm = this;

    vm.title = 'Subjects';
    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.filters = {
      search: '',
      department: '',
      page: 1,
      limit: 25
    };

    vm.rows = [];
    vm.meta = { total: 0, page: 1, limit: 25 };
    vm.loading = false;
    vm.error = null;

    vm.search = function () {
      vm.loading = true;
      vm.error = null;

      var opts = {
        search: (vm.filters.search || '').trim(),
        department: vm.filters.department || '',
        page: vm.filters.page || 1,
        limit: vm.filters.limit || 25
      };

      SubjectsService.list(opts)
        .then(function (data) {
          // Expecting { success: true, data: [...], meta: {...} }
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
            if (data.meta) vm.meta = data.meta;
          } else if (angular.isArray(data)) {
            vm.rows = data;
            vm.meta = { total: data.length, page: 1, limit: data.length };
          } else {
            vm.rows = [];
            vm.meta = { total: 0, page: vm.filters.page, limit: vm.filters.limit };
          }
        })
        .catch(function (e) {
          vm.error = 'Failed to load subjects.';
          vm.rows = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.add = function () {
      $location.path('/subjects/add');
    };

    vm.edit = function (row) {
      var id = row && (row.intID || row.id) ? (row.intID || row.id) : row;
      if (!id) return;
      $location.path('/subjects/' + id + '/edit');
    };

    vm.delete = function (row) {
      var id = row && (row.intID || row.id) ? (row.intID || row.id) : row;
      if (!id) return;
      if ($window.confirm('Delete this subject? This action cannot be undone.')) {
        vm.loading = true;
        vm.error = null;
        SubjectsService.destroy(id)
          .then(function () {
            vm.search();
          })
          .catch(function (err) {
            var msg = 'Delete failed.';
            if (err && err.data && err.data.message) msg = err.data.message;
            vm.error = msg;
          })
          .finally(function () { vm.loading = false; });
      }
    };

    vm.pageTo = function (p) {
      p = parseInt(p, 10);
      if (!p || p < 1) p = 1;
      vm.filters.page = p;
      vm.search();
    };

    // Init
    vm.search();
  }

  SubjectEditController.$inject = ['$routeParams', '$location', 'StorageService', 'SubjectsService', 'GradingService', '$scope'];
  function SubjectEditController($routeParams, $location, StorageService, SubjectsService, GradingService, $scope) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.isEdit = !!vm.id;
    vm.title = vm.isEdit ? 'Edit Subject' : 'Add Subject';

    vm.loading = false;
    vm.error = null;
    vm.success = null;

    // Model mirrors legacy tb_mas_subjects columns; defaults aligned with SubjectSubmitRequest
    vm.model = {
      strCode: '',
      strDescription: '',
      strUnits: '0',
      strTuitionUnits: null,
      strLabClassification: 'none',
      intLab: 0,
      strDepartment: null,
      intLectHours: 0,
      intPrerequisiteID: 0,
      intEquivalentID1: 0,
      intEquivalentID2: 0,
      intProgramID: 0,
      isNSTP: 0,
      isThesisSubject: 0,
      isInternshipSubject: 0,
      include_gwa: 0,
      grading_system_id: null,
      grading_system_id_midterm: null,
      isElective: 0,
      isSelectableElective: 0,
      strand: null,
      intBridging: 0,
      intMajor: 0
    };

    // Grading systems
    vm.gradingSystems = [];
    vm.loadGradingSystems = function () {
      GradingService.list()
        .then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.gradingSystems = (rows || []).map(function (r) {
            return { id: (r && r.id != null) ? parseInt(r.id, 10) : null, name: (r && r.name) ? r.name : ('ID ' + (r && r.id)) };
          });
        })
        .catch(function () { vm.gradingSystems = []; });
    };

    // Prerequisites management
    vm.prereqs = [];
    vm.prereqsLoading = false;
    vm.addPrereqId = null;
    vm.addPrereqProgram = '';
    vm.availableSubjects = [];

    vm.loadAvailableSubjects = function () {
      SubjectsService.list({ limit: 1000 }) // Load all subjects for dropdown
        .then(function (data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.availableSubjects = data.data;
          } else if (angular.isArray(data)) {
            vm.availableSubjects = data;
          } else {
            vm.availableSubjects = [];
          }
        })
        .catch(function () {
          vm.availableSubjects = [];
        });
    };

    vm.loadPrereqs = function () {
      if (!vm.isEdit) { vm.prereqs = []; return; }
      vm.prereqsLoading = true;
      SubjectsService.getPrereqs(vm.id)
        .then(function (data) {
          vm.prereqs = (data && data.success !== false && angular.isArray(data.data)) ? data.data : (angular.isArray(data) ? data : []);
        })
        .catch(function () {
          vm.prereqs = [];
        })
        .finally(function () { vm.prereqsLoading = false; });
    };

    vm.addPrereq = function () {
      var pid = vm.addPrereqId !== null && vm.addPrereqId !== undefined && vm.addPrereqId !== '' ? parseInt(vm.addPrereqId, 10) : 0;
      if (!vm.isEdit || !vm.id) { vm.error = 'Save subject before adding prerequisites.'; return; }
      if (!pid || pid <= 0) { vm.error = 'Enter a valid prerequisite subject ID.'; return; }
      if (pid === vm.id) { vm.error = 'A subject cannot be a prerequisite of itself.'; return; }

      vm.error = null;
      SubjectsService.addPrereq(vm.id, pid, (vm.addPrereqProgram || undefined))
        .then(function (res) {
          vm.addPrereqId = null;
          vm.addPrereqProgram = '';
          vm.loadPrereqs();
        })
        .catch(function (err) {
          var msg = 'Failed to add prerequisite.';
          if (err && err.data && err.data.message) msg = err.data.message;
          vm.error = msg;
        });
    };

    vm.removePrereq = function (row) {
      var id = row && (row.id !== undefined && row.id !== null) ? parseInt(row.id, 10) : null;
      if (!id) return;
      SubjectsService.removePrereq(id)
        .then(function () {
          vm.loadPrereqs();
        })
        .catch(function (err) {
          var msg = 'Failed to remove prerequisite.';
          if (err && err.data && err.data.message) msg = err.data.message;
          vm.error = msg;
        });
    };

    // Corequisites management
    vm.coreqs = [];
    vm.coreqsLoading = false;
    vm.addCoreqId = null;
    vm.addCoreqProgram = '';

    vm.loadCoreqs = function () {
      if (!vm.isEdit) { vm.coreqs = []; return; }
      vm.coreqsLoading = true;
      SubjectsService.getCoreqs(vm.id)
        .then(function (data) {
          vm.coreqs = (data && data.success !== false && angular.isArray(data.data)) ? data.data : (angular.isArray(data) ? data : []);
        })
        .catch(function () {
          vm.coreqs = [];
        })
        .finally(function () { vm.coreqsLoading = false; });
    };

    vm.addCoreq = function () {
      var cid = vm.addCoreqId !== null && vm.addCoreqId !== undefined && vm.addCoreqId !== '' ? parseInt(vm.addCoreqId, 10) : 0;
      if (!vm.isEdit || !vm.id) { vm.error = 'Save subject before adding corequisites.'; return; }
      if (!cid || cid <= 0) { vm.error = 'Enter a valid corequisite subject ID.'; return; }
      if (cid === vm.id) { vm.error = 'A subject cannot be a corequisite of itself.'; return; }

      vm.error = null;
      SubjectsService.addCoreq(vm.id, cid, (vm.addCoreqProgram || undefined))
        .then(function () {
          vm.addCoreqId = null;
          vm.addCoreqProgram = '';
          vm.loadCoreqs();
        })
        .catch(function (err) {
          var msg = 'Failed to add corequisite.';
          if (err && err.data && err.data.message) msg = err.data.message;
          vm.error = msg;
        });
    };

    vm.removeCoreq = function (row) {
      var id = row && (row.id !== undefined && row.id !== null) ? parseInt(row.id, 10) : null;
      if (!id) return;
      SubjectsService.removeCoreq(id)
        .then(function () {
          vm.loadCoreqs();
        })
        .catch(function (err) {
          var msg = 'Failed to remove corequisite.';
          if (err && err.data && err.data.message) msg = err.data.message;
          vm.error = msg;
        });
    };

    vm.load = function () {
      if (!vm.isEdit) return;
      vm.loading = true;
      vm.error = null;
      SubjectsService.get(vm.id)
        .then(function (data) {
          var row = (data && data.success !== false && data.data) ? data.data : data;
          if (!row || (!row.intID && !row.id)) {
            vm.error = 'Subject not found.';
            return;
          }
          // Map fields with safe parsing for ints/bools represented as strings
          vm.model.strCode = (row.strCode || '').trim();
          vm.model.strDescription = (row.strDescription || '').trim();
          vm.model.strUnits = (row.strUnits !== undefined && row.strUnits !== null) ? ('' + row.strUnits) : '0';
          vm.model.strTuitionUnits = (row.strTuitionUnits !== undefined && row.strTuitionUnits !== null && row.strTuitionUnits !== '') ? ('' + row.strTuitionUnits) : null;
          vm.model.strLabClassification = (row.strLabClassification || 'none');
          vm.model.intLab = toInt(row.intLab, 0);
          vm.model.strDepartment = (row.strDepartment !== undefined && row.strDepartment !== null && row.strDepartment !== '') ? ('' + row.strDepartment) : null;
          vm.model.intLectHours = toInt(row.intLectHours, 0);
          vm.model.intPrerequisiteID = toInt(row.intPrerequisiteID, 0);
          vm.model.intEquivalentID1 = toInt(row.intEquivalentID1, 0);
          vm.model.intEquivalentID2 = toInt(row.intEquivalentID2, 0);
          vm.model.intProgramID = toInt(row.intProgramID, 0);
          vm.model.isNSTP = toInt(row.isNSTP, 0) ? 1 : 0;
          vm.model.isThesisSubject = toInt(row.isThesisSubject, 0) ? 1 : 0;
          vm.model.isInternshipSubject = toInt(row.isInternshipSubject, 0) ? 1 : 0;
          vm.model.include_gwa = toInt(row.include_gwa, 0) ? 1 : 0;
          vm.model.grading_system_id = toNullableInt(row.grading_system_id);
          vm.model.grading_system_id_midterm = toNullableInt(row.grading_system_id_midterm);
          vm.model.isElective = toInt(row.isElective, 0) ? 1 : 0;
          vm.model.isSelectableElective = toInt(row.isSelectableElective, 0) ? 1 : 0;
          vm.model.strand = (row.strand !== undefined && row.strand !== null && row.strand !== '') ? ('' + row.strand) : null;
          vm.model.intBridging = toInt(row.intBridging, 0);
          vm.model.intMajor = toInt(row.intMajor, 0);

          vm.loadPrereqs();
          vm.loadCoreqs();
        })
        .catch(function () {
          vm.error = 'Failed to load subject.';
        })
        .finally(function () { vm.loading = false; });
    };

    vm.save = function () {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        strCode: (vm.model.strCode || '').trim(),
        strDescription: (vm.model.strDescription || '').trim(),
        strUnits: (vm.model.strUnits !== undefined && vm.model.strUnits !== null && vm.model.strUnits !== '') ? ('' + vm.model.strUnits) : '0',
        strTuitionUnits: (vm.model.strTuitionUnits !== undefined && vm.model.strTuitionUnits !== null && vm.model.strTuitionUnits !== '') ? ('' + vm.model.strTuitionUnits) : null,
        strLabClassification: (vm.model.strLabClassification || 'none'),
        intLab: toInt(vm.model.intLab, 0),
        strDepartment: (vm.model.strDepartment !== undefined && vm.model.strDepartment !== null && vm.model.strDepartment !== '') ? ('' + vm.model.strDepartment) : null,
        intLectHours: toInt(vm.model.intLectHours, 0),
        intPrerequisiteID: toInt(vm.model.intPrerequisiteID, 0),
        intEquivalentID1: toInt(vm.model.intEquivalentID1, 0),
        intEquivalentID2: toInt(vm.model.intEquivalentID2, 0),
        intProgramID: toInt(vm.model.intProgramID, 0),
        isNSTP: vm.model.isNSTP ? 1 : 0,
        isThesisSubject: vm.model.isThesisSubject ? 1 : 0,
        isInternshipSubject: vm.model.isInternshipSubject ? 1 : 0,
        include_gwa: vm.model.include_gwa ? 1 : 0,
        grading_system_id: toNullableInt(vm.model.grading_system_id),
        grading_system_id_midterm: toNullableInt(vm.model.grading_system_id_midterm),
        isElective: vm.model.isElective ? 1 : 0,
        isSelectableElective: vm.model.isSelectableElective ? 1 : 0,
        strand: (vm.model.strand !== undefined && vm.model.strand !== null && vm.model.strand !== '') ? ('' + vm.model.strand) : null,
        intBridging: toInt(vm.model.intBridging, 0),
        intMajor: toInt(vm.model.intMajor, 0)
      };

      var p = vm.isEdit
        ? SubjectsService.update(vm.id, payload)
        : SubjectsService.create(payload);

      p.then(function (data) {
          if (data && data.success !== false) {
            vm.success = 'Saved.';
            if (!vm.isEdit) {
              // On create, navigate to edit for prerequisites
              setTimeout(function () {
                try { vm.success = null; } catch (e) {}
                var newid = (data.newid !== undefined && data.newid !== null) ? parseInt(data.newid, 10) : null;
                if (newid) {
                  window.location.hash = '#/subjects/' + newid + '/edit';
                } else {
                  window.location.hash = '#/subjects';
                }
              }, 300);
            } else {
              // In edit flow, just refresh prereqs
              vm.loadPrereqs();
              setTimeout(function () {
                try { vm.success = null; } catch (e) {}
              }, 500);
            }
          } else {
            vm.error = 'Save failed.';
          }
        })
        .catch(function (err) {
          if (err && err.data && err.data.errors) {
            var firstKey = Object.keys(err.data.errors)[0];
            vm.error = (err.data.errors[firstKey] && err.data.errors[firstKey][0]) || 'Validation failed.';
          } else if (err && err.data && err.data.message) {
            vm.error = err.data.message;
          } else {
            vm.error = 'Save failed.';
          }
        })
        .finally(function () { vm.loading = false; });
    };

    vm.cancel = function () {
      $location.path('/subjects');
    };

    function toInt(v, d) {
      var n = parseInt(v, 10);
      return isNaN(n) ? (d || 0) : n;
    }
    function toNullableInt(v) {
      if (v === undefined || v === null || v === '') return null;
      var n = parseInt(v, 10);
      return isNaN(n) ? null : n;
    }

    // Init
    vm.load();
    vm.loadAvailableSubjects();
    vm.loadGradingSystems();
  }

})();
