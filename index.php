<?php
$kurulus_yili = 2006;
$su_anki_yil = date("Y");
$deneyim_yili = $su_anki_yil - $kurulus_yili;
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sigorta | Profesyonel Güvence</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.1">
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <span class="new">NEW</span><span class="sigorta">SİGORTA</span>
            </div>
            <div class="menu-toggle" id="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Ana Sayfa</a></li>
                <li><a href="#services">Hizmetlerimiz</a></li>
                <li><a href="#about">Hakkımızda</a></li>
                <li><a href="#contact">İletişim</a></li>
            </ul>

            <div class="nav-right">
                <div class="support-item">
                    <div class="support-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="support-content">
                        <span class="support-text">Müşteri Hattı</span>
                        <a href="tel:05521820639" class="support-link">0552 182 06 39</a>
                    </div>
                </div>
                <a href="#quote" class="nav-button">Teklif Al</a>
            </div>
        </div>

    </nav>
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-badge">Güvenli Yarınlar İçin</span>
                <h1>Geleceğinizi <span class="highlight">New Sigorta</span> ile Güvenceye Alın</h1>
                <p>Hayatın sürprizlerine karşı yanınızdayız. Modern çözümler ve profesyonel destekle size en uygun
                    sigorta tekliflerini sunuyoruz.</p>

                <div class="hero-btns">
                    <a href="#quote" class="btn-primary">Hemen Teklif Al</a>
                    <a href="#services" class="btn-secondary">Hizmetlerimizi İncele</a>
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <strong>10k+</strong>
                        <span>Mutlu Müşteri</span>
                    </div>
                    <div class="stat-item">
                        <strong>24/7</strong>
                        <span>Destek Hizmeti</span>
                    </div>
                </div>
            </div>

            <div class="hero-image">
                <img src="img/hero.png" alt="New Sigorta Güvence">
                <div class="floating-card">
                    <i class="fas fa-shield-alt"></i>
                    <span>%100 Güvenli Poliçe</span>
                </div>
            </div>
        </div>
        <a href="#services" class="scroll-down">
            <i class="fas fa-chevron-down"></i>
            <span>Hizmetlerimizi Gör</span>
        </a>
    </section>


    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <div class="title-wrapper">
                    <span class="sub-title">Size Özel Çözümler</span>
                    <h2>Hayatın Her Anında <br>
                        <span class="thick-text">Tam Güvence</span>
                        <span class="outline-text">Altındasınız</span>
                    </h2>
                    <div class="title-line"></div>
                </div>
                <p class="section-desc">Standart poliçelerin ötesinde, sizin yaşam tarzınıza ve ihtiyaçlarınıza göre
                    şekillenen profesyonel sigortacılık anlayışı ile tanışın.</p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-car"></i></div>
                    <h3>Kasko <span class="thin">Sigortası</span></h3>
                    <p>Aracınızı kaza ve hırsızlığa karşı tam güvence altına alın.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('kasko')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-house-damage"></i></div>
                    <h3>Zorunlu <span class="thin">Deprem</span></h3>
                    <p>DASK ile yuvanızı deprem risklerine karşı koruyun.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('dask')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-user-nurse"></i></div>
                    <h3>Sağlık <span class="thin">Sigortası</span></h3>
                    <p>En iyi hastanelerde fark ödemeden tedavi olun.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('saglik')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-store"></i></div>
                    <h3>İşyeri <span class="thin">Sigortası</span></h3>
                    <p>İşyerinizi ve sermayenizi her türlü riske karşı koruyun.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('isyeri')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-home"></i></div>
                    <h3>Konut <span class="thin">Sigortası</span></h3>
                    <p>Evinizi ve içindeki eşyaları tek poliçeyle koruyun.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('konut')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-fire"></i></div>
                    <h3>Yangın <span class="thin">Sigortası</span></h3>
                    <p>Yangın ve ek risklere karşı mülkünüzü güvenceye alın.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('yangin')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-plane"></i></div>
                    <h3>Seyahat <span class="thin">Sigortası</span></h3>
                    <p>Yurt dışı gezilerinizde sağlık sorunlarına hazırlıklı olun.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('seyahat')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-icon-wrapper"><i class="fas fa-truck"></i></div>
                    <h3>Nakliyat <span class="thin">Sigortası</span></h3>
                    <p>Yükünüz taşınırken yolda oluşabilecek hasarları biz çözelim.</p>
                    <div class="service-footer">
                        <a href="#quote" onclick="selectService('nakliyat')" class="service-btn">Teklif Al <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="about" class="about">
        <div class="container">
            <div class="about-wrapper">
                <div class="about-image">
                    <img src="img/about_img.png" alt="New Sigorta Ofis">
                    <div class="experience-badge">
                        <span class="exp-year">
                            <?php echo $deneyim_yili; ?>+
                        </span>
                        <span class="exp-text">Yıllık <br> Deneyim</span>
                    </div>
                </div>

                <div class="about-content">
                    <span class="sub-title">Bizi Yakından Tanıyın</span>
                    <h2>Güveninizi <span class="thick-text">Hizmetimizle</span> <br> Pekiştiriyoruz</h2>
                    <p>New Sigorta olarak, sadece poliçe satmıyoruz; hayatınızın en zor anlarında yanınızda olacağımızın
                        sözünü veriyoruz. Uzman kadromuzla risklerinizi analiz ediyor, size en şeffaf çözümleri
                        sunuyoruz.</p>

                    <ul class="about-features">
                        <li><i class="fas fa-check-circle"></i> En İyi Fiyat Garantisi</li>
                        <li><i class="fas fa-check-circle"></i> Geniş Sigorta Ağı & Karşılaştırmalı Teklifler</li>
                        <li><i class="fas fa-check-circle"></i> Kişiye Özel Esnek Ödeme Planları</li>
                    </ul>

                    <div class="about-stats-grid">
                        <div class="stat-box">
                            <h3>100%</h3>
                            <p>Müşteri Memnuniyeti</p>
                        </div>
                        <div class="stat-box">
                            <h3>50k+</h3>
                            <p>Aktif Poliçe</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="quote" class="quote-section">
        <div class="container">
            <div class="quote-container">
                <div class="quote-info">
                    <h2>Hızlı <span class="thick-text">Teklif</span> Alın</h2>
                    <p>Bilgilerinizi bırakın, uzman danışmanlarımız size en uygun poliçeyi hazırlayıp 15 dakika içinde
                        dönsün.</p>
                    <div class="contact-mini">
                        <a href="tel:05521820639"
                            style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 10px;">
                            <i class="fas fa-phone-alt"></i>
                            <span>0552 182 06 39</span>
                        </a>
                    </div>
                </div>

                <form class="quote-form" id="insuranceForm">
                    <div class="form-row">
                        <input type="text" placeholder="Adınız Soyadınız" required>
                        <input type="tel" placeholder="Telefon Numaranız" required>
                    </div>

                    <select id="serviceSelect" required>
                        <option value="">Sigorta Türü Seçin</option>
                        <option value="kasko">Kasko Sigortası</option>
                        <option value="dask">Zorunlu Deprem Sigortası (DASK)</option>
                        <option value="saglik">Sağlık Sigortası</option>
                        <option value="isyeri">İşyeri Sigortası</option>
                        <option value="konut">Konut Sigortası</option>
                        <option value="yangin">Yangın Sigortası</option>
                        <option value="seyahat">Seyahat Sigortası</option>
                        <option value="nakliyat">Nakliyat Sigortası</option>
                    </select>

                    <div class="form-buttons" style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" onclick="sendForm('whatsapp')" class="submit-btn"
                            style="background: #25d366; flex: 1;">
                            <i class="fab fa-whatsapp"></i> WhatsApp ile Gönder
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </section>
    <section id="contact" class="contact-details">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-map"
                    style="width: 100%; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3020.3211997563585!2d29.431999999999995!3d40.79893499999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cb211b44cb3537%3A0x2fb00e262458a276!2sNew%20Sigorta%20Arac%C4%B1l%C4%B1k%20Hizmetleri!5e0!3m2!1str!2str!4v1778670536197!5m2!1str!2str"
                        width="100%" height="450" style="border:0; display: block;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>


                <div class="contact-info-cards">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Ofis Adresimiz</h4>
                            <a href="https://www.google.com/maps/search/?api=1&query=Nevin+Ündar+New+Sigorta+Anadolu+Hayat+Emeklilik+Yetkili+Acentesi"
                                target="_blank" class="address-link">
                                <p>Hacıhalil Mah. Hükümet Cd. No:2 Kat:2<br>41400 Gebze/Kocaeli</p>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>E-Posta</h4>
                            <a href="mailto:info@newsigorta.com" style="text-decoration: none; color: inherit;">
                                <p>info@newsigorta.com</p>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <h4>Telefon</h4>
                            <p style="font-weight: 800; color: var(--navy); margin-bottom: 2px;">Nevin Ündar</p>
                            <a href="tel:05521820639" style="text-decoration: none; color: inherit;">
                                <p>0552 182 06 39</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <div class="nav-logo">
                        <span class="new" style="color: white;">NEW</span><span class="sigorta"
                            style="color: #ccc;">SİGORTA</span>
                    </div>
                    <p>Geleceğinizi güvence altına almak için yanınızdayız. Profesyonel kadromuzla modern sigortacılık
                        çözümleri sunuyoruz.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="footer-links">
                    <h4>Hızlı Menü</h4>
                    <ul>
                        <li><a href="#home">Ana Sayfa</a></li>
                        <li><a href="#services">Hizmetlerimiz</a></li>
                        <li><a href="#about">Hakkımızda</a></li>
                        <li><a href="#contact">İletişim</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 New Sigorta. Tüm Hakları Saklıdır.</p>
            </div>
        </div>
    </footer>

    <script>

        const mobileMenu = document.querySelector('#mobile-menu');
        const navMenu = document.querySelector('.nav-menu');

        // 1. Menü Tuşuna Basınca Aç/Kapat
        if (mobileMenu) {
            mobileMenu.addEventListener('click', function () {
                navMenu.classList.toggle('active');
                // İkonu da değiştirmek istersen (isteğe bağlı)
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            });
        }

        // 2. İŞTE ÇÖZÜM: Menü Linklerine Tıklanınca Menüyü Kapat
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {

                navMenu.classList.remove('active');


                const icon = mobileMenu.querySelector('i');
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            });
        });


        function selectService(serviceValue) {
            const selectBox = document.getElementById('serviceSelect');
            if (selectBox) {
                selectBox.value = serviceValue;
                const event = new Event('change', { bubbles: true });
                selectBox.dispatchEvent(event);
                document.getElementById('quote').scrollIntoView({ behavior: 'smooth' });
            }
        }


        function sendForm(type) {
            const form = document.getElementById('insuranceForm');
            const adSoyad = form.querySelectorAll('input')[0].value;
            const telefon = form.querySelectorAll('input')[1].value;
            const selectElement = document.getElementById('serviceSelect');
            const sigortaTuru = selectElement.options[selectElement.selectedIndex].text;


            if (!adSoyad || !telefon || !selectElement.value) {
                alert("Lütfen tüm alanları doldurun.");
                return;
            }


            fetch('send_mail.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `name=${encodeURIComponent(adSoyad)}&phone=${encodeURIComponent(telefon)}&service=${encodeURIComponent(sigortaTuru)}`
            });


            if (type === 'whatsapp') {
                const mesaj = `Merhaba New Sigorta, yeni bir teklif talebi var:%0A%0A- *Ad Soyad:* ${adSoyad}%0A- *Telefon:* ${telefon}%0A- *Sigorta Türü:* ${sigortaTuru}`;
                window.open(`https://wa.me/905521820639?text=${mesaj}`, '_blank');
            }


            form.innerHTML = `
            <div style="text-align:center; padding: 30px; background: #f8faff; border-radius: 20px; border: 2px solid var(--navy);">
                <i class="fas fa-check-circle" style="font-size: 50px; color: #2ecc71; margin-bottom: 15px;"></i>
                <h3 style="color: var(--navy);">Talebiniz Başarıyla Alındı!</h3>
                <p style="color: #666;">Bilgileriniz tarafımıza ulaştı. En kısa sürede sizinle iletişime geçeceğiz.</p>
            </div>
        `;
        }
    </script>

</body>

</html>