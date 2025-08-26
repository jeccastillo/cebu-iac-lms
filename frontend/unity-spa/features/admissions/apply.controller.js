(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdmissionsApplyController', AdmissionsApplyController);

  AdmissionsApplyController.$inject = ['$http', '$location', 'APP_CONFIG'];

  function AdmissionsApplyController($http, $location, APP_CONFIG) {
    var vm = this;

    vm.loading = false;
    vm.programs = [];
    vm.error = null;

    vm.form = {
      // Basic Info
      first_name: '',
      middle_name: '',
      last_name: '',
      gender: '',
      date_of_birth: '',
      // Contact
      email: '',
      email_confirmation: '',
      mobile_number: '',
      mobile_number_confirmation: '',
      // Address
      address: '',
      city: '',
      province: '',
      country: '',
      // Program / Type
      student_type: '',
      type_id: '',
      campus: 'Makati'
    };

    vm.studentTypes = [
      'College - Freshmen iACADEMY',
      'College - Freshmen Other',
      '2nd - Degree iACADEMY',
      '2nd - Degree Other',
      'College - Transferee',
      'SHS - New',
      'SHS - Transferee'
    ];

    vm.genders = ['Male', 'Female'];

    vm.submit = submit;

    activate();

    function activate() {
      // Load active programs list to populate select
      vm.loading = true;
      $http.get(APP_CONFIG.API_BASE + '/programs')
        .then(function (resp) {
          // Expecting array of programs; normalize fields commonly used in codebase
          // Fallback mapping: id=intProgramID, title=strProgramDescription
          var data = Array.isArray(resp.data) ? resp.data : (resp.data.data || []);
          vm.programs = data.map(function (p) {
            return {
              id: p.intProgramID || p.id || p.program_id || '',
              title: p.strProgramDescription || p.title || p.name || ''
            };
          }).filter(function (p) { return !!p.id; });
        })
        .catch(function (err) {
          vm.error = 'Unable to load programs.';
          console.error('Programs load error', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function submit() {
      vm.error = null;

      if (!vm.form.email || vm.form.email !== vm.form.email_confirmation) {
        vm.error = 'Email address does not match confirmation.';
        return;
      }
      if (!vm.form.mobile_number || vm.form.mobile_number !== vm.form.mobile_number_confirmation) {
        vm.error = 'Mobile number does not match confirmation.';
        return;
      }
      if (!vm.form.type_id) {
        vm.error = 'Please select a program.';
        return;
      }
      if (!vm.form.student_type) {
        vm.error = 'Please select a student type.';
        return;
      }

      var payload = angular.copy(vm.form);

      vm.loading = true;
      $http.post(APP_CONFIG.API_BASE + '/admissions/student-info', payload)
        .then(function (resp) {
          if (resp.data && resp.data.success) {
            // Redirect to success page
            $location.path('/admissions/success');
          } else {
            vm.error = (resp.data && resp.data.message) || 'Submission failed.';
          }
        })
        .catch(function (err) {
          vm.error = (err.data && err.data.message) || 'Submission failed.';
          console.error('Submission error', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }
  }
})();
