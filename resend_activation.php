<?php
require_once("config.php");
$connect = mysqli_connect("localhost", "root", "", "bank_app");

$message = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $stmt = $connect->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user["is_verified"] == 0) {
            $new_token = bin2hex(random_bytes(32));
            $update = $connect->prepare("UPDATE users SET verification_token = ? WHERE email = ?");
            $update->bind_param("ss", $new_token, $email);
            $update->execute();
            send_activation_email($email, $new_token);
            $message = "Link aktywacyjny został wysłany ponownie.";
        } else {
            $message = "Konto jest już aktywne.";
        }
    } else {
        $message = "Nie znaleziono użytkownika.";
    }
}
?>

<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wyślij ponownie link aktywacyjny</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <a href="login.php">Zaloguj się</a> |
    <a href="register.php">Załóż konto</a>
</header>

<div class="container">
    <h2>Wyślij ponownie link aktywacyjny</h2>

    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="resend_activation.php">
        <label>Podaj email:
            <input type="email" name="email" required>
        </label>
        <button type="submit">Wyślij ponownie</button>
    </form>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> Bank App</p>
</footer>

</body>
</html>
