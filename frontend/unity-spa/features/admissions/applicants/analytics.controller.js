(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ApplicantsAnalyticsController', ApplicantsAnalyticsController);

  ApplicantsAnalyticsController.$inject = ['$scope', '$timeout', 'ApplicantsAnalyticsService', 'SchoolYearsService', 'CampusService', 'TermService'];
  function ApplicantsAnalyticsController($scope, $timeout, ApplicantsAnalyticsService, SchoolYearsService, CampusService, TermService) {
    var vm = this;

    vm.title = 'Applicants Analytics';
    vm.loading = false;
    vm.error = null;

    // Filters (on-page override)
    vm.filters = {
      // Term A (primary, supports multi), Term B (optional compare, supports multi)
      syidsA: [],
      syidsB: [],
      // Optional date range (Y-m-d)
      start: '',
      end: '',
      // Dimension filters
      campus: '',
      status: '',
      type: '',
      sub_type: '',
      search: ''
    };

    vm.terms = [];
    vm.campuses = [];
    vm.summaryA = null;
    vm.summaryB = null;

    vm.charts = {
      status: null,
      types: null,
      subTypes: null,
      campus: null,
      payments: null,
      waivers: null,
      daily: null
    };

    // Computed metrics (e.g., conversion rate)
    vm.metrics = {
      conversionA: null,
      conversionB: null,
      reservationA: null,
      reservationB: null
    };

    // Exposed handlers
    vm.load = load;
    vm.refresh = refresh;
    vm.clearFilters = clearFilters;
    vm.onCampusChange = onCampusChange;
    vm.onTermAChange = onTermAChange;
    vm.onTermBChange = onTermBChange;
    vm.clearCompare = clearCompare;
    vm.termLabel = termLabel;

    activate();

    function activate() {
      // Initialize campus list and default campus selection (if any)
      initCampus();

      // Load available terms (optionally by campus)
      loadTerms(vm.filters.campus).finally(function () {
        // Default Term A to the global selected term if present
        try {
          var tSel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
          var tid = (tSel && (tSel.intID !== undefined ? tSel.intID : tSel.id)) || '';
          if (tid && String(tid).trim() !== '') {
            var idNum = parseInt(tid, 10);
            if (!isNaN(idNum)) vm.filters.syidsA = [idNum];
          }
        } catch (e) {}

        // Initial load
        load();
      });

      // React to global campus changes from sidebar
      $scope.$on('campusChanged', function (event, data) {
        try {
          if (data && data.availableCampuses) {
            vm.campuses = data.availableCampuses;
          }
          var sc = data && data.selectedCampus ? data.selectedCampus : null;
          var id = (sc && sc.id !== undefined && sc.id !== null) ? parseInt(sc.id, 10) : null;
          vm.filters.campus = (id !== null && !isNaN(id)) ? id : '';
          loadTerms(vm.filters.campus).finally(load);
        } catch (e2) {
          load();
        }
      });
    }

    function initCampus() {
      try {
        if (CampusService && CampusService.init) {
          CampusService.init();
        }
        vm.campuses = (CampusService && CampusService.availableCampuses) ? CampusService.availableCampuses : [];
        if (!vm.filters.campus || ('' + vm.filters.campus).trim() === '') {
          var sc = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
          var id = (sc && sc.id !== undefined && sc.id !== null) ? parseInt(sc.id, 10) : null;
          if (id !== null && !isNaN(id)) vm.filters.campus = id;
        }
      } catch (e) {
        vm.campuses = [];
      }
    }

    function loadTerms(campusId) {
      vm.terms = [];
      try {
        var args = {};
        if (campusId !== null && campusId !== undefined && campusId !== '') {
          args.campus_id = campusId;
        }
        return SchoolYearsService.list(args).then(function (res) {
          var d = (res && res.data) ? res.data : res;
          vm.terms = Array.isArray(d) ? d : [];
          // Ensure selected terms exist (arrays for multi-select)
          ['syidsA', 'syidsB'].forEach(function (key) {
            var arr = Array.isArray(vm.filters[key]) ? vm.filters[key] : [];
            var filtered = arr.filter(function (id) {
              function _toNum(v) {
                try {
                  if (v === null || v === undefined) return null;
                  var s = String(v);
                  if (s.indexOf('number:') === 0) s = s.split(':')[1];
                  var n = parseInt(s, 10);
                  return isNaN(n) ? null : n;
                } catch (e) { return null; }
              }
              var idn = _toNum(id);
              return vm.terms.some(function (t) {
                var tid = (t && (t.intID !== undefined ? t.intID : t.id));
                return _toNum(tid) === idn;
              });
            });
            vm.filters[key] = filtered;
          });
        }).catch(function () {
          vm.terms = [];
          vm.filters.syidsA = [];
          vm.filters.syidsB = [];
        });
      } catch (e) {
        vm.terms = [];
        vm.filters.syidsA = [];
        vm.filters.syidsB = [];
        try { return Promise.resolve(vm.terms); } catch (_e) { return null; }
      }
    }

    function onCampusChange() {
      loadTerms(vm.filters.campus).finally(load);
    }

    function onTermAChange() {
      load();
    }

    function onTermBChange() {
      load();
    }

    function clearCompare() {
      vm.filters.syidsB = [];
      load();
    }

    function clearFilters() {
      var keepA = Array.isArray(vm.filters.syidsA) ? vm.filters.syidsA.slice() : [];
      vm.filters = {
        syidsA: keepA,
        syidsB: [],
        start: '',
        end: '',
        campus: vm.filters.campus || '',
        status: '',
        type: '',
        sub_type: '',
        search: ''
      };
      load();
    }

    // Debounced refresh
    var refreshTimer = null;
    function refresh() {
      if (refreshTimer) $timeout.cancel(refreshTimer);
      refreshTimer = $timeout(function () {
        load();
      }, 250);
    }

    function load() {
      vm.error = null;

      if (!Array.isArray(vm.filters.syidsA) || vm.filters.syidsA.length === 0) {
          vm.summaryA = vm.summaryB = null;
          vm.metrics = { conversionA: null, conversionB: null, reservationA: null, reservationB: null };
          destroyAllCharts();
        return;
      }

      vm.loading = true;
      ApplicantsAnalyticsService.summary(vm.filters)
        .then(function (res) {
          var d = (res && res.data) ? res.data : res;

          // Expect { terms: { syid: summary }, meta: {...} }
          var terms = d && d.terms ? d.terms : {};
          var meta  = d && d.meta ? d.meta : {};

          // Primary (A)
          if (meta && meta.primary_syids && meta.primary_syids.length) {
            vm.summaryA = terms['__combined_A__'] || null;
          } else if (Array.isArray(vm.filters.syidsA) && vm.filters.syidsA.length === 1) {
            vm.summaryA = terms[String(vm.filters.syidsA[0])] || null;
          } else {
            vm.summaryA = null;
          }

          // Compare (B)
          if (meta && meta.compare_syids && meta.compare_syids.length) {
            vm.summaryB = terms['__combined_B__'] || null;
          } else if (Array.isArray(vm.filters.syidsB) && vm.filters.syidsB.length === 1) {
            vm.summaryB = terms[String(vm.filters.syidsB[0])] || null;
          } else {
            vm.summaryB = null;
          }

          // Compute conversion metrics (Enrolled ÷ Reserved) for Term A and optional Term B
          try {
            var convA = computeConversion(vm.summaryA);
            var convB = vm.summaryB ? computeConversion(vm.summaryB) : null;

            // Compute reservation conversion metrics (Reserved ÷ (For Reservation + Reserved + Withdrawn*))
            var resA = computeReservationConversion(vm.summaryA);
            var resB = vm.summaryB ? computeReservationConversion(vm.summaryB) : null;

            vm.metrics = { conversionA: convA, conversionB: convB, reservationA: resA, reservationB: resB };
          } catch (e) {
            vm.metrics = { conversionA: null, conversionB: null, reservationA: null, reservationB: null };
          }
        })
        .catch(function (err) {
          vm.summaryA = vm.summaryB = null;
          vm.metrics = { conversionA: null, conversionB: null, reservationA: null, reservationB: null };
          destroyAllCharts();
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load analytics.';
        })
        .finally(function () {
          vm.loading = false;
          // After loading flag flips, ng-if renders canvases. Build charts on next digest.
          try {
            $timeout(function () {
              if (vm.filters.syidsA && vm.filters.syidsA.length && !vm.error) {
                buildCharts();
              }
            }, 0);
          } catch (_e) {}
        });
    }

    // -----------------------
    // Chart.js helpers
    // -----------------------
    function destroyChart(ref) {
      try {
        if (ref && typeof ref.destroy === 'function') ref.destroy();
      } catch (e) {}
    }

    function destroyAllCharts() {
      Object.keys(vm.charts).forEach(function (k) {
        destroyChart(vm.charts[k]);
        vm.charts[k] = null;
      });
    }

    function buildCharts() {
      // Basic safety
      if (!window.Chart) return;

      // Compose unified categories for compares
      var byStatusMap   = unionKeys(getPath(vm.summaryA, ['counts','by_status']), getPath(vm.summaryB, ['counts','by_status']));
      var byTypeMap     = unionKeys(getPath(vm.summaryA, ['counts','by_applicant_type']), getPath(vm.summaryB, ['counts','by_applicant_type']));
      var bySubTypeMap  = unionKeys(getPath(vm.summaryA, ['counts','by_applicant_sub_type']), getPath(vm.summaryB, ['counts','by_applicant_sub_type']));
      var byCampusMap   = unionKeys(getPath(vm.summaryA, ['counts','by_campus']), getPath(vm.summaryB, ['counts','by_campus']));

      // Status bar (compare via 2 datasets)
      buildCategoryChart('statusChart', 'status', 'bar', byStatusMap, 'by_status');

      // Applicant type bar
      buildCategoryChart('typesChart', 'types', 'bar', byTypeMap, 'by_applicant_type');

      // Sub-type bar
      buildCategoryChart('subTypesChart', 'subTypes', 'bar', bySubTypeMap, 'by_applicant_sub_type');

      // Campus bar
      buildCategoryChart('campusChart', 'campus', 'bar', byCampusMap, 'by_campus');

      // Payments bar (two categories: paid_application_fee, paid_reservation_fee)
      buildPaymentsChart();

      // Waivers bar (one category: waive_application_fee)
      buildWaiversChart();

      // Daily time series line (compare)
      buildDailyChart();
    }

    function unionKeys(mapA, mapB) {
      var labels = [];
      function addKeys(m) {
        if (!m) return;
        Object.keys(m).forEach(function (k) {
          if (labels.indexOf(k) === -1) labels.push(k);
        });
      }
      addKeys(mapA);
      addKeys(mapB);
      return labels;
    }

    function fetchSeries(summary, path, labels) {
      var m = getPath(summary, ['counts', path]);
      return labels.map(function (lbl) {
        var v = m && m.hasOwnProperty(lbl) ? m[lbl] : 0;
        return parseInt(v || 0, 10);
      });
    }

    function buildCategoryChart(canvasId, chartKey, type, labels, mapKey) {
      var ctx = getCtx(canvasId);
      if (!ctx) { destroyChart(vm.charts[chartKey]); vm.charts[chartKey] = null; return; }
      destroyChart(vm.charts[chartKey]);

      var ds = [];
      var paletteA = palette(0);
      var paletteB = palette(1);

      var dataA = fetchSeries(vm.summaryA, mapKey, labels);
      // Use categorical colors for doughnut and bar charts
      var isCategorical = (type === 'doughnut' || type === 'bar');
      var fillA = isCategorical ? categoricalFillColors(labels, 0) : paletteA.bg;
      var strokeA = isCategorical ? categoricalBorderColors(labels, 0) : paletteA.border;

      ds.push({
        label: labelFor('A'),
        data: dataA,
        backgroundColor: fillA,
        borderColor: strokeA,
        borderWidth: 1
      });

      if (vm.summaryB) {
        var dataB = fetchSeries(vm.summaryB, mapKey, labels);
        // For compares, use same categorical hues but different shade for Term B
        var isCategoricalB = (type === 'doughnut' || type === 'bar');
        var fillB = isCategoricalB ? categoricalFillColors(labels, 1) : paletteB.bg;
        var strokeB = isCategoricalB ? categoricalBorderColors(labels, 1) : paletteB.border;

        ds.push({
          label: labelFor('B'),
          data: dataB,
          backgroundColor: fillB,
          borderColor: strokeB,
          borderWidth: 1
        });
      }

      vm.charts[chartKey] = new Chart(ctx, {
        type: type,
        data: {
          labels: labels,
          datasets: ds
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' },
            tooltip: { mode: 'index', intersect: false }
          },
          scales: (type === 'bar') ? { y: { beginAtZero: true, ticks: { precision: 0 } } } : {}
        }
      });
    }

    function buildPaymentsChart() {
      var ctx = getCtx('paymentsChart');
      if (!ctx) { destroyChart(vm.charts.payments); vm.charts.payments = null; return; }
      destroyChart(vm.charts.payments);

      var labels = ['Paid Application Fee', 'Paid Reservation Fee'];
      var paletteA = palette(0);
      var paletteB = palette(1);

      var a = vm.summaryA && vm.summaryA.counts && vm.summaryA.counts.payment_flags || {};
      var b = vm.summaryB && vm.summaryB.counts && vm.summaryB.counts.payment_flags || {};

      var dataA = [
        parseInt(a.paid_application_fee || 0, 10),
        parseInt(a.paid_reservation_fee || 0, 10)
      ];

      var fillA = categoricalFillColors(labels, 0);
      var strokeA = categoricalBorderColors(labels, 0);
      var datasets = [{
        label: labelFor('A'),
        data: dataA,
        backgroundColor: fillA,
        borderColor: strokeA,
        borderWidth: 1
      }];

      if (vm.summaryB) {
        var dataB = [
          parseInt(b.paid_application_fee || 0, 10),
          parseInt(b.paid_reservation_fee || 0, 10)
        ];
        var fillB = categoricalFillColors(labels, 1);
        var strokeB = categoricalBorderColors(labels, 1);
        datasets.push({
          label: labelFor('B'),
          data: dataB,
          backgroundColor: fillB,
          borderColor: strokeB,
          borderWidth: 1
        });
      }

      vm.charts.payments = new Chart(ctx, {
        type: 'bar',
        data: { labels: labels, datasets: datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    }

    function buildWaiversChart() {
      var ctx = getCtx('waiversChart');
      if (!ctx) { destroyChart(vm.charts.waivers); vm.charts.waivers = null; return; }
      destroyChart(vm.charts.waivers);

      var labels = ['Waived Application Fee'];
      var paletteA = palette(0);
      var paletteB = palette(1);

      var a = vm.summaryA && vm.summaryA.counts && vm.summaryA.counts.waivers || {};
      var b = vm.summaryB && vm.summaryB.counts && vm.summaryB.counts.waivers || {};

      var dataA = [parseInt(a.waive_application_fee || 0, 10)];
      var fillA = categoricalFillColors(labels, 0);
      var strokeA = categoricalBorderColors(labels, 0);
      var datasets = [{
        label: labelFor('A'),
        data: dataA,
        backgroundColor: fillA,
        borderColor: strokeA,
        borderWidth: 1
      }];

      if (vm.summaryB) {
        var dataB = [parseInt(b.waive_application_fee || 0, 10)];
        var fillB = categoricalFillColors(labels, 1);
        var strokeB = categoricalBorderColors(labels, 1);
        datasets.push({
          label: labelFor('B'),
          data: dataB,
          backgroundColor: fillB,
          borderColor: strokeB,
          borderWidth: 1
        });
      }

      vm.charts.waivers = new Chart(ctx, {
        type: 'bar',
        data: { labels: labels, datasets: datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    }

    function buildDailyChart() {
      var ctx = getCtx('dailyChart');
      if (!ctx) { destroyChart(vm.charts.daily); vm.charts.daily = null; return; }
      destroyChart(vm.charts.daily);

      var paletteA = palette(0);
      var paletteB = palette(1);

      var dailyA = (vm.summaryA && vm.summaryA.timeseries && vm.summaryA.timeseries.daily_new_applications) || [];
      var dailyB = (vm.summaryB && vm.summaryB.timeseries && vm.summaryB.timeseries.daily_new_applications) || [];

      // Union of dates
      var dateLabels = unionDateLabels(dailyA, dailyB);

      var dataA = dateLabels.map(function (d) {
        var found = dailyA.find(function (x) { return String(x.date) === String(d); });
        return found ? parseInt(found.count || 0, 10) : 0;
      });
      var datasets = [{
        label: labelFor('A'),
        data: dataA,
        tension: 0.2,
        borderColor: paletteA.border,
        backgroundColor: paletteA.bg,
        fill: false
      }];

      if (vm.summaryB) {
        var dataB = dateLabels.map(function (d) {
          var found = dailyB.find(function (x) { return String(x.date) === String(d); });
          return found ? parseInt(found.count || 0, 10) : 0;
        });
        datasets.push({
          label: labelFor('B'),
          data: dataB,
          tension: 0.2,
          borderColor: paletteB.border,
          backgroundColor: paletteB.bg,
          fill: false
        });
      }

      vm.charts.daily = new Chart(ctx, {
        type: 'line',
        data: { labels: dateLabels, datasets: datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } },
            x: { ticks: { autoSkip: true, maxTicksLimit: 12 } }
          }
        }
      });
    }

    function unionDateLabels(a, b) {
      var set = {};
      (a || []).forEach(function (r) { set[String(r.date)] = true; });
      (b || []).forEach(function (r) { set[String(r.date)] = true; });
      return Object.keys(set).sort();
    }

    function getCtx(id) {
      var el = document.getElementById(id);
      if (!el) return null;
      return el.getContext ? el.getContext('2d') : null;
    }

    function getPath(obj, path) {
      try {
        var p = obj;
        for (var i = 0; i < path.length; i++) {
          if (!p || !p.hasOwnProperty(path[i])) return null;
          p = p[path[i]];
        }
        return p;
      } catch (e) {
        return null;
      }
    }

    // -----------------------
    // Derived metrics helpers
    // -----------------------
    function statusCount(summary, key) {
      try {
        var map = getPath(summary, ['counts', 'by_status']) || {};
        var lower = {};
        Object.keys(map).forEach(function (k) {
          lower[String(k).toLowerCase()] = parseInt(map[k] || 0, 10);
        });
        var val = lower[String(key).toLowerCase()];
        return isNaN(val) ? 0 : val;
      } catch (e) {
        return 0;
      }
    }

    function computeConversion(summary) {
      if (!summary) return null;
      var reserved = statusCount(summary, 'reserved');
      var enrolled = statusCount(summary, 'enrolled');
      var withdrawnBefore = statusCount(summary, 'withdrawn before');
      var withdrawnAfter = statusCount(summary, 'withdrawn after');
      var withdrawnEnd = statusCount(summary, 'withdrawn end');
      var enlisted = statusCount(summary, 'enlisted');
      var for_enrollment = statusCount(summary, 'for enrollment');
      var confirmed = statusCount(summary, 'confirmed');
      enrolled += withdrawnBefore + withdrawnAfter + withdrawnEnd;
      // Ever reserved = reserved + enrolled + withdrawn variants
      reserved += enrolled + enlisted + for_enrollment + confirmed;
      var percent = reserved > 0 ? Math.round((enrolled / reserved) * 10000) / 100 : 0;
      return { enrolled: enrolled, reserved: reserved, percent: percent };
    }

    // Conversion Rate for Reservation:
    // reserved / (for_reservation + reserved + withdrawn_before + withdrawn_after + withdrawn_end)
    function computeReservationConversion(summary) {
      if (!summary) return null;
      var reserved = statusCount(summary, 'reserved');
      var for_reservation = statusCount(summary, 'for reservation');      
      var withdrawnBefore = statusCount(summary, 'withdrawn before');
      var withdrawnAfter = statusCount(summary, 'withdrawn after');
      var withdrawnEnd = statusCount(summary, 'withdrawn end');
      var enrolled = statusCount(summary, 'enrolled');      
      var enlisted = statusCount(summary, 'enlisted');
      var for_enrollment = statusCount(summary, 'for enrollment');
      var confirmed = statusCount(summary, 'confirmed');
      reserved += enrolled + enlisted + for_enrollment + confirmed + withdrawnBefore + withdrawnAfter + withdrawnEnd;
      var denom = reserved + for_reservation;
      var percent = denom > 0 ? Math.round((reserved / denom) * 10000) / 100 : 0;
      return {
        reserved: reserved,
        for_reservation: for_reservation,
        withdrawnBefore: withdrawnBefore,
        withdrawnAfter: withdrawnAfter,
        withdrawnEnd: withdrawnEnd,
        percent: percent
      };
    }

    function labelFor(which) {
      if (which === 'A') {
        var arrA = Array.isArray(vm.filters.syidsA) ? vm.filters.syidsA : [];
        return labelFromIds(arrA) || 'Term A';
      }
      if (which === 'B') {
        var arrB = Array.isArray(vm.filters.syidsB) ? vm.filters.syidsB : [];
        return labelFromIds(arrB) || 'Term B';
      }
      return which;
    }
    function labelFromIds(ids) {
      if (!ids || !ids.length) return '';
      if (ids.length > 1) return 'Combined (' + ids.length + ' terms)';
      return termDisplay(ids[0]);
    }

    function termDisplay(syid) {
      if (!syid) return '';
      var _toNum = function (v) {
        try {
          if (v === null || v === undefined) return null;
          var s = String(v);
          if (s.indexOf('number:') === 0) s = s.split(':')[1];
          var n = parseInt(s, 10);
          return isNaN(n) ? null : n;
        } catch (e) { return null; }
      };
      var sid = _toNum(syid);
      var t = vm.terms.find(function (x) {
        var id = (x && (x.intID !== undefined ? x.intID : x.id));
        return _toNum(id) === sid;
      });
      return t ? (t.term_label || termLabel(t)) : '';
    }

    function termLabel(t) {
      if (!t) return '-';
      var st = (t.term_student_type !== undefined && t.term_student_type !== null) ? String(t.term_student_type).trim() : '';
      var sem = (t.enumSem !== undefined && t.enumSem !== null) ? String(t.enumSem).trim() : '';
      var lbl = (t.term_label !== undefined && t.term_label !== null) ? String(t.term_label).trim() : '';
      var ys = (t.strYearStart !== undefined && t.strYearStart !== null) ? String(t.strYearStart).trim() : '';
      var ye = (t.strYearEnd !== undefined && t.strYearEnd !== null) ? String(t.strYearEnd).trim() : '';
      var y = (ys && ye) ? (ys + '-' + ye) : (ys || ye || '');
      var parts = [st, sem, lbl, y].filter(function (x) { return x && x.length; });
      return parts.length ? parts.join(' ') : '-';
    }

    function palette(idx) {
      // Two contrasting palettes for compare datasets
      var P = [
        { bg: 'rgba(59, 130, 246, 0.5)', border: 'rgba(59, 130, 246, 1)' },   // blue-500
        { bg: 'rgba(16, 185, 129, 0.5)', border: 'rgba(16, 185, 129, 1)' }    // emerald-500
      ];
      return P[idx % P.length];
    }

    // Generate categorical colors per label index using HSLA hues.
    // variant 0 => slightly lighter; variant 1 => slightly darker (for Term B ring)
    function colorForIndex(idx, total, variant, alpha) {
      var hue = Math.round((idx * 360 / Math.max(total || 1, 1)) % 360);
      var sat = 65;
      var light = (variant === 1) ? 47 : 57;
      var a = (alpha === undefined || alpha === null) ? 1 : alpha;
      return 'hsla(' + hue + ', ' + sat + '%, ' + light + '%, ' + a + ')';
    }

    function categoricalFillColors(labels, variant) {
      var n = (labels && labels.length) ? labels.length : 0;
      var arr = [];
      for (var i = 0; i < n; i++) {
        arr.push(colorForIndex(i, n, variant, 0.6));
      }
      return arr;
    }

    function categoricalBorderColors(labels, variant) {
      var n = (labels && labels.length) ? labels.length : 0;
      var arr = [];
      for (var i = 0; i < n; i++) {
        arr.push(colorForIndex(i, n, variant, 1));
      }
      return arr;
    }
  }

})();
