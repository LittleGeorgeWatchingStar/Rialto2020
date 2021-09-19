
<?php if ( empty($_GET['dojoSource']) ): ?>
    <?php echo $this->doctype(); ?>
    <html>
    <head>
    <?php
        echo $this->headTitle();
        echo $this->headMeta();
        echo $this->headLink();
        echo $this->headStyle();
        $this->dojo()->addStyleSheetModule('dijit.themes.claro');
        echo $this->dojo();
    ?>
    </head>
    <body class="claro">
<?php endif; ?>

<div class="test_email_main">
Found <?php echo count($this->orders); ?> order(s) &bull;
<a href="<?php echo $this->getUri($this->pdfUri); ?>">PDF</a> &bull;
<a href="<?php echo $this->getUri($this->csvUri); ?>">CSV</a>


<?php foreach ( $this->orders as $orderView ): ?>
    <table id="myData<?php echo $orderView->oid; ?>" style="display: none">
    <thead>
    <tr>
    <?php foreach ( $orderView->columns as $column ): ?>
        <th><?php echo $column; ?></th>
    <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php if ( count($orderView->lineItems) > 0 ): ?>

        <?php foreach ( $orderView->lineItems as $item ): ?>
            <tr>
            <?php foreach ( $orderView->columns as $column ): ?>
                <td><?php if (isset($item[$column]) ) echo $item[$column]; ?></td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; /* lineItems */ ?>

        <tr>
        <?php foreach ( $orderView->columns as $column ): ?>
            <td>
            <?php if ( 'ExtendedCost' == $column ): ?>
                <?php echo $orderView->grandTotal; ?>
            <?php elseif ( 'Description' == $column ): ?>
                GRAND TOTAL
            <?php endif; ?>
            </td>
        <?php endforeach; ?>
        </tr>
        
    <?php endif; ?>
    </tbody>
    </table>

    <div dojoType="dojox.data.HtmlStore"
         dataId="myData<?php echo $orderView->oid; ?>"
         jsId="store_<?php echo $orderView->oid; ?>">
    </div>

    <div style="width: 100%; height: <?php echo 25 * (2 + count($orderView->lineItems)); ?>px;">
         <table id="invTable<?php echo $orderView->oid; ?>"
                class="invTable"
                dojoType="dojox.grid.DataGrid"
                store="store_<?php echo $orderView->oid; ?>"
                query="{ json_key: '*' }"
                rowsPerPage="40">
         <thead>
            <tr>
                <th field="json_key" name="json_key"></th>
                <th field="QtyOrdered" name="QtyOrdered"></th>
                <th field="cancelled" name="cancelled"></th>
                <th field="QtyInvoiced" name="QtyInvoiced"></th>
                <th field="Description" width="300px" name="Description"></th>
                <th field="UnitCost" name="UnitCost"></th>
                <th field="ExtendedCost" name="ExtendedCost"></th>
                <th field="StockItem" width="120px" name="StockItem"></th>
            </tr>
        </thead>
    </table>
    </div>

    <?php echo $orderView->form; ?>
<?php endforeach; /* orderView */ ?>
</div><!-- test_email_main -->

<?php if ( empty($_GET['dojoSource']) ): ?>
    <?php echo $this->layout()->content; ?>
    <?php echo $this->inlineScript(); ?>
    </body>
    </html>
<?php endif; ?>
