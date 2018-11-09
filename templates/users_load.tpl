<div class="users_load">
    <?php if(!isset($csv)) { ?>
    <div>
        Задание 1.
        <form id="load_form" enctype="multipart/form-data" method="post" action="users/load_users">
            <label for="csv1">Load users from csv:</label>
            <p><input id="csv1" type="file" name="csv">
                <input type="submit" value="Загрузить"></p>
        </form>
    </div>
    <?php } ?>
    <div class="users_load_result">
        <?php if(isset($csv)) { ?>
        Load result from <?php echo $csv_name; ?>:
        <p style="color: green;">Count success rows: <?php echo $csv_success_count; ?></p>
        <p style="color: red;">Count fail rows: <?php echo $csv_fail_count; ?></p>
        <ul>
            <?php
        foreach($csv as $key=>$row) {
            if($row['load_error']) { ?>
            <li class="fail">
                <?php echo $key.': '.$row['load_error']; ?>
            </li>
            <?php } ?>
            <?php
        }
        ?></ul>
        <a href="<?php echo $link_url; ?>"><?php echo $link_text; ?></a>
        <?php }
        ?>
    </div>
</div>
