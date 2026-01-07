angular.module('unityApp')
  .controller('VehicleReservationDashboardController', VehicleReservationDashboardController);

VehicleReservationDashboardController.$inject = ['$scope', 'VehicleReservationService', 'ToastService', 'FacultyService', 'StorageService'];

function VehicleReservationDashboardController($scope, VehicleReservationService, ToastService, FacultyService, StorageService) {
  var vm = this;
  vm.loading = true;
  vm.todaysReservations = [];
  vm.myReservations = [];
  vm.pendingReservations = [];
  vm.consolidatedReservations = [];
  vm.isAdmin = false;
  vm.addModalOpen = false;
  vm.model = {};
  vm.vehicles = [];
  vm.facultyList = [];
  vm.minDate = new Date().toISOString().slice(0, 10);
  vm.isCreate = true;
  vm.success = null;
  vm.error = null;

  vm.openAddModal = function() {
    vm.isCreate = true;
    vm.model = {};
    vm.loading = true;
    VehicleReservationService.getAvailableVehicles().then(function(res) {
      vm.vehicles = res.data || [];
      FacultyService.list({ isActive: 1, per_page: 100 }).then(function(faculty) {
        vm.facultyList = faculty && faculty.data ? faculty.data : faculty;
        vm.addModalOpen = true;
        vm.loading = false;
      });
    });
  };

  vm.closeAddModal = function() {
    vm.addModalOpen = false;
    vm.model = {};
    vm.isCreate = true;
  };

  vm.submitAddReservation = function() {
    if (!vm.model.dteReservationDate || !vm.model.dteStartTime || !vm.model.dteEndTime || !vm.model.intVehicleID || !vm.model.intFacultyID || !vm.model.strPurpose || !vm.model.strDestination) {
      ToastService.error('Please fill all required fields');
      return;
    }
    var data = angular.copy(vm.model);
    if (data.dteReservationDate instanceof Date) {
      data.dteReservationDate = data.dteReservationDate.toISOString().slice(0, 10);
    }
    if (data.dteStartTime instanceof Date) {
      data.dteStartTime = data.dteStartTime.toTimeString().slice(0, 8);
    }
    if (data.dteEndTime instanceof Date) {
      data.dteEndTime = data.dteEndTime.toTimeString().slice(0, 8);
    }
    data.intVehicleID = parseInt(data.intVehicleID);
    data.intFacultyID = parseInt(data.intFacultyID);
    if (data.intDriverID) data.intDriverID = parseInt(data.intDriverID);

    var action = vm.isCreate ? VehicleReservationService.create(data) : VehicleReservationService.update(vm.model.intReservationVehicleID, data);
    action.then(function(res) {
      ToastService.success(vm.isCreate ? 'Reservation created' : 'Reservation updated');
      vm.closeAddModal();
      loadDashboard();
    }).catch(function(err) {
      var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Save failed';
      ToastService.error(m);
    });
  };

  vm.openEditModal = function(reservation) {
    if (!reservation || !reservation.intReservationVehicleID) return;
    vm.isCreate = false;
    vm.loading = true;
    VehicleReservationService.view(reservation.intReservationVehicleID).then(function(res) {
      var item = angular.copy(res.data);
      if (item.dteReservationDate) {
        if (item.dteReservationDate instanceof Date) {
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
      if (item.intVehicleID) item.intVehicleID = String(item.intVehicleID);
      if (item.intFacultyID) item.intFacultyID = String(item.intFacultyID);
      if (item.intDriverID) item.intDriverID = String(item.intDriverID);
      vm.model = item;
      VehicleReservationService.getVehicles().then(function(vehicleRes) {
        vm.vehicles = vehicleRes.data || [];
        FacultyService.list({ isActive: 1, per_page: 100 }).then(function(faculty) {
          vm.facultyList = faculty && faculty.data ? faculty.data : faculty;
          vm.addModalOpen = true;
          vm.loading = false;
        });
      });
    });
  };

  vm.deleteReservation = function(reservation) {
    if (!reservation || !reservation.intReservationVehicleID) return;
    if (!confirm('Are you sure you want to delete this vehicle reservation?')) return;
    VehicleReservationService.delete(reservation.intReservationVehicleID)
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
    return reservation.enumStatus === 'pending';
  };

  vm.canReject = vm.canApprove;

  vm.approveReservation = function(reservation) {
    if (!reservation || !reservation.intReservationVehicleID) return;
    VehicleReservationService.approve(reservation.intReservationVehicleID)
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
    if (!reservation || !reservation.intReservationVehicleID) return;
    VehicleReservationService.reject(reservation.intReservationVehicleID)
      .then(function(res) {
        ToastService.success('Reservation rejected');
        loadDashboard();
      })
      .catch(function(err) {
        var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Reject failed';
        ToastService.error(m);
      });
  };

  vm.startUseReservation = function(reservation) {
    if (!reservation || !reservation.intReservationVehicleID) return;
    VehicleReservationService.startUse(reservation.intReservationVehicleID)
      .then(function(res) {
        ToastService.success('Vehicle use started');
        loadDashboard();
      })
      .catch(function(err) {
        var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Failed to start use';
        ToastService.error(m);
      });
  };

  vm.completeReservation = function(reservation) {
    if (!reservation || !reservation.intReservationVehicleID) return;
    VehicleReservationService.complete(reservation.intReservationVehicleID)
      .then(function(res) {
        ToastService.success('Reservation completed');
        loadDashboard();
      })
      .catch(function(err) {
        var m = (err && err.data && err.data.message) || (err && err.data && err.data.error) || 'Failed to complete';
        ToastService.error(m);
      });
  };

  vm.formatTime = function(time) {
    if (!time) return '';
    if (typeof time === 'string') {
      return time.slice(0, 5);
    }
    return time;
  };

  vm.clearAlert = function() {
    vm.success = null;
    vm.error = null;
  };

  function loadDashboard() {
    vm.loading = true;
    VehicleReservationService.dashboard().then(function(res) {
      vm.todaysReservations = res.data.todays_reservations || [];
      vm.pendingReservations = res.data.pending_reservations || [];
      vm.myReservations = res.data.my_reservations || [];
      var todays = res.data.todays_reservations || [];
      var pending = res.data.pending_reservations || [];
      var mine = res.data.my_reservations || [];

      var all = [].concat(todays, pending, mine);
      var seen = {};
      var consolidated = [];
      all.forEach(function(item) {
        var id = item.intReservationVehicleID;
        if (!seen[id]) {
          seen[id] = true;
          if (item.vehicle) {
            item.strVehicleName = item.vehicle.strVehicleName;
            item.strPlateNumber = item.vehicle.strPlateNumber;
          }
          if (item.faculty) {
            item.strFirstname = item.faculty.strFirstname;
            item.strLastname = item.faculty.strLastname;
          }
          consolidated.push(item);
        }
      });
      vm.consolidatedReservations = consolidated;
      vm.loading = false;
    }).catch(function(err) {
      vm.loading = false;
    });
  }

  loadDashboard();
}
