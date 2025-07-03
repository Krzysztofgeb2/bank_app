<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('config.php');

$connect = mysqli_connect("localhost", "root", "", "bank_app");

if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role_id = 1;

    if (empty($username) || empty($email) || empty($password)) {
        die("Wszystkie pola są wymagane.");
    }

    // Sprawdzenie, czy użytkownik z podanym emailem już istnieje
    $stmt = $connect->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Użytkownik z tym emailem już istnieje.");
    }

    $stmt->close();

    // Hashowanie hasła
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));
    $is_verified = 0;

    // Dodanie użytkownika
    $stmt = $connect->prepare("INSERT INTO users (username, email, password_hash, role_id, is_verified, verification_token) VALUES (?, ?, ?, ?, ?,?)");
    $stmt->bind_param("sssiss", $username, $email, $password_hash, $role_id, $is_verified, $verification_token);

    if ($stmt->execute()) {
        $new_user_id = $connect->insert_id;

        function generateAccountNumber($length = 26) {
            $digits = '';
            for ($i = 0; $i < $length; $i++) {
                $digits .= random_int(0, 9); // cryptographically secure
            }
            return $digits;
        }

        $account_number = generateAccountNumber();

        // Tworzenie konta z numerem konta i saldem 0.00
        $stmt_account = $connect->prepare("INSERT INTO accounts (user_id, account_number, balance) VALUES (?, ?, 0.00)");
        $stmt_account->bind_param("is", $new_user_id, $account_number);

        if ($stmt_account->execute()) {
            echo "Rejestracja udana. Potwierdz swoj email.";
        } else {
            echo "Użytkownik dodany, ale wystąpił błąd przy tworzeniu konta: " . $stmt_account->error;
        }

        $stmt_account->close();
    } else {
        echo "Wystąpił błąd przy dodawaniu użytkownika: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>

    <a href="login.php">Masz konto? - zaloguj sie</a>

</header>
<h2>Formularz rejestracji</h2>
<form method="POST" action="register.php">
    <label>Login:
        <input type="text" name="username" required>
    </label><br><br>
    <label>Email:
        <input type="email" name="email" required>
    </label><br><br>
    <label>Hasło:
        <input type="password" name="password" required>
    </label><br><br>
    <button type="submit">Zarejestruj się</button>
    <a href ="resend_activation.php">Nie dostales maila? kliknij</a>
</form>
<footer>
    <p>&copy; 2025 Twoja Firma. Wszystkie prawa zastrzeżone.</p>
</footer>

</body>
</html>

