(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('RegistrationViewerController', RegistrationViewerController);

  RegistrationViewerController.$inject = [
    '$routeParams',
    '$location',
    '$http',
    '$q',
    '$rootScope',
    '$scope',
    '$timeout',
    'APP_CONFIG',
    'StorageService',
    'UnityService',
    'TermService',
    'TuitionYearsService',
    'StudentsService'
  ];
  function RegistrationViewerController($routeParams, $location, $http, $q, $rootScope, $scope, $timeout, APP_CONFIG, StorageService, UnityService, TermService, TuitionYearsService, StudentsService) {
    var vm = this;

    // Auth guard
    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Identifiers
    vm.id = parseInt($routeParams.id, 10);
    vm.sn = null; // student_number resolved from student/{id}
    vm.term = null; // selected term (intID)

    // Student dropdown (for quick navigation across students)
    vm.students = [];
    vm.selectedStudentId = vm.id || null;
    vm.onStudentSelected = function () {
      try {
        var targetId = vm.selectedStudentId != null ? parseInt(vm.selectedStudentId, 10) : null;
        if (targetId && targetId !== vm.id) {
          $location.path('/registrar/registration/' + targetId);
        }
      } catch (e) {}
    };

    // API base
    var API = APP_CONFIG.API_BASE;

    // UI state
    vm.loading = {
      bootstrap: false,
      student: false,
      registration: false,
      tuition: false,
      ledger: false,
      records: false,
      payments: false,
      update: false,
      tuitionYears: false
    };
    vm.error = {
      student: null,
      registration: null,
      tuition: null,
      ledger: null,
      records: null,
      payments: null,
      update: null
    };

    // Data models
    vm.student = null;
    vm.registrationResp = null; // {success, data:{exists, registration}}
    vm.registration = null;     // registration object or null
    vm.tuition = null;          // TuitionBreakdownResource payload (data or direct)
    vm.tuitionSaved = null;  // Saved tuition snapshot row (if any)
    vm.ledger = null;           // { student_number, transactions, ... } or ledger array shape
    vm.paymentDetails = null;   // Payment details for selected term/registration
    vm.records = null;          // records or terms shape (see DataFetcherService)
    vm.tuitionYearOptions = []; // dropdown options for Tuition Year
    vm.tuitionYearsLoaded = false;
    vm.meta = {
      amount_paid: 0,
      remaining_amount: 0,
      tuition_source: 'computed' // 'saved' when using snapshot
    };
    // Track last loaded params to avoid duplicate API calls
    vm._last = {
      registration: null,
      tuition: null,
      records: null,
      payments: null
    };
    // Track last handled termChanged to suppress duplicate bursts
    vm._lastTermEvent = { term: null, ts: 0 };
    // Selected tuition amount based on registered payment type (full vs partial)
    vm.selectedTuitionAmount = null;

    // Controls (editable fields)
    vm.edit = {
      paymentType: null,
      tuition_year: null,
      allow_enroll: null,
      downpayment: null,
      intROG: null
    };

    // Helpers
    vm.currency = function (num) {
      var n = parseFloat(num || 0);
      var s = n.toFixed(2);
      return s.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    // UI: Tuition Breakdown modal state and helpers
    vm.ui = vm.ui || { showTuitionModal: false };

    vm.openTuitionModal = function () {
      vm.ui.showTuitionModal = true;
    };

    vm.closeTuitionModal = function () {
      vm.ui.showTuitionModal = false;
    };

    // Getter: Return the effective tuition payload for display (prefer saved snapshot when present)
    vm.tuitionPayload = function () {
      try {
        if (vm.tuitionSaved && vm.tuitionSaved.payload) return vm.tuitionSaved.payload;
        return vm.tuition || null;
      } catch (e) {
        return vm.tuition || null;
      }
    };

    // Compute preview figures for DP30 and DP50 scenarios
    vm.installmentPreview = function (payload) {
      try {
        var p = payload || vm.tuitionPayload() || {};
        var s = p.summary || {};
        function num(x) { var v = parseFloat(x); return isFinite(v) ? v : 0; }
        var tuition = num(s.tuition);
        var misc = num(s.misc_total);
        var lab = num(s.lab_total);
        var additional = num(s.additional_total);
        var scholarships = num(s.scholarships_total);
        var discounts = num(s.discounts_total);

        function compute(mult) {
          var tuitionNew = tuition * mult;
          var miscNew = misc * mult;
          var labNew = lab;
          var additionalNew = additional;
          var totalNew = (tuitionNew + miscNew + labNew + additionalNew) - scholarships - discounts;
          return {
            tuitionNew: tuitionNew,
            miscNew: miscNew,
            labNew: labNew,
            additionalNew: additionalNew,
            scholarships: scholarships,
            discounts: discounts,
            totalNew: totalNew
          };
        }

        return {
          dp30: compute(1.15),
          dp50: compute(1.09)
        };
      } catch (e) {
        return { dp30: null, dp50: null };
      }
    };

    // Recompute selected tuition amount and remaining from current data (tuition/tuitionSaved + paymentType)
    vm.refreshTuitionSummary = function () {
      try {
        var paymentType = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
        var isPartial = paymentType === 'partial';

        // Prefer saved payload when present
        var source = null;
        var total = null;
        var totalInst = null;

        if (vm.tuitionSaved && vm.tuitionSaved.payload) {
          var sd = vm.tuitionSaved.payload || {};
          var ssum = sd.summary || {};
          var sinst = (ssum.installments || {});
          function nS(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }

          var sFullCandidates = [sd.total, ssum.total_due, ssum.total, ssum.grand_total];
          var sInstCandidates = [sd.total_installment, (sinst ? sinst.total_installment : null), (ssum ? ssum.total_installment : null), (sd.meta && sd.meta.installments ? sd.meta.installments.total_installment : null)];

          for (var i = 0; i < sFullCandidates.length; i++) { var fv = nS(sFullCandidates[i]); if (fv !== null) { total = fv; break; } }
          for (var j = 0; j < sInstCandidates.length; j++) { var iv = nS(sInstCandidates[j]); if (iv !== null) { totalInst = iv; break; } }

          source = 'saved';
        } else if (vm.tuition) {
          var td = vm.tuition || {};
          var summary = td.summary || {};
          var installments = summary.installments || {};
          function n(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }

          var fullCandidates = [td.total, summary.total_due, summary.total, summary.grand_total];
          var instCandidates = [td.total_installment, (installments ? installments.total_installment : null), (summary ? summary.total_installment : null), (td.meta && td.meta.installments ? td.meta.installments.total_installment : null)];

          for (var i2 = 0; i2 < fullCandidates.length; i2++) { var fv2 = n(fullCandidates[i2]); if (fv2 !== null) { total = fv2; break; } }
          for (var j2 = 0; j2 < instCandidates.length; j2++) { var iv2 = n(instCandidates[j2]); if (iv2 !== null) { totalInst = iv2; break; } }

          source = 'computed';
        }

        if (total !== null || totalInst !== null) {
          var sel = isPartial ? (totalInst != null ? totalInst : 0) : (total != null ? total : 0);
          if (isFinite(sel)) {
            vm.selectedTuitionAmount = sel;
            if (!vm.meta) vm.meta = {};
            if (source) vm.meta.tuition_source = source;

            var remain = (vm.selectedTuitionAmount || 0) - (vm.meta.amount_paid || 0);
            vm.meta.remaining_amount = isFinite(remain) ? remain : 0;
          }
        }
      } catch (e) {
        // no-op
      }
    };

    var _termChangePromise = null;
    vm.onTermChange = function () {
      // Debounce to prevent burst-triggered duplicate loads
      if (_termChangePromise) {
        $timeout.cancel(_termChangePromise);
      }
      _termChangePromise = $timeout(function () {
        _termChangePromise = null;
        if (!vm.sn || !vm.term) return;
        vm.loadRegistration()
          .then(vm.loadTuition)
          .then(vm.loadPaymentDetails)
          .then(vm.loadRecords)
          .catch(function () { /* errors captured per-call */ });
      }, 150);
    };

    // Build human-readable term label from global term object
    function buildTermLabel(t) {
      try {
        var parts = [];
        if (t.term_student_type) parts.push(t.term_student_type);
        if (t.enumSem) parts.push(t.enumSem);
        if (t.term_label) parts.push(t.term_label);
        if (t.strYearStart && t.strYearEnd) parts.push(t.strYearStart + '-' + t.strYearEnd);
        var label = parts.join(' ').replace(/\s+/g, ' ').trim();
        return label || (t.label || ('SY ' + (t.syid || t.intID || '')));
      } catch (e) {
        return t && (t.label || ('SY ' + (t.syid || t.intID || ''))) || '';
      }
    }

    // Sync controller term with global TermService selection
    function applyGlobalTerm() {
      try {
        var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
        if (sel && sel.intID) {
          var parsed = parseInt(sel.intID, 10);
          vm.term = isFinite(parsed) ? parsed : sel.intID;
          vm.termLabel = buildTermLabel(sel);
        } else {
          vm.term = null;
          vm.termLabel = '';
        }
      } catch (e) {
        vm.term = null;
        vm.termLabel = '';
      }
    }

    // Listen for global term changes from the sidebar and reload page data (with cleanup and bootstrap guard)
    var unbindTermChanged = $rootScope.$on('termChanged', function () {
      if (vm._inBootstrap) return; // ignore during bootstrap to prevent duplicate loads

      // Capture current and updated term
      var prevTerm = vm.term;
      applyGlobalTerm();
      var newTerm = vm.term;

      // Guard: ignore duplicate term events within a suppression window
      try {
        var now = Date.now();
        if (vm._lastTermEvent && vm._lastTermEvent.term === newTerm && (now - vm._lastTermEvent.ts) < 1000) {
          return;
        }
        vm._lastTermEvent = { term: newTerm, ts: now };
      } catch (e) {
        // noop
      }

      // If term didn't actually change, skip noisy triggers
      if (prevTerm === newTerm) {
        return;
      }

      vm.onTermChange();
    });
    $scope.$on('$destroy', function () {
      if (typeof unbindTermChanged === 'function') {
        unbindTermChanged();
      }
    });

    // Data loaders
    // Load all students for dropdown (cached by StudentsService)
    vm.loadStudents = function () {
      return StudentsService.listAll().then(function (list) {
        vm.students = Array.isArray(list) ? list : [];
        // Initialize dropdown selection to current route id when first loading
        if (!vm.selectedStudentId && vm.id) {
          vm.selectedStudentId = vm.id;
        }
      }).catch(function () {
        vm.students = vm.students || [];
      });
    };

    vm.loadStudent = function () {
      vm.loading.student = true;
      vm.error.student = null;
      return $http.get(API + '/students/' + vm.id)
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          var obj = null;
          if (data && data.data) obj = data.data;
          else obj = data;
          vm.student = obj || null;

          // Resolve student_number from possible shapes
          if (vm.student) {
            vm.sn = vm.student.student_number || vm.student.strStudentNumber || vm.student.strstudentnumber || null;
            if (vm.student.slug && !vm.student.student_number && !vm.student.strStudentNumber) {
              // keep slug if needed in future; not required by current endpoints
            }
          }

          if (!vm.sn) {
            vm.error.student = 'Missing student_number from API response.';
          }
        })
        .catch(function () {
          vm.error.student = 'Failed to load student.';
        })
        .finally(function () {
          vm.loading.student = false;
        });
    };


    // Load Tuition Years options for dropdown
    vm.loadTuitionYears = function () {
      // Avoid duplicate loads; tuition year options are global/static enough for this screen
      if (vm.tuitionYearsLoaded) return $q.when(vm.tuitionYearOptions);
      if (vm.loading.tuitionYears) return $q.when();

      vm.loading.tuitionYears = true;
      return TuitionYearsService.list({})
        .then(function (res) {
          var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.tuitionYearOptions = (items || []).map(function (r) {
            var id = r.intID || r.id;
            var label = r.year || r.strLabel || ('Tuition Year ' + id);
            var nid = parseInt(id, 10);
            return { id: isNaN(nid) ? id : nid, label: label };
          });
          vm.tuitionYearsLoaded = true;
        })
        .catch(function () {
          // Keep any previously loaded options; only default to [] when nothing exists
          if (!Array.isArray(vm.tuitionYearOptions) || vm.tuitionYearOptions.length === 0) {
            vm.tuitionYearOptions = [];
          }
        })
        .finally(function () {
          vm.loading.tuitionYears = false;
        });
    };

    vm.loadRegistration = function (force) {
      if (!vm.sn || !vm.term) return $q.when();
      // Avoid duplicate loads with same params unless forced
      if (!force && vm._last && vm._last.registration && vm._last.registration.sn === vm.sn && vm._last.registration.term === vm.term) {
        return $q.when();
      }
      if (vm.loading.registration) return $q.when();
      vm.loading.registration = true;
      vm.error.registration = null;

      // Use UnityService to include X-Faculty-ID header automatically
      return UnityService.getRegistration(vm.sn, vm.term)
        .then(function (data) {
          // UnityService._unwrap returns the body (resp.data)
          vm.registrationResp = data || null;
          var exists = data && data.data && data.data.exists === true;
          vm.registration = exists ? (data.data.registration || null) : null;

          // Prefer the student's registered term when no explicit term is pre-selected
          try {
            if (!vm.term && vm.registration && vm.registration.intAYID) {
              var t = parseInt(vm.registration.intAYID, 10);
              if (!isNaN(t) && t > 0) {
                vm.term = t;
              }
            }
          } catch (e) {}

          // Bind editable fields snapshot
          vm.edit.paymentType = vm.registration ? (vm.registration.paymentType || null) : null;
          vm.edit.tuition_year = vm.registration ? (vm.registration.tuition_year || null) : null;
          vm.edit.allow_enroll = vm.registration ? (vm.registration.allow_enroll != null ? parseInt(vm.registration.allow_enroll, 10) : null) : null;
          vm.edit.downpayment = vm.registration ? (vm.registration.downpayment != null ? parseInt(vm.registration.downpayment, 10) : null) : null;
          vm.edit.intROG = vm.registration ? (vm.registration.intROG != null ? parseInt(vm.registration.intROG, 10) : null) : null;
          // Mark last loaded params to prevent duplicate API calls
          vm._last.registration = { sn: vm.sn, term: vm.term };
        })
        .catch(function () {
          vm.error.registration = 'Failed to load registration.';
        })
        .finally(function () {
          vm.loading.registration = false;
        });
    };

    vm.loadTuition = function (force) {
      if (!vm.sn || !vm.term) return $q.when();
      // Avoid duplicate loads with same params unless forced
      if (!force && vm._last && vm._last.tuition && vm._last.tuition.sn === vm.sn && vm._last.tuition.term === vm.term) {
        return $q.when();
      }
      if (vm.loading.tuition) return $q.when();
      vm.loading.tuition = true;
      vm.error.tuition = null;

      // Build params (Laravel ignores tuition_year currently; registration context is used server-side)
      var params = { student_number: vm.sn, term: vm.term };

      // Reset saved snapshot state and default source
      vm.tuitionSaved = null;
      vm.meta.tuition_source = 'computed';

      // First: get computed tuition breakdown
      return $http.get(API + '/tuition/compute', { params: params })
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          vm.tuition = (data && data.data) ? data.data : (data || null);

          // Reset meta, then compute from API whenever available
          vm.meta.amount_paid = null; // initialize as null so ledger fallback can decide whether to compute sum
          vm.meta.remaining_amount = 0;
          vm.selectedTuitionAmount = null;

          try {
            var td = vm.tuition || {};
            // Prefer API-provided paid/remaining figures if present
            if (td.meta) {
              if (td.meta.amount_paid != null) vm.meta.amount_paid = parseFloat(td.meta.amount_paid) || 0;
              // Some payloads may carry 'remaining' or 'remaining_amount'
              var remainingApi = td.meta.remaining != null ? td.meta.remaining : td.meta.remaining_amount;
              if (remainingApi != null) vm.meta.remaining_amount = parseFloat(remainingApi) || 0;
            }

            // Normalize TuitionBreakdownResource shape (summary/installments) to flat totals for UI compatibility
            var summary = td.summary || {};
            var installments = summary.installments || {};
            function _num(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }
            // Candidates for full total
            var fullCandidates = [
              td.total,
              summary.total_due,
              summary.total,
              summary.grand_total
            ];
            // Candidates for installment total
            var instCandidates = [
              td.total_installment,
              (installments ? installments.total_installment : null),
              (summary ? summary.total_installment : null),
              (td.meta && td.meta.installments ? td.meta.installments.total_installment : null)
            ];
            var full = null;
            for (var i = 0; i < fullCandidates.length; i++) { var fv = _num(fullCandidates[i]); if (fv !== null) { full = fv; break; } }
            var inst = null;
            for (var j = 0; j < instCandidates.length; j++) { var iv = _num(instCandidates[j]); if (iv !== null) { inst = iv; break; } }
            td.total = full || 0;
            td.total_installment = inst || 0;

            // Determine registered payment type: prefer saved registration over edit snapshot
            var paymentType = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
            var isPartial = paymentType === 'partial';

            // Choose correct tuition total based on registered payment type (computed values)
            var computedSelected = isPartial
              ? (td.total_installment != null ? parseFloat(td.total_installment) : 0)
              : (td.total != null ? parseFloat(td.total) : 0);

            vm.selectedTuitionAmount = computedSelected || 0;

            // If API did not provide remaining, fallback to client computation:
            // remaining = selected tuition - amount_paid (using ledger or API-paid if available)
            if (!vm.meta.remaining_amount) {
              vm.meta.remaining_amount = (vm.selectedTuitionAmount || 0) - (vm.meta.amount_paid || 0);
              if (!isFinite(vm.meta.remaining_amount)) vm.meta.remaining_amount = 0;
            }
          } catch (e) {}
        })
        .catch(function () {
          // Keep error note for compute, but we will still attempt to fetch saved snapshot
          vm.error.tuition = 'Failed to load tuition breakdown.';
        })
        .then(function () {
          // Second: attempt to fetch saved tuition snapshot via UnityService (adds admin headers)
          return UnityService.tuitionSaved(params);
        })
        .then(function (body) {
          var payload = body && body.data ? body.data : null;
          var exists = payload && payload.exists === true;
          var saved = exists ? (payload.saved || null) : null;

          if (saved && saved.payload) {
            vm.tuitionSaved = saved;

            try {
              // Determine registered payment type again (authoritative)
              var paymentType = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
              var isPartial = paymentType === 'partial';

              var sd = saved.payload || {};
              // Normalize saved payload shape to flat totals
              var ssum = sd.summary || {};
              var sinst = (ssum.installments || {});
              function _numS(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }
              var sFullCandidates = [
                sd.total,
                ssum.total_due,
                ssum.total,
                ssum.grand_total
              ];
              var sInstCandidates = [
                sd.total_installment,
                (sinst ? sinst.total_installment : null),
                (ssum ? ssum.total_installment : null),
                (sd.meta && sd.meta.installments ? sd.meta.installments.total_installment : null)
              ];
              var sFull = null;
              for (var si = 0; si < sFullCandidates.length; si++) { var sfv = _numS(sFullCandidates[si]); if (sfv !== null) { sFull = sfv; break; } }
              var sInst = null;
              for (var sj = 0; sj < sInstCandidates.length; sj++) { var siv = _numS(sInstCandidates[sj]); if (siv !== null) { sInst = siv; break; } }
              sd.total = sFull || 0;
              sd.total_installment = sInst || 0;

              var selectedSaved = isPartial
                ? (sd.total_installment != null ? parseFloat(sd.total_installment) : 0)
                : (sd.total != null ? parseFloat(sd.total) : 0);

              if (isFinite(selectedSaved)) {
                vm.selectedTuitionAmount = selectedSaved;
                vm.meta.tuition_source = 'saved';

                // Recompute remaining based on saved snapshot and current amount_paid
                vm.meta.remaining_amount = (vm.selectedTuitionAmount || 0) - (vm.meta.amount_paid || 0);
                if (!isFinite(vm.meta.remaining_amount)) vm.meta.remaining_amount = 0;
              }
            } catch (e) {}
          }
        })
        .catch(function () {
          // Ignore saved snapshot fetch failures; computed values already set if available
        })
        .finally(function () {
          // Mark last loaded params after finishing any tuition requests
          if (vm.sn && vm.term) {
            vm._last.tuition = { sn: vm.sn, term: vm.term };
          }
          vm.loading.tuition = false;
        });
    };

    vm.loadLedger = function () {
      if (!vm.sn) return $q.when();
      vm.loading.ledger = true;
      vm.error.ledger = null;
      return $http.post(API + '/student/ledger', { student_number: vm.sn })
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          vm.ledger = (data && data.data) ? data.data : (data || null);

          // compute paid sum; prefer API-provided filtered amounts when available
          try {
            // Always compute transactions sum for comparison UI
            var txs = vm.ledger && vm.ledger.transactions ? vm.ledger.transactions : [];
            var sum = 0;
            for (var i = 0; i < txs.length; i++) {
              var t = txs[i];
              var amt = (t && t.amount != null) ? parseFloat(t.amount) : 0;
              sum += isFinite(amt) ? amt : 0;
            }
            vm.transactionsTotal = sum;

            // If API returns meta.amount_paid specific to current registration/term, use it.
            var apiPaid = vm.ledger && vm.ledger.meta && vm.ledger.meta.amount_paid != null
              ? parseFloat(vm.ledger.meta.amount_paid) : null;

            if (apiPaid != null && isFinite(apiPaid)) {
              vm.meta.amount_paid = apiPaid;
            } else if (vm.meta.amount_paid == null) {
              // Safeguard: do not override amount_paid if tuition/compute already provided it
              vm.meta.amount_paid = sum;
            }
          } catch (e) {}
        })
        .catch(function () {
          vm.error.ledger = 'Failed to load ledger.';
        })
        .finally(function () {
          vm.loading.ledger = false;
        });
    };

    // Payment Details loader (for selected term/registration)
    vm.loadPaymentDetails = function (force) {
      if (!vm.sn || !vm.term) return $q.when();
      // Avoid duplicate loads with same params unless forced
      if (!force && vm._last && vm._last.payments && vm._last.payments.sn === vm.sn && vm._last.payments.term === vm.term) {
        return $q.when();
      }
      if (vm.loading.payments) return $q.when();
      vm.loading.payments = true;
      vm.error.payments = null;

      // Prefer student_id -> payment_details.student_information_id matching; fallback to student_number
      var params = { term: vm.term };
      if (vm.student && (vm.student.id != null)) {
        params.student_id = vm.student.id;
      } else if (vm.sn) {
        params.student_number = vm.sn;
      }
      return UnityService.paymentDetails(params)
        .then(function (body) {
          // UnityService._unwrap returns body, but keep both shapes safe
          var data = body && body.data ? body.data : body;
          vm.paymentDetails = data || { items: [], meta: {} };

          try {
            var total = (vm.paymentDetails && vm.paymentDetails.meta && vm.paymentDetails.meta.total_paid_filtered != null)
              ? parseFloat(vm.paymentDetails.meta.total_paid_filtered)
              : null;
            vm.paymentDetailsTotal = isFinite(total) ? total : 0;

            // If tuition/ledger did not provide an amount_paid, use filtered payment_details total
            if (vm.meta && (vm.meta.amount_paid == null || !isFinite(vm.meta.amount_paid))) {
              vm.meta.amount_paid = vm.paymentDetailsTotal || 0;
            }
            // Refresh remaining computation with latest paid
            if (typeof vm.refreshTuitionSummary === 'function') {
              vm.refreshTuitionSummary();
            }
          } catch (e) {}
          // Mark last
          vm._last.payments = { sn: vm.sn, term: vm.term };
        })
        .catch(function () {
          vm.error.payments = 'Failed to load payment details.';
        })
        .finally(function () {
          vm.loading.payments = false;
        });
    };

    vm.loadRecords = function (force) {
      if (!vm.sn) return $q.when();
      // Avoid duplicate loads with same params (term may be undefined when loading all)
      var termKey = vm.term || null;
      if (!force && vm._last && vm._last.records && vm._last.records.sn === vm.sn && vm._last.records.term === termKey) {
        return $q.when();
      }
      if (vm.loading.records) return $q.when();
      vm.loading.records = true;
      vm.error.records = null;

      var payload = { student_number: vm.sn, include_grades: true };
      var endpoint = API + '/student/records';
      if (vm.term) {
        endpoint = API + '/student/records-by-term';
        payload.term = '' + vm.term; // API expects string
      }
      return $http.post(endpoint, payload)
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          vm.records = (data && data.data) ? data.data : (data || null);
        })
        .catch(function () {
          vm.error.records = 'Failed to load records.';
        })
        .finally(function () {
          // Mark last loaded params
          vm._last.records = { sn: vm.sn, term: termKey };
          vm.loading.records = false;
        });
    };

    vm.updateRegistration = function () {
      if (!vm.sn || !vm.term) return;
      vm.loading.update = true;
      vm.error.update = null;

      var fields = {};
      if (vm.edit.paymentType !== null && vm.edit.paymentType !== undefined) fields.paymentType = vm.edit.paymentType;
      if (vm.edit.tuition_year !== null && vm.edit.tuition_year !== undefined) fields.tuition_year = vm.edit.tuition_year;
      if (vm.edit.allow_enroll !== null && vm.edit.allow_enroll !== undefined) fields.allow_enroll = parseInt(vm.edit.allow_enroll, 10);
      if (vm.edit.downpayment !== null && vm.edit.downpayment !== undefined) fields.downpayment = parseInt(vm.edit.downpayment, 10);
      if (vm.edit.intROG !== null && vm.edit.intROG !== undefined) fields.intROG = parseInt(vm.edit.intROG, 10);

      var payload = {
        student_number: vm.sn,
        term: vm.term,
        fields: fields
      };

      // Use UnityService to ensure headers carry X-Faculty-ID if available
      return UnityService.updateRegistration(payload)
        .then(function () {
          // After registration update, recompute and persist the saved tuition snapshot
          return UnityService.tuitionSave({
            student_number: vm.sn,
            term: vm.term
          }).catch(function () {
            // don't block UI if save fails; proceed to reload
            return null;
          });
        })
        .then(function () {
          // Force refresh: clear last-cache and reload fresh data and summaries
          vm._last.registration = null;
          vm._last.tuition = null;
          vm._last.records = null;
          vm._last.payments = null;
          vm.tuitionSaved = null;

          return vm.loadRegistration(true)
            .then(function () { return vm.loadTuition(true); })
            .then(function () { return vm.loadPaymentDetails(true); })
            .then(function () {
              // Ensure summary reflects latest server state immediately after tuition reload
              vm.refreshTuitionSummary();
              return vm.loadRecords(true);
            });
        })
        .catch(function () {
          vm.error.update = 'Failed to update registration.';
        })
        .finally(function () {
          vm.loading.update = false;
        });
    };

    // Auto-update saved tuition when options change
    vm.onOptionChange = function () {
      // Trigger update, which chains tuition-save and tuition reload
      vm.updateRegistration();
    };

    // Bootstrap sequence (ensure registration loads before tuition to carry tuition_year)
    vm._inBootstrap = true;
    vm.loading.bootstrap = true;
    $q.when()
      .then(vm.loadStudent)
      .then(vm.loadStudents)
      // Use existing global term selection; avoid calling TermService.init() here to prevent duplicate term list fetches
      .then(function () {
        applyGlobalTerm();
        return vm.loadTuitionYears();
      })
      .then(function () { return vm.loadRegistration(); })
      .then(function () { return vm.loadTuition(); })
      .then(function () { return vm.loadLedger(); })
      .then(function () { return vm.loadPaymentDetails(); })
      .then(function () { return vm.loadRecords(); })
      .finally(function () {
        vm._inBootstrap = false;
        vm.loading.bootstrap = false;
      });
  }

})();
