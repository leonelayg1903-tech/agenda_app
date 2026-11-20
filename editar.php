<?php
session_start();
require_once 'conexion.php';

// 1. Verificar sesión activa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = "";

// 2. Validar que venga un ID por la URL o por POST
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header("Location: dashboard.php");
    exit();
}

$contacto_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['id'];

// Obtener categorías disponibles para el selector
$stmtCategorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY id ASC");
$categorias = $stmtCategorias->fetchAll();
$idsCategoriasValidas = array_column($categorias, 'id');

// 3. Procesar el formulario cuando se guardan los cambios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        // Seguridad IDOR: Actualizar SOLO si el contacto pertenece al usuario logueado
        $stmtUpdate = $pdo->prepare("
            UPDATE contactos
            SET nombre = ?, telefono = ?, correo = ?, ciudad = ?, categoria_id = ?, favorito = ?
            WHERE id = ? AND id_usuario = ?
        ");
        $stmtUpdate->execute([$nombre, $telefono, $correo, $ciudad, $categoria_id, $favorito, $contacto_id, $user_id]);

        header("Location: dashboard.php");
        exit();
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}

// 4. Obtener los datos actuales del contacto (verificando propiedad)
$stmt = $pdo->prepare("SELECT * FROM contactos WHERE id = ? AND id_usuario = ?");
$stmt->execute([$contacto_id, $user_id]);
$contacto = $stmt->fetch();

// Si el contacto no existe o pertenece a otro usuario, expulsar al dashboard
if (!$contacto) {
    header("Location: dashboard.php");
    exit();
}

$categoriaActual = isset($contacto['categoria_id']) ? (int)$contacto['categoria_id'] : 5;
$esFavoritoActual = isset($contacto['favorito']) && (int)$contacto['favorito'] === 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Contacto</title>
    <style>
        :root {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --success: #16a34a;
            --success-hover: #15803d;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: var(--surface);
            padding: 28px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            width: 100%;
            max-width: 390px;
        }
        h2 {
            text-align: center;
            margin: 0 0 18px 0;
            font-size: 20px;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            font-size: 13px;
            color: #334155;
        }
        input[type="text"], input[type="email"], select {
            width: 100%;
            padding: 9px 11px;
            margin-top: 5px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
        }
        input[type="text"]:focus, input[type="email"]:focus, select:focus {
            outline: none;
            border-color: var(--success);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            font-weight: 500;
            cursor: pointer;
        }
        .checkbox-label input {
            width: auto;
            margin: 0;
        }
        button {
            width: 100%;
            background: var(--success);
            color: #fff;
            border: none;
            padding: 11px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: var(--success-hover);
        }
        .cancel {
            display: block;
            text-align: center;
            margin-top: 14px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
        }
        .cancel:hover {
            color: var(--text);
        }
        .msg {
            color: var(--danger);
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Editar Contacto</h2>
        <?php if (!empty($mensaje)): ?>
            <p class="msg"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <form action="editar.php" method="POST">
            <input type="hidden" name="id" value="<?php echo (int)$contacto['id']; ?>">

            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($contacto['nombre']); ?>" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($contacto['telefono']); ?>" required>

            <label>Correo:</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($contacto['correo']); ?>" required>

            <label>Ciudad:</label>
            <input type="text" name="ciudad" value="<?php echo htmlspecialchars($contacto['ciudad']); ?>" required>

            <label>Categoría:</label>
            <select name="categoria_id" required>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)$cat['id'] === $categoriaActual) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="checkbox-label">
                <input type="checkbox" name="favorito" value="1" <?php echo $esFavoritoActual ? 'checked' : ''; ?>>
                <span>Marcar como favorito</span>
            </label>

            <button type="submit">Actualizar Contacto</button>
        </form>
        <a href="dashboard.php" class="cancel">Cancelar y volver</a>
    </div>
</body>
</html>