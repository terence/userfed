var module = angular.module('App', [
    'ngRoute',
    'app.services',
    'ui.bootstrap',
    'ngGrid'
])
.factory('broadcastService', function($rootScope) {
        var service = {};
        service.broadcast = function(n) {
            $rootScope.$broadcast(n);
        };

        return service;
    })
;
module.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/', {
                templateUrl: '/js/custom-angularjs/templates/admin/user/users.html',
                controller: 'GridController',
                action: 'delete'
            }).
            when('/deleted-users', {
                templateUrl: '/js/custom-angularjs/templates/admin/user/deleted-users.html',
                controller: 'GridController',
                action: 'restore'
            }).
            when('/edit/:id/:tab', {
                templateUrl: '/js/custom-angularjs/templates/admin/user/edit.html',
                controller: 'EditController'
            }).
            otherwise({
                redirectTo: '/'
            });
    }]);

// fake payroll controllers
angular.module('App')
    .controller("NavCtrl", function() {})
    .controller("CompanyPickerCtrl", function() {});