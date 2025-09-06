(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SchedulesController', SchedulesController);

  SchedulesController.$inject = ['$location', '$window', '$routeParams', '$http', 'StorageService', 'SchedulesService', 'RoleService', 'ToastService'];
  function SchedulesController($location, $window, $routeParams, $http, StorageService, SchedulesService, RoleService, ToastService) {
    console.log('SchedulesController starting to load...'); // Debug logging
    var vm = this;

    // Initialize basic properties first to ensure the controller loads
    vm.pageTitle = 'Room Schedules';
    vm.rows = [];
    vm.classrooms = [];
    vm.academicYears = [];
    vm.availableClasslists = [];
    vm.allClasslists = []; // For filtering all classlists
    vm.blockSections = []; // For block section selection
    vm.form = {};
    vm.filters = {};
    vm.q = '';
    vm.loading = false;
    vm.saving = false;
    vm.error = null;
    vm.success = null;

    console.log('SchedulesController initialized with pageTitle:', vm.pageTitle); // Debug logging

    vm.state = StorageService.getJSON('loginState');

    // Determine view mode based on route
    vm.isEdit = !!$routeParams.id;
    vm.isAdd = $location.path().indexOf('/add') !== -1;
    vm.isForm = vm.isEdit || vm.isAdd;
    vm.isList = !vm.isForm;

    // Titles
    vm.pageTitle = vm.isList ? 'Room Schedules' : 
                   vm.isEdit ? 'Edit Schedule' : 'Add New Schedule';

    // Data properties
    vm.rows = [];
    vm.classrooms = [];
    vm.academicYears = [];
    vm.availableClasslists = [];
    vm.form = {};
    vm.filters = {};
    vm.q = '';
    vm.loading = false;
    vm.saving = false;
    vm.error = null;
    vm.success = null;

    // Day mapping
    vm.days = {
      1: 'Monday',
      2: 'Tuesday', 
      3: 'Wednesday',
      4: 'Thursday',
      5: 'Friday',
      6: 'Saturday',
      7: 'Sunday'
    };

    // Class types
    vm.classTypes = [
      { value: 'lect', label: 'Lecture' },
      { value: 'lab', label: 'Laboratory' }
    ];

    // Time slots (similar to CI3 system)
    vm.timeSlots = [
      '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
      '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
      '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
      '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
      '19:00', '19:30', '20:00', '20:30', '21:00', '21:30'
    ];

    // Permissions - simplified for debugging
    vm.canCreate = true; // RoleService.hasRole(['registrar', 'admin']);
    vm.canEdit = true; // RoleService.hasRole(['registrar', 'admin']);
    vm.canDelete = true; // RoleService.hasRole(['registrar', 'admin']);

    // Check permissions for form access - temporarily disabled for debugging
    /*
    if (vm.isForm && !vm.canCreate && !vm.canEdit) {
      ToastService.error('You do not have permission to create or edit schedules.');
      $location.path('/schedules');
      return;
    }
    */

    console.log('About to call init()'); // Debug logging
    // === INITIALIZATION ===
    init();

    function init() {
      console.log('init() called, isForm:', vm.isForm, 'isEdit:', vm.isEdit); // Debug logging
      
      if (vm.isForm) {
        // Load classrooms for form
        console.log('Loading classrooms...'); // Debug logging
        loadClassrooms();
        
        // Initialize form with default values
        vm.form = {
          strDay: 1, // Monday
          enumClassType: 'lect',
          intSem: null,
          intClasslistID: null
        };
        
        // Load academic years first
        loadAcademicYears();
        
        // Load schedule data if editing
        if (vm.isEdit) {
          console.log('Loading schedule for edit...'); // Debug logging
          loadSchedule();
        }
      } else {
        // Load schedule list
        console.log('Loading schedule list...'); // Debug logging
        loadAcademicYears(); // Also load for filter dropdown
        loadAllClasslists(); // Load classlists for filtering
        search();
      }
    }

    // === LIST VIEW METHODS ===
    function search() {
      console.log('SchedulesController.search called'); // Debug logging
      vm.loading = true;
      vm.error = null;
      
      var params = {
        search: vm.q
      };
      
      // Apply filters
      if (vm.filters.intSem) {
        params.intSem = vm.filters.intSem;
      }
      if (vm.filters.room_id) {
        params.room_id = vm.filters.room_id;
      }
      if (vm.filters.day) {
        params.day = vm.filters.day;
      }
      if (vm.filters.class_type) {
        params.class_type = vm.filters.class_type;
      }
      if (vm.filters.classlist_id) {
        params.classlist_id = vm.filters.classlist_id;
      }
      
      console.log('Search params:', params); // Debug logging
      
      return SchedulesService.list(params)
        .then(function (data) {
          console.log('Search response data:', data); // Debug logging
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
          } else if (angular.isArray(data)) {
            vm.rows = data;
          } else if (data && angular.isArray(data.rows)) {
            vm.rows = data.rows;
          } else {
            vm.rows = [];
          }
          console.log('Final vm.rows:', vm.rows); // Debug logging
        })
        .catch(function (err) {
          console.error('SchedulesController.search error:', err); // Debug logging
          vm.error = 'Failed to load schedules.';
          vm.rows = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    vm.search = search;

    vm.add = function () {
      $location.path('/schedules/add');
    };

    vm.edit = function (row) {
      if (!row || !row.intRoomSchedID) {
        ToastService.error('Invalid schedule data.');
        return;
      }
      $location.path('/schedules/' + row.intRoomSchedID + '/edit');
    };

    vm.delete = function (row) {
      if (!row || !row.intRoomSchedID) {
        ToastService.error('Invalid schedule data.');
        return;
      }

      var scheduleInfo = (row.strScheduleCode || 'this schedule') + 
                        ' (' + (vm.days[row.strDay] || 'Unknown day') + 
                        ' ' + (row.dteStart || '') + '-' + (row.dteEnd || '') + ')';

      if (!$window.confirm('Are you sure you want to delete "' + scheduleInfo + '"?')) {
        return;
      }

      vm.loading = true;
      vm.error = null;
      vm.success = null;

      SchedulesService.delete(row.intRoomSchedID)
        .then(function (response) {
          if (response && response.success !== false) {
            ToastService.success('Schedule deleted successfully.');
            search(); // Reload the list
          } else {
            var errorMsg = (response && response.message) ? response.message : 'Delete operation failed.';
            vm.error = errorMsg;
            ToastService.error(errorMsg);
          }
        })
        .catch(function (err) {
          console.error('Delete error:', err);
          var msg = 'Failed to delete schedule.';
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          }
          vm.error = msg;
          ToastService.error(msg);
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    // === FORM VIEW METHODS ===
    function loadAcademicYears() {
      SchedulesService.getAcademicYears()
        .then(function(data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.academicYears = data.data;
            console.log('Loaded academic years:', vm.academicYears); // Debug logging
          } else {
            vm.error = 'Failed to load academic years.';
            vm.academicYears = [];
          }
        })
        .catch(function(err) {
          vm.error = 'Failed to load academic years.';
          vm.academicYears = [];
          console.error('SchedulesController loadAcademicYears error:', err);
        });
    }

    function loadAvailableClasslists() {
      if (!vm.form.intSem) {
        vm.availableClasslists = [];
        return;
      }

      vm.loadingClasslists = true;
      SchedulesService.getAvailableClasslists(vm.form.intSem, vm.form.blockSectionID)
        .then(function(data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.availableClasslists = data.data;
            console.log('Loaded available classlists:', vm.availableClasslists); // Debug logging
          } else {
            vm.error = 'Failed to load available classlists.';
            vm.availableClasslists = [];
          }
        })
        .catch(function(err) {
          vm.error = 'Failed to load available classlists.';
          vm.availableClasslists = [];
          console.error('SchedulesController loadAvailableClasslists error:', err);
        })
        .finally(function() {
          vm.loadingClasslists = false;
        });
    }

    // Watch for academic year changes to reload classlists
    vm.onAcademicYearChange = function() {
      vm.form.intClasslistID = null; // Reset classlist selection
      vm.form.blockSectionID = null; // Reset block section selection
      loadAvailableClasslists();
      loadBlockSections();
    };

    // Watch for block section changes to reload classlists
    vm.onBlockSectionChange = function() {
      vm.form.intClasslistID = null; // Reset classlist selection
      loadAvailableClasslists(); // Reload classlists filtered by block section program
    };
    function loadClassrooms() {
      // Load classrooms using $http directly to avoid dependency issues
      var state = StorageService.getJSON('loginState');
      var headers = { "Accept": "application/json" };
      if (state && state.faculty_id) {
        headers["X-Faculty-ID"] = state.faculty_id;
      } else {
        headers["X-Faculty-ID"] = "smssuperadmin";
      }

      // Use APP_CONFIG to get the base URL
      var API_BASE = 'http://127.0.0.1:8000/api/v1'; // Fallback if APP_CONFIG fails
      if (window.API_BASE) {
        API_BASE = window.API_BASE;
      }

      $http.get(API_BASE + '/classroom', { 
        headers: headers 
      })
      .then(function(response) {
        var data = response.data;
        if (data && data.success !== false && angular.isArray(data.data)) {
          vm.classrooms = data.data;
        } else if (angular.isArray(data)) {
          vm.classrooms = data;
        } else {
          vm.classrooms = [];
        }
        console.log('Loaded classrooms:', vm.classrooms); // Debug logging
      })
      .catch(function(err) {
        vm.error = 'Failed to load classrooms.';
        vm.classrooms = [];
        console.error('SchedulesController loadClassrooms error:', err);
      });
    }

    function loadAllClasslists() {
      SchedulesService.getAllClasslists()
        .then(function(data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.allClasslists = data.data;
          } else if (angular.isArray(data)) {
            vm.allClasslists = data;
          } else {
            vm.allClasslists = [];
          }
          console.log('Loaded all classlists for filtering:', vm.allClasslists); // Debug logging
        })
        .catch(function(err) {
          vm.error = 'Failed to load classlists.';
          vm.allClasslists = [];
          console.error('SchedulesController loadAllClasslists error:', err);
        });
    }

    function loadBlockSections() {
      if (!vm.form.intSem) {
        vm.blockSections = [];
        return;
      }

      SchedulesService.getBlockSections(vm.form.intSem)
        .then(function(data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.blockSections = data.data;
          } else if (angular.isArray(data)) {
            vm.blockSections = data;
          } else {
            vm.blockSections = [];
          }
          console.log('Loaded block sections:', vm.blockSections); // Debug logging
        })
        .catch(function(err) {
          vm.error = 'Failed to load block sections.';
          vm.blockSections = [];
          console.error('SchedulesController loadBlockSections error:', err);
        });
    }

    function loadSchedule() {
      vm.loading = true;
      vm.error = null;
      
      SchedulesService.get($routeParams.id)
        .then(function (data) {
          if (data && data.success !== false && data.data) {
            vm.form = angular.copy(data.data);
            // Convert to form-friendly format
            if (vm.form.intRoomID) {
              vm.form.intRoomID = vm.form.intRoomID.toString();
            }
            if (vm.form.strDay) {
              vm.form.strDay = parseInt(vm.form.strDay, 10);
            }
            if (vm.form.intSem) {
              vm.form.intSem = vm.form.intSem.toString();
            }
            if (vm.form.intClasslistID) {
              vm.form.intClasslistID = vm.form.intClasslistID.toString();
            }
            if (vm.form.blockSectionID) {
              vm.form.blockSectionID = vm.form.blockSectionID.toString();
            }
            
            // Load available classlists and block sections for the selected academic year
            if (vm.form.intSem) {
              loadAvailableClasslists();
              loadBlockSections();
            }
          } else {
            vm.error = 'Schedule not found.';
            ToastService.error('Schedule not found.');
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load schedule.';
          ToastService.error('Failed to load schedule.');
          console.error('SchedulesController loadSchedule error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    vm.save = function () {
      if (vm.saving) return;

      // Basic validation
      if (!vm.form.intRoomID) {
        ToastService.error('Classroom is required.');
        return;
      }

      if (!vm.form.intClasslistID) {
        ToastService.error('Classlist is required.');
        return;
      }

      if (!vm.form.intSem) {
        ToastService.error('Academic year is required.');
        return;
      }

      if (!vm.form.blockSectionID) {
        ToastService.error('Block section is required.');
        return;
      }

      if (!vm.form.strScheduleCode || !vm.form.strScheduleCode.trim()) {
        ToastService.error('Schedule code is required.');
        return;
      }

      if (!vm.form.dteStart) {
        ToastService.error('Start time is required.');
        return;
      }

      if (!vm.form.dteEnd) {
        ToastService.error('End time is required.');
        return;
      }

      // Validate time order
      if (vm.form.dteStart >= vm.form.dteEnd) {
        ToastService.error('End time must be after start time.');
        return;
      }

      vm.saving = true;
      vm.error = null;

      var payload = {
        intRoomID: parseInt(vm.form.intRoomID, 10),
        intClasslistID: parseInt(vm.form.intClasslistID, 10),
        blockSectionID: parseInt(vm.form.blockSectionID, 10),
        strScheduleCode: vm.form.strScheduleCode.trim(),
        strDay: parseInt(vm.form.strDay, 10),
        dteStart: vm.form.dteStart,
        dteEnd: vm.form.dteEnd,
        enumClassType: vm.form.enumClassType || 'lect',
        intSem: parseInt(vm.form.intSem, 10)
      };

      var promise = vm.isEdit 
        ? SchedulesService.update($routeParams.id, payload)
        : SchedulesService.create(payload);

      promise
        .then(function (response) {
          if (response && response.success !== false) {
            var msg = vm.isEdit ? 'Schedule updated successfully.' : 'Schedule created successfully.';
            ToastService.success(msg);
            $location.path('/schedules');
          } else {
            var errorMsg = (response && response.message) ? response.message : 'Operation failed.';
            vm.error = errorMsg;
            ToastService.error(errorMsg);
          }
        })
        .catch(function (err) {
          console.error('Save error:', err);
          var msg = vm.isEdit ? 'Failed to update schedule.' : 'Failed to create schedule.';
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          } else if (err && err.data && err.data.errors) {
            // Laravel validation errors
            var errors = err.data.errors;
            var errorMessages = [];
            for (var field in errors) {
              if (errors.hasOwnProperty(field)) {
                errorMessages.push(errors[field].join(' '));
              }
            }
            if (errorMessages.length > 0) {
              msg = errorMessages.join(' ');
            }
          }
          vm.error = msg;
          ToastService.error(msg);
        })
        .finally(function () {
          vm.saving = false;
        });
    };

    vm.cancel = function () {
      $location.path('/schedules');
    };

    // === SHARED METHODS ===
    vm.clearAlert = function () {
      vm.error = null;
      vm.success = null;
    };

    // Test method to verify controller is working
    vm.testController = function() {
      alert('SchedulesController is working!');
    };

    // Helper methods
    vm.getDayName = function (dayNumber) {
      return vm.days[dayNumber] || 'Unknown';
    };

    vm.getClassTypeName = function (type) {
      var found = vm.classTypes.find(function (t) { return t.value === type; });
      return found ? found.label : type;
    };

    vm.getClassroomName = function (roomId) {
      var found = vm.classrooms.find(function (c) { return c.intID == roomId; });
      return found ? found.strRoomCode : 'Unknown';
    };

    vm.formatTime = function (time) {
      if (!time) return '';
      // Convert HH:MM:SS to HH:MM format
      return time.substring(0, 5);
    };

    vm.getAcademicYearLabel = function(intSem) {
      var found = vm.academicYears.find(function(ay) {
        return ay.intID == intSem;
      });
      if (found) {
        return found.enumSem + ' Term SY ' + found.strYearStart + '-' + found.strYearEnd;
      }
      return 'Unknown';
    };

    vm.getClasslistLabel = function(classlistData) {
      if (!classlistData) return 'Unknown';
      
      var parts = [];
      if (classlistData.subject && classlistData.subject.strCode) {
        parts.push(classlistData.subject.strCode);
      }
      if (classlistData.strClassName) {
        parts.push(classlistData.strClassName);
      }
      if (classlistData.sectionCode) {
        parts.push('Section ' + classlistData.sectionCode);
      }
      
      return parts.join(' - ');
    };

    vm.getFacultyName = function(faculty) {
      if (!faculty) return 'Unknown Faculty';
      return (faculty.strFirstname + ' ' + faculty.strLastname).trim();
    };
  }
})();
