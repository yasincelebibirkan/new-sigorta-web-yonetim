<?php
include 'auth.php';
include '../db.php';

$id = $_GET['id'] ?? 0;
$return_url = $_GET['return_url'] ?? 'tum_policeler.php';

if ($id) {
    try {
        // Poliçeyi sil
        $stmt = $pdo->prepare("DELETE FROM policeler WHERE id = ?");
        $stmt->execute([$id]);
        
        // Eğer bu müşterinin başka poliçesi kalmadıysa müşteriyi de silsin mi? 
        // (Genelde poliçe bazlı sistemlerde müşteri kaydı kalır ama istersen ekleyebiliriz)
        
        header("Location: " . $return_url);
        exit;
    } catch (Exception $e) {
        die("Silme hatası oluştu: " . $e->getMessage());
    }
} else {
    header("Location: tum_policeler.php");
    exit;
}