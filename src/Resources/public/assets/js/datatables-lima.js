$(document).ready(function() {
  $('#dataTable').DataTable( {
    "language": {
      "info": "",
      "zeroRecords": "Aucun enregistrement trouvé !",
      "infoEmpty": "",
      "infoFiltered": "",
        "paginate": {
          "previous": "Précédente",
          "next": "Suivante"
        },
      "lengthMenu": "Afficher : _MENU_",
      "search": "Rechercher : _INPUT_"
    }
  });
});