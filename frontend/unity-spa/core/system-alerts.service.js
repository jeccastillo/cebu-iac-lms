(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('SystemAlertsService', SystemAlertsService);

  SystemAlertsService.$inject = ['$http', '$q', '$interval', '$window', 'APP_CONFIG', 'StorageService', 'RoleService', 'CampusService'];
  function SystemAlertsService($http, $q, $interval, $window, APP_CONFIG, StorageService, RoleService, CampusService) {
    var POLL_INTERVAL_MS = 30000; // 30s fallback polling

    var pollPromise = null;

    return {
      getActive: getActive,
      dismiss: dismiss,
      subscribe: subscribe,
      unsubscribe: unsubscribe
    };

    /**
     * Fetch active alerts for current audience (roles/campus), excluding dismissed.
     */
    function getActive() {
      var headers = _buildHeaders();
      return $http.get(APP_CONFIG.API_BASE + '/system-alerts/active', { headers: headers })
        .then(function (resp) {
          var payload = resp && resp.data ? resp.data : resp;
          var rows = [];
          if (payload && payload.success !== false && Array.isArray(payload.data)) {
            rows = payload.data;
          } else if (Array.isArray(payload)) {
            rows = payload;
          }

          // Defensive client-side audience filtering (roles + campus) to avoid over-broadcast
          try {
            var myRoles = (RoleService && RoleService.getRoles ? RoleService.getRoles() : [])
              .map(function (r) { return (r || '').toLowerCase().trim(); })
              .filter(function (r) { return !!r; });

            var campus = (CampusService && CampusService.getSelectedCampus) ? CampusService.getSelectedCampus() : null;
            var campusId = (campus && campus.id != null) ? String(campus.id) : null;

            rows = (rows || []).filter(function (a) {
              try {
                if (!a) return false;

                // Allow global alerts
                if (a.target_all === true || a.target_all === 1 || a.target_all === '1') return true;

                // Role filter: require intersection when role_codes present
                var rc = Array.isArray(a.role_codes)
                  ? a.role_codes.map(function (r) { return (r || '').toLowerCase().trim(); }).filter(Boolean)
                  : [];
                var rolesOk = rc.length ? rc.some(function (code) { return myRoles.indexOf(code) !== -1; }) : false;
                if (!rolesOk) return false;

                // Campus filter: if campus_ids provided, must contain current campus (else pass)
                var cids = Array.isArray(a.campus_ids) ? a.campus_ids.map(function (id) { return String(id); }) : [];
                var campusOk = (!cids.length || campusId == null || cids.indexOf(String(campusId)) !== -1);
                return campusOk;
              } catch (e) {
                return false;
              }
            });
          } catch (e) {
            // best-effort; fall back to original rows
          }

          return rows;
        })
        .catch(function () {
          return [];
        });
    }

    /**
     * Dismiss an alert for current user (idempotent).
     */
    function dismiss(id) {
      var headers = _buildHeaders();
      return $http.post(APP_CONFIG.API_BASE + '/system-alerts/' + encodeURIComponent(id) + '/dismiss', {}, { headers: headers })
        .then(function () { return true; })
        .catch(function () { return false; });
    }

    /**
     * Subscribe to realtime updates via Echo/Pusher if available, otherwise start polling.
     * cb signature: function (event) { event = { action, alert } } or array of alerts on poll refresh.
     * Returns an unsubscribe function.
     */
    function subscribe(cb) {
      // Try Echo (Pusher) first if enabled and available
      try {
        var enabled = !!$window.BROADCAST_ENABLED;
        var Echo = $window.Echo || null;
        if (enabled && Echo && Echo.channel) {
          var chan = Echo.channel('system.alerts');
          chan.listen('.system.alert', function (e) {
            try { cb && cb({ action: e.action, alert: e.alert }); } catch (err) {}
          });
          // Provide unsubscribe that leaves the channel
          return function () {
            try { Echo.leave('system.alerts'); } catch (e) {}
          };
        }
      } catch (e) {
        // ignore; will fallback to polling
      }

      // Fallback polling
      if (!pollPromise) {
        pollPromise = $interval(function () {
          getActive().then(function (alerts) {
            try { cb && cb({ action: 'refresh', alerts: alerts }); } catch (err) {}
          });
        }, POLL_INTERVAL_MS);
      }

      return function () {
        if (pollPromise) {
          $interval.cancel(pollPromise);
          pollPromise = null;
        }
      };
    }

    function unsubscribe(unsubFn) {
      if (typeof unsubFn === 'function') {
        try { unsubFn(); } catch (e) {}
      }
    }

    /**
     * Build audience and identity headers expected by the API:
     * - X-User-Roles: role1,role2
     * - X-Campus-ID: number
     * - X-User-Name, X-Login-Type, X-User-ID: for per-user dismissal
     */
    function _buildHeaders() {
      var headers = {};
      try {
        // Roles
        var roles = RoleService && RoleService.getRoles ? RoleService.getRoles() : [];
        // Fallbacks to ensure we include all roles assigned to the account (not just a single default):
        // - If RoleService returns empty, try loginState.role_codes (array or string),
        //   then loginState.roles (array or string)
        if (!Array.isArray(roles) || !roles.length) {
          try {
            var st = StorageService.getJSON('loginState') || {};
            if (Array.isArray(st.role_codes) && st.role_codes.length) {
              roles = st.role_codes;
            } else if (Array.isArray(st.roles) && st.roles.length) {
              roles = st.roles;
            } else if (typeof st.role_codes === 'string' && st.role_codes.trim()) {
              roles = [st.role_codes];
            } else if (typeof st.roles === 'string' && st.roles.trim()) {
              roles = [st.roles];
            }
          } catch (e) {}
        }
        if (Array.isArray(roles) && roles.length) {
          headers['X-User-Roles'] = roles.join(',');
        }

        // Campus
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id != null) {
          headers['X-Campus-ID'] = campus.id;
        }

        // Identity
        var state = StorageService.getJSON('loginState') || {};
        if (state && state.username) headers['X-User-Name'] = state.username;
        if (state && state.loginType) headers['X-Login-Type'] = state.loginType;
        if (state && state.user_id != null) headers['X-User-ID'] = state.user_id;
        else if (state && state.faculty_id != null) headers['X-User-ID'] = state.faculty_id;
        // Some admin-guarded endpoints (and Gate fallbacks) require explicit faculty context
        if (state && state.faculty_id != null) headers['X-Faculty-ID'] = state.faculty_id;
      } catch (e) {
        // best-effort headers
      }
      return headers;
    }
  }
})();
