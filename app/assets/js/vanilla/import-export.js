$('#import-file').change(function(){
    var v = $(this).val().split('\\');
    var m = v[v.length-1]
    $('#labelChoose').text(m);
});

if (path == "/manager/people/"){
    document.getElementById("exportClick").addEventListener("click", function () {
        let checkboxs = document.getElementsByClassName("checkImport");
        const liste_id = [];
        for (const checkbox of checkboxs) {
            if (checkbox.checked === true) {
                liste_id.push(checkbox.parentNode.id);
            }
        }
        if (liste_id.length > 0) {
            $.ajax({
                url: URL + '/manager/imp-exp/export_selectif',
                method: "POST",
                data: {
                    ids: liste_id
                }
            }).done(function () {
                window.location = URL + "/export_selectif.xlsx";
            }).fail(function () {
                alert("Le serveur a rencontré des difficultés avec votre demande.");
            });
        } else {
            alert("Vous n'avez rien séléctionné");
        }
    });
}