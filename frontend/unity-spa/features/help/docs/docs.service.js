(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('DocsService', DocsService);

  DocsService.$inject = ['$http', '$q'];
  function DocsService($http, $q) {
    var svc = {
      getCategories: getCategories,
      resolvePath: resolvePath,
      fetchMarkdown: fetchMarkdown,
      renderMarkdown: renderMarkdown,
      sanitize: sanitize,
    };
    return svc;

    // -------------------- impl --------------------

    function computeBaseRoot() {
      try {
        var path = window.location.pathname || '/';
        var parts = path.split('/');
        var trimmed = parts;
        if (trimmed.length >= 3) {
          trimmed = parts.slice(0, parts.length - 3);
        }
        var baseRoot = trimmed.join('/') || '/';
        if (baseRoot.length > 1) {
          baseRoot = baseRoot.replace(/\/+$/, '');
        }
        return baseRoot;
      } catch (e) {
        return '';
      }
    }

    function _rolesList() {
      // Internal staff roles only (align with existing roles.constants.js)
      return ['registrar', 'admissions', 'scholarship', 'finance', 'faculty_admin', 'finance_admin', 'admin'];
    }

    function _categoriesConfig() {
      var baseRoot = computeBaseRoot();
      var root = (baseRoot === '/' ? '' : baseRoot) + '/plans/wiki';
      return [
        {
          key: 'registrar',
          label: 'Registrar',
          pages: [
            {
              key: 'index',
              label: 'Index',
              path: root + '/registrar/index.md',
              requiredRoles: ['registrar', 'admin']
            },
            {
              key: 'enlistment',
              label: 'Enlistment',
              path: root + '/registrar/enlistment.md',
              requiredRoles: ['registrar', 'admin']
            }
          ]
        },
        {
          key: 'admissions',
          label: 'Admissions',
          pages: [
            {
              key: 'index',
              label: 'Index',
              path: root + '/admissions/index.md',
              requiredRoles: ['admissions', 'admin']
            }
          ]
        },
        {
          key: 'scholarships',
          label: 'Scholarships',
          pages: [
            {
              key: 'index',
              label: 'Index',
              path: root + '/scholarships/index.md',
              requiredRoles: ['scholarship', 'finance_admin', 'admin']
            }
          ]
        },
        {
          key: 'finance',
          label: 'Finance',
          pages: [
            {
              key: 'index',
              label: 'Index',
              path: root + '/finance/index.md',
              requiredRoles: ['finance', 'finance_admin', 'admin']
            }
          ]
        },
        {
          key: 'academics',
          label: 'Academics',
          pages: [
            {
              key: 'index',
              label: 'Index',
              path: root + '/academics/index.md',
              requiredRoles: ['faculty_admin', 'registrar', 'admin']
            }
          ]
        }
      ];
    }

    function getCategories() {
      // Return a fresh copy each time to avoid mutation side effects
      var cfg = _categoriesConfig();
      return JSON.parse(JSON.stringify(cfg));
    }

    function resolvePath(categoryKey, pageKey) {
      var cats = _categoriesConfig();
      var cat = null;
      for (var i = 0; i < cats.length; i++) {
        if (cats[i].key === categoryKey) { cat = cats[i]; break; }
      }
      if (!cat) return null;
      if (!pageKey) {
        // default page per category
        var def = _defaultPageKeyFor(categoryKey);
        var pg = _findPage(cat, def);
        return (pg && pg.path) ? pg.path : null;
      }
      var p = _findPage(cat, pageKey);
      return (p && p.path) ? p.path : null;
    }

    function _defaultPageKeyFor(categoryKey) {
      if (categoryKey === 'registrar') return 'enlistment';
      return 'index';
    }

    function _findPage(category, pageKey) {
      if (!category || !category.pages) return null;
      for (var i = 0; i < category.pages.length; i++) {
        if (category.pages[i].key === pageKey) return category.pages[i];
      }
      return null;
    }

    function fetchMarkdown(path) {
      if (!path) return $q.reject(new Error('Missing path'));
      // Fetch raw text, not JSON
      return $http.get(path, {
        responseType: 'text',
        transformResponse: [function (data) { return data; }],
        headers: { 'Accept': 'text/markdown, text/plain; charset=utf-8' }
      }).then(function (resp) {
        return (resp && resp.data) ? resp.data : '';
      });
    }

    function renderMarkdown(md) {
      try {
        if (window.marked && typeof window.marked.parse === 'function') {
          return window.marked.parse(md || '');
        }
        // Fallback: minimal escaping
        var t = (md || '').replace(/[&<>]/g, function (ch) {
          if (ch === '&') return '&amp;';
          if (ch === '<') return '<';
          if (ch === '>') return '>';
          return ch;
        });
        return '<pre class="whitespace-pre-wrap">' + t + '</pre>';
      } catch (e) {
        return '<pre class="whitespace-pre-wrap">' + (md || '') + '</pre>';
      }
    }

    function sanitize(html) {
      try {
        if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
          return window.DOMPurify.sanitize(html || '', { USE_PROFILES: { html: true } });
        }
        // Fallback: return as-is (caller should avoid trusting if sanitizer missing)
        return html || '';
      } catch (e) {
        return html || '';
      }
    }
  }

})();
