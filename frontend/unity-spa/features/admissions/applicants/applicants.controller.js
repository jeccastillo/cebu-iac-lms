(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ApplicantsListController', ApplicantsListController)
    .controller('ApplicantViewController', ApplicantViewController);

  ApplicantsListController.$inject = ['$location', 'ApplicantsService'];
  function ApplicantsListController($location, ApplicantsService) {
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
      per_page: 10
    };

    // Methods
    vm.load = load;
    vm.search = search;
    vm.clearFilters = clearFilters;
    vm.changeSort = changeSort;
    vm.changePage = changePage;
    vm.changePerPage = changePerPage;
    vm.view = view;

    // Helpers
    vm.fullName = function (r) {
      var ln = (r && r.strLastname) ? ('' + r.strLastname).toUpperCase() : '';
      var fn = (r && r.strFirstname) ? r.strFirstname : '';
      return (ln && fn) ? (ln + ', ' + fn) : (ln || fn || '(no name)');
    };

    activate();

    function activate() {
      load();
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
      load();
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

    function view(row) {
      if (!row || (row.id === undefined || row.id === null)) return;
      $location.path('/admissions/applicants/' + row.id);
    }
  }

  ApplicantViewController.$inject = ['$routeParams', '$location', 'ApplicantsService'];
  function ApplicantViewController($routeParams, $location, ApplicantsService) {
    var vm = this;

    vm.loading = false;
    vm.error = null;
    vm.id = ($routeParams && $routeParams.id) ? parseInt($routeParams.id, 10) : null;

    vm.user = null;
    vm.status = null;
    vm.created_at = null;
    vm.updated_at = null;
    vm.applicant_data = null;

    // Organized sections built from raw applicant_data
    vm.sections = [];

    vm.backToList = backToList;
    vm.reload = load;

    activate();

    function activate() {
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
