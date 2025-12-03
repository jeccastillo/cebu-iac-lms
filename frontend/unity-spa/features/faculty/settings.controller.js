(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultySettingsController', FacultySettingsController);

  FacultySettingsController.$inject = ['StorageService', 'FacultyService', '$q', '$timeout'];
  function FacultySettingsController(StorageService, FacultyService, $q, $timeout) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    vm.title = 'Settings';

    // Profile model (tb_mas_faculty fields)
    vm.model = {
      strFirstname: '',
      strMiddlename: '',
      strLastname: '',
      strEmail: '',
      strMobileNumber: '',
      strUsername: vm.state ? (vm.state.username || '') : '',
      displayName: vm.state ? (vm.state.username || '') : '',
      notifyByEmail: true,
      theme: 'light'
    };

    // Password change model
    vm.password = {
      current: '',
      new: '',
      confirm: ''
    };

    // UI state
    vm.loading = { profile: false, password: false };
    vm.saving = false;
    vm.changingPassword = false;
    vm.message = '';
    vm.error = { profile: null, password: null };

    // Methods
    vm.save = save;
    vm.changePassword = changePassword;

    // Init
    loadProfile();

    function loadProfile() {
      vm.loading.profile = true;
      vm.error.profile = null;
      FacultyService.getMe()
        .then(function (data) {
          // Data may be raw faculty row or wrapped; FacultyService unwraps to row
          var f = data || {};
          vm.model.strFirstname = f.strFirstname || '';
          vm.model.strMiddlename = (typeof f.strMiddlename === 'string') ? f.strMiddlename : '';
          vm.model.strLastname = f.strLastname || '';
          vm.model.strEmail = f.strEmail || '';
          vm.model.strMobileNumber = f.strMobileNumber || '';
          if (!vm.model.strUsername && f.strUsername) vm.model.strUsername = f.strUsername;
          // Derive displayName for header if available
          var dn = '';
          if (vm.model.strLastname) dn += vm.model.strLastname;
          if (vm.model.strFirstname) dn += (dn ? ', ' : '') + vm.model.strFirstname;
          vm.model.displayName = dn || vm.model.strUsername || vm.model.displayName;
        })
        .catch(function (err) {
          vm.error.profile = extractError(err, 'Failed to load profile.');
        })
        .finally(function () {
          vm.loading.profile = false;
        });
    }

    function save() {
      vm.saving = true;
      vm.message = '';
      vm.error.profile = null;

      var payload = {
        strFirstname: (vm.model.strFirstname || '').trim(),
        strMiddlename: (vm.model.strMiddlename != null ? ('' + vm.model.strMiddlename).trim() : ''),
        strLastname: (vm.model.strLastname || '').trim(),
        strEmail: (vm.model.strEmail || '').trim(),
        strMobileNumber: (vm.model.strMobileNumber || '').trim()
        // strUsername: (vm.model.strUsername || '').trim() // enable if username editing is allowed
      };

      FacultyService.updateMe(payload)
        .then(function (data) {
          vm.message = 'Profile updated successfully.';
          // Refresh model from response to keep in sync
          var f = data || {};
          vm.model.strFirstname = f.strFirstname || payload.strFirstname;
          vm.model.strMiddlename = (typeof f.strMiddlename === 'string') ? f.strMiddlename : payload.strMiddlename;
          vm.model.strLastname = f.strLastname || payload.strLastname;
          vm.model.strEmail = f.strEmail || payload.strEmail;
          vm.model.strMobileNumber = f.strMobileNumber || payload.strMobileNumber;
          // Recompute display name
          var dn = '';
          if (vm.model.strLastname) dn += vm.model.strLastname;
          if (vm.model.strFirstname) dn += (dn ? ', ' : '') + vm.model.strFirstname;
          vm.model.displayName = dn || vm.model.strUsername || vm.model.displayName;
        })
        .catch(function (err) {
          vm.error.profile = extractError(err, 'Failed to update profile.');
        })
        .finally(function () {
          vm.saving = false;
        });
    }

    function changePassword() {
      vm.changingPassword = true;
      vm.error.password = null;
      vm.message = '';

      var cur = (vm.password.current || '').trim();
      var npw = (vm.password.new || '').trim();
      var cfm = (vm.password.confirm || '').trim();

      if (!cur || !npw || !cfm) {
        vm.error.password = 'Please fill in all password fields.';
        vm.changingPassword = false;
        return;
      }
      if (npw !== cfm) {
        vm.error.password = 'New password and confirmation do not match.';
        vm.changingPassword = false;
        return;
      }

      var payload = {
        current_password: cur,
        new_password: npw,
        new_password_confirmation: cfm
      };

      FacultyService.updatePassword(payload)
        .then(function () {
          vm.message = 'Password updated successfully.';
          vm.password.current = '';
          vm.password.new = '';
          vm.password.confirm = '';
        })
        .catch(function (err) {
          vm.error.password = extractError(err, 'Failed to update password.');
        })
        .finally(function () {
          vm.changingPassword = false;
        });
    }

    function extractError(err, fallback) {
      try {
        if (err && err.data) {
          if (typeof err.data.message === 'string' && err.data.message) return err.data.message;
          if (typeof err.data.error === 'string' && err.data.error) return err.data.error;
          if (err.data.errors && typeof err.data.errors === 'object') {
            // Flatten first error
            for (var k in err.data.errors) {
              if (Array.isArray(err.data.errors[k]) && err.data.errors[k].length) {
                return err.data.errors[k][0];
              }
            }
          }
        }
      } catch (e) {}
      return fallback || 'An error occurred.';
    }
  }

})();
