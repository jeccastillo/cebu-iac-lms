(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('GradingListController', GradingListController)
    .controller('GradingEditController', GradingEditController);

  GradingListController.$inject = ['$scope', '$location', 'GradingService', 'ToastService'];
  function GradingListController($scope, $location, GradingService, ToastService) {
    var vm = this;
    vm.loading = false;
    vm.items = [];
    vm.refresh = refresh;
    vm.addNew = addNew;
    vm.edit = edit;
    vm.remove = remove;

    activate();

    function activate() {
      refresh();
    }

    function refresh() {
      vm.loading = true;
      GradingService.list()
        .then(function (res) {
          // API returns { success, data }
          vm.items = (res && res.data) ? res.data : (res || []);
        })
        .catch(function (err) {
          ToastService && ToastService.error && ToastService.error('Failed to load grading systems');
          console && console.error && console.error('Grading list error', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function addNew() {
      $location.path('/grading-systems/new');
    }

    function edit(row) {
      if (!row || !row.id) return;
      $location.path('/grading-systems/' + row.id + '/edit');
    }

    function remove(row) {
      if (!row || !row.id) return;
      if (!confirm('Delete grading system "' + row.name + '"? This cannot be undone if not referenced by subjects.')) return;

      GradingService.remove(row.id)
        .then(function (res) {
          if (res && res.success) {
            ToastService && ToastService.success && ToastService.success('Deleted');
            refresh();
          } else {
            var msg = (res && res.message) ? res.message : 'Delete failed';
            ToastService && ToastService.warn && ToastService.warn(msg);
          }
        })
        .catch(function (err) {
          var msg = (err && err.data && err.data.message) ? err.data.message : 'Delete failed';
          ToastService && ToastService.error && ToastService.error(msg);
          console && console.error && console.error('Grading delete error', err);
        });
    }
  }

  GradingEditController.$inject = ['$scope', '$routeParams', '$location', 'GradingService', 'ToastService'];
  function GradingEditController($scope, $routeParams, $location, GradingService, ToastService) {
    var vm = this;
    vm.loading = false;
    vm.saving = false;

    vm.mode = ($routeParams.id ? 'edit' : 'create');
    vm.form = {
      id: null,
      name: ''
    };
    vm.items = []; // working set for bulk add in create mode or add-more in edit mode

    vm.addLine = addLine;
    vm.removeLine = removeLine;
    vm.save = save;
    vm.addSingleItem = addSingleItem;
    vm.deleteItem = deleteItem;
    vm.cancel = cancel;

    activate();

    function activate() {
      if (vm.mode === 'edit') {
        loadExisting($routeParams.id);
      } else {
        // initialize at least one line for create
        vm.items = [{ value: '', remarks: '' }];
      }
    }

    function loadExisting(id) {
      vm.loading = true;
      GradingService.get(id)
        .then(function (res) {
          // API returns { success, data: { system, items } }
          var payload = res && res.data ? res.data : res;
          vm.form.id = payload.system.id;
          vm.form.name = payload.system.name;
          vm.existingItems = (payload.items || []).slice(); // copy
        })
        .catch(function (err) {
          ToastService && ToastService.error && ToastService.error('Failed to load grading system');
          console && console.error && console.error('Grading load error', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function addLine() {
      vm.items.push({ value: '', remarks: '' });
    }

    function removeLine(i) {
      if (i < 0 || i >= vm.items.length) return;
      vm.items.splice(i, 1);
      if (vm.items.length === 0) {
        vm.items.push({ value: '', remarks: '' });
      }
    }

    function save() {
      if (!vm.form.name || vm.form.name.trim() === '') {
        ToastService && ToastService.warn && ToastService.warn('Name is required');
        return;
      }
      vm.saving = true;

      if (vm.mode === 'create') {
        GradingService.create({ name: vm.form.name.trim() })
          .then(function (res) {
            var sys = res && res.data ? res.data : res;
            // Filter valid items
            var items = (vm.items || []).filter(function (it) {
              return it && it.value !== '' && it.remarks && it.remarks.trim() !== '';
            }).map(function (it) {
              return { value: it.value, remarks: it.remarks.trim() };
            });
            if (items.length === 0) {
              ToastService && ToastService.success && ToastService.success('Created');
              $location.path('/grading-systems');
              return;
            }
            return GradingService.addItems(sys.id, items).then(function () {
              ToastService && ToastService.success && ToastService.success('Created with items');
              $location.path('/grading-systems');
            });
          })
          .catch(function (err) {
            var msg = (err && err.data && err.data.message) ? err.data.message : 'Save failed';
            ToastService && ToastService.error && ToastService.error(msg);
            console && console.error && console.error('Grading create error', err);
          })
          .finally(function () {
            vm.saving = false;
          });
      } else {
        // update details only for edit
        GradingService.update(vm.form.id, { name: vm.form.name.trim() })
          .then(function () {
            // Add any staged items (optional)
            var items = (vm.items || []).filter(function (it) {
              return it && it.value !== '' && it.remarks && it.remarks.trim() !== '';
            }).map(function (it) {
              return { value: it.value, remarks: it.remarks.trim() };
            });
            if (items.length) {
              return GradingService.addItems(vm.form.id, items).then(function () {
                ToastService && ToastService.success && ToastService.success('Updated');
                return loadExisting(vm.form.id);
              });
            } else {
              ToastService && ToastService.success && ToastService.success('Updated');
              return loadExisting(vm.form.id);
            }
          })
          .catch(function (err) {
            var msg = (err && err.data && err.data.message) ? err.data.message : 'Update failed';
            ToastService && ToastService.error && ToastService.error(msg);
            console && console.error && console.error('Grading update error', err);
          })
          .finally(function () {
            vm.saving = false;
          });
      }
    }

    function addSingleItem() {
      if (vm.mode !== 'edit') return;
      if (!vm.singleItem) vm.singleItem = { value: '', remarks: '' };
      var it = vm.singleItem;
      if (it.value === '' || !it.remarks || it.remarks.trim() === '') {
        ToastService && ToastService.warn && ToastService.warn('Value and remarks are required');
        return;
      }

      var valueToSend;
      if (!isNaN(it.value) && it.value !== null && it.value !== '' && isFinite(it.value)) {
        valueToSend = parseFloat(it.value);
      } else {
        valueToSend = it.value;
      }

      GradingService.addItem(vm.form.id, { value: valueToSend, remarks: it.remarks.trim() })
        .then(function () {
          ToastService && ToastService.success && ToastService.success('Item added');
          vm.singleItem = { value: '', remarks: '' };
          return loadExisting(vm.form.id);
        })
        .catch(function (err) {
          var msg = (err && err.data && err.data.message) ? err.data.message : 'Add item failed';
          ToastService && ToastService.error && ToastService.error(msg);
          console && console.error && console.error('Add item error', err);
        });
    }

    function deleteItem(item) {
      if (!item || !item.id) return;
      if (!confirm('Remove this item (' + item.value + ' - ' + item.remarks + ')?')) return;
      GradingService.deleteItem(item.id)
        .then(function () {
          ToastService && ToastService.success && ToastService.success('Item removed');
          return loadExisting(vm.form.id);
        })
        .catch(function (err) {
          var msg = (err && err.data && err.data.message) ? err.data.message : 'Delete item failed';
          ToastService && ToastService.error && ToastService.error(msg);
          console && console.error && console.error('Delete item error', err);
        });
    }

    function cancel() {
      $location.path('/grading-systems');
    }
  }

})();
