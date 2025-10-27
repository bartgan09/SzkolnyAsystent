<?php
session_start();
include("../baza/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_type"] = $user["user_type"];
            $conn->query("UPDATE users SET last_login = NOW() WHERE user_id = " . $user["user_id"]);

            header("Location: ../dashboard/" . $user["user_type"] . ".php");
            exit;
        } else $error = "Nieprawidłowe hasło!";
    } else $error = "Użytkownik nie istnieje!";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Logowanie | Szkolny Asystent</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="form-container">
  <div class="card">
    <h2>Logowanie</h2>
    <form method="post">
      <input type="text" name="username" placeholder="Nazwa użytkownika" required>
      <input type="password" name="password" placeholder="Hasło" required>
      <button type="submit">Zaloguj się</button>
      <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
    <p class="switch">Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
  </div>
</div>
</body>
</html>
