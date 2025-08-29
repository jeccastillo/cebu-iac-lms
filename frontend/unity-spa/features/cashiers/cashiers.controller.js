(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CashiersController', CashiersController);

  CashiersController.$inject = ['$location', '$window', '$scope', 'StorageService', 'CashiersService', 'CampusService'];
  function CashiersController($location, $window, $scope, StorageService, CashiersService, CampusService) {
    var vm = this;

    vm.title = 'Cashier Administration';
    vm.state = StorageService.getJSON('loginState');

    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.rows = [];
    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.filters = {
      includeStats: true
    };

    vm.selectedCampus = null;

    // map of editing payloads by cashier id for ranges
    vm.editing = {}; // { [id]: { or_start, or_end, invoice_start, invoice_end, campus_id } }
    vm.assigning = {}; // { [id]: { query, results:[], selected:null, loading:false } }

    vm.load = function () {
      vm.loading = true;
      vm.error = null;
      var campusId = (vm.selectedCampus && vm.selectedCampus.id !== undefined && vm.selectedCampus.id !== null)
        ? parseInt(vm.selectedCampus.id, 10) : null;

      CashiersService.list({
        includeStats: !!vm.filters.includeStats,
        campus_id: campusId
      })
      .then(function (data) {
        // Expecting { success: true, data: [...] }
        var list = (data && data.success !== false && angular.isArray(data.data)) ? data.data
                   : (angular.isArray(data) ? data : []);
        vm.rows = list.map(function (r) {
          // Normalize shape for UI convenience
          r.or = r.or || { start: null, end: null, current: null };
          r.invoice = r.invoice || { start: null, end: null, current: null };
          r.stats = r.stats || null;
          r.temporary_admin = (typeof r.temporary_admin !== 'undefined' && r.temporary_admin !== null)
            ? parseInt(r.temporary_admin, 10) : 0;
          return r;
        });
      })
      .catch(function (err) {
        vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load cashiers.';
        vm.rows = [];
      })
      .finally(function () {
        vm.loading = false;
      });
    };

    vm.toggleTempAdmin = function (row) {
      // optimistic toggle; revert on error
      var newVal = row.temporary_admin ? 1 : 0;
      CashiersService.update(row.id, { temporary_admin: newVal })
        .then(function (res) {
          vm.success = 'Temporary admin updated.';
          _clearSuccessSoon();
        })
        .catch(function (err) {
          row.temporary_admin = row.temporary_admin ? 0 : 1; // revert
          vm.error = _firstError(err) || 'Failed to update temporary admin.';
        });
    };

    vm.saveCurrents = function (row) {
      var payload = {};
      if (row.or && row.or.current !== null && row.or.current !== undefined && row.or.current !== '') {
        payload.or_current = parseInt(row.or.current, 10);
      }
      if (row.invoice && row.invoice.current !== null && row.invoice.current !== undefined && row.invoice.current !== '') {
        payload.invoice_current = parseInt(row.invoice.current, 10);
      }
      if (Object.keys(payload).length === 0) {
        vm.error = 'Nothing to save.';
        return;
      }
      vm.loading = true;
      vm.error = null;
      CashiersService.update(row.id, payload)
        .then(function () {
          vm.success = 'Current pointers saved.';
          _clearSuccessSoon();
          vm.load();
        })
        .catch(function (err) {
          vm.error = _firstError(err) || 'Failed to save current pointers.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.beginEditRanges = function (row) {
      vm.editing[row.id] = {
        campus_id: (row.campus_id !== null && row.campus_id !== undefined) ? parseInt(row.campus_id, 10) : null,
        or_start: row.or && row.or.start !== null && row.or.start !== undefined ? parseInt(row.or.start, 10) : null,
        or_end: row.or && row.or.end !== null && row.or.end !== undefined ? parseInt(row.or.end, 10) : null,
        invoice_start: row.invoice && row.invoice.start !== null && row.invoice.start !== undefined ? parseInt(row.invoice.start, 10) : null,
        invoice_end: row.invoice && row.invoice.end !== null && row.invoice.end !== undefined ? parseInt(row.invoice.end, 10) : null
      };
    };

    vm.cancelEditRanges = function (row) {
      delete vm.editing[row.id];
    };

    vm.saveRanges = function (row) {
      var edit = vm.editing[row.id];
      if (!edit) return;

      // Build payload: send only keys that are explicitly numbers or null; backend expects both start+end for a type.
      var payload = {};
      if ('campus_id' in edit) payload.campus_id = (edit.campus_id !== null && edit.campus_id !== '') ? parseInt(edit.campus_id, 10) : null;

      var hasOrPair = (edit.or_start !== undefined || edit.or_end !== undefined);
      var hasInvPair = (edit.invoice_start !== undefined || edit.invoice_end !== undefined);

      if (hasOrPair) {
        payload.or_start = (edit.or_start !== null && edit.or_start !== '') ? parseInt(edit.or_start, 10) : null;
        payload.or_end = (edit.or_end !== null && edit.or_end !== '') ? parseInt(edit.or_end, 10) : null;
      }
      if (hasInvPair) {
        payload.invoice_start = (edit.invoice_start !== null && edit.invoice_start !== '') ? parseInt(edit.invoice_start, 10) : null;
        payload.invoice_end = (edit.invoice_end !== null && edit.invoice_end !== '') ? parseInt(edit.invoice_end, 10) : null;
      }

      vm.loading = true;
      vm.error = null;
      CashiersService.updateRanges(row.id, payload)
        .then(function () {
          vm.success = 'Ranges updated.';
          _clearSuccessSoon();
          delete vm.editing[row.id];
          vm.load();
        })
        .catch(function (err) {
          // Surface first meaningful error; includes overlap/usage errors from backend
          vm.error = _firstError(err) || 'Failed to update ranges.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.refreshStats = function (row) {
      CashiersService.stats(row.id)
        .then(function (data) {
          var stats = (data && data.success !== false && data.data) ? data.data : data;
          row.stats = stats || null;
        })
        .catch(function () {
          vm.error = 'Failed to fetch stats.';
        });
    };

    vm.refreshAllStats = function () {
      vm.filters.includeStats = true;
      vm.load();
    };

    // Assignment flow
    vm.openAssign = function (row) {
      vm.assigning[row.id] = {
        query: '',
        results: [],
        selected: null,
        loading: false
      };
    };

    vm.cancelAssign = function (row) {
      delete vm.assigning[row.id];
    };

    vm.searchAssign = function (row) {
      var state = vm.assigning[row.id];
      if (!state) return;
      state.loading = true;
      state.results = [];
      var campusId = (row.campus_id !== null && row.campus_id !== undefined) ? parseInt(row.campus_id, 10) : null;
      CashiersService.searchFaculty(state.query, campusId, 10)
        .then(function (data) {
          var list = (data && data.success !== false && angular.isArray(data.data)) ? data.data
                    : (angular.isArray(data) ? data : []);
          // Normalize shape (FacultyResource-like objects)
          state.results = list.map(function (f) {
            var id = f.intID || f.id || f.intId;
            var first = f.strFirstname || f.first_name || '';
            var last = f.strLastname || f.last_name || '';
            var email = f.strEmail || f.email || '';
            return {
              id: parseInt(id, 10),
              label: (first + ' ' + last).trim() + (email ? (' (' + email + ')') : ''),
              raw: f
            };
          });
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to search faculty.';
        })
        .finally(function () {
          state.loading = false;
        });
    };

    vm.selectAssign = function (row, item) {
      var state = vm.assigning[row.id];
      if (!state) return;
      state.selected = item;
    };

    vm.submitAssign = function (row) {
      var state = vm.assigning[row.id];
      if (!state || !state.selected || !state.selected.id) {
        vm.error = 'Select a faculty first.';
        return;
      }
      vm.loading = true;
      vm.error = null;
      CashiersService.assign(row.id, state.selected.id)
        .then(function () {
          vm.success = 'Cashier assigned.';
          _clearSuccessSoon();
          delete vm.assigning[row.id];
          vm.load();
        })
        .catch(function (err) {
          vm.error = _firstError(err) || 'Failed to assign cashier.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    function _firstError(err) {
      if (err && err.data) {
        if (err.data.errors) {
          var k = Object.keys(err.data.errors)[0];
          if (k && err.data.errors[k] && err.data.errors[k][0]) return err.data.errors[k][0];
        }
        if (err.data.message) return err.data.message;
      }
      return null;
    }

    function _clearSuccessSoon() {
      setTimeout(function () {
        try { vm.success = null; $scope.$applyAsync(); } catch (e) {}
      }, 1200);
    }

    // Campus binding (for filtering scope)
    function initCampusBinding() {
      function setFromSelectedCampus() {
        try {
          var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
          vm.selectedCampus = campus;
        } catch (e) {
          // ignore
        }
      }
      var initPromise = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (initPromise && initPromise.then) {
        initPromise.then(function () { setFromSelectedCampus(); vm.load(); });
      } else {
        setFromSelectedCampus();
        vm.load();
      }

      // React to campus changes
      var unbind = $scope.$on('campusChanged', function (event, data) {
        var campus = data && data.selectedCampus ? data.selectedCampus : null;
        vm.selectedCampus = campus;
        vm.load();
      });
      $scope.$on('$destroy', unbind);
    }

    initCampusBinding();
  }

})();
