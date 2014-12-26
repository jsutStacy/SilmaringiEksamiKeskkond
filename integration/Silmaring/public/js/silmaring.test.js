var silmaringTest = {};
var single_option = '.single_option';
var single_option_size = 2;
var clean_question_block = '#clean-question-block';
var question_block = '.question_block';
var question_size = 0;
var question_text_field = '.question_text';
var question_points_field = '.question_points';

/**
 * Add new question
 */
silmaringTest.addQuestion = function()
{
    if(question_size == 0) question_size = 1;

    var new_question = $(clean_question_block).find(question_block).clone();
    new_question = silmaringTest.replaceTabIds(new_question, question_size);
    new_question = silmaringTest.replaceAllQuestionNameAttr(new_question, question_size);
    new_question.attr('id', 'question' + question_size);
    new_question.find('.questionNr').val(question_size);
    new_question.find('.progress').attr('id', 'progress' + question_size);
    new_question.find('.add_image input').attr('data-nr', question_size);

    $('#visible-question-blocks').append(new_question);

    $('#visible-question-blocks').sortable({
        axis: 'y',
        handle: ".move_item"
    });
    question_size++;
}

/**
 *  Add option
 */
silmaringTest.addOption = function(element)
{
    single_option_size++;
    var parent = element.parent().parent().parent();

    var first_element = $(parent).find(single_option).first().clone();
    first_element.find('.col-sm-5').after('<div class="col-sm-1"><a href="javascript:;" class="remove-option" onclick="silmaringTest.removeOption($(this));">X</a></div>');
    first_element = silmaringTest.replaceName(0,first_element, single_option_size);
    first_element = silmaringTest.replaceName(1,first_element, single_option_size);
    //first_element = silmaringTest.replaceName(2,first_element, single_option_size);
    $(parent).find(single_option).last().after(first_element);
}

/**
 * Remove option
 *
 * @param element
 */
silmaringTest.removeOption = function(element)
{
    $(element).parent().parent().remove();
}

/**
 * Replace option nr etc
 *
 * @param elementNr
 * @param first_element
 * @param single_option_size
 * @returns {*}
 */
silmaringTest.replaceName = function(elementNr, first_element, single_option_size)
{

    var first_input = first_element.find('input')[elementNr];
    var first_input = first_element.find(first_input);

    if(first_input.attr('type')!='radio' && first_input.attr('type')!='checkbox' && first_input.attr('type')!='hidden')
        first_input.val('');
    else
        first_input.prop('checked', false);

    var new_first = first_input.attr('name').replace(/^(.+\[answer_option_element\]\[)\d+(\].+)$/, '$1' + single_option_size + '$2');
    first_element.find(first_input).attr('name', new_first);

    return first_element;
}

/**
 *
 * @param new_question
 * @param question_size
 * @returns {*}
 */
silmaringTest.replaceAllQuestionNameAttr = function(new_question, question_size)
{
    new_question.find('input, textarea').each(function(){
        var f_input = $(this);
        var new_f_input_name = f_input.attr('name').replace(/question_element\[0\]/, 'question_element[' + question_size + ']');
        new_question.find(f_input).attr('name', new_f_input_name);
    });
    return new_question;
}

/**
 * Replace tab ids
 *
 * @param new_question
 * @param question_size
 * @returns {*}
 */
silmaringTest.replaceTabIds = function(new_question, question_size)
{
    $($(new_question).find('.nav-stacked li a')[0]).attr('href', '#tab_a_' + question_size);
    $($(new_question).find('.nav-stacked li a')[1]).attr('href', '#tab_b_' + question_size);
    $($(new_question).find('.nav-stacked li a')[2]).attr('href', '#tab_c_' + question_size);

    $($(new_question).find('.tab-pane')[0]).attr('id', 'tab_b_' + question_size);
    $($(new_question).find('.tab-pane')[1]).attr('id', 'tab_a_' + question_size);
    $($(new_question).find('.tab-pane')[2]).attr('id', 'tab_c_' + question_size);

    return new_question;
}

/**
 * Delete question
 *
 * @param element
 */
silmaringTest.deleteQuestion = function(element)
{
    $(element).parent().parent().parent().parent().parent().remove();
}

/**
 * Delete question
 *
 * @param element
 */
