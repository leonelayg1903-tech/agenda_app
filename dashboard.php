<?php
session_start();
require_once 'conexion.php';

// 1. Restricción de acceso: Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = "";

// Obtener categorías disponibles para el formulario
$stmtCategorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY id ASC");
$categorias = $stmtCategorias->fetchAll();
$idsCategoriasValidas = array_column($categorias, 'id');

// 2. Procesar el formulario para agregar un nuevo contacto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $ciudad = trim($_POST['ciudad']);
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 5;
    $favorito = isset($_POST['favorito']) ? 1 : 0;

    if (!in_array($categoria_id, $idsCategoriasValidas)) {
        $categoria_id = 5;
    }

    if (!empty($nombre) && !empty($telefono) && !empty($correo) && !empty($ciudad)) {
        $stmt = $pdo->prepare("INSERT INTO contactos (id_usuario, nombre, telefono, correo, ciudad, categoria_id, favorito) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $nombre, $telefono, $correo, $ciudad, $categoria_id, $favorito])) {
            $mensaje = "Contacto agregado exitosamente.";
        } else {
            $mensaje = "Error al agregar el contacto.";
        }
    } else {
        $mensaje = "Por favor, completa todos los campos del contacto.";
    }
}

// 3. Procesar la eliminación de un contacto (con validación de seguridad IDOR)
if (isset($_GET['eliminar'])) {
    $contacto_id = (int)$_GET['eliminar'];
    // Verificar que el contacto pertenezca al usuario actual antes de borrarlo
    $stmtDel = $pdo->prepare("DELETE FROM contactos WHERE id = ? AND id_usuario = ?");
    $stmtDel->execute([$contacto_id, $user_id]);
    header("Location: dashboard.php");
    exit();
}

// 4. Obtener la lista exclusiva de contactos del usuario autenticado (con filtro opcional de favoritos)
$soloFavoritos = isset($_GET['favoritos']) && $_GET['favoritos'] == '1';

