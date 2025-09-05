(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('TuitionYearsListController', TuitionYearsListController)
    .controller('TuitionYearEditController', TuitionYearEditController);

  TuitionYearsListController.$inject = ['$location', 'TuitionYearsService'];
  function TuitionYearsListController($location, TuitionYearsService) {
    var vm = this;

    // State
    vm.loading = false;
    vm.rows = [];

    // Methods
    vm.load = load;
    vm.create = create;
    vm.edit = edit;
    vm.duplicate = duplicate;
    vm.remove = removeItem;
    vm.setDefaultCollege = function (id) { return setDefault(id, 'college'); };
    vm.setDefaultShs = function (id) { return setDefault(id, 'shs'); };

    activate();

    function activate() {
      load();
    }

    function load() {
      vm.loading = true;
      TuitionYearsService.list({})
        .then(function (res) {
          // res shape from _unwrap: { success, data }
          var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.rows = (items || []).map(function (r) {
            return {
              intID: r.intID || r.id,
              year: r.year || r.sy || r.strLabel || '',
              isDefault: r.isDefault ? 1 : 0,
              isDefaultShs: r.isDefaultShs ? 1 : 0,
              final: r.final ? 1 : 0
            };
          });
        })
        .finally(function () { vm.loading = false; });
    }

    function create() {
      // Ask for Year label then create
      var yearLabel = '';
      if (window.Swal) {
        Swal.fire({
          title: 'Create Tuition Year',
          input: 'text',
          inputLabel: 'Year label (e.g. AY 2025-2026)',
          inputPlaceholder: 'Enter year label',
          showCancelButton: true,
          confirmButtonText: 'Create'
        }).then(function (res) {
          if (!res.isConfirmed) return;
          yearLabel = (res.value || '').trim();
          if (!yearLabel) {
            Swal.fire({ icon: 'error', title: 'Year label required' });
            return;
          }
          _createWithLabel(yearLabel);
        });
      } else {
        try {
          yearLabel = (prompt('Enter Tuition Year label (e.g. AY 2025-2026):') || '').trim();
        } catch (e) { yearLabel = ''; }
        if (!yearLabel) return;
        _createWithLabel(yearLabel);
      }
    }
    function _createWithLabel(label) {
      vm.loading = true;
      TuitionYearsService.create({ year: label })
        .then(function (res) {
          var newid = (res && res.newid) ? res.newid : (res && res.data && res.data.newid ? res.data.newid : null);
          if (newid) {
            $location.path('/finance/tuition-years/' + newid);
          } else {
            load();
          }
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Created' });
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Create failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function edit(id) {
      $location.path('/finance/tuition-years/' + id);
    }

    function duplicate(id) {
      if (window.Swal) {
        Swal.fire({
          title: 'Duplicate Tuition Year',
          text: 'Create a copy of this tuition year?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Duplicate'
        }).then(function (res) {
          if (!res.isConfirmed) return;
          _duplicate(id);
        });
      } else {
        if (!confirm('Duplicate this tuition year?')) return;
        _duplicate(id);
      }
    }
    function _duplicate(id) {
      vm.loading = true;
      TuitionYearsService.duplicate(id)
        .then(function (res) {
          var newid = (res && res.newid) ? res.newid : (res && res.data && res.data.newid ? res.data.newid : null);
          if (newid) {
            $location.path('/finance/tuition-years/' + newid);
          } else {
            load();
          }
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Duplicated' });
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Duplicate failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function removeItem(id) {
      if (window.Swal) {
        Swal.fire({
          title: 'Delete Tuition Year',
          text: 'This action cannot be undone. Continue?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete'
        }).then(function (res) {
          if (!res.isConfirmed) return;
          _delete(id);
        });
      } else {
        if (!confirm('Delete this tuition year?')) return;
        _delete(id);
      }
    }
    function _delete(id) {
      vm.loading = true;
      TuitionYearsService.remove(id)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' });
          load();
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Delete failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function setDefault(id, scope) {
      vm.loading = true;
      TuitionYearsService.setDefault(id, scope)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Updated', text: 'Default set for ' + scope.toUpperCase() });
          load();
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Set default failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }
  }

  TuitionYearEditController.$inject = ['$routeParams', 'TuitionYearsService', 'ProgramsService', 'SubjectsService'];
  function TuitionYearEditController($routeParams, TuitionYearsService, ProgramsService, SubjectsService) {
    var vm = this;

    // State
    vm.loading = false;
    vm.id = parseInt($routeParams.id, 10) || 0;

    // Base form
    vm.form = {
      intID: vm.id,
      year: '',
      pricePerUnit: 0,
      pricePerUnitOnline: 0,
      pricePerUnitHybrid: 0,
      pricePerUnitHyflex: 0,
      installmentDP: 0,
      installmentIncrease: 0,
      installmentFixed: null,
      freeElectiveCount: null,
      isDefault: 0,
      isDefaultShs: 0,
      final: 0
    };

    // Related entities
    vm.misc = [];
    vm.labs = [];
    vm.tracks = [];
    vm.programs = [];
    vm.electives = [];

    // Select options
    vm.shsProgramOptions = [];
    vm.collegeProgramOptions = [];
    vm.subjectOptions = [];
    // Lookup maps for displaying names instead of IDs
    vm._shsProgramMap = {};
    vm._collegeProgramMap = {};
    vm._subjectMap = {};
    // Lookup helpers exposed to the template
    vm.lookupShsProgram = function (id) { return lookupLabel(vm._shsProgramMap, id); };
    vm.lookupCollegeProgram = function (id) { return lookupLabel(vm._collegeProgramMap, id); };
    vm.lookupSubject = function (id) { return lookupLabel(vm._subjectMap, id); };

    // Inline edit state
    vm.editing = { miscId: null, labId: null };

    // Methods
    vm.loadAll = loadAll;
    vm.save = save;
    vm.finalize = function () { return setFinal(1); };
    vm.unfinalize = function () { return setFinal(0); };

    vm.addMisc = addMisc;
    vm.deleteMisc = deleteMisc;
    vm.startEditMisc = startEditMisc;
    vm.cancelEditMisc = cancelEditMisc;
    vm.updateMisc = updateMisc;

    vm.addLab = addLab;
    vm.deleteLab = deleteLab;
    vm.startEditLab = startEditLab;
    vm.cancelEditLab = cancelEditLab;
    vm.updateLab = updateLab;

    vm.addTrack = addTrack;
    vm.deleteTrack = deleteTrack;

    vm.addProgram = addProgram;
    vm.deleteProgram = deleteProgram;

    vm.addElective = addElective;
    vm.deleteElective = deleteElective;

    activate();

    function activate() {
      if (!vm.id) return;
      loadOptions();
      loadAll();
    }

    function loadOptions() {
      // Load Programs for dropdowns (separate by type)
      ProgramsService.list({ type: 'shs' })
        .then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.shsProgramOptions = (rows || []).map(function (p) {
            var id = p.intProgramID || p.id;
            var code = p.strProgramCode || '';
            var desc = p.strProgramDescription || p.title || '';
            var label = desc ? (desc + (code ? ' (' + code + ')' : '')) : (code || ('Program ' + id));
            return { id: String(id), label: label };
          });
          vm._shsProgramMap = buildMap(vm.shsProgramOptions);
        })
        .catch(function () { vm.shsProgramOptions = []; });

      ProgramsService.list({ type: 'college' })
        .then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.collegeProgramOptions = (rows || []).map(function (p) {
            var id = p.intProgramID || p.id;
            var code = p.strProgramCode || '';
            var desc = p.strProgramDescription || p.title || '';
            var label = desc ? (desc + (code ? ' (' + code + ')' : '')) : (code || ('Program ' + id));
            return { id: String(id), label: label };
          });
          vm._collegeProgramMap = buildMap(vm.collegeProgramOptions);
        })
        .catch(function () { vm.collegeProgramOptions = []; });

      // Load Subjects for dropdown (Electives)
      SubjectsService.list({ limit: 500 })
        .then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.subjectOptions = (rows || []).map(function (s) {
            var id = s.intID || s.id;
            var code = s.strCode || '';
            var desc = s.strDescription || '';
            var label = code ? (code + (desc ? ' - ' + desc : '')) : (desc || ('Subject ' + id));
            return { id: id, label: label };
          });
          vm._subjectMap = buildMap(vm.subjectOptions);
        })
        .catch(function () { vm.subjectOptions = []; });
    }

    function loadAll() {
      vm.loading = true;
      // Base
      TuitionYearsService.show(vm.id).then(function (res) {
        var d = (res && res.data) ? res.data : res;
        if (!d) return;
        vm.form.intID = d.intID || d.id || vm.id;
        vm.form.year = d.year || '';
        vm.form.pricePerUnit = num(d.pricePerUnit);
        vm.form.pricePerUnitOnline = num(d.pricePerUnitOnline);
        vm.form.pricePerUnitHybrid = num(d.pricePerUnitHybrid);
        vm.form.pricePerUnitHyflex = num(d.pricePerUnitHyflex);
        vm.form.installmentDP = num(d.installmentDP);
        vm.form.installmentIncrease = num(d.installmentIncrease);
        vm.form.installmentFixed = d.installmentFixed !== undefined ? d.installmentFixed : null;
        vm.form.freeElectiveCount = d.freeElectiveCount !== undefined ? d.freeElectiveCount : null;
        vm.form.isDefault = d.isDefault ? 1 : 0;
        vm.form.isDefaultShs = d.isDefaultShs ? 1 : 0;
        vm.form.final = d.final ? 1 : 0;
      }).finally(function () {
        // Related entities
        TuitionYearsService.listMisc(vm.id).then(function (res) {
          vm.misc = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        });
        TuitionYearsService.listLabFees(vm.id).then(function (res) {
          vm.labs = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        });
        TuitionYearsService.listTracks(vm.id).then(function (res) {
          vm.tracks = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        });
        TuitionYearsService.listPrograms(vm.id).then(function (res) {
          vm.programs = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        });
        TuitionYearsService.listElectives(vm.id).then(function (res) {
          vm.electives = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        });
        vm.loading = false;
      });
    }

    function save() {
      if (!vm.id) return;
      if (vm.form.final === 1) {
        if (window.Swal) Swal.fire({ icon: 'info', title: 'Finalized', text: 'Un-finalize to edit.' });
        return;
      }
      vm.loading = true;
      // Build payload (exclude intID from fields, service adds intID)
      var fields = {
        year: vm.form.year,
        pricePerUnit: num(vm.form.pricePerUnit),
        pricePerUnitOnline: num(vm.form.pricePerUnitOnline),
        pricePerUnitHybrid: num(vm.form.pricePerUnitHybrid),
        pricePerUnitHyflex: num(vm.form.pricePerUnitHyflex),
        installmentDP: num(vm.form.installmentDP),
        installmentIncrease: num(vm.form.installmentIncrease),
        installmentFixed: vm.form.installmentFixed !== null && vm.form.installmentFixed !== '' ? num(vm.form.installmentFixed) : null,
        freeElectiveCount: vm.form.freeElectiveCount !== null && vm.form.freeElectiveCount !== '' ? parseInt(vm.form.freeElectiveCount, 10) : null,
        isDefault: vm.form.isDefault ? 1 : 0,
        isDefaultShs: vm.form.isDefaultShs ? 1 : 0
      };
      TuitionYearsService.update(vm.id, fields)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Saved' });
          loadAll();
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Save failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function setFinal(flag) {
      if (!vm.id) return;
      vm.loading = true;
      TuitionYearsService.update(vm.id, { final: flag ? 1 : 0 })
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: flag ? 'Finalized' : 'Un-finalized' });
          loadAll();
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Operation failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    // ---- Misc ----
    function addMisc(item) {
      if (!vm.id || !item) return;
      var body = Object.assign({ tuitionYearID: vm.id }, item);
      TuitionYearsService.addExtra('misc', body)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Added' }); })
        .catch(function (err) { alertErr(err, 'Add failed'); });
    }
    function deleteMisc(intID) {
      TuitionYearsService.deleteExtra('misc', intID)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' }); })
        .catch(function (err) { alertErr(err, 'Delete failed'); });
    }

    function startEditMisc(row) {
      if (vm.form.final === 1 || !row) return;
      vm.editing.miscId = row.intID;
      vm.local = vm.local || {};
      vm.local.miscEdit = {
        name: row.name,
        miscRegular: num(row.miscRegular),
        miscOnline: num(row.miscOnline),
        miscHyflex: num(row.miscHyflex),
        miscHybrid: num(row.miscHybrid),
        type: row.type
      };
    }

    function cancelEditMisc() {
      vm.editing.miscId = null;
      vm.local = vm.local || {};
      vm.local.miscEdit = {};
    }

    function updateMisc(id) {
      if (!id || vm.form.final === 1) return;
      var e = (vm.local && vm.local.miscEdit) || {};
      var payload = {
        name: e.name,
        miscRegular: num(e.miscRegular),
        miscOnline: num(e.miscOnline),
        miscHyflex: num(e.miscHyflex),
        miscHybrid: num(e.miscHybrid),
        type: e.type
      };
      TuitionYearsService.updateExtra('misc', id, payload)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Updated' });
          vm.editing.miscId = null;
          loadAll();
        })
        .catch(function (err) { alertErr(err, 'Update failed'); });
    }

    // ---- Lab Fees ----
    function addLab(item) {
      if (!vm.id || !item) return;
      var body = Object.assign({ tuitionYearID: vm.id }, item);
      TuitionYearsService.addExtra('lab_fee', body)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Added' }); })
        .catch(function (err) { alertErr(err, 'Add failed'); });
    }
    function deleteLab(intID) {
      TuitionYearsService.deleteExtra('lab_fee', intID)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' }); })
        .catch(function (err) { alertErr(err, 'Delete failed'); });
    }
    function startEditLab(row) {
      if (vm.form.final === 1 || !row) return;
      vm.editing.labId = row.intID;
      vm.local = vm.local || {};
      vm.local.labEdit = {
        name: row.name,
        labRegular: num(row.labRegular),
        labOnline: num(row.labOnline),
        labHyflex: num(row.labHyflex),
        labHybrid: num(row.labHybrid)
      };
    }
    function cancelEditLab() {
      vm.editing.labId = null;
      vm.local = vm.local || {};
      vm.local.labEdit = {};
    }
    function updateLab(id) {
      if (!id || vm.form.final === 1) return;
      var e = (vm.local && vm.local.labEdit) || {};
      var payload = {
        name: e.name,
        labRegular: num(e.labRegular),
        labOnline: num(e.labOnline),
        labHyflex: num(e.labHyflex),
        labHybrid: num(e.labHybrid)
      };
      TuitionYearsService.updateExtra('lab_fee', id, payload)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Updated' });
          vm.editing.labId = null;
          loadAll();
        })
        .catch(function (err) { alertErr(err, 'Update failed'); });
    }

    // ---- Tracks (SHS) ----
    function addTrack(item) {
      if (!vm.id || !item) return;
      var body = Object.assign({ tuitionyear_id: vm.id }, item);
      TuitionYearsService.addExtra('track', body)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Added' }); })
        .catch(function (err) { alertErr(err, 'Add failed'); });
    }
    function deleteTrack(id) {
      TuitionYearsService.deleteExtra('track', id)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' }); })
        .catch(function (err) { alertErr(err, 'Delete failed'); });
    }

    // ---- Programs (College) ----
    function addProgram(item) {
      if (!vm.id || !item) return;
      var body = Object.assign({ tuitionyear_id: vm.id }, item);
      TuitionYearsService.addExtra('program', body)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Added' }); })
        .catch(function (err) { alertErr(err, 'Add failed'); });
    }
    function deleteProgram(id) {
      TuitionYearsService.deleteExtra('program', id)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' }); })
        .catch(function (err) { alertErr(err, 'Delete failed'); });
    }

    // ---- Electives (SHS) ----
    function addElective(item) {
      if (!vm.id || !item) return;
      // Coerce subject_id to integer to avoid Angular's "number:XX" string leaking to backend
      var subjId = (item.subject_id !== null && item.subject_id !== undefined && item.subject_id !== '') ? parseInt(item.subject_id, 10) : null;
      var body = Object.assign({ tuitionyear_id: vm.id }, item, { subject_id: subjId });
      TuitionYearsService.addExtra('elective', body)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Added' }); })
        .catch(function (err) { alertErr(err, 'Add failed'); });
    }
    function deleteElective(id) {
      TuitionYearsService.deleteExtra('elective', id)
        .then(function () { loadAll(); if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' }); })
        .catch(function (err) { alertErr(err, 'Delete failed'); });
    }

    // Helpers
    function buildMap(options) {
      var m = {};
      (options || []).forEach(function (o) {
        var s = String(o.id);
        m[s] = o.label;
        var n = parseInt(o.id, 10);
        if (!isNaN(n)) m[n] = o.label;
      });
      return m;
    }
    function lookupLabel(map, id) {
      if (id === null || id === undefined || id === '') return '';
      return map[id] || map[String(id)] || String(id);
    }
    function num(v) {
      var n = parseFloat(v);
      return isNaN(n) ? 0 : n;
    }
    function alertErr(err, fallback) {
      var m = (err && err.message) || (err && err.data && err.data.message) || fallback || 'Operation failed';
      if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
    }
  }

})();