silmaringTest.deleteQuestionById = function(element, url)
{
    $(element).parent().parent().parent().parent().parent().remove();
    silmaring.postAjax(url);
}

/**
 *
 * @param element
 */
silmaringTest.updateQuestionText = function(element)
{
    $(element).parent().parent().parent().parent().parent().find(question_text_field).val($(element).val());
}

/**
 *
 * @param element
 */
silmaringTest.updateQuestionPoints = function(element)
{
    $(element).parent().parent().parent().parent().parent().find(question_points_field).val($(element).val());
}

/**
 * Create thumbnail from selected image file
 *
 * @param element
 */
silmaringTest.createImageThumb = function(element)
{
    $('#addForm').attr('action', $('#addTempImageForm').attr('action'));
    $('#addForm').find('.questionNr').val($(element).attr('data-nr'));
    silmaringTest.postAjaxFormQuestionImage($(element).attr('data-nr'), '#addForm');
    $('#addForm').submit();
}

silmaringTest.removeQuestionImage = function(element, url)
{
    $(element).parent().remove();
    silmaring.postAjax(url);
}


/**
 * Ajax post function with question image
 *
 * @param question_nr
 */
silmaringTest.postAjaxFormQuestionImage = function (question_nr, form) {


    $(form).ajaxForm({
        type: 'POST',
        dataType: 'json',
        beforeSubmit: function (e) {
            $('#visible-question-blocks #progress'+question_nr+'').show();
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').width('0%');
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').text('0%');
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').attr('aria-valuenow', 0);
        },
        uploadProgress: function (event, position, total, percentComplete) {
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').width(percentComplete + '%');
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').text(percentComplete + '%');
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').attr('aria-valuenow', percentComplete);
        },
        success: function () {
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').width('100%');
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').text('100%');
            $('#visible-question-blocks #progress'+question_nr+' .progress-bar').attr('aria-valuenow', 100);
        },
        complete: function (data) {
            var resp = $.parseJSON(data.responseText);

            if (!resp.success) {
                alert(resp.message.replace(/<br>/g, '\n'));
                $('#visible-question-blocks #progress'+question_nr+'').hide();
                return;
            }

            //thumbnail
            if(resp.image) {
                $('#visible-question-blocks #question'+question_nr+' .add_images').find('.row:last').append(resp.image);
                var imageCount = $('#visible-question-blocks #question'+question_nr+' .add_images .row img').size();
                if(imageCount%9==0) {
                    $('#visible-question-blocks #question'+question_nr+' .add_images').find('.row:last').after('</div><div class="row">');
                }
            }
        }
    });
}

/**
 * Reset answers after tab click
 * @param element
 */
silmaringTest.cleanAnswerInputs = function(element)
{
    var parentId = $(element).parent().parent().parent().attr('id');
    $('#' + parentId).find('input').prop('disabled', true);
    $('#' + parentId).find('.answerType').prop('disabled', false);
    //$('#' + parentId).find($(element).attr('href')).find('.answers-block').find('input[type=text]').val('');
    //$('#' + parentId).find($(element).attr('href')).find('.answers-block').find('input[type=checkbox]').prop('checked', false);
    $('#' + parentId).find('.answerType').val($(element).attr('data-type'));
    //$('#' + parentId).find($(element).attr('href')).find('.answers-block').find('input[type=checkbox]').prop('checked', false);
    $('#' + parentId).find($(element).attr('href')).find('input').prop('disabled', false);
}

/**
 * View question answers
 * @param url
 * @param id
 */
silmaringTest.viewAnswers = function(url, id)
{
    $('#viewAnswers' + id).slideToggle();
    if($('#viewAnswers' + id).find('.list-group').html()!=null) return true;

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: {
            'question_id': id
        }
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            if (!resp.success) {
                $('#viewAnswers' + id).html(resp.html);
                return;
            }
            $('#viewAnswers' + id).html(resp.html);

        });
}

/**
 * Change grade
 * @param grade
 * @param id
 */
silmaringTest.changeGrade = function(grade, id)
{
    $('#gradeText' + id).text(grade);
    $('#grade' + id).val(grade);
}

silmaringTest.setRightAnswer = function(element, parent)
{
    $(parent).find(':checked').not('input[type=checkbox]').prop('checked', false);
    if($(element).attr('type')!='checkbox')
         $(element).prop('checked', true);
}
