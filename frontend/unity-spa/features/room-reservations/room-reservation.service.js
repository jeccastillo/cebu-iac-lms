angular.module('unityApp')
  .factory('RoomReservationService', RoomReservationService);

RoomReservationService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
function RoomReservationService($http, APP_CONFIG, StorageService) {
  var BASE = APP_CONFIG.API_BASE;
  var svc = {
    dashboard: dashboard,
    list: list,
    view: view,
    addForm: addForm,
    editForm: editForm,
    create: create,
    update: update,
    delete: remove,
    approve: approve,
    reject: reject,
    checkAvailability: checkAvailability,
    getAvailableRooms: getAvailableRooms,
    getScheduleData: getScheduleData
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
    return $http.get(BASE + '/room-reservations/dashboard', _adminHeaders());
  }
  function list() {
    return $http.get(BASE + '/room-reservations', _adminHeaders());
  }
  function view(id) {
    return $http.get(BASE + '/room-reservations/' + id, _adminHeaders());
  }
  function addForm() {
    return $http.get(BASE + '/room-reservations/add-form', _adminHeaders());
  }
  function editForm(id) {
    return $http.get(BASE + '/room-reservations/' + id + '/edit-form', _adminHeaders());
  }
  function create(data) {
    return $http.post(BASE + '/room-reservations', data, _adminHeaders());
  }
  function update(id, data) {
    return $http.put(BASE + '/room-reservations/' + id, data, _adminHeaders());
  }
  function remove(id) {
    // Prefer RESTful DELETE, fallback to POST for backward compatibility
    return $http.delete(BASE + '/room-reservations/' + id, _adminHeaders())
      .catch(function(err) {
        // fallback to POST if DELETE fails (legacy support)
        return $http.post(BASE + '/room-reservations/delete', { id: id }, _adminHeaders());
      });
  }
  function approve(id) {
    return $http.post(BASE + '/room-reservations/approve', { intReservationID: id }, _adminHeaders());
  }
  function reject(id) {
    return $http.post(BASE + '/room-reservations/reject', { intReservationID: id }, _adminHeaders());
  }
  function checkAvailability(data) {
    return $http.post(BASE + '/room-reservations/check-availability', data, _adminHeaders());
  }
  function getAvailableRooms(data) {
    return $http.post(BASE + '/room-reservations/get-available-rooms', data, _adminHeaders());
  }
  function getScheduleData(data) {
    return $http.post(BASE + '/room-reservations/get-schedule-data', data, _adminHeaders());
  }
}
