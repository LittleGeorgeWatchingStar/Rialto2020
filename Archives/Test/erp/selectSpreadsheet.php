<table  jsid="aGrid" dojoType="dojox.grid.DataGrid" store="accountStore" clientSort=false query="{ name: '*' }"  rowsPerPage="10" >
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedVendor = aGrid.store.getValue(aGrid.getItem( clickEvent.rowIndex ),"name");
        var selectedFile   = aGrid.store.getValue(aGrid.getItem( clickEvent.rowIndex ),"id");
        var selectedMsg    = aGrid.store.getValue(aGrid.getItem( clickEvent.rowIndex ),"description");
        bottompane.refresh();
    </script>
    <thead>
            <tr>
                    <th field="name"      width="150px" name="name"></th>
                    <th field="M1"        width="60px" name="JAN"></th>
                    <th field="M2"        width="60px" name="FEB"></th>
                    <th field="M3"        width="60px" name="MAR"></th>
                    <th field="M4"        width="60px" name="APR"></th>
                    <th field="M5"        width="60px" name="MAY"></th>
                    <th field="M6"        width="60px" name="JUN"></th>
                    <th field="M7"        width="60px" name="JUL"></th>
                    <th field="M8"        width="60px" name="AUG"></th>
                    <th field="M9"        width="60px" name="SEP"></th>
                    <th field="M10"       width="60px" name="OCT"></th>
                    <th field="M11"       width="60px" name="NOV"></th>
                    <th field="M12"       width="60px" name="DEC"></th>
                    <th field="YTD"       width="60px" name="YTD"></th>
            </tr>
    </thead>
</table>
