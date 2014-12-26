var silmaring = {};
var flashMessage = '.flash-message';
var loading = '.loading';
var isCommentOpen = false;
var doRefreshOnModalClose = false;

/**
 * Show/Hide loading
 *
 * @param hide
 */
silmaring.loading = function (hide) {
    if (typeof hide == 'undefined')
        hide = false;

    if (hide) {
        $(loading).hide();
    }
    else {
        $(loading).show();
    }
}

/**
 * Show messages on forms
 *
 * @param message
 * @param hide
 * @param type
 */
silmaring.flashMessage = function (message, hide, type) {
    if (typeof type == 'undefined')
        type = 'warning';

    if (typeof hide == 'undefined')
        hide = false;


    if (hide) {
        $(flashMessage).html('');
        $(flashMessage).hide();
    }
    else {
        $(flashMessage).removeClass('alert-warning');
        $(flashMessage).removeClass('alert-success');
        $(flashMessage).addClass('alert-' + type);
        $(flashMessage).html(message);
        $(flashMessage).show();
    }
}


/**
 * Ajax post comment function with form
 *
 * @param url
 * @param form
 * @param reload
 */
silmaring.postCommentAjaxForm = function (url, form, id) {

    $('#item_comment_alert' + id).hide();
    if (typeof data == 'undefined') {
        data = {};
    }

    $('#item_comment_content' + id).html(silmaring.nl2br($('#item_comment' + id).find('textarea').val()));
    $('#item_comment_alert' + id).show();
    $('#item_comment' + id).slideUp();

    isCommentOpen = false;

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: $(form).serialize()
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
        });
}

/**
 * Ajax post function with form
 *
 * @param url
 * @param form
 * @param reload
 */
silmaring.postAjaxForm = function (url, form, reload) {
    if (typeof reload == 'undefined') reload = true;

    silmaring.flashMessage('', true);
    silmaring.loading();
    if (typeof data == 'undefined') {
        data = {};
    }

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: $(form).serialize()
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
            silmaring.loading(true);
        })
        .done(function (resp) {
            if (!resp.success) {
                    if(resp.message_type) {
                        silmaring.doMessageTypeActions(resp.message_type, $(form));
                    }
                silmaring.flashMessage(resp.message);
                silmaring.loading(true);
                return;
            }

            silmaring.flashMessage(resp.message, false, 'success');
            silmaring.loading(true);

            if (reload)
                window.location.reload();
        });
}

/**
 * Wrapper for post ajax form so we can use message
 * @param url
 * @param form
 * @param reload
 * @param message
 * @returns {boolean}
 */
silmaring.postAjaxFormWithConfirm = function (url, form, reload, message) {
    if (!confirm(message)) {
        return false;
    }

    silmaring.postAjaxForm(url, form, reload);
}

/**
 * Ajax post function with file upload
 *
 * @param url
 * @param form
 * @param reload
 */
silmaring.postAjaxFormWithFile = function (url, form, reload) {
    if (typeof reload == 'undefined') reload = true;

    silmaring.flashMessage('', true);
    silmaring.loading();
    if (typeof data == 'undefined') {
        data = {};
    }

    $(form).ajaxForm({
        type: 'POST',
        dataType: 'json',
        beforeSubmit: function (e) {
            $('#modalPopup .loads').show();
            $('#modalPopup .progress').show();
            $('#modalPopup .progress-bar').width('0%');
            $('#modalPopup .progress-bar').text('0%');
            $('#modalPopup .progress-bar').attr('aria-valuenow', 0);
        },
        uploadProgress: function (event, position, total, percentComplete) {
            $('#modalPopup .progress-bar').width(percentComplete + '%');
            $('#modalPopup .progress-bar').text(percentComplete + '%');
            $('#modalPopup .progress-bar').attr('aria-valuenow', percentComplete);
        },
        success: function () {
            $('#modalPopup .progress-bar').width('100%');
            $('#modalPopup .progress-bar').text('100%');
            $('#modalPopup .progress-bar').attr('aria-valuenow', 100);
            $('#modalPopup .loads').hide();
        },
        complete: function (data) {
            var resp = $.parseJSON(data.responseText);

            if (!resp.success) {
                silmaring.flashMessage(resp.message);
                silmaring.loading(true);
                return;
            }

            silmaring.flashMessage(resp.message, false, 'success');
            silmaring.loading(true);


            if (reload)
                window.location.reload();
        }
    });
}

/**
 * Ajax post function with object
 *
 * @param url
 * @param form
 * @param reload
 */
