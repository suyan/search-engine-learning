<?php
header('Content-­‐Type: text/html; charset=utf-­‐8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query) {
    require_once('phpSolrClient/Service.php');
    $solr = new Apache_Solr_Service('localhost', 8984, '/solr/csci572/');
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }
    try {
        $results = $solr->search($query, 0, $limit);
    } catch (Exception $e) {
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>" . $e->__toString() . "</pre></body></html>");
    }
}
?>

<html>
<head>
<title>PHP Solr Client Example</title>
</head> <body>
<form accept-­‐charset="utf-8" method="get">
<label for="q">Search:</label>
<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
<input type="submit"/>
</form>

<?php
if ($results) {
    $total = (int)$results->response->numFound;
    $start = min(1, $total);
    $end = min($limit, $total);
?>
<div>Results <?php echo $start; ?> -­‐ <?php echo $end;?> of <?php echo $total; ?>:</div> <ol>
<?php
    foreach ($results->response->docs as $doc) {
?>
<li> <table style="border: 1px solid black; text-­‐align: left">
<?php
    foreach ($doc as $field => $value) {
?>
<tr>
    <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
    <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
</tr>

<?php
}

?>

</table>
</li>
<?php
}
?>
</ol>
<?php
}
?> </body>
</html>
