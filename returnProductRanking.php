<?php
    require_once 'header.php';
?>
<h2>回購強商品</h2>
<h3>為回購會員重複購買之商品排行榜</h3>
<br/><br/>
<h4>回購次數：<h4>
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
<br/><br/>


<table data-toggle="table" data-url="/api/getproductRanking.php" id="productTable">
    <thead>
        <tr>
            <th data-field="product_name">商品名</th>
            <th data-field="finalScore">回購成績</th>
        </tr>
    </thead>
</table>


<script>
    $( document ).ready(function() {
        var defaultUrl="/api/getproductRanking.php";
        $table = $('#productTable');
        $("input:radio[name=inlineRadioOptions]").click(function() {
            var value = $(this).val();
            $table.bootstrapTable('refresh', {
               url: defaultUrl+'?numberOfReturn='+value
            });
        });
    });     
</script>

<?php require_once 'footer.php';?>
