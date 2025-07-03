<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('config.php');



$connect = mysqli_connect("localhost", "root", "", "bank_app");


if (!$connect) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$search_result = null;
$search_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = trim($_POST['search']);
    $stmt = $connect->prepare("
        SELECT u.id, u.username, u.email, u.role_id, a.id AS account_id, a.account_number, a.balance 
        FROM users u 
        JOIN accounts a ON u.id = a.user_id
        WHERE u.id = ? OR u.email = ? OR a.account_number = ?
    ");
    $stmt->bind_param("iss", $search, $search, $search);
    $stmt->execute();
    $search_result = $stmt->get_result()->fetch_assoc();

    if (!$search_result) $search_error = "Nie znaleziono użytkownika.";
}

// Obsługa akcji admina
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    switch ($_POST['action']) {
        case 'delete_user':
            $stmt = $connect->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            echo "<p style='color:green'>Użytkownik usunięty.</p>";
            break;

        case 'update_user':
            $email = $_POST['email'];
            $role = (int)$_POST['role_id'];
            $stmt = $connect->prepare("UPDATE users SET email = ?, role_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $email, $role, $user_id);
            $stmt->execute();
            echo "<p style='color:green'>Dane zaktualizowane.</p>";
            break;

        case 'adjust_balance':
            $amount = (float)$_POST['amount'];
            $stmt = $connect->prepare("UPDATE accounts SET balance = balance + ? WHERE user_id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
            echo "<p style='color:green'>Saldo zaktualizowane.</p>";
            break;

        case 'delete_transactions':
            $stmt = $connect->prepare("SELECT id FROM accounts WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $account_id = $res->fetch_assoc()['id'];

            $stmt = $connect->prepare("DELETE FROM transfers WHERE from_account_id = ? OR to_account_id = ?");
            $stmt->bind_param("ii", $account_id, $account_id);
            $stmt->execute();
            echo "<p style='color:green'>Transakcje usunięte.</p>";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel administratora</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<h2>Panel administratora – Zarządzanie użytkownikami</h2>

<form method="POST">
    <input type="text" name="search" placeholder="ID, e-mail lub numer konta" required>
    <button type="submit">Szukaj</button>
</form>

<?php if ($search_error): ?>
    <p style="color:red;"><?= htmlspecialchars($search_error) ?></p>
<?php elseif ($search_result): ?>
    <h3>Dane użytkownika:</h3>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?= $search_result['id'] ?>">

        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($search_result['email']) ?>">
        </label><br>

        <label>Rola:
            <select name="role_id">
                <option value="1" <?= $search_result['role_id'] == 1 ? 'selected' : '' ?>>Użytkownik</option>
                <option value="2" <?= $search_result['role_id'] == 2 ? 'selected' : '' ?>>Admin</option>
                <option value="3" <?= $search_result['role_id'] == 3 ? 'selected' : '' ?>>Pracownik</option>
            </select>
        </label><br>

        <button type="submit" name="action" value="update_user">Zapisz zmiany</button>
    </form>

    <h4>Saldo: <?= number_format($search_result['balance'], 2) ?> PLN</h4>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?= $search_result['id'] ?>">
        <label>Kwota do dodania/odjęcia:
            <input type="number" step="0.01" name="amount" required>
        </label>
        <button type="submit" name="action" value="adjust_balance">Zmień saldo</button>
    </form>

    <h4>Operacje systemowe</h4>
    <form method="POST" onsubmit="return confirm('Na pewno usunąć użytkownika?')">
        <input type="hidden" name="user_id" value="<?= $search_result['id'] ?>">
        <button type="submit" name="action" value="delete_user">Usuń użytkownika</button>
    </form>

    <form method="POST" onsubmit="return confirm('Na pewno usunąć historię transakcji?')">
        <input type="hidden" name="user_id" value="<?= $search_result['id'] ?>">
        <button type="submit" name="action" value="delete_transactions">Usuń transakcje</button>
    </form>
<?php endif; ?>
<footer>
    <a href="logout.php">Wyloguj</a>
</footer>
</body>

</html>
