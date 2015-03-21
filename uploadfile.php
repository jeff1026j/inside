<?php
    require_once 'header.php';
?>
            			<h2>Upload csv file - UTF8 only</h2>
			            <form  action="uploadprocess.php" method="post" enctype="multipart/form-data" onsubmit="return Validate(this);">
            			    <div class="form-group">
			            	<input type="file" id="csvfile" name="csvfile">
				
            			    </div>
			                <button type="submit" class="btn btn-default">Submit</button>
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
</script>
<?php require_once 'footer.php';?>
