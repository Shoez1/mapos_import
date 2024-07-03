<?php
require 'vendor/autoload.php'; // Certifique-se de que o autoload do Composer está correto

use Dotenv\Dotenv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// Conectar ao banco de dados MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    $table = $_POST['table'];

    // Carregar o arquivo Excel
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Assumindo que a primeira linha é o cabeçalho
    $header = $data[0];
    unset($data[0]);

    // Prepara a consulta de inserção
    $columns = implode(", ", $header);
    $placeholders = implode(", ", array_fill(0, count($header), '?'));

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);

    // Iterar sobre os dados e executar a consulta
    foreach ($data as $row) {
        $stmt->bind_param(str_repeat('s', count($row)), ...$row);
        $stmt->execute();
    }

    echo "Importação concluída com sucesso!";
}

$conn->close();
?>
