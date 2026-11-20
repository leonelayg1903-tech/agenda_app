<?php
session_start();
require_once 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    if (!empty($correo) && !empty($password)) {
        // Buscar al usuario por su correo
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        // Verificar si el usuario existe y si la contraseña coincide
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Crear variables de sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nombre'];
            $_SESSION['user_correo'] = $usuario['correo'];

            // Redirigir al panel principal
            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "Correo o contraseña incorrectos.";
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
    <title>Iniciar Sesión</title>
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
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
        }
        input[type="email"]:focus, input[type="password"]:focus {
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
        <h2>Iniciar Sesión</h2>
        <?php if (!empty($mensaje)): ?>
            <div class="msg"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label>Correo Electrónico:</label>
            <input type="email" name="correo" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Ingresar</button>
        </form>
        <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
</body>
</html>