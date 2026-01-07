angular.module('unity').factory('EquipmentService', function($http) {
    return {
        list: function(reservationId) {
            return $http.get('/api/v1/reservation-equipment?reservation_id=' + reservationId);
        },
        create: function(data) {
            return $http.post('/api/v1/reservation-equipment', data);
        },
        update: function(id, data) {
            return $http.put('/api/v1/reservation-equipment/' + id, data);
        },
        remove: function(id) {
            return $http.delete('/api/v1/reservation-equipment/' + id);
        },
        changeStatus: function(id, status) {
            return $http.post('/api/v1/reservation-equipment/' + id + '/status', { enumStatus: status });
        }
    };
});
