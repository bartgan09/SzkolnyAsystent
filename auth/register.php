<?php
session_start();
include("../baza/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $user_type = $_POST["user_type"];
    $first = $_POST["first_name"];
    $last = $_POST["last_name"];
    $class = $_POST["class"] ?? null;

    // Walidacja serwerowa (dla bezpieczeństwa)
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=]).{8,}$/', $password)) {
        $error = "Hasło nie spełnia wymagań bezpieczeństwa!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password_hash, $user_type);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            if ($user_type === "student" && $class) {
                $conn->query("INSERT INTO students (user_id, first_name, last_name, class) VALUES ('$user_id', '$first', '$last', '$class')");
            } else {
                $conn->query("INSERT INTO teachers (user_id, first_name, last_name) VALUES ('$user_id', '$first', '$last')");
            }

            // AUTO-LOGIN
            $_SESSION["user_id"] = $user_id;
            $_SESSION["user_type"] = $user_type;
            header("Location: ../dashboard/" . $user_type . ".php");
            exit;
        } else {
            $error = "Ten login lub e-mail jest już zajęty.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Rejestracja | Szkolny Asystent</title>
<link rel="stylesheet" href="../css/style.css">
<script>
// Walidacja hasła
function checkPassword() {
  const pass = document.querySelector('[name="password"]').value;
  const rules = {
    length: pass.length >= 8,
    upper: /[A-Z]/.test(pass),
    number: /\d/.test(pass),
    special: /[!@#$%^&*()_+\-=]/.test(pass)
  };

  for (let key in rules) {
    const row = document.getElementById(key);
    row.className = rules[key] ? 'valid' : 'invalid';
  }
}

// AJAX — sprawdzanie dostępności loginu i e-maila
async function checkAvailability(field, value) {
  if (!value.trim()) return;
  const res = await fetch(`check_availability.php?field=${field}&value=${encodeURIComponent(value)}`);
  const data = await res.text();
  document.getElementById(`${field}-status`).textContent = data;
  document.getElementById(`${field}-status`).style.color = data.includes("dostępny") ? "lime" : "red";
}

// Ukrywanie/pokazywanie pola klasy
function toggleFields() {
  const type = document.querySelector('select[name="user_type"]').value;
  document.getElementById("student-fields").style.display = (type === "student") ? "block" : "none";
}
</script>
</head>
<body>
<div class="form-container">
  <div class="card">
    <h2>Rejestracja</h2>
    <form method="post">
      <input type="text" name="username" placeholder="Nazwa użytkownika" required onkeyup="checkAvailability('username', this.value)">
      <span id="username-status"></span>

      <input type="email" name="email" placeholder="Adres e-mail" required onkeyup="checkAvailability('email', this.value)">
      <span id="email-status"></span>

      <input type="password" name="password" placeholder="Hasło" required onkeyup="checkPassword()">

      <table class="password-rules">
        <tr id="length"><td>- Min. 8 znaków</td></tr>
        <tr id="upper"><td>- Duża litera</td></tr>
        <tr id="number"><td>- Cyfra</td></tr>
        <tr id="special"><td>- Znak specjalny</td></tr>
      </table>

      <input type="text" name="first_name" placeholder="Imię" required>
      <input type="text" name="last_name" placeholder="Nazwisko" required>

      <select name="user_type" onchange="toggleFields()" required>
        <option value="student">Uczeń</option>
        <option value="teacher">Nauczyciel</option>
      </select>

      <div id="student-fields">
        <input type="text" name="class" placeholder="Klasa (np. 3A)">
      </div>

      <button type="submit">Zarejestruj się</button>
      <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
    <p class="switch">Masz już konto? <a href="login.php">Zaloguj się</a></p>
  </div>
</div>
</body>
</html>
