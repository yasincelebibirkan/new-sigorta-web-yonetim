<?php
include 'auth.php';
include '../db.php';

$onizleme_verileri = [];

if (isset($_POST['kontrol_et'])) {
    $dosya = $_FILES['csv_dosya']['tmp_name'];
    $handle = fopen($dosya, "r");
    
    // BOM ve Karakter Seti Ayarı
    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

    $sayac = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $sayac++;
        if ($sayac == 1) continue; // Başlıkları atla

        // CSV'den verileri değişkenlere ata
        $satir = [
            'ad'      => $data[0] ?? '',
            'tc'      => $data[1] ?? '',
            'tel'     => $data[2] ?? '',
            'dtar'    => $data[3] ?? '',
            'ptar'    => $data[4] ?? '',
            'btar'    => $data[5] ?? '',
            'tur'     => $data[6] ?? '',
            'plaka'   => $data[7] ?? '',
            'pno'     => $data[8] ?? '',
            'sirket'  => $data[9] ?? '',
            'brut'    => $data[10] ?? 0,
            'net'     => $data[11] ?? 0,
            'kom'     => $data[12] ?? 0,
            'hata'    => []
        ];

        // --- BASİT KONTROLLER (VALIDATION) ---
        if (strlen($satir['tc']) != 11) $satir['hata'][] = "TC 11 hane olmalı";
        if (empty($satir['ad'])) $satir['hata'][] = "İsim boş olamaz";
        if (empty($satir['plaka'])) $satir['hata'][] = "Plaka eksik";
        
        $onizleme_verileri[] = $satir;
    }
    fclose($handle);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Veri Kontrol Paneli</title>
    <link rel="stylesheet" href="style.css"> <style>
        .hata-satir { background-color: #fee2e2; color: #b91c1c; }
        .onay-satir { background-color: #f0fdf4; }
        .hata-badge { background: red; color: white; padding: 2px 5px; border-radius: 4px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Veri Yükleme Kontrolü</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_dosya" accept=".csv" required>
            <button type="submit" name="kontrol_et" class="btn-filter">DOSYAYI KONTROL ET</button>
        </form>

        <?php if (!empty($onizleme_verileri)): ?>
        <table border="1" style="width:100%; margin-top:20px; border-collapse: collapse;">
            <thead>
                <tr style="background:#eee;">
                    <th>Müşteri</th>
                    <th>TC / Vergi</th>
                    <th>Plaka</th>
                    <th>Bitiş Tarihi</th>
                    <th>Net Prim</th>
                    <th>Durum / Hata</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($onizleme_verileri as $v): ?>
                <tr class="<?php echo !empty($v['hata']) ? 'hata-satir' : 'onay-satir'; ?>">
                    <td><?php echo $v['ad']; ?></td>
                    <td><?php echo $v['tc']; ?></td>
                    <td><?php echo $v['plaka']; ?></td>
                    <td><?php echo $v['btar']; ?></td>
                    <td><?php echo $v['net']; ?> ₺</td>
                    <td>
                        <?php 
                        if (empty($v['hata'])) {
                            echo "<b style='color:green;'>✓ Hazır</b>";
                        } else {
                            foreach ($v['hata'] as $h) echo "<span class='hata-badge'>$h</span> ";
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <p>Eğer yukarıdaki tablo doğruysa, şimdi veritabanına kaydedebilirsiniz.</p>
            <form action="veri_kaydet_islem.php" method="POST">
                <input type="hidden" name="dosya_adi" value="<?php echo $_FILES['csv_dosya']['name']; ?>">
                <button type="submit" class="btn-filter" style="background:green;">HER ŞEY DOĞRU, KAYDET</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>