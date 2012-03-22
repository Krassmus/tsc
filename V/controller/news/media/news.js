TSC.news = {
    
    offsets: {},
	
    getYear: function () {
        return $("#news_year_selected").val();
    },
    
    getLatestDate: function () {
        var latest = 0;
        $("#news_eingang > tbody >tr, #news_ausgang > tbody > tr").each(function (index, row) {
            if (Number($(row).attr("timestamp")) > latest) {
                latest = $(row).attr("timestamp");
            }
        });
        return latest;
    },
    
    cleanBoxes: function () {
        $("table#news_eingang > tbody > tr").remove();
        $("table#news_ausgang > tbody > tr").remove();
    },
    
    loadBoxes: function () {
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "news",
                action: "get_mails",
                year: TSC.news.getYear(),
                latest_date: TSC.news.getLatestDate()
            },
            dataType: "json",
            success: function (message) {
                $.each(message.inbox, function (index, value) {
                    if ($("#news_inbox_" + value.id).length === 0) {
                        $("#news_eingang > tbody").prepend(value.html);
                        $("#news_inbox_" + value.id)
                                .css("display", "none")
                                .delay(20 * index)
                                .fadeIn(300);
                    }
                });
				$.each(message.outbox, function (index, value) {
                    if ($("#news_outbox_" + value.id).length === 0) {
                        $("#news_ausgang > tbody").prepend(value.html);
                        $("#news_outbox_" + value.id)
                                .css("display", "none")
                                .delay(20 * index)
                                .fadeIn(300);
                    }
                });
            }
        });
    },
    
    update: function (data) {
        if (Number(data.news) > 0) {
			TSC.news.loadBoxes();
		}
    },
	
    showMessage: function (id, from) {
            if ($('#news_inbox_' + id).hasClass("pdf")) {
                    window.open("file.php?module=news&type=NewsPDF&file_id=" + id);
            } else {
                    $('#news_singlemessage')
                            .text("Bitte warten ..")
                            .load('ajax.php', {
                                            controller: 'news',
                                            action: 'watch_mail',
                                            message_id: id
                                    },
                                    function () {
                                            $('#news_inbox_' + id).removeClass("neu");
                                    }
                            );
                    TSC.news.offsets[from] = window.pageYOffset;
                    $("table#" + from).fadeOut(function () {
                            $('#news_singlemessage').slideDown();
                    });
            }
    },
    showInbox: function () {
            var callback = function () {
                    $("table#news_eingang").fadeIn(function () {
                            if (TSC.news.offsets['news_eingang'] > 0) {
                                    $.scrollTo(TSC.news.offsets['news_eingang'] + 'px', {
                                            duration: 2,
                                            axis: 'y'
                                    });
                            }
                    });
            };
            if ($('#news_singlemessage').css("display") !== "none") {
                    $('#news_singlemessage').slideUp(callback);
            }
            if ($('table#news_ausgang').css("display") !== "none") {
                    TSC.news.offsets['news_eingang'] = 0;
                    $('table#news_ausgang').fadeOut(callback);
            }
            TSC.news.loadBoxes();
    },
    showOutbox: function () {
            var callback = function () {
                    $("table#news_ausgang").fadeIn(function () {
                            if (TSC.news.offsets['news_ausgang'] > 0) {
                                    $.scrollTo(TSC.news.offsets['news_ausgang'], {
                                            duration: 2,
                                            axis: 'y'
                                    });
                            }
                    });
            };
            if ($('#news_singlemessage').css("display") !== "none") {
                    $('#news_singlemessage').slideUp(callback);
            }
            if ($('table#news_eingang').css("display") !== "none") {
                    TSC.news.offsets['news_ausgang'] = 0;
                    $('table#news_eingang').fadeOut(callback);
            }
            TSC.news.loadBoxes();
    },

    saveNews: function () {
            $.ajax({
                    url: "ajax.php?controller=news&action=save",
                    data: {
                            fromforce: $("#sent_by_force").val(),
                            content: $("#news_composer_message").val(),
                            message_id: $("#news_composer_message_id").val()
                    },
                    success: function (message) {
            if (message) {
                                    $("#news_composer_message_id").val(message);
                            }
        }
            });
    },

    sendNews: function () {
        var adressees = $("#news_composer_adressee_of_" + $("#sent_by_force").val()).val();
        if (adressees !== null) {
            $.ajax({
                url: "ajax.php?controller=news&action=send",
                data: {
                        fromforce: $("#sent_by_force").val(),
                        content: $("#news_composer_message").val(),
                        adressees: adressees,
                        message_id: $("#news_composer_message_id").val(),
                        pdf: $('#news_composer_pdfswitch_pdf').is(":checked") ? 1 : 0
                },
                success: function () {
                        $("#news_composer_message").val("");
                        $("#news_composer_message_id").val("");
                        $("[id^=news_composer_adressee_of_]").val(null);
                        TSC.news.initUpload();
                }
            });
        } else {

        }
    },

    initUpload: function () {
        var uploader = new qq.FileUploader({
            element: $('#news_composer_content_pdf')[0],
            // path to server-side upload script
            action: 'ajax.php',
            params: {
                controller: "news",
                action: "upload_pdf",
                force_id: $("#sent_by_force").val(),
                message_id: $("#news_composer_message_id").val() ? $("#news_composer_message_id").val() : ""
            },
            onComplete: function(id, fileName, responseJSON) {
                $("#news_composer_message_id").val(responseJSON['id']);
                TSC.news.initUpload();
            }
        });
    },
    setFlag: function (id, input) {
        $.ajax({
            url: "ajax.php?controller=news&action=set_flag",
            data: {
                'message_id': id,
                'flag': $(input).is(":checked") ? 1 : 0
            },
            success: function () {
                if ($(input).is(":checked")) {
                    $('#news_inbox_' + id).addClass("marked");
                } else {
                    $('#news_inbox_' + id).removeClass("marked");
                }
            }
        });
    } 
};

$("table#news_eingang > tbody > tr").live("click", function () {
    var message_id = this.id;
	message_id = this.id.substr(this.id.lastIndexOf("_")+1);
	TSC.news.showMessage(message_id, "news_eingang");
});
$("table#news_ausgang > tbody > tr").live("click", function () {
    var message_id = this.id;
	message_id = this.id.substr(this.id.lastIndexOf("_")+1);
	TSC.news.showMessage(message_id, "news_ausgang");
});

$(function () { TSC.news.loadBoxes(); });