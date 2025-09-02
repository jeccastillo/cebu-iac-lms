(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdmissionsApplyController', AdmissionsApplyController);

  AdmissionsApplyController.$inject = ['$http', '$location', 'APP_CONFIG', '$q', 'CampusService', 'SchoolYearsService', 'ProgramsService', '$scope'];

  function AdmissionsApplyController($http, $location, APP_CONFIG, $q, CampusService, SchoolYearsService, ProgramsService, $scope) {
    var vm = this;

    vm.loading = false;
    vm.programs = [];
    vm.error = null;

    // Campus and term selection state
    vm.campuses = [];
    vm.selectedCampus = null;

    vm.termTypes = [];    // distinct term_student_type values (open terms only)
    vm.termOptions = [];  // open terms filtered by selected term_student_type

    // Loading flags for program section (student types/terms)
    vm.loadingTerms = false;
    vm.programLoading = false;

    vm.form = {
      // Basic Info
      first_name: '',
      middle_name: '',
      last_name: '',
      gender: '',
      date_of_birth: '',
      // Contact
      email: '',
      email_confirmation: '',
      mobile_number: '',
      mobile_number_confirmation: '',
      // Address
      address: '',
      city: '',
      province: '',
      country: '',
      // Program / Type
      student_type: '',  // selected term_student_type
      type_id: '',       // program (first choice)
      campus: '',        // kept for backend compatibility (filled on submit)
      term: null,        // tb_mas_sy.intID (selected term)
      campus_id: null    // kept for backend completeness (filled on submit)
    };

    vm.genders = ['Male', 'Female'];

    vm.submit = submit;
    vm.onCampusChange = onCampusChange;
    vm.onTermTypeChange = onTermTypeChange;
    vm.loadProgramsForSelection = loadProgramsForSelection;

    activate();

    function activate() {
      // React to global campus changes
      if ($scope && $scope.$on) {
        $scope.$on('campusChanged', function (evt, data) {
          try {
            vm.selectedCampus = data && data.selectedCampus ? data.selectedCampus : vm.selectedCampus;
            onCampusChange();
            if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
          } catch (e) {}
        });
      }
      // Load programs and initialize campus/terms
      vm.loading = true;

      // Defer program loading until a student level is selected
      var loadPrograms = $q.resolve().then(function () {
        vm.programs = [];
      });

      var initCampuses = (CampusService && CampusService.init ? CampusService.init() : $q.resolve(null))
        .then(function () {
          try {
            vm.campuses = CampusService.availableCampuses || [];
            vm.selectedCampus = CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
          } catch (e) {
            vm.campuses = vm.campuses || [];
          }
          if (!vm.selectedCampus && vm.campuses && vm.campuses.length) {
            vm.selectedCampus = vm.campuses[0];
          }
          if (vm.selectedCampus && vm.selectedCampus.id !== undefined && vm.selectedCampus.id !== null) {
            return loadCampusTerms(vm.selectedCampus.id);
          }
          vm.termTypes = [];
          vm.termOptions = [];
          return $q.resolve([]);
        })
        .catch(function (err) {
          console.error('Campus init error', err);
          vm.campuses = vm.campuses || [];
          vm.selectedCampus = vm.selectedCampus || null;
          vm.termTypes = [];
          vm.termOptions = [];
          return [];
        });

      $q.all([loadPrograms, initCampuses])
        .finally(function () {
          vm.loading = false;
        });
    }

    function submit() {
      vm.error = null;

      // Basic validations
      if (!vm.form.email || vm.form.email !== vm.form.email_confirmation) {
        vm.error = 'Email address does not match confirmation.';
        return;
      }
      if (!vm.form.mobile_number || vm.form.mobile_number !== vm.form.mobile_number_confirmation) {
        vm.error = 'Mobile number does not match confirmation.';
        return;
      }
      if (!vm.form.type_id) {
        vm.error = 'Please select a program.';
        return;
      }
      if (!vm.selectedCampus || vm.selectedCampus.id === undefined || vm.selectedCampus.id === null) {
        vm.error = 'Please select a campus.';
        return;
      }
      if (!vm.form.student_type) {
        vm.error = 'Please select a student type.';
        return;
      }
      if (!vm.form.term) {
        vm.error = 'Please select a term.';
        return;
      }

      // Prepare payload
      var payload = angular.copy(vm.form);
      payload.campus = (vm.selectedCampus && vm.selectedCampus.campus_name) || payload.campus || '';
      payload.campus_id = vm.selectedCampus ? vm.selectedCampus.id : null;
      payload.term = vm.form.term || null; // tb_mas_sy.intID

      vm.loading = true;
      $http.post(APP_CONFIG.API_BASE + '/admissions/student-info', payload)
        .then(function (resp) {
          if (resp.data && resp.data.success) {
            // Redirect to success page
            $location.path('/admissions/success');
          } else {
            vm.error = (resp.data && resp.data.message) || 'Submission failed.';
          }
        })
        .catch(function (err) {
          vm.error = (err.data && err.data.message) || 'Submission failed.';
          console.error('Submission error', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Handlers
    function onCampusChange() {
      if (!vm.selectedCampus || vm.selectedCampus.id === undefined || vm.selectedCampus.id === null) {
        vm.termTypes = [];
        vm.termOptions = [];
        vm.form.student_type = '';
        vm.form.term = null;
        return;
      }
      // Reset selections and reload
      vm.form.student_type = '';
      vm.form.term = null;
      vm.form.type_id = '';
      loadCampusTerms(vm.selectedCampus.id);
      try { vm.loadProgramsForSelection(); } catch (e) {}
    }

    function onTermTypeChange() {
      try {
        if (!vm._openTerms || !vm._openTerms.length) {
          vm.termOptions = [];
          vm.form.term = null;
          return;
        }
        if (!vm.form.student_type) {
          vm.termOptions = [];
          vm.form.term = null;
          return;
        }
        vm.termOptions = vm._openTerms
          .filter(function (r) { return (r.term_student_type || '') === vm.form.student_type; })
          .map(function (r) {
            r._label = buildTermLabel(r);
            return r;
          });
        vm.form.term = null;
      } catch (e) {
        vm.termOptions = [];
        vm.form.term = null;
      }
      // Reset program and reload programs based on new selection
      vm.form.type_id = '';
      try { vm.loadProgramsForSelection(); } catch (e) {}
    }

    // Helpers
    function isOpenTerm(row) {
      if (!row) return false;
      var val = row.endOfApplicationPeriod || null;
      if (!val) return false;
      // Normalize "YYYY-MM-DD HH:mm:ss" to ISO-like for Date parsing
      var normalized = (typeof val === 'string') ? val.replace(' ', 'T') : val;
      var d = new Date(normalized);
      if (isNaN(d.getTime())) return false;
      return d.getTime() > Date.now();
    }

    function buildTermLabel(r) {
      var base = (r.term_label === 'Semester' ? 'Sem' : (r.term_label || 'Term'));
      return (r.enumSem || '') + ' ' + base + ' ' + (r.strYearStart || '') + '-' + (r.strYearEnd || '');
    }

    function setOpenTermCollections() {
      var open = vm._openTerms || [];
      // Distinct term_student_type
      var map = {};
      for (var i = 0; i < open.length; i++) {
        var t = open[i].term_student_type;
        if (t && t.trim() !== '') map[t] = true;
      }
      vm.termTypes = Object.keys(map).sort();

      // Populate options if a type already selected
      if (vm.form.student_type) {
        vm.termOptions = open
          .filter(function (r) { return (r.term_student_type || '') === vm.form.student_type; })
          .map(function (r) { r._label = buildTermLabel(r); return r; });
      } else {
        vm.termOptions = [];
      }
    }

    function loadCampusTerms(campusId) {
      vm.loadingTerms = true;
      return SchoolYearsService
        .list({ campus_id: campusId })
        .then(function (body) {
          var rows = (body && body.data) ? body.data : (Array.isArray(body) ? body : []);
          // Keep only terms where endOfApplicationPeriod > now
          vm._openTerms = rows.filter(isOpenTerm);
          setOpenTermCollections();
          // After terms/types updated, also refresh programs for current campus/type
          try { vm.loadProgramsForSelection(); } catch (e) {}
          return vm._openTerms;
        })
        .catch(function (err) {
          console.error('Load terms error', err);
          vm._openTerms = [];
          vm.termTypes = [];
          vm.termOptions = [];
          return [];
        })
        .finally(function () {
          vm.loadingTerms = false;
        });
    }

    function mapTermTypeToProgramType(tt) {
      if (!tt) return null;
      var s = ('' + tt).toLowerCase();
      // Common mappings: 'college', 'shs', etc.
      if (s.indexOf('college') !== -1) return 'college';
      if (s.indexOf('shs') !== -1) return 'shs';
      // fallback: if values are already 'college' / 'shs'
      if (s === 'college' || s === 'shs') return s;
      return null;
    }

    function loadProgramsForSelection() {
      // Do not load programs until a student level is selected
      if (!vm.form.student_type) {
        vm.programs = [];
        return $q.resolve([]);
      }

      // Determine filters
      var campusId = (vm.selectedCampus && vm.selectedCampus.id !== undefined && vm.selectedCampus.id !== null)
        ? parseInt(vm.selectedCampus.id, 10) : null;
      var progType = mapTermTypeToProgramType(vm.form.student_type);

      vm.programLoading = true;

      // Step 1: fetch programs filtered by type (when available), enabledOnly=true
      var params = { enabledOnly: true };
      if (progType) params.type = progType;

      return ProgramsService.list(params)
        .then(function (resp) {
          var list = (resp && resp.data) ? resp.data : (Array.isArray(resp) ? resp : []);
          // If no campus filter, use type-only list
          if (!campusId) {
            vm.programs = list;
            return list;
          }
          // Step 2: filter by campus using curricula that belong to campus
          return ProgramsService.getCurricula({ campus_id: campusId, limit: 500 })
            .then(function (body) {
              var rows = (body && body.data) ? body.data : (Array.isArray(body) ? body : []);
              // rows are resource-wrapped; normalize to raw when needed
              var progIds = {};
              for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                var pid = r.intProgramID || (r.data && r.data.intProgramID);
                if (pid) progIds['' + pid] = true;
              }
              // intersect programs with those having curricula for campus
              var filtered = list.filter(function (p) { return !!progIds['' + p.id]; });
              vm.programs = filtered;
              return filtered;
            })
            .catch(function () {
              // If curricula endpoint fails, fallback to type-only list
              vm.programs = list;
              return list;
            });
        })
        .catch(function (err) {
          console.error('Programs load (filtered) error', err);
          vm.programs = [];
          return [];
        })
        .finally(function () {
          vm.programLoading = false;
        });
    }
  }
})();
