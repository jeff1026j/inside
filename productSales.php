<?php
    require_once 'header.php';
?>
<div role="tabpanel"> 

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">熱門銷售</a></li>
    <!-- <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">久久未買</a></li> -->
    <!-- <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Cohort名單</a></li> -->
    <!--<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li> -->
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="home">
        <!--<form class="form-inline">-->
        <div class="top-buffer"></div>
        <div class="form-group">
            <label class="radio-inline">
            <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="3" checked="checked"> 3天
            </label>
            <label class="radio-inline">
            <input type="radio" name="inlineRadioOptions" id="inlineRadio2" value="14"> 2 週
            </label>
            <label class="radio-inline">
            <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="30"> 1 個月
            </label>
            <!-- <button class="btn btn-warning Return export-button">Export</button> -->
       </div>
        <!--</form>-->

        <table data-toggle="table" data-url="/api/getProductSales.php" data-search="true" id="productTable" data-sort-name="name" data-sort-order="desc" data-show-export="true" data-query-params="queryParams" data-pagination="true">
            <thead>
                <tr>
                    <th data-field="product_name">產品</th>
                    <th data-field="amount" data-sortable="true">銷售量</th>
                    <th data-field="revenue" data-sortable="true">銷售金額</th>
                    <th data-field="quantity" data-sortable="true">庫存</th>
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
    function queryParams() {
        return {
            per_page: 100,
            page: 1,
            exportDataType: "all"
        }
    }
    $( document ).ready(function() {
        var defaultUrl="/api/getProductSales.php";
        $("input:radio[name=inlineRadioOptions]").click(function() {
            var value = $(this).val();
            console.log(defaultUrl+'?day='+value);
            $('#productTable').bootstrapTable('refresh', {
               url: defaultUrl+'?day='+value
            });
        });
        
        $('#noReturnform').validator().on('submit', function (e) {
            // console.log('xxxxxx');
            var getUsernoReturnURL="/api/getUsernoReturn.php";
            if (e.isDefaultPrevented()) {
            // handle the invalid form...
                e.preventDefault();
            } else {
            // everything looks good!
                e.preventDefault();
                var currentDate = $('#currentDate').val();
                var maxInterval = $('#maxKey').val();
                var minInterval = $('#minKey').val(); 
                $('#userTable').bootstrapTable('refresh', {
                url: getUsernoReturnURL+'?currentDate='+currentDate+'&maxInterval='+maxInterval+'&minInterval='+minInterval
                });

            }
        });

    });     
</script>

<?php require_once 'footer.php';?>
