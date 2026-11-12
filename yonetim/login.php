<?php
session_start();
include '../db.php'; // Veritabanı bağlantısı

$hata = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    // 1. Kullanıcıyı çek
    $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE username = ?");
    $stmt->execute([$user]);
    $kullanici = $stmt->fetch();

    if ($kullanici) {
        $simdi = date('Y-m-d H:i:s');

        // 2. Blok kontrolü
        if ($kullanici['blok_suresi'] && $kullanici['blok_suresi'] > $simdi) {
            $hata = "Çok fazla hatalı deneme! 15 dakika engellendiniz.";
        } else {
            // 3. Şifre Doğrulama
            if (password_verify($pass, $kullanici['password'])) {
                // Giriş BAŞARILI
                $pdo->prepare("UPDATE kullanicilar SET hatali_deneme = 0, blok_suresi = NULL WHERE id = ?")
                    ->execute([$kullanici['id']]);

                $_SESSION['user_id'] = $kullanici['id'];
                $_SESSION['username'] = $kullanici['username'];
                header("Location: index.php");
                exit;
            } else {
                // Giriş HATALI
                $yeni_deneme = $kullanici['hatali_deneme'] + 1;
                $blok_vakti = null;

                if ($yeni_deneme >= 3) {
                    $blok_vakti = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $hata = "3 kez hatalı giriş yaptınız. 15 dakika engellendiniz!";
                } else {
                    $hata = "Hatalı şifre! Kalan deneme hakkı: " . (3 - $yeni_deneme);
                }

                $pdo->prepare("UPDATE kullanicilar SET hatali_deneme = ?, blok_suresi = ? WHERE id = ?")
                    ->execute([$yeni_deneme, $blok_vakti, $kullanici['id']]);
            }
        }
    } else {
        $hata = "Kullanıcı bulunamadı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sisteme Giriş</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: #1e3a8a;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #64748b;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #1e40af;
        }

        .error {
            color: #ef4444;
            background: #fee2e2;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h2>Yönetim Paneli</h2>

        <?php if (!empty($hata)): ?>
            <div class="error"><?php echo $hata; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Giriş Yap</button>
        </form>
    </div>

</body>

</html>