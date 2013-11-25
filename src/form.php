<!DOCTYPE html>
<html>
<head>
    <title><?php echo$_POST['title']?></title>

    <link href="src/style.css"  media="all" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        (function(){
            var iframe, timer;

            function updateHeight() {
                //console.log(iframe);
                var r = document.getElementById('result');
                r.style.height = iframe.document.documentElement.scrollHeight + 'px';
                r.style.width = iframe.document.documentElement.scrollWidth + 'px';
            }

            window.bindIframe = function(element) {
                if (timer) {
                    clearTimeout(timer);
                }
                iframe = element;
                setInterval(updateHeight, 500);
                updateHeight();
            };

        })();


    </script>
</head>
<body>

<form action="" method="post" target="result">
    <input type="hidden" value="1" name="run" />
    <table class="form">
        <tr>
            <td style="width: 50%">
                <select title="instance" name="instance" style="width:100%">
                    <?php foreach ($instances as $instance => $tmp) {
                        $tmp = parse_url($tmp);
                        ?>
                        <option value="<?php echo $instance?>"<?php echo $instance == $_REQUEST['instance'] ? 'selected="selected"' : '' ?>>
                            <?php echo $instance?> (<?php echo $tmp['user'].'@'.$tmp['host'].$tmp['path']?>)
                        </option>
                    <?php } ?>
                </select>
            </td>

            <td style="width: 50%">
                <input name="title" style="width:100%;" value="con" />
            </td>
        </tr>

        <tr>
            <td>
                <textarea name="query" class="multiline query"><?php echo isset($_POST['query']) ? $_POST['query'] : ''?></textarea>
            </td>

            <td>
                <textarea name="eval" class="multiline eval"><?php echo isset($_POST['eval']) ? $_POST['eval'] : ''?></textarea>
            </td>
        </tr>

        <tr>
            <td style="border: none">
                <button type="submit">run</button>
            </td>
        </tr>
    </table>
</form>

<iframe name="result" id="result"></iframe>

<a href="#" onclick="this.nextSibling.style.display='';this.style.display='none'">$_SERVER</a><pre style="display:none"><?php print_r($_SERVER);?></pre>



</body>
</html>
