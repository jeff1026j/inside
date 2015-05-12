<?php
    require_once 'header.php';
?>
<script type="text/javascript" src="js/jquery.zclip.min.js"></script>
<h3>網站使用問題</h3>
- 購物金怎麼使用？：
- 加入購物車後沒東西？：

<div class="answer"><span style="color: #ff0000;">您好，不好意思，可以請您幫我們換個瀏覽器，或是先登入再把商品加到購物車嗎？如果還是有任何問題都在請您和我說。</span></div>

<h3>缺貨問題</h3>
- 到了可以通知嗎：

<div class="answer"><span style="line-height: 1.5; color: #ff0000;">你好，可以加 Line 發問（http://bit.ly/morning-line ）選擇「缺貨到貨通知」，或是加入這個臉書活動 （http://bit.ly/mo-event ）都可以即時得到通知呦 ～：）</span></div>

&nbsp;
<h3>挑選問題</h3>
- 孕婦（母乳媽媽）適合吃麥片嗎？
- 減肥適合吃的麥片
- 小孩適合吃的麥片
&nbsp;
<h3>貨到時間問題</h3>
- 司機到了不在家：

<div class="answer"><span style="color: #ff0000;">您好，如果司機聯絡您時不在家，可以跟他約定下次收貨時間即可喔 :)</span></div>

&nbsp;&nbsp;<br/>

- 剛剛訂什麼時候會到貨：

<div class="answer"><span style="color: #ff0000;">我們都是宅配 24 小時到貨，48 小時到超商，在請您幫我們多注意一下喔 :)</span></div>

&nbsp;&nbsp;<br/>

- 收貨時間可以延後到 x/x？：

<div class="answer"><span style="color: #ff0000;">已經幫您備註，或是司機聯絡您時，可以跟她約定下次方便收貨的時間呦～ 因為有時司機已經出門會看不到我們的備註，在請您幫忙了 :)</span></div>
&nbsp;&nbsp;<br/>
<h3>金流問題</h3>
- 信用卡刷退什麼時候會回到帳戶：

<div class="answer"><span style="color: #ff0000;">您好，收到退貨後（如果有退貨）的 2~3 天系統就會自動幫您刷摟</span></div>
&nbsp;<br/>
<h3>訂單問題</h3>
- 下訂後想要加訂商品，可以嗎？：

<div class="answer"><span style="color: #ff0000;">您好，不好意思我們現在還沒有換貨的功能，可以請您整筆退訂在重新訂購嗎？在麻煩您了，不好意思喔</span></div>

&nbsp;<br/>

- 為什麼不能超商取貨？：

<div class="answer"><span style="color: #ff0000;">您好，不好意思現在一般超取是有體積限制的，所以可能是選購的品項太多，如果真的要超取的話可能要請您幫我們調整一下，不好意思喔</span></div>

&nbsp; <br/>

- 想要退貨

<div class="answer"><span style="color: #ff0000;">您好，可以幫我們至會員專區中的訂單查詢退貨嗎？ 另外可以詢問一下您退訂的原因嗎？  希望我們的服務可以更好！！</span></div>

&nbsp; <br/>

- 麥片要冰嗎？ / 要怎麼保存

<div class="answer"><span style="color: #ff0000;">您好，不一定要冰喔，不要要記得密封好，因為怕空氣跑進去他會受潮軟掉</span></div>

- 收到感謝時
<div class="answer"><span style="color: #ff0000;">有問題再問小編喲～ :)</span></div>

<script type="text/javascript">
$( document ).ready(function() {
	$( ".answer" ).each(function( index ) {
	  $(this).append('<a href="#" class="copy-button">Copy</a>');
	});

	$(".copy-button").zclip({
	    path: "js/ZeroClipboard.swf",
	    copy: function(){
			return $(this).prev().html();
		}
	});

}); 
</script>
<?php require_once 'footer.php';?>