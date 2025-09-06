(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentApplicantViewerController', StudentApplicantViewerController);

  StudentApplicantViewerController.$inject = ['$location', '$http', 'APP_CONFIG', 'StorageService', 'Upload', '$timeout', 'ProgramsService', 'InitialRequirementsService', 'ApplicantJourneyService'];
  function StudentApplicantViewerController($location, $http, APP_CONFIG, StorageService, Upload, $timeout, ProgramsService, InitialRequirementsService, ApplicantJourneyService) {
    var vm = this;

    // Auth state
    vm.state = null;

    // Core state
    vm.loading = true;
    vm.error = null;

    vm.user = null;
    vm.status = null;
    vm.created_at = null;
    vm.updated_at = null;
    vm.applicant_data = null;

    vm.applicant_type = null;
    vm.applicant_type_name = null;
    vm.paid_application_fee = null;
    vm.paid_reservation_fee = null;

    // Interview (read-only summary)
    vm.interviewed = false;
    vm.interview = null; // { scheduled_at, assessment, completed_at } or null

    // Program display resolution
    vm.program_name = null;

    // Rendered sections from applicant_data
    vm.sections = [];

    // Applicant Journey
    vm.applicant_data_id = null;
    vm.journey = [];
    vm.journeyLoading = false;
    vm.journeyError = null;
    vm.loadJourney = loadJourney;

    // Initial requirements (public hash flow)
    vm.hash = null;
    vm.irLoading = false;
    vm.irError = null;
    vm.initial_requirements = [];
    vm.photo2x2Url = null;

    // Upload state
    vm.uploading = {};
    vm.progress = {};
    vm.accept = 'application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,image/*';

    // Methods
    vm.reload = load;
    vm.back = function () { $location.path('/student/dashboard'); };
    vm.loadInitialRequirements = loadInitialRequirements;
    vm.onFilePicked = onFilePicked;

    activate();

    function activate() {
      try { vm.state = StorageService.getJSON('loginState'); } catch (e) { vm.state = null; }
      if (!vm.state || !vm.state.loggedIn) {
        $location.path('/login');
        return;
      }
      load();
    }

    function load() {
      vm.loading = true;
      vm.error = null;

      var url = (APP_CONFIG && APP_CONFIG.API_BASE ? APP_CONFIG.API_BASE : '') + '/student/applicant';
      var body = {};

      // Provide multiple identifiers to maximize backend resolution success
      if (vm.state) {
        // Token/username (parity with /student/viewer)
        if (vm.state.username) {
          body.token = vm.state.username;
          // Also send explicitly as username for backend convenience
          body.username = vm.state.username;
        }

        // Various id shapes often stored in loginState across flows
        if (vm.state.user_id != null) body.student_id = vm.state.user_id;
        if (vm.state.intID != null && body.student_id == null) body.intID = vm.state.intID;
        if (vm.state.id != null && body.student_id == null) body.id = vm.state.id;

        // Student number if available
        if (vm.state.student_number) body.student_number = vm.state.student_number;

        // Email if available (backend will resolve via strEmail)
        if (vm.state.email) body.email = vm.state.email;
        if (vm.state.strEmail) body.strEmail = vm.state.strEmail;
      }

      $http.post(url, body)
        .then(function (resp) {
          var container = (resp && resp.data) ? resp.data : resp;
          if (!container || container.success === false) {
            vm.error = (container && container.message) || 'Failed to load applicant details.';
            return;
          }
          var d = container.data || container;

          vm.user = d.user || null;
          vm.status = d.status || null;
          vm.created_at = d.created_at || null;
          vm.updated_at = d.updated_at || null;
          vm.applicant_data = d.applicant_data || null;

          // surfaced fields
          vm.applicant_type = (typeof d.applicant_type !== 'undefined') ? d.applicant_type : null;
          vm.applicant_type_name = d.applicant_type_name || null;
          vm.paid_application_fee = (typeof d.paid_application_fee !== 'undefined') ? d.paid_application_fee : null;
          vm.paid_reservation_fee = (typeof d.paid_reservation_fee !== 'undefined') ? d.paid_reservation_fee : null;

          // interview summary
          vm.interviewed = !!d.interviewed;
          vm.interview = d.interview_summary || null;

          // initial requirements
          vm.hash = d.hash || null;

          try { resolveProgramName(); } catch (e) {}
          try { vm.sections = buildSections(vm.applicant_data, vm.user); } catch (e) { vm.sections = []; }

          // Resolve applicant_data_id for Journey
          try {
            vm.applicant_data_id = (typeof d.applicant_data_id !== 'undefined' && d.applicant_data_id !== null)
              ? parseInt(d.applicant_data_id, 10) : null;
            if (vm.applicant_data_id) {
              loadJourney();
            } else {
              vm.journey = [];
            }
          } catch (e) { vm.journey = []; }

          if (vm.hash) {
            try { loadInitialRequirements(); } catch (e) {}
          } else {
            vm.initial_requirements = [];
            vm.photo2x2Url = null;
          }
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load applicant details.';
        })
        .finally(function () { vm.loading = false; });
    }

    // Resolve program display name from applicant_data (parity with admin view)
    function resolveProgramName() {
      vm.program_name = null;
      try {
        var ad = vm.applicant_data || {};
        if (ad.program && String(ad.program).trim() !== '') {
          vm.program_name = ad.program;
          return;
        }
        if (ad.type && String(ad.type).trim() !== '') {
          vm.program_name = ad.type;
          return;
        }
        var rawId = (ad.type_id !== undefined && ad.type_id !== null) ? ad.type_id : (ad.program_id !== undefined ? ad.program_id : null);
        var typeId = parseInt(rawId, 10);
        if (!isNaN(typeId) && typeId > 0 && ProgramsService && ProgramsService.get) {
          ProgramsService.get(typeId)
            .then(function (res) {
              var p = (res && res.data) ? res.data : res;
              if (p) {
                vm.program_name = p.strProgramDescription || p.short_name || p.strProgramCode || ('Program #' + typeId);
              }
            })
            .catch(function () { /* ignore */ });
        }
      } catch (e) { /* ignore */ }
    }

    // Load Initial Requirements list by public hash and derive 2x2 photo url
    function loadInitialRequirements() {
      if (!vm.hash) {
        vm.initial_requirements = [];
        vm.photo2x2Url = null;
        return;
      }
      vm.irLoading = true;
      vm.irError = null;

      InitialRequirementsService.getList(vm.hash)
        .then(function (res) {
          var container = (res && res.data) ? res.data : res;
          var data = (container && container.data) ? container.data : container;
          vm.initial_requirements = (data && data.requirements) ? data.requirements : [];

          // Derive 2x2 photo URL from submitted initial requirements
          try {
            var url = null;
            if (Array.isArray(vm.initial_requirements)) {
              for (var i = 0; i < vm.initial_requirements.length; i++) {
                var req = vm.initial_requirements[i] || {};
                var submitted = !!req.submitted_status;
                var n = ((req.name || req.description || '') + '').toLowerCase();
                if (submitted && n) {
                  if (/2\s*x\s*2/.test(n) || n.indexOf('2x2') !== -1 || n.indexOf('2 x 2') !== -1) {
                    url = req.file_link || null;
                    if (url) break;
                  }
                }
              }
            }
            vm.photo2x2Url = url;
          } catch (e) {
            vm.photo2x2Url = null;
          }
        })
        .catch(function (err) {
          vm.irError = (err && (err.message || (err.data && err.data.message))) || 'Failed to load initial requirements.';
          vm.initial_requirements = [];
          vm.photo2x2Url = null;
        })
        .finally(function () { vm.irLoading = false; });
    }

    // File upload using public hash endpoint (student self-upload)
    function onFilePicked(item, file) {
      if (!vm.hash) return;
      if (!item || !file) return;

      // Basic client-side validation to mirror backend constraints
      try {
        var maxBytes = 10 * 1024 * 1024; // 10MB
        if (file.size > maxBytes) {
          try { Swal.fire('Invalid File', 'Maximum file size is 10MB.', 'warning'); } catch (e) { alert('Maximum file size is 10MB.'); }
          return;
        }
        var allowed = [
          'application/pdf',
          'application/vnd.ms-excel',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'text/csv'
        ];
        var name = (file.name || '').toLowerCase();
        var ok = (file.type && file.type.indexOf('image/') === 0)
          || allowed.indexOf(file.type) !== -1
          || /\.(pdf|xls|xlsx|csv|jpg|jpeg|png|gif|webp)$/i.test(name);
        if (!ok) {
          try { Swal.fire('Invalid File', 'Only PDF, Excel (xls, xlsx, csv), and image files are allowed.', 'warning'); } catch (e) { alert('Only PDF, Excel (xls, xlsx, csv), and image files are allowed.'); }
          return;
        }
      } catch (e) { /* ignore */ }

      vm.uploading[item.app_req_id] = true;
      vm.progress[item.app_req_id] = 0;

      var url = (APP_CONFIG && APP_CONFIG.API_BASE ? APP_CONFIG.API_BASE : '') +
        '/public/initial-requirements/' + encodeURIComponent(vm.hash) + '/upload/' + encodeURIComponent(item.app_req_id);

      Upload.upload({
        url: url,
        data: { file: file }
      }).then(function () {
        try { Swal.fire('Uploaded', 'File uploaded successfully.', 'success'); } catch (e) { /* ignore */ }
        $timeout(function () {
          loadInitialRequirements();
        }, 200);
      }, function (err) {
        var msg = (err && err.data && (err.data.message || (err.data.errors && err.data.errors.file && err.data.errors.file[0])) ) || 'Upload failed';
        try { Swal.fire('Error', msg, 'error'); } catch (e) { alert(msg); }
      }, function (evt) {
        if (evt && evt.total > 0) {
          vm.progress[item.app_req_id] = Math.min(100, Math.round(100.0 * evt.loaded / evt.total));
        }
      }).finally(function () {
        $timeout(function () {
          vm.uploading[item.app_req_id] = false;
        }, 200);
      });
    }

    // Applicant Journey loader
    function loadJourney() {
      if (!vm.applicant_data_id) {
        vm.journey = [];
        return;
      }
      vm.journeyLoading = true;
      vm.journeyError = null;
      try {
        ApplicantJourneyService.listByApplicantDataStudent(vm.applicant_data_id)
          .then(function (res) {
            var data = (res && res.data) ? res.data : res;
            var rows = Array.isArray(data) ? data : (data && data.data ? data.data : []);
            vm.journey = rows || [];
          })
          .catch(function (err) {
            vm.journey = [];
            vm.journeyError = (err && (err.message || (err.data && err.data.message))) || 'Failed to load applicant journey.';
          })
          .finally(function () { vm.journeyLoading = false; });
      } catch (e) {
        vm.journeyLoading = false;
        vm.journeyError = 'Failed to load applicant journey.';
      }
    }

    // Helpers to organize fields (parity with admin view)
    function buildSections(data, user) {
      var sections = [];
      data = data || {};

      // Known groupings by common payload fields
      var groups = [
        { key: 'identity',     title: 'Identity',           fields: ['first_name','middle_name','last_name','suffix','gender','date_of_birth','dob'] },
        { key: 'contact',      title: 'Contact',            fields: ['email','mobile_number','mobile','phone','telephone'] },
        { key: 'address',      title: 'Address',            fields: ['address','city','state','province','country','zip','zipcode','postal_code'] },
        { key: 'program',      title: 'Program / Type',     fields: ['type_id','type','program','student_type','intTuitionYear','track','strand','campus'] },
        { key: 'hs',           title: 'High School',        fields: ['high_school','high_school_address','high_school_attended'] },
        { key: 'shs',          title: 'Senior High School', fields: ['senior_high','senior_high_address','senior_high_attended'] },
        { key: 'meta',         title: 'Submission Meta',    fields: ['_server'] },
      ];

      // Seed with user fallbacks where appropriate
      var seeded = Object.assign({}, data);
      if (user) {
        if (!seeded.email && user.strEmail) seeded.email = user.strEmail;
        if (!seeded.mobile_number && (user.strMobileNumber || user.strPhoneNumber)) {
          seeded.mobile_number = user.strMobileNumber || user.strPhoneNumber;
        }
        if (!seeded.gender && user.enumGender) seeded.gender = user.enumGender;
        if (!seeded.date_of_birth && user.dteBirthDate) seeded.date_of_birth = user.dteBirthDate;
        if (!seeded.campus && user.campus) seeded.campus = user.campus;
        if (!seeded.student_type && user.student_type) seeded.student_type = user.student_type;
        if (!seeded.address && user.strAddress) seeded.address = user.strAddress;
      }

      // Build items for each group
      groups.forEach(function(g){
        var items = [];
        g.fields.forEach(function(f){
          if (f === '_server' && typeof seeded._server === 'object' && seeded._server) {
            // Expand server meta
            Object.keys(seeded._server).forEach(function(sk){
              items.push({
                label: toTitle(sk.replace(/^_/,'')),
                value: formatValue(seeded._server[sk])
              });
            });
          } else if (Object.prototype.hasOwnProperty.call(seeded, f) && seeded[f] !== undefined && seeded[f] !== null && seeded[f] !== '') {
            items.push({
              label: toTitle(f),
              value: formatValue(seeded[f], f)
            });
          }
        });
        if (items.length) {
          sections.push({ title: g.title, items: items });
        }
      });

      // Remaining fields -> Other Fields
      var used = new Set(groups.reduce(function(acc, g){
        g.fields.forEach(function(f){ acc.push(f); });
        return acc;
      }, []));
      used.add('_server'); // already expanded if present

      var other = [];
      Object.keys(seeded).forEach(function(k){
        if (!used.has(k)) {
          other.push({
            label: toTitle(k),
            value: formatValue(seeded[k], k)
          });
        }
      });
      if (other.length) {
        sections.push({ title: 'Other Fields', items: other });
      }

      return sections;
    }

    function toTitle(key) {
      if (!key) return '';
      // replace underscores / hyphens, then title case
      var s = ('' + key).replace(/[_\-]+/g, ' ').trim();
      // simple camelCase splitter
      s = s.replace(/([a-z0-9])([A-Z])/g, '$1 $2');
      return s.replace(/\w\S*/g, function(t){ return t.charAt(0).toUpperCase() + t.substr(1).toLowerCase(); });
    }

    function isDateKey(k) {
      return /date|dob|birth/i.test(k || '');
    }

    function formatValue(val, key) {
      if (val === null || val === undefined || val === '') return '-';
      // If the key is a program identifier, prefer the resolved program name
      if (key === 'type_id' || key === 'program_id') {
        if (vm.program_name && String(vm.program_name).trim() !== '') {
          return vm.program_name;
        }
      }
      if (Array.isArray(val)) {
        return val.length ? val.join(', ') : '-';
      }
      if (typeof val === 'object') {
        try { return JSON.stringify(val, null, 2); } catch (e) { return String(val); }
      }
      // date-ish keys
      if (key && isDateKey(key)) {
        // leave raw; Angular date filter in template can format when it's recognizable
        return val;
      }
      return String(val);
    }
  }

})();
