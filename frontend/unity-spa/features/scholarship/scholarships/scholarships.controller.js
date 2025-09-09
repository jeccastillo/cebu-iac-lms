(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ScholarshipsController', ScholarshipsController);

  ScholarshipsController.$inject = ['$location', 'ScholarshipsService', 'StorageService', 'ToastService'];
  function ScholarshipsController($location, ScholarshipsService, StorageService, ToastService) {
    var vm = this;

    // State
    vm.title = 'Scholarship Catalog';
    vm.state = getLoginState();

    vm.filters = {
      q: '',
      status: '',
      deduction_type: '',
      deduction_from: ''
    };

    vm.items = [];
    vm.loading = false;
    vm.error = null;

    // Form modal state
    vm.showForm = false;
    vm.isEditing = false;
    vm.form = {
      id: null,
      code: '',
      name: '',
      deduction_type: 'scholarship',
      deduction_from: 'in-house',
      status: 'active',
      compute_full: true,
      percent: null,
      fixed_amount: null,
      max_stacks: 1,
      description: ''
    };
    vm.validation = {};

    // Mutual Exclusions modal state
    vm.me = {
      open: false,
      base: null,       // selected scholarship row
      existing: [],     // [{id,name,deduction_type,status}]
      selection: ''     // other_id to add
    };
    vm.meOptions = [];  // available options for selection (derived from vm.items)

    // Methods
    vm.load = load;
    vm.search = search;
    vm.resetFilters = resetFilters;

    vm.openCreate = openCreate;
    vm.openEdit = openEdit;
    vm.cancelForm = cancelForm;
    vm.submitForm = submitForm;

    vm.softDelete = softDelete;
    vm.restore = restore;

    vm.statusLabel = statusLabel;
    vm.typeLabel = typeLabel;
    vm.fromLabel = fromLabel;

    // Mutual Exclusions actions
    vm.openME = openME;
    vm.closeME = closeME;
    vm.addME = addME;
    vm.removeME = removeME;

    activate();

    // Mutual Exclusions (ME) modal helpers
    function openME(row) {
      if (!row || !row.id) return;
      vm.me.base = row;
      vm.me.open = true;
      vm.me.selection = '';
      loadME(row.id);
    }

    function closeME() {
      vm.me = { open: false, base: null, existing: [], selection: '' };
      vm.meOptions = [];
    }

    function loadME(baseId) {
      if (!baseId) return;
      return ScholarshipsService.listME(baseId)
        .then(function (res) {
          var items = (res && res.data && res.data.items) ? res.data.items
            : (res && res.items) ? res.items
            : (Array.isArray(res) ? res : []);
          vm.me.existing = Array.isArray(items) ? items : [];
          computeMEOptions();
        })
        .catch(function () {
          try { ToastService && ToastService.error && ToastService.error('Failed to load mutual exclusions'); } catch (e) {}
        });
    }

    function computeMEOptions() {
      try {
        var baseId = vm.me.base && vm.me.base.id;
        var existingIds = {};
        (vm.me.existing || []).forEach(function (it) { existingIds[it.id] = true; });
        vm.meOptions = (vm.items || []).filter(function (it) {
          var id = parseInt(it && it.id, 10);
          if (isNaN(id) || id <= 0) return false;
          if (id === baseId) return false;
          if (existingIds[id]) return false;
          return true;
        });
      } catch (e) {
        vm.meOptions = [];
      }
    }

    function addME() {
      var base = vm.me.base;
      var otherId = parseInt(vm.me.selection, 10);
      if (!base || isNaN(otherId) || otherId <= 0) {
        try { ToastService && ToastService.warn && ToastService.warn('Select another scholarship/discount.'); } catch (e) {}
        return;
      }
      return ScholarshipsService.addME(base.id, otherId)
        .then(function () {
          try { ToastService && ToastService.success && ToastService.success('Mutual exclusion added.'); } catch (e) {}
          vm.me.selection = '';
          return loadME(base.id);
        })
        .catch(function (err) {
          var msg = (err && err.data && err.data.message) ? err.data.message : 'Failed to add mutual exclusion';
          try { ToastService && ToastService.error && ToastService.error(msg); } catch (e) {}
        });
    }

    function removeME(other) {
      var base = vm.me.base;
      var otherId = (other && other.id) ? other.id : parseInt(other, 10);
      if (!base || !otherId) return;
      return ScholarshipsService.removeME(base.id, otherId)
        .then(function () {
          try { ToastService && ToastService.success && ToastService.success('Mutual exclusion removed.'); } catch (e) {}
          return loadME(base.id);
        })
        .catch(function (err) {
          var msg = (err && err.data && err.data.message) ? err.data.message : 'Failed to remove mutual exclusion';
          try { ToastService && ToastService.error && ToastService.error(msg); } catch (e) {}
        });
    }

    function getLoginState() {
      try {
        return StorageService.getJSON('loginState');
      } catch (e) {
        return null;
      }
    }

    function activate() {
      load();
    }

    function setLoading(flag, err) {
      vm.loading = !!flag;
      vm.error = err ? (err.message || 'Request failed') : null;
    }

    function load() {
      setLoading(true);
      ScholarshipsService
        .list({
          q: vm.filters.q,
          status: vm.filters.status,
          deduction_type: vm.filters.deduction_type,
          deduction_from: vm.filters.deduction_from
        })
        .then(function (data) {
          // data = { success, data } or direct array depending on API wrapper
          var payload = data && data.data ? data.data : data;
          // payload might be a resource collection; normalize to array
          if (payload && payload.data && Array.isArray(payload.data)) {
            vm.items = payload.data;
          } else if (Array.isArray(payload)) {
            vm.items = payload;
          } else {
            vm.items = [];
          }
        })
        .catch(function (err) {
          vm.items = [];
          setLoading(false, err);
        })
        .finally(function () {
          setLoading(false);
        });
    }

    function search() {
      load();
    }

    function resetFilters() {
      vm.filters = {
        q: '',
        status: '',
        deduction_type: '',
        deduction_from: ''
      };
      load();
    }

    function openCreate() {
      vm.isEditing = false;
      vm.form = {
        id: null,
        code: '',
        name: '',
        deduction_type: 'scholarship',
        deduction_from: 'in-house',
        status: 'active',
        compute_full: true,
        max_stacks: 1,
        tuition_fee_rate: '',
        tuition_fee_fixed: '',
        basic_fee_rate: '',
        basic_fee_fixed: '',
        misc_fee_rate: '',
        misc_fee_fixed: '',
        lab_fee_rate: '',
        lab_fee_fixed: '',
        penalty_fee_rate: '',
        penalty_fee_fixed: '',
        other_fees_rate: '',
        other_fees_fixed: '',
        total_assessment_rate: '',
        total_assessment_fixed: '',
        description: ''
      };
      vm.validation = {};
      vm.showForm = true;
    }

    function openEdit(row) {
      if (!row) return;
      vm.isEditing = true;
      vm.form = {
        id: row.id,        
        name: row.name || '',
        deduction_type: row.deduction_type || 'scholarship',
        deduction_from: row.deduction_from || 'in-house',
        status: row.status || 'active',
        compute_full: (row && row.compute_full === false) ? false : true,
        max_stacks: (typeof row.max_stacks === 'number' && row.max_stacks > 0) ? row.max_stacks : 1,
        tuition_fee_rate: row.tuition_fee_rate,
        tuition_fee_fixed: row.tuition_fee_fixed,
        basic_fee_rate: row.basic_fee_rate,
        basic_fee_fixed: row.basic_fee_fixed,
        misc_fee_rate: row.misc_fee_rate,
        misc_fee_fixed: row.misc_fee_fixed,
        lab_fee_rate: row.lab_fee_rate,
        lab_fee_fixed: row.lab_fee_fixed,
        penalty_fee_rate: row.penalty_fee_rate,
        penalty_fee_fixed: row.penalty_fee_fixed,
        other_fees_rate: row.other_fees_rate,
        other_fees_fixed: row.other_fees_fixed,
        total_assessment_rate: row.total_assessment_rate,
        total_assessment_fixed: row.total_assessment_fixed,
        description: row.description || ''
      };
      vm.validation = {};
      vm.showForm = true;
    }

    function cancelForm() {
      vm.showForm = false;
      vm.isEditing = false;
      vm.validation = {};
    }

    function submitForm() {
      vm.validation = {};
      var payload = {        
        name: (vm.form.name || '').trim(),
        deduction_type: vm.form.deduction_type || 'scholarship',
        deduction_from: vm.form.deduction_from || 'in-house',
        status: vm.form.status || 'active',
        compute_full: !!vm.form.compute_full,
        tuition_fee_rate: vm.form.tuition_fee_rate === '' ? null : vm.form.tuition_fee_rate,
        tuition_fee_fixed	: vm.form.tuition_fee_fixed	 === '' ? null : vm.form.tuition_fee_fixed	,
        basic_fee_rate: vm.form.basic_fee_rate === '' ? null : vm.form.basic_fee_rate,
        basic_fee_fixed: vm.form.basic_fee_fixed === '' ? null : vm.form.basic_fee_fixed,
        misc_fee_rate: vm.form.misc_fee_rate === '' ? null : vm.form.misc_fee_rate,
        misc_fee_fixed: vm.form.misc_fee_fixed === '' ? null : vm.form.misc_fee_fixed,
        lab_fee_rate: vm.form.lab_fee_rate === '' ? null : vm.form.lab_fee_rate,
        lab_fee_fixed: vm.form.lab_fee_fixed === '' ? null : vm.form.lab_fee_fixed,
        penalty_fee_rate: vm.form.penalty_fee_rate === '' ? null : vm.form.penalty_fee_rate,
        penalty_fee_fixed: vm.form.penalty_fee_fixed === '' ? null : vm.form.penalty_fee_fixed,
        other_fees_rate: vm.form.other_fees_rate === '' ? null : vm.form.other_fees_rate,
        other_fees_fixed: vm.form.other_fees_fixed === '' ? null : vm.form.other_fees_fixed,
        total_assessment_rate: vm.form.total_assessment_rate === '' ? null : vm.form.total_assessment_rate,
        total_assessment_fixed: vm.form.total_assessment_fixed === '' ? null : vm.form.total_assessment_fixed,
        description: vm.form.description || ''
      };

      // Normalize and include max_stacks (default to 1 if blank/invalid)
      var _ms = parseInt(vm.form.max_stacks, 10);
      if (isNaN(_ms) || _ms < 1) { _ms = 1; }
      payload.max_stacks = _ms;

      setLoading(true);

      var p = vm.isEditing
        ? ScholarshipsService.update(vm.form.id, payload)
        : ScholarshipsService.create(payload);

      p.then(function () {
          if (vm.isEditing) {
            try { ToastService && ToastService.success && ToastService.success('Scholarship updated.'); } catch (e) {}
          }
          cancelForm();
          load();
        })
        .catch(function (err) {
          // Try to surface validation messages if present
          try {
            var data = err && err.data ? err.data : null;
            if (data && data.errors) {
              vm.validation = data.errors;
            } else if (data && data.message) {
              vm.error = data.message;
            } else {
              vm.error = 'Save failed';
            }
          } catch (e) {
            vm.error = 'Save failed';
          }
        })
        .finally(function () {
          setLoading(false);
        });
    }

    function softDelete(row) {
      if (!row || !row.id) return;
      setLoading(true);
      ScholarshipsService.destroy(row.id)
        .then(function () {
          load();
        })
        .catch(function (err) {
          vm.error = (err && err.message) || 'Delete failed';
        })
        .finally(function () {
          setLoading(false);
        });
    }

    function restore(row) {
      if (!row || !row.id) return;
      setLoading(true);
      ScholarshipsService.restore(row.id)
        .then(function () {
          load();
        })
        .catch(function (err) {
          vm.error = (err && err.message) || 'Restore failed';
        })
        .finally(function () {
          setLoading(false);
        });
    }

    function statusLabel(v) {
      return (v || '').charAt(0).toUpperCase() + (v || '').slice(1);
    }
    function typeLabel(v) {
      return v === 'discount' ? 'Discount' : 'Scholarship';
    }
    function fromLabel(v) {
      return v === 'external' ? 'External' : 'In-house';
    }
  }
})();
