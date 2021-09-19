<?php echo $this->doctype(); ?>

<html lang="en">
<head>

<?php echo $this->dojo(); ?>

<script type="text/javascript" src="/Test/erp/includes/js/jsonRest.js"></script>

<style type="text/css">
</style>

</head>

<body class="<?php echo $this->dijitTheme; ?>">
    <header>
    <h1>This is the header</h1>
    </header>

    <input id="searchTxt" />
    <button id="searchBtn">Search</button>

    <div id="grid">
        loading...
    </div>

</body>
</html>