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

	//Add subject (display form)
	$("#addSubject").click(function(e){
		e.preventDefault();
		$("#subjects").prepend($('<li class="list-group-item" id="addSubjectLi">').load($(this).attr('href')));
		return false;
	});
	
	//Edit subject (display form)
	$(".editSubject").click(function(e){
		e.preventDefault();
		$(this).closest("li").load($(this).attr('href'));
		return false;
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