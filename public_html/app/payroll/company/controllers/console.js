angular.module('App').controller('CompanyConsoleCtrl', [
    '$scope',
    '$stateParams',
    '$rootScope',
    function($scope, $stateParams, $rootScope) {
        $scope.companyName = $stateParams.companyName;
        $scope.companyId = $stateParams.companyId;
        $rootScope.currentCompany = { id : $stateParams.companyId, name: $stateParams.companyName };
        $rootScope.$broadcast('currentCompanyChanged');
    }
]);