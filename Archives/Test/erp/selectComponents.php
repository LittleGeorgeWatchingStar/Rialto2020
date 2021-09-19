<div id="gfxComponentsBoard"> </div>
<script>
        dojo.empty( dojo.byId("gfxComponentsBoard") );
        var drawing = dojox.gfx.createSurface(dojo.byId("gfxComponentsBoard"), 700, 100);
        drawing.createRect( { width: 700, height: 100, x: 0, y: 0 }).setFill("orange").setStroke("black");
</script>
