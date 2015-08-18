<?php
    require_once 'header.php';
    // $json = file_get_contents('http://inside.morningshop.tw/api/getcohort.php'); // this WILL do an http request for you
    // $data = json_decode($json,true);
    //clear the cache first
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/api/getcohort.php');
    //curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, 'morningshop' . ":" . 'goodmorning');
    // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array( "url"=>'http://'.$_SERVER['HTTP_HOST'].'/')));
    $json = curl_exec($ch);
    curl_close($ch);
   
    $data = json_decode($json, true);
   
?>
<h2>MorningShop Cohort</h2>
<h3>業界標準：12%(一年長期)</h3>
<h3>業界標準：30%(第 2 個月)</h3>
<br/>
<h4>Chris:Hook老顧客，Jake：招回購新會員，Jeff：調產品結構，JPG：商品力、不缺貨，Chi：文案風格形象</h4>
<!--<form class="form-inline">-->
<br/>
<button class="btn btn-info" id="export-button">Export</button>

<!--</form>-->
<br/>
<table id="cohortTable" >
    <thead>
        <tr>
            <?php   
                foreach ($data[0] as $key => $value) {
                    // if ($value == "201412" || $value == "201501" || $value == "201502") {
                        
                    // }else{
                        echo '<th data-field="'.$key.'">'.$value.'</th>';
                    // }
                    
            
                }
                //remove the first array to present data
                unset($data[0]); // remove item at index 0
                $json = json_encode(array_values($data)); // 'reindex' array
            ?>
        </tr>
    </thead>
</table>


<script>
    $( document ).ready(function() {
        var data = <?=$json?>;

        $table = $('#cohortTable');
        
        $table.bootstrapTable({
            data: data
        });
        
        

        $("#export-button").on('click', function (event) {
        // CSV
//          exportTableToCSV.apply(this, [$('#productTable'), 'export.csv']);
            var $table = $('#cohortTable');
            var csv = $table.table2CSV({delivery:'value'});
            window.location.href = 'data:text/csv;charset=UTF-8,'
                                 + encodeURIComponent(csv);
        });
        // $table.bootstrapTable('remove', {field: 'Month', values: ["201412", "201501"]})
        // $table.bootstrapTable('hideRow', {index:0});
        // $table.bootstrapTable('hideRow', {index:1});
        // $table.bootstrapTable('hideRow', {index:2});

        // function avgFormatter(data) {
        //     var total = 0;
        //     // $.each(data, function (i, row) {
        //     //     // total += +(row.price.substring(1));

        //     // });
        //     return '$' + total;
        // }


    });     
    
</script>

<?php require_once 'footer.php';?>
