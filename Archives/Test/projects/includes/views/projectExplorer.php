<div class="claro"  dojoType="dijit.layout.BorderContainer" design="sidebar" gutters="true" liveSplitters="true" id="borderContainer">
	<div dojoType="dijit.layout.AccordionContainer" region="leading" style="width: 300px; "splitter="true">

<?php
foreach ( $_POST['MENUS'] as $menu_item  ) {
	$file_name	= $menu_item['file_name'];
	$title		= $menu_item['title'];
	$store      = $menu_item['store'];
?>
		<div dojoType="dijit.layout.ContentPane"  <?php echo 'title="' . $title  . '"'; ?> >
			<div dojoType="dojo.data.ItemFileWriteStore" 
                <?php echo ' url="gan2json.php?menu_name=' . $file_name . '"'; ?> 
                <?php echo ' jsid="' . $store .'">'; ?>
			</div>
            <table>
                  <tr dojoType="dojo.dnd.Source">
                      <td class="dojoDndItem" bgcolor=lime width=120px>NEW TASK</td>
                      <td class="dojoDndItem" bgcolor=yellow width=120px>NEW PROJECT</td>
                  </tr>
            </table>
			<div dojoType="dijit.tree.ForestStoreModel" store="tree_1" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="true" dndController="dijit.tree.dndSource">
					<script type="dojo/method" event="onClick" args="item">
						alert ( item.name  );
                        var drawing2 = dojox.gfx.createSurface( this.domNode , 100, 10 );
                        drawing2.createRect( { width: 50, height: 10, x: 0, y: 0 }).setFill("blue").setStroke("black");
                    </script>
				</div>
			</div>
		</div>
<?php
}
?>
		<div dojoType="dijit.layout.ContentPane"  title="Projects">
		    <table><tr><td>
				<table dojoType="dojo.dnd.Source">
                      <tr class="dojoDndItem"><td>Limes</td><td bgcolor=lime width=50px></td></tr>
                      <tr class="dojoDndItem"><td>Oranges</td><td bgcolor=orange width=50px></td></tr>
                      <tr class="dojoDndItem"><td>Pears</td><td bgcolor=green width=50px></td></tr>
                      <tr class="dojoDndItem"><td>Kiwis</td><td bgcolor=red width=50px></td></tr>
                      <tr class="dojoDndItem"><td>Nectarines</td><td bgcolor=peach width=50px></td></tr>
                      <tr class="dojoDndItem"><td>Bananas</td><td bgcolor=yellow width=50px></td></tr>
				</table>
		    </td><td>
				<table cellspacing=0>
                      <tr><td dojoType="dojo.dnd.Target" width=50px bgcolor=grey></td><td bgcolor=lime width=500px height=10px >Project name</td></tr>
                      <tr><td dojoType="dojo.dnd.Target" width=50px bgcolor=gray></td><td bgcolor=orange width=50px height=10px>Project name</td></tr>
                      <tr><td dojoType="dojo.dnd.Target" width=50px bgcolor=gray></td><td bgcolor=green width=50px  height=10px>Project name</td></tr>
                      <tr><td dojoType="dojo.dnd.Target" width=50px bgcolor=gray></td><td bgcolor=red width=50px  height=10px>Project name</td></tr>
                      <tr><td dojoType="dojo.dnd.Target" width=50px bgcolor=gray></td><td bgcolor=peach width=50px height=10px>Project name</td></tr>
                      <tr><td dojoType="dojo.dnd.Target" width=50px bgcolor=gray></td><td bgcolor=yellow width=50px height=10px>Project name</td></tr>
				</table>
		    </td></tr></table>
		</div>
	</div>

	<div dojoType="dijit.layout.ContentPane" splitter="true" region="center" jsid="divboard">
        <script>
            function dateDifference( startDate, endDate ) {
                return ( endDate.getTime() - startDate.getTime() ) / ( 3600*24*1000 );
            }
            function drawUs(items) {
				var drawing = dojox.gfx.createSurface(dojo.byId("gfxBoard"), 500, 400 );
                for (i = 0; i < items.length; i++) {
                    var item = items[i];
                    for (j = 0; j < item.children.length; j++) {
                        var task = item.children[j];
                        var str = String(task.start);
                        var startDate = new Date();
                        var endDate = new Date( str.substring(0,4), str.substring(5,7), str.substring(8,10) );
                        var a = dateDifference ( startDate, endDate );
                        var b = task.duration;
                        if ( j<10 ) {
                            //  alert ( a );
                        }
                        drawing.createRect( { width: b, height: 2, x: a, y: 6*j }).setFill("blue").setStroke("black");
                        
                    }
                }
            }
			function initDrawing( ) {
                // Get the list of tasks
                tree_1.fetch( { query: { name: "TASKS" }, onComplete: drawUs } );
            }
			//Set the init function to run when dojo loading and page parsing has completed.
			dojo.addOnLoad(initDrawing);
		</script>
		Checking.
	</div>

	<div dojoType="dijit.layout.ContentPane" splitter="true" region="bottom">
		<div id="gfxBoard">
		</div>
		    <table>
                  <tr dojoType="dojo.dnd.Source">
                      <td class="dojoDndItem" bgcolor=lime width=120px>NEW TASK</td>
                      <td class="dojoDndItem" bgcolor=yellow width=120px>NEW PROJECT</td>
                  </tr>
            </table>
	</div>
</div>