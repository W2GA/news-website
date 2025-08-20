<?php
header('Content-Type: application/json');

// Infos de connexion
$host = "localhost";
$port = 4306;          
$dbname = "news";      
$username = "root";    
$password = "";        

try {
    // Connexion PDO
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si un id est passé en GET
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID manquant'
        ]);
        exit;
    }

    $id = intval($_GET['id']); // sécuriser l'id

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $newsItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($newsItem) {
        echo json_encode([
            'status' => 'success',
            'data' => $newsItem
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Aucune news trouvée pour cet ID'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
