angular.module('App').controller('EmployeesListCtrl', [
    '$scope',
    '$stateParams',
    'Employee',
    '$modal',
    function($scope, $stateParams, Employee, $modal) {
        $scope.companyId = $stateParams.companyId;
        $scope.companyName = $stateParams.companyName;
        $scope.employees = Employee.get({company_id: $scope.companyId});
        
        $scope.syncEmployees = function() {
            var syncEmployeesModalInstance = $modal.open({
                templateUrl: '/app/payroll/employee/templates/modals/import-employees.html',
                controller: 'SyncEmployeesCtrl',
                backdrop: 'static'
            });
            syncEmployeesModalInstance.result.then(function(result) {
                $scope.employees.items = $scope.employees.items.concat(result.imported_employees);
            });
        };
        $scope.selected = null;
        $scope.isSelected = function($index) {
            return $scope.selected === $index;
        };
    }
]);

angular.module('App').controller('SyncEmployeesCtrl', [
    '$scope',
    '$modalInstance',
    '$timeout',
    '$stateParams',
    '$http',
    function($scope, $modalInstance, $timeout, $stateParams, $http) {
        // cos' the element with id loading is in the modal, whose dom only be loaded when the modal is opened
        // so right at the moment the modal is opened, it is not avalable
        // so we need to get it in $timeout function to ensure it is available at the time of fetching
        $timeout(function() {
            var spinner = new Spinner({
                lines: 10, // The number of lines to draw
                length: 8, // The length of each line
                width: 5, // The line thickness
                radius: 8, // The radius of the inner circle
                corners: 1, // Corner roundness (0..1)
                rotate: 0, // The rotation offset
                direction: 1, // 1: clockwise, -1: counterclockwise
                color: '#000', // #rgb or #rrggbb or array of colors
                speed: 1, // Rounds per second
                trail: 50, // Afterglow percentage
                shadow: false, // Whether to render a shadow
                hwaccel: false, // Whether to use hardware acceleration
                className: 'spinner', // The CSS class to assign to the spinner
                zIndex: 2e9, // The z-index (defaults to 2000000000)
                top: '50%', // Top position relative to parent
                left: '50%' // Left position relative to parent
            }).spin(document.getElementById('loading'));
        }, 0);
        
        var loadUserfedUsers = function() {
            var fetchingUrl = "/api/payroll/uf-org/" + $stateParams.companyId + "/get-users";
            $http({
                method: "GET",
                url: fetchingUrl
            }).success(function(result) {
                $scope.loading = false;
                $scope.userfedUsers = result.users;
                // select all by default
                $scope.selected = $scope.userfedUsers;
            }).error(function() {
                alert("Oops!!! There's error on server. Please try again later.");
            });
        };
        
        $scope.loading = true;
        
        $scope.userfedUsers = loadUserfedUsers();
        
        $scope.import = function() {
            $scope.importing = true;
            $scope.importResult = null;
            $http({
                method: "POST",
                url: "/api/payroll/company/" + $stateParams.companyId + "/employee",
                data: { 'employees': $scope.selected }
            }).success(function(result) {
                $scope.importing = false;
                $scope.importDone = true;
                $scope.importResult = result;
            })
            .error(function() {
                alert("Oops! Server error. Please try again later.");
                $modalInstance.dismiss();
            });
        };
        
        $scope.done = function() {
            $modalInstance.close($scope.importResult);
        };
        
        $scope.cancel = function() {
            $modalInstance.dismiss();
        };
        
        $scope.selected = [];
        $scope.updateSelected = function($event, user) {
            if ($event.target.checked) {
                $scope.selected.push(user);
            } else {
                $scope.selected = $scope.selected.filter(function(u) {
                    return u.uf_user_id != user.uf_user_id;
                });
            }
        };
        
        $scope.toggleSelectAll = function($event) {
            if ($event.target.checked) {
                $scope.selected = $scope.userfedUsers;
            } else {
                $scope.selected = [];
            }
        };
    }
]);