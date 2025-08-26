(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SystemLogsController', SystemLogsController);

  SystemLogsController.$inject = ['$scope', 'SystemLogsService'];
  function SystemLogsController($scope, SystemLogsService) {
    var vm = this;

    // State
    vm.loading = false;
    vm.error = null;
    vm.items = [];
    vm.meta = { current_page: 1, per_page: 20, total: 0, last_page: 1 };
    vm.page = 1;

    vm.filters = {
      q: '',
      entity: '',
      action: '',
      user_id: '',
      entity_id: '',
      method: '',
      path: '',
      date_from: '',
      date_to: '',
      per_page: 10
    };

    vm.expanded = {}; // rowId -> boolean

    // Methods
    vm.search = search;
    vm.reset = reset;
    vm.go = go;
    vm.toggleExpand = toggleExpand;
    vm.truncate = truncate;
    vm.pretty = pretty;
    vm.export = exportXlsx;

    activate();

    function activate() {
      search();
    }

    function search() {
      vm.loading = true;
      vm.error = null;
      var params = _buildParams();
      params.page = vm.page;

      SystemLogsService.list(params)
        .then(function (resp) {
          // resp = { success, data, meta? }
          var data = resp && resp.data ? resp.data : [];
          var meta = resp && resp.meta ? resp.meta : { current_page: 1, per_page: vm.filters.per_page, total: data.length, last_page: 1 };

          vm.items = data;
          vm.meta = meta;
          vm.page = meta.current_page || params.page || 1;
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load logs';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function reset() {
      vm.filters = {
        q: '',
        entity: '',
        action: '',
        user_id: '',
        entity_id: '',
        method: '',
        path: '',
        date_from: '',
        date_to: '',
        per_page: 10
      };
      vm.page = 1;
      vm.expanded = {};
      search();
    }

    function go(page) {
      if (!page || page < 1) return;
      if (vm.meta && vm.meta.last_page && page > vm.meta.last_page) return;
      vm.page = page;
      search();
    }

    function toggleExpand(id) {
      vm.expanded[id] = !vm.expanded[id];
    }

    function truncate(s, max) {
      max = max || 60;
      if (s == null) return '';
      s = '' + s;
      if (s.length <= max) return s;
      return s.substr(0, max) + '…';
    }

    function pretty(obj) {
      try {
        return JSON.stringify(obj, null, 2);
      } catch (e) {
        return '' + obj;
      }
    }

    function exportXlsx() {
      vm.loading = true;
      vm.error = null;

      var params = _buildParams();
      // Remove pagination-only params for export
      if ('page' in params) delete params.page;
      if ('per_page' in params) delete params.per_page;

      SystemLogsService.export(params)
        .then(function (resp) {
          // Determine filename from Content-Disposition if present
          var disposition = resp.headers ? resp.headers('content-disposition') : null;
          var filename = null;
          if (disposition && typeof disposition === 'string') {
            var match = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i.exec(disposition);
            if (match && match[1]) {
              filename = match[1].replace(/['"]/g, '');
            }
          }
          if (!filename) {
            var dt = new Date();
            var pad = function (n) { return (n < 10 ? '0' : '') + n; };
            var stamp = dt.getFullYear().toString()
              + pad(dt.getMonth() + 1)
              + pad(dt.getDate())
              + '-' + pad(dt.getHours())
              + pad(dt.getMinutes())
              + pad(dt.getSeconds());
            filename = 'system-logs-' + stamp + '.xlsx';
          }

          var blob = new Blob([resp.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
          var url = window.URL.createObjectURL(blob);
          var a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          setTimeout(function () {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
          }, 0);
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Export failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function _buildParams() {
      var p = {};
      Object.keys(vm.filters).forEach(function (k) {
        var v = vm.filters[k];
        if (v === null || v === undefined) return;
        if (typeof v === 'string' && v.trim() === '') return;
        p[k] = v;
      });
      return p;
    }
  }

})();
