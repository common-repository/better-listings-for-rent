// Access PHP data in JavaScript
function initMap() {
    var mapOptions = {
        center: { lat: 36.1817165, lng: -85.5083744 },
        zoom: 10
    };
    var mapElement = document.getElementById("googlemap");

    if(!mapElement){
        return;
    }

    var map = new google.maps.Map(document.getElementById("googlemap"), mapOptions);

    // Create markers and add them to the map
    var markers = JSON.parse(php_data.markers);

    for (var i = 0; i < markers.length; i++) {
        (function(index) {

            var marker = new google.maps.Marker({
                position: markers[index].position,
                map: map,
                title: markers[index].title
            });

            var infowindow = new google.maps.InfoWindow({
                content: markers[index].content
            });

            marker.addListener("click", function() {
                infowindow.open(map, marker);
            });
        })(i);
    }
}