silmaring.postAjaxByObject = function (url, object, reload) {
    if (typeof reload == 'undefined') reload = true;

    silmaring.flashMessage('', true);
    silmaring.loading();
    if (typeof data == 'undefined') {
        data = {};
    }

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: object.serialize()
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
            silmaring.loading(true);
        })
        .done(function (resp) {
            if (!resp.success) {
                silmaring.flashMessage(resp.message);
                silmaring.loading(true);
                return;
            }

            silmaring.flashMessage(resp.message, false, 'success');
            silmaring.loading(true);

            if (reload)
                window.location.reload();
        });
}

/**
 * Ajax post function
 *
 * @param url
 * @param form
 * @param reload
 */
silmaring.postAjax = function (url, reload) {
    if (typeof reload == 'undefined') reload = false;

    silmaring.flashMessage('', true);
    silmaring.loading();

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
            silmaring.loading(true);
        })
        .done(function (resp) {
            if (!resp.success) {
                silmaring.flashMessage(resp.message);
                silmaring.loading(true);
                return;
            }

            silmaring.flashMessage(resp.message, false, 'success');
            silmaring.loading(true);

            if (reload)
                window.location.reload();
        });
}

///leave it just in case
silmaring.activateEdit2 = function (container) {
    $container = $(container);
    if ($container.find('input[type=text]').val() != null) {
        $container.find('input[type=text]').each(function () {
            $elem = $(this);
            $elem.removeClass('form-control');
            var small_input = '';
            if ($elem.hasClass('small-input')) {
                small_input = 'small-input';
                $elem.removeClass('small-input');
            }
            $elem.replaceWith('<span data-input_class="' + small_input + '" data-placeholder="' + $elem.attr('placeholder') + '" class="editable ' + $elem.attr('class') + '">' + $elem.val() + '</span>');
        });
    }
    else {
        $container.find('.editable').each(function () {
            $elem = $(this);
            $elem.replaceWith('<input type="text" class="form-control ' + $elem.attr('data-input_class') + ' ' + $elem.attr('class') + '" value="' + $elem.text() + '" placeholder="' + $elem.attr('data-placeholder') + '">');
        });
    }
}

/**
 * Enables/disabled form editing
 * Does ajax posting also
 *
 * @param container
 * @param url
 */
silmaring.doEdit = function (container, url) {

    silmaring.showEditCancel(true, container);
    silmaring.flashMessage('', true, '');
    $container = $(container);
    if (!$container.find('input[type=text], input[type=hidden]').is(':disabled')) {

        if ($('.block-1').html() != null && !silmaring.checkNumberNotSmaller('.block-1 .small-input')) {
            alert(visitNrSmallerErrorTranslation);
            return;
        }

        if ($('.block-2').html() != null && !silmaring.checkNumberNotSmaller('.block-2 .small-input')) {
            alert(visitNrSmallerErrorTranslation);
            return;
        }

        silmaring.postAjaxByObject(url, $container.find('input, textarea'), false);
        $container.find('input[type=text]').each(function () {
            $(this).prop('disabled', true);
        });
        silmaring.showEditCancel(false, container);

        //swtich reset workaround
        /* $("[name='switch-checkbox']").bootstrapSwitch('destroy');
         $("[name='switch-checkbox']").prop('disabled', true);
         $("[name='switch-checkbox']").bootstrapSwitch({
         size: 'mini',
         animate: false
         });*/

    }
    else {
        $container.find('input[type=text]').each(function () {
            $(this).prop('disabled', false);
        });

        //swtich reset workaround
        /*$("[name='switch-checkbox']").bootstrapSwitch('destroy');
         $("[name='switch-checkbox']").prop('disabled', false);
         $("[name='switch-checkbox']").bootstrapSwitch({
         size: 'mini',
         animate: false
         });
         $("[name='switch-checkbox']").on('switchChange.bootstrapSwitch', function (event, state) {
         silmaring.subscribe($(this).attr('data-url'), state);
         });*/
    }

    if (!$container.find('textarea').is(':disabled')) {
        $container.find('textarea').each(function () {
            $(this).prop('disabled', true);
        });
    }
    else {
        $container.find('textarea').each(function () {
            $(this).prop('disabled', false);
        });
    }

}

/**
 * Cancel form edit
 *
 * @param container
 */
silmaring.doCancelEdit = function (container) {
    $container = $(container);

    if (!$container.find('input[type=text]').is(':disabled')) {
        $container.find('input[type=text]').each(function () {
            $(this).prop('disabled', true);
        });

        //swtich reset workaround
        /*$("[name='switch-checkbox']").bootstrapSwitch('destroy');
         $("[name='switch-checkbox']").prop('disabled', true);
         $("[name='switch-checkbox']").bootstrapSwitch({
         size: 'mini',
         animate: false
         });*/

        window.location.reload();
    }

    if (!$container.find('textarea').is(':disabled')) {
        $container.find('textarea').each(function () {
            $(this).prop('disabled', true);
        });
    }

    silmaring.showEditCancel(false, container);
}

