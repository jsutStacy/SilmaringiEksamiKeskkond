$(document).ready(function() {
	//Add lesson
	$('select[name="type"] option[value="text"]').prop('selected', 'selected');

	$('select[name="type"]').change(function() {
		if ($(this).val() == 'video') {
			displayUpload('#url-field');
		}
		else if ($(this).val() == 'images') {
			displayUpload('#file-field');

			$('#file-field label span').html('.jpg, .jpeg, .png');
			$('#file-field input').addAttr('multiple', true);
		}
		else if ($(this).val() == 'audio') {
			displayUpload('#file-field');

			$('#file-field label span').html('.mp3, .wav');
			$('#file-field input').removeAttr('multiple');
		}
		else if ($(this).val() == 'presentation') {
			displayUpload('#file-field');

			$('#file-field label span').html('.pdf');
			$('#file-field input').removeAttr('multiple');
		}
		else {
			$('#lesson-upload').children().hide();
			$('input[type="file"]').val('');
		}
	});

	//Add subject (display form)
	$('#addSubject').click(function(e) {
		e.preventDefault();

		if(!$("#subjects").has("#addSubjectLi").length) {
			$("#subjects").prepend($('<li class="list-group-item" id="addSubjectLi">').load($(this).attr('href')));
			return false;
		}
	});

	//(ajax call)
	$(document).on('click', '#submitSubjectForm', function(e) {
		e.preventDefault();
		var formData = $('#form').serialize();

		$.ajax({
			type:"POST",
			dataType: "json",
			url:"add-subject",
			data: formData,
			//beforeSend: alert(formData),
			success: function(data) {
				$("#addSubjectLi").remove();
				$("#subjects").prepend(data.html);
			},
			error: (function(e) {
				alert("viga " + e);
			})
		})
	});

	//Edit subject (display form)
	$(document).on('click', 'a#cancelSubjectAdding', function(e) {
		e.preventDefault();
		$("#addSubjectLi").remove();
	});

	$(document).on('click', '.editSubject', function(e) {
		e.preventDefault();
		if(!$("#subjects").has("#editSubjectForm").length) {
			$(this).closest("li").append($('<div>').load($(this).attr('href')));
		}
		else{
			$("#editSubjectForm").remove();
			$(this).closest("li").append($('<div>').load($(this).attr('href')));
		}
		return false;
	});

	//(ajax call)
	$(document).on('click', 'a#submitEditSubjectForm', function(e) {
		e.preventDefault();
		var formData = $('#editSubjectForm').serialize();

		$.ajax({
			type:"POST",
			dataType: "json",
			url:"edit-subject",
			data: formData,
			success: function(data) {
				$("#editSubjectForm").remove();
				$("#subjectId"+data.subjectId+ " p.subjectName").remove();
				$("#subjectId"+data.subjectId).prepend("<p class='subjectName'>"+data.subjectName+"</p>");
			},
			error: (function(e) {
				alert("viga " + e);
			})
		});
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

	//Delete lesson file (teacher)
	$(".delete-lesson-file").click(function(e) {
		e.preventDefault();

		var url = $(this).attr("href");
		var linkId = $(this).attr("id");
		var id = linkId.match(/delete-lesson-file-(\d+)/)[1];
	
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "http://eksamikeskkond.silmaring.dev/teacher/delete-lesson-file/" + id,
			success: function(data) {
				alert(data.info);
				$("#delete-lesson-file-" + id).closest('tr').remove();
	
				if ($('table.lesson-files tbody').children('tr').length == 1) {
					$('table.lesson-files').hide();
				}
			},
			error: (function(e) {
				alert(JSON.stringify(e));
			})
		});
	});
});

function displayUpload(name) {
	$('#lesson-upload').children().hide();
	$('input[type="file"]').val('');
	$(name).show();
}