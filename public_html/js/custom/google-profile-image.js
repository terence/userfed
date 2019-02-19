/**
 * get profile image of google account.
 */
$(document).ready(function () {
    var size = 50;
    var googleAvatars = $('img').filter('[data-google-id]');
    googleAvatars.each(function (index, element) {
        var googleId = $(element).data('google-id');
        $.ajax({
            url : 'http://picasaweb.google.com/data/entry/api/user/' + googleId,
            data: {alt: 'json'},
            success: function (data) {
                var imageLink = data.entry.gphoto$thumbnail.$t;
                /**
                 * Make link to get an image with custom size
                 * Default is an image with width is 64px
                 * Way to custom size image: replace number 64 in part /s64-c/ with your number
                 * http://lh3.ggpht.com/-VhQ6Ra8dhL4/AAAAAAAAAAI/AAAAAAAAAAA/84bQXBvc48o/s64-c/110199642809922554030.jpg
                 */
                var profileImage = imageLink.replace('/s64-c/', '/s' + size +'-c/');
                $(element).attr('src', profileImage);
            }
        });
    });
});