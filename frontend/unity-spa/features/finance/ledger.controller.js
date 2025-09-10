(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FinanceLedgerController', FinanceLedgerController);

  FinanceLedgerController.$inject = ['$location', '$q', 'LinkService', 'StorageService', 'RoleService', 'TermService', 'FinanceLedgerService', 'StudentsService'];
  function FinanceLedgerController($location, $q, LinkService, StorageService, RoleService, TermService, FinanceLedgerService, StudentsService) {
    var vm = this;

    vm.title = 'Student Ledger';
    vm.state = StorageService.getJSON('loginState');

    // extra guard (in addition to run.js)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Legacy CI links (used during migration) and SPA nav
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();
    // RBAC helper
    vm.canAccess = RoleService.canAccess;

    // Filters and view model
    vm.filter = {
      student_number: '',
      term: 'all', // 'all' | int (syid)
      sort: 'asc'
    };
    vm.terms = [];
    vm.rows = [];
    vm.studentItems = [];
    vm.loading = false;
    vm.error = null;
    vm.summary = {
      opening: 0,
      assessment: 0,
      payment: 0,
      closing: 0
    };

    // Excess application UI state
    vm.excess = {
      showPanel: false,
      source_term_id: null,
      target_term_id: null,
      amount: null,
      notes: '',
      available: 0,
      suggestions: {
        byTermId: {} // cache of { termId: { closing } }
      },
      applications: [] // derived from rows with source === 'excess_application'
    };

    // Expose helpers
    vm.applyExcess = applyExcess;
    vm.revertExcess = revertExcess;
    vm.onTargetTermChange = onTargetTermChange;
    vm.suggestAmount = suggestAmount;
    vm.termLabel = termLabel;
    vm.termLabelById = termLabelById;
    vm.canRevertRow = canRevertRow;
    vm.getApplicationIdFromRow = getApplicationIdFromRow;

    vm.search = search;
    vm.exportCsv = exportCsv;
    vm.computeRunningBalance = computeRunningBalance;
    vm.onTermChange = onTermChange;
    vm.onStudentQuery = onStudentQuery;
    vm.onStudentSelected = onStudentSelected;

    // Initialize term service and default selections
    init();

    function init() {
      // Load terms and set default selection; keep "All terms" as default
      $q.when(TermService.init())
        .finally(function () {
          vm.terms = Array.isArray(TermService.availableTerms) ? TermService.availableTerms : [];
        });
    }

    function onTermChange() {
      // Toggle panel visibility when term changes
      updateExcessPanelVisibility();
    }

    function search() {
      vm.loading = true;
      vm.error = null;
      vm.rows = [];
      vm.summary = { opening: 0, assessment: 0, payment: 0, closing: 0 };

      var params = {
        term: vm.filter.term || 'all',
        sort: vm.filter.sort || 'asc'
      };
      if (vm.filter.student_id) {
        params.student_id = vm.filter.student_id;
      } else if (vm.filter.student_number && vm.filter.student_number.trim() !== '') {
        params.student_number = vm.filter.student_number.trim();
      }

      FinanceLedgerService.getLedger(params).then(function (resp) {
        if (!resp || resp.success !== true) {
          vm.error = (resp && resp.message) ? resp.message : 'Failed to fetch ledger';
          return;
        }
        var data = resp.data || {};
        var rows = Array.isArray(data.rows) ? data.rows : [];
        // compute running balance client-side
        computeRunningBalance(rows);

        // Copy totals if provided
        var meta = data.meta || {};
        vm.summary.opening = Number(meta.opening_balance || 0);
        vm.summary.assessment = Number(meta.total_assessment || 0);
        vm.summary.payment = Number(meta.total_payment || 0);
        vm.summary.closing = Number(meta.closing_balance || 0);

        // Derive applications for revert controls
        vm.excess.applications = deriveApplications(vm.rows);

        // Update panel visibility and defaults
        updateExcessPanelVisibility();
        if (vm.excess.showPanel) {
          vm.excess.source_term_id = getSelectedTermId();
          // default target term: pick any different term from list (first available)
          var defaultTarget = (vm.terms || []).find(function (t) {
            return Number(getTermId(t)) !== Number(vm.excess.source_term_id);
          });
          vm.excess.target_term_id = defaultTarget ? Number(getTermId(defaultTarget)) : null;
          vm.excess.available = Math.abs(Number(vm.summary.closing || 0));
          // compute suggestion if target is set
          suggestAmount();
        } else {
          vm.excess.source_term_id = null;
          vm.excess.target_term_id = null;
          vm.excess.amount = null;
          vm.excess.available = 0;
        }
      }).catch(function (e) {
        console.error('Ledger fetch error:', e);
        vm.error = 'Failed to fetch ledger';
      }).finally(function () {
        vm.loading = false;
      });
    }

    function computeRunningBalance(rows) {
      var balance = 0.0;
      vm.rows = (rows || []).map(function (r) {
        var assess = Number(r.assessment || 0);
        var pay = Number(r.payment || 0);
        balance = round2(balance + assess - pay);
        return Object.assign({}, r, { running_balance: balance });
      });
      // If API totals missing, derive summary from computed rows
      if (!vm.summary || (vm.summary.assessment === 0 && vm.summary.payment === 0)) {
        var totA = 0, totP = 0;
        vm.rows.forEach(function (r) {
          if (isFinite(r.assessment)) totA += Number(r.assessment || 0);
          if (isFinite(r.payment)) totP += Number(r.payment || 0);
        });
        vm.summary = {
          opening: 0,
          assessment: round2(totA),
          payment: round2(totP),
          closing: round2(0 + totA - totP)
        };
      }
      return vm.rows;

      function round2(n) {
        return Math.round((Number(n) || 0) * 100) / 100;
      }
    }

    function exportCsv() {
      var csv = FinanceLedgerService.toCsv(vm.rows || []);
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var link = document.createElement('a');
      link.href = url;
      var filename = 'student-ledger.csv';
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }

    // Autocomplete: fetch student suggestions
    function onStudentQuery(q) {
      return StudentsService.listSuggestions(q).then(function (items) {
        vm.studentItems = items || [];
        return vm.studentItems;
      });
    }

    // Autocomplete: hook for side-effects after selection (optional)
    function onStudentSelected() {
      // No-op for now; search() will read vm.filter.student_id.
      // Could populate vm.filter.student_number from selected item if needed for display/export.
      // Reset excess context when student changes
      vm.excess.applications = [];
      vm.excess.amount = null;
      vm.excess.target_term_id = null;
      vm.excess.source_term_id = null;
      vm.excess.available = 0;
      vm.excess.suggestions.byTermId = {};
    }

    // ----- Excess helpers -----

    function updateExcessPanelVisibility() {
      var hasStudent = !!vm.filter.student_id || (!!vm.filter.student_number && vm.filter.student_number.trim() !== '');
      var isSingleTerm = vm.filter.term !== 'all';
      vm.excess.showPanel = !!hasStudent && !!isSingleTerm && Number(vm.summary.closing) < 0;
    }

    function getSelectedTermId() {
      if (vm.filter.term === 'all') return null;
      return Number(vm.filter.term);
    }

    function getTermId(termObj) {
      // Handles multiple possible shapes of term objects
      if (!termObj) return null;
      return termObj.id || termObj.syid || termObj.sy_id || termObj.intID || termObj.value || null;
    }

    function termLabel(termObj) {
      if (!termObj) return '';
      return termObj.label || termObj.sy_label || termObj.name || termObj.strSYName || ('Term #' + (getTermId(termObj) || ''));
    }

    function termLabelById(termId) {
      var tid = Number(termId || 0);
      if (!tid || !Array.isArray(vm.terms)) return '';
      var found = vm.terms.find(function (t) {
        return Number(getTermId(t)) === tid;
      });
      return termLabel(found);
    }

    function deriveApplications(rows) {
      var apps = {};
      (rows || []).forEach(function (r) {
        if (r && r.source === 'excess_application') {
          var appId = r.source_id || parseAppIdFromRowId(r.id);
          if (!appId) return;
          // Track both directions; unify into single record
          if (!apps[appId]) {
            apps[appId] = {
              id: appId,
              source_term_id: null,
              target_term_id: null,
              amount: Number(r.assessment || r.payment || 0),
              created_at: r.posted_at || null
            };
          }
          if (r.ref_type === 'excess_transfer_out') {
            apps[appId].source_term_id = r.syid || null;
          } else if (r.ref_type === 'excess_transfer_in') {
            apps[appId].target_term_id = r.syid || null;
          }
        }
      });
      // Convert to array
      return Object.keys(apps).map(function (k) { return apps[k]; });
    }

    function parseAppIdFromRowId(id) {
      if (!id || typeof id !== 'string') return null;
      // formats: 'excess_out:{id}:{term}' or 'excess_in:{id}:{term}'
      var parts = id.split(':');
      if (parts.length >= 2 && (parts[0] === 'excess_out' || parts[0] === 'excess_in')) {
        var n = Number(parts[1]);
        return isFinite(n) ? n : null;
      }
      return null;
    }

    function onTargetTermChange() {
      suggestAmount();
    }

    function suggestAmount() {
      // Suggest min(abs(sourceClosing), max(0, targetClosing))
      var sourceAvail = Math.abs(Number(vm.summary.closing || 0));
      var tid = Number(vm.excess.target_term_id || 0);
      if (!tid) {
        vm.excess.amount = null;
        return;
      }
      // If we already cached target closing, use it
      if (vm.excess.suggestions.byTermId[tid] && isFinite(vm.excess.suggestions.byTermId[tid].closing)) {
        var targetClosing = Number(vm.excess.suggestions.byTermId[tid].closing || 0);
        var targetDue = Math.max(0, targetClosing);
        vm.excess.amount = round2(Math.min(sourceAvail, targetDue));
        return;
      }
      // Fetch target term ledger for closing
      var params = {
        term: tid,
        sort: vm.filter.sort || 'asc'
      };
      if (vm.filter.student_id) params.student_id = vm.filter.student_id;
      if (!params.student_id && vm.filter.student_number) params.student_number = vm.filter.student_number.trim();

      FinanceLedgerService.getLedger(params).then(function (resp) {
        var data = resp && resp.data ? resp.data : null;
        var meta = data && data.meta ? data.meta : null;
        var closing = Number(meta && meta.closing_balance || 0);
        vm.excess.suggestions.byTermId[tid] = { closing: closing };
        var targetDue = Math.max(0, closing);
        vm.excess.amount = round2(Math.min(sourceAvail, targetDue));
      }).catch(function () {
        vm.excess.amount = round2(sourceAvail);
      });
    }

    function applyExcess() {
      vm.error = null;
      if (!vm.filter.student_id) {
        vm.error = 'Select a student before applying excess.';
        return;
      }
      if (!vm.excess.showPanel) {
        vm.error = 'Excess is not available for the selected term.';
        return;
      }
      var sourceTermId = getSelectedTermId();
      var targetTermId = Number(vm.excess.target_term_id || 0);
      var amt = Number(vm.excess.amount || 0);
      if (!targetTermId || targetTermId === Number(sourceTermId)) {
        vm.error = 'Select a different target term.';
        return;
      }
      if (!(amt > 0)) {
        vm.error = 'Enter a valid amount greater than zero.';
        return;
      }

      var payload = {
        student_id: Number(vm.filter.student_id),
        source_term_id: Number(sourceTermId),
        target_term_id: Number(targetTermId),
        amount: round2(amt),
        notes: vm.excess.notes || null
      };

      vm.loading = true;
      FinanceLedgerService.applyExcess(payload).then(function (resp) {
        if (!resp || resp.success !== true) {
          vm.error = (resp && resp.message) ? resp.message : 'Failed to apply excess.';
          return;
        }
        // Refresh current ledger
        search();
      }).catch(function (e) {
        vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to apply excess.';
      }).finally(function () {
        vm.loading = false;
      });
    }

    function canRevertRow(row) {
      return row && row.source === 'excess_application' && (row.source_id || parseAppIdFromRowId(row.id));
    }

    function getApplicationIdFromRow(row) {
      return row && (row.source_id || parseAppIdFromRowId(row.id));
    }

    function revertExcess(appOrRow) {
      vm.error = null;
      var appId = typeof appOrRow === 'number' ? appOrRow : getApplicationIdFromRow(appOrRow);
      if (!appId) {
        vm.error = 'Invalid application to revert.';
        return;
      }
      vm.loading = true;
      FinanceLedgerService.revertExcess({ application_id: Number(appId), notes: null }).then(function (resp) {
        if (!resp || resp.success !== true) {
          vm.error = (resp && resp.message) ? resp.message : 'Failed to revert.';
          return;
        }
        // Refresh ledger
        search();
      }).catch(function (e) {
        vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to revert.';
      }).finally(function () {
        vm.loading = false;
      });
    }

    function round2(n) {
      return Math.round((Number(n) || 0) * 100) / 100;
    }
  }

})();
