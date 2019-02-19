angular.module('App').controller('AddCompanyCtrl', [
    '$scope',
    function($scope) {
        var init = function() {
            $scope.action = {
                name: 'add',
                data : {
                    confirmButtonText: "Add Company"
                }
            };
        };
        init();
    }
]);