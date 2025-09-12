(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClinicController', ClinicController)
    .controller('ClinicRecordViewController', ClinicRecordViewController)
    .controller('ClinicRecordNewController', ClinicRecordNewController)
    .controller('ClinicRecordEditController', ClinicRecordEditController);

  ClinicController.$inject = ['$location', '$scope', 'ClinicService', 'CampusService'];
  function ClinicController($location, $scope, ClinicService, CampusService) {
    var vm = this;

    // Filters and state
    vm.loading = false;
    vm.error = null;
    vm.records = [];
    vm.meta = { total: 0, page: 1, per_page: 20, last_page: 1 };

    vm.filters = {
      q: '',
      student_number: '',
      faculty_id: '',
      last_name: '',
      first_name: '',
      program_id: '',
      year_level: '',
      diagnosis: '',
      medication: '',
      allergy: '',
      date_from: '',
      date_to: ''
    };

    // Actions
    vm.search = search;
    vm.clearFilters = clearFilters;
    vm.gotoPage = gotoPage;
    vm.viewRecord = viewRecord;
    vm.goNew = goNew;
    vm.editRecord = editRecord;

    activate();

    function activate() {
      search(1);
      // React to global campus changes
      if ($scope && $scope.$on) {
        $scope.$on('campusChanged', function () {
          search(1);
        });
      }
    }

    function search(page) {
      vm.loading = true;
      vm.error = null;

      var params = angular.copy(vm.filters) || {};
      params.page = page || vm.meta.page || 1;
      params.per_page = vm.meta.per_page || 20;

      // Apply selected campus automatically
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
          params.campus_id = parseInt(campus.id, 10);
        }
      } catch (e) {}

      // Clean empty params
      Object.keys(params).forEach(function (k) {
        if (params[k] === '' || params[k] === null || typeof params[k] === 'undefined') {
          delete params[k];
        }
      });

      ClinicService.searchRecords(params)
        .then(function (res) {
          if (!res || res.success !== true) {
            vm.error = 'Unexpected response';
            vm.records = [];
            vm.meta = { total: 0, page: 1, per_page: params.per_page, last_page: 1 };
            return;
          }
          vm.records = res.data || [];
          vm.meta = res.meta || { total: vm.records.length, page: 1, per_page: params.per_page, last_page: 1 };
        })
        .catch(function (err) {
          try {
            vm.error = (err && err.data && (err.data.message || err.data.error)) || 'Search failed';
          } catch (e) {
            vm.error = 'Search failed';
          }
          vm.records = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function clearFilters() {
      vm.filters = {
        q: '',
        student_number: '',
        faculty_id: '',
        last_name: '',
        first_name: '',
        program_id: '',
        year_level: '',
        diagnosis: '',
        medication: '',
        allergy: '',
        date_from: '',
        date_to: ''
      };
      search(1);
    }

    function gotoPage(p) {
      if (!p || p === vm.meta.page) return;
      if (p < 1) p = 1;
      if (p > (vm.meta.last_page || 1)) p = vm.meta.last_page || 1;
      search(p);
    }
 
    function goNew() {
      $location.path('/clinic/records/new');
    }
 
    function viewRecord(rec) {
      var id = (typeof rec === 'number') ? rec : (rec && rec.id);
      if (!id) return;
      $location.path('/clinic/records/' + id);
    }
    function editRecord(rec) {
      var id = (typeof rec === 'number') ? rec : (rec && rec.id);
      if (!id) return;
      $location.path('/clinic/records/' + id + '/edit');
    }
  }

  ClinicRecordViewController.$inject = ['$routeParams', 'ClinicService'];
  function ClinicRecordViewController($routeParams, ClinicService) {
    var vm = this;
    vm.id = parseInt($routeParams.id, 10);
    vm.loading = false;
    vm.error = null;
    vm.record = null;

    // Visits
    vm.visitsLoading = false;
    vm.visitsError = null;
    vm.visits = [];
    vm.vmeta = { total: 0, page: 1, per_page: 10, last_page: 1 };

    vm.refresh = refresh;
    vm.loadVisits = loadVisits;
    vm.gotoVisitPage = gotoVisitPage;

    activate();

    function activate() {
      refresh();
    }

    function refresh() {
      if (!vm.id || vm.id <= 0) return;
      vm.loading = true;
      vm.error = null;

      ClinicService.getRecord(vm.id)
        .then(function (res) {
          if (res && res.success) {
            vm.record = res.data;
            loadVisits(1);
          } else {
            vm.error = 'Failed to load record';
          }
        })
        .catch(function (err) {
          try {
            vm.error = (err && err.data && (err.data.message || err.data.error)) || 'Failed to load record';
          } catch (e) {
            vm.error = 'Failed to load record';
          }
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function loadVisits(page) {
      if (!vm.record || !vm.record.id) return;
      vm.visitsLoading = true;
      vm.visitsError = null;

      var params = { page: page || vm.vmeta.page || 1, per_page: vm.vmeta.per_page || 10 };
      ClinicService.listVisits(vm.record.id, params)
        .then(function (res) {
          if (res && res.success) {
            vm.visits = res.data || [];
            vm.vmeta = res.meta || { total: vm.visits.length, page: params.page, per_page: params.per_page, last_page: 1 };
          } else {
            vm.visitsError = 'Failed to load visits';
            vm.visits = [];
          }
        })
        .catch(function (err) {
          try {
            vm.visitsError = (err && err.data && (err.data.message || err.data.error)) || 'Failed to load visits';
          } catch (e) {
            vm.visitsError = 'Failed to load visits';
          }
          vm.visits = [];
        })
        .finally(function () {
          vm.visitsLoading = false;
        });
    }

    function gotoVisitPage(p) {
      if (!p || p === vm.vmeta.page) return;
      if (p < 1) p = 1;
      if (p > (vm.vmeta.last_page || 1)) p = vm.vmeta.last_page || 1;
      loadVisits(p);
    }
  }
 
  // New: ClinicRecordNewController
  ClinicRecordNewController.$inject = ['$location', '$scope', 'CampusService', 'ClinicService', 'StudentsService', 'FacultyService'];
  function ClinicRecordNewController($location, $scope, CampusService, ClinicService, StudentsService, FacultyService) {
    var vm = this;
    vm.saving = false;
    vm.error = null;
 
    vm.form = {
      person_type: 'student',
      person_student_id: null,
      person_faculty_id: null,
      blood_type: '',
      height_cm: null,
      weight_kg: null,
      allergies: [],
      medications: [],
      immunizations: [],
      conditions: [],
      notes: '',
      campus_id: null
    };

    // Default campus from global selector
    try {
      var campusInit = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
      vm.form.campus_id = (campusInit && campusInit.id !== undefined && campusInit.id !== null) ? parseInt(campusInit.id, 10) : null;
    } catch (e) {
      vm.form.campus_id = null;
    }
    if ($scope && $scope.$on) {
      $scope.$on('campusChanged', function (event, data) {
        var c = data && data.selectedCampus ? data.selectedCampus : null;
        vm.form.campus_id = (c && c.id !== undefined && c.id !== null) ? parseInt(c.id, 10) : null;
      });
    }
 
    // Autocomplete sources
    vm.studentResults = [];
    vm.facultyResults = [];
 
    vm.save = save;
    vm.cancel = cancel;
    vm.reset = reset;
    vm.onStudentQuery = onStudentQuery;
    vm.onFacultyQuery = onFacultyQuery;
 
    function reset() {
      vm.error = null;
      vm.form = {
        person_type: vm.form.person_type || 'student',
        person_student_id: null,
        person_faculty_id: null,
        blood_type: '',
        height_cm: null,
        weight_kg: null,
        allergies: [],
        medications: [],
        immunizations: [],
        conditions: [],
        notes: '',
        campus_id: null
      };
      // Reset campus to global selection
      try {
        var campusReset = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        vm.form.campus_id = (campusReset && campusReset.id !== undefined && campusReset.id !== null) ? parseInt(campusReset.id, 10) : null;
      } catch (e) { vm.form.campus_id = null; }
      vm.studentResults = [];
      vm.facultyResults = [];
    }
 
    function cancel() {
      $location.path('/clinic');
    }
 
    function save() {
      vm.error = null;
 
      var pt = vm.form.person_type;
      if (pt !== 'student' && pt !== 'faculty') {
        vm.error = 'Please select a person type.';
        return;
      }
      if (pt === 'student') {
        var sid = parseInt(vm.form.person_student_id, 10);
        if (!sid || sid <= 0) {
          vm.error = 'Student is required.';
          return;
        }
        vm.form.person_student_id = sid;
        vm.form.person_faculty_id = null;
      } else {
        var fid = parseInt(vm.form.person_faculty_id, 10);
        if (!fid || fid <= 0) {
          vm.error = 'Faculty is required.';
          return;
        }
        vm.form.person_faculty_id = fid;
        vm.form.person_student_id = null;
      }
 
      // Basic numeric sanity checks (front-end only)
      if (vm.form.height_cm != null && vm.form.height_cm !== '' && (vm.form.height_cm < 0 || vm.form.height_cm > 300)) {
        vm.error = 'Height must be between 0 and 300 cm.';
        return;
      }
      if (vm.form.weight_kg != null && vm.form.weight_kg !== '' && (vm.form.weight_kg < 0 || vm.form.weight_kg > 500)) {
        vm.error = 'Weight must be between 0 and 500 kg.';
        return;
      }

      // Ensure CSV helpers are coerced to arrays before submit (backend accepts arrays/objects)
      try {
        if (!Array.isArray(vm.form.allergies) || vm.form.allergies.length === 0) {
          if (typeof vm._allergiesCsv === 'string') {
            vm.form.allergies = (vm._allergiesCsv || '').split(',').map(function (s) {
              s = (s || '').trim();
              return s ? { name: s } : null;
            }).filter(Boolean);
            if (!vm.form.allergies.length) vm.form.allergies = null;
          }
        }
      } catch (e1) { /* ignore */ }
      try {
        if (!Array.isArray(vm.form.medications) || vm.form.medications.length === 0) {
          if (typeof vm._medicationsCsv === 'string') {
            vm.form.medications = (vm._medicationsCsv || '').split(',').map(function (s) {
              s = (s || '').trim();
              return s ? { name: s } : null;
            }).filter(Boolean);
            if (!vm.form.medications.length) vm.form.medications = null;
          }
        }
      } catch (e2) { /* ignore */ }
      try {
        if (!Array.isArray(vm.form.immunizations) || vm.form.immunizations.length === 0) {
          if (typeof vm._immunizationsCsv === 'string') {
            vm.form.immunizations = (vm._immunizationsCsv || '').split(',').map(function (s) {
              s = (s || '').trim();
              return s ? { name: s } : null;
            }).filter(Boolean);
            if (!vm.form.immunizations.length) vm.form.immunizations = null;
          }
        }
      } catch (e3) { /* ignore */ }
      try {
        if (!Array.isArray(vm.form.conditions) || vm.form.conditions.length === 0) {
          if (typeof vm._conditionsCsv === 'string') {
            vm.form.conditions = (vm._conditionsCsv || '').split(',').map(function (s) {
              s = (s || '').trim();
              return s ? { name: s } : null;
            }).filter(Boolean);
            if (!vm.form.conditions.length) vm.form.conditions = null;
          }
        }
      } catch (e4) { /* ignore */ }

      vm.saving = true;
      ClinicService.createOrUpdateRecord(vm.form)
        .then(function(res) {
          if (res && res.success && res.data && res.data.id) {
            $location.path('/clinic/records/' + res.data.id);
          } else {
            vm.error = 'Failed to save record.';
          }
        })
        .catch(function(err) {
          try {
            vm.error = (err && err.data && (err.data.message || err.data.error)) || 'Failed to save record.';
          } catch (e) {
            vm.error = 'Failed to save record.';
          }
        })
        .finally(function() {
          vm.saving = false;
        });
    }
 
    // Autocomplete: Students (first page suggestions)
    function onStudentQuery(q) {
      var term = (q || '').trim();
      if (term.length < 2) {
        vm.studentResults = [];
        return;
      }
      try {
        StudentsService.listSuggestions(term)
          .then(function (items) {
            var arr = Array.isArray(items) ? items : [];
            vm.studentResults = arr.map(function (s) {
              return {
                id: s.id,
                student_number: s.student_number || '',
                last_name: s.last_name || '',
                first_name: s.first_name || '',
                middle_name: s.middle_name || ''
              };
            });
          })
          .catch(function () { vm.studentResults = []; });
      } catch (e) {
        vm.studentResults = [];
      }
    }
 
    // Autocomplete: Faculty (admin-only or permitted roles)
    function onFacultyQuery(q) {
      var term = (q || '').trim();
      if (term.length < 2) {
        vm.facultyResults = [];
        return;
      }
      try {
        FacultyService.list({ q: term, page: 1, per_page: 20 })
          .then(function (res) {
            var rows = [];
            try {
              // unwrap common shapes
              if (Array.isArray(res)) {
                rows = res;
              } else if (res && Array.isArray(res.data)) {
                rows = res.data;
              } else if (res && res.data && Array.isArray(res.data.data)) {
                rows = res.data.data;
              } else {
                rows = [];
              }
            } catch (e) { rows = []; }
 
            vm.facultyResults = rows.map(function (it) {
              var id = (it.id != null ? it.id : (it.intID != null ? it.intID : null));
              var ln = it.last_name || it.strLastname || it.lastName || '';
              var fn = it.first_name || it.strFirstname || it.firstName || '';
              var full = it.full_name || (ln && fn ? (ln + ', ' + fn) : (ln || fn));
              if (!full) full = 'Faculty #' + (id != null ? id : '');
              return { id: id, full_name: full };
            }).filter(function (r) { return r.id != null; });
          })
          .catch(function () { vm.facultyResults = []; });
      } catch (e) {
        vm.facultyResults = [];
      }
    }
  }
 
  // Edit: ClinicRecordEditController
  ClinicRecordEditController.$inject = ['$routeParams', '$location', '$scope', 'CampusService', 'ClinicService'];
  function ClinicRecordEditController($routeParams, $location, $scope, CampusService, ClinicService) {
    var vm = this;
    vm.id = parseInt($routeParams.id, 10);
    vm.loading = false;
    vm.saving = false;
    vm.error = null;

    vm.person_type = null;
    vm.student_number = null;
    vm.student_name = null;
    vm.faculty_name = null;

    vm.form = {
      blood_type: '',
      height_cm: null,
      weight_kg: null,
      allergies: [],
      medications: [],
      immunizations: [],
      conditions: [],
      notes: '',
      campus_id: null
    };

    vm._allergiesCsv = '';
    vm._medicationsCsv = '';
    vm._immunizationsCsv = '';
    vm._conditionsCsv = '';

    vm.load = load;
    vm.save = save;
    vm.cancel = cancel;

    // Default campus from global selector
    try {
      var campusInit = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
      vm.form.campus_id = (campusInit && campusInit.id !== undefined && campusInit.id !== null) ? parseInt(campusInit.id, 10) : null;
    } catch (e) { vm.form.campus_id = null; }
    if ($scope && $scope.$on) {
      $scope.$on('campusChanged', function (event, data) {
        var c = data && data.selectedCampus ? data.selectedCampus : null;
        vm.form.campus_id = (c && c.id !== undefined && c.id !== null) ? parseInt(c.id, 10) : null;
      });
    }

    activate();
    function activate() {
      if (!vm.id || vm.id <= 0) {
        vm.error = 'Invalid record id.';
        return;
      }
      load();
    }

    function load() {
      vm.loading = true;
      vm.error = null;
      ClinicService.getRecord(vm.id)
        .then(function (res) {
          if (res && res.success && res.data) {
            var d = res.data;
            vm.person_type = d.person_type || null;
            vm.student_number = d.student_number || null;
            vm.student_name = d.student_name || null;
            vm.faculty_name = d.faculty_name || null;

            vm.form.blood_type = d.blood_type || '';
            vm.form.height_cm = d.height_cm != null ? parseFloat(d.height_cm) : null;
            vm.form.weight_kg = d.weight_kg != null ? parseFloat(d.weight_kg) : null;
            vm.form.allergies = Array.isArray(d.allergies) ? d.allergies : [];
            vm.form.medications = Array.isArray(d.medications) ? d.medications : [];
            vm.form.immunizations = Array.isArray(d.immunizations) ? d.immunizations : [];
            vm.form.conditions = Array.isArray(d.conditions) ? d.conditions : [];
            vm.form.notes = d.notes || '';
            vm.form.campus_id = d.campus_id != null ? parseInt(d.campus_id, 10) : vm.form.campus_id;

            function arrToCsv(arr) {
              try {
                return (arr || []).map(function (it) { return (it && it.name) ? it.name : ''; })
                  .filter(function (s) { return !!s; }).join(', ');
              } catch (e) { return ''; }
            }
            vm._allergiesCsv = arrToCsv(vm.form.allergies);
            vm._medicationsCsv = arrToCsv(vm.form.medications);
            vm._immunizationsCsv = arrToCsv(vm.form.immunizations);
            vm._conditionsCsv = arrToCsv(vm.form.conditions);
          } else {
            vm.error = 'Failed to load record.';
          }
        })
        .catch(function (err) {
          try { vm.error = (err && err.data && (err.data.message || err.data.error)) || 'Failed to load record.'; }
          catch (e) { vm.error = 'Failed to load record.'; }
        })
        .finally(function () { vm.loading = false; });
    }

    function cancel() {
      $location.path('/clinic/records/' + vm.id);
    }

    function save() {
      vm.error = null;

      if (vm.form.height_cm != null && vm.form.height_cm !== '' && (vm.form.height_cm < 0 || vm.form.height_cm > 300)) {
        vm.error = 'Height must be between 0 and 300 cm.';
        return;
      }
      if (vm.form.weight_kg != null && vm.form.weight_kg !== '' && (vm.form.weight_kg < 0 || vm.form.weight_kg > 500)) {
        vm.error = 'Weight must be between 0 and 500 kg.';
        return;
      }

      // Coerce CSV helpers into arrays if form arrays are empty
      try {
        if (!Array.isArray(vm.form.allergies) || vm.form.allergies.length === 0) {
          vm.form.allergies = (vm._allergiesCsv || '').split(',').map(function (s) {
            s = (s || '').trim();
            return s ? { name: s } : null;
          }).filter(Boolean);
          if (!vm.form.allergies.length) vm.form.allergies = null;
        }
      } catch (e1) {}
      try {
        if (!Array.isArray(vm.form.medications) || vm.form.medications.length === 0) {
          vm.form.medications = (vm._medicationsCsv || '').split(',').map(function (s) {
            s = (s || '').trim();
            return s ? { name: s } : null;
          }).filter(Boolean);
          if (!vm.form.medications.length) vm.form.medications = null;
        }
      } catch (e2) {}
      try {
        if (!Array.isArray(vm.form.immunizations) || vm.form.immunizations.length === 0) {
          vm.form.immunizations = (vm._immunizationsCsv || '').split(',').map(function (s) {
            s = (s || '').trim();
            return s ? { name: s } : null;
          }).filter(Boolean);
          if (!vm.form.immunizations.length) vm.form.immunizations = null;
        }
      } catch (e3) {}
      try {
        if (!Array.isArray(vm.form.conditions) || vm.form.conditions.length === 0) {
          vm.form.conditions = (vm._conditionsCsv || '').split(',').map(function (s) {
            s = (s || '').trim();
            return s ? { name: s } : null;
          }).filter(Boolean);
          if (!vm.form.conditions.length) vm.form.conditions = null;
        }
      } catch (e4) {}

      vm.saving = true;
      var payload = {
        blood_type: vm.form.blood_type || null,
        height_cm: vm.form.height_cm != null && vm.form.height_cm !== '' ? parseFloat(vm.form.height_cm) : null,
        weight_kg: vm.form.weight_kg != null && vm.form.weight_kg !== '' ? parseFloat(vm.form.weight_kg) : null,
        allergies: vm.form.allergies || null,
        medications: vm.form.medications || null,
        immunizations: vm.form.immunizations || null,
        conditions: vm.form.conditions || null,
        notes: vm.form.notes || null,
        campus_id: vm.form.campus_id != null ? parseInt(vm.form.campus_id, 10) : null
      };

      ClinicService.updateRecord(vm.id, payload)
        .then(function (res) {
          if (res && res.success) {
            $location.path('/clinic/records/' + vm.id);
          } else {
            vm.error = 'Failed to save changes.';
          }
        })
        .catch(function (err) {
          try { vm.error = (err && err.data && (err.data.message || err.data.error)) || 'Failed to save changes.'; }
          catch (e) { vm.error = 'Failed to save changes.'; }
        })
        .finally(function () { vm.saving = false; });
    }
  }
})();
