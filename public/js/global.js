$(document).ready(function(){

	//Add/edit lesson (teacher)
	typeSelect = $("[name='type']");
	urlField = $("#url-field");
	urlField.css("display", "none");

	if (typeSelect.val() == "video"){
		urlField.css("display", "block");
	}

	typeSelect.change(function(){
		if ($(this).val() == "video"){
			urlField.css("display", "block");
		}
		else{
			urlField.css("display", "none");
		}
	});
	
	//Show lesson (student)
	$(".change-lesson").click(function(e){
		id = $(this).attr("href");
		e.preventDefault();
		$.ajax({
			type:"POST",
			dataType: "json",
			url:"change-lesson/"+id,
			success: function(data){
				$("#lessonContent").text(data.content);
				if(data.type == "video"){
					$("#lessonContent").append(data.html);
				}
			},
			error: (function(){
				$("#lessonContent").text("Vabandame, tekkis viga.");
			})
		});

	});

});