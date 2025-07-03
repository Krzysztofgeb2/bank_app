<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('config.php');

$connect = mysqli_connect("localhost", "root", "", "bank_app");

if (!$connect) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

/*if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 3) {
    header("Location: login.php");
    exit();
}*/

$search_result = null;
$transactions = [];
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

    if (!$search_result) {
        $search_error = "Nie znaleziono użytkownika.";
    } else {
        $account_id = $search_result['account_id'];
        $stmt = $connect->prepare("
            SELECT t.amount, t.transfer_date, t.from_account_id, t.to_account_id 
            FROM transfers t 
            WHERE t.from_account_id = ? OR t.to_account_id = ?
            ORDER BY t.transfer_date DESC
        ");
        $stmt->bind_param("ii", $account_id, $account_id);
        $stmt->execute();
        $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Obsługa akcji pracownika
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    // Sprawdź czy użytkownik ma rolę klienta
    $stmt = $connect->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $role_result = $stmt->get_result()->fetch_assoc();

    if (!$role_result || $role_result['role_id'] != 1) {
        echo "<p style='color:red'>Można edytować tylko użytkowników z rolą 1 (klient).</p>";
    } else {
        switch ($_POST['action']) {
            case 'update_user':
                $username = $_POST['username'];
                $email = $_POST['email'];
                $stmt = $connect->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
                $stmt->execute();
                echo "<p style='color:green'>Dane zaktualizowane.</p>";
                break;

            case 'add_balance':
                $amount = (float)$_POST['amount'];
                $stmt = $connect->prepare("UPDATE accounts SET balance = balance + ? WHERE user_id = ?");
                $stmt->bind_param("di", $amount, $user_id);
                $stmt->execute();
                echo "<p style='color:green'>Saldo zaktualizowane.</p>";
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel pracownika</title>
    <link rel="stylesheet" href="employee_style.css">
</head>
<body>
<h2>Panel pracownika – Obsługa klientów</h2>

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

        <label>Login:
            <input type="text" name="username" value="<?= htmlspecialchars($search_result['username']) ?>">
        </label><br>

        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($search_result['email']) ?>">
        </label><br>

        <button type="submit" name="action" value="update_user">Zapisz zmiany</button>
    </form>

    <h4>Saldo konta: <?= number_format($search_result['balance'], 2) ?> PLN</h4>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?= $search_result['id'] ?>">
        <label>Kwota do dodania:
            <input type="number" step="0.01" name="amount" required>
        </label>
        <button type="submit" name="action" value="add_balance">Dodaj środki</button>
    </form>

    <h4>Historia transakcji:</h4>
    <?php if (count($transactions) > 0): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Data</th>
                <th>Kwota</th>
                <th>Z konta</th>
                <th>Na konto</th>
            </tr>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= $t['transfer_date'] ?></td>
                    <td><?= number_format($t['amount'], 2) ?> PLN</td>
                    <td><?= $t['from_account_id'] ?></td>
                    <td><?= $t['to_account_id'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Brak transakcji.</p>
    <?php endif; ?>
<?php endif; ?>
<footer>
    <a href="logout.php">Wyloguj</a>
</footer>
</body>

</html>
