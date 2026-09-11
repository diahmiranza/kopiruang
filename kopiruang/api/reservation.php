<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate
$required = ['name', 'email', 'phone', 'date', 'time', 'guests'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
        exit;
    }
}

$name   = clean($_POST['name']);
$email  = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone  = clean($_POST['phone']);
$date   = clean($_POST['date']);
$time   = clean($_POST['time']);
$guests = (int)$_POST['guests'];
$notes  = clean($_POST['notes'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

if ($guests < 1 || $guests > 20) {
    echo json_encode(['success' => false, 'message' => 'Jumlah tamu antara 1-20 orang.']);
    exit;
}

// Check date not in past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'Tanggal reservasi tidak boleh di masa lalu.']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO reservations (name, email, phone, date, time, guests, notes)
        VALUES (:name, :email, :phone, :date, :time, :guests, :notes)
    ");
    $stmt->execute([
        ':name'   => $name,
        ':email'  => $email,
        ':phone'  => $phone,
        ':date'   => $date,
        ':time'   => $time,
        ':guests' => $guests,
        ':notes'  => $notes
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil disimpan!',
        'id'      => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Coba lagi nanti.']);
}
?>
