<?php 
include 'auth.php'; 
include '../db.php'; 
?>

<?php
include '../db.php'; // Veritabanı bağlantısı

// 1. YENİ SİGORTA TÜRÜ EKLEME
if (isset($_POST['yeni_ekle'])) {
    $tur_adi = strtolower(trim($_POST['yeni_tur']));
    $oran = $_POST['yeni_oran'];
    $anahtar = "komisyon_" . str_replace(' ', '_', $tur_adi);

    $ekle = $pdo->prepare("INSERT IGNORE INTO ayarlar (ayar_anahtar, ayar_deger) VALUES (?, ?)");
    $ekle->execute([$anahtar, $oran]);
    $mesaj = "Yeni sigorta türü başarıyla eklendi!";
}

// 2. SİGORTA TÜRÜ SİLME
if (isset($_GET['sil'])) {
    $sil_id = $_GET['sil'];
    $pdo->prepare("DELETE FROM ayarlar WHERE id = ?")->execute([$sil_id]);
    header("Location: ayarlar.php?durum=silindi");
    exit;
}

// 3. MEVCUT ORANLARI GÜNCELLEME
if (isset($_POST['oran_guncelle'])) {
    foreach ($_POST['ayar'] as $id => $deger) {
        $pdo->prepare("UPDATE ayarlar SET ayar_deger = ? WHERE id = ?")->execute([$deger, $id]);
    }
    $mesaj = "Komisyon oranları güncellendi!";
}

// Tüm listeyi çek
$ayarlar = $pdo->query("SELECT * FROM ayarlar ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sigorta Türleri ve Komisyonlar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a3a6e;
            --gray: #7a7a7a;
            --danger: #e74c3c;
        }

        body {
            font-family: sans-serif;
            background: #f4f7fa;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        h2 {
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 0;
        }

        /* Formlar */
        .inline-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-add {
            background: var(--navy);
            color: white;
        }

        .btn-save {
            background: #2ecc71;
            color: white;
            width: 100%;
            margin-top: 15px;
        }

        /* Liste Tablosu */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #eee;
            color: var(--gray);
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .btn-delete {
            color: var(--danger);
            text-decoration: none;
            font-size: 18px;
        }

        .alert {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="index.php"
            style="text-decoration: none; color: var(--gray); margin-bottom: 20px; display: inline-block;"><i
                class="fas fa-arrow-left"></i> Geri Dön</a>

        <?php if (isset($mesaj)): ?>
            <div class="alert"><?php echo $mesaj; ?></div> <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> Yeni Sigorta Türü Ekle</h2>
            <form method="POST" class="inline-form">
                <input type="text" name="yeni_tur" placeholder="Örn: Konut Sigortası" required style="flex: 2;">
                <input type="number" name="yeni_oran" placeholder="Komisyon %" step="0.1" required style="flex: 1;">
                <button type="submit" name="yeni_ekle" class="btn btn-add">Ekle</button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-list"></i> Sigorta Türleri ve Oranlar</h2>
            <form method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Sigorta Türü</th>
                            <th>Komisyon (%)</th>
                            <th style="text-align: right;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ayarlar as $ayar): ?>
                            <tr>
                                <td><strong><?php echo str_replace(['komisyon_', '_'], ['', ' '], $ayar['ayar_anahtar']); ?></strong>
                                </td>
                                <td>
                                    <input type="number" name="ayar[<?php echo $ayar['id']; ?>]"
                                        value="<?php echo $ayar['ayar_deger']; ?>" step="0.1"
                                        style="width: 80px; padding: 5px;">
                                </td>
                                <td style="text-align: right;">
                                    <a href="?sil=<?php echo $ayar['id']; ?>" class="btn-delete"
                                        onclick="return confirm('Bu sigorta türünü silmek istediğinize emin misiniz?')"><i
                                            class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="oran_guncelle" class="btn btn-save">Tüm Oranları Güncelle</button>
            </form>
        </div>
    </div>

</body>

</html>