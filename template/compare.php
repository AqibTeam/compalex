<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COMPALEX - database schema compare tool</title>
    <script src="public/js/jquery.min.js"></script>
    <script src="public/js/functional.js"></script>
    <style type="text/css" media="all">
        @import url("public/css/style.css");
        .changes-detected{
            background-color: hsl(0deg 100% 50% / 21%) !important;
        }
        .ok{
            background-color: hsl(120deg 100% 25% / 26%);
        }
    </style>
</head>

<body>
<div class="modal-background" onclick="Data.hideTableData(); return false;">
    <div class="modal">
        <iframe src="" frameborder="0"></iframe>
    </div>
</div>

<div class="compare-database-block">
    <h1>Compalex</h1>

    <h3>Database schema compare tool</h3>
    <table class="table">
        <tr class="panel">
            <td>
                <?php
                switch (DRIVER) {
                    case 'mysql':
                        $buttons = array('tables', 'views', 'procedures', 'functions', 'indexes', 'triggers');
                        break;
                    case 'mssql':
                    case 'dblib':
                        $buttons = array('tables', 'views', 'procedures', 'functions', 'indexes');
                        break;
                    case 'pgsql':
                        $buttons = array('tables', 'views', 'functions', 'indexes');
                        break;
                }

                if (!isset($_REQUEST['action'])) $_REQUEST['action'] = 'tables';
                foreach ($buttons as $li) {
                    echo '<a href="index.php?action=' . $li . '"  ' . ($li == $_REQUEST['action'] ? 'class="active"' : '') . '>' . $li . '</a>&nbsp;';
                }
                ?>

            </td>
            <td class="sp">
                <a href="#" onclick="Data.showAll(this); return false;" class="active">all</a>
                <a href="#" onclick="Data.showDiff(this); return false;">changed</a>

            </td>
        </tr>
    </table>
    <table class="table">
        <tr class="header">
            <td width="50%">
                <h2><?php echo DATABASE_NAME ?></h2>
                <span><?php $spath = explode("@", FIRST_DSN);
                    echo end($spath); ?></span>
            </td>
            <td  width="50%">
                <h2><?php echo DATABASE_NAME_SECONDARY ?></h2>
                <span><?php $spath = explode("@", SECOND_DSN);
                    echo end($spath); ?></span>
            </td>
        </tr>
    <?php foreach ($tables as $tableName => $data) { ?>
        <tr class="data data_<?php echo $tableName?> ok">
            <?php foreach (array('fArray', 'sArray') as $blockType) { ?>
            <td class="type-<?php echo $_REQUEST['action']; ?>">
                <h3><?php echo $tableName; ?> <sup style="color: red;"><?php if(is_array($data[$blockType])){ echo count($data[$blockType]); }?></sup></h3>
                <div class="table-additional-info">
                    <?php if(isset($additionalTableInfo[$tableName][$blockType])) {
                            foreach ($additionalTableInfo[$tableName][$blockType] as $paramKey => $paramValue) {
                                if(strpos($paramKey, 'ARRAY_KEY') === false) echo "<b>{$paramKey}</b>: {$paramValue}<br />";
                            }
                        }
                    ?>
                </div>
                <?php if ($data[$blockType]) { ?>
                    <ul>
                        <?php
                        $statements = "";
                        $i = 0;
                        $len = count($data[$blockType]);
                        $complete_statement ="";
                        ?>
                        <?php foreach ($data[$blockType] as $fieldName => $tparam) { ?>
                            <li <?php if (isset($tparam['isNew']) && $tparam['isNew']) {
                                echo 'style="color: red;" class="new" ';
                            } ?>><b><?php echo $fieldName; ?></b>

                                <span <?php if (isset($tparam['changeType']) && $tparam['changeType']): ?>style="color: red;" class="new" <?php endif;?>>
                                    <?php echo $tparam['dtype']; ?>
                                </span>
                                <span><?php if (isset($tparam['isNew']) && $tparam['isNew']) {?>
                                        <?php
                                        if($i == $len - 1){
                                            $statements .='ADD '.$fieldName.' '.$tparam['dtype'].',';
                                        }else{
                                            $statements .='ADD '.$fieldName.' '.$tparam['dtype'].','.PHP_EOL;
                                        }
                                        ?>(Delete Or Replace)<?php }?></span>
                            </li>
                        <?php
                            $i++;
                        } ?>
                        <?php
                        if($statements != ""){
                            $alter = "ALTER TABLE $tableName";
                            $complete_statement =$alter.PHP_EOL.substr_replace($statements, ";", -1);
                            $fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/".$tableName.".sql","wb");
                            fwrite($fp,$complete_statement);
                            fclose($fp);
                            echo "<textarea style='width: 100%; height: 50px'>".$complete_statement."</textarea>";
                            echo "<script>$('.data_$tableName').addClass('changes-detected');</script>";
                        }
                        ?>
                    </ul>
                <?php } ?>
                <?php if (is_array($data[$blockType]) && count($data[$blockType]) && in_array($_REQUEST['action'], array('tables', 'views'))) { ?><a
                    target="_blank"
                    onclick="Data.getTableData('index.php?action=rows&baseName=<?php echo $basesName[$blockType]; ?>&tableName=<?php echo $tableName; ?>'); return false;"
                    href="#" class="sample-data">Sample data (<?php echo SAMPLE_DATA_LENGTH; ?> rows)</a><?php } else{echo "<br><strong>DROP TABLE ".$tableName."; <br><strong>";} ?>
            </td>
            <?php } ?>
        </tr>
    <?php } ?>
    </table>

</div>
</body>
