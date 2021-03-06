if(path=="/manager/modeles/new") {
    let indice_tableau=0;

    let tab = ["Nom","Prénom","Date de naissance", "Code postal", "Ville", "Abonné à la newsletter", "Adresse mail", "Institution"];


    let titre = document.createElement('h6');
    titre.innerHTML = "Personnaliser les champs existant";

    while(indice_tableau < 8){
        let label_div = document.createElement("div");
        let input_div = document.createElement("div");
        let input_label = document.createElement("input");

        input_label.name = "custom_data["+indice_tableau+"][label]"
        input_label.className = "form-control";
        input_label.name = "custom_data["+indice_tableau+"][label]";
        input_label.readOnly = true;
        input_label.value = tab[indice_tableau];

        let input_value = document.createElement("input");
        
        input_value.required = true;
        input_value.className = "form-control";
        input_value.name = "custom_data["+indice_tableau+"][value]";
        input_value.placeholder = "Nom du champ...";


        let row = document.createElement("div");
        row.style = "display: flex; flex-direction: row;"


        label_div.append(input_label);
        label_div.style = "width: 40%; margin-right: 2%;";

        input_div.append(input_value);
        input_div.style = "width: 60%; margin-left: 2%;";

        let icon = document.createElement('i');
        icon.className = "fas fa-equals";

        row.append(label_div, icon, input_div);
        console.log(indice_tableau);
        if ( indice_tableau == 0 ) {
            console.log('ok');
            let big_div = document.createElement('div');
            big_div.append(titre, row);
            big_div.style = "display: flex; flex-direction: column;";
            $("#person_data").before(big_div);
        } else {
            $("#person_data").before(row);
        }
        row.style = "padding-bottom: 20px; padding-top: 10px; display: flex; flex-direction: row; align-items: center;";

        indice_tableau++;
    }

    document.getElementById("add_custom_data").addEventListener('click',function () {

        let label_div = document.createElement("div");
        let input_div = document.createElement("div");
        let input_label = document.createElement("input");

        input_label.className = "form-control";
        input_label.name = "custom_data["+indice_tableau+"][label]";
        input_label.readOnly = true;
        input_label.value = "Complémentaires "+indice_tableau;
        input_label.placeholder = "Nom du champ...";
        
        let input_value = document.createElement("input");

        input_value.className = "form-control";
        input_value.name = "custom_data["+indice_tableau+"][value]";
        input_value.placeholder = "Nom du champ...";

        let row = document.createElement("div");
        row.style = "display: flex; flex-direction: row;"

        label_div.append(input_label);
        label_div.style = "width: 40%; margin-right: 2%;";

        input_div.append(input_value);
        input_div.style = "width: 60%; margin-left: 2%;";

        let icon = document.createElement('i');
        icon.className = "fas fa-equals";

        row.append(label_div, icon, input_div);
        if ( indice_tableau == 0 ) {
            let big_div = document.createElement('div');
            big_div.append(titre, row);
            big_div.style = "display: flex; flex-direction: column;";
            $("#person_data").before(big_div);
        } else {
            $("#person_data").before(row);
        }
        row.style = "padding-bottom: 20px; padding-top: 10px; display: flex; flex-direction: row; align-items: center;";

        indice_tableau++;
    })


}