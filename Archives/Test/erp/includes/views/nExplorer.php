<?php
echo $this->doctype();
?>
<HTML>
<HEAD>
<?php  echo $this->dojo(); ?>

<link href="/css/themes/<?php echo STYLE; ?>/rialto.css"
    type="text/css"
    rel="stylesheet"
    media="screen" />

<style type="text/css">
    html, body { width: 100%; height: 100%; margin: 0; overflow:hidden; }
    #borderContainer { width: 100%; height: 100%; }
    #emailGrid, .test_email_main {
        font-size: 10pt;
    }
</style>

</HEAD>

<BODY>
<div dojoType="dojo.data.ItemFileReadStore" url="jsonTable.php?fID=4" jsid="projectStore" clearOnClose="true"> </div>
<div dojoType="dojo.data.ItemFileReadStore"
     url="/index.php/Purchasing/SupplierEmail"
     jsid="emailStore"
     clearOnClose="true"> </div>
<div dojoType="dojo.data.ItemFileReadStore" url="../../jsonGLProfit_Loss.php" jsid="accountStore" clearOnClose="true"> </div>
<div dojoType="dojo.data.ItemFileReadStore" url="eaglelayout.php?sheet=1" jsid="layout" clearOnClose="true"></div>
<div dojoType="dojo.data.ItemFileReadStore" url="../../jsonGauge.php?execute=1" jsid="dashboardStore" clearOnClose="true"> </div>

<div class="claro" dojoType="dijit.layout.BorderContainer" design="sidebar" gutters="true" liveSplitters="true" id="borderContainer">
	<!-- Start of Border Container -->

	<div dojoType="dijit.layout.AccordionContainer" region="leading" style="width: 300px;" splitter="true">  <!-- Start of Accordian Container -->

