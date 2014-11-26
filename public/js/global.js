$(document).ready(function(){

	//Add lesson
	typeSelect = $("[name='type']");
	urlField = $("#url-field");
	urlField.css("display", "none");
	typeSelect.change(function(){
		if ($(this).val() == "video"){
			urlField.css("display", "block");
		}
		else{
			urlField.css("display", "none");
		}
	});
});