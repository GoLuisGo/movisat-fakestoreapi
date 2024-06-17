<?php
// Iniciar sesión antes de cualquier salida
session_start();

// Función para obtener productos por categoría
function getProductosCategoria($categoria) {
    $apiUrl = "https://fakestoreapi.com/products/category/$categoria";
    $products = json_decode(file_get_contents($apiUrl), true);
    return $products;
}

// Obtener las categorías desde la API
$categoriasUrl = "https://fakestoreapi.com/products/categories";
$categoriasAPI = json_decode(file_get_contents($categoriasUrl));

// Guardar la selección de categoría selecionada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['categoriaSeleccionada'])) {
      $_SESSION['categoria_selecionada'] = $_POST['categoriaSeleccionada'];
  }
}

// Obtener la categoría seleccionada de sesion
$categoria_selecionada = isset($_SESSION['categoria_selecionada']) ? $_SESSION['categoria_selecionada'] : '';

// Si se ha seleccionado una categoría, obtener los productos
if (!empty($categoria_selecionada)) {
    $productosAPI = getProductosCategoria($categoria_selecionada);
} else {
    // Obtener todos los productos si no se ha seleccionado una categoría
    $apiUrl = "https://fakestoreapi.com/products";
    $productos = file_get_contents($apiUrl);
    $productosAPI = json_decode($productos, true);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Productos</title>
    <!-- Css propios -->
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <!-- Incluir jqwidgets CSS -->
    <link rel="stylesheet" href="https://jqwidgets.com/public/jqwidgets/styles/jqx.base.css" type="text/css" />
    <!-- Incluir jqwidgets JavaScript -->
    <script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqx-all.js"></script>
</head>
<body>
    <div id="prompt" style="display: none;">
        <div>¿Esta Seguro de eliminar este producto?</div>
        <div style="margin-top: 10px; text-align: center;">
            <h3>producto con id: <span id="mensajeid"></span></h3>
            <br>
            <button id="confirmOKButton">Aceptar</button>
            <button id="cancelButton">Cancelar</button>
        </div>
    </div>
    <div id="messageNotification">
        <div id="notificacion">
        </div>
    </div>
    <h1>Listado de Productos</h1>
    <!-- Formulario con jqxComboBox y campo oculto -->
    <form id="myForm" method="post">
        <div id="combobox"></div>
        <input type="hidden" id="categoriaSeleccionada" name="categoriaSeleccionada" value="">
        <button type="submit" id="filtrar" class="btn-filtro" >Filtrar</button>
        <button type="button" id="agregar" class="btn-filtro" onclick="crearProducto();" >Agregar</button>

        <br>
    </form>
    <div id="grid"></div>

    <script type="text/javascript" src="js/funciones.js" defer></script>
    <script type="text/javascript" defer>
        $(document).ready(function () {

            $("#messageNotification").jqxNotification({
                width: 250, position: "top-right", opacity: 0.9,
                autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
            });

            // Datos obtenidos desde PHP
            var productosAPI = <?php echo json_encode($productosAPI); ?>;

            // Inicializar el jqxGrid con los productos actuales
            actualizarGRID(productosAPI);

            // Evento para guardar los datos que se editar una celda
            $('#grid').on('cellendedit', function (event) {
                    var args = event.args;
                    var campo = args.datafield;
                    var oldValue = args.oldvalue;
                    var newValue = args.value;
                    var productId = args.row.id;

                    // Si el valor ha cambiado, mostrar el alert
                    if (oldValue !== newValue) {
                        alert('el campo "' + campo + '" con id ['+productId+'] ha cambiado de "' + oldValue + '" a "' + newValue + '".');
                    
                        // Hacer la llamada PUT a la API para actualizar el producto
                        var updateData = {};
                        updateData[campo] = newValue;

                        fetch('https://fakestoreapi.com/products/' + productId, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(updateData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            $("#notificacion").html(campo+' actualizado:');
                            $("#messageNotification").jqxNotification("open");
                            console.log('Producto actualizado:', data);
                        })
                        .catch(error => {
                            $("#notificacion").html('Error al actualizar el '+campo);    
                            $("#messageNotification").jqxNotification("open");
                            console.log('Error al actualizar el producto:', error);
                        });
                    }else{
                        alert('el campo "' + campo + '" con id ['+productId+'] No cambio');
                    }
                });

            // Cargar las categorías desde PHP
            var categorias = <?php echo json_encode($categoriasAPI); ?>;

            // Insertar un objeto al principio del array de categorías
            categorias.unshift("Todas las categorias");

            // Inicializar jqxComboBox
            $("#combobox").jqxComboBox({
                placeHolder: "Filtrar por categoria",
                source: categorias,
                width: 300,
                autoComplete: true,
                autoDropDownHeight: true
            });

            // Manejar el evento de cambio de selección usando addEventHandler
            $("#combobox").on('change', function (event) {
                var args = event.args;
                if (args) {
                    var index = args.index; // índice del elemento seleccionado
                    var item = args.item;   // objeto del elemento seleccionado
                    if (index > 0 && item) {
                      var value = item.value;  // valor del elemento seleccionado
                      $("#categoriaSeleccionada").val(value); // actualizar el campo oculto con el valor seleccionado
                    }else{
                      $("#categoriaSeleccionada").val("");
                    }
                }
            });

            // Configurar la opción inicial personalizada como seleccionada
            $("#combobox").val("<?php echo $categoria_selecionada; ?>");

            // Actualizar el v0alor del campo oculto con la categoría seleccionada por defecto
            $("#categoriaSeleccionada").val($("#combobox").val());

           // Inicializar la ventana jqxWindow
           $("#prompt").jqxWindow({
                width: 300,
                height: 150,
                resizable: false,
                draggable: false,
                isModal: true,
                modalOpacity: 0.7,
                autoOpen: false
            });

            // Manejar el evento de clic del botón Aceptar
            $("#confirmOKButton").click(function () {
                var productId = $("#mensajeid").html();
                fetch('https://fakestoreapi.com/products/' + productId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    var productos = deleteProductById(productosAPI, productId);
                   
                    $("#notificacion").html('Producto '+productId+' Eliminado:');
                    $("#messageNotification").jqxNotification("open");
                    console.log('Producto Eliminado '+productId);
                     //actualizar lista
                    actualizarGRID(productos);
                })
                .catch(error => {
                    $("#notificacion").html('Error al Eliminar el producto '+productId);    
                    $("#messageNotification").jqxNotification("open");
                    console.log('Error al Eliminar el producto:', error);
                });
                $("#prompt").jqxWindow('close');
            });

             // Manejar el evento de clic del botón Aceptar
             $("#cancelButton").click(function () {
                $("#prompt").jqxWindow('close');
            });
            

            // Create jqxButton widgets.
            StylebotonesJQW(); 
        });
    </script>
</body>
</html>


