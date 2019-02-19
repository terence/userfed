angular.module('App').controller('PayrunsDetailCtrl', [
    '$scope',
    '$stateParams',
    '$http',
    function($scope, $stateParams, $http) {
        $scope.companyName = $stateParams.companyName;
        $scope.companyId = $stateParams.companyId;
        $scope.payrunId = $stateParams.payrunId;
        $http({
            method: 'GET',
            url: '/api/payroll/company/1/payrun/' + $scope.payrunId
        }).success(function(data){
            $scope.payrun = data;
        }).error(function(){
            alert("Server error");
        });
    }
]);