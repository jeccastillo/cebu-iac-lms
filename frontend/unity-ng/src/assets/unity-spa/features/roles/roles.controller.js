(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('RolesController', RolesController);

  RolesController.$inject = ['$q', 'RolesAdminService', 'StorageService', 'RoleService'];
  function RolesController($q, RolesAdminService, StorageService, RoleService) {
    var vm = this;

    // State
    vm.loading = false;
    vm.error = '';
    vm.notice = '';

    // Manage Roles
    vm.includeInactive = false;
    vm.roles = [];
    vm.form = _emptyForm();
    vm.isEditing = false;

    // Assign Roles
    vm.facultyQuery = '';
    vm.facultyResults = [];
    vm.selectedFaculty = null;
    vm.assigned = { roles: [], codes: [] };
    vm.activeRoles = []; // cache of active roles for multiselect
    vm.assignCodesInput = ''; // comma-separated role codes to assign
    vm.assignSelectedIds = {}; // checkbox map by roleId for assignment

    // Methods
    vm.reload = reload;
    vm.toggleIncludeInactive = toggleIncludeInactive;

    vm.startAdd = startAdd;
    vm.startEdit = startEdit;
    vm.cancelEdit = cancelEdit;
    vm.save = save;
    vm.disable = disable;

    vm.searchFaculty = searchFaculty;
    vm.selectFaculty = selectFaculty;
    vm.assignByCodes = assignByCodes;
    vm.assignSelected = assignSelected;
    vm.removeFacultyRole = removeFacultyRole;

    vm.hasAdmin = function () { return RoleService.hasRole('admin'); };

    activate();

    // ---------------- lifecycle ----------------
    function activate() {
      reload().then(function () {
        // Build active role cache
        vm.activeRoles = vm.roles.filter(function (r) { return (r.intActive === 1 || r.intActive === '1'); });
      });
    }

    // ---------------- manage roles ----------------
    function reload() {
      vm.loading = true;
      vm.error = '';
      return RolesAdminService.list(vm.includeInactive === true)
        .then(function (data) {
          if (data && data.success) {
            vm.roles = data.data || [];
            vm.notice = '';
          } else {
            vm.roles = [];
            vm.error = (data && data.message) ? data.message : 'Failed to load roles';
          }
        })
        .catch(function (err) {
          vm.roles = [];
          vm.error = (err && err.message) ? err.message : 'Failed to load roles';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function toggleIncludeInactive() {
      reload().then(function () {
        vm.activeRoles = vm.roles.filter(function (r) { return (r.intActive === 1 || r.intActive === '1'); });
      });
    }

    function startAdd() {
      vm.isEditing = false;
      vm.form = _emptyForm();
      vm.notice = '';
      vm.error = '';
    }

    function startEdit(role) {
      vm.isEditing = true;
      vm.form = {
        intRoleID: role.intRoleID,
        strCode: role.strCode,
        strName: role.strName,
        strDescription: role.strDescription || '',
        intActive: (role.intActive === 1 || role.intActive === '1') ? 1 : 0
      };
      vm.notice = '';
      vm.error = '';
    }

    function cancelEdit() {
      startAdd();
    }

    function save() {
      vm.error = '';
      vm.notice = '';
      var payload = {
        strCode: (vm.form.strCode || '').trim().toLowerCase(),
        strName: (vm.form.strName || '').trim(),
        strDescription: (vm.form.strDescription || '').trim(),
        intActive: vm.form.intActive ? 1 : 0
      };

      if (!payload.strCode || !payload.strName) {
        vm.error = 'Code and Name are required';
        return;
      }

      vm.loading = true;
      var p;
      if (vm.isEditing && vm.form.intRoleID) {
        p = RolesAdminService.update(vm.form.intRoleID, payload);
      } else {
        p = RolesAdminService.create(payload);
      }

      p.then(function (data) {
        if (data && data.success) {
          vm.notice = vm.isEditing ? 'Role updated' : 'Role created';
          startAdd();
          return reload().then(function () {
            vm.activeRoles = vm.roles.filter(function (r) { return (r.intActive === 1 || r.intActive === '1'); });
          });
        } else {
          vm.error = (data && data.message) ? data.message : 'Save failed';
        }
      })
      .catch(function (err) {
        vm.error = (err && (err.message || (err.data && err.data.message))) ? (err.message || err.data.message) : 'Save failed';
      })
      .finally(function () {
        vm.loading = false;
      });
    }

    function disable(role) {
      if (!role || !role.intRoleID) return;
      vm.loading = true;
      vm.error = '';
      vm.notice = '';
      RolesAdminService.remove(role.intRoleID)
        .then(function (data) {
          if (data && data.success) {
            vm.notice = 'Role disabled';
            return reload().then(function () {
              vm.activeRoles = vm.roles.filter(function (r) { return (r.intActive === 1 || r.intActive === '1'); });
            });
          } else {
            vm.error = (data && data.message) ? data.message : 'Disable failed';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.message) ? err.message : 'Disable failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // ---------------- assign roles ----------------
    function searchFaculty() {
      vm.error = '';
      vm.notice = '';
      var q = (vm.facultyQuery || '').trim();
      if (!q) {
        vm.facultyResults = [];
        return;
      }
      vm.loading = true;
      RolesAdminService.searchFaculty(q)
        .then(function (data) {
          if (data && data.success) {
            vm.facultyResults = data.data || [];
          } else {
            vm.facultyResults = [];
            vm.error = (data && data.message) ? data.message : 'Faculty search failed';
          }
        })
        .catch(function (err) {
          vm.facultyResults = [];
          vm.error = (err && err.message) ? err.message : 'Faculty search failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function selectFaculty(fx) {
      vm.selectedFaculty = fx;
      vm.assigned = { roles: [], codes: [] };
      vm.assignCodesInput = '';
      vm.assignSelectedIds = {};
      if (!fx || !fx.id) return;

      vm.loading = true;
      RolesAdminService.facultyRoles(fx.id)
        .then(function (data) {
          if (data && data.success && data.data) {
            vm.assigned.roles = data.data.roles || [];
            vm.assigned.codes = data.data.codes || [];
          } else {
            vm.error = (data && data.message) ? data.message : 'Load faculty roles failed';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.message) ? err.message : 'Load faculty roles failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Assign by entering comma-separated role codes (e.g., "admin, registrar")
    function assignByCodes() {
      if (!vm.selectedFaculty || !vm.selectedFaculty.id) return;
      var raw = (vm.assignCodesInput || '').toLowerCase();
      var codes = raw.split(',').map(function (c) { return (c || '').trim(); }).filter(Boolean);
      if (!codes.length) return;

      vm.loading = true;
      RolesAdminService.assignFacultyRoles(vm.selectedFaculty.id, { role_codes: codes })
        .then(function (data) {
          if (data && data.success && data.data) {
            vm.assigned.roles = data.data.roles || [];
            vm.assigned.codes = data.data.codes || [];
            vm.notice = 'Roles assigned';
            vm.assignCodesInput = '';
          } else {
            vm.error = (data && data.message) ? data.message : 'Assign failed';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.message) ? err.message : 'Assign failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Assign using selected checkboxes from activeRoles
    function assignSelected() {
      if (!vm.selectedFaculty || !vm.selectedFaculty.id) return;
      var ids = [];
      vm.activeRoles.forEach(function (r) {
        if (vm.assignSelectedIds[r.intRoleID]) {
          ids.push(r.intRoleID);
        }
      });
      if (!ids.length) return;

      vm.loading = true;
      RolesAdminService.assignFacultyRoles(vm.selectedFaculty.id, { role_ids: ids })
        .then(function (data) {
          if (data && data.success && data.data) {
            vm.assigned.roles = data.data.roles || [];
            vm.assigned.codes = data.data.codes || [];
            vm.notice = 'Roles assigned';
            vm.assignSelectedIds = {};
          } else {
            vm.error = (data && data.message) ? data.message : 'Assign failed';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.message) ? err.message : 'Assign failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function removeFacultyRole(role) {
      if (!vm.selectedFaculty || !vm.selectedFaculty.id) return;
      if (!role || !role.intRoleID) return;

      vm.loading = true;
      RolesAdminService.removeFacultyRole(vm.selectedFaculty.id, role.intRoleID)
        .then(function (data) {
          if (data && data.success && data.data) {
            vm.assigned.roles = data.data.roles || [];
            vm.assigned.codes = data.data.codes || [];
            vm.notice = 'Role removed from faculty';
          } else {
            vm.error = (data && data.message) ? data.message : 'Remove failed';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.message) ? err.message : 'Remove failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // ---------------- utils ----------------
    function _emptyForm() {
      return {
        intRoleID: null,
        strCode: '',
        strName: '',
        strDescription: '',
        intActive: 1
      };
    }
  }

})();