silmaring.showEditCancel = function (show, container) {
    if (typeof show == 'undefined')
        show = true;

    if (show) {
        $(container).find('.cancel-button').show();
    }
    else {
        $(container).find('.cancel-button').hide();
    }
}


/**
 * Opens given url in modal with ajax request
 *
 * @param url
 */
silmaring.openInModal = function (url, modalSize) {
    if (typeof modalSize == 'undefined') modalSize = '';

    silmaring.showModalLoading();
    $('#modalPopup').modal('show');
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
            silmaring.loading(true);
        })
        .done(function (resp) {
            if (!resp.success) {
                alert("Something went wrong. Contact us and let us know!");
                return;
            }

            $('#modalPopup').find('.modal-header h2').html(resp.title);
            $('#modalPopup').find('.modal-body').html(resp.html);
            $('#modalPopup').find('.modal-dialog').addClass(modalSize);

            $('#userpop').each(function () {
                $(this).popover('hide');
            });

            //silmaring.createDatepicker('#datepicker_birthday');
        });

    $('#modalPopup').on('hide.bs.modal', function () {
        if (doRefreshOnModalClose) window.location.reload();
    });
}

/**
 * Create jquery datepicker
 *
 * @param elem
 */
silmaring.createDatepicker = function (elem) {
    return $(elem).datepicker({
        firstDay: 1,
        dateFormat: 'dd.mm.yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '-100:+0'
    });
}


/**
 * Load search results
 * @param url
 */
silmaring.loadSearchAutoComplete = function (url) {
    $('#searchform #search-input').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: url,
                type: "POST",
                data: request,
                success: function (data) {
                    response($.map(data, function (el) {
                        return {
                            label: el.label,
                            value: el.value
                        };
                    }));
                }
            });
        },
        select: function (event, ui) {
            $('#searchform #search-input').val(ui.item.label);
            event.preventDefault();
            window.location.href = ui.item.value;
        },
        focus: function (event, ui) {
            $('#searchform #search-input').val(ui.item.label);
            event.preventDefault();
        }
    });
}


/**
 * Change invite level
 *
 * @param input
 * @param elem
 */
silmaring.changeInviteLevel = function (input, elem) {
    var parent = $(elem).parent();
    var val = 1;
    if (parent.hasClass('active')) {
        val = 0;
        parent.removeClass('active')
    }
    else {
        parent.addClass('active')
    }

    $(input).val(val);
}

silmaring.checkNumberNotSmaller = function (elems) {
    $biggestNr = 0;
    $success = true;
    $(elems).each(function () {
        if ($biggestNr > $(this).val() && $(this).val() != 0) {
            $(this).css('background', '#F59898');
            $success = false;
        }
        if ($biggestNr < $(this).val()) {
            $biggestNr = parseInt($(this).val());
            $(this).css('background', '#eee');
        }
    });

    return $success;
}

/**
 * Show loading
 */
silmaring.showModalLoading = function () {
    $('#modalPopup .modal-header h2').html(ajaxLoaderTitle);
    $('#modalPopup .modal-body').html('<img src="' + ajaxLoader + '" alt="loading" />');
}

/**
 *
 * @param url
 * @param removeSchoolUrl
 * @param removeText
 */
silmaring.connectSchools = function (url, removeSchoolUrl, removeText) {
    var schools = [];
    var tr = '';
    $('#choose-school input').each(function () {
        if ($(this).is(':checked')) {
            schools.push($(this).val());

            var name = $(this).parent().parent().find('td').eq(1).text();

            tr += '<tr>';
            tr += '<td>' + name + '</td>';
            tr += '<td><a href="' + removeSchoolUrl + '/' + $(this).val() + '">' + removeText + '</a></td>';
            tr += '</tr>';
        }
    });
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: {
            'schools': schools
        }
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            if (!resp.success) return;
            $('#connected-schools tbody').append(tr);
            //$('#modalPopup').modal('hide');
            window.location.reload();
        });
}

/**
 *
 * @param id
 */
silmaring.editComment = function (id) {
    $('#item_comment' + id).slideToggle();
    $('#item_comment_alert' + id).hide();
    if (!isCommentOpen) {
        $('#item_comment' + id).find('textarea').val($('#item_comment_content' + id).text());
        $('#item_comment_content' + id).text('');
        isCommentOpen = true;
    }
    else {
        $('#item_comment_content' + id).html(silmaring.nl2br($('#item_comment' + id).find('textarea').val()));
        isCommentOpen = false;
    }

}

/**
 *
 * @param str
 * @param is_xhtml
 * @returns {string}
 */
silmaring.nl2br = function (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

/**
 *
 * @param url
 * @param id
 */
silmaring.doDelete = function (url, id) {
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            $('#item' + id).remove();
            $('#item_' + id).remove();
            //window.location.reload();
        });
}

/**
 *
 * @param element
 */
