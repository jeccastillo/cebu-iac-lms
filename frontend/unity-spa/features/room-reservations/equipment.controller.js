angular.module('unity').controller('EquipmentController', function($scope, $stateParams, EquipmentService) {
    $scope.equipmentList = [];
    $scope.newEquipment = {};
    $scope.loadEquipment = function() {
        EquipmentService.list($stateParams.reservationId).then(function(res) {
            $scope.equipmentList = res.data;
        });
    };
    $scope.addEquipment = function() {
        var data = angular.copy($scope.newEquipment);
        data.intReservationID = $stateParams.reservationId;
        EquipmentService.create(data).then(function() {
            $scope.newEquipment = {};
            $scope.loadEquipment();
        });
    };
    $scope.editEquipment = function(equipment) {
        $scope.editingEquipment = angular.copy(equipment);
    };
    $scope.updateEquipment = function() {
        EquipmentService.update($scope.editingEquipment.intReservationEquipmentID, $scope.editingEquipment).then(function() {
            $scope.editingEquipment = null;
            $scope.loadEquipment();
        });
    };
    $scope.deleteEquipment = function(equipment) {
        EquipmentService.remove(equipment.intReservationEquipmentID).then(function() {
            $scope.loadEquipment();
        });
    };
    $scope.changeStatus = function(equipment, status) {
        EquipmentService.changeStatus(equipment.intReservationEquipmentID, status).then(function() {
            $scope.loadEquipment();
        });
    };
    $scope.loadEquipment();
});
