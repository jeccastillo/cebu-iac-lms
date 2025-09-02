(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FinanceStudentBillingController', FinanceStudentBillingController);

  FinanceStudentBillingController.$inject = ['$scope', 'ToastService', 'StudentBillingService', 'TermService', 'StudentsService', 'PaymentDescriptionsService'];
  function FinanceStudentBillingController($scope, ToastService, StudentBillingService, TermService, StudentsService, PaymentDescriptionsService) {
    var vm = this;

    // Filters and pagination
    vm.filters = {
      student_number: '',
      student_id: null,
      term: null
    };

    // Data
    vm.items = [];
    vm.loading = false;
    vm.students = [];
    vm.termOptions = [];
    vm.paymentDescriptions = [];
    vm.selectedStudent = null;

    // UI State
    vm.modalOpen = false;
    vm.editing = false;
    vm.current = {
      id: null,
      student_id: null,
      term: null,
      description: '',
      amount: null,
      posted_at: null,
      remarks: ''
    };

    // Methods
    vm.search = search;
    vm.resetFilters = resetFilters;
    vm.openAdd = openAdd;
    vm.openEdit = openEdit;
    vm.closeModal = closeModal;
    vm.save = save;
    vm.remove = remove;
    vm.onStudentSelect = onStudentSelect; // optional if we wire autocomplete
    vm.loadActiveTerm = loadActiveTerm;
    vm.getStudentDisplay = getStudentDisplay;
    vm.getTermDisplay = getTermDisplay;
    vm.onPaymentDescriptionChange = onPaymentDescriptionChange;

    // Helpers
    vm.toMySQLDateTime = toMySQLDateTime;
    vm.toInputDateTime = toInputDateTime;

    activate();

    function activate() {
      // Preload term options and try to apply selected/active term
      try {
        if (TermService && TermService.loadTerms) {
          TermService.loadTerms()
            .then(function (terms) {
              vm.termOptions = Array.isArray(terms) ? terms : [];
              try {
                var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
                if (sel && sel.intID) {
                  vm.filters.term = sel.intID;
                } else {
                  loadActiveTerm();
                }
              } catch (e) {
                loadActiveTerm();
              }
            })
            .catch(function () {
              loadActiveTerm();
            });
        } else {
          loadActiveTerm();
        }
      } catch (e) {
        loadActiveTerm();
      }

      // Preload students for autocomplete
      try {
        if (StudentsService && StudentsService.listAll) {
          StudentsService.listAll().then(function (list) {
            vm.students = Array.isArray(list) ? list : [];
          });
        }
      } catch (e2) {}

      // Preload payment descriptions for dropdown in Add modal
      try {
        if (PaymentDescriptionsService && PaymentDescriptionsService.list) {
          PaymentDescriptionsService.list({ sort: 'name', order: 'asc', per_page: 1000 }).then(function (res) {
            // Handle both shapes: {data,meta} or plain array
            vm.paymentDescriptions = (res && res.data) ? (Array.isArray(res.data) ? res.data : []) : (Array.isArray(res) ? res : []);
          }).catch(function () {
            vm.paymentDescriptions = [];
          });
        }
      } catch (e3) {}

      // Keep selectedStudent and coerced student_id in sync with autocomplete model
      try {
        $scope.$watch(function () { return vm.filters.student_id; }, function (nv) {
          try {
            if (nv === undefined || nv === null || nv === '') {
              vm.selectedStudent = null;
              return;
            }
            var sid = parseInt(nv, 10);
            if (isNaN(sid)) {
              vm.selectedStudent = null;
              return;
            }
            // locate student and sync student_number
            var match = null;
            for (var i = 0; i < vm.students.length; i++) {
              var s = vm.students[i];
              if (s && parseInt(s.id, 10) === sid) { match = s; break; }
            }
            if (match) {
              vm.selectedStudent = match;
              vm.filters.student_id = sid; // coerce to int
              vm.filters.student_number = match.student_number || vm.filters.student_number || '';
            }
          } catch (e) {}
        });
      } catch (e4) {}
    }

    function loadActiveTerm() {
      try {
        // Prefer globally selected term when available
        if (TermService && TermService.getSelectedTerm) {
          var sel = TermService.getSelectedTerm();
          if (sel && sel.intID) {
            vm.filters.term = sel.intID;
            return;
          }
        }
        // Fallback: fetch active term (may be async)
        var p = TermService && TermService.getActiveTerm ? TermService.getActiveTerm() : null;
        if (p && typeof p.then === 'function') {
          p.then(function (t) {
            if (t && t.intID) vm.filters.term = t.intID;
          });
        } else if (p && p.intID) {
          vm.filters.term = p.intID;
        }
      } catch (e) {
        // no-op
      }
    }

    function search() {
      // Coerce IDs to integers to satisfy backend validation
      var sid = parseInt(vm.filters.student_id, 10);
      if (isNaN(sid)) sid = null;
      // Fallback to selectedStudent if present
      if (!sid && vm.selectedStudent && vm.selectedStudent.id != null) {
        var tmpSid = parseInt(vm.selectedStudent.id, 10);
        if (!isNaN(tmpSid)) sid = tmpSid;
      }
      var term = parseInt(vm.filters.term, 10);
      if (isNaN(term)) term = null;

      if (!term) {
        ToastService.warn('Please select a term (syid) before searching.');
        return;
      }
      if (!vm.filters.student_number && !sid) {
        ToastService.warn('Enter a student number or select a student.');
        return;
      }
      vm.loading = true;
      StudentBillingService.list({
        student_number: vm.filters.student_number || null,
        student_id: sid,
        term: term
      }).then(function (data) {
        vm.items = (data && data.data) ? data.data : (data || []);
      }).catch(function () {
        ToastService.error('Failed to load student billing items.');
      }).finally(function () {
        vm.loading = false;
      });
    }

    function resetFilters() {
      vm.filters.student_number = '';
      vm.filters.student_id = null;
      vm.selectedStudent = null;
      loadActiveTerm();
      vm.items = [];
    }

    function openAdd() {
      // Coerce filters to integers before using them
      var term = parseInt(vm.filters.term, 10);
      if (isNaN(term) || !term) {
        ToastService.warn('Please select a term (syid) first.');
        return;
      }
      var sid = parseInt(vm.filters.student_id, 10);
      if (isNaN(sid)) sid = null;

      // Fallback to selectedStudent if present
      if (!sid && vm.selectedStudent && vm.selectedStudent.id != null) {
        var tmpSid = parseInt(vm.selectedStudent.id, 10);
        if (!isNaN(tmpSid)) sid = tmpSid;
      }

      // Additional fallback: resolve from typed student_number if available
      if (!sid && vm.filters.student_number) {
        try {
          var sn = (vm.filters.student_number || '').trim();
          if (sn) {
            for (var i = 0; i < vm.students.length; i++) {
              var s = vm.students[i];
              if (s && (s.student_number + '') === sn) {
                var r = parseInt(s.id, 10);
                if (!isNaN(r)) {
                  sid = r;
                  vm.selectedStudent = s;
                  vm.filters.student_id = r;
                  break;
                }
              }
            }
          }
        } catch (e) {}
      }

      if (!sid) {
        ToastService.warn('Select a student first.');
        return;
      }
      vm.editing = false;
      vm.current = {
        id: null,
        student_id: sid,
        term: term,
        description: '',
        amount: null,
        // Default to current local datetime in input-friendly format
        posted_at: toInputDateTime(new Date()),
        remarks: '',
        payment_description_id: null,
        generate_invoice: true
      };
      vm.modalOpen = true;
    }

    function openEdit(item) {
      if (!item) return;
      vm.editing = true;
      vm.current = {
        id: item.id,
        student_id: item.student_id,
        term: item.syid,
        description: item.description,
        amount: item.amount,
        // Convert server value (e.g., 'YYYY-MM-DD HH:mm:ss') to input-friendly 'YYYY-MM-DDTHH:mm'
        posted_at: toInputDateTime(item.posted_at),
        remarks: item.remarks
      };
      vm.modalOpen = true;
    }

    function closeModal() {
      vm.modalOpen = false;
      vm.current = {
        id: null,
        student_id: null,
        term: null,
        description: '',
        amount: null,
        posted_at: null,
        remarks: ''
      };
    }

    function save() {
      // Map selected payment description (Add mode) into description/amount before validation
      if (!vm.editing && vm.current && vm.current.payment_description_id) {
        var sel = null;
        for (var i = 0; i < vm.paymentDescriptions.length; i++) {
          var d = vm.paymentDescriptions[i];
          if (d && String(d.intID) === String(vm.current.payment_description_id)) { sel = d; break; }
        }
        if (sel) {
          if (!vm.current.description || vm.current.description.trim() === '') {
            vm.current.description = sel.name || '';
          }
          // If amount not set, attempt to use default from payment description (if non-zero)
          if ((vm.current.amount === null || vm.current.amount === undefined || vm.current.amount === '') && sel.amount !== null && sel.amount !== undefined) {
            vm.current.amount = sel.amount;
          }
        }
      }

      // Basic validations
      if (!vm.current.description || vm.current.description.trim() === '') {
        ToastService.warn('Description is required.');
        return;
      }
      if (vm.current.amount === null || vm.current.amount === undefined || vm.current.amount === 0) {
        ToastService.warn('Amount is required and cannot be zero. Use negative value for credits.');
        return;
      }
      if (!vm.current.student_id || !vm.current.term) {
        ToastService.warn('Student and term are required.');
        return;
      }

      var payload = {
        description: vm.current.description,
        amount: parseFloat(vm.current.amount),
        // Normalize to MySQL DATETIME 'YYYY-MM-DD HH:mm:ss' (no timezone)
        posted_at: toMySQLDateTime(vm.current.posted_at),
        remarks: vm.current.remarks || null
      };

      if (!vm.editing) {
        // Create requires student_id and term
        payload.student_id = vm.current.student_id;
        payload.term = vm.current.term;
        // Pass generate_invoice flag (default true)
        payload.generate_invoice = !!vm.current.generate_invoice;
        StudentBillingService.create(payload).then(function () {
          ToastService.success('Student billing item created.');
          vm.modalOpen = false;
          search();
        }).catch(function () {
          ToastService.error('Failed to create billing item.');
        });
      } else {
        StudentBillingService.update(vm.current.id, payload).then(function () {
          ToastService.success('Student billing item updated.');
          vm.modalOpen = false;
          search();
        }).catch(function () {
          ToastService.error('Failed to update billing item.');
        });
      }
    }

    function remove(item) {
      if (!item || !item.id) return;
      if (!confirm('Delete this billing item?')) return;
      StudentBillingService.remove(item.id).then(function () {
        ToastService.success('Student billing item deleted.');
        search();
      }).catch(function () {
        ToastService.error('Failed to delete billing item.');
      });
    }

    function onStudentSelect() {
      // pui-autocomplete sets vm.filters.student_id; find student to sync student_number
      try {
        var id = vm.filters.student_id != null ? String(vm.filters.student_id) : null;
        if (!id) return;
        var match = null;
        for (var i = 0; i < vm.students.length; i++) {
          var s = vm.students[i];
          if (s && String(s.id) === id) { match = s; break; }
        }
        if (match) {
          vm.filters.student_number = match.student_number || vm.filters.student_number || '';
          vm.selectedStudent = match;
          // Coerce student_id to integer for API compatibility
          var parsed = parseInt(match.id, 10);
          if (!isNaN(parsed)) vm.filters.student_id = parsed;
        } else {
          vm.selectedStudent = null;
        }
      } catch (e) {}
    }

    function getStudentDisplay() {
      try {
        var id = vm.current && vm.current.student_id != null ? String(vm.current.student_id) : null;
        if (!id) return '—';
        var match = null;
        for (var i = 0; i < vm.students.length; i++) {
          var s = vm.students[i];
          if (s && String(s.id) === id) { match = s; break; }
        }
        if (!match) return 'Student #' + id;
        var name = (match.last_name || '') + ', ' + (match.first_name || '');
        if (match.middle_name) name += ' ' + match.middle_name;
        return name.trim();
      } catch (e) { return '—'; }
    }

    function getTermDisplay() {
      try {
        var tid = vm.current && vm.current.term != null ? String(vm.current.term) : null;
        if (!tid) return '—';
        var t = null;
        for (var j = 0; j < vm.termOptions.length; j++) {
          var it = vm.termOptions[j];
          if (it && String(it.intID) === tid) { t = it; break; }
        }
        if (!t) return 'SY ' + tid;
        return t.label || ('SY ' + t.intID);
      } catch (e) { return '—'; }
    }

    function onPaymentDescriptionChange() {
      try {
        if (!vm.current || !vm.current.payment_description_id) return;
        var sel = null;
        for (var i = 0; i < vm.paymentDescriptions.length; i++) {
          var d = vm.paymentDescriptions[i];
          if (d && String(d.intID) === String(vm.current.payment_description_id)) { sel = d; break; }
        }
        if (sel) {
          vm.current.description = sel.name || vm.current.description || '';
          // Pre-fill amount only if not yet provided
          if (vm.current.amount === null || vm.current.amount === undefined || vm.current.amount === '') {
            if (sel.amount !== null && sel.amount !== undefined) vm.current.amount = sel.amount;
          }
        }
      } catch (e) {}
    }

    // Convert a Date/object/ISO/datetime-local string to 'YYYY-MM-DD HH:mm:ss' (MySQL DATETIME)
    function toMySQLDateTime(val) {
      if (!val) return null;

      function pad(n) { return (n < 10 ? '0' + n : '' + n); }

      // If already in MySQL format, return as-is
      if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(val)) {
        return val;
      }

      // Handle 'YYYY-MM-DDTHH:mm' directly
      if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(val)) {
        var p = val.split('T');
        var datePart = p[0];
        var timePart = p[1] + ':00';
        return datePart + ' ' + timePart;
      }

      // Fallback: try Date parsing (handles ISO like '...Z' and with seconds)
      var d = null;
      if (val instanceof Date) {
        d = val;
      } else if (typeof val === 'string') {
        // Remove trailing Z if present so we don't mis-format as UTC text
        var s = val.replace(/Z$/, '');
        d = new Date(s);
      } else {
        try { d = new Date(val); } catch (e) { d = null; }
      }

      if (!d || isNaN(d.getTime())) return null;

      return [
        d.getFullYear(),
        '-',
        pad(d.getMonth() + 1),
        '-',
        pad(d.getDate()),
        ' ',
        pad(d.getHours()),
        ':',
        pad(d.getMinutes()),
        ':',
        pad(d.getSeconds())
      ].join('');
    }

    // Convert server value 'YYYY-MM-DD HH:mm:ss' or ISO to input-friendly 'YYYY-MM-DDTHH:mm'
    function toInputDateTime(val) {
      if (!val) return null;
      // If already datetime-local form, keep minutes precision
      if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(val)) {
        return val.slice(0, 16);
      }
      // MySQL format
      if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/.test(val)) {
        return val.replace(' ', 'T').slice(0, 16);
      }
      // Fallback parse
      var d = null;
      try { d = new Date(val); } catch (e) { d = null; }
      if (!d || isNaN(d.getTime())) return null;

      function pad(n) { return (n < 10 ? '0' + n : '' + n); }
      return [
        d.getFullYear(),
        '-',
        pad(d.getMonth() + 1),
        '-',
        pad(d.getDate()),
        'T',
        pad(d.getHours()),
        ':',
        pad(d.getMinutes())
      ].join('');
    }
  }
})();
