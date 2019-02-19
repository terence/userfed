var app = angular.module('App', [
    'app.directives.ngMatch',
    'app.services'
]).controller('RegisterController', ['$scope', '$http', '$timeout', 'checkUniqueService', function($scope, $http, $timeout, checkUniqueService) {
    $scope.data = {};

    $scope.isFormEmpty = function() {
        for (var i in $scope.data) {
            if ($scope.data[i]) {
                return false;
            }
        }
        if ($scope.password) {
            return false;
        }
        return true;
    };

    $scope.status = {
        loading: false,
        error: false,
        success: false
    };

    var formElements = angular.element("[name=form] input");
    $scope.submit = function($event) {
        $event.preventDefault();
        if (!$scope.form.$valid) {
            // cancel submit and show error messages if form is submitted when not valid
            formElements.trigger("input");
            formElements.trigger("blur");
            return false;
        }
        $scope.status.loading = true;
        // grap password separately because ng-model='data.password' on password input causes checkStrength directive work incorrectly
        $scope.data.password = $scope.password;
        $http({
            method  : 'POST',
            url     : '/register',
            data    : $.param($scope.data),  // pass in data as strings
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
        })
        .success(function(result) {
            $scope.status.loading = false;
            if (!result.error) {
                $scope.status.success = true;
            } else {
                $scope.status.error = true;
            }
            $scope.message = result.message;
        });
    };
    
    var isEmptyObject = function(obj) {
        for (var i in obj) {
            return false;
        }
        return true;
    };
    var emailElem = angular.element("[name=email]");
    var unusedEmailList = [];
    var usedEmailList = [];
    $scope.checkUniqueEmail = function(errors) {
        // put code in $timeout function to ensure the errors variable is updated
        $timeout(function() {
            $scope.form.email.$setValidity('unique', true);
            if (isEmptyObject(errors)) {
                // user input a valid email, check if it was already used by someone
                var email = emailElem.val();
                if (unusedEmailList.indexOf(email) != -1) {
                    $scope.form.email.$setValidity('unique', true);
                    return;
                }
                if (usedEmailList.indexOf(email) != -1) {
                    $scope.form.email.$setValidity('unique', false);
                    return;
                }
                if (!$scope.checking) {
                    $scope.checking = {};
                }
                $scope.checking.username = true;
                checkUniqueService.check('username', email)
                    .then(function (result) {
                        $scope.checking.username = false;
                        // store the checking result to avoid repeatedly checking
                        if (result) {
                            unusedEmailList.push(email);
                            $timeout(function() {
                                angular.element("[name=email_confirm]").focus();
                            }, 0);
                        } else {
                            $timeout(function() {
                                emailElem.select();
                            }, 0);
                            usedEmailList.push(email);
                        }
                        //Ensure value that being checked hasn't changed
                        //since the Ajax call was made
                        if (email === emailElem.val()) { 
                            $scope.form.email.$setValidity('unique', result);
                        }
                    }, function () {
                        alert("Server error.");
                        $scope.form.email.$setValidity('unique', false);
                    });
            }
        }, 0);
    };
}]);;