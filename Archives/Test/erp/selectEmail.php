<table id="emailGrid" jsid="eGrid" dojoType="dojox.grid.DataGrid" store="emailStore" clientSort=true query="{ emailID: '*' }"  rowsPerPage="10" >
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedVendor = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"supplierNo");
        var selectedFile   = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"contents");
        var selectedMsg    = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"messageId");
        exp2.href= "/index.php/Purchasing/SupplierEmail/" + selectedVendor + "/" + encodeURIComponent(selectedFile);
        exp2.refresh();
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
