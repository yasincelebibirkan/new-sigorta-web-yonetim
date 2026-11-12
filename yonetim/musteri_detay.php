<?php 
include 'auth.php'; 
include '../db.php'; 
?>

<?php
include '../db.php';
$musteri_id = $_GET['id'];

// Müşteri bilgileri
$musteri = $pdo->prepare("SELECT * FROM musteriler WHERE id = ?");
$musteri->execute([$musteri_id]);
$m = $musteri->fetch();

// Poliçe geçmişi
$policeler = $pdo->prepare("
    SELECT p.* FROM policeler p 
    JOIN musteriler m ON p.musteri_id = m.id 
    WHERE m.tc_no = ? 
    ORDER BY p.baslangic_tarihi DESC
");
$policeler->execute([$m['tc_no']]);
$history = $policeler->fetchAll();
?>

<div class="wrapper">
    <h2>
        <?php echo $m['ad_soyad']; ?> - Poliçe Analizi
    </h2>

    <div class="data-card">
        <table>
            <tr>
                <th>Yıl</th>
                <th>Poliçe Türü</th>
                <th>Ödenen Prim</th>
                <th>Yıllık Artış Oranı</th>
            </tr>
            <?php
            $onceki_fiyat = 0;
            foreach ($history as $p):
                $yil = date('Y', strtotime($p['baslangic_tarihi']));
                $artis = ($onceki_fiyat > 0) ? (($p['prim_tutari'] - $onceki_fiyat) / $onceki_fiyat) * 100 : 0;
                ?>
                <tr>
                    <td>
                        <?php echo $yil; ?>
                    </td>
                    <td>
                        <?php echo $p['sigorta_turu']; ?>
                    </td>
                    <td>
                        <?php echo $p['prim_tutari']; ?> ₺
                    </td>
                    <td style="color: <?php echo $artis > 0 ? 'red' : 'green'; ?>">
                        <?php echo $onceki_fiyat > 0 ? '%' . number_format($artis, 1) : '-'; ?>
                    </td>
                </tr>
                <?php
                $onceki_fiyat = $p['prim_tutari'];
            endforeach;
            ?>
        </table>
    </div>
</div>