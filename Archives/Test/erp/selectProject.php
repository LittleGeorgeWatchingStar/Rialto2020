<div id="bugStore" dojoType="dojo.data.ItemFileReadStore" url="jsonTable.php?pID=28" jsid="bugStore" clearOnClose="true"> </div>
<div dojoType="dojo.data.ItemFileReadStore" url="jsonTable.php?pID=28" jsid="componentStore" clearOnClose="true"> </div>
<table id="projectGrid" jsid="pGrid" dojoType="dojox.grid.DataGrid" store="projectStore" clientSort=true query="{ name: '*' }"  rowsPerPage="10" >
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedProject= pGrid.store.getValue(pGrid.getItem( clickEvent.rowIndex ),"name");
        var selectedId     = pGrid.store.getValue(pGrid.getItem( clickEvent.rowIndex ),"id");
        var selectedMsg    = pGrid.store.getValue(pGrid.getItem( clickEvent.rowIndex ),"description");
	bugStore.close();
	componentStore.close();
	bugStore.url="jsonTable.php?pID=" + selectedId;
	componentStore.url="jsonTable.php?pID=" + selectedId;
        exp2.href= "../../formSample.php?pID=" + selectedId + '&dojoSource=1' ;
        exp2.refresh();
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
