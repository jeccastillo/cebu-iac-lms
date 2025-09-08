(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdmissionsApplyController', AdmissionsApplyController);

  AdmissionsApplyController.$inject = ['$http', '$location', 'APP_CONFIG', '$q', 'CampusService', 'SchoolYearsService', 'ProgramsService', 'TuitionYearsService', 'PreviousSchoolsService', 'ApplicantTypesService', '$scope', 'ToastService', '$document', '$timeout', '$window'];

  function AdmissionsApplyController($http, $location, APP_CONFIG, $q, CampusService, SchoolYearsService, ProgramsService, TuitionYearsService, PreviousSchoolsService, ApplicantTypesService, $scope, ToastService, $document, $timeout, $window) {
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

    // Applicant Types selection state
    vm.loadingApplicantTypes = false;
    vm.applicantTypes = [];
    vm.applicantTypesByGroup = {}; // { groupName: [ {id,name} ] }
    vm.applicantTypeGroupOrder = ['Freshman', '2nd Degree', 'Other'];
    vm.totalApplicantTypeCount = 0;

    // Educational Background state
    vm.previousSchools = [];
    vm.educ = { notOnList: false, selected: null };

    vm.form = {
      // Basic Info
      first_name: '',
      middle_name: '',
      last_name: '',
      gender: '',
      date_of_birth: '',
      citizenship_country: 'Philippines',
      // Contact
      email: '',
      email_confirmation: '',
      mobile_number: '',
      mobile_number_confirmation: '',
      // Address
      address: '',
      city: '',
      province: '',
      country: 'Philippines',
      // Educational Background
      previous_school_id: null,
      previous_school_name: '',
      previous_school_city: '',
      previous_school_province: '',
      previous_school_country: 'Philippines',
      grade_year_level: '',
      program_strand_degree: '',
      lrn_number: '',
      // Parents / Guardians
      mother_name: '',
      mother_occupation: '',
      mother_email: '',
      mother_mobile: '',
      father_name: '',
      father_occupation: '',
      father_email: '',
      father_mobile: '',
      guardian_name: '',
      guardian_relationship: '',
      guardian_email: '',
      guardian_mobile: '',
      primary_contact: '',
      // Additional Information
      good_moral_standing: null,
      illegal_activities_involved: null,
      hospitalized_before: null,
      health_conditions: { diabetes: false, allergies: false, high_blood: false, anemia: false, others: false, others_specify: '' },
      other_health_concerns: '',
      // Program / Type
      student_type: '',  // selected term_student_type
      type_id: '',       // program (first choice)
      campus: '',        // kept for backend compatibility (filled on submit)
      term: null,        // tb_mas_sy.intID (selected term)
      campus_id: null,   // kept for backend completeness (filled on submit)
      intTuitionYear: null, // resolved after selecting student level
      applicant_type_id: null, // selected applicant type id
      // Awareness section (multi-select)
      awareness: {
        google: false,
        facebook: false,
        instagram: false,
        tiktok: false,
        news: false,
        school_fair_orientation: false,
        billboard: false,
        event: false,
        event_name: '',
        referral: false,
        name_of_referee: '',
        others: false,
        others_specify: ''
      },
      // Privacy Policy consent
      privacy_policy_agreed: false
    };

    vm.genders = ['Male', 'Female'];
    // Countries for dropdown (sorted alphabetically by name)
    vm.countries = [
      'Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia','Austria',
      'Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan',
      'Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi','Cabo Verde','Cambodia',
      'Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia','Comoros','Congo','Costa Rica',
      "Cote d'Ivoire",'Croatia','Cuba','Cyprus','Czechia (Czech Republic)','Democratic Republic of the Congo','Denmark','Djibouti','Dominica','Dominican Republic',
      'Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea','Estonia','Eswatini','Ethiopia','Fiji','Finland',
      'France','Gabon','Gambia','Georgia','Germany','Ghana','Greece','Grenada','Guatemala','Guinea',
      'Guinea-Bissau','Guyana','Haiti','Holy See','Honduras','Hungary','Iceland','India','Indonesia','Iran',
      'Iraq','Ireland','Israel','Italy','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati',
      'Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania',
      'Luxembourg','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Mauritania','Mauritius',
      'Mexico','Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco','Mozambique','Myanmar (formerly Burma)','Namibia',
      'Nauru','Nepal','Netherlands','New Zealand','Nicaragua','Niger','Nigeria','North Korea','North Macedonia','Norway',
      'Oman','Pakistan','Palau','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal',
      'Qatar','Romania','Russia','Rwanda','Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines','Samoa','San Marino','Sao Tome and Principe',
      'Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia',
      'South Africa','South Korea','South Sudan','Spain','Sri Lanka','Sudan','Suriname','Sweden','Switzerland','Syria',
      'Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tonga','Trinidad and Tobago','Tunisia','Turkey',
      'Turkmenistan','Tuvalu','Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Uzbekistan','Vanuatu',
      'Venezuela','Vietnam','Yemen','Zambia','Zimbabwe'
    ];
    if (Array.isArray(vm.countries)) {
      vm.countries = vm.countries.slice().sort(function(a, b) { return ('' + a).localeCompare(b); });
    }
    vm.submit = submit;
    vm.onCampusChange = onCampusChange;
    vm.onTermTypeChange = onTermTypeChange;
    vm.loadProgramsForSelection = loadProgramsForSelection;
    vm.loadPreviousSchools = loadPreviousSchools;
    vm.onPreviousSchoolChange = onPreviousSchoolChange;
    vm.toggleNotOnList = toggleNotOnList;
    vm.loadApplicantTypesForSelection = loadApplicantTypesForSelection;
    vm.hasAnyAwarenessSelected = hasAnyAwarenessSelected;

    activate();

    // Toast + scroll utilities
    function getElementByName(name) {
      try { return ($document[0] || document).querySelector("[name='" + name + "']"); } catch (e) { return null; }
    }
    function scrollToElement(el) {
      if (!el) return;
      try { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {
        try { el.scrollIntoView(); } catch (e2) {}
      }
      try { if (el.focus) { el.focus(); } } catch (e3) {}
    }
    function scrollToName(name) {
      $timeout(function () { scrollToElement(getElementByName(name)); }, 0);
    }
    function scrollToFirstInvalid() {
      try {
        var form = $scope && $scope.applyForm ? $scope.applyForm : null;
        var ctrl = null;
        if (form && form.$error) {
          var order = ['required', 'email', 'tel', 'pattern', 'min', 'max'];
          for (var i = 0; i < order.length; i++) {
            var group = form.$error[order[i]];
            if (group && group.length) { ctrl = group[0]; break; }
          }
        }
        if (ctrl && ctrl.$name) {
          scrollToName(ctrl.$name);
          return;
        }
      } catch (e) {}
      // Fallback to privacy policy checkbox if nothing found
      scrollToName('privacy_policy_agreed');
    }

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

      // Load previous schools for Educational Background section
      var loadPrevSchools = $q.resolve().then(function () {
        return vm.loadPreviousSchools();
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

      $q.all([loadPrograms, initCampuses, loadPrevSchools])
        .finally(function () {
          vm.loading = false;
        });
    }

    function submit() {
      vm.error = null;

      // Mark form as submitted to surface validation state
      try { if ($scope && $scope.applyForm && $scope.applyForm.$setSubmitted) { $scope.applyForm.$setSubmitted(); } } catch (e) {}

      // Angular form-level validation first
      if ($scope && $scope.applyForm && $scope.applyForm.$invalid) {
        ToastService.error('Please complete the required fields.');
        try { scrollToFirstInvalid(); } catch (e) {}
        return;
      }

      // Basic validations with targeted scroll
      if (!vm.form.email || vm.form.email !== vm.form.email_confirmation) {
        ToastService.error('Email address does not match confirmation.');
        scrollToName('email_confirmation');
        return;
      }
      if (!vm.form.mobile_number || vm.form.mobile_number !== vm.form.mobile_number_confirmation) {
        ToastService.error('Mobile number does not match confirmation.');
        scrollToName('mobile_number_confirmation');
        return;
      }
      if (!vm.form.type_id) {
        ToastService.error('Please select a program.');
        scrollToName('type_id');
        return;
      }
      if (!vm.selectedCampus || vm.selectedCampus.id === undefined || vm.selectedCampus.id === null) {
        ToastService.error('Please select a campus.');
        scrollToName('campus');
        return;
      }
      if (!vm.form.student_type) {
        ToastService.error('Please select a student type.');
        scrollToName('student_type');
        return;
      }
      if (!vm.form.term) {
        ToastService.error('Please select a term.');
        scrollToName('term');
        return;
      }
      if (!vm.form.applicant_type_id) {
        ToastService.error('Please select an applicant type.');
        scrollToName('applicant_type_id');
        return;
      }
      // Validate Parent/Guardian contacts: require at least one group with name + (email or mobile)
      if (!validateParentContacts()) {
        ToastService.error('Please provide at least one parent/guardian with Name and either Email Address or Mobile Number.');
        // Best-effort focus to first parent group field
        scrollToName('mother_name');
        return;
      }

      // Privacy policy consent
      if (!vm.form.privacy_policy_agreed) {
        // Do not use blocking alerts; use toast and auto-scroll
        ToastService.error('Please agree to the privacy policy.');
        scrollToName('privacy_policy_agreed');
        return;
      }

      // Require at least one awareness option
      if (!vm.hasAnyAwarenessSelected()) {
        ToastService.error('Please select at least one awareness option.');
        scrollToName('awareness_google');
        return;
      }

      // Prepare payload
      var payload = angular.copy(vm.form);
      payload.campus = (vm.selectedCampus && vm.selectedCampus.campus_name) || payload.campus || '';
      payload.campus_id = vm.selectedCampus ? vm.selectedCampus.id : null;
      payload.term = vm.form.term || null; // tb_mas_sy.intID

      // Attach previously resolved tuition year id (loaded on student level selection)
      var tyid = (vm.form.intTuitionYear !== undefined && vm.form.intTuitionYear !== null && vm.form.intTuitionYear !== '')
        ? parseInt(vm.form.intTuitionYear, 10)
        : null;
      payload.intTuitionYear = isNaN(tyid) ? null : tyid;
      payload.applicant_type = (vm.form.applicant_type_id !== undefined && vm.form.applicant_type_id !== null)
        ? parseInt(vm.form.applicant_type_id, 10)
        : null;

      // Build awareness payload (array of items)
      try {
        payload.awareness = buildAwareness(vm.form.awareness);
      } catch (e) {
        payload.awareness = [];
      }

      vm.loading = true;

      $http.post(APP_CONFIG.API_BASE + '/admissions/student-info', payload)
        .then(function (resp) {
          if (resp && resp.data && resp.data.success) {
            var hash = null;
            try {
              hash = (resp.data && resp.data.data && resp.data.data.hash) || null;
            } catch (e) {
              hash = null;
            }
            if (hash) {
              $location.path('/admissions/success').search({ hash: hash });
            } else {
              $location.path('/admissions/success');
            }
          } else {
            vm.error = (resp && resp.data && resp.data.message) || 'Submission failed.';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) || 'Submission failed.';
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
        vm.form.intTuitionYear = null;
        return;
      }
      // Reset selections and reload
      vm.form.student_type = '';
      vm.form.term = null;
      vm.form.type_id = '';
      vm.form.intTuitionYear = null;
      vm.form.applicant_type_id = null;
      loadCampusTerms(vm.selectedCampus.id);
      try { vm.loadProgramsForSelection(); } catch (e) {}
      try { vm.loadApplicantTypesForSelection(); } catch (e) {}
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
      vm.form.applicant_type_id = null;
      try { vm.loadProgramsForSelection(); } catch (e) {}
      try { vm.loadApplicantTypesForSelection(); } catch (e) {}
      // Load default tuition year id for this student level
      try { loadDefaultTuitionYearForSelectedStudentType(); } catch (e) { vm.form.intTuitionYear = null; }
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
      // Common mappings: 'college', 'shs', 'grad'
      if (s.indexOf('college') !== -1) return 'college';
      if (s.indexOf('shs') !== -1) return 'shs';
      if (s.indexOf('grad') !== -1 || s.indexOf('graduate') !== -1) return 'grad';
      // fallback: if values are already one of the API types
      if (s === 'college' || s === 'shs' || s === 'grad') return s;
      return null;
    }

    // Resolve and store default tuition year id after selecting student level
    function loadDefaultTuitionYearForSelectedStudentType() {
      var stype = (vm.form.student_type || '').toLowerCase();
      var isShs = stype.indexOf('shs') !== -1;
      var isCollegeOrGrad = (stype.indexOf('college') !== -1) || (stype.indexOf('grad') !== -1) || (stype.indexOf('graduate') !== -1);

      var listPromise;
      if (isCollegeOrGrad) {
        listPromise = TuitionYearsService.list({ default: true });
      } else if (isShs) {
        listPromise = TuitionYearsService.list({ defaultShs: true });
      } else {
        listPromise = $q.resolve([]);
      }

      return $q.when(listPromise)
        .then(function (body) {
          var rows = (body && body.data) ? body.data : (Array.isArray(body) ? body : []);
          var def = rows && rows.length ? rows[0] : null;
          var id = def ? (def.intID || def.id || def.tuitionYearID || def.tuitionyear_id || def.tuition_year_id) : null;
          vm.form.intTuitionYear = (id !== null && id !== undefined && id !== '') ? (function () {
            var n = parseInt(id, 10);
            return isNaN(n) ? null : n;
          })() : null;
          return vm.form.intTuitionYear;
        })
        .catch(function () {
          vm.form.intTuitionYear = null;
          return null;
        });
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

    function loadApplicantTypesForSelection() {
      // Clear if no student level chosen
      if (!vm.form || !vm.form.student_type) {
        vm.applicantTypes = [];
        vm.applicantTypesByGroup = {};
        vm.applicantTypeGroupOrder = ['Freshman', '2nd Degree', 'Other'];
        vm.totalApplicantTypeCount = 0;
        return $q.resolve([]);
      }
      var type = mapTermTypeToProgramType(vm.form.student_type);
      if (!type) {
        vm.applicantTypes = [];
        vm.applicantTypesByGroup = {};
        vm.applicantTypeGroupOrder = ['Freshman', '2nd Degree', 'Other'];
        vm.totalApplicantTypeCount = 0;
        return $q.resolve([]);
      }

      vm.loadingApplicantTypes = true;
      return ApplicantTypesService.publicList({ type: type, per_page: 500, sort: 'name', order: 'asc' })
        .then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          // Normalize
          var list = rows.map(function (r) {
            var d = r && r.data ? r.data : r;
            return {
              id: d.id || d.intID,
              name: d.name || '',
              type: d.type || '',
              sub_type: d.sub_type || 'Other'
            };
          });
          vm.applicantTypes = list;

          // Group by sub_type
          var groups = {};
          var counts = 0;
          for (var i = 0; i < list.length; i++) {
            var g = list[i].sub_type || 'Other';
            if (!groups[g]) groups[g] = [];
            groups[g].push(list[i]);
            counts++;
          }
          // Sort each group's items by name
          Object.keys(groups).forEach(function (k) {
            groups[k].sort(function (a, b) { return ('' + a.name).localeCompare(b.name); });
          });

          // Determine group order: prefer known order, then append any others alphabetically
          var known = ['Freshman', '2nd Degree', 'Other'];
          var present = Object.keys(groups).sort();
          var order = [];
          known.forEach(function (x) { if (groups[x] && groups[x].length) order.push(x); });
          present.forEach(function (x) { if (order.indexOf(x) === -1) order.push(x); });

          vm.applicantTypesByGroup = groups;
          vm.applicantTypeGroupOrder = order;
          vm.totalApplicantTypeCount = counts;

          return list;
        })
        .catch(function () {
          vm.applicantTypes = [];
          vm.applicantTypesByGroup = {};
          vm.applicantTypeGroupOrder = ['Freshman', '2nd Degree', 'Other'];
          vm.totalApplicantTypeCount = 0;
          return [];
        })
        .finally(function () { vm.loadingApplicantTypes = false; });
    }

    // Parent/Guardian helpers
    function hasValue(v) { return v !== undefined && v !== null && ('' + v).trim() !== ''; }
    function isParentGroupValid(name, email, mobile) { return hasValue(name) && (hasValue(email) || hasValue(mobile)); }
    function validateParentContacts() {
      try {
        var m = isParentGroupValid(vm.form.mother_name, vm.form.mother_email, vm.form.mother_mobile);
        var f = isParentGroupValid(vm.form.father_name, vm.form.father_email, vm.form.father_mobile);
        var g = isParentGroupValid(vm.form.guardian_name, vm.form.guardian_email, vm.form.guardian_mobile);
        return !!(m || f || g);
      } catch (e) { return false; }
    }
    vm.validateParentContacts = validateParentContacts;

    // Educational Background helpers
    function loadPreviousSchools() {
      return $q.when(PreviousSchoolsService && PreviousSchoolsService.publicList ? PreviousSchoolsService.publicList({ per_page: 500 }) : [])
        .then(function (body) {
          var rows = (body && body.data) ? body.data : (Array.isArray(body) ? body : []);
          vm.previousSchools = rows.map(function (r) {
            var d = r && r.data ? r.data : r;
            return {
              id: d.intID || d.id,
              intID: d.intID || d.id,
              name: d.name || '',
              city: d.city || '',
              province: d.province || '',
              country: d.country || ''
            };
          });
          return vm.previousSchools;
        })
        .catch(function () {
          vm.previousSchools = [];
          return [];
        });
    }

    function onPreviousSchoolChange() {
      var sel = vm.educ ? vm.educ.selected : null;
      if (sel && (sel.id !== undefined && sel.id !== null)) {
        vm.form.previous_school_id = sel.intID || sel.id;
        vm.form.previous_school_name = sel.name || '';
        vm.form.previous_school_city = sel.city || '';
        vm.form.previous_school_province = sel.province || '';
        vm.form.previous_school_country = sel.country || '';
      } else {
        vm.form.previous_school_id = null;
        if (!vm.educ || !vm.educ.notOnList) {
          vm.form.previous_school_name = '';
        }
        vm.form.previous_school_city = '';
        vm.form.previous_school_province = '';
        vm.form.previous_school_country = '';
      }
    }

    function toggleNotOnList() {
      if (vm.educ && vm.educ.notOnList) {
        vm.educ.selected = null;
        vm.form.previous_school_id = null;
        // Allow manual entry of fields
      } else {
        // Return to list mode
        vm.form.previous_school_name = '';
      }
    }

    // Awareness helpers
    function hasAnyAwarenessSelected() {
      try {
        return buildAwareness(vm.form.awareness).length > 0;
      } catch (e) { return false; }
    }
    function buildAwareness(a) {
      var items = [];
      if (!a) return items;

      if (a.google) items.push({ name: 'Google' });
      if (a.facebook) items.push({ name: 'Facebook' });
      if (a.instagram) items.push({ name: 'Instagram' });
      if (a.tiktok) items.push({ name: 'Tiktok' });
      if (a.news) items.push({ name: 'News' });
      if (a.school_fair_orientation) items.push({ name: 'School Fair/Orientation' });
      if (a.billboard) items.push({ name: 'Billboard' });

      if (a.event) {
        var ev = { name: 'Event' };
        if (a.event_name && ('' + a.event_name).trim() !== '') {
          ev.sub_name = ('' + a.event_name).trim();
        }
        items.push(ev);
      }

      if (a.referral) {
        var ref = { name: 'Referral', referral: true };
        if (a.name_of_referee && ('' + a.name_of_referee).trim() !== '') {
          ref.name_of_referee = ('' + a.name_of_referee).trim();
        }
        items.push(ref);
      }

      if (a.others) {
        var oth = { name: 'Others' };
        if (a.others_specify && ('' + a.others_specify).trim() !== '') {
          oth.sub_name = ('' + a.others_specify).trim();
        }
        items.push(oth);
      }

      return items;
    }
  }
})();
