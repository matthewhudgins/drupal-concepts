$ = jQuery;

// Initialize and add the map
function initMap() {
    // The location of the center of the US
    const centerOfUSA = { lat: 44.967243, lng: -103.77155 };
    // The map, centered at the US
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 4,
        center: centerOfUSA,
    });
    // Loop through each lcoation and make clickable info windows that display their temperature
    $('.location').each(function() {
        const coord = { lat: $(this).data('lat'), lng: $(this).data('lng') };
        const temp = $(this).data('temp')
        const name = $(this).data('name');
        const marker = new google.maps.Marker({
            position: coord,
            map: map,
        });

        google.maps.event.addListener(marker, "click", () => {
            const infowindow = new google.maps.InfoWindow();
            infowindow.setContent(`<div class="title">${name}</div><div class="temp">${temp} °F</div>`);
            infowindow.open(map, marker);
        });
    });

}
if ($('#map')) {
    initMap();
}