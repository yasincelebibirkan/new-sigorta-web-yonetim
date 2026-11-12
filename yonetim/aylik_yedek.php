<?php
include 'auth.php';
// SADECE TERMİNALDEN (CRON) ÇALIŞMASINA İZİN VER
if (php_sapi_name() !== 'cli' && !isset($_GET['guvenli_anahtar'])) {
    die("Bu sayfaya dogrudan erisim yasaktir.");
}
// Arka planda çalışacağı için __DIR__ kullanarak tam yol belirtiyoruz
include __DIR__ . '/db.php';

// Her ayın 1'inde çalıştığında, BİR ÖNCEKİ ayın verilerini yedeklemesi için:
$ay = date('m', strtotime("-1 month"));
$yil = date('Y', strtotime("-1 month"));
$dosya_adi = "Otomatik_Yedek_{$yil}_{$ay}.csv";

$klasor = __DIR__ . '/yedek_data/';
if (!file_exists($klasor)) {
    mkdir($klasor, 0755, true);
}

$tam_yol = $klasor . $dosya_adi;

// SQL Sorgusu: Tüm sütunlar (PDF formatındaki gibi)
$sql = "SELECT m.*, p.* FROM policeler p 
        JOIN musteriler m ON p.musteri_id = m.id 
        WHERE MONTH(p.baslangic_tarihi) = :ay AND YEAR(p.baslangic_tarihi) = :yil";

$stmt = $pdo->prepare($sql);
$stmt->execute(['ay' => $ay, 'yil' => $yil]);
$veriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($veriler) {
    $fp = fopen($tam_yol, 'w');
    fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Türkçe karakter desteği

    // Tüm Başlıklar
    fputcsv($fp, [
        'MÜŞTERİ ADI',
        'TC KİMLİK',
        'TELEFON',
        'DOĞUM TARİHİ',
        'DÜZENLEME TARİHİ',
        'BAŞLANGIÇ TARİHİ',
        'BİTİŞ TARİHİ',
        'POLİÇE TÜRÜ',
        'RUHSAT SERİ NO',
        'PLAKA',
        'POLİÇE NO',
        'SİGORTA ŞİRKETİ',
        'ÖDEME ŞEKLİ',
        'BRÜT PRİM',
        'NET PRİM',
        'KOMİSYON %',
        'KOMİSYON TUTARI',
        'NOTLAR'
    ]);

    foreach ($veriler as $row) {
        $komisyon_tutari = ($row['prim_tutari'] * $row['komisyon_orani']) / 100;

        fputcsv($fp, [
            $row['ad_soyad'],
            $row['tc_no'],
            $row['telefon'],
            $row['dogum_tarihi'],
            $row['yapilma_tarihi'],
            $row['baslangic_tarihi'],
            $row['bitis_tarihi'],
            $row['sigorta_turu'],
            $row['ruhsat_seri'],
            strtoupper($row['plaka']),
            $row['police_no'],
            $row['sigorta_sirketi'],
            $row['odeme_sekli'],
            $row['prim_tutari'],
            $row['net_prim'],
            $row['komisyon_orani'],
            number_format($komisyon_tutari, 2, ',', '.'),
            $row['notlar']
        ]);
    }
    fclose($fp);
}
?>