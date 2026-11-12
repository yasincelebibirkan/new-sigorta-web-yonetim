<?php
include 'auth.php';
include '../db.php';
$turler = $pdo->query("SELECT ayar_anahtar FROM ayarlar WHERE ayar_anahtar LIKE 'komisyon_%'")->fetchAll(PDO::FETCH_ASSOC);

if ($_POST) {
    // 1. Verileri Al
    $tc_no = $_POST['tc_no'];

    // AD SOYAD FORMATLAMA: Baş harfler büyük, gerisi küçük (Türkçe Karakter Uyumlu)
    $ad_soyad = mb_convert_case(trim($_POST['ad_soyad']), MB_CASE_TITLE, "UTF-8");

    $telefon = $_POST['telefon'];
    $dogum_tarihi = $_POST['dogum_tarihi'];
    $sigorta_turu = $_POST['sigorta_turu'];

    // ARACI ACENTE FORMATLAMA: Eğer girildiyse baş harfleri büyüt
    $araci_acente = !empty($_POST['araci_acente'])
        ? mb_convert_case(trim($_POST['araci_acente']), MB_CASE_TITLE, "UTF-8")
        : null;

    // Tutar Temizliği
    $prim_tutari = str_replace(['.', ','], ['', '.'], $_POST['prim_tutari']);

    // Plaka Formatlama
    $raw_plaka = strtoupper(str_replace(' ', '', $_POST['plaka']));
    $plaka = preg_replace('/(\d{2})([A-Z]{1,3})(\d{2,4})/', '$1 $2 $3', $raw_plaka);

    // 2. Komisyon Oranı
    $ayar_anahtar = "komisyon_" . $sigorta_turu;
    $ayar_sorgu = $pdo->prepare("SELECT ayar_deger FROM ayarlar WHERE ayar_anahtar = ?");
    $ayar_sorgu->execute([$ayar_anahtar]);
    $komisyon_orani = $ayar_sorgu->fetchColumn() ?: 0;

    // 3. Müşteri İşlemleri
    $check = $pdo->prepare("SELECT id FROM musteriler WHERE tc_no = ?");
    $check->execute([$tc_no]);
    $existing = $check->fetch();

    if ($existing) {
        $musteri_id = $existing['id'];
        // Müşteri zaten varsa bilgilerini güncelle (Ad soyad burada da formatlanmış haliyle gider)
        $pdo->prepare("UPDATE musteriler SET ad_soyad = ?, telefon = ?, dogum_tarihi = ? WHERE id = ?")
            ->execute([$ad_soyad, $telefon, $dogum_tarihi, $musteri_id]);
    } else {
        // Yeni müşteri ise formatlanmış isimle kaydet
        $stmt = $pdo->prepare("INSERT INTO musteriler (tc_no, ad_soyad, telefon, dogum_tarihi) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tc_no, $ad_soyad, $telefon, $dogum_tarihi]);
        $musteri_id = $pdo->lastInsertId();
    }

    // 4. Poliçe Kaydı
    $sql = "INSERT INTO policeler 
    (musteri_id, yapilma_tarihi, sigorta_turu, ruhsat_seri, plaka, police_no, 
     sigorta_sirketi, odeme_sekli, baslangic_tarihi, bitis_tarihi, prim_tutari, 
     komisyon_orani, araci_acente) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $musteri_id,
        $_POST['yapilma_tarihi'],
        $sigorta_turu,
        $_POST['ruhsat_seri'],
        $plaka,
        $_POST['police_no'],
        $_POST['sigorta_sirketi'],
        $_POST['odeme_sekli'],
        $_POST['baslangic_tarihi'],
        $_POST['bitis_tarihi'],
        $prim_tutari,
        $komisyon_orani,
        $araci_acente // Formatlanmış acente ismi
    ]);

    echo "<script>alert('Poliçe Başarıyla Kaydedildi!'); window.location='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>New Sigorta | Poliçe Ekle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a3a6e;
            --bg: #f8fafc;
            --border: #cbd5e1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 20px;
            color: #334155;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--navy);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(26, 58, 110, 0.1);
            border: 1px solid #e2e8f0;
        }

        h2 {
            color: var(--navy);
            margin: 0 0 30px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header {
            font-size: 13px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 25px 0 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .form-grid {
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
            background: #fff;
        }

        input:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 4px rgba(26, 58, 110, 0.08);
        }

        .price-field {
            font-size: 18px;
            font-weight: 700;
            color: var(--navy);
            border: 2px solid var(--navy);
            background: #f0f4f8;
        }

        .submit-btn {
            background: var(--navy);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
            box-shadow: 0 10px 15px -3px rgba(26, 58, 110, 0.2);
        }

        .date-info {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--navy);
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-chevron-left"></i> Geri Dön
        </a>

        <div class="form-card">
            <form method="POST" id="policeForm">
                <h2><i class="fas fa-file-signature"></i> Yeni Poliçe Girişi</h2>

                <div class="section-header">Müşteri Bilgileri</div>
                <div class="form-grid">
                    <div class="full field">
                        <label>Müşteri Ad Soyad</label>
                        <input type="text" name="ad_soyad" placeholder="Örn: Ayşenur Kurt Erol" required
                            style="text-transform: capitalize;">
                    </div>
                    <div class="field">
                        <label>TC / Vergi No</label>
                        <input type="text" name="tc_no" maxlength="11" placeholder="11 haneli TC No" required>
                    </div>
                    <div class="field">
                        <label>Telefon</label>
                        <input type="text" name="telefon" placeholder="05XX XXX XX XX">
                    </div>
                    <div class="full field">
                        <label>Doğum Tarihi</label>
                        <input type="date" name="dogum_tarihi" value="<?php echo $p['dogum_tarihi'] ?? ''; ?>"
                            min="1920-01-01" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="section-header">Poliçe Teknik Bilgileri</div>
                <div class="form-grid">
                    <div class="field">
                        <label>Sigorta Türü</label>
                        <select name="sigorta_turu">
                            <?php foreach ($turler as $t):
                                $temiz_ad = str_replace('komisyon_', '', $t['ayar_anahtar']); ?>
                                <option value="<?php echo $temiz_ad; ?>"><?php echo strtoupper($temiz_ad); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Poliçe Numarası</label>
                        <input type="text" name="police_no" placeholder="Örn: 181660565">
                    </div>
                    <div class="field">
                        <label>Başlangıç Tarihi</label>
                        <input type="date" name="baslangic_tarihi" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="field">
                        <label>Bitiş Tarihi</label>
                        <input type="date" name="bitis_tarihi"
                            value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                    </div>
                    <div class="full field">
                        <label>Sigorta Şirketi</label>
                        <input type="text" name="sigorta_sirketi"
                            placeholder="Örn: Anadolu Sigorta / Doğa / Ak Sigorta">
                    </div>
                    <div class="full field">
                        <label>Aracı Acente (Kota Paslaşması)</label>
                        <input type="text" name="araci_acente"
                            placeholder="Eğer iş başka bir acenteden paslandıysa buraya yazın">
                    </div>
                </div>

                <div class="section-header">Ödeme ve Araç Bilgileri</div>
                <div class="form-grid">
                    <div class="field">
                        <label>Ödeme Şekli</label>
                        <select name="odeme_sekli">
                            <option value="Peşin">Peşin</option>
                            <option value="2 Taksit">2 Taksit</option>
                            <option value="3 Taksit">3 Taksit</option>
                            <option value="4 Taksit">4 Taksit</option>
                            <option value="6 Taksit">6 Taksit</option>
                            <option value="9 Taksit">9 Taksit</option>
                            <option value="12 Taksit">12 Taksit</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Poliçe Toplam Tutarı (₺)</label>
                        <input type="text" name="prim_tutari" id="prim_tutari" class="price-field" placeholder="0,00"
                            required>
                    </div>
                    <div class="field">
                        <label>Araç Plaka</label>
                        <input type="text" name="plaka" id="plaka_input" placeholder="41 ADZ 543"
                            style="text-transform: uppercase;">
                    </div>
                    <div class="field">
                        <label>Ruhsat Seri No</label>
                        <input type="text" name="ruhsat_seri" placeholder="HP460509">
                    </div>
                </div>

                <div class="section-header">Sistem Kayıt Tarihi</div>
                <div class="date-info">
                    <label>Poliçenin Kesildiği Tarih</label>
                    <input type="date" name="yapilma_tarihi" value="<?php echo date('Y-m-d'); ?>"
                        style="width:100%; margin-top:8px; border:none; background:transparent; font-weight:bold;">
                    <p style="margin:8px 0 0 0; color:#64748b;">* Geçmiş bir poliçeyi giriyorsanız bu tarihi
                        değiştirebilirsiniz.</p>
                </div>

                <button type="submit" class="submit-btn">KAYDET</button>
            </form>
        </div>
    </div>

    <script>
        // PARA FORMATI MASKESİ (18.750,00 formatı)
        const primInput = document.getElementById('prim_tutari');
        primInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, "");
            if (value === "") { e.target.value = ""; return; }
            value = (value / 100).toFixed(2).replace(".", ",");
            value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            e.target.value = value;
        });

        // PLAKA FORMATLAMA (Sen yazarken görsel boşluk ekler)
        const plakaInput = document.getElementById('plaka_input');
        plakaInput.addEventListener('input', function (e) {
            let val = e.target.value.replace(/\s+/g, '').toUpperCase();
            let formatted = "";
            if (val.length > 2) {
                // İlk 2 rakamı ayır
                formatted = val.substring(0, 2) + " ";
                let rest = val.substring(2);
                // Harfleri bul
                let letterMatch = rest.match(/^[A-Z]+/);
                if (letterMatch) {
                    formatted += letterMatch[0] + " ";
                    formatted += rest.substring(letterMatch[0].length);
                } else {
                    formatted += rest;
                }
            } else {
                formatted = val;
            }
            e.target.value = formatted.trim();
        });
    </script>

</body>

</html>