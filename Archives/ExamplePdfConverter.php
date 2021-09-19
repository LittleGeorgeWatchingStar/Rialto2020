<?php

/* "use" lines like this one must be the first thing in your script. */
use Rialto\UtilBundle\Formatter\PdfConverter;

require_once 'config.php';

ob_start();
?>
<style type="text/css">
    table {
        border: 1px solid black;
    }
    table td {
        border: 1px dotted black;
    }
    div.exclaim {
        font-size: larger;
        font-style: italic;
        background-color: red;
        margin: 10px;
    }
</style>

<h1>Here is some html</h1>

<table>
    <tr>
        <th>Col 1</th>
        <th>Col 2</th>
    </tr>
    <tr>
        <td>Value 1</td>
        <td>Value 2</td>
    </tr>
</table>

<div class="exclaim">
Whatddya know?  This PDF converter supports CSS!
<div>
<?php

$html = ob_get_clean();

$converter = new PdfConverter();

header('Content-type: application/pdf');
echo $converter->convertHtml($html);