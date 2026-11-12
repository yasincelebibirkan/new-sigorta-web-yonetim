<?php
include 'auth.php';
include '../db.php';
?>

<?php
include '../db.php';

// 1. TARIH FILTRELEME MANTIĞI
// Eğer kullanıcı tarih seçmemişse, içinde bulunulan ayın ilk ve son gününü al
$bas_tarih = $_GET['bas_tarih'] ?? date('Y-m-01');
$bit_tarih = $_GET['bit_tarih'] ?? date('Y-m-t'); // 't' parametresi ayın kaç çektiğini (28, 30, 31) otomatik hesaplar

// 2. BILDIRIM TAMAMLA İŞLEMİ
if (isset($_GET['tamamla'])) {
    $p_id = $_GET['tamamla'];
    $stmt = $pdo->prepare("UPDATE policeler SET durum = 1 WHERE id = ?");
    $stmt->execute([$p_id]);
    header("Location: index.php?bas_tarih=$bas_tarih&bit_tarih=$bit_tarih");
    exit;
}

// 3. FINANSAL OZET SORGUSU (Ciro ve Net Kazanç)
$ozet_sorgu = $pdo->prepare("
    SELECT 
        SUM(prim_tutari) as toplam_ciro,
        SUM(prim_tutari * komisyon_orani / 100) as toplam_kazanc,
        COUNT(id) as toplam_adet
    FROM policeler 
    WHERE yapilma_tarihi BETWEEN ? AND ?
");
$ozet_sorgu->execute([$bas_tarih, $bit_tarih]);
$ozet = $ozet_sorgu->fetch();

// 4. SIGORTA TURU BAZLI ADETLER
$tur_sorgu = $pdo->prepare("
    SELECT sigorta_turu, COUNT(*) as adet 
    FROM policeler 
    WHERE yapilma_tarihi BETWEEN ? AND ?
    GROUP BY sigorta_turu
");
$tur_sorgu->execute([$bas_tarih, $bit_tarih]);
$tur_dagilimi = $tur_sorgu->fetchAll();

// 5. YAKLAŞAN POLİÇELER (Hatırlatıcı - Sabit 14 Gün)
$bugun = date('Y-m-d');
$hatirlatma_sorgu = $pdo->query("
    SELECT p.*, m.ad_soyad, m.telefon,
    p.bitis_tarihi,
    DATEDIFF(p.bitis_tarihi, '$bugun') as kalan_gun
    FROM policeler p 
    JOIN musteriler m ON p.musteri_id = m.id
    WHERE p.bitis_tarihi >= '$bugun' 
    AND p.bitis_tarihi <= DATE_ADD('$bugun', INTERVAL 14 DAY)
    AND p.durum = 0 
    ORDER BY p.bitis_tarihi ASC
");
$yaklasan_policeler = $hatirlatma_sorgu->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>New Sigorta | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a3a6e;
            --bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 20px;
            color: #334155;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Filtreleme Alanı */
        .filter-bar {
            background: var(--white);
            padding: 20px;
            border-radius: 15px;
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .filter-bar div {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-bar input {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
        }

        .btn-filter {
            background: var(--navy);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        /* İstatistik Kartları */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border-left: 5px solid var(--navy);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 24px;
            font-weight: 800;
            color: var(--navy);
            margin-top: 10px;
            display: block;
        }

        /* Tür Bazlı Dağılım */
        .type-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .type-badge {
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            border: 1px solid var(--border);
        }

        /* Tablolar */
        .card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            background: #f8fafc;
            font-size: 13px;
            color: #64748b;
        }

        td {
            padding: 15px;
            border-top: 1px solid var(--border);
            font-size: 14px;
        }

        .alert-row {
            background: #fff5f5;
        }

        /* Yaklaşan Poliçeler Tablosunu Sabit Boyutta Tutma */
        .scrollable-table {
            max-height: 400px;
            /* Yaklaşık 6-7 kayıt gösterir, gerisini kaydırır */
            overflow-y: auto;
            /* Dikeyde kaydırma çubuğu çıkarır */
            border: 1px solid var(--border);
            border-radius: 0 0 15px 15px;
        }

        /* Kaydırma çubuğunu şıklaştıralım */
        .scrollable-table::-webkit-scrollbar {
            width: 8px;
        }

        .scrollable-table::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .scrollable-table::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .scrollable-table::-webkit-scrollbar-thumb:hover {
            background: var(--navy);
        }

        /* Tablo başlığının kayarken en üstte sabit kalması için */
        .sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8fafc;
            box-shadow: 0 1px 0 var(--border);
        }

        .btn-reset-month {
            background: #e2e8f0;
            color: #475569;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            border: 1px solid #cbd5e1;
        }

        .btn-reset-month:hover {
            background: #cbd5e1;
            color: var(--navy);
        }
    </style>
</head>

<body>

    <div
        style="background: #fff; padding: 15px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">

        <div style="font-weight: 700; color: #1e3a8a; font-size: 18px;">
            <i class="fas fa-shield-alt"></i> POLİÇE YÖNETİM SİSTEMİ
        </div>

        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="text-align: right;">
                <span
                    style="display: block; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Hoş
                    Geldiniz</span>
                <a href="profil.php" style="text-decoration: none;"><span
                        style="display: block; font-weight: 700; color: #334155;">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span></a>
            </div>

            <a href="logout.php" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')"
                style="background: #fee2e2; color: #ef4444; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; border: 1px solid #fecaca;">
                <i class="fas fa-sign-out-alt"></i> ÇIKIŞ YAP
            </a>
        </div>
    </div>
    <br>
    <div class="container">

        <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:20px;">
            <h1 style="color: var(--navy); margin:0;">New Sigorta Paneli</h1>

            <div style="display: flex; gap: 10px;">
                <a href="police_ekle.php" class="btn-filter" style="text-decoration:none;">
                    <i class="fas fa-plus"></i> Yeni Poliçe Ekle
                </a>
                <a href="ayarlar.php" class="btn-filter" style="text-decoration:none; background-color: #2ecc71;">
                    <i class="fas fa-plus"></i> Ayarlar
                </a>
            </div>
        </div>

        <form class="filter-bar" method="GET">
            <div>
                <label style="font-size:12px; font-weight:bold;">Başlangıç Tarihi</label>
                <input type="date" name="bas_tarih" value="<?php echo $bas_tarih; ?>">
            </div>
            <div>
                <label style="font-size:12px; font-weight:bold;">Bitiş Tarihi</label>
                <input type="date" name="bit_tarih" value="<?php echo $bit_tarih; ?>">
            </div>
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Sorgula
            </button>

            <a href="index.php" class="btn-reset-month">
                <i class="fas fa-calendar-alt"></i> Bu Ayı Göster
            </a>
        </form>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Toplam Ciro (Brüt)</h3>
                <span class="value"><?php echo number_format($ozet['toplam_ciro'] ?: 0, 2, ',', '.'); ?> ₺</span>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <h3>Net Kazanç (Komisyon)</h3>
                <span class="value"
                    style="color: #10b981;"><?php echo number_format($ozet['toplam_kazanc'] ?: 0, 2, ',', '.'); ?>
                    ₺</span>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <h3>Poliçe Adetleri</h3>
                <span class="value"><?php echo $ozet['toplam_adet'] ?: 0; ?> Adet</span>
                <div class="type-list">
                    <?php foreach ($tur_dagilimi as $t): ?>
                        <span class="type-badge"><?php echo strtoupper($t['sigorta_turu']); ?>:
                            <?php echo $t['adet']; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="color: #e53e3e;">
                <span><i class="fas fa-exclamation-triangle"></i> Yenilemesi Yaklaşan Poliçeler (Son 14 Gün)</span>
                <span class="badge"
                    style="background:#fed7d7; color:#c53030; padding:2px 8px; border-radius:10px; font-size:12px;">
                    <?php echo count($yaklasan_policeler); ?> Kayıt
                </span>
            </div>

            <div class="scrollable-table">
                <table class="sticky-header">
                    <thead>
                        <tr>
                            <th>Müşteri</th>
                            <th>Telefon</th>
                            <th>Poliçe Türü</th>
                            <th>Bitiş Tarihi</th>
                            <th>Kalan Gün</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($yaklasan_policeler as $p):
                            // --- WHATSAPP VERI HAZIRLAMA ---
                            $temiz_tel = preg_replace('/[^0-9]/', '', $p['telefon']);
                            if (substr($temiz_tel, 0, 1) == '0')
                                $temiz_tel = "90" . substr($temiz_tel, 1);

                            // Dinamik ifade belirleme
                            $tur = strtoupper($p['sigorta_turu']);
                            $plaka = strtoupper($p['plaka']);

                            if ($tur == 'TRAFIK' || $tur == 'TRAFİK' || $tur == 'KASKO') {
                                $nesne_ifadesi = "$plaka plakalı aracınızın $tur poliçesi";
                            } else {
                                $nesne_ifadesi = "$tur poliçeniz";
                            }

                            $mesaj = "Sayın " . $p['ad_soyad'] . ", " . $nesne_ifadesi . " " . date('d.m.Y', strtotime($p['bitis_tarihi'])) . " tarihinde sona erecektir. Yenileme teklifi almak için bizimle iletişime geçebilirsiniz.";
                            $wa_url = "https://wa.me/" . $temiz_tel . "?text=" . urlencode($mesaj);
                            ?>
                            <tr class="alert-row">
                                <td><strong><?php echo $p['ad_soyad']; ?></strong></td>
                                <td><?php echo $p['telefon']; ?></td>
                                <td><?php echo strtoupper($p['sigorta_turu']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($p['bitis_tarihi'])); ?></td>
                                <td><span style="color:red; font-weight:bold;"><?php echo $p['kalan_gun']; ?> Gün</span>
                                </td>
                                <td>
                                    <a href="<?php echo $wa_url; ?>" target="_blank"
                                        style="color: #25D366; text-decoration:none; font-weight:bold; margin-right:15px;">
                                        <i class="fab fa-whatsapp"></i> Hatırlat
                                    </a>

                                    <a href="index.php?tamamla=<?php echo $p['id']; ?>&bas_tarih=<?php echo $bas_tarih; ?>&bit_tarih=<?php echo $bit_tarih; ?>"
                                        onclick="return confirm('Müşteriye ulaşıldı olarak işaretlensin mi?')"
                                        style="color: #10b981; text-decoration:none; font-weight:bold;">
                                        <i class="fas fa-check"></i> Ulaşıldı
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach;
                        if (empty($yaklasan_policeler))
                            echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#94a3b8;'>Yakın tarihte bitecek poliçe bulunamadı.</td></tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-history"></i> En Son Eklenen 5 Poliçe</span>
                <a href="tum_policeler.php" style="font-size:12px; color:var(--navy);">Tümünü Gör</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>TC No</th>
                        <th>Müşteri Ad Soyad</th>
                        <th>Sigorta Türü</th>
                        <th>Kayıt Tarihi</th>
                        <th>Tutar</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $liste = $pdo->query("SELECT p.*, m.tc_no, m.ad_soyad FROM policeler p 
                                     JOIN musteriler m ON p.musteri_id = m.id 
                                     ORDER BY p.id DESC LIMIT 5");
                    while ($row = $liste->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $row['tc_no']; ?></td>
                            <td><strong><?php echo $row['ad_soyad']; ?></strong></td>
                            <td><?php echo strtoupper($row['sigorta_turu']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($row['yapilma_tarihi'])); ?></td>
                            <td><?php echo number_format($row['prim_tutari'], 2, ',', '.'); ?> ₺</td>
                            <td>
                                <a href="musteri_gecmis.php?tc=<?php echo $row['tc_no']; ?>"
                                    style="color: var(--navy); text-decoration:none;">
                                    <i class="fas fa-history"></i> Geçmiş
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>