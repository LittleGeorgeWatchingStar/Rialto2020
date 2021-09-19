<div class="claro" dojoType="dijit.layout.BorderContainer" design="sidebar" gutters="true" liveSplitters="true" id="borderContainer">

	<div dojoType="dijit.layout.AccordionContainer" region="leading" style="width: 300px; "splitter="true">

<?php
foreach ( $_POST['MENUS'] as $menu_item  ) {
	$file_name	= $menu_item['file_name'];
	$title		= $menu_item['title'];
?>
		<div dojoType="dojo.data.ItemFileReadStore" <?php echo 'url="menu2json.php?menu_name=' . $file_name . '"'; ?>  jsid="tree_1">
		</div>

		<div dojoType="dijit.layout.ContentPane"  <?php echo 'title="' . $title  . '"'; ?> >
			<div dojoType="dijit.tree.ForestStoreModel" store="tree_1" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="false">  
					<script type="dojo/method" event="onClick" args="item">
						var theTarget = tree_1.getValue(item, "target");
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
}
?>
	</div>

	<div dojoType="dijit.layout.StackContainer" region="center" id="e1" jsid="stack1" style='height="30%"'>

		<div dojoType="dojo.data.ItemFileReadStore" url="jsonTable.php" jsid="projectStore" clearOnClose="true">
		</div>

        <div dojoType="dijit.layout.ContentPane" jsid="exp1" id="t1">
                <table id="projectGrid" jsid="pGrid" dojoType="dojox.grid.DataGrid" store="projectStore" clientSort=true query="{ name: '*' }"  rowsPerPage="10" >
                <script type="dojo/method" event="onRowClick" args="clickEvent">
					var selectedVendor = pGrid.store.getValue(pGrid.getItem( clickEvent.rowIndex ),"name");
					var selectedFile   = pGrid.store.getValue(pGrid.getItem( clickEvent.rowIndex ),"id");
					var selectedMsg    = pGrid.store.getValue(pGrid.getItem( clickEvent.rowIndex ),"description");
					bottompane.href= "../../test_gauge.php?SupplierNo=" + selectedVendor + "&FileName=" + selectedFile + "&messageId=" + selectedMsg ;
					bottompane.refresh();
				</script>
                  <thead>
                        <tr>
                                <th field="name"          width="150px" name="name"></th>
                                <th field="id"            width="150px" name="id"></th>
                                <th field="amount"        width="150px" name="amount"></th>
                                <th field="description"   width="150px" name="description"></th>
                        </tr>
                  </thead>
                </table>
	</div>

        <div dojoType="dojo.data.ItemFileReadStore" url="jsonEmail.php" jsid="emailStore" clearOnClose="true">
        </div>

        <div dojoType="dijit.layout.ContentPane" jsid="exp2" id="t2">
			<table id="emailGrid" jsid="eGrid" dojoType="dojox.grid.DataGrid" store="emailStore" clientSort=true query="{ emailID: '*' }"  rowsPerPage="10" >
                <script type="dojo/method" event="onRowClick" args="clickEvent">
					var selectedVendor = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"supplierNo");
					var selectedFile   = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"contents");
					var selectedMsg    = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"messageId");
					bottompane.href= "../../test_email.php?SupplierNo=" + selectedVendor + "&FileName=" + selectedFile + "&messageId=" + selectedMsg ;
					bottompane.refresh();
					this.edit.rowClick( clickEvent );
					this.selection.clickSelectEvent( clickEvent );
				</script>
			  <thead>
					<tr>
						<th field="emailID"        width="40px" name="emailID"></th>
						<th field="from"           width="350px" name="from"></th>
						<th field="subject"        width="250px" name="subject"></th>
						<th field="supplierNo"     width="25px" name="SupplierNo"></th>
						<th field="contents"       width="250px" name="Contents"></th>
					</tr>
			  </thead>
		</table>
	</div>

	</div>

	<div jsid="bottompane" dojoType="dojox.layout.ContentPane" splitter="true" region="bottom" style="height: 60%">
	</div>

</div>
