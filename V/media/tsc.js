/**
 * Javascript file for tsc-interface
 */

/**
 * the main global object for all TSC-javascript ressources.
 */
var TSC = {};

/**
 * the link-logic for the navigation to display a different tab / action.
 */
TSC.link = {
    animation_duration: 100,
    show: function (controller, view, action) {
        var animation_duration = this.animation_duration;
        var opened_yet = false;
        //zuerst das wieder Ausblenden von allem bis auf die Kopfzeile:
        if ($("#body_" + controller + ":visible").length > 0 
                && typeof view === "undefined") {
            $("#body_" + controller + ":visible").fadeOut(animation_duration, function () {
                TSC.link.showAction(controller, view, action);
                opened_yet = true;
            });
            return false;
        }
        //Der Normalfall - etwas muss geschlossen werden, bevor es geöffnet werden kann:
        $("#bodies > div:visible:not(#body_" + controller + ")")
                .fadeOut(animation_duration, function () {
            $("#body_" + controller + ":not(:visible)")
                    .fadeIn(animation_duration, function () {
                TSC.link.showAction(controller, view, action);
                opened_yet = true;
            });
        });
        //Es ist noch gar nichts offen, dann öffne sofort den Reiter:
        if ($("#bodies div:visible:not(#body_" + controller + ")").length === 0) {
            $("#body_" + controller + ":not(:visible)")
                    .fadeIn(animation_duration, function () {
                TSC.link.showAction(controller, view, action);
                opened_yet = true;
            });
        }
        if (typeof view !== "undefined" && opened_yet === false) {
            TSC.link.showAction(controller, view, action);
        }
        return false;
    },
    showAction: function (controller, view, action) {
        var animation_duration = this.animation_duration;
        //Wenn der Content-Bereich unsichtbar ist.
        if ($("#content_" + controller + "_" + view).css("display") === "none") {
            $("#body_" + controller + " .content:visible").fadeOut(animation_duration, function () {
                $("#content_" + controller + "_" + view)
                        .delay(100)
                        .fadeIn(animation_duration);
            });
            if ($("#body_" + controller + " .content").length === 0) {
                $("#content_" + controller + "_" + view)
                        .fadeIn(animation_duration);
            }
        }
        //Laden des Contents:
        if (typeof view !== "undefined") {
            this.loadContents(controller, view, action);
        }
        return false;
    },
    loadContents: function (controller, view, action) {
        if (typeof action === "undefined") {
            if (!$.trim($("#content_" + controller + "_" + view).html())) {
                //load the view
                $("#content_" + controller + "_" + view)
                        .load("ajax.php?controller=" + controller + "&action=" + view, TSC.link.onLoad);             
            }
        } else {
            //load the special action
            $("#content_" + controller + "_" + view)
                    .load("ajax.php?controller=" + controller + "&action=" + action, TSC.link.onLoad);
        }
        return false;
    },
	/**
	 * what to do if some contents are loaded
	 */
    onLoad: function () {
        $("h1,h2,h3,h4,h5,h6,div,span,td,textarea,.colored,select").trigger("load");
    },
    activateGroup: function (controller, group) {
        TSC.link.show(controller, controller);
        $("." + controller + "_grouped_content:visible:not(#" + controller + "_content_" + group + ")").fadeOut();
        $("#" + controller + "_content_" + group).fadeIn();
    }
};

/**
 * the mouseover effect. This is no css but javascript to make it more smooth
 */
TSC.lighten = {
    delaytime: 200,
    steps: 5,
    isHighlighted: {},
    enlight: function (element) {
        $(element).animate({
            backgroundColor: "rgb(85, 85, 85)" 
        }, this.delaytime);
    },
    darken: function (element) {
        $(element).animate({
            backgroundColor: "rgb(51, 51, 51)" 
        }, this.delaytime);
    }
};
$(".lightable").live("mouseenter", function () {
    TSC.lighten.enlight(this);
});
$(".lightable").live("mouseleave", function () {
    TSC.lighten.darken(this);
});



/* ------------------------------------------------------------------------
 * JSUpdater
 * ------------------------------------------------------------------------ */

/**
 * the update function for the periodical update.
 * It receives data from update.php and simply hands it to the TSC.<module>.update() function, if this function exists.
 */
