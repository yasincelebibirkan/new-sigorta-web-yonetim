<?php
include 'auth.php';
include '../db.php';

// 1. Filtreleme Parametreleri
$tum_zamanlar = isset($_GET['tum_zamanlar']);
$bas_tarih = $_GET['bas_tarih'] ?? date('Y-m-01');
$bit_tarih = $_GET['bit_tarih'] ?? date('Y-m-d');
$tur_filtre = $_GET['tur'] ?? '';
$arama = $_GET['search'] ?? '';

// --- PAGINATION (SAYFALAMA) HESAPLAMALARI ---
$kayit_basina = 10;
$sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
if ($sayfa < 1)
    $sayfa = 1;
$offset = ($sayfa - 1) * $kayit_basina;

// 2. Dinamik SQL Sorgusu Oluşturma (Base Sorgu)
$where_sql = " WHERE 1=1";
$params = [];

if (!$tum_zamanlar) {
    $where_sql .= " AND (p.yapilma_tarihi BETWEEN :bas AND :bit 
                   OR p.baslangic_tarihi BETWEEN :bas AND :bit)";
    $params['bas'] = $bas_tarih;
    $params['bit'] = $bit_tarih;
}

if ($tur_filtre) {
    $where_sql .= " AND p.sigorta_turu = :tur";
    $params['tur'] = $tur_filtre;
}

if ($arama) {
    $where_sql .= " AND (m.ad_soyad LIKE :search 
                   OR m.tc_no LIKE :search 
                   OR p.police_no LIKE :search 
                   OR p.ruhsat_seri LIKE :search 
                   OR REPLACE(p.plaka, ' ', '') LIKE :search_clean 
                   OR p.plaka LIKE :search 
                   OR m.telefon LIKE :search)";
    $params['search'] = "%$arama%";
    $params['search_clean'] = "%" . str_replace(' ', '', $arama) . "%";
}

// 3. TOPLAM KAYIT SAYISINI BUL (Kaç sayfa yapacağımızı bilmek için)
$count_sql = "SELECT COUNT(*) FROM policeler p JOIN musteriler m ON p.musteri_id = m.id" . $where_sql;
$count_sorgu = $pdo->prepare($count_sql);
$count_sorgu->execute($params);
$toplam_kayit = $count_sorgu->fetchColumn();
$toplam_sayfa = ceil($toplam_kayit / $kayit_basina);

// 4. VERİLERİ ÇEK (LIMIT ve OFFSET ile sadece 25 taneyi alıyoruz)
$sql = "SELECT p.*, m.ad_soyad, m.tc_no, m.telefon 
        FROM policeler p 
        JOIN musteriler m ON p.musteri_id = m.id " .
    $where_sql .
    " ORDER BY p.id DESC LIMIT $kayit_basina OFFSET $offset";

$sorgu = $pdo->prepare($sql);
$sorgu->execute($params);
$policeler = $sorgu->fetchAll();

