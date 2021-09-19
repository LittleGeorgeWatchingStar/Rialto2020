<button dojoType="dijit.form.Button" type="button">
        Click to show the lower panel.
        <script type="dojo/method" event="onClick" args="evt">
        bottompane.href= "../../test_gauge.php?format=1&&SupplierNo=1";
        bottompane.refresh();
        </script>
</button>

<button dojoType="dijit.form.Button" type="button">
        Click to show the full dashboard.
        <script type="dojo/method" event="onClick" args="evt">
        bottompane.href= "../../test_gauge.php?format=2&&SupplierNo=2";
        bottompane.refresh();
        </script>
</button>

<!--
<table id="dashGrid" jsid="dGrid" dojoType="dojox.grid.DataGrid" store="dashboardStore" clientSort=true query="{ queryID: '*' }"  rowsPerPage="3" >
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedVendor = dGrid.store.getValue(dGrid.getItem( clickEvent.rowIndex ),"name");
        var selectedFile   = dGrid.store.getValue(dGrid.getItem( clickEvent.rowIndex ),"queryId");
        var selectedMsg    = dGrid.store.getValue(dGrid.getItem( clickEvent.rowIndex ),"description");
        bottompane.href= "../../test_gauge.php?SupplierNo=" + selectedVendor + "&FileName=" + selectedFile + "&messageId=" + selectedMsg ;
        bottompane.refresh();
    </script>
    <thead>
        <tr>
            <th field="name"          width="150px" name="name"></th>
            <th field="queryID"       width="150px" name="queryID"></th>
            <th field="amount"        width="150px" name="amount"></th>
            <th field="description"   width="150px" name="description"></th>
        </tr>
    </thead>
</table>
--!>

