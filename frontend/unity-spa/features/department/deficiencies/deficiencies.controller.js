(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('DepartmentDeficienciesController', DepartmentDeficienciesController);

  DepartmentDeficienciesController.$inject = ['$scope', 'DepartmentDeficienciesService', 'ToastService', 'TermService', 'StudentsService'];
  function DepartmentDeficienciesController($scope, DepartmentDeficienciesService, ToastService, TermService, StudentsService) {
    var vm = this;

    // State
    vm.loading = false;
    vm.meta = { departments: [], payment_descriptions: [] };
    vm.filters = {
      department_code: null,
      student_number: '',
      student_id: null,
      term: null,
      page: 1,
      per_page: 25
    };
    vm.form = {
      department_code: null,
      student_number: '',
      student_id: null,
      term: null,
      payment_description_id: null,
      new_payment_description: {
        name: '',
        amount: null,
        campus_id: null
      },
      use_new_pd: false,
      description: '',
      amount: null,
      posted_at: null,
      remarks: ''
    };
    vm.items = [];
    vm.metaLoading = false;
    vm.terms = [];
    vm.total = 0;

    // Student autocomplete state
    vm.students = [];
    vm.selectedStudentId = null;

    // Term display label (from global TermService)
    vm.termLabel = '';

    // Methods
    vm.loadMeta = loadMeta;
    vm.loadTerms = loadTerms;
    vm.search = search;
    vm.pageChange = pageChange;
    vm.toggleNewPd = toggleNewPd;
    vm.onPdChange = onPdChange;
    vm.onStudentQuery = onStudentQuery;
    vm.onStudentSelect = onStudentSelect;
    vm.submit = submit;
    vm.resetForm = resetForm;
    vm.setDepartment = setDepartment;

    init();

    function init() {
      // Initialize global term and preload students
      try {
        TermService.init()
          .then(function () {
            applyGlobalTerm();
            preloadStudents();
          })
          .catch(function () {
            applyGlobalTerm();
            preloadStudents();
          });
      } catch (e) {
        applyGlobalTerm();
      }

      // React to global term changes from TermService
      $scope.$on('termChanged', function () {
        applyGlobalTerm();
      });

      // Load meta including departments filtered by user
      loadMeta();
    }

    function loadMeta() {
      vm.metaLoading = true;
      DepartmentDeficienciesService.meta()
        .then(function (res) {
          if (res && res.success) {
            vm.meta.departments = Array.isArray(res.data.departments) ? res.data.departments : [];
            vm.meta.payment_descriptions = Array.isArray(res.data.payment_descriptions) ? res.data.payment_descriptions : [];
            // Default department and set filters/form defaults
            if (!vm.form.department_code && vm.meta.departments.length) {
              vm.form.department_code = vm.meta.departments[0];
            }
            if (!vm.filters.department_code && vm.meta.departments.length) {
              vm.filters.department_code = vm.meta.departments[0];
            }
          } else {
            ToastService && ToastService.error && ToastService.error('Failed to load meta.');
          }
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Failed to load meta.';
          ToastService && ToastService.error && ToastService.error(msg);
        })
        .finally(function () {
          vm.metaLoading = false;
          $scope.$evalAsync();
        });
    }

    function loadTerms() {
      try {
        TermService.list().then(function (res) {
          var rows = (res && res.data) ? res.data : (res && res.items) ? res.items : [];
          vm.terms = rows;
          // Try to default to active term
          if (!vm.form.term) {
            TermService.active().then(function (act) {
              var t = (act && act.data) ? act.data : (act && act.term) ? act.term : null;
              if (t && t.id) {
                vm.form.term = t.id;
                vm.filters.term = vm.filters.term || t.id;
              }
            }).catch(function () {});
          }
        }).catch(function(){});
      } catch (e) {}
    }

    function setDepartment(code) {
      vm.filters.department_code = code;
      vm.form.department_code = code;
    }

    function search(resetPage) {
      if (resetPage) vm.filters.page = 1;
      vm.loading = true;
      var params = {
        student_number: nonEmpty(vm.filters.student_number),
        student_id: isNum(vm.filters.student_id) ? Number(vm.filters.student_id) : null,
        term: isNum(vm.filters.term) ? Number(vm.filters.term) : null,
        department_code: nonEmpty(vm.filters.department_code),
        page: vm.filters.page,
        per_page: vm.filters.per_page
      };
      DepartmentDeficienciesService.list(params)
        .then(function (res) {
          if (res && res.success) {
            vm.items = Array.isArray(res.data) ? res.data : [];
            var meta = res.meta || {};
            vm.total = meta.total || vm.items.length;
            if (vm.items.length === 0) {
              ToastService && ToastService.info && ToastService.info('No results.');
            }
          } else {
            ToastService && ToastService.error && ToastService.error('Failed to load list.');
          }
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Failed to load list.';
          ToastService && ToastService.error && ToastService.error(msg);
        })
        .finally(function () {
          vm.loading = false;
          $scope.$evalAsync();
        });
    }

    function pageChange(delta) {
      var next = (vm.filters.page || 1) + delta;
      if (next < 1) next = 1;
      vm.filters.page = next;
      search(false);
    }

    function toggleNewPd() {
      vm.form.use_new_pd = !vm.form.use_new_pd;
      if (vm.form.use_new_pd) {
        vm.form.payment_description_id = null;
      } else {
        vm.form.new_payment_description = { name: '', amount: null, campus_id: null };
      }
    }

    function onPdChange() {
      if (!vm.form.payment_description_id) return;
      var chosen = (vm.meta.payment_descriptions || []).find(function (p) {
        return Number(p.id) === Number(vm.form.payment_description_id);
      });
      if (chosen) {
        // Default amount from PD but keep editable
        vm.form.amount = (chosen.amount != null) ? Number(chosen.amount) : vm.form.amount;
        vm.form.description = chosen.name;
      }
    }

    // Apply global term from TermService to form and filters; set display label
    function applyGlobalTerm() {
      try {
        // Sync term options for search filters
        vm.terms = (TermService && TermService.availableTerms) ? TermService.availableTerms : vm.terms;

        var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
        if (sel && (sel.intID || sel.id)) {
          var id = sel.intID || sel.id;
          vm.form.term = id;
          vm.filters.term = vm.filters.term || id;
          vm.termLabel = sel.label || String(id);
        } else {
          // fallback: try fetching active term once
          var p = TermService && TermService.getActiveTerm ? TermService.getActiveTerm() : null;
          if (p && typeof p.then === 'function') {
            p.then(function (act) {
              if (act && (act.intID || act.id)) {
                var id2 = act.intID || act.id;
                vm.form.term = id2;
                vm.filters.term = vm.filters.term || id2;
                vm.termLabel = act.label || String(id2);
              }
            }).catch(function(){});
          }
        }
      } catch (e) {}
    }

    // Preload initial student suggestions (first page)
    function preloadStudents() {
      try {
        if (StudentsService && typeof StudentsService.listPage === 'function') {
          StudentsService.listPage({ per_page: 100, page: 1, include_applicants: 1 })
            .then(function (list) { vm.students = Array.isArray(list) ? list : []; })
            .catch(function(){});
        }
      } catch (e) {}
    }

    // Remote query for student autocomplete
    function onStudentQuery(q) {
      try {
        var text = (q != null) ? String(q) : '';
        if (StudentsService && typeof StudentsService.listPage === 'function') {
          StudentsService.listPage({ q: text, per_page: 20, page: 1, include_applicants: 1 })
            .then(function (list) { vm.students = Array.isArray(list) ? list : []; })
            .catch(function(){});
        }
      } catch (e) {}
    }

    // When a student is selected from autocomplete, sync form fields
    function onStudentSelect() {
      try {
        var sid = vm.selectedStudentId;
        if (sid == null) return;
        var sel = (vm.students || []).find(function (it) { return Number(it.id) === Number(sid); });
        if (sel) {
          vm.form.student_id = sel.id;
          vm.form.student_number = sel.student_number || null;
        } else {
          // best-effort: just set id
          vm.form.student_id = sid;
        }
      } catch (e) {}
    }

    function submit() {
      // Basic validation
      var hasStudent = !!(nonEmpty(vm.form.student_number) || isNum(vm.form.student_id));
      var termOk = isNum(vm.form.term);
      var deptOk = nonEmpty(vm.form.department_code);
      if (!deptOk) {
        ToastService && ToastService.warn && ToastService.warn('Please select a department.');
        return;
      }
      if (!termOk) {
        ToastService && ToastService.warn && ToastService.warn('Please select a term (syid).');
        return;
      }
      if (!hasStudent) {
        ToastService && ToastService.warn && ToastService.warn('Provide student_id or student_number.');
        return;
      }
      if (!vm.form.use_new_pd && !vm.form.payment_description_id && !nonEmpty(vm.form.description)) {
        ToastService && ToastService.warn && ToastService.warn('Select a payment description or input a description.');
        return;
      }

      var body = {
        department_code: vm.form.department_code,
        term: Number(vm.form.term),
        student_id: isNum(vm.form.student_id) ? Number(vm.form.student_id) : null,
        student_number: nonEmpty(vm.form.student_number),
        payment_description_id: vm.form.use_new_pd ? null : (isNum(vm.form.payment_description_id) ? Number(vm.form.payment_description_id) : null),
        new_payment_description: vm.form.use_new_pd ? sanitizeNewPd(vm.form.new_payment_description) : null,
        description: nonEmpty(vm.form.description),
        amount: isNum(vm.form.amount) ? Number(vm.form.amount) : null,
        posted_at: nonEmpty(vm.form.posted_at),
        remarks: nonEmpty(vm.form.remarks)
      };

      vm.loading = true;
      DepartmentDeficienciesService.create(body)
        .then(function (res) {
          if (res && res.success) {
            ToastService && ToastService.success && ToastService.success('Deficiency created.');
            resetForm(true);
            search(true);
          } else {
            ToastService && ToastService.error && ToastService.error((res && res.message) || 'Create failed.');
          }
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Create failed.';
          ToastService && ToastService.error && ToastService.error(msg);
        })
        .finally(function () {
          vm.loading = false;
          $scope.$evalAsync();
        });
    }

    function resetForm(preserveDept) {
      var dept = preserveDept ? vm.form.department_code : (vm.meta.departments[0] || null);
      var term = vm.form.term;
      vm.form = {
        department_code: dept,
        student_number: '',
        student_id: null,
        term: term,
        payment_description_id: null,
        new_payment_description: { name: '', amount: null, campus_id: null },
        use_new_pd: false,
        description: '',
        amount: null,
        posted_at: null,
        remarks: ''
      };
      vm.selectedStudentId = null;
    }

    // Helpers
    function sanitizeNewPd(npd) {
      if (!npd) return null;
      var name = nonEmpty(npd.name);
      var amount = isNum(npd.amount) ? Number(npd.amount) : null;
      var campus_id = isNum(npd.campus_id) ? Number(npd.campus_id) : null;
      return { name: name, amount: amount, campus_id: campus_id };
    }

    function nonEmpty(v) {
      if (v == null) return null;
      var s = ('' + v).trim();
      return s === '' ? null : s;
    }
    function isNum(v) {
      return v !== null && v !== '' && !isNaN(v);
    }
  }
})();
