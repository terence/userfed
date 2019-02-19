(function() {
    angular.module('App').controller('GridController', [
        '$scope',
        '$http',
        'AbortableRequestService',
        '$modal',
        'broadcastService',
        function($scope, $http, AbortableRequestService, $modal, broadcastService) {
            $scope.actionUrls = actionUrls;
            $scope.totalServerItems = 0;
            $scope.totalPages = 0;
            $scope.gridLoading = true;
            $scope.gridLoadingMessage = "Loading...";
            $scope.filterOptions = {
                filterText: "",
                useExternalFilter: true
            };
            $scope.sortInfo = {
                fields: [''],
                directions: ['']
            };
            $scope.pagingOptions = {
                pageSizes: [10, 25, 50, 100, 250, 500, 1000],
                pageSize: 25,
                currentPage: 1
            };
            var buildUrl = function() {
                var pageSize = $scope.pagingOptions.pageSize;
                var page = $scope.pagingOptions.currentPage;
                var searchText = $scope.filterOptions.filterText;
                var start = (page - 1) * pageSize;
                var url = $scope.actionUrls.list;
                if (url.indexOf("?") === -1) {
                    url += "?";
                } else {
                    url += "&";
                }
                url += "start=" + start + "&length=" + pageSize;
                if (searchText) {
                    url += "&search=" + searchText;
                }
                var column = $scope.sortInfo.fields;
                if (column) {
                    var order = $scope.sortInfo.directions;
                    url += "&column=" + column + "&order=" + order;
                }
                var status = $scope.filterStatus;
                if (status) {
                    url += "&status=" + status;
                }
                return url;
            };
            var requestForUsers = null;
            $scope.loadData = function () {
                var url = buildUrl();
                setTimeout(function () {
                    $scope.gridLoading = true;
                    // cancel previous request
                    if (requestForUsers) {
                        requestForUsers.abort();
                    }
                    (requestForUsers = AbortableRequestService.request('get', url)).then(
                        function (result) {
                            request = null;
                            var data = result.data;
                            $scope.totalServerItems = result.recordsTotal;
                            $scope.myData = data;
                            $scope.totalPages = Math.ceil(data.length / $scope.pagingOptions.pageSize);
                            $scope.gridLoading = false;
                            if (!$scope.$$phase) {
                                $scope.$apply();
                            }
                        },
                        function() {
                            request = null;
                        }
                    );
                }, 100);
            };

            $scope.showConfirm = function() {
                var tempUrl = '/js/custom-angularjs/templates/admin/user/modals/grid-delete.html';
                if ($scope.action === 'restore') {
                    tempUrl = '/js/custom-angularjs/templates/admin/user/modals/grid-restore.html';
                }
                $modal.open({
                    templateUrl: tempUrl,
                    controller: GridConfirmModalController
                });
            };
            $scope.create = function() {
                $modal.open({
                    templateUrl: '/js/custom-angularjs/templates/admin/user/modals/create.html',
                    controller: CreateController
                });
            };
            $scope.applyAction = function(callback) {
                var selectedItems = $scope.gridOptions.selectedItems;
                var length = selectedItems.length;
                if (length) {
                    var ids = [];
                    for (var i = 0; i < length; i++) {
                        ids.push(selectedItems[i].user_id);
                    }
                    $scope.gridLoading = true;
                    var url = "";
                    switch ($scope.action) {
                        case "delete":
                            url = $scope.actionUrls.delete;
                            $scope.gridLoadingMessage = "Deleting...";
                            break;
                        case "restore":
                            url = $scope.actionUrls.restore;
                            $scope.gridLoadingMessage = "Restoring...";
                            break;
                    }
                    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
                    var param = $.param({ ids: ids });
                    $http.post(url, param).success(function() {
                        selectedItems = [];
                        $scope.loadData();
                        $scope.gridLoadingMessage = "Reloading...";
                        if (callback) {
                            callback();
                        }
                    });
                }
            };

            $scope.loadData();

            $scope.$watch('pagingOptions', function (newVal, oldVal) {
                if (newVal !== oldVal) {
                    $scope.loadData();
                }
            }, true);
            $scope.$watch('filterOptions', function (newVal, oldVal) {
                if (newVal !== oldVal) {
                    $scope.loadData();
                }
            }, true);
            $scope.$watch('sortInfo', function () {
                $scope.loadData();
            }, true);

            var rowHeight = 45;
            $scope.gridOptions = {
                data: 'myData',
                enablePaging: true,
                showFooter: true,
                showPageSizeSelector: false,
                showTotalSelectInfo: false,
                totalServerItems: 'totalServerItems',
                pagingOptions: $scope.pagingOptions,
                filterOptions: $scope.filterOptions,
                useExternalSorting: true,
                sortInfo: $scope.sortInfo,
                enableCellSelection: false,
                enableRowSelection: true,
                showSelectionCheckbox: true,
                selectedItems: [],
                columnDefs: [
                    {
                        field: 'is_enabled',
                        displayName: 'Status',
                        enableCellEdit: false,
                        sortable: false,
                        headerClass: 'text-center',
                        width: 60,
                        cellTemplate: '<div class="text-center cell" style="width:60px;height:' + rowHeight + 'px;display:table-cell;"><i class="fa fa-circle" ng-class="{\'icon-disabled\' : row.getProperty(col.field) == \'0\', \'icon-enabled\' : row.getProperty(col.field) == \'1\'}"></i></div>'
                    },
                    {
                        field:'firstname',
                        displayName:'First name',
                        enableCellEdit: false,
                        headerClass: 'text-center',
                        cellTemplate: '<a href="/admin/user/edit/{{ row.getProperty(\'user_id\') }}" class="btn btn-link cell" ng-class="col.colIndex()" title="Edit this user">{{ row.getProperty(col.field) }}</a>'
                    },
                    {
                        field:'lastname',
                        displayName:'Last name',
                        enableCellEdit: false,
                        headerClass: 'text-center',
                        cellTemplate: '<a href="/admin/user/edit/{{ row.getProperty(\'user_id\') }}" class="btn btn-link cell" ng-class="col.colIndex()" title="Edit this user">{{ row.getProperty(col.field) }}</a>'
                    },
                    {
                        field:'email',
                        displayName:'Email',
                        enableCellEdit: false,
                        headerClass: 'text-center',
                        width: 240,
                        cellTemplate: '<a href="/admin/user/edit/{{ row.getProperty(\'user_id\') }}" class="btn btn-link cell" ng-class="col.colIndex()" title="Edit this user">{{ row.getProperty(col.field) }}</a>'
                    },
                    {
                        field:'app_count',
                        displayName:'Application',
                        enableCellEdit: false,
                        sortable: false,
                        headerClass: 'text-center',
                        cellTemplate:   '<a href="/admin/user/app/{{ row.getProperty(\'user_id\') }}"\n\
                                            class="btn btn-link cell"\n\
                                            ng-class="col.colIndex()"\n\
                                            ng-switch="{{ row.getProperty(col.field) }}"\n\
                                        >\n\
                                            <i class="fa fa-tasks fa-lg"></i> \n\
                                            <span ng-switch-when="0">Add app</span>\n\
                                            <span ng-switch-when="1">1 app</span>\n\
                                            <span ng-switch-default>{{ row.getProperty(col.field) }} apps</span>\n\
                                        </a>'
                    },
                    {
                        field:'org_count',
                        displayName:'Organization',
                        enableCellEdit: false,
                        sortable: false,
                        headerClass: 'text-center',
                        cellTemplate:   '<a href="/admin/user/org/{{ row.getProperty(\'user_id\') }}"\n\
                                            class="btn btn-link cell"\n\
                                            ng-class="col.colIndex()"\n\
                                            ng-switch="{{ row.getProperty(col.field) }}"\n\
                                        >\n\
                                            <i class="fa fa-sitemap fa-lg"></i> \n\
                                            <span ng-switch-when="0">Add org</span>\n\
                                            <span ng-switch-when="1">1 org</span>\n\
                                            <span ng-switch-default>{{ row.getProperty(col.field) }} orgs</span>\n\
                                        </a>'
                    },
                    {
                        field:'log_url',
                        displayName:'Log',
                        enableCellEdit: false,
                        sortable: false,
                        headerClass: 'text-center',
                        cellTemplate:   '<a href="/admin/user/log/{{ row.getProperty(\'user_id\') }}" class="btn btn-link cell" ng-class="col.colIndex()" title="View log">\n\
                                            <i class="fa fa-list-alt fa-lg"></i> View log\n\
                                        </a>'
                    },
                    {
                        field:'role',
                        displayName:'Role',
                        enableCellEdit: false,
                        sortable: false,
                        headerClass: 'text-center',
                        cellClass: 'cell'
                    },
                    {
                        field:'last_updated',
                        displayName:'Last updated',
                        enableCellEdit: false,
                        headerClass: 'text-center',
                        cellFilter: 'date:\'MM/dd/yyyy\'',
                        cellClass: 'cell'
                    }
                ],
                checkboxCellTemplate: '<div class="ngSelectionCell cell" style="height:50px;width:25px;text-align:center;"><input tabindex="-1" class="ngSelectionCheckbox" type="checkbox" ng-checked="row.selected" /></div>',
                jqueryUITheme: false,
                showFilter: false,
                rowHeight: rowHeight,
                headerRowHeight: rowHeight,
                footerTemplate: '<div ng-show="showFooter">\n\
                                    <div ng-show="gridOptions.showTotalSelectInfo" class="ngTotalSelectContainer">\n\
                                        <div class="ngFooterTotalItems" ng-class="{\'ngNoMultiSelect\': !multiSelect}" >\n\
                                            <span class="ngLabel">{{i18n.ngTotalItemsLabel}}{{maxRows()}}</span>\n\
                                            <span ng-show="filterText.length > 0" class="ngLabel">({{i18n.ngShowingItemsLabel}} {{totalFilteredItemsLength()}})</span>\n\
                                        </div>\n\
                                        <div class="ngFooterSelectedItems" ng-show="multiSelect">\n\
                                            <span class="ngLabel">{{i18n.ngSelectedItemsLabel}} {{selectedItems.length}}</span>\n\
                                        </div>\n\
                                    </div>\n\
                                    <div style="text-align:right;">\n\
                                        <div ng-show="gridOptions.showPageSizeSelector" style="display:inline-block;" class="ngRowCountPicker">\n\
                                            <select style="height: 32px;width: 70px;position:relative;top:-32px;" ng-model="pagingOptions.pageSize" >\n\
                                                <option ng-repeat="size in pagingOptions.pageSizes">{{size}}</option>\n\
                                            </select>\n\
                                        </div>\n\
                                        <pagination total-items="totalServerItems" items-per-page="pagingOptions.pageSize" ng-model="pagingOptions.currentPage"></pagination>\n\
                                    </div>\n\
                                </div>'
            };

            $scope.setPage = function (pageNo) {
                $scope.pagingOptions.currentPage = pageNo;
            };

            $scope.$on("modalConfirmed", function () {
                $scope.applyAction(function() {
                    broadcastService.broadcast("handlingCompleted");
                });
            });
            // reload grid when create user completed
            $scope.$on("createUserCompleted", function () {
                if ($scope.displayingGrid === "users") {
                    $scope.gridLoadingMessage = "Reloading...";
                    $scope.loadData();
                }
            });

            $scope.$on("$routeChangeSuccess", function (event, current) {
                var action = current.$$route.action;
                switch(action) {
                    case "delete":
                        $scope.actionUrls.list = "/admin/user/list";
                        $scope.displayingGrid = "users";
                        break;
                    case "restore":
                        $scope.actionUrls.list = "/admin/user/list?status=deleted";
                        $scope.displayingGrid = "deleted-users";
                        break;
                }
                $scope.action = action;
                $scope.gridLoading = true;
                $scope.gridLoadingMessage = "Loading...";
            });
        }
    ]);
    var GridConfirmModalController = function ($scope, $modalInstance, broadcastService) {
        $scope.ok = function () {
           broadcastService.broadcast("modalConfirmed");
        };
        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
        $scope.$on("handlingCompleted", function () {
            $modalInstance.close('completed');
        });
    };
    var CreateController = function ($scope, $modalInstance, AbortableRequestService, broadcastService) {
        $scope.status = {
            loading: false,
            error: false
        };
        $scope.data = {};
        var request = null;
        $scope.close = function() {
            if (request) {
                request.abort();
            }
            $modalInstance.close();
        };
        $scope.submit = function() {
            $scope.status.loading = true;
            if (request) {
                request.abort();
            }
            var url = "/admin/user/create";
            // use this flag to tell server return json
            $scope.data.async = true;
            // @TODO: fake data. Remove later when use rest api
            $scope.data.email_confirm = $scope.data.email;
            $scope.data.role = 'admin';
            (request = AbortableRequestService.request('post', url, $.param($scope.data))).then(
                function (r) {
                    $scope.status.loading = false;
                    if (r.error) {
                        $scope.status.error = true;
                        $scope.message = r.message;
                    } else {
                        $scope.close();
                        $scope.status.success = true;
                        broadcastService.broadcast("createUserCompleted");
                    }
                },
                function() {
                    alert("Something wrong happened to server. Please try again later.");
                    $scope.close();
                }
            );
        };
    };
})();