angular.module('unityApp')
  .controller('RoomReservationListController', function($scope, RoomReservationService, ToastService) {
    var vm = this;
    vm.loading = false;
    vm.items = [];
    vm.refresh = function() {
      vm.loading = true;
      RoomReservationService.list().then(function(res) {
        vm.items = res.data;
      }).finally(function() {
        vm.loading = false;
      });
    };
    vm.refresh();
  })
  .controller('RoomReservationFormController', function($scope, $routeParams, $location, RoomReservationService, ToastService) {
    var vm = this;
    vm.loading = false;
    vm.saving = false;
    vm.form = {};
    vm.classrooms = [];
    vm.mode = $routeParams.id ? 'edit' : 'create';
    if (vm.mode === 'edit') {
      vm.loading = true;
      RoomReservationService.editForm($routeParams.id).then(function(res) {
        vm.form = res.data.item;
        vm.classrooms = res.data.classrooms;
      }).finally(function() {
        vm.loading = false;
      });
    } else {
      vm.loading = true;
      RoomReservationService.addForm().then(function(res) {
        vm.classrooms = res.data.classrooms;
      }).finally(function() {
        vm.loading = false;
      });
    }
    vm.save = function() {
      vm.saving = true;
      var action = vm.mode === 'edit' ? RoomReservationService.update(vm.form.intReservationID, vm.form) : RoomReservationService.create(vm.form);
      action.then(function(res) {
        ToastService.success('Saved');
        $location.path('/room-reservations');
      }).catch(function(err) {
        ToastService.error('Save failed');
      }).finally(function() {
        vm.saving = false;
      });
    };
  })
  .controller('RoomReservationViewController', function($scope, $routeParams, RoomReservationService) {
    var vm = this;
    vm.loading = false;
    vm.item = {};
    if ($routeParams.id) {
      vm.loading = true;
      RoomReservationService.view($routeParams.id).then(function(res) {
        vm.item = res.data;
      }).finally(function() {
        vm.loading = false;
      });
    }
  })
  .controller('RoomReservationDashboardController', RoomReservationDashboardController);

  RoomReservationDashboardController.$inject = ['$scope', 'RoomReservationService', 'ToastService', 'FacultyService', 'StorageService'];

  function RoomReservationDashboardController($scope, RoomReservationService, ToastService, FacultyService, StorageService) {
    var vm = this;
    vm.loading = true;
    vm.todaysReservations = [];
    vm.myReservations = [];
    vm.pendingReservations = [];
    vm.isAdmin = false;
    vm.addModalOpen = false;
    vm.model = {};
    vm.classrooms = [];
    vm.facultyList = [];
    vm.minDate = new Date().toISOString().slice(0, 10);
    vm.isCreate = true;

    vm.openEditModal = function(reservation) {
      if (!reservation || !reservation.intReservationID) return;
      vm.isCreate = false;
      vm.loading = true;
      RoomReservationService.editForm(reservation.intReservationID).then(function(res) {
        var item = angular.copy(res.data.item);
        // Fix date and time fields for input compatibility
        // dteReservationDate: must be a Date object for Angular date input
        if (item.dteReservationDate) {
          if (item.dteReservationDate instanceof Date) {
            // do nothing
          } else if (/^\d{4}-\d{2}-\d{2}$/.test(item.dteReservationDate)) {
            item.dteReservationDate = new Date(item.dteReservationDate + 'T00:00:00');
          } else {
            var d = new Date(item.dteReservationDate);
            if (!isNaN(d.getTime())) {
              item.dteReservationDate = d;
            } else {
              item.dteReservationDate = null;
            }
          }
        }
        // dteStartTime, dteEndTime: must be Date objects for Angular time input
        function toTimeDate(val) {
          if (!val) return null;
          if (val instanceof Date) return val;
          if (/^\d{2}:\d{2}$/.test(val)) return new Date('1970-01-01T' + val + ':00');
          if (/^\d{2}:\d{2}:\d{2}$/.test(val)) return new Date('1970-01-01T' + val);
          var t = new Date('1970-01-01T' + val);
          if (!isNaN(t.getTime())) return t;
          return null;
        }
        item.dteStartTime = toTimeDate(item.dteStartTime);
        item.dteEndTime = toTimeDate(item.dteEndTime);
        // Room and Faculty: ensure correct type for ng-model
        if (item.intRoomID) item.intRoomID = String(item.intRoomID);
        if (item.intFacultyID) item.intFacultyID = String(item.intFacultyID);
        vm.model = item;
        vm.classrooms = res.data.classrooms || [];
        // Fetch faculty list for dropdown
        FacultyService.list({ isActive: 1, per_page: 100 }).then(function(faculty) {
          vm.facultyList = faculty && faculty.data ? faculty.data : faculty;
          vm.addModalOpen = true;
          vm.loading = false;
        });
      });
    };
    vm.deleteReservation = function(reservation) {
      if (!reservation || !reservation.intReservationID) return;
      if (!confirm('Are you sure you want to delete this reservation?')) return;
      RoomReservationService.delete(reservation.intReservationID)
        .then(function(res) {
          ToastService.success('Reservation deleted');
          loadDashboard();
        })
        .catch(function(err) {
          var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Delete failed';
          ToastService.error(m);
        });
    };

    vm.canApprove = function(reservation) {
      var loginState = StorageService.getJSON('loginState');
      if (!reservation || !loginState) return false;
      var userId = loginState.user_id || loginState.faculty_id;
      return reservation.intApprovedBy == userId && reservation.enumStatus === 'pending' && reservation.intCreatedBy != userId;
    };
    vm.canReject = vm.canApprove;
    vm.approveReservation = function(reservation) {
      if (!reservation || !reservation.intReservationID) return;
      RoomReservationService.approve(reservation.intReservationID)
        .then(function(res) {
          ToastService.success('Reservation approved');
          loadDashboard();
        })
        .catch(function(err) {
          var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Approve failed';
          ToastService.error(m);
        });
    };
    vm.rejectReservation = function(reservation) {
      if (!reservation || !reservation.intReservationID) return;
      RoomReservationService.reject(reservation.intReservationID)
        .then(function(res) {
          ToastService.success('Reservation rejected');
          loadDashboard();
        })
        .catch(function(err) {
          var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Reject failed';
          ToastService.error(m);
        });
    };
    vm.myReservations = [];
    vm.pendingReservations = [];
    vm.isAdmin = false;
    vm.addModalOpen = false;
    vm.model = {};
    vm.classrooms = [];
    vm.facultyList = [];
    vm.minDate = new Date().toISOString().slice(0, 10);
    vm.isCreate = true;

    function loadDashboard() {
      vm.loading = true;
      RoomReservationService.dashboard().then(function(res) {
        vm.todaysReservations = res.data.todays_reservations || [];
        vm.pendingReservations = res.data.pending_reservations || [];
        vm.myReservations = res.data.my_reservations || [];
        var todays = res.data.todays_reservations || [];
        var pending = res.data.pending_reservations || [];
        var mine = res.data.my_reservations || [];

        // Consolidate and deduplicate by intReservationID
        var all = [].concat(todays, pending, mine);
        var seen = {};
        var consolidated = [];
        all.forEach(function(item) {
          var id = item.intReservationID;
          if (id && !seen[id]) {
            seen[id] = true;
            consolidated.push(item);
          }
        });
        vm.consolidatedReservations = consolidated;
        vm.isAdmin = res.data.is_admin || false;
      }).finally(function() {
        vm.loading = false;
      });
    }
    loadDashboard();

    vm.openAddModal = function() {
      vm.model = {
        dteReservationDate: '',
        dteStartTime: '',
        dteEndTime: '',
        intRoomID: '',
        strPurpose: '',
        intFacultyID: ''
      };
      vm.isCreate = true;
      RoomReservationService.addForm().then(function(res) {
        vm.classrooms = res.data.classrooms || [];
        // Fetch faculty list for dropdown
        FacultyService.list({ isActive: 1, per_page: 100 }).then(function(faculty) {
          vm.facultyList = faculty && faculty.data ? faculty.data : faculty;
          vm.addModalOpen = true;
        });
      });
    };
    vm.closeAddModal = function() {
      vm.addModalOpen = false;
    };
    vm.submitAddReservation = function() {
      // Debug: log model
      console.log('Submitting reservation:', angular.copy(vm.model));
      // Basic validation
      if (!vm.model.dteReservationDate || !vm.model.dteStartTime || !vm.model.dteEndTime || !vm.model.intRoomID || !vm.model.strPurpose || !vm.model.intFacultyID) {
        ToastService.error('All fields are required.');
        return;
      }
      // Always set intCreatedBy right before POST (for create)
      var loginState = StorageService.getJSON('loginState');
      var createdBy = 1;
      if (loginState && loginState.user_id) {
        createdBy = loginState.user_id;
      } else if (loginState && loginState.faculty_id) {
        createdBy = loginState.faculty_id;
      }
      if (vm.isCreate) {
        vm.model.intCreatedBy = createdBy;
      }

      // Format date fields for MySQL
      var payload = angular.copy(vm.model);
      // dteReservationDate: YYYY-MM-DD
      if (payload.dteReservationDate) {
        var d = new Date(payload.dteReservationDate);
        payload.dteReservationDate = d.toISOString().slice(0, 10);
      }
      // Always format dteStartTime and dteEndTime as HH:MM:SS
      function toTimeString(val) {
        if (!val) return '';
        if (typeof val === 'string' && val.length === 5) return val + ':00';
        if (typeof val === 'string' && val.length > 5) {
          // ISO or full datetime string
          var d = new Date(val);
          return d.toTimeString().slice(0,8);
        }
        if (val instanceof Date) {
          return val.toTimeString().slice(0,8);
        }
        return val;
      }
      payload.dteStartTime = toTimeString(payload.dteStartTime);
      payload.dteEndTime = toTimeString(payload.dteEndTime);

      // Log the payload for confirmation
      console.log('Submitting payload (should include intCreatedBy):', angular.copy(payload));
      var promise;
      if (vm.isCreate) {
        promise = RoomReservationService.create(payload);
      } else {
        promise = RoomReservationService.update(payload.intReservationID, payload);
      }
      promise.then(function(res) {
        vm.closeAddModal();
        loadDashboard();
      }).catch(function(err) {
        var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || (err && err.data && err.data.error) || 'Save failed';
        ToastService.error(m);
      });
    };
    vm.clearAlert = function() {
      vm.error = null;
      vm.success = null;
    };
    vm.formatTime = function(t) {
      if (!t) return '';
      return t.substring(0,5);
    };
  }
