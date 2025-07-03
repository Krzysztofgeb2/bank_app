<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (isset($_SESSION["user"])) {
    // już zalogowany
    header("Location: dashboard.php");
    exit();
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputUsername = $_POST["username"] ?? "";
    $inputPassword = $_POST["password"] ?? "";

    $connect = mysqli_connect("localhost", "root", "", "bank_app");

    if (!$connect) {
        die("Błąd połączenia z bazą: " . mysqli_connect_error());
    }

    $stmt = $connect->prepare("SELECT id, username, password_hash, role_id, is_verified FROM users WHERE username = ?");
    $stmt->bind_param("s", $inputUsername);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($user = $result->fetch_assoc()) {
        if(!$user["is_verified"]) {
        $error = "Konto nieakwtywne sprawdz email [kliknij tutaj}(resend_activation.php)";
        }else if (password_verify($inputPassword, $user["password_hash"])) {

            // Zapisz dane do sesji
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role_id"] = $user["role_id"];

            // Przekierowanie wg roli
            switch ($user["role_id"]) {
                case 3:
                    header("Location: admin_dashboard.php");
                    break;
                case 2:
                    header("Location: employee_dashboard.php");
                    break;
                default:
                    header("Location: user_dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Niepoprawne hasło.";
        }
    } else {
        $error = "Użytkownik nie istnieje.";
    }

    $stmt->close();
    $connect->close();
}

?>

<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>


<div class="container container-sm">
    <header>
        <h3>Bank app</h3>
        <a href="register.php">Załóż konto</a>
    </header>
    <h2>Zaloguj się</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label for="username">Login:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Zaloguj się</button>
    </form>
</div>

<footer>
</footer>
</body>
</html>
