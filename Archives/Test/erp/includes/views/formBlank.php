<?php
if ( !isset( $_GET['dojoSource'])) {
    echo $this->doctype();
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css"  href="/js/dojo/dijit/themes/claro/claro.css" >
<LINK rel="stylesheet" type="text/css"  href="/js/dojo/dojox/grid/resources/Grid.css" >
        <style type="text/css">
            body, html { font-family:helvetica,arial,sans-serif; font-size:90%; }
        </style>

<?php
    echo $this->headTitle();
    echo $this->headMeta();
    echo $this->headLink();
    echo $this->headStyle();
    echo $this->dojo();
}
?>
</HEAD>

<BODY CLASS="claro">
<H1>
<BR>
<INPUT id="Headline" dojoType="dijit.form.TextBox" placeHolder="Headline for the press release." style="width:700px;" />
</H1>

<HR>
<FORM id='masterForm_ID' action="../../formSample.php">
<CENTER>
Launch date:<INPUT type="text" name="date2" id="date2" value="2011-12-30" dojoType="dijit.form.DateTextBox" required="true"/>
<BUTTON id="InsertButton" type="button" onClick='dojo.xhrPost( { form: masterForm_ID, load: function(data) { alert(data);} , handleAs: "text" } )'>Approved</button>
</CENTER>
<HR>

<TABLE BORDER=2>
<TR><TD>

<TABLE><TR>
<TD>
Simple description for webERP
<BR>
<textarea id="Description" dojoType="dijit.form.Textarea" style="width:400px;height:30px;"></textarea>
</TD>
</TR>

<TR>
<TD colspan=3>
Quotation for the press release
<BR>
<textarea id="Quotation" dojoType="dijit.form.Textarea" style="width:400px; height:30px"></textarea>
<BR>
Source's name for the press release
<BR>
<textarea id="QuoteSource" dojoType="dijit.form.Textarea" placeHolder="Source's name for the press release"></textarea>
<BR>
Gumstix quotation for the press release
<BR>
<textarea id="GumQuote"   dojoType="dijit.form.Textarea" placeHolder="Source's name for the press release"></textarea>
</TD>
</TR>

<TR>
<TD align=right colspan=1 width="200px">
Price<INPUT id="Price" dojoType="dijit.form.CurrencyTextBox" placeHolder="Retail price"/>
<BR>
Label<INPUT id="Label" dojoType="dijit.form.TextBox" placeHolder="Label"/>
<BR>
Harmonization<INPUT id="Harmonization" dojoType="dijit.form.TextBox" placeHolder="0000 0000 00"/>
</TD>
</TR></TABLE>

</TD>

<TD BGCOLOR="0xAAFFFF" width="300px">
</TD>

</TR>
</TABLE>
</FORM>

<H2>
Outstanding issues
</H2>
<table id="issueGrid" jsid="bGrid" dojoType="dojox.grid.DataGrid" store="bugStore" clientSort=true query="{ issueID: '*' }"  rowsPerPage="10" height="200px">
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedProject= bGrid.store.getValue(bGrid.getItem( clickEvent.rowIndex ),"name");
        var selectedFile   = bGrid.store.getValue(bGrid.getItem( clickEvent.rowIndex ),"issueID");
        var selectedMsg    = bGrid.store.getValue(bGrid.getItem( clickEvent.rowIndex ),"description");
        alert( "This will link to the mantis DB");
    </script>
    <thead>
        <tr>
            <th field="id"            width="50px" name="id"></th>
            <th field="description"   width="700px" name="description"></th>
        </tr>
    </thead>
</table>
<HR>

<H2>
Identified features and components
</H2>
<table id="componentGrid" jsid="cGrid" dojoType="dojox.grid.DataGrid" store="componentStore" clientSort=true query="{ id: '14' }"  rowsPerPage="10" >
    <script type="dojo/method" event="onRowClick" args="clickEvent">
        var selectedProject= cGrid.store.getValue(cGrid.getItem( clickEvent.rowIndex ),"name");
        var selectedFile   = cGrid.store.getValue(cGrid.getItem( clickEvent.rowIndex ),"id");
        var selectedMsg    = cGrid.store.getValue(cGrid.getItem( clickEvent.rowIndex ),"description");
          alert( "This will link to the stockID page of the component");
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
<HR>


</BODY>
