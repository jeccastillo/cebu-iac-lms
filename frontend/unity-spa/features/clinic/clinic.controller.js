(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClinicController', ClinicController)
    .controller('ClinicRecordViewController', ClinicRecordViewController);

  ClinicController.$inject = ['$location', 'ClinicService'];
  function ClinicController($location, ClinicService) {
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
      campus_id: '',
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

    activate();

    function activate() {
      search(1);
    }

    function search(page) {
      vm.loading = true;
      vm.error = null;

      var params = angular.copy(vm.filters) || {};
      params.page = page || vm.meta.page || 1;
      params.per_page = vm.meta.per_page || 20;

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
        campus_id: '',
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

    function viewRecord(rec) {
      var id = (typeof rec === 'number') ? rec : (rec && rec.id);
      if (!id) return;
      $location.path('/clinic/records/' + id);
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

})();
