<form action="" method="post">
    <input name="title" style="display:block;width:99%;margin:0;padding:0;font-size:10px;"
           value="<?=$_POST['title']?>"/>
    <select title="instance" name="instance" style="width:49%">
        <?php foreach ($instances as $instance => $tmp) {
            $tmp = parse_url($tmp);
            ?>
            <option value="<?=$instance?>"<?=$instance == $_REQUEST['instance'] ? 'selected="selected"' : '' ?>>
                <?=$instance?> (<?=$tmp['user'].'@'.$tmp['host'].$tmp['path']?>)
            </option>
        <?php } ?>
    </select>
    <input name="mq_query" style="width:39%" value="<?=isset($_POST['mq_query']) ? $_POST['mq_query'] : ''?>"/>
    <select name="mq_server"
            style="width:10%"><?=empty($_POST['mq_server']) ? $mt4_options : str_replace('value="' . $_POST['mq_server'] . '"', 'value="' . $_POST['mq_server'] . '" selected="selected"', $mt4_options)?></select>
    <br/>
    <textarea name="query" class="multiline query"><?=isset($_POST['query']) ? $_POST['query'] : ''?></textarea>
    <textarea name="eval" class="multiline eval"><?=isset($_POST['eval']) ? $_POST['eval'] : ''?></textarea><br/>
    <input type="submit"/>

</form>