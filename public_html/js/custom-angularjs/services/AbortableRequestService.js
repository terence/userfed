angular.module('app.services', []).service(
    "AbortableRequestService",
    function( $http, $q ) {
        function request(type, url, data) {
            var deferredAbort = $q.defer();
            // Initiate the AJAX request.
            var options = {
                method: type,
                url: url,
                timeout: deferredAbort.promise
            };
            if (data) {
                options.data = data;
            }
            if (type === 'post') {
                options.headers = {'Content-Type': 'application/x-www-form-urlencoded'};
            }
            var request = $http(options);
            var promise = request.then(
                function( response ) {
                    return( response.data );
                },
                function( response ) {
                    return( $q.reject( "Something went wrong" ) );
                }
            );

            promise.abort = function() {
                deferredAbort.resolve();
            };
            promise.finally(
                function() {
                    promise.abort = angular.noop;
                    deferredAbort = request = promise = null;
                }
            );
            return( promise );
        }
        // Return the public API.
        return({
            request: request
        });
    }
);