<?php
    require_once 'header.php';
?>
<div role="tabpanel"> 

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">貨品狀態</a></li>
    <!-- <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">久久未買</a></li> -->
    <!-- <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Cohort名單</a></li> -->
    <!--<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li> -->
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="home">
        <!--<form class="form-inline">-->
        <div class="top-buffer"></div>
        <!-- <div class="form-group"> 
            <label class="radio-inline">
            <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="3" checked="checked"> 3天
            </label>
            <label class="radio-inline">
            <input type="radio" name="inlineRadioOptions" id="inlineRadio2" value="14"> 2 週
            </label>
            <label class="radio-inline">
            <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="30"> 1 個月
            </label>
            <!-- <button class="btn btn-warning Return export-button">Export</button>
       </div> -->
        <!--</form>-->

        <table data-toggle="table" data-url="/api/getProductsNew.php" data-search="true" id="productTable" data-sort-name="name" data-sort-order="desc" data-show-export="true">
            <thead>
                <tr>
                    <th data-field="product_name">產品</th>
                    <th data-field="storage_id">倉庫編碼</th>
                    <th data-field="avg_sale" data-sortable="true">平均月銷量</th>
                    <th data-field="quantity" data-sortable="true">庫存</th>
                    <th data-field="seller" data-sortable="true">負責人員</th>
                </tr>
            </thead>
        </table>
    </div>
    <!-- <div role="tabpanel" class="tab-pane" id="profile">
        <h2>久久未買</h2>
        <br/> 
        <h4>計算日期：</h4>
        <div class="input-group date">
          <input type="text" id="currentDate" class="form-control" value=""><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
        </div>
        <h4>未購買時間</h4>
        <form class="form-horizontal" data-toggle="validator" role="form" id="noReturnform">
            <div class="form-group"> 
                <div class="col-md-6">
                    <div class="form-group row">
                        <label for="inputKey" class="col-md-1 control-label">Min</label>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="minKey" placeholder="0">
                        </div>
                        <label for="inputValue" class="col-md-1 control-label">Max</label>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="maxKey" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-default btn-info">更新</button>    
                        </div>
                        <!-- <div class="col-md-2">
                            <button class="btn btn-warning noReturn export-button">Export</button>
                        </div> 
                    </div>
                </div>
            </div>
        </form>
        

        <table data-toggle="table" data-url="/api/getUsernoReturn.php" data-search="true" id="userTable" data-sort-name="name" data-sort-order="desc" data-show-export="true">
            <thead>
                <tr>
                    <th data-field="email">Email</th>
                    <th data-field="user_name">姓名</th>
                    <th data-field="max_order_time" data-sortable="true">最新訂購時間</th>
                    <th data-field="product" class="no_export">過往購買商品</th>
                </tr>
            </thead>
        </table>

    </div>
    <div role="tabpanel" class="tab-pane" id="messages">
        <h2>Cohort名單</h2>
        <br/> 
        <h4>首購日期：</h4>
        <div class="input-group date">
          <input type="text" id="cohortDate" class="form-control" value=""><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
        </div>
        <table data-toggle="table" data-url="/api/getCohortUserList.php" data-search="true" id="cohortTable" data-sort-name="name" data-sort-order="desc" data-show-export="true">
            <thead>
                <tr>
                    <th data-field="email">Email</th>
                    <th data-field="user_name">姓名</th>
                    <th data-field="max_order_time" data-sortable="true">最新訂購時間</th>
                    <th data-field="product" class="no_export">過往購買商品</th>
                </tr>
            </thead>
        </table>
    </div> -->
    <!--<div role="tabpanel" class="tab-pane" id="settings">...</div> -->
  </div>

</div>

<script>
    $( document ).ready(function() {
        var defaultUrl="/api/getProducts.php";
    });     
</script>

<?php require_once 'footer.php';?>
