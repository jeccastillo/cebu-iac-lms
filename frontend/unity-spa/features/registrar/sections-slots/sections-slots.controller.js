(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SectionsSlotsController', SectionsSlotsController);

  SectionsSlotsController.$inject = ['$scope', '$q', 'StorageService', 'TermService', 'ToastService', 'SectionsSlotsService'];
  function SectionsSlotsController($scope, $q, StorageService, TermService, ToastService, SectionsSlotsService) {
    var vm = this;

    // State
    vm.state = StorageService.getJSON('loginState');
    vm.loading = {
      bootstrap: true,
      list: false
    };

    // Term selector
    vm.term = null;
    vm.termOptions = [];
    vm.setTerm = setTerm;

    // Data
    vm.rows = [];
    vm.meta = { page: 1, per_page: 20, total: 0 };

    // Filters (optional – extend as needed)
    vm.filters = {
      intSubjectID: '',
      intFacultyID: '',
      section: '',
      class_name: '',
      year: '',
      sub_section: ''
    };

    // Search UI state (client-side search)
    vm.search = {
      section: '',
      subject: ''
    };

    // View rows (after local filtering)
    vm.viewRows = [];

    // Expose actions for UI
    vm.applySearch = applySearch;
    vm.clearSearch = clearSearch;
    vm.applyLocalFilter = applyLocalFilter;

    // Actions
    vm.reload = reload;

    activate();

    function activate() {
      // Guard (global guard in run.js also enforces this)
      if (!vm.state || !vm.state.loggedIn) {
        vm.loading.bootstrap = false;
        return;
      }

      // Server-side search (no local instant filtering watchers)
      // Initialize visible rows
      applyLocalFilter();

      // Load terms then first page
      $q.when()
        .then(loadTerms)
        .then(function () { return reload(true); })
        .finally(function () { vm.loading.bootstrap = false; });
    }

    function loadTerms() {
      try {
        return TermService.init().then(function () {
          var raw = Array.isArray(TermService.availableTerms) ? TermService.availableTerms : [];
          vm.termOptions = raw.map(function (t) {
            var id = (t && t.intID !== undefined) ? t.intID : (t && t.id !== undefined ? t.id : null);
            var label = (t && (t.term_label || t.label)) || '';
            if (!label) {
              var ys = (t && t.strYearStart) ? ('' + t.strYearStart) : '';
              var ye = (t && t.strYearEnd) ? ('' + t.strYearEnd) : '';
              var sem = (t && t.enumSem) ? ('' + t.enumSem) : '';
              label = [ys, ye].filter(Boolean).join('-');
              if (sem) label = (label ? (label + ' ' + sem) : sem);
              if (!label) label = 'Term ' + (id !== null ? id : '');
            }
            return { id: id, label: label };
          }).filter(function (o) { return o.id !== null; });

          var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
          if (sel && (sel.intID || sel.id)) {
            vm.term = sel.intID || sel.id;
          } else if (vm.termOptions && vm.termOptions.length) {
            vm.term = vm.termOptions[0].id;
          } else {
            vm.term = null;
          }
        });
      } catch (e) {
        vm.termOptions = [];
        vm.term = null;
        return $q.when();
      }
    }

    function setTerm(id) {
      vm.term = id;
      // Reset to first page when term changes
      vm.meta.page = 1;
      return reload(true);
    }

    function buildParams() {
      var p = {
        term: vm.term,
        page: vm.meta.page,
        perPage: vm.meta.per_page
      };
      // Apply filters if present
      Object.keys(vm.filters).forEach(function (k) {
        var v = vm.filters[k];
        if (v !== null && v !== undefined && ('' + v).trim() !== '') {
          p[k] = v;
        }
      });
      return p;
    }

    // --------------- Local view helper ---------------
    function applyLocalFilter() {
      // Server-side filtering: just mirror the current page rows
      vm.viewRows = Array.isArray(vm.rows) ? vm.rows.slice(0) : [];
    }

    function applySearch() {
      vm.filters.section = (vm.search && vm.search.section) || '';
      vm.filters.subject = (vm.search && vm.search.subject) || '';
      vm.meta.page = 1;
      return reload(true);
    }

    function clearSearch() {
      vm.search = { section: '', subject: '' };
      vm.filters.section = '';
      vm.filters.subject = '';
      vm.meta.page = 1;
      return reload(true);
    }

    function reload(force) {
      if (!vm.term) return $q.when();
      if (vm.loading.list) return $q.when();

      vm.loading.list = true;
      var params = buildParams();

      return SectionsSlotsService.list(params)
        .then(function (body) {
          var data = (body && body.data) || [];
          var meta = (body && body.meta) || { page: vm.meta.page, per_page: vm.meta.per_page, total: 0 };
          vm.rows = Array.isArray(data) ? data : [];
          vm.meta = {
            page: parseInt(meta.page, 10) || 1,
            per_page: parseInt(meta.per_page, 10) || vm.meta.per_page,
            total: parseInt(meta.total, 10) || 0
          };
          // Refresh visible rows after data update
          applyLocalFilter();
        })
        .catch(function (err) {
          try {
            var msg = (err && err.data && (err.data.message || err.data.error)) || err.statusText || 'Failed to load sections/slots.';
            ToastService.error(msg);
          } catch (e) {
            // swallow
          }
        })
        .finally(function () {
          vm.loading.list = false;
        });
    }
  }
})();
