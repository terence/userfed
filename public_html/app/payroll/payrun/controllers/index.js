angular.module('App').controller('PayrunsIndexCtrl', [
    '$scope',
    '$modal',
    '$stateParams',
    '$sce',
    'Dictionary',
    '$http',
    function($scope, $modal, $stateParams, $sce, Dictionary, $http) {
        $scope.companyName = $stateParams.companyName;
        $scope.companyId = $stateParams.companyId;
        $scope.payruns = [];
        var loadPayruns = function() {
            $scope.loading = true;
            $http({
                 method: 'GET',
                 url: '/api/payroll/company/' + $scope.companyId + '/payrun'
            }).success(function(result){
                $scope.loading = false;
                $scope.payruns = result.payruns;
            }).error(function(){
                $scope.loading = false;
                alert("Server error");
            });
        };
        loadPayruns();
        var periods = Dictionary.get({type:'period-ending', company: $scope.companyId});
        
        $scope.openNewPayrunModal = function() {
            var modalInstance = $modal.open({
                templateUrl: '/app/payroll/payrun/templates/modals/new-payrun.html',
                controller: 'SubmitPayrunCtrl',
                backdrop: 'static',
                resolve: {
                    data: function() {
                        return {
                            periods : periods,
                            companyId : $scope.companyId
                        };
                    }
                }
            });

            modalInstance.result.then(function (data) {
                //show message
                if(data && data.message && data.message.length) {
                    $scope.message = $sce.trustAsHtml(data.message.join('<br/>'));
                } else {
                    $scope.message = '';
                }
                loadPayruns();
            });
        };
    }
]);
angular.module('App').controller('SubmitPayrunCtrl', [
    '$scope',
    '$modalInstance',
    'Payrun',
    'data',
    function($scope, $modalInstance, Payrun, data) {
        $scope.periods = data.periods;

        $scope.data = {
            company_id: data.companyId
        };
        
        $scope.cancel = function() {
            $modalInstance.dismiss();
        };
        $scope.create = function() {
            $scope.loading = true;
            Payrun.save({ company_id : data.companyId }, $scope.data, function(result) {
                $scope.loading = false;
                if (!result.error) {
                    $modalInstance.close();
                } else {
                    alert("Submit payrun failed.");
                }
            }, function() {
                alert("Oops! Server error. Please try again later.");
            });
        };
    }
]);