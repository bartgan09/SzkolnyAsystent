<?php
include("../baza/db.php");

$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');

if (!$field || !$value) exit;

if (!in_array($field, ['username', 'email'])) exit;

$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $field = ?");
$stmt->bind_param("s", $value);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();

if ($count > 0) echo ucfirst($field) . " jest już zajęty 😞";
else echo ucfirst($field) . " jest dostępny -";
