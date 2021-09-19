<?php
echo $this->doctype();
?>
<HTML>
<HEAD>


<?php  echo $this->dojo();		?>

<style type="text/css">
    html, body { width: 100%; height: 100%; margin: 0; overflow:hidden; }
    #borderContainer { width: 100%; height: 100%; }
</style>

</HEAD>

<BODY class='claro'>
    <div id="main" dojoType="dojox.mobile.View" selected="true">
        <h1 dojoType="dojox.mobile.Heading">Overview</h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-financial-categories.png"  moveTo="Payables">Payables</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/accessories-text-editor.png"  moveTo="Purchasing">Purchasing</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/mimetypes/package-x-generic.png"  moveTo="Stock">Stock</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/mimetypes/x-office-address-book.png"  moveTo="Sales">Sales</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/system-users.png"  onmouseover="selectReview('www.gumstix.net')" moveTo="HR">HR</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-documents-finances.png"  moveTo="Finance">Finance</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/categories/applications-system.png"  moveTo="Logistics">Logistics</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/edit-find.png" onmouseover="selectReview('www.gumstix.com')" moveTo="Marketing">Marketing</li>
            <script>
                function selectReview ( stringValue) {
                    stdoHeading.label =  stringValue ;
                }
            </script>
        </ul> 
    </div>
    
    <div id="Payables" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading">Payables</h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/places/mail-folder-inbox.png"  moveTo="emails">Review emailed invoices</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/mimetypes/application-pdf.png"  moveTo="stodo"> Entering PDF Invoice</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/status/mail-unread-new.png"  moveTo="stodo"> Reading UPS emails</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/status/mail-unread.png"  moveTo="stodo">Allocate uploaded payments</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/mail-forward.png"  moveTo="stodo"> Mark email as read</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/document-print.png"  moveTo="stodo"> Checks to print</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    
    <div id="Purchasing" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> Purchasing </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/edit-find-replace.png"  moveTo="stodo"> Correct stock levels</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/preferences-web-browser-shortcuts.png"  moveTo="stodo"> Place online orders</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/places/mail-folder-outbox.png"  moveTo="stodo"> Email orders</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    
    <div id="Stock" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> Stock </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/utilities-file-archiver.png"  moveTo="stodo"> Manage Stock Items</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/preferences-contact-list.png"  moveTo="stodo"> Manage stock quotations</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/configure.png"  moveTo="stodo"> Create customization</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/utilities-file-archiver.png"  moveTo="stodo"> Review stock errors</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/step.png"  moveTo="stodo"> Designs to review</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/status/image-loading.png"  moveTo="stodo"> Files to upload</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/kspread.png"  moveTo="stodo"> Review COGS</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    <div id="Sales" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> Sales </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-financial-transfer.png"  moveTo="stodo"> Allocate receipts</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/okteta.png"  moveTo="stodo"> Allocate sales details</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/dialog-ok-apply.png"  moveTo="stodo"> Approve sales order</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    <div id="HR" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> HR </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/mimetypes/application-vnd.ms-excel.png"  moveTo="stodo"> Submitting expenses</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/format-list-ordered.png"  moveTo="stodo"> Review priorities</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    <div id="Finance" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> Finance </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-financial-payment-mode.png"  moveTo="stodo"> SVB daily loads</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-bank-account-checking.png"  moveTo="stodo"> SVB balance checked</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-credit-card-account.png"  moveTo="stodo"> Alter sweeps</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-documents-finances.png"  moveTo="stodo"> Uploading a budget</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    <div id="Logistics" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> Logistics </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/flag-red.png"  moveTo="stodo"> Orders overdue orders</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/apps/utilities-file-archiver.png"  moveTo="stodo"> Kits to ship</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/view-calendar-day.png"  moveTo="stodo"> Kits completion date info</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/mimetypes/package-x-generic.png"  moveTo="stodo"> POs waiting for to ship</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/edit-table-insert-row-above.png"  moveTo="stodo"> What will be short</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    <div id="Marketing" dojoType="dojox.mobile.View"> <h1 dojoType="dojox.mobile.Heading"> Marketing </h1>
        <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/document-sign.png" moveTo="stodo"> Press stock text to validate</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/document-preview.png"  moveTo="stodo"> Tests to approve</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/document-new.png"  moveTo="stodo"> Stocking orders</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/document-export.png"  moveTo="stodo"> Samples sent out</li>
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    
    
    <div id="emails" dojoType="dojox.mobile.View" > <h1 dojoType="dojox.mobile.Heading"> eMails to read </h1>
        <div id="fileReading"  dojoType="dojo.data.ItemFileReadStore" url="jsonEmail.php" jsid="emailStore"   clearOnClose="true"> </div>
        <table jsid="eGrid" dojoType="dojox.grid.DataGrid" store="emailStore" query="{ emailID: '*' }" onRowClick="selectMessage" style="height: 350px" class="claro">
            <thead>
                <tr>
                    <th field="compoundLine" width="320px" name="Email messages" formatter="emailFormat">Messages</th>
                </tr>
            </thead>
        </table>
        <script>
            function selectMessage( clickEvent ) {
                var selectedVendor = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"supplierNo");
                var selectedFile   = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"contents");
                var selectedMsg    = eGrid.store.getValue(eGrid.getItem( clickEvent.rowIndex ),"messageId");
                exp2.href= "../../test_email.php?SupplierNo=" + selectedVendor + "&FileName=" + selectedFile + "&messageId=" + selectedMsg +'&dojoSource=1';
                exp2.refresh();
                exp2.style="height: 90px; background-color: #DDDDDD; ";
            }
            function emailFormat( theItem, rowIndex ) {
                var rowData = this.grid.getItem(rowIndex);
                        return ( '<div id="gfx_' + rowData.id + '"><B>' + rowData.from + '</B><BR>' + rowData.contents + '</div>' );
            }
        </script>
         <ul dojoType="dojox.mobile.EdgeToEdgeList">
            <li dojoType="dojox.mobile.ListItem" icon="/icons/oxygen/32x32/actions/go-home.png"  transition="flip" moveTo="main">home </li>
        </ul>
    </div>
    
    <div id="stodo" dojoType="dojox.mobile.View">
        <h1 id="stdoHeading" dojoType="dojox.mobile.Heading" label="Review"></h1>
	    <div dojoType="dijit.layout.TabContainer" jsId="newPane">
	        <div dojoType="dijit.layout.ContentPane" jsId="needleLabel11" style="{ background-color: #DDDDDD }" >
                <ul dojoType="dojox.mobile.EdgeToEdgeList">
                    <li id="exp3" dojoType="dojox.mobile.ListItem" style="background-color: #DDDDDD; " icon="/icons/oxygen/32x32/actions/go-home.png"  transition="slide" moveTo="main" label="Return Home"></li>
               </ul>
            </div>
        </div>
        <div dojoType="dijit.layout.ContentPane" jsId="needleLabel12" style="{ background-color: #DDDDDD }" >
            <ul dojoType="dojox.mobile.EdgeToEdgeList">
                <li id="exp4" dojoType="dojox.mobile.ListItem" style="background-color: #DDDDDD; " icon="/icons/oxygen/32x32/actions/go-home.png"  transition="slide" moveTo="main" label="Return Home"></li>
           </ul>
        </div>

    </div>

</body>
</html>
