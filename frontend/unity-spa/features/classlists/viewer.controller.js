(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClasslistViewerController', ClasslistViewerController);

  ClasslistViewerController.$inject = ['$routeParams', '$location', 'ClasslistsService', 'ToastService', 'RoleService', 'StorageService'];
  function ClasslistViewerController($routeParams, $location, ClasslistsService, ToastService, RoleService, StorageService) {
    var vm = this;

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.classlist = null;
    vm.students = [];
    vm.options = { midterm: { mode: 'numeric', min: 1, max: 100 }, finals: { mode: 'numeric', min: 1, max: 100 } };
    vm.window = null;
    vm.permissions = { can_edit: false, can_finalize: false, can_unfinalize: false, role: null };

    // Determine active period based on intFinalized:
    //  - 0 => midterm
    //  - 1 => finals
    //  - 2 => finals (read-only unless registrar/admin unfinalize)
    vm.period = 'midterm';

    // edits: { csid: { midterm: value | null, finals: value | null } }
    vm.edits = {};

    vm.init = init;
    vm.reload = reload;
    vm.save = save;
    vm.finalize = finalize;
    vm.unfinalize = unfinalize;
    vm.back = back;
    vm.hasIncompleteGrades = hasIncompleteGrades;

    // Excel template + import (per-classlist)
    vm.downloadTemplate = downloadTemplate;
    vm.onFilePicked = onFilePicked;
    vm._onFileChange = _onFileChange; // DOM onchange handler wrapper
    vm.upload = upload;
    vm.uploads = { midterm: null, finals: null };

    function init() {
      if (!vm.id || isNaN(vm.id)) {
        vm.error = 'Invalid classlist id';
        return;
      }
      reload();
    }

    function reload() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.getViewer(vm.id)
        .then(function (res) {
          var data = (res && res.data) ? res.data : res;
          if (!data || !data.classlist) {
            vm.error = 'Classlist not found';
            return;
          }
          vm.classlist = data.classlist;
          vm.students = Array.isArray(data.students) ? data.students : [];
          vm.options = data.grade_options || vm.options;
          vm.window = data.classlist.window || null;
          vm.permissions = data.classlist.permissions || vm.permissions;

          // Frontend safety override:
          // - if user has admin/registrar roles (per RoleService), allow edit/finalize
          // - if user is assigned faculty (loginState.faculty_id matches classlist.intFacultyID), allow edit/finalize
          try {
            if (RoleService && RoleService.hasAny && RoleService.hasAny(['admin', 'registrar'])) {
              vm.permissions.can_edit = true;
              vm.permissions.can_finalize = true;
              vm.permissions.can_unfinalize = true;
              vm.permissions.role = vm.permissions.role || (RoleService.hasAny(['admin']) ? 'admin' : 'registrar');
            } else {
              var state = null;
              try {
                state = StorageService && StorageService.getJSON ? StorageService.getJSON('loginState') : null;
              } catch (_e) { /* ignore */ }
              var fid = state && state.faculty_id ? parseInt(state.faculty_id, 10) : null;
              var clFid = vm.classlist && vm.classlist.intFacultyID ? parseInt(vm.classlist.intFacultyID, 10) : null;
              if (fid && clFid && fid === clFid) {
                vm.permissions.can_edit = true;
                vm.permissions.can_finalize = true;
                vm.permissions.role = vm.permissions.role || 'faculty';
              }
            }
          } catch (e) {
            // no-op
          }

          vm.period = (vm.classlist.intFinalized === 0) ? 'midterm' : 'finals';

          // Prime edits from existing grades
          vm.edits = {};
          vm.students.forEach(function (s) {
            vm.edits[s.intCSID] = {
              midterm: normalizeGrade(s.floatMidtermGrade),
              finals: normalizeGrade(s.floatFinalsGrade)
            };
          });
        })
        .catch(function (e) {
          console.error('viewer load error:', e);
          // Block viewing when not assigned faculty and not registrar/admin (403 from API)
          if (e && (e.status === 403 || (e.data && e.data.message && /forbidden/i.test(e.data.message)))) {
            vm.error = (e && e.data && e.data.message) ? e.data.message : 'Forbidden: you are not allowed to view this classlist.';
            if (ToastService && ToastService.error) {
              ToastService.error(vm.error);
            }
            // Navigate back to classlists list
            $location.path('/classlists');
            return;
          }
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : null;
          vm.error = apiMsg || 'Failed to load classlist viewer';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function normalizeGrade(g) {
      if (g === null || typeof g === 'undefined') return null;
      // In legacy data, 50 or "NGS" often indicates no-grade-submitted.
      if (g === 'NGS' || g === 50) return null;
      return g;
    }

    function buildItemsPayload() {
      var items = [];
      vm.students.forEach(function (s) {
        var csid = s.intCSID;
        var val = vm.period === 'midterm'
          ? (vm.edits[csid] ? vm.edits[csid].midterm : null)
          : (vm.edits[csid] ? vm.edits[csid].finals : null);

        if (val !== null && typeof val !== 'undefined' && val !== '') {
          items.push({
            intCSID: csid,
            grade: val
          });
        }
      });
      return items;
    }

    // Returns true if any student lacks a grade for the active period
    function hasIncompleteGrades() {
      return missingCountForPeriod() > 0;
    }

    // Internal helper to count missing grades in the active period
    function missingCountForPeriod() {
      var missing = 0;
      var isMidterm = (vm.period === 'midterm');
      vm.students.forEach(function (s) {
        var csid = s.intCSID;
        var val = isMidterm
          ? (vm.edits[csid] ? vm.edits[csid].midterm : null)
          : (vm.edits[csid] ? vm.edits[csid].finals : null);
        if (val === null || typeof val === 'undefined' || val === '') {
          missing++;
        }
      });
      return missing;
    }

    function save() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        period: vm.period,
        overwrite_ngs: true, // allow filling previously NGS/empty values by default
        items: buildItemsPayload()
      };

      if (!payload.items.length) {
        vm.loading = false;
        vm.error = 'No grades to save for the current period.';
        return;
      }

      ClasslistsService.saveGrades(vm.id, payload)
        .then(function (res) {
          if (res && res.success) {
            vm.success = 'Grades saved';
            ToastService && ToastService.success && ToastService.success('Grades saved');
            // Refresh to reflect any server-side remark computations and latest values
            reload();
          } else {
            vm.error = (res && res.message) ? res.message : 'Save failed';
            ToastService && ToastService.error && ToastService.error(vm.error);
          }
        })
        .catch(function (e) {
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : null;
          vm.error = apiMsg || 'Save failed';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function finalize() {
      vm.error = null;
      vm.success = null;

      // Prevent finalization if any grade is missing for the active period
      if (hasIncompleteGrades()) {
        var periodLabel = (vm.period || '').toUpperCase();
        var miss = missingCountForPeriod();
        var msg = 'Cannot finalize: there ' + (miss === 1 ? 'is 1 student' : ('are ' + miss + ' students')) +
          ' without a grade for ' + periodLabel + '. Please complete all grades.';
        vm.error = msg;
        ToastService && ToastService.error && ToastService.error(msg);
        return;
      }

      vm.loading = true;

      var payload = { period: vm.period, confirm_complete: false };

      ClasslistsService.finalize(vm.id, payload)
        .then(function (res) {
          if (res && res.success) {
            vm.success = 'Finalized';
            ToastService && ToastService.success && ToastService.success('Finalized');
            reload();
          } else {
            vm.error = (res && res.message) ? res.message : 'Finalize failed';
            ToastService && ToastService.error && ToastService.error(vm.error);
          }
        })
        .catch(function (e) {
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : null;
          vm.error = apiMsg || 'Finalize failed';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function unfinalize() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.unfinalize(vm.id)
        .then(function (res) {
          if (res && res.success) {
            vm.success = 'Unfinalized';
            ToastService && ToastService.success && ToastService.success('Unfinalized');
            reload();
          } else {
            vm.error = (res && res.message) ? res.message : 'Unfinalize failed';
            ToastService && ToastService.error && ToastService.error(vm.error);
          }
        })
        .catch(function (e) {
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : null;
          vm.error = apiMsg || 'Unfinalize failed';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function back() {
      $location.path('/classlists');
    }

    // Download per-classlist template for a period
    function downloadTemplate(period) {
      try {
        vm.loading = true;
        ClasslistsService.downloadGradesTemplate(vm.id, period)
          .then(function (res) {
            var data = (res && res.data) ? res.data : null;
            var filename = (res && res.filename) ? res.filename : ('classlist-' + vm.id + '-' + (period || 'period') + '-template.xlsx');
            if (!data) {
              throw new Error('No file data');
            }
            var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var url = (window.URL || window.webkitURL).createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
              document.body.removeChild(a);
              (window.URL || window.webkitURL).revokeObjectURL(url);
            }, 0);
            ToastService && ToastService.success && ToastService.success('Template downloaded: ' + filename);
          })
          .catch(function (e) {
            var msg = (e && e.message) ? e.message : 'Failed to download template';
            ToastService && ToastService.error && ToastService.error(msg);
          })
          .finally(function () {
            vm.loading = false;
          });
      } catch (err) {
        vm.loading = false;
        ToastService && ToastService.error && ToastService.error('Failed to download template');
      }
    }

    // DOM onchange wrapper: file input calls this; routes to onFilePicked for current active period
    function _onFileChange($event) {
      try {
        onFilePicked(vm.period, $event);
      } catch (e) {
        // no-op
      }
    }

    // Capture selected file
    function onFilePicked(period, $event) {
      try {
        var file = $event && $event.target && $event.target.files && $event.target.files.length ? $event.target.files[0] : null;
        if (!file) {
          ToastService && ToastService.warn && ToastService.warn('No file selected');
          return;
        }
        if (!/\.xlsx$/i.test(file.name)) {
          ToastService && ToastService.warn && ToastService.warn('Please select an .xlsx file');
          return;
        }
        vm.uploads = vm.uploads || { midterm: null, finals: null };
        vm.uploads[period] = file;
        ToastService && ToastService.success && ToastService.success('Selected: ' + file.name + ' for ' + (period || '').toUpperCase());
      } catch (e) {
        ToastService && ToastService.error && ToastService.error('Failed to read selected file');
      }
    }

    // Upload grades for the given period using the selected file
    function upload(period) {
      vm.error = null;
      vm.success = null;

      if (!vm.permissions || !vm.permissions.can_edit) {
        ToastService && ToastService.error && ToastService.error('Not allowed to upload grades.');
        return;
      }

      var file = vm.uploads && vm.uploads[period];
      if (!file) {
        ToastService && ToastService.warn && ToastService.warn('Please select an .xlsx file first.');
        return;
      }

      vm.loading = true;
      ClasslistsService.importGrades(vm.id, file, period)
        .then(function (res) {
          if (res && res.success) {
            var result = res.result || {};
            var msg = 'Upload complete: ' + (result.updated || 0) + ' updated, ' + (result.skipped || 0) + ' skipped.';
            vm.success = msg;
            ToastService && ToastService.success && ToastService.success(msg);
            // Clear file input and cache
            try {
              var el = document.getElementById('gradesUploadInput');
              if (el) el.value = '';
            } catch (_e) {}
            if (vm.uploads) vm.uploads[period] = null;
            // Refresh viewer to reflect latest grades/remarks
            reload();
          } else {
            var emsg = (res && res.message) ? res.message : 'Upload failed';
            vm.error = emsg;
            ToastService && ToastService.error && ToastService.error(emsg);
          }
        })
        .catch(function (e) {
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : (e && e.message ? e.message : null);
          vm.error = apiMsg || 'Upload failed';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Kick off
    vm.init();
  }

})();