TSC.JSUpdater = {
    lastAjaxDuration: 200, //ms of the duration of an ajax-call
    currentDelayFactor: 0,
    lastJsonResult: {},
    dateOfLastCall: new Date(),
    idOfCurrentQueue: "",
    max_pause_time: 60000, //eine Minute, länger sollte es nicht dauern bis zum neuen Request

    processUpdate: function (new_things) {
        var somethingnew = false;
        $.each(new_things, function (modulename, infos) {
            if (typeof infos.data === "undefined") {
                infos.data = [];
            }
            if (typeof TSC[modulename] !== "undefined" && typeof TSC[modulename].update === "function") {
                TSC[modulename].update(infos.data);
            }
            if (typeof infos.title !== "undefined" && typeof infos.title.image_url !== "undefined") {
                $("#header_" + modulename).css("background-image", "url(" + infos.title.image_url + ")");
            }
            if (infos.data.alert) {
                somethingnew = true;
            }
        });
        window.document.title = somethingnew ? "TSC (!)" : "TSC";
    },

    /**
     * function to generate a queue of repeated calls
     * @call_id : id of the call-queue
     */
    call: function (queue_id) {
        if (queue_id !== TSC.JSUpdater.idOfCurrentQueue) {
            //stop this queue if there is another one
            return false;
        }
        TSC.JSUpdater.dateOfLastCall = new Date();
        $.ajax({
            url: "updater.php",
            success: function (json, textStatus, jqXHR) {
                TSC.JSUpdater.resetJsonMemory(json);
                TSC.JSUpdater.processUpdate(json);
                TSC.JSUpdater.nextCall(queue_id);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                TSC.JSUpdater.resetJsonMemory({ 'text' : textStatus, 'error': errorThrown });
                TSC.errorFunction("", jqXHR, {}, errorThrown);
                TSC.JSUpdater.nextCall(queue_id);
            }
        });
    },
    resetJsonMemory: function (json) {
        json = JSON.stringify(json);
        if (json !== TSC.JSUpdater.lastJsonResult) {
            TSC.JSUpdater.currentDelayFactor = 0;
        }
        TSC.JSUpdater.lastJsonResult = json;
        var now = new Date();
        TSC.JSUpdater.lastAjaxDuration = Number(now) - Number(TSC.JSUpdater.dateOfLastCall);
    },
    nextCall: function (queue_id) {
        var pause_time = TSC.JSUpdater.lastAjaxDuration
            * Math.pow(1.33, TSC.JSUpdater.currentDelayFactor)
            * 15; //bei 200 ms von einer Anfrage, sind das mindestens 4 Sekunden bis zum nächsten Request
        if (TSC.JSUpdater.max_pause_time > 0 && pause_time > TSC.JSUpdater.max_pause_time) {
            pause_time = TSC.JSUpdater.max_pause_time;
        }
        window.setTimeout(function () { TSC.JSUpdater.call(queue_id); }, pause_time);
        TSC.JSUpdater.currentDelayFactor++;
    }
};
$(function () {
    $("body").bind("mousemove", function () {
        TSC.JSUpdater.currentDelayFactor = 0;
        if (Number(new Date()) - Number(TSC.JSUpdater.dateOfLastCall) > 5000) {
            TSC.JSUpdater.idOfCurrentQueue = Math.floor(Math.random() * 1000000);
            TSC.JSUpdater.call(TSC.JSUpdater.idOfCurrentQueue);
        }
    });
    TSC.JSUpdater.idOfCurrentQueue = Math.floor(Math.random() * 1000000);
    TSC.JSUpdater.call(TSC.JSUpdater.idOfCurrentQueue);
});


/**
 * special error handling when an ajax-call results in an error/exception.
 * This is set in general for all ajax-calls, but may locally be overwritten via $.
 */
TSC.errorFunction = function(event, request, settings, thrownError) {
    if ($(".ui-dialog.error").length === 0) {
        $("<div>" + request.responseText + "</div>").dialog({
            modal: false,
            title: "Fehler im Modul!",
            width: 500,
            height: 400,
            show: 'puff',
            hide: 'puff',
            dialogClass: 'error',
            close: function () {
                $(this).remove();
            }
        });
    }
};
$(window.document).ajaxError(TSC.errorFunction);

/**
 * some functions to be called whenever some content is loaded via ajax.
 */
$(function () {
    $("h1, h2, h3, h4, .colored").live("load", function () {
        $(this).css("color", TSC.stil.headercolor === "1" ? "#8BEDFF" : (TSC.stil.headercolor === "2" ? "#5CFF58" : "#FFAD00"));
        return true;
    });
    $("textarea").live("load", function () {
        $(this).autoResize();
    });
    //for a good start-up:
    TSC.link.onLoad();
});

