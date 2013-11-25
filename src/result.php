<!DOCTYPE html>
<html>
<head>
    <link href="src/style.css"  media="all" rel="stylesheet" type="text/css" />
    <script src="src/js/sortable.js"></script>
    <script type="text/javascript">
        window.parent.bindIframe(window);

    </script>
</head>
<body>

<?php
// SQL
if ($db = db($_REQUEST['instance'])) {
    if (!empty($_POST['query'])) {
        if (strpos($_POST['query'], "###") !== false) {
            $queries = explode("###", $_POST['query']);
        }
        else {
            $queries = array(&$_POST['query']);
        }

        foreach ($queries as &$q) {
            $db->query($q)->show(empty($_POST['format']) ? 'html' : 'serialize');
        }
    }
}
else echo("Could not connect");


// EVAL
if (isset($_REQUEST['eval']) && $_REQUEST['eval']) {
    echo "<h4>Eval</h4>";
    eval($_REQUEST['eval']);
}

xlsReport::finalize();
?>

</body>
</html>