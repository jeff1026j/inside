<?php
    require_once 'header.php';
?>
<h2>購買名單</h2>
<br/>
<h4>購買次數：<h4>
<!--<form class="form-inline">-->
    <div class="form-group">
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="1" checked="checked"> 1
        </label>
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio2" value="2"> 2
        </label>
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="3"> 3
        </label>
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="4"> 4
        </label>
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="5"> 5
        </label>
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="9999"> 以上
        </label>
        <label class="radio-inline">
        <input type="radio" name="inlineRadioOptions" id="inlineRadio4" value="0"> 全部
        </label>
        <button class="btn btn-info" id="export-button">Export</button>
   </div>
<!--</form>-->

<br/><br/>

<table data-toggle="table" data-url="/api/getuserList.php" id="productTable" data-sort-name="name" data-sort-order="desc">
    <thead>
        <tr>
            <th data-field="email">Email</th>
            <th data-field="user_name">姓名</th>
            <th data-field="phone">Phone</th>
            <th data-field="max_order_time" data-sortable="true">最新訂購時間</th>
            <th data-field="numberProducts">最新訂購品數</th>
        </tr>
    </thead>
</table>


<script>
    $( document ).ready(function() {
        var defaultUrl="/api/getuserList.php";
        $table = $('#productTable');
        $("input:radio[name=inlineRadioOptions]").click(function() {
            var value = $(this).val();
            $table.bootstrapTable('refresh', {
               url: defaultUrl+'?numberOfReturn='+value
            }) ;
        });
        // This must be a hyperlink
        $("#export-button").on('click', function (event) {
        // CSV
//          exportTableToCSV.apply(this, [$('#productTable'), 'export.csv']);
            var $table = $('#productTable');
            var csv = $table.table2CSV({delivery:'value'});
            window.location.href = 'data:text/csv;charset=UTF-8,'
                                 + encodeURIComponent(csv);
                                        
        // IF CSV, don't do event.preventDefault() or return false
        // We actually need this to be a typical hyperlink
        });
    });     
</script>

<?php require_once 'footer.php';?>
