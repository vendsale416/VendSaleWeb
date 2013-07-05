<html>
<head>
<title>VendSale - Coming Soon</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content"VendSale allows the user to access the prices of produce and determine the availabiltiy of these goods in their area. After the user inputs the search radius and the name of the produce, they are given a list of the cheapest and closest stores as defines by their search terms." />
<link rel="icon" type="image/ico" href="/favicon.ico"/>
<link rel="stylesheet" type="text/css" href="css/template.css" />
</head>

<body>
<body bgcolor="ffffff">

<form name="top" action="./ser/read.php" method="GET">
	<input type="hidden" name="latlong"/>
	<input type="hidden" name="lat"/>
	<input type="hidden" name="lng"/>
	<div style="text-align: center;">
		<img style="width: 1000px; height: 90px; position: relative;" alt="VendSale" src="res/MainBanner.png"><br>
	</div>
	<input type="button" style=" background-image:url(res/searchIconTrans.png); width: 64px; height: 64px; 
		position: absolute; top: 30px; left: 210px; opacity:0.6; filter:alpha(opacity=60)" 
		onclick="shopt()">
	<input type="button" style="background-image:url(res/vendorsIconTrans.png); width: 64px; height: 64px; 
		position: absolute; top: 30px; left: 375px;  opacity:0.6; filter:alpha(opacity=60)" 
		onclick="changeme()">
	<input type="button" style="background-image:url(res/settingsIconTrans.png); width: 64px; height: 64px; 
		position: absolute; top: 30px; left: 1080px; opacity:0.6; filter:alpha(opacity=60)"
		onclick="changeme()">
	<input type="button" style="background-image:url(res/listIconTrans.png); width: 64px; height: 64px; 
		position: absolute; top: 30px; left: 930px; opacity:0.6; filter:alpha(opacity=60)"
		onclick="changeme()">
		
	<INPUT type="TEXT" value="Search..." name="ser" id="ser" onkeydown="if (event.keyCode == 13) changeme()"
		style="position: absolute; top:100px; left:190px; z-index:5; display:none;">
	<INPUT type="TEXT" value="Radius..." name="rad" id="rad" onkeydown="if (event.keyCode == 13) drawBounds(this.form)"
		style="position: absolute; top:125px; left:190px; z-index:5; display:none;">
</form>


<!---Google Map start --->
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <article>
		
      <p><span id="status"><img src="loading.gif" width="40" height="40"><font size = "5">Finding your location...</font></span></p>
    </article>
<!---Google Map end--->

<script>
 
 // TODO:
 // 	send/receive info from db 
 // 	make web page pretty with icons
 
var map;
var latlng;
var circ;
var circleBounds;
var cnt = 0;

var directionsDisplay;
var directionsService = new google.maps.DirectionsService();

var userMarker;						// For the user					
var frstMarkerColor = "0000FF";		// For the first place store
var scndMarkerColor = "FF0000";		// For the second place store
var rstMarkerColor = "FCD116";		// For are the other stores
var frstMarkerImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + frstMarkerColor,
	new google.maps.Size(21, 34),
    new google.maps.Point(0,0),
    new google.maps.Point(10, 34));	
var scndMarkerImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + scndMarkerColor,
	new google.maps.Size(21, 34),
    new google.maps.Point(0,0),
    new google.maps.Point(10, 34));	
var rstMarkerImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + rstMarkerColor,
	new google.maps.Size(21, 34),
    new google.maps.Point(0,0),
    new google.maps.Point(10, 34));	
    
var stores = [];			// Will hold all the markers for the stores
        
