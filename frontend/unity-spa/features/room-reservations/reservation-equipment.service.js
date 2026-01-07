angular.module('unityApp')
  .factory('ReservationEquipmentService', ReservationEquipmentService);

ReservationEquipmentService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
function ReservationEquipmentService($http, APP_CONFIG, StorageService) {
  var BASE = APP_CONFIG.API_BASE;
  var svc = {
    list: list,
    view: view,
    create: create,
    update: update,
    delete: remove,
    approve: approve,
    deny: deny,
    deliver: deliver,
    returned: returned
  };
  return svc;

  function _adminHeaders(extra) {
    var state = StorageService.getJSON('loginState');
    var headers = Object.assign({}, extra || {});
    if (state && state.faculty_id) {
      headers['X-Faculty-ID'] = state.faculty_id;
    }
    return { headers: headers };
  }
  function list(params) {
    return $http.get(BASE + '/reservation-equipment', _adminHeaders());
  }
  function view(id) {
    return $http.get(BASE + '/reservation-equipment/' + id, _adminHeaders());
  }
  function create(data) {
    return $http.post(BASE + '/reservation-equipment', data, _adminHeaders());
  }
  function update(id, data) {
    return $http.put(BASE + '/reservation-equipment/' + id, data, _adminHeaders());
  }
  function remove(id) {
    return $http.delete(BASE + '/reservation-equipment/' + id, _adminHeaders());
  }
  function approve(id) {
    return $http.post(BASE + '/reservation-equipment/' + id + '/approve', {}, _adminHeaders());
  }
  function deny(id) {
    return $http.post(BASE + '/reservation-equipment/' + id + '/deny', {}, _adminHeaders());
  }
  function deliver(id) {
    return $http.post(BASE + '/reservation-equipment/' + id + '/deliver', {}, _adminHeaders());
  }
  function returned(id) {
    return $http.post(BASE + '/reservation-equipment/' + id + '/returned', {}, _adminHeaders());
  }
}
