(function () {
  // Compute base root of this project when served from XAMPP or any subfolder.
  // If this file is served from /iacademy/cebu-iac-lms/frontend/unity-spa/index.html
  // then baseRoot => /iacademy/cebu-iac-lms
  var path = window.location.pathname || "/";
  var parts = path.split("/");
  // remove last 3 segments: 'frontend', 'unity-spa', and file/empty
  var trimmed = parts;
  if (trimmed.length >= 3) {
    trimmed = parts.slice(0, parts.length - 3);
  }
  var baseRoot = trimmed.join("/") || "/";
  if (baseRoot.length > 1) {
    baseRoot = baseRoot.replace(/\/+$/, "");
  }

  var defaultApi =
    (baseRoot === "/" ? "" : baseRoot) + "/laravel-api/public/api/v1";
  //var defaultApi = "http://127.0.0.1:8000/api/v1";

  // Base URL of the Laravel API v1. Can be overridden by setting window.API_BASE before this script.
  window.API_BASE = window.API_BASE || defaultApi;

  // Optional: redirects after successful login (if served under same host)
  window.AFTER_LOGIN_REDIRECTS = window.AFTER_LOGIN_REDIRECTS || {
    faculty: (baseRoot === "/" ? "" : baseRoot) + "/unity",
    student: (baseRoot === "/" ? "" : baseRoot) + "/portal",
  };

  // Control whether to use redirects above or show a local dashboard
  window.LOGIN_APP_CONFIG = Object.assign(
    { useRedirects: false },
    window.LOGIN_APP_CONFIG || {}
  );
})();
