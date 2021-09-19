<HTML>
<HEAD>

<LINK rel="stylesheet"
      type="text/css"
      href="<?php echo $this->dojo()->getCdnBase(); ?><?php
        echo $this->dojo()->getCdnVersion(); ?>/dojox/grid/resources/Grid.css" />

<?php
    if ( !isset( $_GET['SupplierNo'])) {
        echo $this->dojo();
    }
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iP');
?>

<style type="text/css">
    html, body { width: 100%; height: 100%; margin: 0; overflow:hidden; }
    #borderContainer { width: 100%; height: 100%; }
</style>

<script>
function padding_left(s, c, n) {
    if (! s || ! c || s.length >= n) {
        return s;
    }

    var max = (n - s.length)/c.length;
    for (var i = 0; i < max; i++) {
        s = c + s;
    }

    return s;
}

    function getErpData() {
        gaugeStore.url="../../../../jsonGauge.php?execute=2";
        gaugeStore.close();
        gaugeStore.fetch( {
            query:      { name : '*' },
            onComplete: updateErp
            }
        );
    }

    function getGoogleData() {
	newsStore.close();
	newsStore.fetch( {
	    query: { text: "gumstix" },
	    onComplete: updateNews
	    }
	)

        searchStore.close();
        searchStore.fetch( {
            query: { text: "gumstix" },
            onComplete: updateSearch,
	    count: 8,
	    start: 0,
	    onError: onErrorFunction
            }
        )
    }
    var onErrorFunction = function() {
            console.log("An error occurred getting Google Search data");
    }

    function updateErp( items, request ) {
        var i;
	var item;
	var dijitName;
	var dojoName;
        for (i = 0; i < items.length; i++) {
                item = items[i];
		dijitName = dijit.byId( gaugeStore.getValue( item, "name" ));
		if ( dijitName ) {
			if ( gaugeStore.getValue( item, "type" ) == 'text' ) {
				dijitName.attr( "value", dijitName.attr("placeHolder") +": " + padding_left( gaugeStore.getValue( item, "value" ), ' ',  8)  );
			} else {
				dijitName.update( gaugeStore.getValue( item, "value" ));
			}
		}
	}
    }

    function updateNews( items, request ) {
        var dijitName;
        var item = items[0];
	dijitName = dijit.byId( "F_NewsCount" );
	if ( dijitName ) {
		dijitName.attr( "value", dijitName.attr("placeHolder") +": " + padding_left( items.length, ' ',  8)  );
	}
    }

    function updateSearch( items, request ) {
        var dijitName;
	var item = items[0];
        dijitName = dijit.byId( "F_SearchCount" );
        if ( dijitName ) {
		if ( items.length == 0) {
                	dijitName.attr( "value", dijitName.attr("placeHolder") +": " + padding_left( 0, ' ',  8)  );
		} else {
			dijitName.attr( "value", dijitName.attr("placeHolder") +": " + padding_left( searchStore.getValue( item, "estimatedResultCount" ), ' ',  8)  );
        	}
	}
    }

    setInterval( "getErpData()",   10000);
    setInterval( "getGoogleData()",30000);

</script>

</HEAD>

<BODY CLASS="claro" >

<div dojoType="dojo.data.ItemFileReadStore" url="../../../../jsonGauge.php?execute=1" jsid="gaugeStore" clearOnClose="true"></div>
<div dojoType="dojox.data.GoogleNewsSearchStore" jsId="newsStore"></div>
<div dojoType="dojox.data.GoogleSearchStore"     jsId="searchStore"></div>

