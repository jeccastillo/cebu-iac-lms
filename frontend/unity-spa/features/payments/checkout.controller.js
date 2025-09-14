(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('PaymentsCheckoutController', PaymentsCheckoutController);

  PaymentsCheckoutController.$inject = ['$window', '$timeout', 'PaymentsService'];
  function PaymentsCheckoutController($window, $timeout, PaymentsService) {
    var vm = this;

    vm.loading = false;
    vm.modes = [];
    vm.selectedMode = null;
    vm.message = '';
    vm.otc = { reference: null, instructions: null }; // for nonbank_otc

    // Minimal payload to demo flows; integrate with your existing student context as needed
    vm.form = {
      student_information_id: '',
      student_number: '',
      first_name: '',
      middle_name: '',
      last_name: '',
      email: '',
      contact_number: '',
      description: 'Tuition Payment',
      remarks: '',
      mode_of_payment_id: null,
      total_price_without_charge: 0,
      total_price_with_charge: 0,
      charge: 0,
      mailing_fee: 0,
      order_items: [
        // Example single item; user can adjust values
        { id: 1, title: 'Tuition Fee', qty: 1, price_default: 0, term: '', academic_year: '' }
      ],
      // BDO bill_to fields (auto-filled from name/email if left empty)
      bill_to_forename: '',
      bill_to_surname: '',
      bill_to_email: ''
    };

    vm.init = function () {
      vm.loading = true;
      PaymentsService.listPaymentModes({ is_active: true }).then(function (res) {
        var rows = res && res.data ? res.data : (Array.isArray(res) ? res : []);
        // Filter out Maya
        vm.modes = rows.filter(function (m) {
          return (m && m.pmethod !== 'maya_pay');
        });
      }).finally(function () {
        vm.loading = false;
      });
    };

    vm.onModeChange = function () {
      vm.otc = { reference: null, instructions: null };
      vm.message = '';
      if (!vm.selectedMode) return;
      vm.form.mode_of_payment_id = vm.selectedMode.id;

      // Example: for BDO pay, ensure charge is 0 in UI and compute total accordingly
      if (vm.selectedMode.pmethod === 'bdo_pay' || vm.selectedMode.pmethod === 'maxx_payment') {
        vm.form.charge = 0;
      } else {
        // For percentage charge types, UI uses provided charge; backend will verify parity
        vm.form.charge = (typeof vm.selectedMode.charge === 'number') ? vm.selectedMode.charge : 0;
      }
      vm.recomputeTotals();
    };

    vm.recomputeTotals = function () {
      var subtotal = 0;
      try {
        vm.form.order_items.forEach(function (it) {
          var qty = parseInt(it.qty || 0, 10);
          var price = parseFloat(it.price_default || 0);
          if (!isFinite(qty) || qty < 0) qty = 0;
          if (!isFinite(price) || price < 0) price = 0;
          subtotal += (qty * price);
        });
      } catch (e) {}
      subtotal = round2(subtotal);
      vm.form.total_price_without_charge = subtotal;

      // Compute percentage charges client-side only for display; backend validates
      var charge = parseFloat(vm.form.charge || 0);
      var computedCharge = charge;
      if (vm.selectedMode && vm.selectedMode.type === 'percentage') {
        computedCharge = round2((charge / 100) * subtotal);
        if (computedCharge < 28) computedCharge = 28.00; // parity with backend min-charge rule
      }
      var mailing = round2(parseFloat(vm.form.mailing_fee || 0));
      var total = round2(subtotal + mailing + (vm.selectedMode && vm.selectedMode.type === 'percentage' ? computedCharge : charge));
      vm.form.total_price_with_charge = total;
    };

    vm.submit = function () {
      vm.message = '';
      vm.otc = { reference: null, instructions: null };
      if (!vm.selectedMode) {
        vm.message = 'Please select a payment mode.';
        return;
      }
      // If BDO, ensure bill_to is auto-filled when empty
      if (vm.selectedMode.pmethod === 'bdo_pay') {
        if (!vm.form.bill_to_email) vm.form.bill_to_email = vm.form.email;
        if (!vm.form.bill_to_forename) vm.form.bill_to_forename = vm.form.first_name;
        if (!vm.form.bill_to_surname) vm.form.bill_to_surname = vm.form.last_name;
      }

      vm.loading = true;
      PaymentsService.checkout(angular.copy(vm.form))
        .then(function (res) {
          if (!res || res.success !== true) {
            vm.message = (res && res.message) ? res.message : 'Payment request failed.';
            return;
          }
          var gateway = res.gateway;

          if (gateway === 'bdo_pay') {
            // Auto-submit to BDO via hidden form
            var action = res.action_url;
            var fields = res.post_data || {};
            if (!action || !fields || !fields.signature) {
              vm.message = 'Invalid BDO response.';
              return;
            }
            autoSubmitForm(action, fields);
            return;
          }

          if (gateway === 'paynamics') {
            // onlinebanktransfer/wallet => redirect to payment_action_info
            // nonbank_otc => show reference/instructions
            if (res.payment_link && typeof res.payment_link === 'string' && !res.message) {
              // Redirect flow
              $window.location.href = res.payment_link;
              return;
            }
            // OTC flow (display reference/instructions if available)
            if (res && res.data && res.data.direct_otc_info && res.data.direct_otc_info[0]) {
              vm.otc.reference = res.data.direct_otc_info[0].pay_reference || null;
              vm.otc.instructions = res.data.direct_otc_info[0].pay_instructions || null;
              vm.message = 'Please check your email for payment instructions.';
              return;
            }
            // Fallback
            if (res.payment_link) {
              vm.message = 'Open the provided payment link to continue.';
            } else {
              vm.message = 'Payment created. Please check your email for next steps.';
            }
            return;
          }

          if (gateway === 'maxx_payment') {
            if (res.payment_link) {
              $window.location.href = res.payment_link;
              return;
            }
            vm.message = 'Invalid MaxxPayment response.';
            return;
          }

          vm.message = 'Unsupported payment method.';
        })
        .catch(function (err) {
          vm.message = (err && err.message) ? err.message : 'Request failed.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    function autoSubmitForm(actionUrl, fields) {
      var form = document.createElement('form');
      form.method = 'POST';
      form.action = actionUrl;
      form.style.display = 'none';

      Object.keys(fields).forEach(function (k) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = fields[k];
        form.appendChild(input);
      });

      document.body.appendChild(form);
      $timeout(function () { form.submit(); }, 50);
    }

    function round2(n) {
      var x = parseFloat(n || 0);
      if (!isFinite(x)) x = 0;
      return Math.round(x * 100) / 100;
    }

    vm.init();
  }
})();
