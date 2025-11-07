(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ApplicantsListController', ApplicantsListController)
    .controller('ApplicantViewController', ApplicantViewController);

  ApplicantsListController.$inject = ['$location', '$scope', '$window', 'ApplicantsService', 'CampusService', 'SchoolYearsService'];
  function ApplicantsListController($location, $scope, $window, ApplicantsService, CampusService, SchoolYearsService) {
    var vm = this;

    vm.title = 'Applicants';
    vm.error = null;
    vm.total = 0;

    // Row actions dropdown state (UX parity with other list pages)
    vm.menuOpenId = null;
    vm.toggleMenu = function (id) { vm.menuOpenId = (vm.menuOpenId === id ? null : id); };
    vm.isMenuOpen = function (id) { return vm.menuOpenId === id; };
    vm.closeMenu = function () { vm.menuOpenId = null; };

    // State
    vm.loading = false;
    vm.rows = [];
    vm.meta = { current_page: 1, per_page: 10, total: 0, last_page: 1 };
    vm.filters = {
      search: '',
      status: '',
      campus: '',
      date_from: '',
      date_to: '',
      sort: 'application_created_at',
      order: 'desc',
      page: 1,
      per_page: 10,
      syid: ''
    };

    // Methods
    vm.load = load;
    vm.search = search;
    vm.clearFilters = clearFilters;
    vm.changeSort = changeSort;
    vm.changePage = changePage;
    vm.changePerPage = changePerPage;
    vm.view = view;
    vm.onCampusChange = onCampusChange;

    // Term helpers
    vm.termLabel = termLabel;
    vm.onTermChange = onTermChange;
    vm.loadTerms = loadTerms;

    // Data for campus dropdown and term dropdown
    vm.campuses = [];
    vm.terms = [];

    // Helpers
    vm.fullName = function (r) {
      var ln = (r && r.strLastname) ? ('' + r.strLastname).toUpperCase() : '';
      var fn = (r && r.strFirstname) ? r.strFirstname : '';
      return (ln && fn) ? (ln + ', ' + fn) : (ln || fn || '(no name)');
    };

    vm.downloadTemplate = function () {
      vm.error = null;
      try {
        ApplicantsService.downloadTemplate().then(function (res) {
          var data = res && res.data ? res.data : null;
          var filename = 'applicants-import-template.xlsx';
          if (!data) {
            vm.error = 'Failed to download template.';
            return;
          }
          // Convert arraybuffer to string for CSV
          var csvString = new TextDecoder('utf-8').decode(data);
          // Create blob and trigger download
          var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
          if (navigator.msSaveBlob) {
            // IE 10+
            navigator.msSaveBlob(blob, filename);
          } else {
            var link = document.createElement('a');
            if (link.download !== undefined) {
              var url = URL.createObjectURL(blob);
              link.setAttribute('href', url);
              link.setAttribute('download', filename);
              link.style.visibility = 'hidden';
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
              URL.revokeObjectURL(url);
            }
          }
        }).catch(function () {
          vm.error = 'Failed to download template.';
        });
      } catch (e) {
        vm.error = 'Failed to download template.';
      }
    };

    activate();

    function activate() {
      // Initialize campus context then initial load
      try {
        var p = (CampusService && CampusService.init) ? CampusService.init() : null;

        function afterInit() {
          try {
            vm.campuses = (CampusService && CampusService.availableCampuses) ? CampusService.availableCampuses : [];
            // Default campus from global selection if not set
            if (!vm.filters.campus || ('' + vm.filters.campus).trim() === '') {
              var sc = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
              var id = (sc && sc.id !== undefined && sc.id !== null) ? parseInt(sc.id, 10) : null;
              if (id !== null && !isNaN(id)) vm.filters.campus = id;
            }
        } catch (e) {}
          var _cid = (vm.filters && vm.filters.campus !== undefined && vm.filters.campus !== null) ? vm.filters.campus : '';
          var p2 = (vm.loadTerms && vm.loadTerms.call) ? vm.loadTerms(_cid) : null;
          if (p2 && p2.finally) {
            p2.finally(function(){ load(); });
          } else if (p2 && p2.then) {
            p2.then(function(){ load(); }).catch(function(){ load(); });
          } else {
            load();
          }
        }

        if (p && p.then) { p.then(afterInit); } else { afterInit(); }
      } catch (e) {
        var _cid2 = (vm.filters && vm.filters.campus !== undefined && vm.filters.campus !== null) ? vm.filters.campus : '';
        var p3 = (vm.loadTerms && vm.loadTerms.call) ? vm.loadTerms(_cid2) : null;
        if (p3 && p3.finally) {
          p3.finally(function(){ load(); });
        } else if (p3 && p3.then) {
          p3.then(function(){ load(); }).catch(function(){ load(); });
        } else {
          load();
        }
      }

      // React to global campus changes
      $scope.$on('campusChanged', function (event, data) {
        try {
          // update campuses list if provided
          if (data && data.availableCampuses) {
            vm.campuses = data.availableCampuses;
          }
          var sc = data && data.selectedCampus ? data.selectedCampus : null;
          var id = (sc && sc.id !== undefined && sc.id !== null) ? parseInt(sc.id, 10) : null;
          vm.filters.campus = (id !== null && !isNaN(id)) ? id : '';
          vm.filters.page = 1;
          var p4 = vm.loadTerms(vm.filters.campus);
          if (p4 && p4.then) {
            p4.then(function(){ load(); }).catch(function(){ load(); });
          } else {
            load();
          }
        } catch (e2) {}
      });
    }

    function normalizeResponse(res) {
      // API returns { success, data, meta? }
      var data = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
      var meta = (res && res.meta) ? res.meta : null;
      return { data: data, meta: meta };
    }

    function load() {
      vm.loading = true;
      vm.error = null;
      ApplicantsService.list(vm.filters)
        .then(function (res) {
          var r = normalizeResponse(res);
          vm.rows = Array.isArray(r.data) ? r.data : [];
          if (r.meta) {
            vm.meta.current_page = r.meta.current_page || 1;
            vm.meta.per_page = r.meta.per_page || vm.filters.per_page;
            vm.meta.total = r.meta.total || vm.rows.length;
            vm.meta.last_page = r.meta.last_page || 1;
          } else {
            vm.meta.current_page = 1;
            vm.meta.last_page = 1;
            vm.meta.total = vm.rows.length;
          }
          vm.total = vm.meta.total || (vm.rows ? vm.rows.length : 0);
        })
        .catch(function (err) {
          vm.rows = [];
          vm.total = 0;
          vm.meta.current_page = 1;
          vm.meta.last_page = 1;
          vm.meta.total = 0;
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load applicants.';
        })
        .finally(function () { vm.loading = false; });
    }

    function search() {
      vm.filters.page = 1;
      load();
    }

    function clearFilters() {
      vm.filters.search = '';
      vm.filters.status = '';
      vm.filters.campus = '';
      vm.filters.date_from = '';
      vm.filters.date_to = '';
      vm.filters.sort = 'application_created_at';
      vm.filters.order = 'desc';
      vm.filters.page = 1;
      vm.filters.syid = '';

      // Reset campus to global selection if available
      try {
        var sc = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        var id = (sc && sc.id !== undefined && sc.id !== null) ? parseInt(sc.id, 10) : null;
        vm.filters.campus = (id !== null && !isNaN(id)) ? id : '';
      } catch (e) {}

      var p = vm.loadTerms(vm.filters.campus);
      if (p && p.then) {
        p.then(function(){ load(); }).catch(function(){ load(); });
      } else {
        load();
      }
    }

    function changeSort(field) {
      if (vm.filters.sort === field) {
        vm.filters.order = (vm.filters.order === 'asc') ? 'desc' : 'asc';
      } else {
        vm.filters.sort = field;
        vm.filters.order = 'asc';
      }
      load();
    }

    function changePage(delta) {
      var p = (vm.filters.page || 1) + delta;
      if (p < 1) p = 1;
      if (vm.meta && vm.meta.last_page && p > vm.meta.last_page) p = vm.meta.last_page;
      vm.filters.page = p;
      load();
    }

    function changePerPage() {
      vm.filters.page = 1;
      load();
    }

    function onCampusChange() {
      vm.filters.page = 1;
      var p = vm.loadTerms(vm.filters.campus);
      if (p && p.then) {
        p.then(function(){ load(); }).catch(function(){ load(); });
      } else {
        load();
      }
    }

    function view(row) {
      if (!row || (row.id === undefined || row.id === null)) return;
      $location.path('/admissions/applicants/' + row.id);
    }

    // --------------------------
    // Term helpers / loader
    // --------------------------
    function termLabel(t) {
      if (!t) return '-';
      var st = (t.term_student_type !== undefined && t.term_student_type !== null) ? String(t.term_student_type).trim() : '';
      var sem = (t.enumSem !== undefined && t.enumSem !== null) ? String(t.enumSem).trim() : '';
      var lbl = (t.term_label !== undefined && t.term_label !== null) ? String(t.term_label).trim() : '';
      var ys = (t.strYearStart !== undefined && t.strYearStart !== null) ? String(t.strYearStart).trim() : '';
      var ye = (t.strYearEnd !== undefined && t.strYearEnd !== null) ? String(t.strYearEnd).trim() : '';
      var y = (ys && ye) ? (ys + '-' + ye) : (ys || ye || '');
      var parts = [st, sem, lbl, y].filter(function(x){ return x && x.length; });
      return parts.length ? parts.join(' ') : '-';
    }

    function loadTerms(campusId) {
      vm.terms = [];
      try {
        var args = {};
        if (campusId !== null && campusId !== undefined && campusId !== '') {
          args.campus_id = campusId;
        }
        // Optionally, could filter by term_student_type if needed later
        return SchoolYearsService.list(args).then(function(res){
          var d = (res && res.data) ? res.data : res;
          vm.terms = Array.isArray(d) ? d : [];
          // Clear selected term if not present in new list
          var exists = vm.terms.some(function(t){
            var tid = (t && (t.intID !== undefined ? t.intID : t.id));
            return String(tid) === String(vm.filters.syid || '');
          });
          if (!exists) {
            vm.filters.syid = '';
          }
          return vm.terms;
        }).catch(function(){
          vm.terms = [];
          vm.filters.syid = '';
          return vm.terms;
        });
      } catch (e) {
        vm.terms = [];
        vm.filters.syid = '';
        try { return Promise.resolve(vm.terms); } catch (_e) { return null; }
      }
    }

    function onTermChange() {
      vm.filters.page = 1;
      load();
    }
  }

  ApplicantViewController.$inject = ['$routeParams', '$location', 'ApplicantsService', 'ProgramsService', 'RoleService', 'InitialRequirementsService', 'InterviewsService', 'ApplicantJourneyService'];
  function ApplicantViewController($routeParams, $location, ApplicantsService, ProgramsService, RoleService, InitialRequirementsService, InterviewsService, ApplicantJourneyService) {
    var vm = this;

    vm.loading = false;
    vm.error = null;
    vm.id = ($routeParams && $routeParams.id) ? parseInt($routeParams.id, 10) : null;

    vm.user = null;
    vm.status = null;
    vm.created_at = null;
    vm.updated_at = null;
    vm.applicant_data = null;
    vm.program_name = null;

    // Initial Requirements state
    vm.hash = null;
    vm.irLoading = false;
    vm.irError = null;
    vm.initial_requirements = [];
    // Resolved 2x2 photo URL from uploaded initial requirements
    vm.photo2x2Url = null;

    // Organized sections built from raw applicant_data
    vm.sections = [];

    vm.editing = false;
    vm.saving = false;
    vm.form = {};

    // Waiver UI state
    vm.canEditWaiver = false;
    vm.waiver = { waive_application_fee: false, waive_reason: '' };
    vm.savingWaiver = false;

    // Interview UI state
    vm.canManageInterview = false;
    vm.applicant_data_id = null;
    vm.interview = null;
    vm.interviewLoading = false;
    vm.interviewError = null;
    vm.scheduling = false;
    vm.resultSaving = false;
    vm.schedule = { scheduled_at: '', remarks: '' };
    vm.result = { assessment: '', remarks: '', reason_for_failing: '' };

    // Applicant Journey state
    vm.journey = [];
    vm.journeyLoading = false;
    vm.journeyError = null;

    vm.backToList = backToList;
    vm.reload = load;
    vm.startEdit = startEdit;
    vm.cancelEdit = cancelEdit;
    vm.save = save;
    vm.saveWaiver = saveWaiver;
    vm.loadInitialRequirements = loadInitialRequirements;

    // Admin control for Initial Requirements upload/replace
    vm.canManageInitialRequirements = false;
    vm.onIRFilePicked = onIRFilePicked;

    // Interview methods
    vm.loadInterview = loadInterview;
    vm.scheduleInterview = scheduleInterview;
    vm.submitResult = submitResult;

    // Applicant Journey
    vm.loadJourney = loadJourney;

    activate();

    function activate() {
      try {
        vm.canEditWaiver = (RoleService && RoleService.hasAny) ? RoleService.hasAny(['admissions','admin']) : false;
        vm.canManageInterview = (RoleService && RoleService.hasAny) ? RoleService.hasAny(['admissions','admin']) : false;
        vm.canManageInitialRequirements = (RoleService && RoleService.hasAny) ? RoleService.hasAny(['admissions','admin']) : false;
      } catch (e) {
        vm.canEditWaiver = false;
        vm.canManageInitialRequirements = false;
      }
      load();
    }

    function load() {
      if (!vm.id) {
        vm.error = 'Missing applicant id.';
        return;
      }
      vm.loading = true;
      vm.error = null;
      ApplicantsService.show(vm.id)
        .then(function (res) {
          var d = (res && res.data) ? res.data : res;
          if (!d) {
            vm.error = 'Applicant not found.';
            return;
          }
          vm.user = d.user || null;
          vm.status = d.status || null;
          vm.created_at = d.created_at || null;
          vm.updated_at = d.updated_at || null;
          vm.applicant_data = d.applicant_data || null;

          try { resolveProgramName(); } catch (e) {}

          // New: surfaced fields from API (applicant type + payment statuses)
          vm.applicant_type = (d.applicant_type !== undefined && d.applicant_type !== null) ? d.applicant_type : null;
          vm.applicant_type_name = d.applicant_type_name || null;
          vm.paid_application_fee = (typeof d.paid_application_fee !== 'undefined') ? d.paid_application_fee : null;
          vm.paid_reservation_fee = (typeof d.paid_reservation_fee !== 'undefined') ? d.paid_reservation_fee : null;
          // Interviewed flag
          vm.interviewed = (typeof d.interviewed !== 'undefined') ? !!d.interviewed : false;

          // Waiver fields
          vm.waive_application_fee = !!(d && d.waive_application_fee);
          vm.waive_reason = d && d.waive_reason ? d.waive_reason : '';
          vm.waived_at = d && d.waived_at ? d.waived_at : null;

          // Seed waiver form
          vm.waiver = {
            waive_application_fee: vm.waive_application_fee,
            waive_reason: vm.waive_reason || ''
          };

          // Seed form for editing when not actively editing
          try {
            if (!vm.editing) {
              seedForm();
            }
          } catch (e) {}

          // Interview applicant_data_id and current interview load
          vm.applicant_data_id = (typeof d.applicant_data_id !== 'undefined' && d.applicant_data_id !== null) ? parseInt(d.applicant_data_id, 10) : null;
          try {
            if (vm.applicant_data_id) {
              vm.loadInterview();
              vm.loadJourney();
            } else {
              vm.interview = null;
              vm.journey = [];
            }
          } catch (e) {}

          // Initial Requirements hash and fetch
          vm.hash = d.hash || null;
          try {
            if (vm.hash) {
              loadInitialRequirements();
            }
          } catch (e) {}

          // Build organized sections for display
          try {
            vm.sections = buildSections(vm.applicant_data, vm.user);
          } catch (e) {
            vm.sections = [];
          }
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load applicant details.';
        })
        .finally(function () { vm.loading = false; });
    }

    function backToList() {
      $location.path('/admissions/applicants');
    }

    // Editing helpers

    function toYMD(val) {
      if (!val) return '';
      try {
        var d = new Date(val);
        if (isNaN(d.getTime())) {
          // try splitting if already Y-m-d
          var m = String(val).match(/^(\d{4})-(\d{2})-(\d{2})/);
          if (m) return m[1] + '-' + m[2] + '-' + m[3];
          return '';
        }
        var yyyy = d.getFullYear();
        var mm = (d.getMonth() + 1).toString().padStart(2, '0');
        var dd = d.getDate().toString().padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
      } catch (e) {
        return '';
      }
    }

    function seedForm() {
      var ad = vm.applicant_data || {};
      var u = vm.user || {};
      vm.form = {
        first_name:    ad.first_name    || u.strFirstname   || '',
        middle_name:   ad.middle_name   || u.strMiddlename  || '',
        last_name:     ad.last_name     || u.strLastname    || '',
        email:         ad.email         || u.strEmail       || '',
        mobile_number: ad.mobile_number || u.strMobileNumber || u.strPhoneNumber || '',
        date_of_birth: toYMD(ad.date_of_birth || ad.dob || u.dteBirthDate || '')
      };
    }

    function startEdit() {
      try { seedForm(); } catch (e) {}
      vm.editing = true;
      vm.error = null;
    }

    function cancelEdit() {
      vm.editing = false;
      vm.saving = false;
      vm.error = null;
    }

    function save() {
      if (!vm.id) return;
      vm.saving = true;
      vm.error = null;
      // Only send known fields
      var payload = {
        first_name: vm.form.first_name,
        middle_name: vm.form.middle_name,
        last_name: vm.form.last_name,
        email: vm.form.email,
        mobile_number: vm.form.mobile_number,
        date_of_birth: vm.form.date_of_birth
      };
      ApplicantsService.update(vm.id, payload)
        .then(function () {
          vm.editing = false;
          return vm.reload();
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to save applicant updates.';
        })
        .finally(function () { vm.saving = false; });
    }

    function saveWaiver() {
      if (!vm.id || !vm.canEditWaiver) return;
      vm.savingWaiver = true;
      vm.error = null;
      var payload = {
        waive_application_fee: !!(vm.waiver && vm.waiver.waive_application_fee),
        waive_reason: (vm.waiver && vm.waiver.waive_reason ? ('' + vm.waiver.waive_reason).trim() : '')
      };
      ApplicantsService.update(vm.id, payload)
        .then(function () {
          return vm.reload();
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to save waiver update.';
        })
        .finally(function () { vm.savingWaiver = false; });
    }

    // Resolve program display name from applicant_data (type_id/program/type)
    function resolveProgramName() {
      vm.program_name = null;
      try {
        var ad = vm.applicant_data || {};
        // Use explicit program text if present
        if (ad.program && String(ad.program).trim() !== '') {
          vm.program_name = ad.program;
          return;
        }
        // Fallback to type text if present
        if (ad.type && String(ad.type).trim() !== '') {
          vm.program_name = ad.type;
          return;
        }
        // Otherwise, try to fetch by type_id (or program_id as fallback)
        var rawId = (ad.type_id !== undefined && ad.type_id !== null) ? ad.type_id : (ad.program_id !== undefined ? ad.program_id : null);
        var typeId = parseInt(rawId, 10);
        if (!isNaN(typeId) && typeId > 0 && ProgramsService && ProgramsService.get) {
          ProgramsService.get(typeId)
            .then(function (res) {
              var p = (res && res.data) ? res.data : res;
              if (p) {
                vm.program_name = p.strProgramDescription || p.short_name || p.strProgramCode || ('Program #' + typeId);
              }
            })
            .catch(function () {
              // leave as null; UI will fallback to '-'
            });
        }
      } catch (e) {
        // ignore safely
      }
    }

    function loadInitialRequirements() {
      if (!vm.hash) {
        vm.initial_requirements = [];
        vm.photo2x2Url = null;
        return;
      }
      vm.irLoading = true;
      vm.irError = null;
      try {
        InitialRequirementsService.getList(vm.hash)
          .then(function (res) {
            var container = (res && res.data) ? res.data : res;
            var data = (container && container.data) ? container.data : container;
            vm.initial_requirements = (data && data.requirements) ? data.requirements : [];

            // Derive 2x2 photo URL from submitted initial requirements
            try {
              var url = null;
              if (Array.isArray(vm.initial_requirements)) {
                for (var i = 0; i < vm.initial_requirements.length; i++) {
                  var req = vm.initial_requirements[i] || {};
                  var submitted = !!req.submitted_status;
                  var n = ((req.name || req.description || '') + '').toLowerCase();
                  if (submitted && n) {
                    if (/2\s*x\s*2/.test(n) || n.indexOf('2x2') !== -1 || n.indexOf('2 x 2') !== -1) {
                      url = req.file_link || null;
                      if (url) break;
                    }
                  }
                }
              }
              vm.photo2x2Url = url;
            } catch (e) {
              vm.photo2x2Url = null;
            }
          })
          .catch(function (err) {
            vm.irError = (err && (err.message || (err.data && err.data.message))) || 'Failed to load initial requirements.';
            vm.initial_requirements = [];
            vm.photo2x2Url = null;
          })
          .finally(function () {
            vm.irLoading = false;
          });
      } catch (e) {
        vm.irLoading = false;
        vm.irError = 'Failed to load initial requirements.';
      }
    }

    // --------------------------
    // Initial Requirements — Admin Upload/Replace
    // --------------------------
    function onIRFilePicked(req, file) {
      if (!vm.canManageInitialRequirements) return;
      if (!req || !file || !vm.user || !vm.user.intID) return;

      // Basic client-side validation to mirror backend constraints
      try {
        var maxBytes = 10 * 1024 * 1024; // 10MB
        if (file.size > maxBytes) {
          try { Swal.fire('Invalid File', 'Maximum file size is 10MB.', 'warning'); } catch (e) { alert('Maximum file size is 10MB.'); }
          return;
        }
        var allowed = [
          'application/pdf',
          'application/vnd.ms-excel',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'text/csv'
        ];
        var name = (file.name || '').toLowerCase();
        var ok = (file.type && file.type.indexOf('image/') === 0)
              || allowed.indexOf(file.type) !== -1
              || /\.(pdf|xls|xlsx|csv|jpg|jpeg|png|gif|webp)$/i.test(name);
        if (!ok) {
          try { Swal.fire('Invalid File', 'Only PDF, Excel (xls, xlsx, csv), and image files are allowed.', 'warning'); } catch (e) { alert('Only PDF, Excel (xls, xlsx, csv), and image files are allowed.'); }
          return;
        }
      } catch (e) { /* ignore */ }

      // UI flag
      req._uploading = true;

      InitialRequirementsService
        .adminUpload(vm.user.intID, req.app_req_id, file)
        .then(function () {
          try { Swal.fire('Uploaded', 'File uploaded successfully.', 'success'); } catch (e) {}
          // Reload list to reflect updated status and file link
          try { vm.loadInitialRequirements(); } catch (e) {}
        })
        .catch(function (err) {
          var msg = (err && (err.message || (err.data && (err.data.message || (err.data.errors && err.data.errors.file && err.data.errors.file[0]))))) || 'Upload failed';
          try { Swal.fire('Error', msg, 'error'); } catch (e) { alert(msg); }
        })
        .finally(function () {
          req._uploading = false;
        });
    }

    // --------------------------
    // Interview helpers (load/schedule/result)
    // --------------------------
    function loadInterview() {
      if (!vm.applicant_data_id) {
        vm.interview = null;
        return;
      }
      vm.interviewLoading = true;
      vm.interviewError = null;
      try {
        InterviewsService.getByApplicantData(vm.applicant_data_id)
          .then(function (res) {
            var data = (res && res.data) ? res.data : res;
            vm.interview = data || null;
          })
          .catch(function (err) {
            // 404 means not scheduled yet
            var status = err && (err.status || (err.data && err.data.status));
            if (String(status) === '404') {
              vm.interview = null;
            } else {
              vm.interviewError = (err && (err.message || (err.data && err.data.message))) || 'Failed to load interview.';
            }
          })
          .finally(function () { vm.interviewLoading = false; });
      } catch (e) {
        vm.interviewLoading = false;
        vm.interviewError = 'Failed to load interview.';
      }
    }

    function scheduleInterview() {
      if (!vm.canManageInterview || !vm.applicant_data_id) return;
      // Avoid coercing Date objects to the string "[object Date]".
      // Pass through Date as-is; strings get trimmed. Service will normalize.
      var rawWhen = vm.schedule && vm.schedule.scheduled_at;
      var when = (rawWhen instanceof Date) ? rawWhen
               : (typeof rawWhen === 'string' ? rawWhen.trim() : '');
      if (!when) {
        vm.interviewError = 'Please select a schedule date and time.';
        return;
      }
      vm.scheduling = true;
      vm.interviewError = null;
      var payload = {
        scheduled_at: when,
        remarks: (vm.schedule && vm.schedule.remarks ? ('' + vm.schedule.remarks).trim() : null)
      };
      InterviewsService.schedule(vm.applicant_data_id, payload)
        .then(function () {
          // Clear form and reload interview and applicant payload (interviewed flag may change after result later)
          vm.schedule = { scheduled_at: '', remarks: '' };
          return vm.loadInterview();
        })
        .catch(function (err) {
          vm.interviewError = (err && (err.message || (err.data && err.data.message))) || 'Failed to schedule interview.';
        })
        .finally(function () { vm.scheduling = false; });
    }

    function submitResult(assessment) {
      if (!vm.canManageInterview || !vm.interview || !vm.interview.id) return;
      var a = (assessment || (vm.result && vm.result.assessment) || '').trim();
      if (!a) {
        vm.interviewError = 'Please select an assessment.';
        return;
      }
      if (a === 'Failed') {
        var reason = vm.result && vm.result.reason_for_failing ? ('' + vm.result.reason_for_failing).trim() : '';
        if (!reason) {
          vm.interviewError = 'Reason for failing is required.';
          return;
        }
      }

      vm.resultSaving = true;
      vm.interviewError = null;
      var payload = {
        assessment: a,
        remarks: (vm.result && vm.result.remarks ? ('' + vm.result.remarks).trim() : null)
      };
      if (a === 'Failed') {
        payload.reason_for_failing = ('' + vm.result.reason_for_failing).trim();
      }

      InterviewsService.submitResult(vm.interview.id, payload)
        .then(function () {
          // Clear result form, reload interview, and refresh applicant payload to update flags
          vm.result = { assessment: '', remarks: '', reason_for_failing: '' };
          vm.loadInterview();
          try { vm.reload(); } catch (e) {}
        })
        .catch(function (err) {
          vm.interviewError = (err && (err.message || (err.data && err.data.message))) || 'Failed to submit interview result.';
        })
        .finally(function () { vm.resultSaving = false; });
    }

    // --------------------------
    // Applicant Journey helper
    // --------------------------
    function loadJourney() {
      if (!vm.applicant_data_id) {
        vm.journey = [];
        return;
      }
      vm.journeyLoading = true;
      vm.journeyError = null;
      try {
        ApplicantJourneyService.listByApplicantData(vm.applicant_data_id)
          .then(function (res) {
            var data = (res && res.data) ? res.data : res;
            var rows = Array.isArray(data) ? data : (data && data.data ? data.data : []);
            vm.journey = rows || [];
          })
          .catch(function (err) {
            vm.journey = [];
            vm.journeyError = (err && (err.message || (err.data && err.data.message))) || 'Failed to load applicant journey.';
          })
          .finally(function () { vm.journeyLoading = false; });
      } catch (e) {
        vm.journeyLoading = false;
        vm.journeyError = 'Failed to load applicant journey.';
      }
    }

    // --------------------------
    // Helpers to organize fields
    // --------------------------

    function buildSections(data, user) {
      var sections = [];
      data = data || {};

      // Known groupings by common payload fields
      var groups = [
        { key: 'identity',     title: 'Identity',           fields: ['first_name','middle_name','last_name','suffix','gender','date_of_birth','dob'] },
        { key: 'contact',      title: 'Contact',            fields: ['email','mobile_number','mobile','phone','telephone'] },
        { key: 'address',      title: 'Address',            fields: ['address','city','state','province','country','zip','zipcode','postal_code'] },
        { key: 'program',      title: 'Program / Type',     fields: ['type_id','type','program','student_type','intTuitionYear','track','strand','campus'] },
        { key: 'hs',           title: 'High School',        fields: ['high_school','high_school_address','high_school_attended'] },
        { key: 'shs',          title: 'Senior High School', fields: ['senior_high','senior_high_address','senior_high_attended'] },
        { key: 'meta',         title: 'Submission Meta',    fields: ['_server'] },
      ];

      // Seed with user fallbacks where appropriate
      var seeded = Object.assign({}, data);
      if (user) {
        if (!seeded.email && user.strEmail) seeded.email = user.strEmail;
        if (!seeded.mobile_number && (user.strMobileNumber || user.strPhoneNumber)) {
          seeded.mobile_number = user.strMobileNumber || user.strPhoneNumber;
        }
        if (!seeded.gender && user.enumGender) seeded.gender = user.enumGender;
        if (!seeded.date_of_birth && user.dteBirthDate) seeded.date_of_birth = user.dteBirthDate;
        if (!seeded.campus && user.campus) seeded.campus = user.campus;
        if (!seeded.student_type && user.student_type) seeded.student_type = user.student_type;
        if (!seeded.address && user.strAddress) seeded.address = user.strAddress;
      }

      // Build items for each group
      groups.forEach(function(g){
        var items = [];
        g.fields.forEach(function(f){
          if (f === '_server' && typeof seeded._server === 'object' && seeded._server) {
            // Expand server meta
            Object.keys(seeded._server).forEach(function(sk){
              items.push({
                label: toTitle(sk.replace(/^_/,'')),
                value: formatValue(seeded._server[sk])
              });
            });
          } else if (seeded.hasOwnProperty(f) && seeded[f] !== undefined && seeded[f] !== null && seeded[f] !== '') {
            items.push({
              label: toTitle(f),
              value: formatValue(seeded[f], f)
            });
          }
        });
        if (items.length) {
          sections.push({ title: g.title, items: items });
        }
      });

      // Remaining fields -> Other Fields
      var used = new Set(groups.reduce(function(acc, g){
        g.fields.forEach(function(f){ acc.push(f); });
        return acc;
      }, []));
      used.add('_server'); // already expanded if present

      var other = [];
      Object.keys(seeded).forEach(function(k){
        if (!used.has(k)) {
          other.push({
            label: toTitle(k),
            value: formatValue(seeded[k], k)
          });
        }
      });
      if (other.length) {
        sections.push({ title: 'Other Fields', items: other });
      }

      return sections;
    }

    function toTitle(key) {
      if (!key) return '';
      // replace underscores / hyphens, then title case
      var s = ('' + key).replace(/[_\-]+/g, ' ').trim();
      // simple camelCase splitter
      s = s.replace(/([a-z0-9])([A-Z])/g, '$1 $2');
      return s.replace(/\w\S*/g, function(t){ return t.charAt(0).toUpperCase() + t.substr(1).toLowerCase(); });
    }

    function isDateKey(k) {
      return /date|dob|birth/i.test(k || '');
    }

    function formatValue(val, key) {
      if (val === null || val === undefined || val === '') return '-';
      // If the key is a program identifier, prefer the resolved program name
      if (key === 'type_id' || key === 'program_id') {
        if (vm.program_name && String(vm.program_name).trim() !== '') {
          return vm.program_name;
        }
        // fall through to default formatting if name not available
      }
      if (Array.isArray(val)) {
        return val.length ? val.join(', ') : '-';
      }
      if (typeof val === 'object') {
        try { return JSON.stringify(val, null, 2); } catch (e) { return String(val); }
      }
      // date-ish keys
      if (key && isDateKey(key)) {
        // leave raw; Angular date filter in template can format when it's recognizable
        return val;
      }
      return String(val);
    }
  }

})();
