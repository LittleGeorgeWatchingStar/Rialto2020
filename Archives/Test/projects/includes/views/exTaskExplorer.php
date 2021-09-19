<?php
echo $this->doctype();
?>

<HTML>
<HEAD>
<style type="text/css">
    html, body { width: 100%; height: 100%; margin: 0; overflow:hidden; }
.claro .dojoxGridExpando {
	float: left;
	height: 16px;
	width: 16px;
	text-align: center;
	margin-top: -3px;
}
.claro .dojoxGridCell {
	border-style: dotted
}
.claro .dojoxGridExpandoCell {
	padding-top: 5px;
}
.claro .dojoxGridExpandoNode {
	background-image:
url('images/treeExpandImages.png');
	width: 16px;
	height: 16px;
	background-position: 1px 0px;
}
.claro .dojoxGridExpandoOpened .dojoxGridExpandoNode {
	background-position: -35px 0px
}
.claro .dojoxGridExpandoLoading .dojoxGridExpandoNode {
	background-image:
url('images/treeExpand_loading.gif');		
}
.claro .dojoxGridTreeModel .dojoxGridNoChildren .dojoxGridExpando {
	visibility: visible !important;
}
.claro .dojoxGridTreeModel .dojoxGridNoChildren .dojoxGridExpandoNode {
	background-image: none;
}
.claro .dojoxGridExpandoNodeInner {
	visibility: hidden;
}
.dijit_a11y .dojoxGridExpandoNodeInner {
	visibility: visible;
}
		
.claro .dojoxGridSummaryRow .dojoxGridCell {
	border-top-color: #999;
	border-top-style: solid;
}
.claro .dojoxGridSubRowAlt {
	background-color: #F8F8F8;
}
.claro .dojoxGridRowOdd .dojoxGridSubRowAlt {
	background-color: #EDEFF3;
}



</style>
<?php  echo $this->headLink();	?>
<?php  echo $this->dojo();		?>

<!--
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojo/resources/dojo.css" >
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dijit/themes/claro/claro.css" >
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojox/grid/resources/Grid.css" >
<LINK rel="stylesheet" type="text/css"  href="http://ajax.googleapis.com/ajax/libs/dojo/1.5/dojox/grid/resources/claroGrid.css" >
--!>

<LINK rel="stylesheet" type="text/css"  href="/dojo/dojo/resources/dojo.css" >
<LINK rel="stylesheet" type="text/css"  href="/dojo/dijit/themes/claro/claro.css" >
<LINK rel="stylesheet" type="text/css"  href="/dojo/dojox/grid/resources/Grid.css" >
<LINK rel="stylesheet" type="text/css"  href="/dojo/dojox/grid/resources/claroGrid.css" >

</HEAD>
<BODY CLASS=<?php echo STYLE . '/' . STYLE;?>>

<div dojoType="dojo.data.ItemFileWriteStore"  url="gan2json.php?menu_name=Gumstix.gan" jsID="tree_1">
</div>

<div dojoType="dijit.tree.ForestStoreModel" store="tree_1" childrenAttrs="children" openOnClick="true" showRoot="false"  defaultOpen="true"
     query= "{type: '*'}" rootId="id" rootLabel="id" jsID="ForestModel1">
</div>

<table dojoType="dojox.grid.TreeGrid" treeModel="ForestModel1" class="claro" id="grid">
  <thead>
    <tr>
      <th name="name" field="name" width="auto"></th>
      <th name="duration" field="duration" width="auto"></th>
      <th name="type" field="type" width="auto"></th>
    </tr>
  </thead>
</table>

</BODY>
</HTML>
