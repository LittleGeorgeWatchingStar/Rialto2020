<div id="gfxBoard"> </div>
<script>
    dojo.empty( dojo.byId("gfxBoard") );
    var drawing = dojox.gfx.createSurface(dojo.byId("gfxBoard"), 1000, 500);
    drawing.createRect( { width: 1000, height: 500, x: 0, y: 0 }).setFill("green").setStroke("black");

    function initDrawing() {
        var draw_them = function( items, request ) {
            var scale = 2;
            var xMin  = +items[0].x;
            var xMax  = xMin;
            var yMin  = +items[0].y;
            var yMax  = yMin;
            for (var k = 0; k < items.length; k++) {
                var item = items[k];
                if ( +item.x < xMin ) { xMin = +item.x; }
                if ( +item.y < yMin ) { yMin = +item.y; }
                if ( +item.x > xMax ) { xMax = +item.x; }
                if ( +item.y > yMax ) { yMax = +item.y; }
            }
            var toDrag = drawing.createRect( { width: scale*(1+xMax-xMin), height: scale*(1+yMax-yMin), x: scale*xMin, y: scale*yMin }).setFill("red").setStroke("black");
            for (var i = 0; i < items.length; i++) {
               var item = items[i];
                drawing.createRect( { width: scale, height: scale, x: scale*item.x, y: scale*item.y }).setFill("blue").setStroke("black");
            }
            new dojox.gfx.Moveable( toDrag );
        }
        var request = layout.fetch( { query: {name: "*"}, onComplete: draw_them } );
    }
</script>
