<?php
include 'auth.php';
include '../db.php';

if (isset($_POST['yukle'])) {
    $dosya = $_FILES['csv_dosya']['tmp_name'];
    $handle = fopen($dosya, "r");
    
    // Türkçe karakter desteği
    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

    $sayac = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $sayac++;
        if ($sayac == 1) continue; // Başlık satırını atla

        // CSV Sütun Eşleşmeleri (Sıralamaya dikkat!)
        $ad_soyad      = $data[0];
        $tc_no         = $data[1];
        $telefon       = $data[2];
        $dogum_tarihi  = $data[3];
        $yapilma_tar   = $data[4];
        $bitis_tar     = $data[5];
        $sigorta_turu  = $data[6];
        $plaka         = $data[7];
        $police_no     = $data[8];
        $sirket        = $data[9];
        $prim          = $data[10];

        try {
            $pdo->beginTransaction();

            // 1. Müşteri var mı kontrol et, yoksa ekle
            $m_sorgu = $pdo->prepare("SELECT id FROM musteriler WHERE tc_no = ?");
            $m_sorgu->execute([$tc_no]);
            $musteri_id = $m_sorgu->fetchColumn();

            if (!$musteri_id) {
                $m_ekle = $pdo->prepare("INSERT INTO musteriler (ad_soyad, tc_no, telefon, dogum_tarihi) VALUES (?, ?, ?, ?)");
                $m_ekle->execute([$ad_soyad, $tc_no, $telefon, $dogum_tarihi]);
                $musteri_id = $pdo->lastInsertId();
            }

            // 2. Poliçeyi ekle
            $p_ekle = $pdo->prepare("INSERT INTO policeler (musteri_id, yapilma_tarihi, bitis_tarihi, sigorta_turu, plaka, police_no, sigorta_sirketi, prim_tutari) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $p_ekle->execute([$musteri_id, $yapilma_tar, $bitis_tar, $sigorta_turu, $plaka, $police_no, $sirket, $prim]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Hata satır $sayac: " . $e->getMessage() . "<br>";
        }
    }
    fclose($handle);
    echo "Aktarım tamamlandı!";
}
?>

<form method="POST" enctype="multipart/form-data" style="padding:20px; background:#fff; border-radius:10px;">
    <h3>CSV'den Veri Aktar</h3>
    <input type="file" name="csv_dosya" accept=".csv" required>
    <button type="submit" name="yukle">Verileri Veritabanına Yaz</button>
</form>