function success(position) {

  directionsDisplay = new google.maps.DirectionsRenderer();
  var s = document.querySelector('#status');
  
  var w = window.innerWidth;
  var h = window.innerHeight;
  if (s.className == 'success') {
    // not sure why we're hitting this twice in FF, I think it's to do with a cached result coming back    
    return;
  }
  
  s.innerHTML = "";
  s.className = '';
  
  var mapcanvas = document.createElement('div');
  mapcanvas.id = 'mapcanvas';
  mapcanvas.style.height = h-140;
  mapcanvas.style.width = 1000;
  mapcanvas.style.left = "183px";
  mapcanvas.style.top = "100px";
  mapcanvas.style.position = "absolute";
  
  document.querySelector('article').appendChild(mapcanvas);
 
  latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
  CNTower = new google.maps.LatLng(43.642604,-79.387117);
  altlatlng = new google.maps.LatLng(position.coords.latitude+0.001, position.coords.longitude+0.001);
  altlatlngtwo = new google.maps.LatLng(position.coords.latitude-0.002, position.coords.longitude-0.002);
  altlatlngthree = new google.maps.LatLng(position.coords.latitude+0.0015, position.coords.longitude-0.0015);
  altlatlngfour = new google.maps.LatLng(position.coords.latitude-0.0015, position.coords.longitude-0.0015);
  
  var myOptions = {
    zoom: 15,
    center: latlng,
    mapTypeControl: false,
    //navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map = new google.maps.Map(document.getElementById("mapcanvas"), myOptions);
  directionsDisplay.setMap(map);
  
 //user location
  var marker = new google.maps.Marker({
      position: latlng, 
      map: map, 
	  clickable: false,
      title:"You are here! (at least within a "+position.coords.accuracy+" meter radius)"
  });
  
  circ = {									// Defines the circle with all it's properties
	     	strokeColor: '#00FF00',
	  	    strokeOpacity: 0.8,
	      	strokeWeight: 2,
	      	fillColor: '#00FF00',
	      	fillOpacity: 0.35,
	      	map: map,
	      	center: latlng,
	    };
	circleBounds = new google.maps.Circle(circ);		// Draws the circle to the map
	
	addStore(altlatlng);
	addStore(altlatlngtwo);
	addStore(altlatlngthree);
	addStore(altlatlngfour);
	addMarkers();

 //----lat+long extract to sumit query----START
document.top.latlong.value = latlng; /*split[1];*/
 var latlong = document.top.latlong.value;
 var lata = latlong.split(",");
 var lat = (lata[0].split("("));
 document.top.lat.value = lat[1]; //latitude set in textbox
 
 var longa = latlong.split(",");
 var lng1 = (longa[1].split(")"));
 var lng = (lng1[0].split(" "));
 document.top.lng.value = lng[1]; //longitude set in textbox
 
 
 
 //user location end
	
}
function error(msg) {
  var s = document.querySelector('#status');
  s.innerHTML = typeof msg == 'string' ? msg : "failed";
  s.className = 'fail';
  
  // console.log(arguments);
}

if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(success, error);
} else {
  error('not supported');
}

// Function that draws a circle around the user           	
function drawBounds(frm){
	//circleBounds.setRadius(500);
	circleBounds.setRadius(parseInt(frm.rad.value, 10));
}

// Function that adds a store (given location)
function addStore(loc){
	var mrk = new google.maps.Marker({		// Creates a new marker object given a location
      		position: loc, 
      		map: map, 
	  		clickable: true,
	  		visible: false,
	  		icon: rstMarkerImage,
  		});
	stores.push(mrk);						// Puts the marker in an array
	
	// TODO: replace info, need to make db call
	var inf = new google.maps.InfoWindow({
    		content: '<h1>item:price</h1><h4>name of store</h4>location<br />hours opertion/is open<br />'+
			'<a href="http://www.xkcd.com" target="_blank">store website</a>'
	    });
	google.maps.event.addListener(mrk, 'click', function() {
	   	inf.open(map, mrk);
	   	calcRt(loc)
	});
}

// Function that removes all the store marker
function removeMarkers(){
    for(var i=0; i < stores.length; i++){
        stores[i].setMap(null);				// Kills the marker
    }
    stores = [];							// Resets the array
}

// Adds all the markers to the map
function addMarkers(){
	if (stores[0] != null){						// So it does not goof
		stores[0].setIcon(frstMarkerImage);		// Specifies the first place marker icon
	}
	if (stores[1] != null){						// So it does not goof
		stores[1].setIcon(scndMarkerImage);		// Specifies the second place marker icon
	}
	var numStore = stores.length;
	for(var i = 0; i < numStore; i ++){
		stores[i].setVisible(true);				// Makes all the markers visible
	}
}

// Function that calcultes the route form the users position to another location(loc)
function calcRt(loc){
	var request = {
		origin: latlng,
		destination: loc,
		travelMode: google.maps.DirectionsTravelMode.DRIVING
	};
	directionsService.route(request, function(response, status) {
    	if (status == google.maps.DirectionsStatus.OK) {
      		directionsDisplay.setDirections(response);
    	}
  	});
}

function shopt(){
	cnt = cnt + 1;
	if (cnt%2 == 1){
		document.getElementById("ser").style.display="block";
		document.getElementById("rad").style.display="block";
	}else{
		document.getElementById("ser").style.display="none";
		document.getElementById("rad").style.display="none";
	}
}

function hidopt(){
	document.getElementById("ser").style.display="none";
	document.getElementById("rad").style.display="none";
}

</script>
