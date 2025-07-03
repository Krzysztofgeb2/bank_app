<?php
require_once("config.php");
$connect = mysqli_connect("localhost", "root", "", "bank_app");

if (isset($_GET["token"])) {
    $token = $_GET["token"];
    $stmt = $connect->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $userId = $user["id"];
        $update = $connect->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $userId);
        $update->execute();
        echo "Twoje konto zostało aktywowane.";
    } else {
        echo "Token jest nieprawidłowy lub konto już aktywne.";
    }
}
?>