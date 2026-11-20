<?php
require_once 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    // Validar que los campos no estén vacíos
    if (!empty($nombre) && !empty($correo) && !empty($password)) {

        // 1. Validar que el correo no exista previamente
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);

        if ($stmt->rowCount() > 0) {
            $mensaje = "El correo electrónico ya está registrado.";
        } else {
            // 2. Cifrar la contraseña con password_hash
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // 3. Insertar el nuevo usuario en la base de datos
            $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)");
            if ($stmtInsert->execute([$nombre, $correo, $passwordHash])) {
                $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión.";
            } else {
                $mensaje = "Error al registrar el usuario.";
            }
        }
    } else {
        $mensaje = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
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
            --success-bg: #dcfce7;
            --success-text: #166534;
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
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            width: 100%;
            max-width: 380px;
        }
        h2 {
            text-align: center;
            margin: 0 0 18px 0;
            font-size: 22px;
            color: var(--text);
        }
        label {
            display: block;
            margin-top: 14px;
            font-weight: 600;
            font-size: 13px;
            color: #334155;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(85, 107, 47, 0.18);
        }
        button {
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 11px;
            margin-top: 22px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: var(--primary-hover);
        }
        .msg {
            text-align: center;
            margin-top: 14px;
            padding: 10px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger);
            font-weight: 600;
            font-size: 13px;
        }
        .msg.success {
            background: var(--success-bg);
            border: 1px solid #bbf7d0;
            color: var(--success-text);
        }
        p {
            text-align: center;
            margin: 18px 0 0 0;
            font-size: 14px;
            color: var(--muted);
        }
        a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
            color: var(--primary-hover);
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Registro</h2>
        <?php if (!empty($mensaje)): ?>
            <div class="msg <?php echo strpos($mensaje, 'exitoso') !== false ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form action="registro.php" method="POST">
            <label>Nombre Completo:</label>
            <input type="text" name="nombre" required>

            <label>Correo Electrónico:</label>
            <input type="email" name="correo" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</body>
</html>