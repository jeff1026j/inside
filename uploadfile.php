<?php
    require_once 'header.php';
?>
            			<h3>Upload csv file - UTF8 only</h3>
                        <form id="fileUpload" class="form-horizontal" action="uploadprocess.php" method="post" enctype="multipart/form-data" onsubmit="return Validate(this);">
            			    <div class="top-buffer form-group">
                                <label class="text-left col-sm-2 control-label">檔案資料</label>
                                <div class="col-sm-10">
                                  <select class="form-control">
                                      <option>訂單</option>
                                      <option>商品</option>
                                      <option>賣場商品AM</option>
                                      <option>會員</option>
                                      <option>倉庫商品轉移</option>
                                      <option>叫貨表轉移</option>
                                  </select>
                                </div>
                            </div>
                            <div class="top-buffer form-group">
                                <label class="text-left col-sm-2 control-label ">選擇檔案</label>
                                <div class="col-sm-10">
			                 	   <input type="file" id="csvfile" name="csvfile">
                                </div>       
            			    </div>
                            <div class="top-buffer form-group">
                                <div class="col-sm-10">
			                     <button type="submit" class="btn btn-default">Submit</button>
                                </div>
                            </div>
        			    </form>
 

<script type="text/javascript">
var _validFileExtensions = [".csv"];

function Validate(oForm) {
        var arrInputs = oForm.getElementsByTagName("input");
            for (var i = 0; i < arrInputs.length; i++) {
                var oInput = arrInputs[i];
                    if (oInput.type == "file") {
                        var sFileName = oInput.value;
                        if (sFileName.length > 0) {
                            var blnValid = false;
                            for (var j = 0; j < _validFileExtensions.length; j++) {
                                var sCurExtension = _validFileExtensions[j];
                                if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                                   blnValid = true;
                                   break;
                                }
                            }
                            if (!blnValid) {
                                alert("Sorry, only " + _validFileExtensions.join(", "));
                                return false;
                            }
                        } 
                    }
              }
              return true;
}
$( "select" )
  .change(function () {
    str=""
    $( "select option:selected" ).each(function() {
        var str = $( this ).text();
        var url = "";
        if (str=="訂單") {
            url = "uploadprocess.php";
        }else if(str=="商品"){
            url = "uploadProductProcess.php";
        }else if(str=="賣場商品AM"){
            url = "parseUitoxAMfile.php";
        }else if(str=="會員"){
            url = "parseUserfile.php";
        }else if(str=="倉庫商品轉移"){
            url = "newWareHouseProcess.php";
        }else if(str=="叫貨表轉移"){
            url = "uploadaskProductprocess.php";
        }  
        $("#fileUpload").attr("action", url);
    });
});
</script>
<?php require_once 'footer.php';?>
