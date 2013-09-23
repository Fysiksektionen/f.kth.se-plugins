jQuery(document).ready(function() {
	 jQuery(".condTextBox").hide();
	 jQuery(".condTextSwitch").click(function() {
		if(jQuery(this).is(':checked')) jQuery(this).parent("p").siblings(".condTextBox").slideDown();
		else jQuery(this).parent("p").siblings(".condTextBox").slideUp();
	 });
 });