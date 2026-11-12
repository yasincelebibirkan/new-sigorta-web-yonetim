<?php
include 'auth.php';
include '../db.php';
?>

<?php
include '../db.php';

$tc = $_GET['tc'] ?? '';

if (!$tc) {
    header("Location: tum_policeler.php");
    exit;
}

// 1. SAYFALAMA AYARLARI
$limit = 10; // Her sayfada kaç kayıt gösterilecek
$sayfa = isset($_GET['s']) ? (int) $_GET['s'] : 1;
if ($sayfa < 1)
    $sayfa = 1;
$offset = ($sayfa - 1) * $limit;

// 2. TOPLAM KAYIT SAYISINI BUL (Sayfa sayısını hesaplamak için)
$toplam_sorgu = $pdo->prepare("SELECT COUNT(*) FROM policeler p JOIN musteriler m ON p.musteri_id = m.id WHERE m.tc_no = ?");
$toplam_sorgu->execute([$tc]);
$toplam_kayit = $toplam_sorgu->fetchColumn();
$toplam_sayfa = ceil($toplam_kayit / $limit);

// 3. MÜŞTERİ BİLGİSİNİ VE POLİÇELERİ LİMİTLİ ÇEK
$sorgu = $pdo->prepare("SELECT p.*, m.ad_soyad, m.telefon 
                        FROM policeler p 
                        JOIN musteriler m ON p.musteri_id = m.id 
                        WHERE m.tc_no = :tc 
                        ORDER BY p.id DESC 
                        LIMIT :limit OFFSET :offset");

// Parametreleri tek tek bağlıyoruz (BindValue kullanmak LIMIT için daha güvenlidir)
$sorgu->bindValue(':tc', $tc, PDO::PARAM_STR);
$sorgu->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
$sorgu->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

$sorgu->execute(); // Artık execute içine dizi vermiyoruz çünkü bindValue ile bağladık
$gecmis = $sorgu->fetchAll();

// Müşteri genel bilgisi için ilk kaydı alalım
$musteri = $gecmis[0] ?? null;
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Müşteri Geçmişi | <?php echo $tc; ?></title>
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
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header-card {
            background: var(--navy);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 13px;
        }

        td {
            padding: 15px;
            border-top: 1px solid var(--border);
            font-size: 14px;
        }

        /* Sayfalama Butonları */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 25px;
        }

        .page-link {
            padding: 8px 16px;
            background: white;
            border: 1px solid var(--border);
            text-decoration: none;
            color: var(--navy);
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .page-link.active {
            background: var(--navy);
            color: white;
            border-color: var(--navy);
        }

        .page-link:hover:not(.active) {
            background: #f1f5f9;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .bg-blue {
            background: #e0e7ff;
            color: #4338ca;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="index.php"
            style="text-decoration:none; color:var(--navy); font-weight:bold; display:block; margin-bottom:15px;">
            <i class="fas fa-arrow-left"></i> Paneli Geri Dön
        </a>

        <?php if ($musteri): ?>
            <div class="header-card">
                <h2 style="margin:0;"><?php echo $musteri['ad_soyad']; ?></h2>
                <p style="margin:5px 0 0 0; opacity:0.8;">TC: <?php echo $tc; ?> | Tel: <?php echo $musteri['telefon']; ?>
                </p>
                <p style="margin:10px 0 0 0; font-size:13px;"><i class="fas fa-file-invoice"></i> Toplam
                    <?php echo $toplam_kayit; ?> Poliçe Bulundu
                </p>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Kayıt Tarihi</th>
                            <th>Poliçe Türü</th>
                            <th>Poliçe No</th>
                            <th>Plaka</th>
                            <th>Tutar</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gecmis as $p): ?>
                            <tr>
                                <td><?php echo date('d.m.Y', strtotime($p['yapilma_tarihi'])); ?></td>
                                <td><span class="badge bg-blue"><?php echo strtoupper($p['sigorta_turu']); ?></span></td>
                                <td><?php echo $p['police_no']; ?></td>
                                <td><strong><?php echo $p['plaka']; ?></strong></td>
                                <td style="font-weight:bold;"><?php echo number_format($p['prim_tutari'], 2, ',', '.'); ?> ₺
                                </td>
                                <td>
                                    <a href="police_duzenle.php?id=<?php echo $p['id']; ?>" style="color:var(--navy);"><i
                                            class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($toplam_sayfa > 1): ?>
                <div class="pagination"
                    style="margin: 30px 0; text-align: center; display: flex; justify-content: center; align-items: center; gap: 5px;">
                    <?php
                    $gorunur_sayfa = 2; // Aktif sayfanın sağında ve solunda görünecek sayı miktarı
            
                    // 1. İLK SAYFA VE GERİ BUTONU
                    if ($sayfa > 1): ?>
                        <a href="?tc=<?php echo $tc; ?>&s=1" class="page-link" title="İlk Sayfa">« İlk</a>
                        <a href="?tc=<?php echo $tc; ?>&s=<?php echo $sayfa - 1; ?>" class="page-link"><i
                                class="fas fa-chevron-left"></i></a>
                    <?php endif;

                    // 2. SAYFA NUMARALARI DÖNGÜSÜ
                    for ($i = 1; $i <= $toplam_sayfa; $i++):
                        // Sadece ilk, son ve aktif sayfanın yakınlarını göster (Sliding Window Jargonu)
                        if ($i == 1 || $i == $toplam_sayfa || ($i >= $sayfa - $gorunur_sayfa && $i <= $sayfa + $gorunur_sayfa)): ?>
                            <a href="?tc=<?php echo $tc; ?>&s=<?php echo $i; ?>"
                                class="page-link <?php echo ($i == $sayfa) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php
                            // Aralara üç nokta "..." koyma mantığı
                        elseif ($i == $sayfa - $gorunur_sayfa - 1 || $i == $sayfa + $gorunur_sayfa + 1): ?>
                            <span style="padding: 8px; color: #94a3b8;">...</span>
                        <?php endif;
                    endfor;

                    // 3. İLERİ VE SON SAYFA BUTONU
                    if ($sayfa < $toplam_sayfa): ?>
                        <a href="?tc=<?php echo $tc; ?>&s=<?php echo $sayfa + 1; ?>" class="page-link"><i
                                class="fas fa-chevron-right"></i></a>
                        <a href="?tc=<?php echo $tc; ?>&s=<?php echo $toplam_sayfa; ?>" class="page-link" title="Son Sayfa">Son
                            »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="header-card" style="background:#f59e0b; text-align:center;">
                <h2>Kayıt Bulunamadı</h2>
                <p>Bu müşteriye ait henüz bir poliçe kaydı girilmemiş.</p>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>