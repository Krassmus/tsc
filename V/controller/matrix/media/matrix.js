
TSC.matrix = {
    
    openMatrix: function (matrix) {
        //öffnet die Anzeigeseite für die Artikel:
        TSC.link.show('matrix', 'pages');
        //öffne die richtige Matrix:
        if ($("div#matrix_content_" + matrix).css("display") === "none") {
            $("div#grouped_content:not(#matrix_content_" + matrix + ")").fadeout(function () {
                $("div#matrix_content_" + matrix).fadeIn();
            });
        }
    }, 
	
    openArticle: function (matrix, article) {
        TSC.matrix.openMatrix(matrix);
        //lade den Artikel:
        article = article.replace(/%HOCHKOMMA%/, "'");
        $("div#matrix_content_" + matrix).load("ajax.php", {
            controller: "matrix",
            action: "pages",
            group: matrix,
            artikel: unescape(article)
        });
        
    },
    
    searchLetter: function (matrix, letter) {
        TSC.matrix.openMatrix(matrix);
	//lade den Artikel:
        $("div#matrix_content_" + matrix).load("ajax.php", {
            controller: "matrix",
            action: "pages",
            group: matrix,
            artikel: "SUCHMASKE",
            letter: letter
        });
        
    },
    
    searchArticle: function (matrix, suche) {
        TSC.matrix.openMatrix(matrix);
		//lade den Artikel:
        $("div#matrix_content_" + matrix).load("ajax.php", {
            controller: "matrix",
            action: "pages",
            group: matrix,
            artikel: "SUCHMASKE",
			suche: suche
        });
        
    },
    
    editArticle: function (matrix, article) {
        TSC.matrix.openMatrix(matrix);
        //lade den Artikel:
        article = article.replace(/%HOCHKOMMA%/, "'");
        $("div#matrix_content_" + matrix).load("ajax.php", {
            controller: "matrix",
            action: "edit_article",
            group: matrix,
            artikel: article
        }, function () {
            $("#matrix_content").trigger("keyup"); //damit der Autoresize schon mal startet
            TSC.link.onLoad();
        });

    },

    showVersion: function (matrix, article, version) {
        TSC.matrix.openMatrix(matrix);
        //lade den Artikel:
        article = article.replace(/%HOCHKOMMA%/, "'");
        $("div#matrix_content_" + matrix).load("ajax.php", {
            controller: "matrix",
            action: "version",
            group: matrix,
            artikel: article,
            version: version
        });
    },

    saveArticle: function () {
        $.ajax({
            url: "ajax.php",
            data: {
                controller: "matrix",
                action: "save_article",
                group: $("#matrix_group").val(),
                title: $("#matrix_title").val(),
                content: $("#matrix_content").val(),
                autor: $("#matrix_autor").val(),
                bild: $("#matrix_bild").val()
            },
            type: "POST",
            success: function () {
                TSC.matrix.openArticle($("#matrix_group").val(), $("#matrix_title").val());
            }
        });
    },
    
    askForDeleting: function (matrix, article) {
        article = article.replace(/%HOCHKOMMA%/, "'");
        $("<div id='matrix_" + matrix + "_question'>Soll dieser Artikel wirklich gelöscht werden?</div>").dialog({
            title: "Sicherheitsabfrage",
            buttons: {
                "JA": function () {
                    $.ajax({
                        url: "ajax.php",
                        data: {
                            controller: "matrix",
                            action: "delete_article",
                            group: matrix,
                            page: article
                        },
                        success: function () {
                            $("#matrix_" + matrix + "_question").dialog("close").remove();
                            TSC.matrix.openArticle(matrix, "SUCHMASKE");
                            
                        }
                    });
                },
                "NEIN": function () {
                    $(this).dialog("close").remove();
                }
            }
        });
    },
    
    showImageDetails: function (image_id) {
		$("#matrix_image_details").load("ajax.php?controller=matrix&action=get_image_details&file_id="+image_id, function () {
			$("#matrix_image_canvas").slideUp(function () {
				$("#matrix_image_details").slideDown();
			});
		});
	},
	
	initUpload: function () {
		var uploader = new qq.FileUploader({
			element: $('#matrix_fileuploader')[0],
			// path to server-side upload script
			action: 'ajax.php',
			params: {
				controller: "matrix",
				action: "upload_picture",
				force_id: $("#matrix_pictureupload_force_id").val()
			},
			debug: false,
			onComplete: function(id, fileName, responseJSON) {
				TSC.link.loadContents('matrix', 'pictures', 'pictures');
			}
		});
	}
};
