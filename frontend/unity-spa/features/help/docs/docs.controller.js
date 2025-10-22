(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('DocsController', DocsController);

  DocsController.$inject = ['$routeParams', '$location', '$sce', '$scope', 'RoleService', 'DocsService'];
  function DocsController($routeParams, $location, $sce, $scope, RoleService, DocsService) {
    var vm = this;

    // State
    vm.categories = [];
    vm.currentCategory = null;
    vm.currentPage = null;
    vm.loading = false;
    vm.error = null;
    vm.safeHtmlTrusted = $sce.trustAsHtml('');

    // Expose
    vm.select = select;
    vm.hasRole = RoleService.hasRole;
    vm.canView = canView;
    vm.isActive = isActive;
    vm.pageHref = pageHref;

    activate();

    function activate() {
      try {
        vm.categories = DocsService.getCategories();
      } catch (e) {
        vm.categories = [];
      }
      // Determine selection
      var catKey = ($routeParams.category || '').trim();
      var pageKey = ($routeParams.page || '').trim();

      if (!catKey) {
        // default landing: registrar/enlistment (most used)
        catKey = 'registrar';
        pageKey = 'enlistment';
      }

      // Select and load
      select(catKey, pageKey);
    }

    function select(categoryKey, pageKey) {
      vm.error = null;
      vm.loading = true;

      // Resolve category
      vm.currentCategory = findCategory(categoryKey);
      if (!vm.currentCategory) {
        vm.loading = false;
        vm.error = 'Category not found.';
        return;
      }

      // Resolve page
      vm.currentPage = findPage(vm.currentCategory, pageKey) ||
                       findPage(vm.currentCategory, defaultPageKey(vm.currentCategory.key));

      if (!vm.currentPage) {
        vm.loading = false;
        vm.error = 'Page not found.';
        return;
      }

      // Role-gate check
      if (!canView(vm.currentPage)) {
        vm.loading = false;
        vm.error = 'You do not have access to view this page.';
        return;
      }

      // Push route for deep-linking (avoid infinite loop when already at the same path)
      var desired = '/docs/' + vm.currentCategory.key + '/' + vm.currentPage.key;
      if ($location.path() !== desired) {
        $location.path(desired);
      }

      // Fetch + render + sanitize
      var mdPath = vm.currentPage.path || DocsService.resolvePath(vm.currentCategory.key, vm.currentPage.key);
      if (!mdPath) {
        vm.loading = false;
        vm.error = 'File not found for this page.';
        return;
      }

      DocsService.fetchMarkdown(mdPath)
        .then(function (md) {
          try {
            var html = DocsService.renderMarkdown(md || '');
            var safe = DocsService.sanitize(html);
            vm.safeHtmlTrusted = $sce.trustAsHtml(safe || '');
          } catch (e) {
            vm.safeHtmlTrusted = $sce.trustAsHtml('<p>Failed to render content.</p>');
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load documentation content.';
        })
        .finally(function () {
          vm.loading = false;
          if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
        });
    }

    function canView(page) {
      try {
        if (!page) return false;
        var allowed = page.requiredRoles || [];
        if (!allowed.length) return true;
        return RoleService.hasAny(allowed);
      } catch (e) {
        return false;
      }
    }

    function isActive(categoryKey, pageKey) {
      try {
        var ck = (vm.currentCategory && vm.currentCategory.key) || '';
        var pk = (vm.currentPage && vm.currentPage.key) || '';
        return ck === (categoryKey || '') && pk === (pageKey || '');
      } catch (e) {
        return false;
      }
    }

    function pageHref(categoryKey, pageKey) {
      return '#/docs/' + (categoryKey || '') + '/' + (pageKey || '');
    }

    // ----------------- helpers -----------------

    function findCategory(key) {
      key = (key || '').trim();
      for (var i = 0; i < (vm.categories || []).length; i++) {
        if (vm.categories[i] && vm.categories[i].key === key) return vm.categories[i];
      }
      return null;
    }

    function findPage(category, pageKey) {
      pageKey = (pageKey || '').trim();
      if (!category || !Array.isArray(category.pages)) return null;
      for (var i = 0; i < category.pages.length; i++) {
        if (category.pages[i] && category.pages[i].key === pageKey) return category.pages[i];
      }
      return null;
    }

    function defaultPageKey(categoryKey) {
      return (categoryKey === 'registrar') ? 'enlistment' : 'index';
    }
  }

})();
