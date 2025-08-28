(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyClassesController', FacultyClassesController);

  FacultyClassesController.$inject = ['$http', 'APP_CONFIG', 'StorageService', 'ClasslistsService', '$location'];
  function FacultyClassesController($http, APP_CONFIG, StorageService, ClasslistsService, $location) {
    var vm = this;
    vm.title = 'My Classes';

    // State
    vm.terms = [];
    vm.selectedTerm = null;
    vm.page = 1;
    vm.perPage = loadPerPage();
    vm.meta = null;
    vm.loading = false;
    vm.error = null;
    vm.classes = [];

    // Handlers
    vm.onTermChange = onTermChange;
    vm.onPerPageChange = onPerPageChange;
    vm.prevPage = prevPage;
    vm.nextPage = nextPage;
    vm.gotoViewer = gotoViewer;

    init();

    function init() {
      vm.loading = true;
      loadTerms()
        .then(loadActiveOrStoredTerm)
        .then(function () { return loadClasses(1); })
        .catch(function (err) {
          vm.error = 'Failed to initialize My Classes';
          if (typeof console !== 'undefined' && console.error) console.error(err);
        })
        .finally(function () { vm.loading = false; });
    }

    function loadTerms() {
      return $http.get(APP_CONFIG.API_BASE + '/generic/terms')
        .then(function (resp) {
          var data = (resp && resp.data && resp.data.data) ? resp.data.data : [];
          vm.terms = Array.isArray(data) ? data : [];
        });
    }

    function loadActiveOrStoredTerm() {
      var stored = null;
      try { stored = StorageService.get('myClasses.selectedTerm'); } catch (e) {}
      if (stored) {
        vm.selectedTerm = stored;
        return Promise.resolve();
      }
      return $http.get(APP_CONFIG.API_BASE + '/generic/active-term')
        .then(function (resp) {
          var t = (resp && resp.data && resp.data.data) ? resp.data.data : null;
          if (t && t.intID) {
            vm.selectedTerm = t.intID;
            persistTerm();
          }
        });
    }

    function loadClasses(page) {
      vm.loading = true;
      vm.page = page || vm.page || 1;
      vm.error = null;

      var state = null;
      try { state = StorageService.getJSON('loginState'); } catch (e) {}
      var facultyId = state && state.faculty_id ? state.faculty_id : null;

      if (!facultyId) {
        vm.classes = [];
        vm.meta = { current_page: 1, per_page: vm.perPage, total: 0, last_page: 1 };
        vm.error = 'Missing faculty session. Please sign in again.';
        vm.loading = false;
        return Promise.resolve();
      }

      var termId = vm.selectedTerm;
      return ClasslistsService.list({
        term: termId,
        intFacultyID: facultyId,
        page: vm.page,
        per_page: vm.perPage
      }).then(function (res) {
        var rows, meta;
        if (res && Array.isArray(res)) { rows = res; }
        else if (res && res.data) { rows = res.data; meta = res.meta || null; }
        else { rows = []; }

        vm.classes = (rows || []).map(function (r) {
          return {
            intID: r.intID || r.intId || r.id,
            subjectCode: r.subjectCode || r.strCode || '',
            subjectDescription: r.subjectDescription || r.strDescription || '',
            sectionCode: r.sectionCode || r.strSection || '',
            finalized: (typeof r.intFinalized !== 'undefined') ? (parseInt(r.intFinalized, 10) === 1) : null
          };
        });

        vm.meta = meta || {
          current_page: vm.page,
          per_page: vm.perPage,
          total: vm.classes.length,
          last_page: vm.classes.length ? 1 : 1
        };
      }).catch(function (err) {
        vm.error = 'Failed to load classlists';
        if (typeof console !== 'undefined' && console.error) console.error(err);
      }).finally(function () {
        vm.loading = false;
      });
    }

    function onTermChange() {
      persistTerm();
      vm.page = 1;
      loadClasses(1);
    }

    function onPerPageChange() {
      persistPerPage();
      vm.page = 1;
      loadClasses(1);
    }

    function prevPage() {
      if (vm.meta && vm.page > 1) {
        vm.page -= 1;
        loadClasses(vm.page);
      }
    }

    function nextPage() {
      var last = vm.meta && vm.meta.last_page ? vm.meta.last_page : 1;
      if (vm.page < last) {
        vm.page += 1;
        loadClasses(vm.page);
      }
    }

    function gotoViewer(c) {
      if (c && c.intID) {
        $location.path('/classlists/' + c.intID + '/viewer');
      }
    }

    function persistTerm() {
      try { StorageService.set('myClasses.selectedTerm', vm.selectedTerm); } catch (e) {}
    }
    function persistPerPage() {
      try { StorageService.set('myClasses.perPage', vm.perPage); } catch (e) {}
    }
    function loadPerPage() {
      var v;
      try { v = StorageService.get('myClasses.perPage'); } catch (e) {}
      v = parseInt(v || '20', 10);
      if (!v || v < 5) v = 20;
      if (v > 100) v = 100;
      return v;
    }
  }

})();
