(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ProgramsListController', ProgramsListController)
    .controller('ProgramEditController', ProgramEditController);

  ProgramsListController.$inject = ['$location', '$window', 'StorageService', 'ProgramsService'];
  function ProgramsListController($location, $window, StorageService, ProgramsService) {
    var vm = this;

    vm.title = 'Programs';
    vm.state = StorageService.getJSON('loginState');

    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.filters = {
      search: '',
      type: '',
      enabledOnly: true
    };

    vm.rows = [];
    vm.loading = false;
    vm.error = null;

    vm.types = [
      { value: '', label: 'All types' },
      { value: 'college', label: 'College' },
      { value: 'shs', label: 'SHS' },
      { value: 'drive', label: 'DRIVE' },
      { value: 'other', label: 'Other' }
    ];

    vm.search = function () {
      vm.loading = true;
      vm.error = null;
      var opts = {
        enabledOnly: !!vm.filters.enabledOnly
      };
      if (vm.filters.type) opts.type = vm.filters.type;
      if (vm.filters.search && ('' + vm.filters.search).trim() !== '') {
        opts.search = vm.filters.search.trim();
      }
      ProgramsService.list(opts)
        .then(function (data) {
          // Expecting { success: true, data: [...] } from API
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
          } else if (angular.isArray(data)) {
            vm.rows = data;
          } else if (data && angular.isArray(data.rows)) {
            vm.rows = data.rows;
          } else {
            vm.rows = [];
          }
        })
        .catch(function () {
          vm.error = 'Failed to load programs.';
          vm.rows = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.add = function () {
      $location.path('/programs/add');
    };

    vm.edit = function (row) {
      var id = row && (row.id || row.intProgramID) ? (row.id || row.intProgramID) : row;
      if (!id) return;
      $location.path('/programs/' + id + '/edit');
    };

    vm.disable = function (row) {
      var id = row && (row.id || row.intProgramID) ? (row.id || row.intProgramID) : row;
      if (!id) return;
      if ($window.confirm('Disable this program? This will set enumEnabled=0.')) {
        vm.loading = true;
        vm.error = null;
        ProgramsService.disable(id)
          .then(function (res) {
            vm.search();
          })
          .catch(function (err) {
            var msg = 'Disable failed.';
            if (err && err.data && err.data.message) msg = err.data.message;
            vm.error = msg;
          })
          .finally(function () {
            vm.loading = false;
          });
      }
    };

    // Init
    vm.search();
  }

  ProgramEditController.$inject = ['$routeParams', '$location', 'StorageService', 'ProgramsService'];
  function ProgramEditController($routeParams, $location, StorageService, ProgramsService) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.isEdit = !!vm.id;
    vm.title = vm.isEdit ? 'Edit Program' : 'Add Program';

    vm.types = [
      { value: 'college', label: 'College' },
      { value: 'shs', label: 'SHS' },
      { value: 'drive', label: 'DRIVE' },
      { value: 'other', label: 'Other' }
    ];

    vm.model = {
      strProgramCode: '',
      strProgramDescription: '',
      strMajor: '',
      type: 'college',
      school: '',
      short_name: '',
      default_curriculum: null,
      enumEnabled: 1,
      campus_id: null
    };

    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.load = function () {
      if (!vm.isEdit) return;
      vm.loading = true;
      vm.error = null;
      ProgramsService.get(vm.id)
        .then(function (data) {
          // Expecting { success: true, data: { ... } }
          var row = (data && data.success !== false && data.data) ? data.data : data;
          if (!row || (!row.intProgramID && !row.id)) {
            vm.error = 'Program not found.';
            return;
          }
          vm.model.strProgramCode = row.strProgramCode || '';
          vm.model.strProgramDescription = row.strProgramDescription || '';
          vm.model.strMajor = row.strMajor || '';
          vm.model.type = row.type || 'college';
          vm.model.school = row.school || '';
          vm.model.short_name = row.short_name || '';
          vm.model.default_curriculum = (typeof row.default_curriculum !== 'undefined' && row.default_curriculum !== null)
            ? parseInt(row.default_curriculum, 10) : null;
          vm.model.enumEnabled = (typeof row.enumEnabled !== 'undefined' && row.enumEnabled !== null)
            ? parseInt(row.enumEnabled, 10) : 1;
          vm.model.campus_id = (typeof row.campus_id !== 'undefined' && row.campus_id !== null)
            ? parseInt(row.campus_id, 10) : null;
        })
        .catch(function () {
          vm.error = 'Failed to load program.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.save = function () {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        strProgramCode: (vm.model.strProgramCode || '').trim(),
        strProgramDescription: (vm.model.strProgramDescription || '').trim(),
        strMajor: (vm.model.strMajor || '') || null,
        type: vm.model.type || 'college',
        school: (vm.model.school || '') || null,
        short_name: (vm.model.short_name || '') || null,
        default_curriculum: (vm.model.default_curriculum !== null && vm.model.default_curriculum !== '') ? parseInt(vm.model.default_curriculum, 10) : null,
        enumEnabled: vm.model.enumEnabled ? 1 : 0,
        campus_id: (vm.model.campus_id !== null && vm.model.campus_id !== '') ? parseInt(vm.model.campus_id, 10) : null
      };

      var p = vm.isEdit
        ? ProgramsService.update(vm.id, payload)
        : ProgramsService.create(payload);

      p.then(function (data) {
          if (data && data.success !== false) {
            vm.success = 'Saved.';
            setTimeout(function () {
              try { vm.success = null; } catch (e) {}
              window.location.hash = '#/programs';
            }, 300);
          } else {
            vm.error = 'Save failed.';
          }
        })
        .catch(function (err) {
          if (err && err.data && err.data.errors) {
            var firstKey = Object.keys(err.data.errors)[0];
            vm.error = (err.data.errors[firstKey] && err.data.errors[firstKey][0]) || 'Validation failed.';
          } else if (err && err.data && err.data.message) {
            vm.error = err.data.message;
          } else {
            vm.error = 'Save failed.';
          }
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.cancel = function () {
      $location.path('/programs');
    };

    vm.load();
  }

})();
