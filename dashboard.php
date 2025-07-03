<?php
session_start();
if (isset($_SESSION["user"])) {
    header('Location: login.php');
    exit;
}

?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>dashboard</title>
</head>
<body>
<h2>Witaj na swoim koncie <?= htmlspecialchars($_SESSION["user"])?></h2>
<p>Panel uzytkownika</p>
<a href="logout.php">Wyloguj</a>

</body>
</html>
