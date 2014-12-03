$(document).ready(function() {
	//Add lesson
	$('select[name="type"] option[value="text"]').prop('selected', 'selected');

	$('select[name="type"]').change(function() {
		if ($(this).val() == 'video') {
			displayUpload('#url-field');
		}
		else if ($(this).val() == 'images' || $(this).val() == 'audio' || $(this).val() == 'presentation') {
			displayUpload('#file-field');
		}
		else {
			$('#lesson-upload').children().hide();
			$('input[type="file"]').val('');
		}
	});

	//Add subject (display form)
	$("#addSubject").click(function(e) {
		e.preventDefault();
		$("#subjects").prepend($('<li class="list-group-item" id="addSubjectLi">').load($(this).attr('href')));

		return false;
	});
	
	//Edit subject (display form)
	$(".editSubject").click(function(e) {
		e.preventDefault();
		$(this).closest("li").load($(this).attr('href'));

		return false;
	});

	//Show lesson (student)
	$(".change-lesson").click(function(e) {
		id = $(this).attr("href");
		e.preventDefault();

		$.ajax({
			type:"POST",
			dataType: "json",
			url:"change-lesson/" + id,
			success: function(data) {
				$("#lessonContent").text(data.content);

				if (data.type == "video") {
					$("#lessonContent").append(data.html);
				}
			},
			error: (function() {
				$("#lessonContent").text("Vabandame, tekkis viga.");
			})
		});
	});
});

function displayUpload(name) {
	$('#lesson-upload').children().hide();
	$('input[type="file"]').val('');
	$(name).show();
};
