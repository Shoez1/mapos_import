<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$sql = "SHOW TABLES";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload de Arquivo Excel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 50px;
            background: linear-gradient(to right, #f9d423, #ff4e50, #000000);
            color: white;
        }
        .container {
            max-width: 600px;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .progress {
            margin-top: 20px;
            display: none;
        }
        .progress-bar {
            width: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">IMPORTADOR MAPOS</h1>
        <form id="uploadForm" action="import_excel.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="table">Escolha a tabela:</label>
                <select name="table" id="table" class="form-control" required>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_array()) {
                            echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
                        }
                    } else {
                        echo '<option value="">Nenhuma tabela encontrada</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="file">Escolha o arquivo Excel:</label>
                <input type="file" name="file" id="file" class="form-control-file" accept=".xlsx" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Upload e Importar</button>
        </form>
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var percentComplete = Math.round((e.loaded / e.total) * 100);
                                $('.progress').show();
                                $('.progress-bar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    type: 'POST',
                    url: 'import_excel.php',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert('Importação concluída com sucesso!');
                        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('');
                        $('.progress').hide();
                    },
                    error: function() {
                        alert('Erro ao importar o arquivo.');
                        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('');
                        $('.progress').hide();
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
