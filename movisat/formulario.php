<?php
error_reporting(0);

// Variables para almacenar los valores por defecto o del producto existente
$id = null;
$producto = [
    'title' => '',
    'price' => '',
    'description' => '',
    'category' => '',
    'image' => ''
];

// Obtener las categorías desde la API
$categoriasUrl = "https://fakestoreapi.com/products/categories";
$categoriasAPI = json_decode(file_get_contents($categoriasUrl));

// Verificar si se ha recibido el parámetro 'id' en la URL
if (isset($_GET['id'])) {
    // Obtener el ID del producto de la URL y asegurarse de que sea un número entero válido
    $id = intval($_GET['id']);

    // Función para obtener los detalles del producto desde la API
    function getProducto($id) {
        $apiUrl = "https://fakestoreapi.com/products/$id";
        $product = json_decode(file_get_contents($apiUrl), true);
        return $product;
    }

    // Obtener los detalles del producto usando la función
    $producto = getProducto($id);

    // Verificar si el producto existe
    if (!$producto) {
        // Redirigir o mostrar un mensaje de error si el producto no se encuentra
        echo "Error: Producto no encontrado.";
        exit;
    }
}

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    // Validar y obtener los datos del formulario
    $data = [
        'title' => $_POST['title'],
        'price' => $_POST['price'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'image' => $_POST['image']
    ];

    // Hacer la solicitud POST o PUT a la API dependiendo de si es una creación o edición
    if ($id) {
        // Es una edición, usar PUT
        $ch = curl_init("https://fakestoreapi.com/products/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        // Verificar la respuesta de la API y mostrar un mensaje adecuado
        if ($response) {
            $mensaje = "Producto actualizado exitosamente.";
            $producto = json_decode($response); // Set true to convert to associative array
        } else {
            $mensaje = "Error al actualizar el producto.";
        }
    } else {
        $id = "";
        // Es una creación, usar POST
        $ch = curl_init("https://fakestoreapi.com/products");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        // Verificar la respuesta de la API y mostrar un mensaje adecuado
        if ($response) {
            $mensaje = "Producto creado exitosamente.";
        } else {
            $mensaje = "Error al crear el producto.";
        }
    }
    //print_r($producto);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Editar Producto' : 'Crear Producto'; ?></title>
    <!-- Css propios -->
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <!-- Incluir jqwidgets CSS -->
    <link rel="stylesheet" href="https://jqwidgets.com/public/jqwidgets/styles/jqx.base.css" type="text/css" />
    <!-- Incluir jqwidgets JavaScript -->
    <script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqx-all.js"></script>
</head>
<body class="center">
    <di style="text-align: center;">
        <div id="messageNotification">
            <div>
                <?php echo $mensaje; ?>
            </div>
        </div>
        <h1><?php echo $id ? 'Editar Producto' : 'Crear Producto'; ?></h1>

        <div id='form' style="width: 400px; height: auto;"></div> 
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#messageNotification").jqxNotification({
                width: 250, position: "top-right", opacity: 0.9,
                autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
            });
            
            var mensaje = '<?php echo $mensaje; ?>';

            if(mensaje){
                console.log(mensaje,`<?php echo $response; ?>`);
                $("#messageNotification").jqxNotification("open");
            };

            // Cargar las categorías desde PHP
            var categorias = <?php echo json_encode($categoriasAPI); ?>;

            // Insertar un objeto vacio al principio del array de categorías
            categorias.unshift("");

            var template = [
                {
                    bind: 'id',
                    type: 'text',
                    label: 'id',
                    editable: false, 
                    required: true,
                    labelWidth: '80px',
                    width: '250px',
                    info: 'id del producto',
                    infoPosition: 'right'
                },
                {
                    bind: 'title',
                    type: 'text',
                    label: 'titulo',
                    required: true,
                    labelWidth: '80px',
                    width: '250px'
                },
                {
                    bind: 'price',
                    type: 'number',
                    label: 'Precio',
                    required: true,
                    labelWidth: '80px',
                    width: '250px'
                },  
                {
                    bind: 'description',
                    type: 'text',
                    label: 'description',
                    required: false,
                    labelWidth: '80px',
                    width: '250px'
                },
                {
                    bind: 'category',
                    type: 'option',
                    label: 'Categoria',
                    required: true,
                    labelWidth: '80px',
                    width: '250px',
                    component: 'jqxDropDownList',
                    info: 'Seleciona una categoria',
                    infoPosition: 'right',
                    options: categorias
                },
                {
                    bind: 'image',
                    type: 'text',
                    label: 'Url Imagen',
                    required: true,
                    labelWidth: '80px',
                    width: '250px'
                },
                {
                    type: 'blank',
                    rowHeight: '10px'
                },
                {
                    columns: [
                        {
                            id: 'submitButton',
                            name: 'submitButton',
                            type: 'button',
                            text: "<?php echo $id ? 'Editar' : 'Crear'; ?>",
                            width: '90px',
                            height: '30px',
                            rowHeight: '40px',
                            columnWidth: '50%',
                            align: 'right',onclick: function () {alert("asd");}
                        },
                        {
                            id: 'cancelButton',
                            name: 'cancelButton',
                            type: 'button',
                            text: 'Cancel',
                            width: '90px',
                            height: '30px',
                            rowHeight: '40px',
                            columnWidth: '50%'
                        }                
                    ]
                }
            ]; 
            
            var id = "<?php echo $id; ?>";
            var method;

            //elimino el primer campo si no se va a editar
            id ? method = "POST" : template.shift(); method = "PUT"; 

            //obtengo los valores del producto
            var productoData = <?php echo json_encode($producto); ?>;

            var form = $('#form');
            form.jqxForm({
                template: template,
                value: productoData,
                padding: { left: 10, top: 10, right: 10, bottom: 10 }
            });

            var submit = form.jqxForm('getComponentByName', 'submitButton');
            submit.on('click', function () {
                // function: submit
                form.jqxForm('submit', "formulario.php", "", method);
            });
            var cancel = form.jqxForm('getComponentByName', 'cancelButton');
            cancel.on('click', function () {
                // function: submit
                form.jqxForm('submit', "https://luisgomezarango.com/movisat", "", '');
            });


            
        });
    </script>
</body>
</html>