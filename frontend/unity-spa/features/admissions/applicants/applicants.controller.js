(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ApplicantsListController', ApplicantsListController)
    .controller('ApplicantViewController', ApplicantViewController);

  ApplicantsListController.$inject = ['$location', '$scope', 'ApplicantsService', 'CampusService', 'SchoolYearsService'];
  function ApplicantsListController($location, $scope, ApplicantsService, CampusService, SchoolYearsService) {
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

  ApplicantViewController.$inject = ['$routeParams', '$location', 'ApplicantsService', 'ProgramsService', 'RoleService'];
  function ApplicantViewController($routeParams, $location, ApplicantsService, ProgramsService, RoleService) {
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

    // Organized sections built from raw applicant_data
    vm.sections = [];

    vm.editing = false;
    vm.saving = false;
    vm.form = {};

    // Waiver UI state
    vm.canEditWaiver = false;
    vm.waiver = { waive_application_fee: false, waive_reason: '' };
    vm.savingWaiver = false;

    vm.backToList = backToList;
    vm.reload = load;
    vm.startEdit = startEdit;
    vm.cancelEdit = cancelEdit;
    vm.save = save;
    vm.saveWaiver = saveWaiver;

    activate();

    function activate() {
      try {
        vm.canEditWaiver = (RoleService && RoleService.hasAny) ? RoleService.hasAny(['admissions','admin']) : false;
      } catch (e) {
        vm.canEditWaiver = false;
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
