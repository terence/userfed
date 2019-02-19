angular.module('App').controller('CompanyTimesheetCtrl', [
    '$scope',
    '$modal',
    '$stateParams',
    'Dictionary',
    'Timesheet',
    function($scope, $modal, $stateParams, Dictionary, Timesheet) {
        var spinner = new Spinner({
            lines: 13, // The number of lines to draw
            length: 20, // The length of each line
            width: 10, // The line thickness
            radius: 30, // The radius of the inner circle
            corners: 1, // Corner roundness (0..1)
            rotate: 0, // The rotation offset
            direction: 1, // 1: clockwise, -1: counterclockwise
            color: '#000', // #rgb or #rrggbb or array of colors
            speed: 1, // Rounds per second
            trail: 60, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            top: '200%', // Top position relative to parent
            left: '50%' // Left position relative to parent
        }).spin(document.getElementById('loading'));
        $scope.companyName = $stateParams.companyName;
        $scope.companyId = $stateParams.companyId;
        $scope.timesheets = [];
        $scope.dataLoaded = false;
        var getTimesheets = function() {
            Timesheet.get({company_id: $scope.companyId}, function(result) {
                $scope.dataLoaded = true;
                $scope.timesheets = result.items;
            });
        };
        getTimesheets();
//        var periods = Dictionary.get({type:'period-ending', company: $scope.companyId});
//        var uploadUrl = "/api/payroll/company/" + $scope.companyId + "/timesheet";
//        
//        $scope.openUploadModal = function() {
//            var modalInstance = $modal.open({
//                templateUrl: '/app/payroll/company/templates/modals/upload-timesheets.html',
//                controller: 'UploadTimesheetsCtrl',
//                resolve: {
//                    periods: function() {
//                        return periods;
//                    },
//                    uploadUrl: function() {
//                        return uploadUrl;
//                    }
//                }
//            });
//
//            modalInstance.result.then(function () {
//                getTimesheets();
//            });
//        };
    }
]);
//angular.module('App').controller('UploadTimesheetsCtrl', [
//    '$scope',
//    '$modalInstance',
//    '$upload',
//    'periods',
//    'uploadUrl',
//    function($scope, $modalInstance, $upload, periods, uploadUrl) {
//        $scope.periods = periods;
//        $scope.cancel = function() {
//            $modalInstance.dismiss();
//        };
//        $scope.data = {};
//        $scope.progressPercentage = null;
//        $scope.upload = function (timesheetFile) {
//            $upload.upload({
//                method: 'POST',
//                url: uploadUrl,
//                file: timesheetFile,
//                fields: {period_ending : $scope.data.period_ending}
//            }).progress(function (evt) {
//                $scope.progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
//            }).success(function (data, status, headers, config) {
//                $scope.progressPercentage = null;
//                $modalInstance.close();
//            });
//        };
//    }
//]);