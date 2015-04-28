<?php
    require_once 'header.php';

?>
<div role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">購買名單</a></li>
    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">久久未買</a></li>
    <!-- <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Messages</a></li>
    <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li> -->
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="home">
        <h2>購買名單</h2>
        <br/>
        <h4>購買次數：</h4>
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
                <!-- <button class="btn btn-warning Return export-button">Export</button> -->
           </div>
        <!--</form>-->

        <br/><br/>

        <table data-toggle="table" data-url="/api/getuserList.php" data-search="true" id="productTable" data-sort-name="name" data-sort-order="desc" data-show-export="true">
            <thead>
                <tr>
                    <th data-field="email">Email</th>
                    <th data-field="user_name">姓名</th>
                    <th data-field="phone">Phone</th>
                    <th data-field="max_order_time" data-sortable="true">最新訂購時間</th>
                    <!-- <th data-field="numberProducts">最新訂購品數</th> -->
                    <th data-field="product" class="no_export">過往購買商品</th>
                </tr>
            </thead>
        </table>
    </div>
    <div role="tabpanel" class="tab-pane" id="profile">
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
                        </div> -->
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
    <!-- <div role="tabpanel" class="tab-pane" id="messages">...</div>
    <div role="tabpanel" class="tab-pane" id="settings">...</div> -->
  </div>

</div>

<script>
    $( document ).ready(function() {
        var defaultUrl="/api/getuserList.php";
        $("input:radio[name=inlineRadioOptions]").click(function() {
            var value = $(this).val();
            // console.log('yyyydsdsdyyy');
            $('#productTable').bootstrapTable('refresh', {
               url: defaultUrl+'?numberOfReturn='+value
            });
        });
        // This must be a hyperlink
//         $(".export-button").on('click', function (event) {
//         // CSV
//             var csv;
// //          exportTableToCSV.apply(this, [$('#productTable'), 'export.csv']);
//             if ($(this).hasClass('Return')) {
//                 csv = $('#productTable').table2CSV({delivery:'value'});
//             }else{
//                 csv = $('#userTable').table2CSV({delivery:'value'});
//             };
//             window.open('data:text/csv;charset=UTF-8,'+ encodeURIComponent(csv), '_blank');
//         // We actually need this to be a typical hyperlink
//         });
        /*for 未回購專區*/
        // $("input:radio[name=inlineRadioOptions]").click(function() {
        //     var value = $(this).val();
        
        // });
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
                // console.log('currentDate: '+currentDate+' maxInterval: '+maxInterval+' minInterval: '+minInterval);
                // console.log(getUsernoReturnURL+'?currentDate='+currentDate+'&maxInterval='+maxInterval+'&minInterval='+minInterval);
                $('#userTable').bootstrapTable('refresh', {
                url: getUsernoReturnURL+'?currentDate='+currentDate+'&maxInterval='+maxInterval+'&minInterval='+minInterval
                });

            }
        });

        //for date picker
        $('.input-group.date').datepicker( {
            format: "yyyy-mm-dd",
            orientation: "top auto",
            todayHighlight: true,
            autoclose: true
        }).on("changeDate", function(e){
            // console.log(e.date);
            // window.location = "/?endtime="+ e.date.getFullYear() + "-" + (e.date.getMonth() + 1)  + "-" + e.date.getDate();

        });;      
    });     
</script>

<?php require_once 'footer.php';?>
