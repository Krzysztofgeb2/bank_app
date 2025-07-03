<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$connect = mysqli_connect("localhost", "root", "", "bank_app");
if (!$connect) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Pobierz konto użytkownika
$stmt = $connect->prepare("SELECT id, balance, account_number FROM accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

if (!$account) {
    die("Nie znaleziono konta użytkownika.");
}

$account_id = $account["id"];
$balance = $account["balance"];
$message = "";
$message_class = "";

// Obsługa wpłaty
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["deposit_amount"])) {
    $deposit_amount = floatval($_POST["deposit_amount"]);

    if ($deposit_amount <= 0) {
        $message = "Kwota wpłaty musi być większa niż 0.";
        $message_class = "error";
    } else {
        $stmt = $connect->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $deposit_amount, $account_id);

        if ($stmt->execute()) {
            $balance += $deposit_amount;
            $message = "Wpłata zakończona sukcesem.";
            $message_class = "success";
        } else {
            $message = "Błąd podczas wpłaty.";
            $message_class = "error";
        }
    }
}

// Obsługa przelewu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["recipient_account_number"])) {
    $to_account_number = $_POST["recipient_account_number"];
    $amount = floatval($_POST["amount"]);

    if ($amount <= 0) {
        $message = "Kwota musi być większa niż 0.";
        $message_class = "error";
    } elseif ($to_account_number == $account["account_number"]) {
        $message = "Nie możesz wysłać przelewu na własne konto.";
        $message_class = "error";
    } else {
        $stmt = $connect->prepare("SELECT * FROM accounts WHERE account_number = ?");
        if (!$stmt) {
            die("Błąd przygotowania zapytania: " . $connect->error);
        }
        $stmt->bind_param("s", $to_account_number);
        $stmt->execute();
        $recipient_account = $stmt->get_result()->fetch_assoc();

        if (!$recipient_account) {
            $message = "Nie znaleziono konta odbiorcy.";
            $message_class = "error";
        } elseif ($balance < $amount) {
            $message = "Niewystarczające środki.";
            $message_class = "error";
        } else {
            $connect->begin_transaction();
            try {
                // Odjęcie kwoty od konta nadawcy
                $stmt = $connect->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $account_id);
                $stmt->execute();

                // Dodanie kwoty na konto odbiorcy
                $stmt = $connect->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $recipient_account["id"]);
                $stmt->execute();

                // Dodanie wpisu w tabeli przelewów
                $stmt = $connect->prepare("INSERT INTO transfers (from_account_id, to_account_id, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("iid", $account_id, $recipient_account["id"], $amount);
                $stmt->execute();

                $connect->commit();
                $message = "Przelew zrealizowany.";
                $message_class = "success";
                $balance -= $amount;
            } catch (Exception $e) {
                $connect->rollback();
                $message = "Błąd przelewu: " . $e->getMessage();
                $message_class = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Panel użytkownika</h1>
    <nav>
        <a href="logout.php">Wyloguj</a>
    </nav>
</header>

<div class="container">
    <p>Numer konta: <strong><?= htmlspecialchars($account["account_number"]) ?></strong></p>
    <p>Saldo: <strong><?= number_format($balance, 2) ?> PLN</strong></p>

    <?php if ($message): ?>
        <p class="<?= $message_class ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <hr>
    <h3>Wpłać środki</h3>
    <form method="POST" action="">
        <label>Kwota:
            <input type="number" name="deposit_amount" step="0.01" required>
        </label><br><br>
        <button type="submit">Wpłać</button>
    </form>

    <h3>Wykonaj przelew</h3>
    <form method="POST" action="">
        <label>Numer konta odbiorcy:
            <input type="text" name="recipient_account_number" required>
        </label><br><br>
        <label>Kwota:
            <input type="number" name="amount" step="0.01" required>
        </label><br><br>
        <button type="submit">Wyślij przelew</button>
    </form>

    <hr>

    <h3>Historia przelewów</h3>
    <table>
        <thead>
        <tr>
            <th>Typ</th>
            <th>Data</th>
            <th>Kwota</th>
            <th>Druga strona</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Pobieranie historii przelewów
        $stmt = $connect->prepare("
            SELECT t.*, 
                   s.user_id AS sender_user_id, s.account_number AS sender_account,
                   r.user_id AS recipient_user_id, r.account_number AS recipient_account,
                   su.username AS sender_name,
                   ru.username AS recipient_name
            FROM transfers t
            JOIN accounts s ON t.from_account_id = s.id
            JOIN accounts r ON t.to_account_id = r.id
            JOIN users su ON s.user_id = su.id
            JOIN users ru ON r.user_id = ru.id
            WHERE s.user_id = ? OR r.user_id = ?
            ORDER BY t.transfer_date DESC
        ");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $type = ($row["sender_user_id"] == $user_id) ? "Wysłano do" : "Otrzymano od";
            $other_user = ($row["sender_user_id"] == $user_id) ? $row["recipient_name"] : $row["sender_name"];
            $amount = number_format($row["amount"], 2);
            $date = $row["transfer_date"];
            echo "<tr>
                        <td>{$type}</td>
                        <td>{$date}</td>
                        <td>{$amount} PLN</td>
                        <td>" . htmlspecialchars($other_user) . "</td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> Bank App</p>
</footer>
</body>
</html>
