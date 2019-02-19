
$(document).ready(function () {
    $('#create-server').validate({
        onkeyup: false,
        rules: {
             title: {
                required: true
            },
            ip: {
                //@warning: very difficult to understand code
                //To validate both ipv4 - ipv6 
                //We need ipv4 here
                ipv4: {
                    depends: function (element) {
                        
                        /**
                         * Clone object methods of jquery validator
                         * @link http://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-clone-an-object
                         */
                        var methods = $.extend({}, $.validator.methods);
                        
                        /**
                         * ipv6 + ipv4 validation
                         */
                        methods.optional = function () {
                            return false;
                        };
                        //we need ipv6 here.
                        var isValid = methods.ipv6($(element).val(), element);
                        return !isValid;
                    }
                }
            },
            domain: {
                required: true,
                url: true
            }
        },
        messages: {
            ip: {
                ipv4: "Please enter a valid IP address."
            }
        }
    });
});