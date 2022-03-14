$ = jQuery;

let geocodeData = {};
//Get data about the user's location based on their IP
//So it can be used as the default location
jQuery.ajax({
    url: "https://json.geoiplookup.io/",
    success: function(result) {
        console.log(result);
        geocodeData = result;
    }
});
let numberOfLocations = 0;
let latitude = '';
let longitude = '';

const geocoder = new google.maps.Geocoder();
const getLatLngFromZip = (zip) => {
    geocoder.geocode({
        componentRestrictions: {
            postalCode: zip
        }
    }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            latitude = results[0].geometry.location.lat();
            longitude = results[0].geometry.location.lng();
            console.log(latitude + ", " + longitude);
        } else {
            console.log("Request failed.")
        }
    });
};
//Let's check for empty locations every now and again. It's a little more consistent than automatically checking for mutations to the dom
setInterval(function() {
    // Before bothering to do any lookups lets check to see if the number of locations have changed
    if ($('.location-fieldset-wrapper').length != numberOfLocations) {
        numberOfLocations = $('.location-fieldset-wrapper').length;
        $('.location-fieldset-wrapper').each(function() {
            let lat = $(this).find('.lat');
            let lng = $(this).find('.lng');
            let nickname = $(this).find('.nickname');
            let zip = $(this).find('.zip');
            if ($(lat).val() == 0 && $(lng).val() == 0) {
                // Set the values and manually trigger the change event
                $(lat).val(geocodeData.latitude);
                $(lng).val(geocodeData.longitude);
                $(nickname).val(geocodeData.city);
                $(zip).val(geocodeData.postal_code);
                $('body').prepend('<div id="loading-screen" class="overlay"><div class="modal">Loading...</div></div>');
                //Not 100% necessary but I want to give Drupal a little time to breathe before triggering the change event
                setTimeout(function() {
                    $(lat).change();
                    $(lng).change();
                }, 1000)
                setTimeout(function() {
                    $(zip).change();
                }, 2000)
                setTimeout(function() {
                    $(nickname).change();
                    $('#loading-screen').remove();
                }, 2500)
            }
        });
    }
}, 3000)