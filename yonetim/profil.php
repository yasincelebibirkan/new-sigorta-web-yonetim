<?php
include 'auth.php';
include '../db.php';

$mesaj = "";
$hata = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeni_sifre = $_POST['yeni_sifre'];
    $yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'];

    // Boş alan kontrolü
    if (!empty($yeni_sifre) && !empty($yeni_sifre_tekrar)) {
        
        // Şifrelerin eşleşme kontrolü
        if ($yeni_sifre !== $yeni_sifre_tekrar) {
            $hata = "Girdiğiniz şifreler birbiriyle eşleşmiyor!";
        } 
        // Uzunluk kontrolü
        elseif (strlen($yeni_sifre) < 6) {
            $hata = "Şifre en az 6 karakter olmalıdır!";
        } 
        // Her şey yolundaysa güncelle
        else {
            $hash_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE kullanicilar SET password = ? WHERE id = ?");
            $stmt->execute([$hash_sifre, $_SESSION['user_id']]);
            $mesaj = "Şifreniz başarıyla güncellendi.";
        }
    } else {
        $hata = "Lütfen her iki şifre alanını da doldurun!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Güncelleme</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a237e;
            --slate: #475569;
            --emerald: #10b981;
            --rose: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: var(--navy);
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--slate);
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--navy);
        }

        input:disabled {
            background: #f8fafc;
            color: #94a3b8;
            cursor: not-allowed;
        }

        .btn-update {
            width: 100%;
            background: var(--navy);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.3s;
            margin-top: 10px;
        }

        .btn-update:hover {
            opacity: 0.9;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--slate);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .back-btn:hover {
            color: var(--navy);
        }
    </style>
</head>

<body>

    <div class="profile-card">
        <h2><i class="fas fa-lock-open"></i> Şifre Değiştir</h2>

        <?php if (!empty($mesaj)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $mesaj; ?></div>
        <?php endif; ?>

        <?php if (!empty($hata)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $hata; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" disabled>
            </div>

            <div class="form-group">
                <label>Yeni Şifre</label>
                <input type="password" name="yeni_sifre" placeholder="En az 6 karakter" required>
            </div>

            <div class="form-group">
                <label>Yeni Şifre (Tekrar)</label>
                <input type="password" name="yeni_sifre_tekrar" placeholder="Şifreyi tekrar yazın" required>
            </div>

            <button type="submit" class="btn-update">ŞİFREYİ GÜNCELLE</button>
        </form>

        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Panele Dön</a>
    </div>

</body>
</html>