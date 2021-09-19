<?php
if ( !isset( $_GET['dojoSource'])) {
    echo $this->doctype();
?>
<HTML>
<HEAD>
<?php
    echo $this->headTitle();
    echo $this->headMeta();
    echo $this->headLink();
    echo $this->headStyle();
    $this->dojo()->addStyleSheetModule('dijit.themes.claro');
    echo $this->dojo();
}
?>
</HEAD>
<BODY CLASS="claro">
<?php
echo $this->layout()->content;
echo $this->form;
echo $this->inlineScript();
?>
</BODY>
