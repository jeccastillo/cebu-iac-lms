(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('HeaderController', HeaderController);

  HeaderController.$inject = ['$rootScope', '$scope', '$location', '$window', '$timeout', 'LinkService', 'StorageService', 'RoleService', 'SystemAlertsService', 'TermService', 'CampusService'];
  function HeaderController($rootScope, $scope, $location, $window, $timeout, LinkService, StorageService, RoleService, SystemAlertsService, TermService, CampusService) {
    var vm = this;
    var closeMenuTimeout;

    vm.state = StorageService.getJSON('loginState');
    vm.isLoggedIn = !!(vm.state && vm.state.loggedIn);

    // External CI links and internal SPA links
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();

    // RBAC helper for conditional header links
    vm.canAccess = RoleService.canAccess;
    vm.hasRole = RoleService.hasRole;

    // Initialize global campus/term services to ensure selectors work from header
    try {
      if (TermService && TermService.init) { TermService.init(); }
      if (CampusService && CampusService.init) { CampusService.init(); }
    } catch (e) {}

    vm.userMenuOpen = false;
    vm.alertMenuOpen = false;
    var closeAlertTimeout;

    // System Alerts state
    vm.alerts = [];
    vm.dismissAlert = dismissAlert;
    vm.dismissAll = dismissAll;
    vm.openAlert = openAlert;

    var _alertsUnsub = null;

    // Initialize alerts: initial fetch + subscribe
    initAlerts();

    function initAlerts() {
      try {
        SystemAlertsService.getActive().then(function (rows) {
          vm.alerts = Array.isArray(rows) ? rows : [];
        });
        _alertsUnsub = SystemAlertsService.subscribe(function (evt) {
          if (!evt) return;
          if (evt.action === 'refresh' && Array.isArray(evt.alerts)) {
            vm.alerts = evt.alerts.slice();
            return;
          }
          if (evt.alert && evt.alert.id != null) {
            var id = evt.alert.id;
            if (evt.action === 'delete') {
              vm.alerts = vm.alerts.filter(function (a) { return a.id !== id; });
            } else if (evt.action === 'update' || evt.action === 'create') {
              // upsert
              var found = false;
              for (var i = 0; i < vm.alerts.length; i++) {
                if (vm.alerts[i].id === id) { vm.alerts[i] = evt.alert; found = true; break; }
              }
              if (!found) vm.alerts.unshift(evt.alert);
            }
          }
        });
      } catch (e) {}
    }

    function dismissAlert(alert) {
      if (!alert || alert.id == null) return;
      SystemAlertsService.dismiss(alert.id).then(function (ok) {
        if (ok) {
          vm.alerts = vm.alerts.filter(function (a) { return a.id !== alert.id; });
        }
      });
    }

    function dismissAll() {
      try {
        var list = (vm.alerts || []).slice();
        list.forEach(function (a) {
          if (a && a.id != null) {
            try { SystemAlertsService.dismiss(a.id); } catch (e) {}
          }
        });
      } catch (e) {}
      vm.alerts = [];
      vm.alertMenuOpen = false;
    }

    function openAlert(a) {
      if (!a || !a.link) return;
      try {
        var l = (a.link + '').trim();
        if (!l) return;
        // Internal SPA path beginning with '/'
        if (/^\/[^/]/.test(l)) {
          $location.path(l);
          vm.alertMenuOpen = false;
          return;
        }
        // Hash paths beginning with '#'
        if (/^#/.test(l)) {
          window.location.href = l;
          vm.alertMenuOpen = false;
          return;
        }
        // External URL
        window.open(l, '_blank');
      } catch (e) {}
    }

    // Cleanup subscription when controller is destroyed
    $scope.$on('$destroy', function () {
      try { _alertsUnsub && _alertsUnsub(); } catch (e) {}
      _alertsUnsub = null;
    });
    
    vm.toggleUserMenu = function toggleUserMenu() {
      // Cancel any pending close timeout
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
        closeMenuTimeout = null;
      }
      vm.userMenuOpen = !vm.userMenuOpen;
    };
    
    vm.closeUserMenu = function closeUserMenu() {
      vm.userMenuOpen = false;
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
        closeMenuTimeout = null;
      }
    };

    vm.scheduleCloseUserMenu = function scheduleCloseUserMenu() {
      // Cancel any existing timeout
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
      }
      // Schedule close after a short delay
      closeMenuTimeout = $timeout(function() {
        vm.userMenuOpen = false;
        closeMenuTimeout = null;
      }, 300); // 300ms delay
    };

    vm.cancelCloseUserMenu = function cancelCloseUserMenu() {
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
        closeMenuTimeout = null;
      }
    };

    // Alerts dropdown controls
    vm.toggleAlertMenu = function toggleAlertMenu() {
      if (closeAlertTimeout) {
        $timeout.cancel(closeAlertTimeout);
        closeAlertTimeout = null;
      }
      vm.alertMenuOpen = !vm.alertMenuOpen;
    };

    vm.closeAlertMenu = function closeAlertMenu() {
      vm.alertMenuOpen = false;
      if (closeAlertTimeout) {
        $timeout.cancel(closeAlertTimeout);
        closeAlertTimeout = null;
      }
    };

    vm.scheduleCloseAlertMenu = function scheduleCloseAlertMenu() {
      if (closeAlertTimeout) {
        $timeout.cancel(closeAlertTimeout);
      }
      closeAlertTimeout = $timeout(function () {
        vm.alertMenuOpen = false;
        closeAlertTimeout = null;
      }, 300);
    };

    vm.cancelCloseAlertMenu = function cancelCloseAlertMenu() {
      if (closeAlertTimeout) {
        $timeout.cancel(closeAlertTimeout);
        closeAlertTimeout = null;
      }
    };

    // Close dropdowns on route change
    $rootScope.$on('$routeChangeStart', function () {
      vm.closeUserMenu();
      vm.closeAlertMenu();
    });

    vm.logout = function logout() {
      StorageService.remove('loginState');
      $location.path('/login');
    };

    vm.goToEmployeePortal = function () {
      $window.open('https://employeeportal.iacademy.edu.ph', '_blank');
    };
  }

})();
