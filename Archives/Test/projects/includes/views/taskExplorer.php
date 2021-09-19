<?php
echo $this->doctype();
?>

<HTML>
<HEAD>

<style type="text/css">
    html, body { width: 100%; height: 100%; margin: 0; }
    .grid {
        width: 70em;
        height: 40em;
    }


</style>
<?php  echo $this->headLink();	?>
<?php  echo $this->dojo();		?>

<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojo/resources/dojo.css" >
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dijit/themes/claro/claro.css" >

<LINK rel="stylesheet" type="text/css"  href="http://archive.dojotoolkit.org/nightly/dojotoolkit/dojox/grid/resources/Grid.css" >
<LINK rel="stylesheet" type="text/css"  href="http://archive.dojotoolkit.org/nightly/dojotoolkit/dojox/grid/resources/claroGrid.css" >

<!--
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojox/grid/resources/Grid.css" >
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojox/grid/resources/claroGrid.css" >
--!>

<!--
<LINK rel="stylesheet" type="text/css"  href="/dojo/dojo/resources/dojo.css" >
<LINK rel="stylesheet" type="text/css"  href="/dojo/dijit/themes/claro/claro.css" >
<LINK rel="stylesheet" type="text/css"  href="/dojo/dojox/grid/resources/Grid.css" >
<LINK rel="stylesheet" type="text/css"  href="/dojo/dojox/grid/resources/claroGrid.css" >

--!>

</HEAD>
<BODY CLASS=<?php echo STYLE . '/' . STYLE;?>>

<div id="surface"></div>

<div dojoType="dojo.data.ItemFileReadStore"  url="gan2json.php?menu_name=Gumstix.gan" jsID="tree_1"></div>

<div dojoType="dijit.tree.ForestStoreModel" store="tree_1" childrenAttrs="children" openOnClick="true"  
     rootId="Projects" rootLabel="Projects" query="{type: 'folder'}"    jsID="ForestModel1" defaultOpen=false >

    <table  dojoType="dojox.grid.TreeGrid" treeModel="ForestModel1" class="claro" jsId="theGrid" onmouseover="findAndFill"  onclick="findAndFill"
            id="grid" showRoot="false" style="height: 400px" selectable="true"  columnReordering="true">
      <thead>
        <tr>
          <th name="name" field="name" width="500px"></th>
          <th name="start" field="start" width="50px"></th>
          <th name="images" field="_item" width="375px" formatter="cell_formatter"></th>
          <th name="duration" field="duration" width="50px"></th>
          <th name="id" field="id" width="50px"></th>
          <th name="type" field="type" width="50px"></th>
        </tr>
      </thead>
    </table>
    <script>
        
        function cell_formatter( theItem )  {
            return ( '<div id="gfx_' + theItem.id + '"> </div>' );
        }

        function fillThemIn( items, request ) {
            var newYear = new Date( 2011,0,1);
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                var itemNode =  dojo.byId( "gfx_" + item.id );
                if ( itemNode ) {
                    dojo.empty( itemNode );
                    var drawing = dojox.gfx.createSurface( itemNode , 300, 10);
                    var start_date_string = new String ( item.start );
                    var start_date        = new Date( start_date_string.substr(0,4), start_date_string.substr(5,2), start_date_string.substr(8,2) );
                    var start = ( start_date.getTime() - newYear.getTime() ) / (3600*1000*24);
                    drawing.createRect( { width: item.duration, height: 10, x: start , y: 0 }).setFill("blue").setStroke("black");
                }
            }
        }

        function findAndFill() {
            var a=tree_1.fetch( { query: {id: "*" }, onComplete: fillThemIn } );

        }
    </script>

</div>
<!--
<button onClick="findAndFill()">Gantt</button>
--!>
</BODY>
</HTML>