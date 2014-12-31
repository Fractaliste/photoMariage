$('.menu').on('click', 'a', function (e) {
    if (!$(e.target).hasClass('bubble'))
        e.preventDefault();
});

var files = {nb: 0, taille: 0};
var fileUploading = 0;

if ($('#fileupload').length > 0)
    $('#fileupload').fileupload({
        url: "/deposer",
        add: function (e, data) {
            $("#submit").removeAttr('disabled');
            $("#liste").show();
            $.each(data.files, function (i, v) {
                if (!isValid(v))
                    $("#liste ul").append("<li>" + v.name + " <em>(Ce fichier n'a pas le bon format, il ne sera pas téléchargé !)</em></li>");
                else
                    $("#liste ul").append("<li>" + v.name + "</li>");
                updateFileInfo(v);
            });
            $("#submit").on("click", function (e) {
                e.preventDefault();
                if ($("#folder").val() === '') {
                    $("#folder").attr("placeholder", "Ce champ est obligatoire !")
                } else {
                    data.submit();
                }
            });

        },
        submit: function (e, data) {
            if (data.files.length === 1 && !isValid(data.files[0]))
                return false;
            else {
                $("#upload").hide();
                fileUploading++;
                if ($("#progress").length === 0)
                    $("#upload").parent().append("<p id='progress'>L'envoi des fichiers à commencé !<em></em></p>");
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $("#progress em").html((data.loaded / 1048576).toFixed(2) + "/" + (data.total / 1048576).toFixed(2) + "Mo (" + progress + "%)");
        },
        done: function () {
            fileUploading--;
            if (fileUploading === 0) {
                $("#upload").parent().append("<p>C'est terminé ! Pour envoyer de nouvelles photos actualisez la page (F5).</p>");
                $("#upload").parent().append("<p>Les photos seront disponible au téléchargement d'ici quelques minutes...</p>");
                $("#upload").parent().append("<p>Pour accéder au menu principal c'est <a href=\"/\">ICI</a></p>");

            }
        }
    });

function updateFileInfo(f) {
    files.taille += f.size;
    files.nb++;

    $("#nb_fichier").html(files.nb);
    $("#taille_fichier").html((files.taille / 1048576).toFixed(2));
}

function isValid(f) {
    var regex = /.*(jpeg)|(jpg)|(png)$/i;
    return f.name.match(regex);
}


if ($('#slide').length > 0) {
    var wHeight = $(window).height();
    var wWidth = $(window).width();
    if ($('#slide img').length > 1) {
        $(function () {
            $("#slide").slidesjs({
                width: wWidth * 0.5,
                height: wHeight * 0.5,
                pagination: {
                    active: false
                },
                callback: {
                    loaded: function (number) {
                        $(".slidesjs-previous").parent().append('<div id="navigation">')
                        var container = $("#navigation");
                        container.append($(".slidesjs-previous").detach());
                        container.append(' - ');
                        container.append($(".slidesjs-next").detach());
                        container.css('text-align', 'center');
                        $(".slidesjs-previous").html('Précédent');
                        $(".slidesjs-next").html('Suivant');

                        $('#slide .slidesjs-container').css('text-align', 'center');
                        
                        $('#slide img').css('max-width', '100%');
                        $('#slide img').css('max-height', '100%');
                        $('#slide img').css('width', 'auto');
                        $('#slide img').css('margin', 'auto');
                        $('#slide img').css('display', 'block');
                    }
                }
            });
        });
    }
    if ($('#slide img').length === 1) {
        $('#slide').css('display', 'block');
        $('#slide img').css('display', 'block');
        $('#slide img').css('margin', 'auto');
        $('#slide img').css('max-width', wWidth * 0.5);
        $('#slide img').css('max-height', wHeight * 0.5);
    }
}