// Orijinal kodundaki ComboBox'ı dolduran kısım:
$turler = $pdo->query("SELECT ayar_anahtar FROM ayarlar WHERE ayar_anahtar LIKE 'komisyon_%'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Poliçe Portföyü | New Sigorta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a3a6e;
            --light: #f8fafc;
            --success: #2ecc71;
            --border: #d1d9e6;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--light);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Geri Dön Butonu Stili */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--navy);
            font-weight: 700;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn-back:hover {
            color: #000;
            transform: translateX(-5px);
        }

        .filter-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 25px;
            align-items: end;
        }

        .filter-grid label {
            font-weight: bold;
            color: var(--navy);
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .filter-grid input,
        .filter-grid select {
            padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            width: 100%;
            font-size: 14px;
            outline: none;
        }

        .btn-filter {
            background: var(--navy);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-reset {
            background: #e2e8f0;
            color: #475569;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .table-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        th {
            background: #f1f5f9;
            color: var(--navy);
            padding: 18px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 15px 18px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .small-text {
            color: #64748b;
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }

        .bold-data {
            font-weight: 700;
            color: #1e293b;
            font-size: 16px;
            display: block;
        }

        .badge {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

        .type-badge {
            background: #e0e7ff;
            color: #4338ca;
        }

        .company-badge {
            background: #fef3c7;
            color: #92400e;
        }

        .price-text {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
        }

        .kar-text {
            color: var(--success);
            font-weight: 800;
            font-size: 16px;
        }

        /* Bu sınıf her zaman her şeyi içinde tam ortalar */
        .tum-zamanlar-box {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            height: 45px;
            padding: 0 15px;
            min-width: 140px;
            margin-right: 15px;
            cursor: pointer;
        }

        .tum-zamanlar-box input {
            margin: 0 !important;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .tum-zamanlar-box label {
            margin: 0 !important;
            cursor: pointer;
            color: #2c3e50;
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="top-nav">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
            </a>
            <div style="display: flex; gap: 15px;">
                <a href="police_ekle.php"
                    style="background: var(--success); color: white; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);">
                    <i class="fas fa-plus"></i> Yeni Poliçe Kaydı
                </a>
            </div>
        </div>

        <h2 style="color: var(--navy); margin-bottom: 25px; font-size: 28px;">
            <i class="fas fa-file-invoice-dollar"></i> Tüm Poliçeler
        </h2>

        <div class="filter-card">
            <form method="GET" class="filter-grid">
                <div>
                    <label>Başlangıç Tarihi</label>
                    <input type="date" name="bas_tarih" value="<?php echo $bas_tarih; ?>">
                </div>
                <div>
                    <label>Bitiş Tarihi</label>
                    <input type="date" name="bit_tarih" value="<?php echo $bit_tarih; ?>">
                </div>

                <div>
                    <label>Sigorta Türü</label>
                    <select name="tur">
                        <option value="">Tüm Türler</option>
                        <?php foreach ($turler as $t):
                            $val = str_replace('komisyon_', '', $t['ayar_anahtar']); ?>
                            <option value="<?php echo $val; ?>" <?php echo $tur_filtre == $val ? 'selected' : ''; ?>>
                                <?php echo strtoupper($val); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Hızlı Arama</label>
                    <input type="text" name="search" value="<?php echo $arama; ?>"
                        placeholder="İsim, Plaka, TC veya No...">
                </div>
                <div class="tum-zamanlar-box">
                    <input type="checkbox" name="tum_zamanlar" id="tz" value="1" <?php echo isset($_GET['tum_zamanlar']) ? 'checked' : ''; ?>>
                    <label for="tz">Tüm Zamanlar</label>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-filter" style="flex: 3;">
                        <i class="fas fa-search"></i> LİSTELE
                    </button>
                    <a href="tum_policeler.php" class="btn-reset" style="flex: 1;" title="Filtreleri Sıfırla">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Geçerlilik Tarihleri</th>
                        <th>Müşteri Bilgisi</th>
                        <th>Poliçe / Araç Detay</th>
                        <th>Şirket & Ödeme</th>
                        <th>Poliçe Tutarı</th>
                        <th>Kâr Tutarı</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($policeler as $row):
                        $kar_tutari = ($row['prim_tutari'] * $row['komisyon_orani']) / 100;
                        ?>
                        <tr>
                            <td>
                                <span class="bold-data">Bşl:
                                    <?php echo date('d.m.Y', strtotime($row['baslangic_tarihi'])); ?></span>
                                <span class="small-text">Bit:
                                    <b><?php echo $row['bitis_tarihi'] ? date('d.m.Y', strtotime($row['bitis_tarihi'])) : 'Veri Yok'; ?></b></span>
                                <div style="margin-top: 5px; padding-top: 5px; border-top: 1px dashed #eee;">
                                    <span class="small-text" style="color: #7f8c8d;">
                                        <i class="fas fa-calendar-plus" style="font-size: 10px;"></i> Düzenleme:
                                        <?php echo date('d.m.Y', strtotime($row['yapilma_tarihi'])); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--navy); font-size: 16px;">
                                    <?php echo $row['ad_soyad']; ?>
                                </div>
                                <span class="small-text"><b>TC:</b> <?php echo $row['tc_no']; ?></span>
                                <span class="small-text"><b>Tel:</b> <?php echo $row['telefon'] ?: '---'; ?></span>
                            </td>
                            <td style="min-width: 220px; padding: 15px; vertical-align: top;">
                                <div
                                    style="font-weight: 800; color: #2c3e50; font-size: 17px; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-shield-alt" style="color: #e74c3c;"></i>
                                    <?php echo $row['sigorta_turu']; ?>
                                </div>

                                <?php
                                // Sigorta türüne göre plaka arka plan rengini belirliyoruz
                                $plaka_bg = ($row['sigorta_turu'] == 'KASKO') ? '#e3f2fd' : '#f5f5f5';
                                $plaka_border = ($row['sigorta_turu'] == 'KASKO') ? '#2196f3' : '#9e9e9e';
                                ?>
                                <div style="font-weight: 800; color: #1a1a1a; font-size: 16px; background: <?php echo $plaka_bg; ?>; 
                display: inline-block; padding: 4px 12px; border-radius: 6px; 
                border-left: 5px solid <?php echo $plaka_border; ?>; box-shadow: 2px 2px 5px rgba(0,0,0,0.05);
                letter-spacing: 1px; font-family: 'Roboto Mono', monospace;">
                                    <?php echo $row['plaka']; ?>
                                </div>

                                <div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid #eee;">
                                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                        <span
                                            style="background: #2c3e50; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-right: 8px; font-weight: 700;">POLİÇE
                                            NO</span>
                                        <span
                                            style="font-size: 15px; color: #2c3e50; font-weight: 700; letter-spacing: 0.5px;">
                                            <?php echo $row['police_no']; ?>
                                        </span>
                                    </div>

                                    <div style="display: flex; align-items: center;">
                                        <span
                                            style="background: #95a5a6; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-right: 8px; font-weight: 700;">RUHSAT
                                            SERİ</span>
                                        <span style="font-size: 14px; color: #7f8c8d; font-weight: 600;">
                                            <?php echo $row['ruhsat_seri'] ?: '---'; ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge company-badge"><?php echo $row['sigorta_sirketi'] ?: 'Veri Yok'; ?></span>

                                <?php if (!empty($row['araci_acente'])): ?>
                                    <div style="margin-top: 5px; color: #2980b9; font-size: 12px; font-weight: 700;">
                                        <i class="fas fa-handshake"></i> Aracı:
                                        <?php echo mb_convert_case($row['araci_acente'], MB_CASE_TITLE, "UTF-8"); ?>
                                    </div>
                                <?php endif; ?>

                                <span class="small-text"
                                    style="display: block; margin-top: 3px;"><?php echo $row['odeme_sekli'] ?: 'Peşin'; ?></span>
                            </td>
                            <td class="price-text"><?php echo number_format($row['prim_tutari'], 2, ',', '.'); ?> ₺</td>
                            <td>
                                <div class="kar-text"><?php echo number_format($kar_tutari, 2, ',', '.'); ?> ₺</div>
                                <span class="small-text">%<?php echo $row['komisyon_orani']; ?> Komisyon</span>
                            </td>
                            <td>
                                <a href="police_duzenle.php?id=<?php echo $row['id']; ?>&return_url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                    style="color: #3498db; font-size: 20px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="policeSil(<?php echo $row['id']; ?>)"
                                    style="color: #e74c3c; font-size: 20px; margin-left: 10px;">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination"
            style="margin: 30px 0; text-align: center; display: flex; justify-content: center; align-items: center; gap: 5px;">
            <?php
            $gorunur_sayfa = 2; // Mevcut sayfanın sağında ve solunda kaç numara görünsün?
            
            // Geri butonu
            if ($sayfa > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => 1])); ?>"
                    style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: var(--navy); font-weight: bold;">«
                    İlk</a>
            <?php endif;

            for ($i = 1; $i <= $toplam_sayfa; $i++):
                // Sadece ilk sayfa, son sayfa ve mevcut sayfanın yakınlarını göster
                if ($i == 1 || $i == $toplam_sayfa || ($i >= $sayfa - $gorunur_sayfa && $i <= $sayfa + $gorunur_sayfa)): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => $i])); ?>"
                        style="padding: 8px 14px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; font-weight: bold; <?php echo ($i == $sayfa) ? 'background: var(--navy); color: #fff; border-color: var(--navy);' : 'color: var(--navy);'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php elseif ($i == $sayfa - $gorunur_sayfa - 1 || $i == $sayfa + $gorunur_sayfa + 1): ?>
                    <span style="padding: 8px;">...</span>
                <?php endif;
            endfor;

            // İleri butonu
            if ($sayfa < $toplam_sayfa): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => $toplam_sayfa])); ?>"
                    style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: var(--navy); font-weight: bold;">Son
                    »</a>
            <?php endif; ?>
        </div>
    </div>




    <script>
        function policeSil(id) {
            if (confirm("Bu poliçeyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz!")) {
                window.location.href = "police_sil.php?id=" + id + "&return_url=" + encodeURIComponent(window.location.href);
            }
        }
    </script>




</body>

</html>