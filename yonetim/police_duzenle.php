<?php
include 'auth.php';
include '../db.php';

$return_url = $_GET['return_url'] ?? 'tum_policeler.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: tum_policeler.php");
    exit;
}

// 1. Mevcut Bilgileri Çek
$sorgu = $pdo->prepare("SELECT p.*, m.ad_soyad, m.tc_no, m.telefon, m.dogum_tarihi, m.id as musteri_id 
                        FROM policeler p 
                        JOIN musteriler m ON p.musteri_id = m.id 
                        WHERE p.id = ?");
$sorgu->execute([$id]);
$p = $sorgu->fetch();

if (!$p) {
    die("Poliçe bulunamadı.");
}

// 2. KAYDET BUTONUNA BASILDIĞINDA
// 2. KAYDET BUTONUNA BASILDIĞINDA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $sigorta_turu = $_POST['sigorta_turu'];
        // Poliçe tutarındaki noktayı sil, virgülü noktaya çevir (4.500,00 -> 4500.00)
        $_POST['prim_tutari'] = str_replace(['.', ','], ['', '.'], $_POST['prim_tutari']);

        // ---Aracı acente bilgisini al ---
        $araci_acente = !empty($_POST['araci_acente']) ? trim($_POST['araci_acente']) : null;

        $ayar_anahtar = "komisyon_" . strtolower($sigorta_turu);
        $ayar_sorgu = $pdo->prepare("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = ?");
        $ayar_sorgu->execute([$ayar_anahtar]);
        $yeni_oran = $ayar_sorgu->fetchColumn();

        if ($yeni_oran === false) {
            $yeni_oran = $p['komisyon_orani'];
        }

        // Müşteri Güncelleme aynı kalıyor...
        $m_update = $pdo->prepare("UPDATE musteriler SET ad_soyad = ?, tc_no = ?, telefon = ?, dogum_tarihi = ? WHERE id = ?");
        $m_update->execute([$_POST['ad_soyad'], $_POST['tc_no'], $_POST['telefon'], $_POST['dogum_tarihi'], $p['musteri_id']]);

        // --- BURAYI GÜNCELLE: araci_acente sütununu ekledik ---
        $p_update = $pdo->prepare("UPDATE policeler SET 
            police_no = ?, sigorta_sirketi = ?, sigorta_turu = ?, prim_tutari = ?, 
            baslangic_tarihi = ?, bitis_tarihi = ?, odeme_sekli = ?, plaka = ?, 
            ruhsat_seri = ?, yapilma_tarihi = ?, komisyon_orani = ?, araci_acente = ?
            WHERE id = ?");

        $p_update->execute([
            $_POST['police_no'],
            $_POST['sigorta_sirketi'],
            $sigorta_turu,
            $_POST['prim_tutari'],
            $_POST['baslangic_tarihi'],
            $_POST['bitis_tarihi'],
            $_POST['odeme_sekli'],
            $_POST['plaka'],
            $_POST['ruhsat_seri'],
            $_POST['yapilma_tarihi'],
            $yeni_oran,
            $araci_acente, // Yeni değişken
            $id
        ]);

        $pdo->commit();
        header("Location: " . $return_url);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Hata oluştu: " . $e->getMessage());
    }
}

