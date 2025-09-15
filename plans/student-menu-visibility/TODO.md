Title: Hide Department and Clinic from Student Sidebar

Context:
- Students reported seeing "Department" and "Clinic" groups in the sidebar even though they cannot access those routes.
- RoleService.canAccess(pathString) returns true by default when no ACCESS_MATRIX entry is configured for that path, which allowed the groups to appear.

Change Implemented:
- File: frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
- Function: canShowGroup(group)
- Logic added: If the logged-in user has role 'student_view', hide sidebar groups with keys 'department' and 'clinic'.

Code Snippet:
function canShowGroup(group) {
  if (!group || !group.children || !group.children.length) return false;

  // Hide specific groups for student users
  try {
    var gkey = (group.key || '').toLowerCase();
    if (vm && typeof vm.hasRole === 'function' && vm.hasRole('student_view')) {
      if (gkey === 'department' || gkey === 'clinic') {
        return false;
      }
    }
  } catch (e) {}

  for (var i = 0; i < group.children.length; i++) {
    var c = group.children[i];
    var testPath = c.path || '';
    if (testPath &amp;&amp; vm.canAccess(testPath)) {
      return true;
    }
  }
  return false;
}

Rationale:
- Avoids exposing irrelevant menu groups to student users.
- Minimal change with no backend impact and preserves existing RoleService behavior for other roles.

Testing Steps:
1) Login as a user with role: ['student_view'].
2) Observe sidebar: "Department" and "Clinic" groups should not be visible.
3) Login as registrar, clinic_staff, clinic_admin, or admin:
   - Ensure the corresponding groups still appear according to canAccess(child.path) and route requirements.

Notes:
- If an ACCESS_MATRIX is later introduced, the explicit hide remains a safe guard for student_view users.
