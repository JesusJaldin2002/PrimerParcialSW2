// Verifica si DataTable ya está inicializado y destrúyelo
if ($.fn.DataTable.isDataTable("#datatable")) {
    $("#datatable").DataTable().destroy();
}

$(document).ready(function() {
    $.fn.dataTable.ext.type.order['hu-pre'] = function ( data ) {
        var match = data.match(/HU(\d+)/);
        return match ? parseInt( match[1], 10 ) : 0;
    };

    $('#datatable').DataTable({
        responsive: true,
        autoWidth: false,
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontró ningún resultado",
            "info": "Mostrando la página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        columnDefs: [
            { type: 'hu', targets: 0 }  // Aplica el tipo de ordenamiento personalizado a la columna de tareas
        ],
        order: [
            [4, 'asc'],  // Ordena primero por Sprint (suponiendo que está en la columna 4)
            [0, 'asc']   // Luego por Tarea (columna 0)
        ]
    });
});