<?php

session_start();

// Iniciando matriz principal.
if (!isset($_SESSION['productos'])) {
    $_SESSION['productos'] = [];
    
}

if (!isset($_SESSION['ventas'])) {
    $_SESSION['ventas'] = [];
    
}

// Variales Temporales para enviar a la interfaz.
$_SESSION['errores'] = [];
$_SESSION['mensaje'] = "";

// Definimos las categorías exactas que vamos a permitir.
$categorias_permitidas = ['Alimentos', 'Bebidas', 'Limpieza', 'Cuidado Personal', 'Mascotas', 'Hogar', 'Electrónica'];

// Solo ejecutamos si recibimos la petición por post.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    
    // Modo guardar (crear o editar):
   
    if ($accion === 'guardar') {
        
        $id = trim($_POST['id'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precio = trim($_POST['precio'] ?? '');
        $stock = trim($_POST['stock'] ?? '');
        $es_edicion = $_POST['es_edicion'] ?? '0';

        $errores_temp = [];

        // Para revisar que el usuario no pueda dejar ningún campo en blanco.
        if ($id === '' || $nombre === '' || $descripcion === '' || $categoria === '' || $precio === '' || $stock === '') {
            $errores_temp[] = "Completa todos los campos del formulario.";
        } else {
             
            if (!in_array($categoria, $categorias_permitidas)) {
                $errores_temp[] = "La categoría seleccionada no es válida.";
            }

            if (!is_numeric($precio) || !is_numeric($stock)) {
                $errores_temp[] = "El precio y el stock deben ser valores numéricos.";
            } else {
                
                if ($precio <= 0 || $precio > 100000) {
                    $errores_temp[] = "El precio debe ser como minimo $0.01 y no exceder los $100,000.";
                }
                if ($stock < 0 || $stock > 5000) {
                    $errores_temp[] = "El stock no puede ser negativo y el límite de bodega es de 5,000.";
                }
            }

            // Comprobar que el ID no se repetita (solo si el producto es nuevo)
            if ($es_edicion === '0') {
                foreach ($_SESSION['productos'] as $prod) {
                    if ($prod['id'] === $id) {
                        $errores_temp[] = "El ID ingresado ('$id') ya se encuentra registrado. Utiliza uno distinto.";
                        break;
                    }
                }
            }
        }

        // Si comete errores, se guardan en sesión y detenemos para que corrija sus datos.
        if (!empty($errores_temp)) {
            $_SESSION['errores'] = $errores_temp;
            header("Location: vista.php");
            exit;
        }

        // Ajustamos precios para realismo.
        $precio_realista = round((float)$precio, 2);
        $stock_entero = (int)$stock;

        if ($es_edicion === '1') {
            
            // Editar producto existente:
           
            foreach ($_SESSION['productos'] as $key => $prod) {
                if ($prod['id'] === $id) {
                    $_SESSION['productos'][$key] = [
                        'id' => $id,
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'categoria' => $categoria,
                        'precio' => $precio_realista,
                        'stock' => $stock_entero
                    ];
                    $_SESSION['mensaje'] = "Los datos del producto se actualizaron.";
                    break;
                }
            }
        } else {
            // Crear nuevo producto:
            // Nuevo registro al final de la matriz de productos.
            $_SESSION['productos'][] = [
                'id' => $id,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'categoria' => $categoria,
                'precio' => $precio_realista,
                'stock' => $stock_entero
            ];
            $_SESSION['mensaje'] = "El producto se ha registrado correctamente en el inventario.";
        }
    }

    // Modo eliminar producto:
  
    elseif ($accion === 'confirmarEliminar') {
        $_SESSION['idEliminar'] = $_POST['id'];
    }
    elseif ($accion === 'cancelarEliminar') {
        unset($_SESSION['idEliminar']);
    }
    elseif ($accion === 'eliminar') {
        $id_eliminar = $_POST['id'] ?? '';
        foreach ($_SESSION['productos'] as $key => $prod) {
            if ($prod['id'] === $id_eliminar) {
                // Eliminamos el producto de la matriz.
                unset($_SESSION['productos'][$key]);
                // Reordeno los índices del arreglo.
                $_SESSION['productos'] = array_values($_SESSION['productos']);
                $_SESSION['mensaje'] = "El producto fue eliminado.";
                break;
            }
        }
        unset($_SESSION['idEliminar']);
    }

    // Modo vender producto:

    elseif ($accion === 'vender') {

        $errores_temp = [];
        $id_vender = trim($_POST['id'] ??'');
        $cantidad = trim($_POST['cantidad'] ??'');

        // Validacion si los campos estan vacios
        if ($id_vender === '' || $cantidad === '') {
            $errores_temp[] = "Debe seleccionar un producto y seleccionar la cantidad a comprar";
        }

        // Validacion solo para datos numericos
        if (!is_numeric($cantidad) || (int) $cantidad <= 0) {
            $errores_temp[] = "La cantidad debe ser un número mayor que 0.";
        }

        $cantidad = (int) $cantidad;
        $producto_existente = false;

        foreach ($_SESSION['productos'] as $key => $prod) {

            // Corroboración de la existencia del producto
            if ($prod['id'] === $id_vender) {
                $producto_existente = true;

                // Validacion para la cantidad de productos a vender
                if ($cantidad > $prod['stock']) {
                        $errores_temp[] = "La cantidad solicitada sobrepasa el inventario";
                } else {
                    // Resta de la cantidad de productos almacenados en el Stock
                    $_SESSION['productos'][$key]['stock'] -= $cantidad;

                    // En caso de no existir ventas, se registran nuevas en el arreglo
                    if (!isset($_SESSION['ventas'])) {
                        $_SESSION['ventas'] = [];
                    }

                    $_SESSION['ventas'][] = [
                        'id' => $prod['id'],
                        'nombre' => $prod['nombre'],
                        'cantidad' => $cantidad,
                        'precio_unitario' => $prod['precio'],
                        'total' => $cantidad * $prod['precio'],
                        'fecha' => date("Y-m-d H:i:s")
                    ];

                    $_SESSION['mensaje'] = "La venta se registró correctamente.";
                }
                break;
            }
        }

        // Validacion para corroborar que el producto este registrado
        if (!$producto_existente) {
            $errores_temp[] = "El producto no existe.";
        }

        if (!empty($errores_temp)) {
            $_SESSION['errores'] = $errores_temp;
        }
    }

    // Al final redirigimos a la vista.
    header("Location: vista.php");
    exit;
}
?>