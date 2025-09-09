(function () {
  'use strict';

  /**
   * @typedef {Object} CatalogItem
   * @property {number} id
   * @property {string} name
   * @property {string} deduction_type
   * @property {boolean=} requires_referrer
   *
   * @typedef {Object} CreatePayload
   * @property {number} student_id
   * @property {number} syid
   * @property {number} discount_id
   * @property {number=} referrer_student_id
   * @property {string=} referrer_name
   */

  angular
    .module('unityApp')
    .controller('ScholarshipAssignmentsController', ScholarshipAssignmentsController);

  ScholarshipAssignmentsController.$inject = [
    '$q',
    '$location',
    'StorageService',
    'TermService',
    'ScholarshipAssignmentsService',
    'ScholarshipsService',
    'ToastService',
    'StudentsService'
  ];

  function ScholarshipAssignmentsController(
    $q,
    $location,
    StorageService,
    TermService,
    ScholarshipAssignmentsService,
    ScholarshipsService,
    ToastService,
    StudentsService
  ) {
    var vm = this;

    // State
    vm.state = StorageService.getJSON('loginState');
    vm.loading = false;
    vm.error = null;

    vm.terms = [];
    vm.selectedTerm = null;      // term object (from TermService.availableTerms)
    vm.studentId = '';           // student id (set by pui-autocomplete selection)
    vm.query = '';               // optional search by name/number (legacy; not used when studentId is selected)
    vm.students = [];            // pui-autocomplete suggestions list

    vm.items = [];               // assignment items loaded for term+student or q
    vm.catalog = [];             // scholarships/discounts catalog (active)
    vm.catalogFilter = {
      type: ''                   // '', 'scholarship', 'discount'
    };

    // Referral matching: tolerate both "Referal" and "Referral"
    var REFERRAL_NAMES = ['referal', 'referral'];
    // Set + normalizer for robust checks
    var REFERRAL_NAMES_SET = { referal: true, referral: true };
    function normalizeName(s) {
      try { return (s == null ? '' : String(s)).toLowerCase().trim(); } catch (e) { return ''; }
    }

    vm.pendingSelection = {
      discount_id: ''
    };

    // Referrer input state (shown when selected discount requires a referrer)
    vm.referrer = {
      mode: 'student', // 'student' | 'text'
      studentId: '',
      text: ''
    };

    // Suggestions list for referrer student autocomplete
    vm.referrerStudents = [];

    vm.selectedIds = {};         // { id: true } for bulk apply
    vm.applying = false;
    vm.creating = false;
    vm.removing = {};

    // Guards
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Methods
    vm.init = init;
    vm.loadItems = loadItems;
    vm.loadCatalog = loadCatalog;
    vm.filteredCatalog = filteredCatalog;

    // Autocomplete methods
    vm.loadStudents = loadStudents;
    vm.onStudentQuery = onStudentQuery;
    vm.onStudentSelect = onStudentSelect;

    vm.onTermChange = onTermChange;
    vm.onStudentIdChange = onStudentIdChange;
    vm.onDiscountChange = onDiscountChange;

    // Referrer autocomplete methods
    vm.onReferrerQuery = onReferrerQuery;
    vm.onReferrerSelect = onReferrerSelect;

    vm.createPending = createPending;
    vm.applySelected = applySelected;
    vm.toggleSelected = toggleSelected;
    vm.removeItem = removeItem;

    // Helpers exposed to template
    vm.hasSelectedStudent = hasSelectedStudent;
    vm.termLabel = termLabel;
    vm.isPending = function (it) {
      try { return String((it && it.assignment_status) || '').toLowerCase().trim() === 'pending'; } catch (e) { return false; }
    };
    vm.isApplied = function (it) {
      try { return String((it && it.assignment_status) || '').toLowerCase().trim() === 'applied'; } catch (e) { return false; }
    };

    // Catalog helpers
    vm.getSelectedCatalogItem = getSelectedCatalogItem;
    vm.requiresReferrer = requiresReferrer;

    // Expose label helper for reuse in templates
    vm.studentLabel = getStudentLabel;

    // Toast notify helper to reduce try/catch noise
    function notify(level, message) {
      try {
        if (!ToastService) return;
        if (level === 'warn' && ToastService.warn) { ToastService.warn(message); return; }
        if (level === 'error' && ToastService.error) { ToastService.error(message); return; }
        if (level === 'success' && ToastService.success) { ToastService.success(message); return; }
      } catch (e) {}
    }

    // Boot
    init();

    function init() {
      vm.loading = true;
      vm.error = null;
      // Preload initial student suggestions for autocomplete
      try { vm.loadStudents(); } catch (e) {}

      TermService.init()
        .then(function () {
          vm.terms = TermService.availableTerms || [];
          vm.selectedTerm = TermService.getSelectedTerm() || null;
          return loadCatalog();
        })
        .then(function () {
          return loadItems();
        })
        .catch(function (e) {
          console.error('Assignments init error:', e);
          vm.error = 'Failed to initialize page';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function loadItems() {
      if (!vm.selectedTerm) return $q.resolve([]);

      var opts = { syid: normalizeId(vm.selectedTerm && vm.selectedTerm.intID) };
      var sid = resolveStudentId();
      if (sid) {
        opts.student_id = sid;
      } else if (vm.query && ('' + vm.query).trim() !== '') {
        opts.q = ('' + vm.query).trim();
      }

      // If neither a valid student nor a query is present, do not fetch anything.
      if (!sid && (!vm.query || ('' + vm.query).trim() === '')) {
        vm.items = [];
        vm.selectedIds = {};
        return $q.resolve([]);
      }

      vm.loading = true;
      vm.error = null;

      return ScholarshipAssignmentsService.list(opts)
        .then(function (data) {
          // data = { success, data: { items: [...] } } by API wrapper _unwrap
          var payload = data && (data.items || data.data && data.data.items) || [];
          vm.items = Array.isArray(payload) ? payload : [];
          // Reset selected map
          vm.selectedIds = {};
          return vm.items;
        })
        .catch(function (e) {
          console.error('Failed to load assignments:', e);
          vm.items = [];
          vm.error = 'Failed to load assignments';
          notify('error', 'Failed to load assignments.');
          return [];
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function loadCatalog() {
      // Load active scholarships/discounts
      var params = { status: 'active' };
      return ScholarshipsService.list(params)
        .then(function (res) {
          // res = { success, data: [] } by _unwrap
          vm.catalog = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          return vm.catalog;
        })
        .catch(function (e) {
          console.error('Failed to load catalog:', e);
          vm.catalog = [];
          return [];
        });
    }

    function filteredCatalog() {
      var t = (vm.catalogFilter.type || '').trim();
      if (!t) return vm.catalog;
      return vm.catalog.filter(function (c) {
        return (c.deduction_type || '') === t;
      });
    }

    function onTermChange() {
      // Persist via TermService
      if (vm.selectedTerm) {
        TermService.setSelectedTerm(vm.selectedTerm);
      }
      loadItems();
    }

    function onStudentIdChange() {
      // When studentId provided, ignore q
      if (vm.studentId) {
        vm.query = '';
      }
      loadItems();
    }

    function createPending() {
      if (!vm.selectedTerm) {
        notify('warn', 'Please select a term.');
        return;
      }
      var syid = normalizeId(vm.selectedTerm && vm.selectedTerm.intID);
      var did = normalizeId(vm.pendingSelection.discount_id);

      if (!syid) {
        notify('warn', 'Invalid term selected.');
        return;
      }
      if (!did) {
        notify('warn', 'Please select a scholarship/discount to assign.');
        return;
      }

      // Resolve student id (try local parse, then remote lookup as fallback)
      var sid = resolveStudentId();
      var p = $q.when(sid);
      if (!sid) {
        var raw = (vm.studentId != null) ? ('' + vm.studentId).trim() : '';
        if (raw) {
          p = resolveStudentIdAsync(raw);
        }
      }

      p.then(function (resolvedSid) {
        if (!resolvedSid) {
          notify('warn', 'Select a student.');
          return;
        }
        vm.creating = true;
        vm.error = null;

        var payload = {
          student_id: resolvedSid,
          syid: syid,
          discount_id: did
        };

        // If referral discount selected, validate and include referrer fields
        if (requiresReferrer()) {
          if ((vm.referrer.mode || 'student') === 'student') {
            var rid = normalizeId(vm.referrer && vm.referrer.studentId);
            if (!rid) {
              notify('warn', 'Please select a referrer student or enter a name.');
              vm.creating = false;
              return;
            }
            payload.referrer_student_id = rid;
          } else {
            var rname = (vm.referrer && vm.referrer.text != null) ? ('' + vm.referrer.text).trim() : '';
            if (!rname) {
              notify('warn', 'Please enter the referrer name.');
              vm.creating = false;
              return;
            }
            payload.referrer_name = rname;
          }
        }

        return ScholarshipAssignmentsService.create(payload)
        .then(function () {
          vm.pendingSelection.discount_id = '';
          // Reset referrer after successful create
          vm.referrer = { mode: 'student', studentId: '', text: '' };
          return loadItems();
        })
        .catch(function (e) {
          console.error('Create pending failed:', e);
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to create pending assignment';
          notify('error', vm.error);
        })
        .finally(function () {
          vm.creating = false;
        });
      });
    }

    function applySelected() {
      var ids = Object.keys(vm.selectedIds || {})
        .filter(function (k) { return vm.selectedIds[k]; })
        .map(function (k) { return parseInt(k, 10); })
        .filter(function (v) { return v > 0; });

      if (!ids.length) {
        notify('warn', 'Select at least one pending assignment to apply.');
        return;
      }

      vm.applying = true;
      vm.error = null;

      ScholarshipAssignmentsService.apply(ids)
        .then(function () {
          return loadItems();
        })
        .catch(function (e) {
          console.error('Apply failed:', e);
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to apply assignments';
          notify('error', vm.error);
        })
        .finally(function () {
          vm.applying = false;
        });
    }

    function toggleSelected(item) {
      if (!item || !vm.isPending(item)) return;
      var id = item.id;
      vm.selectedIds[id] = !vm.selectedIds[id];
    }

    function removeItem(item) {
      if (!item) return;      

      if (!confirm('Remove this assignment?')) {
        return;
      }

      vm.removing[item.id] = true;
      vm.error = null;

      ScholarshipAssignmentsService.remove(item.id)
        .then(function () {
          return loadItems();
        })
        .catch(function (e) {
          console.error('Delete failed:', e);
          vm.error = (e && e.data && e.data.message) ? e.data.message : 'Failed to delete assignment';
          notify('error', vm.error);
        })
        .finally(function () {
          vm.removing[item.id] = false;
        });
    }

    // Autocomplete: preload first page for dropdown-like behavior
    function loadStudents() {
      try {
        if (StudentsService && typeof StudentsService.listPage === 'function') {
          return StudentsService.listPage({ per_page: 100, page: 1, include_applicants: 1 })
            .then(function (list) {
              vm.students = Array.isArray(list) ? list : [];
            })
            .catch(function () {});
        }
      } catch (e) {}
    }

    var _studentQuerySeq = 0;
    function onStudentQuery(q) {
      try {
        _studentQuerySeq++;
        var seq = _studentQuerySeq;
        var text = (q || '').trim();
        if (!text || text.length < 1) {
          return loadStudents();
        }
        var p = (StudentsService && typeof StudentsService.listPage === 'function')
          ? StudentsService.listPage({ q: text, per_page: 20, page: 1, include_applicants: 1 })
          : null;
        if (p && typeof p.then === 'function') {
          return p.then(function (list) {
            if (seq === _studentQuerySeq) {
              vm.students = Array.isArray(list) ? list : [];
            }
          }).catch(function () {});
        }
      } catch (e) {}
    }

    function onStudentSelect() {
      try {
        var id = parseInt(vm.studentId, 10);
        if (!isNaN(id)) vm.studentId = id;
      } catch (e) {}
      loadItems();
    }

    function onDiscountChange() {
      if (!requiresReferrer()) {
        // Clear referrer state when discount no longer requires it
        vm.referrer = { mode: 'student', studentId: '', text: '' };
      }
    }

    function getSelectedCatalogItem() {
      var did = normalizeId(vm.pendingSelection && vm.pendingSelection.discount_id);
      if (!did) return null;
      for (var i = 0; i < vm.catalog.length; i++) {
        var c = vm.catalog[i];
        var id = parseInt(c && c.id, 10);
        if (!isNaN(id) && id === did) return c || null;
      }
      return null;
    }

    function requiresReferrer() {
      var it = getSelectedCatalogItem();
      if (!it) return false;
      if (it.requires_referrer === true) return true;
      try {
        var type = normalizeName(it.deduction_type);
        if (type !== 'discount') return false;
        var n = normalizeName(it.name);
        // Substring match to tolerate variants like "Referral" / "Referal"
        return (n.indexOf('referral') !== -1) || (n.indexOf('referal') !== -1);
      } catch (e) { return false; }
    }

    // Referrer autocomplete
    function onReferrerQuery(q) {
      try {
        var text = (q || '').trim();
        if (!text || text.length < 1) {
          // Reuse initial preload if available
          return $q.when(loadStudents()).then(function () {
            vm.referrerStudents = Array.isArray(vm.students) ? vm.students.slice(0) : [];
          });
        }
        var p = (StudentsService && typeof StudentsService.listPage === 'function')
          ? StudentsService.listPage({ q: text, per_page: 20, page: 1, include_applicants: 1 })
          : null;
        if (p && typeof p.then === 'function') {
          return p.then(function (list) {
            vm.referrerStudents = Array.isArray(list) ? list : [];
          }).catch(function () {});
        }
      } catch (e) {}
    }

    function onReferrerSelect() {
      try {
        var id = parseInt(vm.referrer && vm.referrer.studentId, 10);
        if (!isNaN(id)) vm.referrer.studentId = id;
      } catch (e) {}
    }

    // Utils
    function getStudentLabel(item) {
      try {
        return (((item.student_number && item.student_number.length) ? item.student_number : ('ID:' + item.id)) + ' — ' + (item.last_name || '') + ', ' + (item.first_name || '') + (item.middle_name ? (' ' + item.middle_name) : ''));
      } catch (e) { return ''; }
    }

    function resolveStudentId() {
      try {
        var v = vm.studentId;
        if (v === undefined || v === null || v === '') return 0;
        var n = parseInt(v, 10);
        if (!isNaN(n) && n > 0) return n;

        var raw = ('' + v).trim();
        if (!raw) return 0;

        // Pattern "ID:123 — "
        var m = raw.match(/^ID:(\d+)\s+—/i);
        if (m && m[1]) {
          var nid = parseInt(m[1], 10);
          if (!isNaN(nid)) return nid;
        }

        var lower = raw.toLowerCase();
        for (var i = 0; i < vm.students.length; i++) {
          var s = vm.students[i];
          if (!s) continue;
          // Match by student_number
          if ((s.student_number || '').toLowerCase() === lower) {
            var sid = parseInt(s.id, 10);
            if (!isNaN(sid)) return sid;
          }
          // Match by full label
          var lbl = (getStudentLabel(s) || '').toLowerCase();
          if (lbl === lower) {
            var sid2 = parseInt(s.id, 10);
            if (!isNaN(sid2)) return sid2;
          }
        }
        return 0;
      } catch (e) { return 0; }
    }

    function hasSelectedStudent() {
      try { return resolveStudentId() > 0; } catch (e) { return false; }
    }

    // Attempt remote resolution when local match fails
    function resolveStudentIdAsync(raw) {
      try {
        var text = (raw != null ? String(raw).trim() : '');
        if (!text) return $q.when(0);
        if (StudentsService && typeof StudentsService.listPage === 'function') {
          return StudentsService.listPage({ q: text, per_page: 1, page: 1, include_applicants: 1 })
            .then(function (list) {
              if (Array.isArray(list) && list.length > 0) {
                var s = list[0];
                var id = parseInt(s && s.id, 10);
                if (!isNaN(id)) {
                  vm.studentId = id;
                  return id;
                }
              }
              return 0;
            })
            .catch(function () { return 0; });
        }
      } catch (e) {}
      return $q.when(0);
    }

    function normalizeId(v) {
      var n = parseInt(v, 10);
      return isNaN(n) ? 0 : n;
    }

    function termLabel(t) {
      if (!t) return '';
      return t.label || ('SY ' + (t.intID || ''));
    }
  }
})();
