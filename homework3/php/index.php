<?php
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query) {
    require_once('phpSolrClient/Service.php');
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572/');
    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }
    $param = [];
    if (array_key_exists("pagerank", $_REQUEST)) {
        $param['sort'] ="pagerank.txt desc";
    }
    $results = $solr->search($query, 0, $limit, $param);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Solr Client</title>
</head>
<body>
    <form accept-charset="utf-8" method="get">
        <label for="q">Search:</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
        <input type="submit"/>
        <br>
        <input type="checkbox" name="pagerank">Use Page Rank Algorhthm<br>
        <br>
    </form>
    <?php if ($results): ?>
        <?php 
            $total = (int)$results->response->numFound;
            $start = min(1, $total);
            $end = min($limit, $total);
        ?>
        <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
        <hr>
        <?php foreach ($results->response->docs as $doc): ?>
            <?php 
                $id = $doc->id;
                $url = substr($id, 21);
                $url = urldecode($url);
            ?>
            <a href="<?php echo $url; ?>">Document</a>
            <?php echo $doc->title ? $doc->title : "None"; ?>
            <p>
                Author: <?php echo $doc->author ? $doc->author : "None"; ?> | Size: <?php echo $doc->stream_size ? $doc->stream_size : "None"; ?>
            </p>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
