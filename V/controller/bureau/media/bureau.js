
TSC.bureau = {
    set: function (typus, field) {
        $.ajax({
            url: "ajax.php?controller=bureau&action=set",
            data: {
                typus: typus,
                val: $(field).length ? $(field).val() : field
            },
            type: "POST"
        });
        if (typus === "headercolor") {
            TSC.stil.headercolor = $(field).val();
            $("h1, h2, h3, h4, .colored").trigger("load");
        }
        if (typus === "backgroundimage") {
            $("body").css("background-image", 
                            $(field).val() > 0 
                            ? "url('file.php?module=matrix&type=MatrixImage&file_id=" + $(field).val() + "')"
                            : "url('media/images/Homecoming_by_keepwalking07.jpg')");
        }
    },
    changepassword: function () {
        if ($('#pass1').val() && $('#pass1').val() === $('#pass2').val()) {
            this.set("password", $('#pass1').val());
            $('#pass1').val('');
            $('#pass2').val('');
        }
    },
	
	setForcePicture: function (force_id, picture_id) {
		$.ajax({
            url: "ajax.php?controller=bureau&action=set_force_picture",
            data: {
                force_id: force_id,
                picture_id: picture_id
            },
            type: "POST",
			success: function (response) {
				console.log($("#bureau_settings_forcepicture_" + force_id));
				$("#bureau_settings_forcepicture_" + force_id)
					.css("background-image", "url(file.php?module=matrix&type=MatrixImage&file_id=" + picture_id + ")");
			}
        });
        
	},
    
    adminHappynewyear: function (gruppe) {
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "bureau",
                action: "happynewyear",
                group: gruppe
            },
            success: function (message) {
                $('#bureau_content_' + gruppe).html(message);
            }
        });
    },

    adminModules: function () {
        var gruppe = $(this).closest("tr").attr("group");
        var modules = [];
        $(this).children(".module").each(
            function () {
                modules.push($.trim($(this).text()));
            }
        );
        $.ajax({
            url: "ajax.php?controller=bureau&action=set_module_enabled",
            data: {
                'modules': modules,
                'group': gruppe
            }
        });
    },
    
    //Anzeige einer einzelnen Macht der Gruppe
    adminMachtanzeige: function (gruppe, macht) {
        TSC.link.show("bureau", "admin");
        $("#bureau_admin_" + gruppe + "_macht").load("ajax.php", 
            {
                controller: "bureau",
                action: "admin_show_macht",
                group: gruppe,
                macht: macht
            }, function () {
            //umschalten
            $("#bureau_admin_" + gruppe + "_overview").fadeOut(TSC.link.animation_duration, function () {
                $("#bureau_admin_" + gruppe + "_macht").slideDown();
            });
            
        });
    },
    //Zeige wieder die Übersicht über die Gruppe
    adminUebersicht: function (gruppe) {
        $("#bureau_admin_" + gruppe + "_macht").slideUp(TSC.link.animation_duration, function () {
            $("#bureau_admin_" + gruppe + "_overview").fadeIn(TSC.link.animation_duration, function () {
                $("#bureau_admin_" + gruppe + "_macht").html("");
            });
            $('#bureau_content_' + gruppe).load("ajax.php", {
                controller: "bureau",
                action: "admin",
                group: gruppe
            });
        });
    },
    
    //neuen Spieler aufnehmen:
    adminNeuerSpielerUndNameFuerGruppe: function (gruppe) {
        var spielername = $("#bureau_admin_" + gruppe + "_spielername").val();
        var machtname = $("#bureau_admin_" + gruppe + "_spielermacht").val();
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "bureau",
                action: "neuer_spieler_und_macht",
                group: gruppe,
                spielername: spielername,
                machtname: machtname
            },
            success: TSC.bureau.adminSuccessMessage
        });
    },
    
    //neue Macht anlegen:
    adminNeueMachtFuerGruppe: function (gruppe) {
        var machtname = $("#bureau_admin_" + gruppe + "_neuemacht").val();
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "bureau",
                action: "neue_macht",
                group: gruppe,
                machtname: machtname
            },
            success: TSC.bureau.adminSuccessMessage
        });
    },
    
    adminSuccessMessage: function (message) {
        $("<div>" + message + "</div>").dialog({
            title: "Es hat geklappt!",
            close: function () {
                $(this).remove(); //immer schön aufräumen!
            }
        });
    },
    
    admin_setcontact: function (gruppe, macht1, macht2, checkbox) {
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "bureau",
                action: "setcontact",
                group: gruppe,
                macht1: macht1,
                macht2: macht2,
                contact: checkbox.checked ? 1 : 0
            }
        });
    },
    
    admin_give_force_to: function (gruppe, macht, spieler) {
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "bureau",
                action: "give_force_to",
                group: gruppe,
                macht: macht,
                spieler: spieler
            },
            success: function(message) {
                console.log(message);
                $('#bureau_admin_' + gruppe + '_macht').html(message);
            }
        });
    },
    admin_activate_module: function () {
        var module = $.trim($(this).closest("label").text());
        var checked = this.checked;
        var group = $(this).closest("[id^=bureau_admin_]").children("input[type=hidden][name=gruppe]").val();
        var position = $(this).closest("li").children("[name=position]").val();
        $.ajax({
            url: "ajax.php?controller=bureau&action=set_module_enabled",
            data: {
                'module': module,
                'checked': checked ? 1 : 0,
                'group': group,
                'position': position
            }
        });
    }
};

$("input[type=checkbox].admin_activate_module").live("change", TSC.bureau.admin_activate_module);