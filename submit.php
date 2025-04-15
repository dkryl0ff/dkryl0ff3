<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'u68529'; 
$username = 'u68529'; 
$password = '4465490'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$errors = [];

if (empty($_POST['full_name'])) {
    $errors[] = "ФИО обязательно для заполнения.";
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s]+$/u', $_POST['full_name'])) {
    $errors[] = "ФИО должно содержать только буквы и пробелы.";
}

if (empty($_POST['phone'])) {
    $errors[] = "Телефон обязателен для заполнения.";
}

if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Введите корректный email.";
}

if (empty($_POST['birth_date'])) {
    $errors[] = "Дата рождения обязательна.";
}

if (empty($_POST['gender'])) {
    $errors[] = "Укажите пол.";
}

if (empty($_POST['languages'])) {
    $errors[] = "Выберите хотя бы один язык программирования.";
}

if (empty($_POST['contract_agreed'])) {
    $errors[] = "Необходимо подтвердить ознакомление с контрактом.";
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<div class='error'>$error</div>";
    }
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO applications 
        (full_name, phone, email, birth_date, gender, biography, contract_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birth_date'],
        $_POST['gender'],
        $_POST['biography'],
        (int)$_POST['contract_agreed']
    ]);

    $applicationId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id) 
        VALUES (?, ?)
    ");

    foreach ($_POST['languages'] as $languageId) {
        $stmt->execute([$applicationId, $languageId]);
    }

    echo "<div style='color: green;'>Данные успешно сохранены!</div>";
} catch (PDOException $e) {
    die("Ошибка при сохранении данных: " . $e->getMessage());
}
?>