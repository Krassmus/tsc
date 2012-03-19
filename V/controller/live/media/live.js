TSC.live = {
    history: [],
    update: function (data) {
        
        if (data.contacts.length === 0) {
            //wenn aktuell niemand online ist:
            $("ul#live_contacts > li:not(#live_contact_niemand)")
                .slideUp("normal", function() {$(this).remove();} );
            if ($("ul#live_contacts > li#live_contact_niemand").length === 0) {
                $("ul#live_contacts")
                    .append('<li id="live_contact_niemand">Niemand außer Ihnen</li>');
                $("ul#live_contacts > li#live_contact_niemand")
                    .css("display", "none")
                    .slideDown();
            }
        } else {
        
            var vorhanden = false;
            //Nicht mehr aktive Kontakte löschen:
            $("ul#live_contacts > li").each(function (index, element) {
                vorhanden = false;
                $.each(data.contacts, function (data_index, contact) {
                    if ("live_contact_" + contact['id'] === element.id) {
                        vorhanden = true;
                    }
                });
                if (!vorhanden) {
                    $(element).slideUp("normal", function() {$(this).remove();} );
                }
            });
            //Neue Kontakte hinzufügen:
            $.each(data.contacts, function (index, contact) {
                vorhanden = false;
                $("ul#live_contacts > li").each(function (index, element) {
                    if (element.id === "live_contact_" + contact['id']) {
                        vorhanden = true;
                    }
                });
                if (!vorhanden) {
                    $("ul#live_contacts")
                        .append('<li id="live_contact_' + contact['id'] + '" class="live_contact_online">' + contact['view'] + '</li>');
                    $("li#live_contact_" + contact['id'])
                        .css("display", "none")
                        .slideDown();
                }
            });
        
        }

        //Nachrichten gehen ein:
        $.each(data.shorts, function (index, message) {
            if (!$('#live_incoming_message_' + message.id)[0]) {
                $('#live_incoming_list')
                    .append('<li id="live_incoming_message_' + message.id + '"></li>');
                $('#live_incoming_message_' + message.id).text("Nachricht von " + message.name)
                    .append($('<div/>').addClass("live_incoming_message_content").html(message.content))
                    .append($('<div/>').addClass("live_incoming_message_from_force").html(message.from_force))
                    .css("display", "none")
                    .slideDown();
                TSC.live.initHistory(message.from_force);
                TSC.live.history[message.from_force].name = message.name;
            }
        });
    },


    initHistory: function (force) {
        if (typeof TSC.live.history[force] === "undefined") {
            var name = $("#live_contact_" + force).text();
            TSC.live.history[force] = {
                'message': "",
                'history': [],
                'name': name
            };
        }
    },


    viewMessage: function (event) {
        var id = this.id.substr(this.id.lastIndexOf("_")+1);
        var content = $(this).children(".live_incoming_message_content").html();
        var force = $(this).children(".live_incoming_message_from_force").html();
        TSC.live.initHistory(force);
        TSC.live.history[force].history.push(content);
        $("#live_conversations").html(content);
        $(this).slideUp(function () {$(this).remove();});
        $.ajax({
            url: "ajax.php?controller=live&action=have_read_message",
            data: {
                message_id: id
            }
        });
        TSC.live.writeMessage(force);
    },

    
    writeMessage: function (force) {
        //Alte Nachricht sichern:
        if (jQuery("#live_writer_write_to").val()) {
            TSC.live.initHistory(jQuery("#live_writer_write_to").val());
            TSC.live.history[$("#live_writer_write_to").val()].message
                = jQuery("#live_message_content").val();
        }

        TSC.live.initHistory(force);
        
        //Stream der letzten Nachrichten hin schreiben:
        $("#live_conversation_with").html("Verlauf mit " + TSC.live.history[force].name + ": <hr>");
        $("#live_conversations").html("");
        $.each(TSC.live.history[force].history, function (index, element) {
            $("#live_conversations").append($("<div/>").html(element))
                                    .append($("<hr>"));
        });
        $("#live_conversations").scrollTo("100%");

        jQuery("#live_writer_forcename").html($(this).html());
        jQuery("#live_writer_write_to").val(force);
        if (typeof TSC.live.history[force] !== "undefined" ) {
            jQuery("#live_message_content").val(TSC.live.history[force].message);
        }
        $("#live_writer").show();
    },


    sendMessage: function () {
        //disable buttons
        $('#live_writer input[type=button]').attr('disabled', 'disabled');
        var force = $("#live_writer_write_to").val();
        $.ajax({
            url: "ajax.php?controller=live&action=send_message",
            data: {
                fromforce: $("#live_onlineas").val(),
                to: force,
                message: $("#live_message_content").val()
            },
            /*error: function () {
                //release the message and display error
                $('#live_writer input[type=button]').removeAttr('disabled');
            },*/
            success: function (response) {
                //clear message and enable buttons
                $('#live_writer input[type=button]').removeAttr('disabled');
                $("#live_message_content").val("");
                TSC.live.history[force].message = "";
                TSC.live.history[force].history.push("&gt; " + response);
                TSC.live.writeMessage(force);
            }
        });
    },


    change_as_online: function (new_force) {
        $.ajax({
            url: "ajax.php?controller=live&action=change_as_online",
            data: {
                new_force: new_force
            }
        });
    }
};

$(function () {
    $("ul#live_contacts > li.live_contact_online").live("click", function () {TSC.live.writeMessage(this.id.substr(this.id.lastIndexOf("_")+1));} );
    $("#live_incoming_list > li").live("click", TSC.live.viewMessage);
});