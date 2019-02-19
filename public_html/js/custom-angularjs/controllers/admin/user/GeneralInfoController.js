(function() {
    angular.module('App').controller('GeneralInfoController', [
        '$scope',
        '$routeParams',
        'AbortableRequestService',
        '$modal',
        '$location',
        function ($scope, $routeParams, AbortableRequestService, $modal, $location) {
            // $scope is shared with EditController
            var tabId = 'general';
            var url = "/admin/user/edit/" + $routeParams.id + "?async=1";
            var request = null;
            $scope.loadData = function() {
                if (request) {
                    request.abort();
                }
                (request = AbortableRequestService.request('get', url)).then(
                    function(r) {
                        $scope.status.loaded = true;
                        $scope.user = r;
                        // convert "0" or "1" to false or true
                        $scope.user.is_enabled = $scope.user.is_enabled === "1";
                        $scope.user.is_deleted = $scope.user.is_deleted === "1";
                        $scope.user.is_enabled_checkbox = $scope.user.is_enabled;
                        if ($scope.user.is_deleted) {
                            $scope.showDelete = false;
                            $scope.showRestore = true;
                            $scope.showGeneratePassword = false;
                        } else {
                            $scope.showDelete = true;
                            $scope.showRestore = false;
                            $scope.showGeneratePassword = true;
                        }
                    },
                    function() {
                        $scope.status.loaded = true;
                        alert("Something went wrong to server. Please try again later!");
                    }
                );
            };
            if ($scope.selectedTab === tabId) {
                $scope.loadData();
            }
            
            $scope.status = {
                loaded: false,
                loading: false
            };
            $scope.submit = function() {
                $scope.status.loading = true;
                // convert true or false to "1" or "0"
                $scope.user.is_enabled = ( $scope.user.is_enabled_checkbox ? "1" : "0");
                AbortableRequestService.request('post', url, $.param($scope.user)).then(
                    function (r) {
                        $scope.user.is_enabled = ( $scope.user.is_enabled === "1" ? true : false);
                        $scope.status.loading = false;
                        if (r.error) {
                            $scope.status.error = true;
                            $scope.message = r.message;
                        } else {
                            $scope.status.success = true;
                        }
                    },
                    function() {
                        $scope.status.loading = false;
                        alert("Something wrong happened to server. Please try again later.");
                    }
                );
            };
            // delete
            $scope.delete = function() {
                var tempUrl = '/js/custom-angularjs/templates/admin/user/modals/delete-user.html';
                $modal.open({
                    templateUrl: tempUrl,
                    controller: ActionController,
                    backdrop : 'static'
                });
            };
            // permanent delete
            $scope.permanentDelete = function() {
                var tempUrl = '/js/custom-angularjs/templates/admin/user/modals/permanent-delete.html';
                $modal.open({
                    templateUrl: tempUrl,
                    controller: ActionController,
                    backdrop : 'static'
                });
            };
            // restore
            $scope.restore = function() {
                var tempUrl = '/js/custom-angularjs/templates/admin/user/modals/restore-user.html';
                $modal.open({
                    templateUrl: tempUrl,
                    controller: ActionController,
                    backdrop : 'static'
                });
            };
            $scope.$on("deleteUserCompleted", function() {
                $scope.showDelete = false;
                $scope.showRestore = true;
                $scope.showGeneratePassword = false;
                // update user status
                $scope.user.is_deleted = true;
            });
            $scope.$on("restoreUserCompleted", function() {
                $scope.showDelete = true;
                $scope.showRestore = false;
                $scope.showGeneratePassword = true;
                // update user status
                $scope.user.is_deleted = false;
            });
            $scope.$on("permanentDeleteUserCompleted", function() {
                setTimeout(function() {
                    $location.path("/");
                }, 2000);
            });
        }
    ]);
    var ActionController = function ($scope, $modalInstance, $routeParams, broadcastService, AbortableRequestService) {
        var userId = $routeParams.id;
        $scope.loading = false;
        $scope.permanentlyDeleted = false;
        $scope.do = function(action) {
            $scope.loading = true;
            var url = '/admin/user/delete/' + userId + "?async=1";;
            var broadCastEvent = "deleteUserCompleted";
            var timeout = 0;
            switch (action) {
                case "delete":
                    break;
                case "restore":
                    url = '/admin/user/restore/' + userId + "?async=1";
                    broadCastEvent = "restoreUserCompleted";
                    break;
                case "permanent-delete":
                    url = '/admin/user/permanently-delete/' + userId + "?async=1";
                    broadCastEvent = "permanentDeleteUserCompleted";
                    timeout = 3000;
                    break;
            }
            AbortableRequestService.request('get', url).then(
                function(result) {
                    $scope.loading = false;
                    if (!result.error) {
                        broadcastService.broadcast(broadCastEvent);
                        if (action === "permanent-delete") {
                            $scope.permanentlyDeleted = true;
                        }
                    } else {
                        alert("error");
                    }
                    setTimeout(function() {
                        $scope.close();
                    }, timeout);
                }
            );
        };
        $scope.delete = function() {
            $scope.do("delete");
        };
        $scope.restore = function() {
            $scope.do("restore");
        };
        $scope.permanentDelete = function() {
            $scope.do("permanent-delete");
        };
        $scope.close = function() {
            $modalInstance.close();
        };
    };
})();