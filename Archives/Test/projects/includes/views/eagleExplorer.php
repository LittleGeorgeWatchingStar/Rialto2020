<div dojoType="dojo.data.ItemFileReadStore" url="eagleJsonExplorer.php" jsid="eid">
</div>

<div dojoType="dojo.data.ItemFileReadStore" url="eaglelayout.php?sheet=1" jsid="layout" clearOnClose="true">
</div>

<div dojoType="dojo.data.ItemFileReadStore" url="svn2jsonExplorer.php?select=PCB0" jsid="connex" clearOnClose="true">
</div>

<div dojoType="dojo.data.ItemFileReadStore" url="svn2jsonExplorer.php?select=PCB1" jsid="verdex" clearOnClose="true">
</div>

<div dojoType="dojo.data.ItemFileReadStore" url="svn2jsonExplorer.php?select=PCB3" jsid="overo" clearOnClose="true">
</div>

<div class="claro"  dojoType="dijit.layout.BorderContainer" design="sidebar" gutters="true" liveSplitters="true" id="borderContainer">

	<div dojoType="dijit.layout.AccordionContainer" region="leading" style="width: 300px; "splitter="true">

		<div dojoType="dijit.layout.ContentPane"  title="Eagle Modules">
			<div dojoType="dijit.tree.ForestStoreModel" store="eid" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" query="{ type: 'folder'}" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="false">  
					<script type="dojo/method" event="onClick" args="item">
							layout.close();
							layout.url = "eaglelayout.php?board=" + eid.getValue(item,"schematic") + "&sheet=" + eid.getValue(item, "sheet");
							layout.close();
							layout.fetch();
							init();
//							makeObjects();
					</script>
				</div>
			</div>
		</div>

		<div dojoType="dijit.layout.ContentPane"  title="connex boards">
			<div dojoType="dijit.tree.ForestStoreModel" store="connex" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="false">  
					<script type="dojo/method" event="onClick" args="item">
							layout.close();
							layout.url = "eaglelayout.php?sheet=" + eid.getValue(item, "sheet");
							layout.close();
							layout.fetch();
							init();
					</script>
				</div>
			</div>
		</div>

		<div dojoType="dijit.layout.ContentPane"  title="verdex boards">
			<div dojoType="dijit.tree.ForestStoreModel" store="verdex" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="false">  
					<script type="dojo/method" event="onClick" args="item">
							layout.close();
							layout.url = "eaglelayout.php?sheet=" + eid.getValue(item, "sheet");
							layout.close();
							layout.fetch();
							init();
					</script>
				</div>
			</div>
		</div>

		<div dojoType="dijit.layout.ContentPane"  title="overo boards">
			<div dojoType="dijit.tree.ForestStoreModel" store="overo" rootId="id" rootLabel="id"  jsID="ForestModel1" childrenAttrs="children" >
				<div dojoType="dijit.Tree" model="ForestModel1" openOnClick="true" showRoot="false">  
					<script type="dojo/method" event="onClick" args="item">
							layout.close();
							alert(  eid.getValue(item, "sheet") );
							layout.url = "eaglelayout.php?sheet=" + eid.getValue(item, "sheet");
							layout.url = "/gumstix-hardware/Production/PCB/" + eid.getValue(item, "name");

							layout.close();
							layout.fetch();

							init();
					</script>
				</div>
			</div>
		</div>
	</div>

	<div dojoType="dijit.layout.ContentPane" splitter="true" region="center" jsid="divboard">

		<div id="gfxBoard3d">
		</div>

		<script>
			dojo.require("dojox.gfx");
			dojo.require("dojox.gfx3d");
			dojo.require("dojox.gfx.utils");
			dojo.require("dijit.form.Button");

			function draw_them_3d( items, request ) {
				dojo.empty( dojo.byId("gfxBoard3d") );
				var surface = dojox.gfx.createSurface(dojo.byId("gfxBoard3d"), 500, 500);
				var view = surface.createViewport();
				view.setLights([{direction: {x: -10, y: -5, z: 5}, color: "white"}],  {color:"white", intensity: 2}, "white");
				var m = dojox.gfx3d.matrix;
				for (var i = 0; i < items.length; i++) {
				   var item = items[i];
				   view.createCube({bottom: {x: 4*item.x, y: 4*item.y, z: 10}, top: {x: 4*item.x+6, y: 4*item.y+6, z: 16}}).setFill({type: "plastic", finish: "dull", color: "blue"});
				}
			    view.createCube({bottom: {x: 0, y: 0, z: -2}, top: {x: 300, y: 200, z: 0}}).setFill({type: "plastic", finish: "dull", color: "lime"});
				var camera = [m.cameraRotateXg( 0 ), m.cameraRotateYg( 2 ), m.cameraTranslate(-100, -100, 0)];
				view.applyCameraTransform(camera);
				view.render();
			}

			function	makeObjects() {
				var request2 = layout.fetch( { query: {name: "*"}, onComplete: draw_them_3d });
			};
			dojo.addOnLoad(makeObjects);
			
		</script>
	</div>

	<div dojoType="dijit.layout.ContentPane" splitter="true" region="bottom">
		<script>
			function init() {
				//Create our surface.
				var drawing = dojox.gfx.createSurface(dojo.byId("gfxBoard"), 500, 500);
				var draw_them = function(items, request ) {
					for (var i = 0; i < items.length; i++) {
					   var item = items[i];
					   drawing.createRect( { width: 6, height: 6, x: 5*item.x, y: 5*item.y }).setFill("blue").setStroke("black");
					}
				}
				var request = layout.fetch( { query: {name: "*"}, onComplete: draw_them });
			}
			//Set the init function to run when dojo loading and page parsing has completed.
			dojo.addOnLoad(init);
		</script>
		<div id="gfxBoard">
		</div>
	</div>


</div>