if ($soloFavoritos) {
    $stmtContactos = $pdo->prepare("
        SELECT c.*, cat.nombre AS categoria_nombre
        FROM contactos c
        LEFT JOIN categorias cat ON c.categoria_id = cat.id
        WHERE c.id_usuario = ? AND c.favorito = 1
        ORDER BY c.id DESC
    ");
    $stmtContactos->execute([$user_id]);
} else {
    $stmtContactos = $pdo->prepare("
        SELECT c.*, cat.nombre AS categoria_nombre
        FROM contactos c
        LEFT JOIN categorias cat ON c.categoria_id = cat.id
        WHERE c.id_usuario = ?
        ORDER BY c.id DESC
    ");
    $stmtContactos->execute([$user_id]);
}
$contactos = $stmtContactos->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Agenda de Contactos</title>
    <style>
        :root {
            --bg: #e6ead9;
            --surface: #ffffff;
            --text: #0f172a;
            --muted: #57604a;
            --border: #cbd4b8;
            --primary: #556b2f;
            --primary-hover: #3f5221;
            --danger: #dc2626;
            --danger-hover: #b91c1c;
            --warning: #f59e0b;
            --warning-hover: #d97706;
            --success-bg: #dcfce7;
            --success-text: #166534;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 24px;
        }
        .page {
            max-width: 1240px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: var(--surface);
            padding: 18px 24px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }
        .header h2 {
            margin: 0 0 4px 0;
            font-size: 20px;
        }
        .header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }
        .logout {
            display: inline-block;
            background: var(--danger);
            color: #ffffff;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .logout:hover {
            background: var(--danger-hover);
        }
        .msg {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
            margin: 16px 0 0 0;
        }
        .container {
            display: grid;
            grid-template-columns: 310px 1fr;
            gap: 20px;
            margin-top: 20px;
            align-items: start;
        }
        .card {
            background: var(--surface);
            padding: 22px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }
        .card h3 {
            margin: 0 0 16px 0;
            font-size: 17px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 4px;
        }
        input[type="text"], input[type="email"], select {
            width: 100%;
            padding: 9px 11px;
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }
        input[type="text"]:focus, input[type="email"]:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(85, 107, 47, 0.18);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        .checkbox-label input {
            width: auto;
            margin: 0;
        }
        .btn-primary {
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 11px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .filtros {
            display: flex;
            gap: 8px;
        }
        .filtro-btn {
            padding: 7px 14px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            background: #f4f6ee;
            color: #334155;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }
        .filtro-btn.activo {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }
        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 9px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
        }
        th {
            background-color: #f8faf5;
            color: #475569;
            font-weight: 600;
            font-size: 13px;
        }
        tr:hover td {
            background-color: #f9fbf5;
        }
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: #e3ecd4;
            color: #36471c;
            white-space: nowrap;
        }
        .fav-si {
            color: #b45309;
            font-weight: 700;
            white-space: nowrap;
        }
        .fav-no {
            color: var(--muted);
        }
        .acciones {
            display: flex;
            gap: 6px;
            flex-wrap: nowrap;
        }
        .btn-edit {
            background: var(--warning);
            padding: 5px 10px;
            color: #111827;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-edit:hover {
            background: var(--warning-hover);
        }
        .btn-danger {
            background: var(--danger);
            padding: 5px 10px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-danger:hover {
            background: var(--danger-hover);
        }
        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; }
            .header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                <p><?php echo htmlspecialchars($_SESSION['user_correo']); ?></p>
            </div>
            <a href="logout.php" class="logout">Cerrar Sesión</a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <p class="msg"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <div class="container">
            <!-- Formulario para Registrar Contacto -->
            <div class="card">
                <h3>Agregar Contacto</h3>
                <form action="dashboard.php" method="POST">
                    <input type="hidden" name="accion" value="agregar">

                    <label>Nombre:</label>
                    <input type="text" name="nombre" required>

                    <label>Teléfono:</label>
                    <input type="text" name="telefono" required>

                    <label>Correo:</label>
                    <input type="email" name="correo" required>

                    <label>Ciudad:</label>
                    <input type="text" name="ciudad" required>

                    <label>Categoría:</label>
                    <select name="categoria_id" required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == 5) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label class="checkbox-label">
                        <input type="checkbox" name="favorito" value="1">
                        <span>Marcar como favorito</span>
                    </label>

                    <button type="submit" class="btn-primary">Guardar Contacto</button>
                </form>
            </div>

            <!-- Tabla de Listado de Contactos -->
            <div class="card">
                <div class="toolbar">
                    <h3 style="margin: 0;"><?php echo $soloFavoritos ? 'Mis Contactos Favoritos' : 'Mis Contactos'; ?></h3>
                    <div class="filtros">
                        <a href="dashboard.php" class="filtro-btn <?php echo !$soloFavoritos ? 'activo' : ''; ?>">Todos</a>
                        <a href="dashboard.php?favoritos=1" class="filtro-btn <?php echo $soloFavoritos ? 'activo' : ''; ?>">Solo Favoritos</a>
                    </div>
                </div>

                <?php if (count($contactos) > 0): ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Ciudad</th>
                                    <th>Categoría</th>
                                    <th>Favorito</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contactos as $c): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($c['correo']); ?></td>
                                        <td><?php echo htmlspecialchars($c['ciudad']); ?></td>
                                        <td><span class="badge"><?php echo htmlspecialchars($c['categoria_nombre'] ?? 'Otro'); ?></span></td>
                                        <td>
                                            <?php if ((int)$c['favorito'] === 1): ?>
                                                <span class="fav-si">★ Sí</span>
                                            <?php else: ?>
                                                <span class="fav-no">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="acciones">
                                                <a href="editar.php?id=<?php echo $c['id']; ?>" class="btn-edit">Editar</a>
                                                <a href="dashboard.php?eliminar=<?php echo $c['id']; ?>" class="btn-danger" onclick="return confirm('¿Estás seguro de eliminar este contacto?');">Eliminar</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: var(--muted);"><?php echo $soloFavoritos ? 'No tienes contactos marcados como favoritos.' : 'No tienes contactos registrados todavía.'; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>