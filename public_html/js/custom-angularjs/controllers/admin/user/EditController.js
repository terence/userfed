(function() {
    angular.module('App').controller('EditController', [
        '$scope',
        '$routeParams',
        '$location',
        function ($scope, $routeParams, $location) {
            $scope.selectedTab = $routeParams.tab;
            $scope.tabs = [
                {
                    title: 'General info',
                    id: 'general',
                    templateUrl: '/js/custom-angularjs/templates/admin/user/partials/edit-general-info.html',
                    active: false
                },
                {
                    title: 'Role',
                    id:'role',
                    templateUrl: '/js/custom-angularjs/templates/admin/user/partials/edit-role.html',
                    active: false
                },
                {
                    title: 'Identity',
                    id: 'identity',
                    templateUrl: '/js/custom-angularjs/templates/admin/user/partials/edit-identity.html',
                    active: false
                },
                {
                    title: 'Organisation',
                    id: 'org',
                    templateUrl: '/js/custom-angularjs/templates/admin/user/partials/edit-org.html',
                    active: false
                },
                {
                    title: 'Application',
                    id: 'app',
                    templateUrl: '/js/custom-angularjs/templates/admin/user/partials/edit-app.html',
                    active: false
                },
                {
                    title: 'Log',
                    id: 'log',
                    templateUrl: '/js/custom-angularjs/templates/admin/user/partials/edit-log.html',
                    active: false
                }
            ];
            $scope.init = function() {
                var tabs = $scope.tabs;
                for (var i in tabs) {
                    var tabId = tabs[i].id;
                    if (tabId === $scope.selectedTab) {
                        tabs[i].active = true;
                        break;
                    }
                }
            };
            $scope.init();
            $scope.active = function(tabId) {
                $scope.selectedTab = tabId;
            };
            // back to view the grid. clear selectedTab
            $scope.back = function() {
                $scope.selectedTab = undefined;
                $location.path("/");
            };
            $scope.$watch('selectedTab', function (newVal, oldVal) {
                if (newVal && newVal !== oldVal) {
                    $location.path("/edit/" + $routeParams.id + "/" + newVal);
                }
            }, true);
        }
    ]);
})();