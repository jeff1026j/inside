<?php
    require_once 'header.php';
?>
<h2>MorningShop 無敵叫貨王</h2>

<div class = "col-md-8">
	<form class="form-horizontal" role="form">
		<input type="hidden" name="cash" id="cash" value="270000">
		<input type="hidden" name="adsSpend" id="adsSpend" value="300000">
		<input type="hidden" name="operCost" id="operCost" value="200000">
		<input type="hidden" name="ratioOfBorrowBuy" id="ratioOfBorrowBuy" value="0.7">
		<input type="hidden" name="goodsCostlastMonth" id="goodsCostlastMonth" value="370000">
		<input type="hidden" name="expectCash" id="expectCash" value="150000">
		<input type="hidden" name="updateMonth" id="updateMonth" value="3">
	 	<div class="form-group">
			<div class="checkbox" >
		      <label>
		        <input type="checkbox" id="ranking20"> 是否為前 20% 商品或有潛力？
		      </label>
		    </div>
		</div>
		<div class="form-group">
		    	<label for="income">輸入 <?=date('m')-1?>/12~NOW 的營業額：</label>
		    	<input type="text" class="form-control" id="income">
		</div>
		<div class="form-group">
		    	<label for="currentGoods">本月叫貨金額：</label>
		    	<input type="text" class="form-control" id="currentGoods">
		</div>
		<br/>
		
		<div class="form-group">
			<button type="submit" class="btn btn-default">試算</button>
		</div>
	</form>

    <div class="alert alert-success" role="alert" id="result"></div>
</div>
<div class = "col-md-4">
	<div class="alert alert-danger" role="alert">寄倉就直接叫 1.3x 月銷量的貨</div>
</div>
<script type="text/javascript">

function dataNeedsUpdate(){
	var resultString = '';
	var updateMonth = parseInt($('#updateMonth').val());
	if (updateMonth != <?=date('m')?>) {
		resultString = '請先完成月初結帳再使用';
	};
	return resultString;
}



$( document ).ready(function() {
	var result = '' ;
	$( "form" ).submit(function( event ) {
		var cash = parseInt($('#cash').val());
		var adsSpend = parseInt($('#adsSpend').val());
		var operCost = parseInt($('#operCost').val());
		var goodsCostlastMonth = parseInt($('#goodsCostlastMonth').val());
		var ranking20 = $("#ranking20").prop('checked');
		var income = parseFloat($('#income').val());
		var currentGoods = parseInt($('#currentGoods').val());
		var ratioOfBorrowBuy = parseFloat($('#ratioOfBorrowBuy').val());
		var expectCash = parseFloat($('#expectCash').val());
		
		//compute the stock value we can buy
		var safeStockValue = cash -adsSpend*2 - operCost*2 - goodsCostlastMonth + income;
		var remainQuta = safeStockValue - currentGoods-expectCash;
		
		//console.log('cash: '+cash);console.log('adsSpend: '+adsSpend);console.log('operCost: '+operCost);console.log('goodsCostlastMonth: '+goodsCostlastMonth);console.log('ranking20: '+ranking20);console.log('income: '+income);console.log('currentGoods: '+currentGoods);console.log('ratioOfBorrowBuy: '+ratioOfBorrowBuy);console.log('remainQuta: '+remainQuta);console.log('safeStockValue: '+safeStockValue );
		
		if (remainQuta < 0) { //we don't have any money
			result = ranking20?'<a href="https://www.facebook.com/messages/wu.tsungjung" target="blank">點擊和 Jeffrey 討論</a>':'先不要叫貨';
		}else{
			result = ranking20?'以 1.3 倍月銷量叫（扣掉爆量的幾天）， 最多叫'+remainQuta:'以兩個禮拜的量來叫，最多叫 '+remainQuta;
		}

		var updateNeeded = dataNeedsUpdate();
		result = updateNeeded !='' ? updateNeeded : result;

		

	  event.preventDefault();
	  $('#result').html(result);
	});

	
});


</script>


<?php require_once 'footer.php';?>