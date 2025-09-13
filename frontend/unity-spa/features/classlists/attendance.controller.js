(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClasslistAttendanceController', ClasslistAttendanceController);

  ClasslistAttendanceController.$inject = ['$routeParams', '$location', '$timeout', 'ClasslistsService', 'ToastService'];
  function ClasslistAttendanceController($routeParams, $location, $timeout, ClasslistsService, ToastService) {
    var vm = this;

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;

    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.dates = [];
    vm.selectedDate = null; // { id, attendance_date, ... }
    vm.rows = []; // [{ intCSID, intStudentID, is_present (null/true/false), remarks, ... , _dirty }]
    vm.newDate = '';
    vm.newPeriod = 'midterm';
    // Import/Export (template + upload)
    vm.importFile = null;
    vm.uploading = false;

    // Matrix Template & Import (date-range, per period)
    vm.matrixStart = '';
    vm.matrixEnd = '';
    vm.matrixPeriod = 'midterm';
    vm.matrixFile = null;
    vm.matrixDownloading = false;
    vm.matrixUploading = false;
    vm.matrixFileLoading = false; // show loading indicator right after selecting file

    vm.init = init;
    vm.back = back;

    vm.loadDates = loadDates;
    vm.createDate = createDate;
    vm.selectDate = selectDate;
    vm.reloadDate = reloadDate;

    vm.markAllPresent = markAllPresent;
    vm.markAllAbsent = markAllAbsent;
    vm.markAllUnset = markAllUnset;

    vm.setPresent = setPresent;
    vm.setAbsent = setAbsent;
    vm.setUnset = setUnset;
    vm.onRemarksChange = onRemarksChange;

    vm.save = save;
    // Template download + Import upload
    vm.downloadTemplate = downloadTemplate;
    vm.onImportFileChange = onImportFileChange;
    vm.importAttendance = importAttendance;

    // Matrix actions
    vm.downloadMatrixTemplate = downloadMatrixTemplate;
    vm.onMatrixFileChange = onMatrixFileChange;
    vm.importMatrix = importMatrix;

    function init() {
      if (!vm.id || isNaN(vm.id)) {
        vm.error = 'Invalid classlist id';
        return;
      }
      loadDates();
    }

    function back() {
      $location.path('/classlists/' + vm.id + '/viewer');
    }

    function loadDates() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.getAttendanceDates(vm.id)
        .then(function (res) {
          var data = (res && res.data) ? res.data : res;
          vm.dates = Array.isArray(data) ? data : [];
          // If a date was previously selected, try to keep selection
          if (vm.selectedDate) {
            var match = vm.dates.find(function (d) { return d.id === vm.selectedDate.id; });
            if (!match && vm.dates.length) {
              vm.selectedDate = null;
            }
          }
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to load attendance dates';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function createDate() {
      if (!vm.newDate) {
        vm.error = 'Please select a date (YYYY-MM-DD)';
        return;
      }
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.createAttendanceDate(vm.id, vm.newDate, vm.newPeriod)
        .then(function (res) {
          if (res && res.success === false) {
            vm.error = res.message || 'Failed to create attendance date';
            ToastService && ToastService.error && ToastService.error(vm.error);
            return;
          }
          vm.success = 'Attendance date created';
          ToastService && ToastService.success && ToastService.success('Date created');
          vm.newDate = '';
          return loadDates();
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to create attendance date';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function selectDate(dateRow) {
      if (!dateRow || !dateRow.id) return;
      vm.selectedDate = dateRow;
      reloadDate();
    }

    function reloadDate() {
      if (!vm.selectedDate || !vm.selectedDate.id) return;
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.getAttendanceByDate(vm.id, vm.selectedDate.id)
        .then(function (res) {
          var data = (res && res.data) ? res.data : res;
          var rows = (data && data.students) ? data.students : [];
          vm.rows = rows.map(function (r) {
            return Object.assign({}, r, { _dirty: false });
          });
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to load attendance details';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function markAllPresent() {
      vm.rows.forEach(function (r) {
        if (r.is_present !== true || r.remarks) {
          r.is_present = true;
          r.remarks = null;
          r._dirty = true;
        }
      });
    }

    function markAllAbsent() {
      vm.rows.forEach(function (r) {
        if (r.is_present !== false) {
          r.is_present = false;
          // keep remarks as-is; user may bulk-absent then add remarks per-row
          r._dirty = true;
        }
      });
    }

    function markAllUnset() {
      vm.rows.forEach(function (r) {
        if (r.is_present !== null || r.remarks) {
          r.is_present = null;
          r.remarks = null;
          r._dirty = true;
        }
      });
    }

    function setPresent(row) {
      if (!row) return;
      if (row.is_present !== true || row.remarks) {
        row.is_present = true;
        row.remarks = null;
        row._dirty = true;
      }
    }

    function setAbsent(row) {
      if (!row) return;
      if (row.is_present !== false) {
        row.is_present = false;
        // remarks optional; leave as-is
        row._dirty = true;
      }
    }

    function setUnset(row) {
      if (!row) return;
      if (row.is_present !== null || row.remarks) {
        row.is_present = null;
        row.remarks = null;
        row._dirty = true;
      }
    }

    function onRemarksChange(row) {
      if (!row) return;
      // If user types remarks but row is marked present, flip to absent automatically?
      // Requirement: remarks only if absent. We'll not auto-flip; instead clear remarks if present on save.
      row._dirty = true;
    }

    function buildItemsPayload() {
      var items = [];
      vm.rows.forEach(function (r) {
        if (r._dirty) {
          items.push({
            intCSID: r.intCSID,
            is_present: r.is_present,
            remarks: r.is_present === false ? (r.remarks || null) : null
          });
        }
      });
      return items;
    }

    function save() {
      if (!vm.selectedDate || !vm.selectedDate.id) {
        vm.error = 'Please select a date first';
        return;
      }
      var items = buildItemsPayload();
      if (!items.length) {
        vm.error = 'No changes to save';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.saveAttendance(vm.id, vm.selectedDate.id, items)
        .then(function (res) {
          if (res && res.success === false) {
            vm.error = res.message || 'Failed to save attendance';
            ToastService && ToastService.error && ToastService.error(vm.error);
            return;
          }
          vm.success = 'Attendance saved';
          ToastService && ToastService.success && ToastService.success('Attendance saved');
          // Reload details to clear dirty flags and reflect persisted values
          reloadDate();
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to save attendance';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Download per-date attendance template (.xlsx)
    function downloadTemplate() {
      if (!vm.selectedDate || !vm.selectedDate.id) {
        vm.error = 'Please select a date first';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      vm.loading = true;
      vm.error = null;
      vm.success = null;
      ClasslistsService.downloadAttendanceTemplate(vm.id, vm.selectedDate.id)
        .then(function (res) {
          var data = res && res.data ? res.data : null;
          var filename = (res && res.filename) ? res.filename : ('classlist-' + vm.id + '-attendance-' + vm.selectedDate.id + '-template.xlsx');
          if (!data) {
            vm.error = 'Failed to download template';
            ToastService && ToastService.error && ToastService.error(vm.error);
            return;
          }
          saveBlob(data, filename);
          vm.success = 'Template downloaded';
          ToastService && ToastService.success && ToastService.success('Template downloaded');
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to download template';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Handle file selection (accept File or DOM Event)
    function onImportFileChange(fileOrEvent) {
      try {
        if (fileOrEvent && fileOrEvent.name) {
          vm.importFile = fileOrEvent;
        } else if (fileOrEvent && fileOrEvent.target && fileOrEvent.target.files && fileOrEvent.target.files[0]) {
          vm.importFile = fileOrEvent.target.files[0];
        } else if (window.File && fileOrEvent instanceof window.File) {
          vm.importFile = fileOrEvent;
        }
      } catch (e) {}
    }

    // Upload .xlsx and apply to selected date
    function importAttendance() {
      if (!vm.selectedDate || !vm.selectedDate.id) {
        vm.error = 'Please select a date first';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      if (!vm.importFile) {
        vm.error = 'Please select an .xlsx file';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      vm.uploading = true;
      vm.error = null;
      vm.success = null;
      ClasslistsService.importAttendance(vm.id, vm.selectedDate.id, vm.importFile)
        .then(function (res) {
          if (res && res.success === false) {
            vm.error = res.message || 'Failed to import attendance';
            ToastService && ToastService.error && ToastService.error(vm.error);
            return;
          }
          vm.success = 'Attendance imported';
          ToastService && ToastService.success && ToastService.success('Attendance imported');
          vm.importFile = null;
          // Reload details to reflect changes
          reloadDate();
          // Also refresh summaries on dates list
          loadDates();
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to import attendance';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.uploading = false;
        });
    }

    // -------------------------
    // Matrix Template & Import
    // -------------------------

    function downloadMatrixTemplate() {
      if (!vm.matrixStart || !vm.matrixEnd) {
        vm.error = 'Please select a start and end date (YYYY-MM-DD)';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      vm.matrixDownloading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.downloadAttendanceMatrixTemplate(vm.id, vm.matrixStart, vm.matrixEnd, vm.matrixPeriod || 'midterm')
        .then(function (res) {
          var data = res && res.data ? res.data : null;
          var filename = (res && res.filename) ? res.filename : ('classlist-' + vm.id + '-attendance-matrix-' + (vm.matrixPeriod || 'midterm') + '.xlsx');
          if (!data) {
            vm.error = 'Failed to download matrix template';
            ToastService && ToastService.error && ToastService.error(vm.error);
            return;
          }
          saveBlob(data, filename);
          vm.success = 'Matrix template downloaded';
          ToastService && ToastService.success && ToastService.success('Matrix template downloaded');
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to download matrix template';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.matrixDownloading = false;
        });
    }

    function onMatrixFileChange(fileOrEvent) {
      try {
        vm.matrixFileLoading = true;
        if (fileOrEvent && fileOrEvent.name) {
          vm.matrixFile = fileOrEvent;
        } else if (fileOrEvent && fileOrEvent.target && fileOrEvent.target.files && fileOrEvent.target.files[0]) {
          vm.matrixFile = fileOrEvent.target.files[0];
        } else if (window.File && fileOrEvent instanceof window.File) {
          vm.matrixFile = fileOrEvent;
        }
      } catch (e) {
        // no-op
      } finally {
        // Brief loading indicator to reflect OS file dialog handoff and large file handle readiness
        $timeout(function () {
          vm.matrixFileLoading = false;
        }, 300);
      }
    }

    function importMatrix() {
      if (!vm.matrixFile) {
        vm.error = 'Please select an .xlsx file';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      vm.matrixUploading = true;
      vm.matrixFileLoading = false; // ensure selection loading is cleared once upload starts
      vm.error = null;
      vm.success = null;

      ClasslistsService.importAttendanceMatrix(vm.id, vm.matrixFile, vm.matrixPeriod || 'midterm')
        .then(function (res) {
          if (res && res.success === false) {
            vm.error = res.message || 'Failed to import attendance matrix';
            ToastService && ToastService.error && ToastService.error(vm.error);
            return;
          }
          vm.success = 'Attendance matrix imported';
          ToastService && ToastService.success && ToastService.success('Attendance matrix imported');
          vm.matrixFile = null;
          // Refresh dates list to reflect new dates created and summary counts
          loadDates();
          // Reload selected date, if any, to reflect changes
          reloadDate();
        })
        .catch(function (e) {
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to import attendance matrix';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.matrixUploading = false;
        });
    }

    function saveBlob(data, filename) {
      try {
        var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        var url = (window.URL || window.webkitURL).createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename || 'attendance.xlsx';
        document.body.appendChild(a);
        a.click();
        setTimeout(function () {
          document.body.removeChild(a);
          (window.URL || window.webkitURL).revokeObjectURL(url);
        }, 0);
      } catch (e) {
        // Fallback: open in new tab
        try {
          var blob2 = new Blob([data], { type: 'application/octet-stream' });
          var url2 = (window.URL || window.webkitURL).createObjectURL(blob2);
          window.open(url2, '_blank');
        } catch (err) {}
      }
    }

    // Kick off
    vm.init();
  }
})();
