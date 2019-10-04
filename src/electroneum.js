// JavaScript Document
jQuery(document).ready(function(e) {
       jQuery('#donnation_amt').focus(function(){
		   jQuery(this).data('placeholder',jQuery(this).attr('placeholder'))
				  .attr('placeholder','');
		}).blur(function(){
		   jQuery(this).attr('placeholder',jQuery(this).data('placeholder'));
		});
});

function openpay()
{
	 code = "";
	 paymentid = jQuery("#paymentid").val();
	 outlet = jQuery("#outlet").val();
	 total = jQuery("#etn").val();
	 code = "etn-it-"+outlet+"/"+paymentid+'/'+total;
	 window.open("https://link.electroneum.com/jWEpM5HcxP?vendor=" + code);
}


function donateamount()
{
	sitebaseurl = jQuery("#sitebaseurl").val();
	jQuery("#donnation_amt").removeClass("borderred");
	donnation_amt = parseFloat(jQuery("#donnation_amt").val());
	if(donnation_amt > 0)
	{
		
			 jQuery.ajax({
				type: "POST",
				cache: false,
				dataType: "json",
				url: sitebaseurl,
				data : { "ajaxtask" : 'getqr', "amtval": donnation_amt}
			 }).done(
			 function (data, textStatus){
				 jQuery("#firstdiv_donate").hide();
				 jQuery("#seconddiv").html(data.html);
				 jQuery("#seconddiv").show();
				 
				 
				 jQuery("#qrimage").click(function(){
						openpay();
					});
				 
				 setTimeout(function(){
					 checkelectroneumresponse(1);
				 }, 5000);
				 
			 });
	}
	else
	{
		 jQuery("#donnation_amt").addClass("borderred");
	}
}
function checkelectroneumresponse()
{
	sitebaseurl = jQuery("#sitebaseurl").val();
	jQuery("#donnation_amt").removeClass("borderred");
	donnation_amt = parseFloat(jQuery("#donnation_amt").val());
	
	 jQuery.ajax({
		type: "POST",
		cache: false,
		dataType: "json",
		url: sitebaseurl,
		data : jQuery("#electronium_payform :input").serialize(),
	 }).done(
	 function (data, textStatus){
		 
		 if(data.success == 0)
		 {
			 errorstring = '<div class="uk-alert" uk-alert><a href="" class="uk-alert-close uk-close"></a><p>'+data.message+'</p></div>';
			 //jQuery("#error_div").html(errorstring);
			 
			  setTimeout(function(){
				checkelectroneumresponse(1);
			 }, 5000);
			 
			 //jQuery("#electroniumform").submit();
			 //jQuery("#paymentprogress").hide();
		 }
		 if(data.success == 1)
		 {
			
			 jQuery("#seconddiv").hide();
			 jQuery("#thirddiv").show();
			 jQuery("#checkmark_svg").show();
			 
			 setTimeout(function(){
				 jQuery("#electronium_payform").submit();
			 }, 3000);
		 }
		 
	 });

	
}