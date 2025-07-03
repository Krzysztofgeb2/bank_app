<?php
$connect = mysqli_connect("localhost", "root", "", "bank_app");

if (mysqli_connect_errno()) {
    die("Błąd połączenia z bazą danych.");
}

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    $stmt = $connect->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $update = $connect->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();

        echo "Twoje konto zostało aktywowane! Możesz się teraz <a href='login.php'>zalogować</a>.";
    } else {
        echo "Nieprawidłowy lub już użyty token.";
    }
} else {
    echo "Brak tokenu w zapytaniu.";
}
?>
