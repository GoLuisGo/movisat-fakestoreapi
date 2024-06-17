
function actualizarGRID(products) {
    // Fuente de datos para jqxGrid
    var datos = {
        localdata: products,
        datatype: "array",
        datafields: [
            { name: 'id', type: 'number' },
            { name: 'title', type: 'string' },
            { name: 'price', type: 'number' },
            { name: 'description', type: 'string' },
            { name: 'category', type: 'string' },
            { name: 'image', type: 'string' }
        ]
    };

    // Instanciar el componente jqxGrid
    var dataAdapter = new $.jqx.dataAdapter(datos);

    // Localización en español (traducir los controles del grid)
    var localizationobj = {
        pagergotopagestring: "Ir a la página:",
        pagershowrowsstring: "Mostrar filas:",
        pagerrangestring: " de ",
        pagerpreviousbuttonstring: "anterior",
        pagernextbuttonstring: "siguiente",
        pagerfirstbuttonstring: "primero",
        pagerlastbuttonstring: "último",
        filterapplystring: "Aplicar",
        filtercancelstring: "Cancelar",
        filterclearstring: "Limpiar",
        filterstring: "Filtro",
        emptydatastring: "No hay datos que mostrar",
        loadtext: "Cargando...",
        sortascendingstring: "Orden Ascendente",
        sortdescendingstring: "Orden Descendente",
        sortremovestring: "Quitar orden",
        // Traducción de filtros
        filterselectallstring: "(Seleccionar Todo)",
        filterchoosestring: "Por favor elija:",
        filterstringcomparisonoperators: ['vacío', 'no vacío', 'contiene', 'contiene(caso)', 'no contiene', 'no contiene(caso)', 'comienza con', 'comienza con(caso)', 'termina con', 'termina con(caso)', 'igual', 'igual(caso)', 'nulo', 'no nulo'],
        filternumericcomparisonoperators: ['igual', 'diferente', 'menor que', 'menor o igual', 'mayor que', 'mayor o igual', 'nulo', 'no nulo'],
        filterdatecomparisonoperators: ['igual', 'diferente', 'menor que', 'menor o igual', 'mayor que', 'mayor o igual', 'nulo', 'no nulo'],
        filterbooleancomparisonoperators: ['igual', 'diferente'],
    };

    // Configuración de jqxGrid
    $("#grid").jqxGrid({
        width: '100%',
        source: dataAdapter,
        columnsresize: true,
        pageable: true,
        autoheight: true,
        sortable: true,
        editable: true,
        altrows: true,
        enabletooltips: true,
        filterable: true,
        localization: localizationobj,
        columns: [
            { text: 'ID', datafield: 'id', width: 50, editable: false },
            { text: 'Título', datafield: 'title', width: 200, editable: true },
            { text: 'Precio', datafield: 'price', cellsformat: 'c2', width: 100, editable: true },
            { text: 'Descripción', datafield: 'description', width: 300, editable: false },
            { text: 'Categoría', datafield: 'category', width: 150, editable: false },
            { text: 'Imagen', datafield: 'image', cellsrenderer: function (row, column, value) {
                    return '<img src="' + value + '" width="50" height="50"/>';
                }, width: 100, editable: false
            },
            { text: 'Editar', datafield: 'edit', width: 100, cellsrenderer: function (row) {
                    var rowData = $("#grid").jqxGrid('getrowdata', row); // Obtener el ID de la primera columna
                    return '<button class="actualizar" onclick="editarProducto(' + rowData.id + ')">Editar</button>';
                },  editable: false
            },
            { text: 'Eliminar', datafield: 'delete', width: 100, cellsrenderer: function (row) {
                var rowData = $("#grid").jqxGrid('getrowdata', row); // Obtener el ID de la primera columna
                return '<button class="eliminar" onclick="eliminarProducto(' + rowData.id + ')">Eliminar</button>';
            },  editable: false
        }
        ]
    });
}

function editarProducto(id) {
    window.location.href = 'formulario.php?id=' + id;
}

function eliminarProducto(id) {
    $("#prompt").jqxWindow('open');
    $("#mensajeid").html(id);
}

function crearProducto(id) {
    window.location.href = 'formulario.php';
}

function deleteProductById(products, id) {
    // Encuentra el índice del producto con el ID dado
    const index = products.findIndex(product => product.id == id);
    
    // Si se encuentra el producto, elimínalo
    if (index !== -1) {
        products.splice(index, 1);
    }
    
    return products;
}

function StylebotonesJQW() {
    // Create jqxButton widgets.
    //$("#infoButton").jqxButton({ template: "info" });
    $("#confirmOKButton").jqxButton({ template: "success" });
    $("#agregar").jqxButton({ template: "success" });
    $("#cancelButton").jqxButton({ template: "danger" });
    $("#filtrar").jqxButton({ template: "inverse" });
}