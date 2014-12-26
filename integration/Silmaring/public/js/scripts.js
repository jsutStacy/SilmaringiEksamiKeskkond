$(function(){
$(document).on('submit', '.form-signin', function (e) {
    e.preventDefault();
    silmaring.postAjaxForm(loginUrl, "#login");
});

$(".has_tooltip").tooltip();

$("#userpop").popover({
    html : true,
    title: function() {
        return $('#userpop_title').html();
    },
    content: function() {
        return $('#userpop_content').html();
    }
});



$('.class-selection .dropdown-menu input, .class-selection .dropdown-menu label, .class-selection .dropdown-menu a.select-all').click(function(e) {
    e.stopPropagation();
});

//click popover outside popover
$(document).on('click', 'body', function (e) {
    $('#userpop').each(function () {
        // hide any open popovers when the anywhere else in the body is clicked
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
            $(this).popover('hide');
        }
    });
});

$('#lesson-plan').sortable({
    axis: 'y',
    handle: ".move_item",
    update: function (event, ui) {
        var data = $('#lesson-plan').sortable('serialize');
        $.ajax({
            type: 'POST',
            url: $('#lesson-plan').attr('data-url'),
            dataType: "json",
            data : data
        })
            .fail(function () {
                alert("Something went wrong. Contact us and let us know!");
            });
    }
});

/*$('li .open_comment').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $collapse = $this.closest('.collapse-group').find('.collapse');
        $collapse.collapse('toggle');
});*/


 if ($('.ajax-school-dt').html() != null) {

        if (typeof dt_url == 'undefined') dt_url = '';

        $('.ajax-school-dt').dataTable({
            "oLanguage": {
                "sUrl": dataTablesLang
            },
            "bFilter": true,
            "bInfo": false,
            "sPaginationType": "bootstrap",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": dt_url,
            "aLengthMenu": [25, 50, 75],
            "iDisplayLength": 25,
            "aaSorting": [
                [ 0, "desc" ]
            ],
            "aoColumns": [
                { "sWidth": "5%", 'sType' : 'numeric' },
                { "sWidth": "65%", 'sType' : 'string'  },
                { "sWidth": "15%", 'sType' : 'string'  },
                { "sWidth": "5%" },
                { "sWidth": "10%" }
            ]
        });
    }

    if ($('.ajax-my-school-dt').html() != null) {

        if (typeof dt_url == 'undefined') dt_url = '';

        $('.ajax-my-school-dt').dataTable({
            "oLanguage": {
                "sUrl": dataTablesLang
            },
            "bFilter": true,
            "bInfo": false,
            "sPaginationType": "bootstrap",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": dt_url,
            "aLengthMenu": [15, 25, 35],
            "iDisplayLength": 15,
            "aaSorting": [
                [ 0, "desc" ]
            ],
            "aoColumns": [
                { "sWidth": "65%", 'sType' : 'string'  },
                { "sWidth": "15%", 'sType' : 'string'  }
            ]
        });
    }

    if ($('.ajax-users-dt').html() != null) {

        if (typeof dt_url == 'undefined') dt_url = '';

        $('.ajax-users-dt').dataTable({
            "oLanguage": {
                "sUrl": dataTablesLang
            },
            "bFilter": true,
            "bInfo": false,
            "sPaginationType": "bootstrap",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": dt_url,
            "aLengthMenu": [25, 50, 75],
            "iDisplayLength": 25,
            "aaSorting": [
                [ 0, "desc" ]
            ],
            "aoColumns": [
                { "sWidth": "5%", 'sType' : 'numeric' },
                { "sWidth": "20%", 'sType' : 'string'  },
                { "sWidth": "15%", 'sType' : 'string'  },
                { "sWidth": "15%", 'sType' : 'string'  },
                { "sWidth": "10%", 'sType' : 'string'  },
                { "sWidth": "10%", 'sType' : 'string'  },
                { "sWidth": "5%" },
                { "sWidth": "10%" }
            ]
        });
    }

    if ($('.ajax-teachers-dt').html() != null) {

        if (typeof dt_url == 'undefined') dt_url = '';

        $('.ajax-teachers-dt').dataTable({
            "oLanguage": {
                "sUrl": dataTablesLang
            },
            "bFilter": true,
            "bInfo": false,
            "sPaginationType": "bootstrap",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": dt_url,
            "aLengthMenu": [25, 50, 75],
            "iDisplayLength": 25,
            "aaSorting": [
                [ 0, "desc" ]
            ],
            "aoColumns": [
                { "sWidth": "25%", 'sType' : 'string' },
                { "sWidth": "20%", 'sType' : 'string'  },
                { "sWidth": "15%", 'sType' : 'string'  },
                { "sWidth": "30%", 'sType' : 'string'  },
                { "sWidth": "5%" },
                { "sWidth": "10%" }
            ]
        });
    }

    if ($('.ajax-students-dt').html() != null) {

        if (typeof dt_url == 'undefined') dt_url = '';

        $('.ajax-students-dt').dataTable({
            "oLanguage": {
                "sUrl": dataTablesLang
            },
            "bFilter": true,
            "bInfo": false,
            "sPaginationType": "bootstrap",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": dt_url,
            "aLengthMenu": [25, 50, 75],
            "iDisplayLength": 25,
            "aaSorting": [
                [ 0, "desc" ]
            ],
            "aoColumns": [
                { "sWidth": "25%", 'sType' : 'string' },
                { "sWidth": "20%", 'sType' : 'string'  },
                { "sWidth": "15%", 'sType' : 'string'  },
                { "sWidth": "30%", 'sType' : 'string'  },
                { "sWidth": "5%" },
                { "sWidth": "10%" }
            ]
        });
    }


    $("#datapaginaton").ready(function () {
        $(".dataTables_paginate").appendTo($("#datapaginaton"));
    });
});