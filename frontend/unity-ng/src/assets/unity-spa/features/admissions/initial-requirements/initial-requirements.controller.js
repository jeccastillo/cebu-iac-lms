(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('InitialRequirementsController', InitialRequirementsController);

  InitialRequirementsController.$inject = ['$routeParams', '$location', 'InitialRequirementsService', 'Upload', 'APP_CONFIG', '$timeout'];
  function InitialRequirementsController($routeParams, $location, InitialRequirementsService, Upload, APP_CONFIG, $timeout) {
    var vm = this;

    vm.hash = $routeParams.hash;
    vm.loading = true;
    vm.student = null;
    vm.items = [];
    vm.uploading = {};
    vm.progress = {};
    vm.accept = 'application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,image/*';
    vm.error = null;
    vm.invalid = false;

    // Support email + copy helper
    vm.supportEmail = 'admissions@iacademy.edu.ph';
    try {
      vm.returnUrl = ($location.search() && $location.search().return) || null;
    } catch (e) {
      vm.returnUrl = null;
    }
    vm.copySupportEmail = function copySupportEmail() {
      var email = vm.supportEmail || '';
      if (!email) return;
      if (window && window.navigator && navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(email).then(function () {
          try { Swal.fire('Copied', 'Support email copied to clipboard.', 'success'); } catch (e) {}
        }).catch(function () {
          try { Swal.fire('Copied', 'Support email copied to clipboard.', 'success'); } catch (e) {}
        });
      } else {
        try {
          var ta = document.createElement('textarea');
          ta.value = email;
          ta.setAttribute('readonly', '');
          ta.style.position = 'absolute';
          ta.style.left = '-9999px';
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
          try { Swal.fire('Copied', 'Support email copied to clipboard.', 'success'); } catch (e) {}
        } catch (err) {
          alert('Copy not supported on this browser.');
        }
      }
    };

    vm.reload = reload;
    vm.onSelect = onSelect;
    vm.onDrop = onDrop;
    vm.replaceFile = replaceFile;
    vm.viewFile = viewFile;

    activate();

    function activate() {
      reload();
    }

    function reload() {
      vm.loading = true;
      InitialRequirementsService.getList(vm.hash)
        .then(function (res) {
          // { success: true, data: { student: {...}, requirements: [...] } }
          if (res && res.success && res.data) {
            vm.student = res.data.student || null;
            vm.items = res.data.requirements || [];
          } else if (res && res.data) {
            vm.student = res.data.student || null;
            vm.items = res.data.requirements || [];
          }
          vm.invalid = false;
          vm.error = null;
        })
        .catch(function (err) {
          console.error('Failed to load initial requirements', err);
          var status = err && err.status;
          var message = (err && err.data && (err.data.message || err.data.error)) || 'Failed to load requirements';
          if (status === 404) {
            vm.invalid = true;
          } else {
            vm.invalid = false;
            vm.error = message;
          }
          try {
            Swal.fire('Error', message, 'error');
          } catch (e) {
            alert(message);
          }
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function onSelect(item, file) {
      if (!file) return;

      // Client-side validation (basic)
      var ok = validateFile(file);
      if (!ok) {
        try {
          Swal.fire('Invalid File', 'Only PDF, Excel (xls, xlsx, csv), and image files are allowed. Max 10MB.', 'warning');
        } catch (e) {
          alert('Only PDF, Excel (xls, xlsx, csv), and image files are allowed. Max 10MB.');
        }
        return;
      }

      uploadFile(item, file);
    }

    function onDrop(item, files) {
      if (!files || !files.length) return;
      var file = files[0];

      var ok = validateFile(file);
      if (!ok) {
        try {
          Swal.fire('Invalid File', 'Only PDF, Excel (xls, xlsx, csv), and image files are allowed. Max 10MB.', 'warning');
        } catch (e) {
          alert('Only PDF, Excel (xls, xlsx, csv), and image files are allowed. Max 10MB.');
        }
        return;
      }

      uploadFile(item, file);
    }

    function replaceFile(item) {
      // Trigger a hidden file input for this item
      var input = document.getElementById('file-input-' + item.app_req_id);
      if (input) {
        input.click();
      }
    }

    function uploadFile(item, file) {
      vm.uploading[item.app_req_id] = true;
      vm.progress[item.app_req_id] = 0;

      var url = APP_CONFIG.API_BASE + '/public/initial-requirements/' + encodeURIComponent(vm.hash) + '/upload/' + encodeURIComponent(item.app_req_id);

      Upload.upload({
        url: url,
        data: { file: file }
      }).then(function (resp) {
        var data = (resp && resp.data) ? resp.data : resp;
        if (data && data.success && data.data) {
          // Update item in place
          item.submitted_status = data.data.submitted_status;
          item.file_link = data.data.file_link;
          try {
            Swal.fire('Uploaded', 'File uploaded successfully.', 'success');
          } catch (e) {
            // ignore
          }
        } else {
          try {
            Swal.fire('Error', (data && data.message) || 'Upload failed', 'error');
          } catch (e) {
            alert((data && data.message) || 'Upload failed');
          }
        }
      }, function (err) {
        console.error('Upload error', err);
        var msg = (err && err.data && (err.data.message || (err.data.errors && err.data.errors.file && err.data.errors.file[0])) ) || 'Upload failed';
        try {
          Swal.fire('Error', msg, 'error');
        } catch (e) {
          alert(msg);
        }
      }, function (evt) {
        // progress notify
        var progressPercentage = parseInt(100.0 * evt.loaded / evt.total, 10);
        vm.progress[item.app_req_id] = progressPercentage;
      }).finally(function () {
        $timeout(function () {
          vm.uploading[item.app_req_id] = false;
        }, 200);
      });
    }

    function validateFile(file) {
      // 10MB max
      var maxBytes = 10 * 1024 * 1024;
      if (file.size > maxBytes) return false;

      var allowed = [
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv'
      ];
      if ((file.type || '').indexOf('image/') === 0) return true;
      if (allowed.indexOf(file.type) !== -1) return true;

      // Fallback by extension if type is empty or generic
      var name = (file.name || '').toLowerCase();
      var okExt = ['.pdf', '.xls', '.xlsx', '.csv', '.jpg', '.jpeg', '.png', '.gif', '.webp'];
      for (var i = 0; i < okExt.length; i++) {
        if (name.endsWith(okExt[i])) return true;
      }
      return false;
    }

    function viewFile(item) {
      if (!item || !item.file_link) return;
      window.open(item.file_link, '_blank');
    }
  }
})();
