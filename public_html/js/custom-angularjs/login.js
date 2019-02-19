angular.module('App', []).controller('LoginController', ['$scope', '$http', function($scope, $http) {
    $scope.status = {
        loginError: false,
        loading: false,
        change: false,
        success: false
    };
    $scope.change = function() {
        $scope.status.change = true;
    };
    $scope.submit = function($event) {
        $event.preventDefault();
        if (!$scope.form.$valid) {
            // manually valid inputs
            angular.element("[name='form']").find("input").trigger("input").trigger("blur");
            return false;
        }
        $scope.status.loginError = false;
        $scope.status.change = false;
        $scope.status.loading = true;
        $http({
            method  : 'POST',
            url     : '/login',
            data    : $.param($scope.identity),  // pass in data as strings
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
        })
        .success(function(data) {
            $scope.status.loading = false;
            if (!data.error) {
                $scope.status.success = true;
                window.location = data.redirectTo;
            } else {
                $scope.status.loginError = true;
                // if successful, bind success message to message
                $scope.message = data.message;
            }
        });
    };
}]);