<?php
foreach ( $_POST['MENUS'] as $menu_item  ) {
	$file_name	= $menu_item['file_name'];
	$title		= $menu_item['title'];
	$type 		= $menu_item['type'];
	switch ( $type ) {
		case 'xml':
?>
		<div dojoType="dojo.data.ItemFileReadStore" <?php echo 'url="menu2json.php?menu_name=' . $file_name . '"'; ?>  jsid="menu_1">
		</div>
		<div dojoType="dojox.layout.ContentPane"  <?php echo 'title="' . $title  . '"'; ?> >
			<div dojoType="dijit.tree.ForestStoreModel" store="menu_1" rootId="id" rootLabel="id"  jsID="menuForestModel" childrenAttrs="children" >
			<div dojoType="dijit.Tree" model="menuForestModel" openOnClick="true" showRoot="false">
					<script type="dojo/method" event="onClick" args="item">
						var theTarget = menu_1.getValue(item, "target");
						switch ( theTarget ) {
							case "Emails":
									exp1.href = "selectEmail.php";
									exp1.refresh();
									break;
							case "Projects":
									projectStore.close();
									exp1.href = "selectProject.php";
									exp1.refresh();
									break;
							case "Profits":
									exp1.href = "selectDates.php";
									exp1.refresh();
									break;
							case "Gauges":
									exp1.href = "selectDashboard.php";
									exp1.refresh();
									break;
							default:alert(   " name is " + menu_1.getValue(item, "name")   + "\r\n" +
									 "image is " + menu_1.getValue(item, "image") + "\r\n" +
                                                                         " icon is " + menu_1.getValue(item, "icon")  );
						}
					</script>
				</div>
			</div>
		</div>
<?php
				break;
		case 'svn':
?>
		<div dojoType="dojo.data.ItemFileReadStore" <?php echo 'url="svn2jsonExplorer.php?select=' . $file_name . '"'; ?>  jsid="svn_1"></div>
		<div dojoType="dojox.layout.ContentPane"  <?php echo 'title="' . $title  . '"'; ?> >
			<div dojoType="dijit.tree.ForestStoreModel" store="svn_1" rootId="id" rootLabel="id"  jsID="svnForestModel" childrenAttrs="children" >
			<div dojoType="dijit.Tree" model="svnForestModel" openOnClick="true" showRoot="false">
					<script type="dojo/method" event="onClick" args="item">
						var theTarget = svn_1.getValue(item, "target");
						var theContainer = dijit.byId("e1");
						var theTabOne    = dijit.byId("t1");
						var theTabTwo    = dijit.byId("t2");
						switch ( theTarget ) {
							case "Emails":
								theContainer.selectChild( theTabTwo );
								emailStore.close();
								eGrid.sort();
								break;
							case "Projects":
								alert( 'projects' );
								theContainer.selectChild( theTabOne );
								projectStore.close();
								pGrid.sort();
								break;
							default:alert( theTarget+" is not yet implemented"  );
						}
					</script>
				</div>
			</div>
		</div>
<?php
				break;
		case 'proj':
?>
		<div dojoType="dojo.data.ItemFileReadStore"  url="gan2json.php?menu_name=Gumstix.gan" jsID="proj_1"></div>
		<div dojoType="dojox.layout.ContentPane"  <?php echo 'title="' . $title  . '"'; ?> >
			<div dojoType="dijit.tree.ForestStoreModel" store="proj_1" rootId="id" rootLabel="id"  jsID="projectForestModel" childrenAttrs="children" query="{ type: 'folder'}" >
				<div dojoType="dijit.Tree" model="projectForestModel" openOnClick="true" showRoot="false">
					<script type="dojo/method" event="onClick" args="item">
						var theTarget = proj_1.getValue(item, "target");
						var theContainer = dijit.byId("e1");
						var theTabOne    = dijit.byId("t1");
						var theTabTwo    = dijit.byId("t2");
						switch ( theTarget ) {
							case "Emails":
								theContainer.selectChild( theTabTwo );
								emailStore.close();
								eGrid.sort();
								break;
							case "Projects":
								theContainer.selectChild( theTabOne );
								projectStore.close();
								pGrid.sort();
								break;
                                                        case "Profits":
                                                                theContainer.selectChild( theTabOne );
								projectStore.url="../../jsonGLProfit_Loss.php";
                                                                projectStore.close();
                                                                pGrid.sort();
                                                                break;
							default:alert( theTarget+" is not yet implemented"  );
						}
					</script>
				</div>
			</div>
		</div>
<?php
				break;
		case 'jsconf':
?>
		<div dojoType="dojo.data.ItemFileReadStore" url="eagleJsonExplorer.php" jsid="jsConf_1"></div>
		<div dojoType="dojox.layout.ContentPane" title="Eagle">
			<script type="dojo/connect" event="onShow">
				exp1.href = "selectComponents.php";
				exp1.refresh();
				exp2.href = "startLayout.php";
				exp2.refresh();
			</script>
			<div dojoType="dijit.tree.ForestStoreModel" store="jsConf_1" rootId="id" rootLabel="id"  jsID="jsConfForestModel" childrenAttrs="children"  query="{ type: 'folder'}">
			<div dojoType="dijit.Tree" model="jsConfForestModel" openOnClick="true" showRoot="false">
					<script type="dojo/method" event="onClick" args="item">
							layout.url = "eaglelayout.php?board=" + jsConf_1.getValue(item,"schematic") + "&sheet=" + jsConf_1.getValue(item, "sheet");
							layout.close();
							initDrawing();
					</script>
				</div>
			</div>
		</div>
<?php
				break;
	}
?>
<?php
}
?>
	</div>		 <!-- End of Accordian Container -->

    <div jsid="exp1" id="listPane" title="top"      dojoType="dojox.layout.ContentPane" splitter="true" region="top"    style="height: 10%; background-color: #DDDDDD; " class="claro">	top    </div>

    <div jsid="exp2" id="detailPane" title="middle"   dojoType="dojox.layout.ContentPane" splitter="true" region="center" style="height: auto; background-color: #EEEEEE; " class="claro">	center </div>

	<div jsid="bottompane" id="bottomPane" title="bottom"   dojoType="dojox.layout.ContentPane" splitter="true" region="bottom" style="height: 10%; background-color: #000000; " class="claro">   bottom </div>

</div>			<!-- End of Border Container -->

</BODY>
</HTML>
