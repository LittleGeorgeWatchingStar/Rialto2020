<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load('visualization', '1', {packages:['gauge']});
	google.setOnLoadCallback(drawGauge);


function drawGauge() {
  var gaugeData = new google.visualization.DataTable();
  gaugeData.addColumn('number', 'Online');
  gaugeData.addColumn('number', 'Sales');
  gaugeData.addRows(2);
  gaugeData.setCell(0, 0, 10 );
  gaugeData.setCell(0, 1, 50);
  var gaugeOptions = {width: 200, height: 200, min: 0, max:180, yellowFrom: 200, yellowTo: 250,
      minorTicks: 5};
  var gauge;

  gauge = new google.visualization.Gauge(document.getElementById('gauge_div'));
  gauge.draw(gaugeData, gaugeOptions);
  var t = setTimeout( updateGauge, 1000 );

  var req =new XMLHttpRequest();

	function updateGauge() {
          gauge.draw(gaugeData, gaugeOptions);
          var t = setTimeout( updateGauge, 3000 );

	  req.onreadystatechange = processReqChange;
	  req.open("GET", "data.php", true);
	  req.send(null);
	}

	function processReqChange() {
    		if (req.readyState == 4) {
        		if (req.status == 200) {
				var response  = req.responseXML;
				var users    = response.getElementsByTagName('users')[0].firstChild.data;
				var sales    = response.getElementsByTagName('sales')[0].firstChild.data;
				if ( req.responseXML == null ) { 
					alert("There was a problem retrieving the XML data:\n" + req.statusText);
				}
				gaugeData.setValue(0, 0,  Number( users ) );
				gaugeData.setValue(0, 1,  Number( sales ) );
				gauge.draw(gaugeData, gaugeOptions);
			} else { 
        			alert("There was a problem retrieving the XML data:\n" + req.statusText);
         		}
    		}
	}
}	
</script>

	echo '<div id="gauge_div" align=center></div>';
        echo '<input type=hidden id=gauge_val value=10></input>';
