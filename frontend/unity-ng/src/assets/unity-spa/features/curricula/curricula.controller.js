(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CurriculaListController', CurriculaListController)
    .controller('CurriculumEditController', CurriculumEditController);

  CurriculaListController.$inject = ['$location', '$scope', 'CurriculaService', 'CampusService'];
  function CurriculaListController($location, $scope, CurriculaService, CampusService) {
    var vm = this;
    // Expose CampusService for template bindings
    vm.CampusService = CampusService;

    // State
    vm.loading = false;
    vm.error = null;
    vm.rows = [];
    vm.searchTerm = '';
    vm.page = 1;
    vm.limit = 25;

    // Methods
    vm.load = load;
    vm.onSearch = onSearch;
    vm.addNew = addNew;
    vm.edit = edit;
    vm.delete = remove;

    activate();

    function activate() {
      // Ensure campus context is ready, then initial load
      var p = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (p && p.then) {
        p.then(function () {
          load();
        });
      } else {
        load();
      }

      // React to campus changes: reset to first page and reload
      $scope.$on('campusChanged', function () {
        vm.page = 1;
        load();
      });
    }

    function buildParams() {
      var params = {
        limit: vm.limit,
        page: vm.page
      };
      if (vm.searchTerm && ('' + vm.searchTerm).trim() !== '') {
        params.search = vm.searchTerm;
      }
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
          params.campus_id = parseInt(campus.id, 10);
        }
      } catch (e) {
        // ignore
      }
      return params;
    }

    function load() {
      vm.loading = true;
      vm.error = null;
      CurriculaService.list(buildParams())
        .then(function (data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
            vm.meta = data.meta || null;
          } else if (angular.isArray(data)) {
            vm.rows = data;
            vm.meta = null;
          } else {
            vm.rows = [];
            vm.meta = null;
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load curricula.';
          vm.rows = [];
          console.error('Curricula list error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function onSearch() {
      vm.page = 1;
      load();
    }

    function addNew() {
      $location.path('/curricula/add');
    }

    function edit(row) {
      if (!row || !row.intID) return;
      $location.path('/curricula/' + row.intID + '/edit');
    }

    function remove(row) {
      if (!row || !row.intID) return;
      var ok = window.confirm('Delete curriculum "' + (row.strName || row.intID) + '"? This cannot be undone.');
      if (!ok) return;

      vm.loading = true;
      CurriculaService.remove(row.intID)
        .then(function (res) {
          if (res && res.success !== false) {
            load();
          } else {
            vm.error = (res && res.message) ? res.message : 'Delete failed.';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Delete failed (see console).';
          console.error('Delete curriculum error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }
  }

  CurriculumEditController.$inject = ['$routeParams', '$location', '$scope', '$q', 'CurriculaService', 'CampusService'];
  function CurriculumEditController($routeParams, $location, $scope, $q, CurriculaService, CampusService) {
    var vm = this;
    // Expose CampusService if template needs to reference it
    vm.CampusService = CampusService;

    vm.isEdit = !!($routeParams && $routeParams.id);
    vm.loading = false;
    vm.saving = false;
    vm.error = null;
    vm.success = null;

    // Form model
    vm.model = {
      strName: '',
      intProgramID: null,
      campus_id: null,
      active: 1,
      isEnhanced: 0
    };

    // Dropdown data
    vm.programs = [];
    vm.campuses = [];

    // Methods
    vm.load = load;
    vm.loadDropdowns = loadDropdowns;
    vm.save = save;
    vm.cancel = cancel;
    vm.onCampusChange = onCampusChange;

    activate();

    function activate() {
      // Ensure campus service is ready to default campus on Add
      var initPromise = (CampusService && CampusService.init) ? CampusService.init() : $q.resolve();
      initPromise
        .then(function () {
          return loadDropdowns();
        })
        .then(function () {
          if (vm.isEdit) {
            return load();
          } else {
            // Default campus from global selection when adding
            try {
              var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
              if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
                vm.model.campus_id = parseInt(campus.id, 10);
              }
            } catch (e) { /* no-op */ }
          }
        });

      // Keep campus in sync while adding
      $scope.$on('campusChanged', function (event, data) {
        if (vm.isEdit) return;
        var campus = data && data.selectedCampus ? data.selectedCampus : null;
        var id = (campus && campus.id !== undefined && campus.id !== null) ? parseInt(campus.id, 10) : null;
        vm.model.campus_id = id;
      });
    }

    function loadDropdowns() {
      vm.loading = true;
      vm.error = null;

      var proms = [
        CurriculaService.getPrograms(),
        (function () {
          // Prefer CampusService cache for consistency
          var list = CampusService && CampusService.availableCampuses ? CampusService.availableCampuses : [];
          if (list && list.length) {
            return $q.resolve({ data: list, success: true });
          }
          return CurriculaService.getCampuses();
        })()
      ];

      return $q.all(proms)
        .then(function (results) {
          // Programs
          var p = results[0];
          var pdata = (p && p.data) ? p.data : p;
          if (pdata && pdata.success !== false && angular.isArray(pdata.data)) {
            vm.programs = pdata.data;
          } else if (angular.isArray(pdata)) {
            vm.programs = pdata;
          } else {
            vm.programs = [];
          }

          // Campuses
          var c = results[1];
          var cdata = (c && c.data) ? c.data : c;
          if (cdata && cdata.success !== false && angular.isArray(cdata.data)) {
            vm.campuses = cdata.data;
          } else if (angular.isArray(cdata)) {
            vm.campuses = cdata;
          } else {
            vm.campuses = [];
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load dropdown data.';
          console.error('Dropdown load error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function load() {
      if (!vm.isEdit) return $q.resolve();
      vm.loading = true;
      vm.error = null;

      return CurriculaService.get($routeParams.id)
        .then(function (data) {
          if (data && data.success !== false && data.data) {
            var row = data.data;
            vm.model.strName = row.strName || '';
            vm.model.intProgramID = (row.intProgramID !== undefined && row.intProgramID !== null) ? parseInt(row.intProgramID, 10) : null;
            vm.model.campus_id = (row.campus_id !== undefined && row.campus_id !== null) ? parseInt(row.campus_id, 10) : null;
            vm.model.active = (row.active !== undefined && row.active !== null) ? parseInt(row.active, 10) : 1;
            vm.model.isEnhanced = (row.isEnhanced !== undefined && row.isEnhanced !== null) ? parseInt(row.isEnhanced, 10) : 0;
          } else {
            vm.error = 'Failed to load curriculum.';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load curriculum.';
          console.error('Load curriculum error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function onCampusChange() {
      // Hook kept for parity with other features if needed later
    }

    function save() {
      vm.saving = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        strName: (vm.model.strName || '').trim(),
        intProgramID: vm.model.intProgramID !== null ? parseInt(vm.model.intProgramID, 10) : null,
        campus_id: vm.model.campus_id !== null ? parseInt(vm.model.campus_id, 10) : null,
        active: vm.model.active ? 1 : 0,
        isEnhanced: vm.model.isEnhanced ? 1 : 0
      };

      var p;
      if (vm.isEdit) {
        p = CurriculaService.update($routeParams.id, payload);
      } else {
        p = CurriculaService.create(payload);
      }

      p.then(function (res) {
        if (res && res.success !== false) {
          $location.path('/curricula');
        } else {
          vm.error = (res && res.message) ? res.message : 'Save failed.';
        }
      })
      .catch(function (err) {
        vm.error = (err && err.data && err.data.message) ? err.data.message : 'Save failed (see console).';
        console.error('Save curriculum error:', err);
      })
      .finally(function () {
        vm.saving = false;
      });
    }

    function cancel() {
      $location.path('/curricula');
    }
  }

})();
