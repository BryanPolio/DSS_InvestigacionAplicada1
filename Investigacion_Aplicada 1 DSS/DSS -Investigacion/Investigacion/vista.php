<?php
session_start();

$productos = $_SESSION['productos'] ?? [];
$ventas = $_SESSION['ventas'] ?? [];
$errores = $_SESSION['errores'] ?? [];
$mensaje = $_SESSION['mensaje'] ?? "";
unset($_SESSION['errores'], $_SESSION['mensaje']);

$producto_editar = null;
if (isset($_GET['editar'])) {
    foreach ($productos as $prod) {
        if ($prod['id'] === $_GET['editar']) {
            $producto_editar = $prod;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - UDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6366f1;
            --success-color: #10b981;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        .navbar-custom {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .header-title {
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--primary-color);
        }

        /* Tarjetas Estilo Moderno */
        .custom-card {
            background: var(--card-bg);
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease;
        }

        .custom-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .card-label {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .card-label i { margin-right: 8px; color: var(--secondary-color); }

        /* Estilo de Inputs */
        .form-control, .form-select {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            background-color: #fdfdfd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            border-color: var(--primary-color);
        }

        /* Botones */
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.75rem;
            padding: 0.8rem;
            font-weight: 700;
        }

        .btn-success {
            background-color: var(--success-color);
            border: none;
            border-radius: 0.75rem;
            padding: 0.8rem;
            font-weight: 700;
        }

        /* Tablas */
        .table-container {
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table thead {
            background-color: #f1f5f9;
        }

        .table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #475569;
            padding: 1rem;
        }

        .badge-stock {
            padding: 0.5em 0.8em;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar-custom">
    <div class="container d-flex justify-content-between align-items-center">
        <h2 class="header-title m-0"><i class="fas fa-layer-group me-2"></i>Investigación Aplicada 1</h2>
        <span class="badge bg-soft-primary text-primary fw-bold">DSS G01T - UDB</span>
    </div>
</nav>

<div class="container">

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <h6 class="fw-bold"><i class="fas fa-exclamation-circle me-2"></i> Errores encontrados:</h6>
            <ul class="mb-0 small">
                <?php foreach ($errores as $error) echo "<li>$error</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center">
            <i class="fas fa-check-circle me-3 fa-lg"></i>
            <div><?php echo $mensaje; ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['idEliminar'])): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center">
                <span><i class="fas fa-question-circle me-2"></i> ¿Confirmas que deseas eliminar este producto?</span>
                <div>
                    <form action="backend.php" method="POST" class="d-inline">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $_SESSION['idEliminar']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm px-3">Sí, eliminar</button>
                    </form>
                    <form action="backend.php" method="POST" class="d-inline">
                        <input type="hidden" name="accion" value="cancelarEliminar">
                        <button type="submit" class="btn btn-light btn-sm px-3 border">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="custom-card">
                <h5 class="fw-800 mb-4 text-dark">
                    <i class="fas <?php echo $producto_editar ? 'fa-edit' : 'fa-plus-circle'; ?> me-2 text-primary"></i>
                    <?php echo $producto_editar ? 'Editar' : 'Nuevo'; ?> Producto
                </h5>
                
                <form action="backend.php" method="POST">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="es_edicion" value="<?php echo $producto_editar ? '1' : '0'; ?>">

                    <div class="mb-3">
                        <label class="card-label"><i class="fas fa-fingerprint"></i> ID Producto</label>
                        <input type="text" name="id" class="form-control" value="<?php echo $producto_editar['id'] ?? ''; ?>" <?php echo $producto_editar ? 'readonly' : ''; ?> placeholder="Ej: PROD-01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="card-label"><i class="fas fa-tag"></i> Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo $producto_editar['nombre'] ?? ''; ?>" placeholder="Nombre del artículo">
                    </div>

                    <div class="mb-3">
                        <label class="card-label"><i class="fas fa-align-left"></i> Descripción</label>
                        <input type="text" name="descripcion" class="form-control" value="<?php echo $producto_editar['descripcion'] ?? ''; ?>" placeholder="Detalles breves">
                    </div>
                    
                    <div class="mb-3">
                        <label class="card-label"><i class="fas fa-list"></i> Categoría</label>
                        <select name="categoria" class="form-select">
                            <option value="">-- Seleccione --</option>
                            <?php 
                            $categorias = ['Alimentos', 'Bebidas', 'Limpieza', 'Cuidado Personal', 'Mascotas', 'Hogar', 'Electrónica'];
                            foreach ($categorias as $cat) {
                                $selected = (isset($producto_editar['categoria']) && $producto_editar['categoria'] === $cat) ? 'selected' : '';
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="card-label"><i class="fas fa-dollar-sign"></i> Precio</label>
                            <input type="text" name="precio" class="form-control" value="<?php echo $producto_editar['precio'] ?? ''; ?>" placeholder="0.00">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="card-label"><i class="fas fa-boxes"></i> Stock</label>
                            <input type="text" name="stock" class="form-control" value="<?php echo $producto_editar['stock'] ?? ''; ?>" placeholder="Cant.">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 shadow-sm mt-3">
                        <i class="fas fa-save me-2"></i> Guardar Producto
                    </button>
                    
                    <?php if ($producto_editar): ?>
                        <a href="vista.php" class="btn btn-light w-100 mt-2 border text-muted">Cancelar edición</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="custom-card border-start border-success border-4">
                <h5 class="fw-800 mb-4 text-dark"><i class="fas fa-shopping-cart me-2 text-success"></i>Registrar Venta</h5>
                <form action="backend.php" method="POST">
                    <input type="hidden" name="accion" value="vender">
                    <div class="mb-3 text-start">
                        <label class="card-label">Seleccionar Producto</label>
                        <select name="id" class="form-select" required>
                            <option value="">-- Buscar producto --</option>
                            <?php foreach ($productos as $p): ?>
                                <?php if ($p['stock'] > 0): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?> (Disp: <?php echo $p['stock']; ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="card-label">Cantidad a Vender</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required placeholder="0">
                    </div>
                    <button type="submit" class="btn btn-success w-100 shadow-sm">
                        Confirmar Venta <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="custom-card">
                <h5 class="fw-800 mb-4 text-dark"><i class="fas fa-table me-2 text-primary"></i>Inventario Actual</h5>
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle m-0 text-start">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No hay productos registrados aún.</td></tr>
                            <?php else: ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($prod['nombre']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($prod['id']); ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($prod['categoria']); ?></span></td>
                                        <td class="fw-bold text-dark">$<?php echo number_format($prod['precio'], 2); ?></td>
                                        <td>
                                            <?php 
                                                $stockClass = ($prod['stock'] <= 5) ? 'bg-danger' : 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $stockClass; ?> badge-stock"><?php echo $prod['stock']; ?> unidades</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                <a href="vista.php?editar=<?php echo $prod['id']; ?>" class="btn btn-sm btn-warning border-0 px-3"><i class="fas fa-edit"></i></a>
                                                <form action="backend.php" method="POST" class="d-inline m-0">
                                                    <input type="hidden" name="accion" value="confirmarEliminar">
                                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger border-0 px-3"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="custom-card mt-4">
                <h5 class="fw-800 mb-4 text-dark"><i class="fas fa-history me-2 text-indigo"></i>Historial de Ventas</h5>
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle m-0 text-start">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Total</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ventas)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted small italic">No hay ventas registradas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ventas as $venta): ?>
                                    <tr>
                                        <td class="text-muted small">#<?php echo $venta['id']; ?></td>
                                        <td class="fw-bold"><?php echo $venta['nombre']; ?></td>
                                        <td><?php echo $venta['cantidad']; ?></td>
                                        <td class="text-success fw-800">$<?php echo number_format($venta['total'], 2); ?></td>
                                        <td class="small text-muted"><?php echo $venta['fecha']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center text-muted mt-5 mb-4 small">
    &copy; 2026 Universidad Don Bosco - Facultad de Ingeniería
</footer>

</body>
</html>