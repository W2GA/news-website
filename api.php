<?php
header('Content-Type: application/json');

// Informations de connexion
$host = "localhost";
$port = 4306;          
$dbname = "news";      
$username = "root";    
$password = "";        

$filename = "last_insert.txt";  // fichier pour stocker la dernière date
$today = date("Y-m-d");         // date du jour

try {
    // Connexion PDO
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fonction pour vider la table
    function clearNewsTable($pdo) {
        $pdo->exec("TRUNCATE TABLE news");
    }

    // Vérifier la date du dernier insert
    $lastDate = file_exists($filename) ? file_get_contents($filename) : "";

    if ($lastDate !== $today) {
        // Vider la table avant d'insérer les nouvelles données
        clearNewsTable($pdo);

        // Récupérer les news via l'API
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://arabic-news-api.p.rapidapi.com/aljazeera",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-host: arabic-news-api.p.rapidapi.com",
                "x-rapidapi-key: 7648ab37f2mshb1413505ceecc53p14b708jsndc456bd54fbb"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if (!$err) {
            $newsArray = json_decode($response, true)["results"] ?? [];

            foreach ($newsArray as $tab) {
                $sql = "INSERT INTO news (headline, content, image) VALUES (:headline, :content, :image)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':headline' => $tab["headline"] ?? '',
                    ':content' => $tab["content"] ?? '',
                    ':image' => $tab["image"] ?? ''
                ]);
            }

            // Mettre à jour le fichier avec la date d'aujourd'hui
            file_put_contents($filename, $today);
        }
    }

    // Récupérer toutes les news et les renvoyer en JSON
    $stmt = $pdo->query("SELECT * FROM news ORDER BY id DESC");
    $allNews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $allNews
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
