(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('PaymentsResultController', PaymentsResultController);

  PaymentsResultController.$inject = ['$location', '$timeout'];
  function PaymentsResultController($location, $timeout) {
    var vm = this;

    vm.state = 'pending';
    vm.title = 'Processing Payment';
    vm.message = 'Please wait while we process your payment result...';

    vm.init = function () {
      var path = ($location && typeof $location.path === 'function') ? $location.path() : '';
      if (/\/payments\/success$/.test(path)) {
        vm.state = 'success';
        vm.title = 'Payment Successful';
        vm.message = 'Thank you. Your payment has been processed successfully.';
      } else if (/\/payments\/failure$/.test(path)) {
        vm.state = 'failure';
        vm.title = 'Payment Failed';
        vm.message = 'The payment was declined or failed. Please try again or use another payment method.';
      } else if (/\/payments\/cancel$/.test(path)) {
        vm.state = 'cancel';
        vm.title = 'Payment Cancelled';
        vm.message = 'The payment was cancelled or expired.';
      } else {
        vm.state = 'unknown';
        vm.title = 'Payment Status';
        vm.message = 'Unable to determine payment status.';
      }

      // Optional: auto-redirect from pending/unknown after a short delay
      if (vm.state === 'pending' || vm.state === 'unknown') {
        $timeout(function () {
          try { $location.path('/payments/checkout'); } catch (e) {}
        }, 2500);
      }
    };

    vm.backToCheckout = function () {
      try { $location.path('/payments/checkout'); } catch (e) {}
    };

    vm.init();
  }
})();