<center><table cellspacing=0 border=0 cellpadding=0>
    <tr><td colspan=1>
        <div dojoType="dojox.widget.AnalogGauge" width="250"     height="100"
             cx="110" cy="98" radius="80" startAngle="-90" endAngle="90" useRangeStyles="0"
             hideValues="true"
             color="black"
             majorTicks="{length: 10, offset: 70, interval: 50, color: 'gray'}"
             minorTicks="{length:  5, offset: 75, interval: 10, color: 'ltgray'}"  >

            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="0" id="OnlineCount" width="8" length="70" color="red">  </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="0" id="OnlineWithin30" width="6" length="60" color="blue"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="0" id="Order_OS_Orders" width="6" length="60" color="yellow"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="0" id="Order_DI_Orders" width="6" length="60" color="green"> </div>
            <div dojoType="dojox.widget.gauge.Range" low="0" high="200" color="{'color': 'black'}">
            </div>
        </div>
    </td><td colspan=1>
        <div dojoType="dojox.widget.AnalogGauge" width="250" height="100"
             cx="110" cy="98" radius="80" startAngle="-90" endAngle="90" useRangeStyles="0"
             hideValues="true"
             color="white"
             majorTicks="{length: 10, offset: 70, interval: 100000, color: 'gray'}"
             minorTicks="{length:  5, offset: 70, interval:  50000, color: 'gray'}"  >

            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="20" id="mtdOnlineActual"  width="6" length="60" color="blue"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="10" id="Order_OS_Backlog" width="8" length="70" color="red"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="10" id="Order_OS_Total" width="8" length="70" color="yellow"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="20" id="mtdOnlineBudget"  width="6" length="60" color="green"> </div>
            <div dojoType="dojox.widget.gauge.Range" low="0" high="500000" color="{'color': 'black'}">
            </div>
        </div>
    </td>
    <td colspan=1>
        <div dojoType="dojox.widget.AnalogGauge" width="250"     height="100"
             cx="110" cy="98" radius="80" startAngle="-90" endAngle="90" useRangeStyles="0"
             hideValues="true"
             color="white"
             majorTicks="{length: 10, offset: 70, interval: 100000, color: 'gray'}"
             minorTicks="{length:  5, offset: 70, interval:  50000, color: 'gray'}"  >

            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="20" id="mtdOEMActual"  width="6" length="60" color="blue"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="10" id="Order_DI_Backlog" width="8" length="70" color="red"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="10" id="Order_DI_Total" width="8" length="70" color="yellow"> </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="1" id="mtdOEMBudget" width="6" length="60" color="green"> </div>
            <div dojoType="dojox.widget.gauge.Range" low="0" high="500000" color="{'color': 'black'}">
            </div>
        </div>
    </td><td colspan=1>
        <div dojoType="dojox.widget.AnalogGauge" width="250" height="100"
             cx="110" cy="98" radius="80" startAngle="-90" endAngle="90" useRangeStyles="0"
             hideValues="false"
             color="white"
             majorTicks="{length: 10, offset: 70, interval: 10, color: 'gray'}"
             minorTicks="{length:  5, offset: 70, interval:  5, color: 'gray'}"
             >
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="0" id="TotalSweeps" width="8" length="70" color="red">  </div>
            <div dojoType="dojox.widget.gauge.AnalogNeedleIndicator" value="1" width="6" length="60" color="blue"> </div>

            <div dojoType="dojox.widget.gauge.Range" low="0" high="100" color="{'color': 'black'}">
            </div>
        </div>
    </td>
    </tr>
    <tr bgcolor=#DDDDDD valign="top" style="font-family:monospace;font-size:9pt;" >
	<td color=#FFFFFF><center> Order counts </td>
	<td color=#FFFFFF><center> Online </td>
	<td color=#FFFFFF><center> OEM </td>
	<td color=#FFFFFF><center> Quotations </td>
    </tr>
    <tr bgcolor=#DDDDDD valign="top" style="font-family:monospace;font-size:9pt;" >
        <td><center>
		<div dojoType="dijit.form.TextBox" id="F_OnlineWithin30"    placeHolder="   Baskets"      style="{ color: blue;   background-color: #DDDDDD }" ></div><BR>
		<div dojoType="dijit.form.TextBox" id="F_OnlineCount"       placeHolder="     Users"      style="{ color: red;    background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Order_OS_Orders"   placeHolder="osC Orders"      style="{ color: yellow; background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Order_DI_Orders"   placeHolder="OEM Orders"      style="{ color: green;  background-color: #DDDDDD }" ></div>
	</td>
        <td><center>
		<div dojoType="dijit.form.TextBox" id="F_mtdOnlineActual"   placeHolder="    MTD"   style="{ color: blue;   background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Order_OS_Backlog"  placeHolder="Backlog"   style="{ color: red;    background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Order_OS_Total"    placeHolder="  Total"   style="{ color: yellow; background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_mtdOnlineBudget"   placeHolder=" Budget"   style="{ color: green;  background-color: #DDDDDD }" ></div>
	</td>
        <td><center>
                <div dojoType="dijit.form.TextBox" id="F_mtdOEMActual"      placeHolder="    MTD"   style="{ color: blue;   background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Order_DI_Backlog"  placeHolder="Backlog"   style="{ color: red;    background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Order_DI_Total"    placeHolder="  Total"   style="{ color: yellow; background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_mtdOEMBudget"      placeHolder=" Budget"   style="{ color: green;  background-color: #DDDDDD }" ></div>
	</td>
        <td><center>
                <div dojoType="dijit.form.TextBox" id="F_NewsCount"          placeHolder="Google news"    style="{ color: blue;   background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_SearchCount"        placeHolder="Google links"   style="{ color: red;    background-color: #DDDDDD }" ></div><BR>
		<div dojoType="dijit.form.TextBox" id="F_Quotation_DI_Total" placeHolder="OEM Quotations" style="{ color: blue;   background-color: #DDDDDD }" ></div><BR>
                <div dojoType="dijit.form.TextBox" id="F_Quotation_RM_Total" placeHolder="RMA Quotations" style="{ color: gray;   background-color: #DDDDDD }" ></div>
	</td>
    </tr>
</table>

</BODY>
</HTML>
