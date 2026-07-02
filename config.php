<?php
$host = 'localhost';      // Siempre localhost en XAMPP
$user = 'root';           // Usuario por defecto de XAMPP
$password = '';           // Sin contraseña por defecto
$dbname = 'empleadoss_departamentoss';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

<?php
// config.php
$servername = "localhost";
$username = "root"; // Cambia por tu usuario
$password = ""; // Cambia por tu contraseña
$dbname = "empleadoss_departamentoss";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");
?>