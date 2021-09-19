<div dojoType="dojo.data.ItemFileReadStore" url="jsonExplorer.php" jsid="id">
</div>

<div class="claro"  dojoType="dijit.layout.BorderContainer" design="sidebar" gutters="true" liveSplitters="true" id="borderContainer">
	<div dojoType="dijit.layout.AccordionContainer" region="leading" style="width: 300px; "splitter="true">

		<div dojoType="dijit.layout.ContentPane"  title="UserID 1">
			<div dojoType="dijit.tree.ForestStoreModel" store="id" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="false">  
					<script type="dojo/method" event="onClick" args="item">
							projectID.close();
							projectID.url = "jsonTable.php?choice=" + id.getValue(item, "choice");
							mainGrid.sort();
					</script>
				</div>
			</div>
		</div>

	</div>

	<div dojoType="dojo.data.ItemFileReadStore" url="jsonTable.php" jsid="projectID" clearOnClose="true">
	</div>

	<div dojoType="dijit.layout.ContentPane" splitter="true" region="center">
		<table id="grid" jsid="mainGrid" dojoType="dojox.grid.DataGrid" store="projectID" onRowClick="row_click()" clientSort=true query="{ name: '*' }"  rowsPerPage="10" >
		  <thead>
			<tr>
<?php
//	foreach ( $fields as $field ) {
//	echo '<th field="' . $field['name'] . '" width="' . $field['width'] . 'px" name="' . $field['name'] . '"  >' . $field['label'] . '</th>';
//}

?>
			</tr>
		  </thead>
		</table>
		<script>
			function row_click()
				{
				return document.getElementById("iFrameId").src = "http://www.gumstix.net";
				}
		</script>
	</div>

	<div jsid="bottompane" dojoType="dijit.layout.ContentPane" splitter="true" region="bottom">
		<iframe id="iFrameId" src="http://www.gumstix.com" width="100%" height="300">
		  <p>Your browser does not support iframes.</p>
		</iframe>
	</div>

</div>


