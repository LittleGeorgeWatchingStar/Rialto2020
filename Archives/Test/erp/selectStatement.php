<table  jsid="aGrid" dojoType="dojox.grid.DataGrid" store="accountStore" clientSort=true query="{ name: '*' }"  rowsPerPage="10" >
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedVendor = aGrid.store.getValue(aGrid.getItem( clickEvent.rowIndex ),"name");
        var selectedFile   = aGrid.store.getValue(aGrid.getItem( clickEvent.rowIndex ),"id");
        var selectedMsg    = aGrid.store.getValue(aGrid.getItem( clickEvent.rowIndex ),"description");
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