silmaring.checkAll = function (element) {
    $(element).find('input').prop('checked', true);
}

/**
 *
 * @param url
 * @param id
 */
silmaring.loadMore = function (url) {
    $('#modal-load-more .text-center').find('button').hide();
    $('#modal-load-more .text-center').find('.loading').show();
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            $('#modal-load-more').parent().append(resp.html);
            $('#modal-load-more').remove();
        });
}

/**
 *
 * @param url
 * @param id
 */
silmaring.addToLessonaPlan = function (url, id) {
    $('#addToLessonPlan' + id).find('a').hide();
    $('#addToLessonPlan' + id).find('.loading').show();
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            $('#addToLessonPlan' + id).find('.loading').hide();
            $('#addToLessonPlan' + id).find('.text-success').show();

            doRefreshOnModalClose = true;
        });
}


/**
 * Check alerts
 * @param url
 */
silmaring.checkAlerts = function (url) {
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
            //alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            if (resp.count > 0) {
                $('#userpop').html('<span class="pop_number">' + resp.count + '</span>');
                $('#userpop_title').remove();
                $('#userpop_content').remove();
                $('#notify_logout').append(resp.html);
                $('#userpop').parent().removeClass('normal');
            }
            else {
                $('#userpop .pop_number').remove();
                if (resp.html != '') {
                    $('#userpop_title').remove();
                    $('#userpop_content').remove();
                }
            }
        });
}

silmaring.markAlertsAsRead = function (url) {
    if ($('#userpop_title').html() == null) return;
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
            //alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            $('#userpop .pop_number').remove();
            //$('#userpop_title').hide();
            //$('#userpop').parent().addClass('normal');
        });
}

/**
 * Add file view
 * @param url
 */
silmaring.addViewed = function (url) {
    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json"
    })
        .fail(function () {
        })
        .done(function (resp) {
        });
}

/**
 * Load statistics
 * @param url
 * @param type
 */
silmaring.loadStatisticsView = function (url, type, t_class) {
    var t_class_string = '';
    if (typeof t_class != 'undefined') {
        t_class_string = '#c' + t_class + ' ';
    }
    else {
        t_class = '';
    }

    if ($(t_class_string + '.' + type + '_wrap canvas').html() != null) {
        $(t_class_string + '.canvas_wrap').hide();
        $(t_class_string + '.' + type + '_wrap').show();
        return;
    }

    $(t_class_string + '.canvas_wrap').hide();
    $(t_class_string + '.' + type + '_wrap').show();

    if (typeof loadingHtml != 'undefined') {
        $(t_class_string + '.' + type + '_wrap').html(loadingHtml);
    }

    if (t_class == 'my') {
        t_class = '';
    }

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: {
            'type': type,
            'class_id': t_class
        }
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            if (!resp.success) {
                $(t_class_string + '.' + type + '_wrap').html('');
                return;
            }
            $(t_class_string + '.' + type + '_wrap').html(resp.html);

        });
}

/**
 * Load statistics tab
 * @param url
 * @param type
 */
silmaring.loadStatisticsTabView = function (url, class_id) {
    var t_class = '#c' + class_id;

    if ($(t_class).find('canvas').html() != null) {
        return;
    }

    if (typeof loadingHtml != 'undefined') {
        $(t_class).html(loadingHtml);
    }

    $.ajax({
        type: 'POST',
        url: url,
        dataType: "json",
        data: {
            'class_id': class_id
        }
    })
        .fail(function () {
            alert("Something went wrong. Contact us and let us know!");
        })
        .done(function (resp) {
            if (!resp.success) {
                $(t_class).html('');
                return;
            }
            $(t_class).html(resp.html);

        });
}

/**
 * Change category tree state
 * @param element
 * @param parent_id
 */
silmaring.changeCategoryTreeState = function (element) {
    if (element.is(':checked')) {
        silmaring.changeCategoryTreeSubState($('#c' + $(element).val()), true);
    }
    else {
        silmaring.changeCategoryTreeSubState($('#c' + $(element).val()), false);
    }
}

/**
 * Change category tree parent state
 * @param element
 * @param state
 */
silmaring.changeCategoryTreeSubState = function (element, state) {
    if ($(element).find('ul').find('input').size() > 0 && !state) {
        $(element).find('ul').find('input').each(function () {
            $(this).prop('checked', state);
            var id = $(this).val();
            if (id != 0) {
                silmaring.changeCategoryTreeSubState($('#c' + id), state);
            }
        });
    }
}

/**
 * Do message type actions
 *
 * @param messageType
 */
silmaring.doMessageTypeActions = function(messageType, form)
{
    if(messageType == 'ERROR_POINTS_EMPTY') {
        $(form).find('.question_points').each(function(){
            if($(this).val()=='') $(this).addClass('input-error')
            else $(this).removeClass('input-error');
        });
    }
}