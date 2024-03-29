$(document).ready(function() {

	//Add lesson
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

	//Edit lesson
	var selectedOption = $('select[name="type"] option[selected="selected"]').val();

	if (selectedOption == 'video') {
		displayUpload('#url-field');
	}
	else if (selectedOption == 'images') {
		displayUpload('#file-field');

		$('#file-field label span').html('.jpg, .jpeg, .png');
		$('#file-field input').addAttr('multiple', true);
	}
	else if (selectedOption == 'audio') {
		displayUpload('#file-field');

		$('#file-field label span').html('.mp3, .wav');
		$('#file-field input').removeAttr('multiple');
	}
	else if (selectedOption == 'presentation') {
		displayUpload('#file-field');

		$('#file-field label span').html('.pdf');
		$('#file-field input').removeAttr('multiple');
	}

	//Add subject (display form)
	$(document).on('click', '#addSubject', function(e) {
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
				alert("viga " + JSON.stringify(e));
			})
		})
	});

	//Edit course description
	$(document).on('click', '#editDesctiption', function(e) {
		e.preventDefault();
		if(!$("#courseDescription").has("#editDescriptionForm").length) {
			$("#courseDescription").append($('<div>').load($(this).attr('href')));
		}
		return false;
	});

	//(ajax call)
	$(document).on('click', 'a#submitEditDesc', function(e) {
		e.preventDefault();
		var formData = $('#editDescriptionForm').serialize();
		var url = $(this).attr("href");
		$.ajax({
			type:"POST",
			dataType: "json",
			url:url,
			data: formData,
			beforeSend: alert(formData),
			success: function(data) {
				$("#editSubjectForm").remove();
				$("#subjectId"+data.subjectId+ " p.subjectName").remove();
				$("#subjectId"+data.subjectId).prepend("<p class='subjectName'>"+data.subjectName+"</p>");
			},
			error: (function(e) {
				$("#courseDescription").html(JSON.stringify(e));
			})
		});
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

	//Add subsubject (display form)
	$(document).on('click', '.addSubsubject', function(e) {
		e.preventDefault();
		var subsubjectsUl =  $(this).parent().next().find(".subsubjects");
		if(!subsubjectsUl.has("#addSubsubjectLi").length) {
			$( "ul.subsubjects" ).each(function() {
				if ($(this).has("#addSubsubjectLi").length) {
					$("#addSubsubjectLi").remove();
				}
			});
			subsubjectsUl.prepend($('<li class="list-group-item" id="addSubsubjectLi">').load($(this).attr('href')));
			return false;
		}
	});

	//(ajax call)
	$(document).on('click', '#submitSubsubjectForm', function(e) {
		e.preventDefault();
		var formData = $('#subsubjectForm').serialize();
		var url = $(this).attr('href');
		var subsubjectsUl = $("#addSubsubjectLi").parent();
		$.ajax({
			type:"POST",
			dataType: "json",
			url:url,
			data: formData,
			success: function(data) {
				$("#addSubsubjectLi").remove();
				subsubjectsUl.prepend(data.html);
			},
			error: (function(e) {
				$("#addSubsubjectLi").prepend(JSON.stringify(e));
			})
		})
	});

	//Edit subsubject (display form)
	$(document).on('click', 'a#cancelSubsubjectAdding', function(e) {
		e.preventDefault();
		$("#addSubsubjectLi").remove();
	});

	$(document).on('click', '.editSubsubject', function(e) {
		e.preventDefault();
		if (!$(this).closest("li").has("#editSubsubjectForm").length) {
			$( "ul.subsubjects" ).each(function() {
				if ($(this).has("#editSubsubjectForm").length) {
					$("#editSubsubjectForm").remove();
				}
			});
			$(this).closest("li").prepend($('<div id="temporary">').load($(this).attr('href')));
		}		
		return false;
	});

	//(ajax call)
	$(document).on('click', 'a#submitEditSubsubjectForm', function(e) {
		e.preventDefault();
		var formData = $('#editSubsubjectForm').serialize();

		$.ajax({
			type:"POST",
			dataType: "json",
			url:"edit-subsubject",
			data: formData,
			success: function(data) {
				$("#temporary").remove();
				$("#subsubjectId"+data.subsubjectId+ " p.subsubjectName").remove();
				$("#subsubjectId"+data.subsubjectId).prepend("<p class='subsubjectName pull-left'>"+data.subsubjectName+"</p>");
			},
			error: (function(e) {
				$( "ul.subsubjects" ).text(JSON.stringify(e));
			})
		});
	});

	//Show lesson (student)
	$(document).on('click', '.change-lesson', function(e) {
		e.preventDefault();
		$("#lessonContent").load($(this).attr('href'));
	});

	//Delete lesson file (teacher)
	$(document).on('click', '.delete-lesson-file', function(e) {
		e.preventDefault();

		var url = $(this).attr("href");
		var linkId = $(this).attr("id");
		var id = linkId.match(/delete-lesson-file-(\d+)/)[1];
	
		$.ajax({
			type: "POST",
			dataType: "json",
			url: url,
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

	//Delete homework file (teacher)
	$(document).on('click', '.delete-homework-file', function(e) {
		e.preventDefault();

		var url = $(this).attr("href");
		var linkId = $(this).attr("id");
		var id = linkId.match(/delete-homework-file-(\d+)/)[1];
	
		$.ajax({
			type: "POST",
			dataType: "json",
			url: url,
			success: function(data) {
				alert(data.info);
				$("#delete-homework-file-" + id).closest('tr').remove();
	
				if ($('table.homework-file tbody').children('tr').length == 1) {
					$('table.homework-file').hide();
				}
			},
			error: (function(e) {
				alert(JSON.stringify(e));
			})
		});
	});

	//Mark lesson done (student)
	$(document).on('click', '.mark-lesson-done', function(e) {
		e.preventDefault();

		var url = $(this).attr("href");
		var linkId = $(this).attr("id");
		var id = linkId.match(/mark-lesson-done-(\d+)/)[1];

		$.ajax({
			type: "POST",
			dataType: "json",
			url: url,
			success: function() {
				$("#mark-lesson-done-" + id).hide();
				$("#mark-lesson-done-" + id).after("<p>Tund on märgitud tehtuks!</p>");

				$("#lesson-" + id).append('<span class="glyphicon glyphicon-ok pull-right"></span>');
			},
			error: (function(e) {
				alert(JSON.stringify(e));
			})
		});
	});
});

//Add note (display form)

$(document).on('click', '.new-note', function(e) {
	e.preventDefault();
	var url = $(this).attr('href');
	if($("#notes").length) {
		if(!$("#notes").has("#addNoteForm").length) {
			$("#notes").prepend($('<div>').load(url));
		}
	}
	else{
		alert(url);
	}
	
	return false;
});

//(ajax call)
$(document).on('click', 'a#submitAddNoteForm', function(e) {
	e.preventDefault();
	var formData = $('#addNoteForm').serialize();
	var url = $(this).attr("href");

	$.ajax({
		type:"POST",
		dataType: "json",
		url:url,
		data: formData,
		success: function(data) {
			$("#addNoteForm").remove();
			$("#notes").prepend(data.html);
		},
		error: (function(e) {
			alert("viga " + JSON.stringify(e));
		})
	});
});

//Edit note (display form)
$(document).on('click', '.edit-note', function(e) {
	e.preventDefault();
	var noteContainer = $(this).parent();
	if(!noteContainer.has("#editNote").length) {
		noteContainer.prepend($('<div id="editNote" class="well">').load($(this).attr('href')));
	}
	return false;
});

//(ajax call)
$(document).on('click', 'a#submitEditNoteForm', function(e) {
	e.preventDefault();
	var formData = $('#editNoteForm').serialize();
	var url = $(this).attr("href");
	$.ajax({
		type:"POST",
		dataType: "json",
		url:url,
		data: formData,
		//beforeSend: alert(url),
		success: function(data) {
			$("#editNote").remove();
			$("#note"+data.noteId+ " p").text(data.content);
		},
		error: (function(e) {
			$("#editNote").html("error"+JSON.stringify(e));
		})
	});
});


//Delete note
$(document).on('click', '.delete-note', function(e) {
	e.preventDefault();

	var url = $(this).attr("href");
	var noteContainer = $(this).parent();
	$.ajax({
		type:"POST",
		dataType: "json",
		url:url,
		success: function(data) {
			noteContainer.remove();
		},
		error: (function(e) {
			alert("viga " + JSON.stringify(e));
		})
	});
});


function displayUpload(name) {
	$('#lesson-upload').children().hide();
	$('input[type="file"]').val('');
	$(name).show();
}