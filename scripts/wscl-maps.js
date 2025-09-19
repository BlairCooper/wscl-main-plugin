/**
 * @ts-check
 */

class MapMarker {
	constructor(markerInfo, map, openInfoWindows) {
		this.markerInfo = markerInfo;
		
		let marker = new google.maps.Marker({
			position: {lat: markerInfo['latitude'], lng: markerInfo['longitude']}, 
			map: map, 
			title: markerInfo['markerTitle'],
			icon: 'https://washingtonleague.org/wp-content/uploads/2021/08/WSCL_Logo_155x155_transparent-36x36.png'
			});
		let infoWindow = new google.maps.InfoWindow({content: markerInfo['windowContent'], ariaLabel: markerInfo['windowTitle']});

		marker.addListener("click", () => {
			if (0 != openInfoWindows.length) {
				openInfoWindows.pop().close();
			}

			infoWindow.open({anchor: marker, map});
			openInfoWindows.push(infoWindow);
			});
	}
}

function initMap(divId, mapData) {
	let openInfoWindows = []; 
	let mapCenter = {lat: 47.4803543, lng: -120.3197115};
                
	let map = new google.maps.Map (
		document.getElementById(divId),
		{zoom: 7, center: mapCenter, controlSize: 25, minZoom: 7, maxZoom: 15, streetViewControl: false}
		);

	mapData.forEach (function (markerInfo) {
		new MapMarker(markerInfo, map, openInfoWindows);	// NOSONAR - ok not to save instance
	}) 
}
