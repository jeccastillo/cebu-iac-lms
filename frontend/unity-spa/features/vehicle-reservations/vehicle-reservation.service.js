angular.module('unityApp')
  .factory('VehicleReservationService', VehicleReservationService);

VehicleReservationService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
function VehicleReservationService($http, APP_CONFIG, StorageService) {
  var BASE = APP_CONFIG.API_BASE;
  var svc = {
    dashboard: dashboard,
    list: list,
    view: view,
    create: create,
    update: update,
    delete: remove,
    approve: approve,
    reject: reject,
    startUse: startUse,
    complete: complete,
    checkAvailability: checkAvailability,
    getVehicles: getVehicles,
    getAvailableVehicles: getAvailableVehicles
  };
  return svc;

  function _getLoginState() {
    try {
      return StorageService.getJSON('loginState') || null;
    } catch (e) {
      return null;
    }
  }
  function _adminHeaders(extra) {
    var state = _getLoginState();
    var headers = Object.assign({}, extra || {});
    if (state && state.faculty_id) {
      headers['X-Faculty-ID'] = state.faculty_id;
    }
    return { headers: headers };
  }
  function dashboard() {
    return $http.get(BASE + '/vehicle-reservations/dashboard', _adminHeaders());
  }
  function list() {
    return $http.get(BASE + '/vehicle-reservations', _adminHeaders());
  }
  function view(id) {
    return $http.get(BASE + '/vehicle-reservations/' + id, _adminHeaders());
  }
  function create(data) {
    return $http.post(BASE + '/vehicle-reservations', data, _adminHeaders());
  }
  function update(id, data) {
    return $http.put(BASE + '/vehicle-reservations/' + id, data, _adminHeaders());
  }
  function remove(id) {
    return $http.delete(BASE + '/vehicle-reservations/' + id, _adminHeaders());
  }
  function approve(id) {
    return $http.post(BASE + '/vehicle-reservations/approve', { intReservationVehicleID: id }, _adminHeaders());
  }
  function reject(id) {
    return $http.post(BASE + '/vehicle-reservations/reject', { intReservationVehicleID: id }, _adminHeaders());
  }
  function startUse(id) {
    return $http.post(BASE + '/vehicle-reservations/' + id + '/start-use', {}, _adminHeaders());
  }
  function complete(id) {
    return $http.post(BASE + '/vehicle-reservations/' + id + '/complete', {}, _adminHeaders());
  }
  function checkAvailability(data) {
    return $http.post(BASE + '/vehicle-reservations/check-availability', data, _adminHeaders());
  }
  function getVehicles() {
    return $http.get(BASE + '/vehicles', _adminHeaders());
  }
  function getAvailableVehicles() {
    return $http.get(BASE + '/vehicles/available', _adminHeaders());
  }
}