// Sigorta Türlerini Çek
$turler = $pdo->query("SELECT ayar_anahtar FROM ayarlar WHERE ayar_anahtar LIKE 'komisyon_%'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Poliçe Düzenle | New Sigorta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a3a6e;
            --bg: #f8fafc;
            --border: #cbd5e1;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            padding: 20px;
            color: #334155;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: var(--navy);
            margin-top: 0;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            margin: 25px 0 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full {
            grid-column: span 2;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        input,
        select {
            padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 4px rgba(26, 58, 110, 0.08);
        }

        .btn-save {
            background: var(--navy);
            color: white;
            border: none;
            padding: 16px;
            width: 100%;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 30px;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: #122b52;
            transform: translateY(-2px);
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card">
            <form method="POST">
                <h2><i class="fas fa-edit"></i> Poliçe Bilgilerini Güncelle</h2>

                <div class="section-title">Müşteri Bilgileri</div>
                <div class="grid">
                    <div class="full field">
                        <label>Müşteri Ad Soyad</label>
                        <input type="text" name="ad_soyad" value="<?php echo $p['ad_soyad']; ?>" required>
                    </div>
                    <div class="field">
                        <label>TC / Vergi No</label>
                        <input type="text" maxlength="11" name="tc_no" value="<?php echo $p['tc_no']; ?>" required>
                    </div>
                    <div class="field">
                        <label>Telefon</label>
                        <input type="text" name="telefon" value="<?php echo $p['telefon']; ?>">
                    </div>
                    <div class="full field">
                        <label>Doğum Tarihi</label>
                        <input type="date" name="dogum_tarihi" value="<?php echo $p['dogum_tarihi']; ?>">
                    </div>
                </div>

                <div class="section-title">Poliçe Teknik Detaylar</div>
                <div class="grid">
                    <div class="field">
                        <label>Sigorta Türü</label>
                        <select name="sigorta_turu">
                            <?php foreach ($turler as $t):
                                $tur_key = str_replace('komisyon_', '', $t['ayar_anahtar']);
                                $sel = ($p['sigorta_turu'] == $tur_key) ? 'selected' : ''; ?>
                                <option value="<?php echo $tur_key; ?>" <?php echo $sel; ?>>
                                    <?php echo strtoupper($tur_key); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Poliçe No</label>
                        <input type="text" name="police_no" value="<?php echo $p['police_no']; ?>">
                    </div>
                    <div class="field">
                        <label>Başlangıç Tarihi</label>
                        <input type="date" name="baslangic_tarihi" value="<?php echo $p['baslangic_tarihi']; ?>"
                            required>
                    </div>
                    <div class="field">
                        <label>Bitiş Tarihi</label>
                        <input type="date" name="bitis_tarihi" value="<?php echo $p['bitis_tarihi']; ?>" required>
                    </div>
                    <div class="full field">
                        <label>Sigorta Şirketi</label>
                        <input type="text" name="sigorta_sirketi" value="<?php echo $p['sigorta_sirketi']; ?>">
                    </div>
                    <div class="field">
                        <label>Aracı Acente (Opsiyonel)</label>
                        <input type="text" name="araci_acente"
                            value="<?php echo htmlspecialchars($p['araci_acente'] ?? ''); ?>"
                            placeholder="Varsa aracı acente adı">
                    </div>
                </div>

                <div class="section-title">Ödeme & Araç Bilgileri</div>
                <div class="grid">
                    <div class="field">
                        <label>Ödeme Şekli</label>
                        <select name="odeme_sekli">
                            <option value="Peşin" <?php echo $p['odeme_sekli'] == 'Peşin' ? 'selected' : ''; ?>>Peşin
                            </option>
                            <option value="2 Taksit" <?php echo $p['odeme_sekli'] == '2 Taksit' ? 'selected' : ''; ?>>2
                                Taksit</option>
                            <option value="3 Taksit" <?php echo $p['odeme_sekli'] == '3 Taksit' ? 'selected' : ''; ?>>3
                                Taksit</option>
                            <option value="4 Taksit" <?php echo $p['odeme_sekli'] == '4 Taksit' ? 'selected' : ''; ?>>4
                                Taksit</option>
                            <option value="6 Taksit" <?php echo $p['odeme_sekli'] == '6 Taksit' ? 'selected' : ''; ?>>6
                                Taksit</option>
                            <option value="9 Taksit" <?php echo $p['odeme_sekli'] == '9 Taksit' ? 'selected' : ''; ?>>9
                                Taksit</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Poliçe Tutarı (₺)</label>
                        <input type="text" name="prim_tutari"
                            value="<?php echo number_format($p['prim_tutari'], 2, ',', '.'); ?>"
                            style="font-weight: bold; border-color: var(--navy);" id="prim_input"
                            oninput="formatPara(this)">
                    </div>
                    <div class="field">
                        <label>Araç Plaka</label>
                        <input type="text" name="plaka" value="<?php echo $p['plaka']; ?>">
                    </div>
                    <div class="field">
                        <label>Ruhsat Seri No</label>
                        <input type="text" name="ruhsat_seri" value="<?php echo $p['ruhsat_seri']; ?>">
                    </div>
                </div>

                <div class="section-title">Kayıt Tarihi</div>
                <div class="field">
                    <input type="date" name="yapilma_tarihi" value="<?php echo $p['yapilma_tarihi']; ?>">
                </div>

                <button type="submit" class="btn-save"><i class="fas fa-save"></i> DEĞİŞİKLİKLERİ KAYDET</button>
                <a href="<?php echo htmlspecialchars($return_url); ?>" class="btn-cancel">İPTAL</a>
            </form>
        </div>
    </div>

</body>


<script>
    function formatPara(input) {
        // Sadece rakamları al
        let value = input.value.replace(/\D/g, "");

        // Eğer boşsa boş bırak
        if (value === "") return;

        // Kuruşlandırma yap ve formatla
        value = (parseInt(value) / 100).toFixed(2);
        let parts = value.split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        input.value = parts.join(",");
    }
</script>


</html>