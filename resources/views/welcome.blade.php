<?php
    // PHP variables can be defined here if needed.
    $currentYear = date("Y");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Câu Chuyện Thương Hiệu MG - Đẳng Cấp & Phong Cách</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Icons Library (Boxicons) -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /*
        ========================================
        CSS VARIABLES - TRUNG TÂM TÙY CHỈNH
        ========================================
        */
        :root {
            --font-primary: 'Jost', sans-serif; /* Font chữ cho nội dung chính */
            --font-secondary: 'Cormorant Garamond', serif; /* Font chữ nghệ thuật cho tiêu đề */

            --color-background: #fdfdfd; /* Màu nền chính */
            --color-text: #222222; /* Màu chữ chính */
            --color-primary: #b99a7b; /* Màu nhấn (vàng cát) */
            --color-surface: #ffffff; /* Màu nền cho các thẻ (card) */
            --color-subtle-text: #666666; /* Màu chữ phụ */
        }

        /*
        ========================================
        GLOBAL STYLES & RESET
        ========================================
        */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--color-background);
            color: var(--color-text);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /*
        ========================================
        ANIMATION SETUP
        ========================================
        */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1.2s ease, transform 1.2s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /*
        ========================================
        HERO SECTION
        ========================================
        */
        .hero {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            align-items: center;
            min-height: 90vh;
            padding: 80px 0;
            gap: 24px;
        }

        .hero-text {
            grid-column: 1 / span 6;
        }

        .hero-text .subtitle {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--color-primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 16px;
        }

        .hero-text .title {
            font-family: var(--font-secondary);
            font-size: 4.5rem;
            font-weight: 600;
            line-height: 1.1;
            color: var(--color-text);
            margin-bottom: 24px;
        }

        .hero-text .description {
            font-size: 1.1rem;
            max-width: 500px;
            color: var(--color-subtle-text);
        }

        .hero-image {
            grid-column: 7 / span 6;
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-image img {
            width: 100%;
            max-height: 75vh;
            height: auto;
            object-fit: contain;
            object-position: center;
            border-radius: 4px;
        }
        
        /*
        ========================================
        BUTTON STYLES (MÃ MỚI CHO NÚT BẤM)
        ========================================
        */
        .btn {
            display: inline-block;
            margin-top: 32px;
            padding: 14px 32px;
            background-color: var(--color-primary);
            color: var(--color-surface);
            font-family: var(--font-primary);
            font-weight: 500;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 50px; /* Bo góc tròn trịa */
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn:hover {
            background-color: #a5886b; /* Màu đậm hơn một chút khi di chuột vào */
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /*
        ========================================
        STORY SECTION
        ========================================
        */
        .story-section {
            padding: 100px 0;
            text-align: center;
        }

        .story-section .section-title {
            font-family: var(--font-secondary);
            font-size: 3rem;
            margin-bottom: 16px;
        }

        .story-section .section-subtitle {
            max-width: 600px;
            margin: 0 auto 60px auto;
            color: var(--color-subtle-text);
            font-size: 1.1rem;
        }

        .timeline {
            display: grid;
            grid-template-columns: 1fr;
            gap: 50px;
            text-align: left;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 30px;
        }

        .timeline-year {
            background-color: var(--color-primary);
            color: var(--color-surface);
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 50px;
            flex-shrink: 0;
        }

        .timeline-content h3 {
            font-family: var(--font-secondary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .timeline-content p {
            color: var(--color-subtle-text);
        }

        /*
        ========================================
        VALUES SECTION
        ========================================
        */
        .values-section {
            padding: 100px 0;
            background-color: #f7f3f0;
        }

        .values-section .section-title {
            text-align: center;
            font-family: var(--font-secondary);
            font-size: 3rem;
            margin-bottom: 60px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .value-card {
            background-color: var(--color-surface);
            padding: 40px;
            border-radius: 4px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .value-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        }

        .value-card .icon {
            font-size: 3rem;
            color: var(--color-primary);
            margin-bottom: 20px;
        }

        .value-card h3 {
            font-family: var(--font-secondary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .value-card p {
            color: var(--color-subtle-text);
        }

        /*
        ========================================
        FOOTER
        ========================================
        */
        .footer {
            text-align: center;
            padding: 40px 24px;
            color: var(--color-subtle-text);
            font-size: 0.9rem;
            border-top: 1px solid #e0e0e0;
            margin-top: 100px;
        }
        
        /*
        ========================================
        RESPONSIVE DESIGN
        ========================================
        */
        @media (max-width: 992px) {
            .hero {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero-text {
                grid-column: 1 / -1;
                grid-row: 2;
                margin-top: 40px;
            }
            .hero-image {
                grid-column: 1 / -1;
                grid-row: 1;
            }
            .hero-text .title {
                font-size: 3.5rem;
            }
            .hero-text .description {
                margin: 0 auto;
            }
        }
        @media (max-width: 768px) {
            .hero-text .title {
                font-size: 2.8rem;
            }
            .story-section, .values-section {
                padding: 80px 0;
            }
        }

    </style>
</head>
<body>

    <main>
        <!-- HERO SECTION -->
        <section class="hero container reveal">
            <div class="hero-text">
                <p class="subtitle">Thương hiệu thời trang MG</p>
                <h1 class="title">Nơi mỗi trang phục là một tuyên ngôn.</h1>
                <p class="description">
                    Chúng tôi tin rằng thời trang không chỉ là quần áo, đó là cách bạn kể câu chuyện về chính mình mà không cần một lời nào.
                </p>
                <!-- === NÚT BẤM ĐÃ ĐƯỢC THÊM VÀO ĐÂY === -->
                <a href="https://online-shop-sigma-eight.vercel.app" class="btn">Về Trang Chủ</a>
            </div>
            <figure class="hero-image">
                <img src="https://photo.znews.vn/w1920/Uploaded/wohaahp/2021_04_11/Nguoi_mau_nam_noi_tieng_moi_thoi_dai_9.jpg" alt="Một người mẫu mặc trang phục thời trang của MG">
            </figure>
        </section>

        <!-- STORY SECTION -->
        <section class="story-section">
            <div class="container">
                <div class="reveal">
                    <h2 class="section-title">Hành Trình Của Chúng Tôi</h2>
                    <p class="section-subtitle">
                        Từ một ý tưởng táo bạo đến một thương hiệu được yêu mến, MG là hành trình của đam mê, sáng tạo và không ngừng hoàn thiện để định hình phong cách Việt.
                    </p>
                </div>
                <div class="timeline">
                    <div class="timeline-item reveal" style="transition-delay: 0.2s;">
                        <div class="timeline-year">2021</div>
                        <div class="timeline-content">
                            <h3>Khởi đầu</h3>
                            <p>MG ra đời từ một cửa hàng nhỏ, với khát vọng mang đến những sản phẩm thời trang Việt Nam chất lượng cao, có gu thẩm mỹ riêng biệt.</p>
                        </div>
                    </div>
                    <div class="timeline-item reveal" style="transition-delay: 0.4s;">
                        <div class="timeline-year">2023</div>
                        <div class="timeline-content">
                            <h3>Khẳng định vị thế</h3>
                            <p>Ra mắt cửa hàng flagship đầu tiên và website chính thức, mở rộng tệp khách hàng trên toàn quốc, nhận được sự tin tưởng và yêu mến.</p>
                        </div>
                    </div>
                    <div class="timeline-item reveal" style="transition-delay: 0.6s;">
                        <div class="timeline-year">Tương lai</div>
                        <div class="timeline-content">
                            <h3>Vươn xa</h3>
                            <p>Tiếp tục đổi mới, sáng tạo, hướng đến việc trở thành nguồn cảm hứng phong cách hàng đầu và đồng hành cùng bạn trên mọi hành trình.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VALUES SECTION -->
        <section class="values-section">
            <div class="container">
                <h2 class="section-title reveal">Giá Trị Cốt Lõi</h2>
                <div class="values-grid">
                    <div class="value-card reveal" style="transition-delay: 0.2s;">
                        <div class="icon"><i class='bx bx-diamond'></i></div>
                        <h3>Chất Lượng Vượt Trội</h3>
                        <p>Mỗi sản phẩm đều là kết tinh của chất liệu cao cấp và sự tỉ mỉ trong từng đường may, đảm bảo sự bền đẹp cùng năm tháng.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay: 0.4s;">
                        <div class="icon"><i class='bx bx-pen'></i></div>
                        <h3>Thiết Kế Sáng Tạo</h3>
                        <p>Chúng tôi không theo đuổi xu hướng, chúng tôi tạo ra chúng. Luôn tiên phong với những thiết kế độc đáo, tôn vinh vóc dáng người Việt.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay: 0.6s;">
                        <div class="icon"><i class='bx bx-face'></i></div>
                        <h3>Trải Nghiệm Tận Tâm</h3>
                        <p>Sự hài lòng của bạn là ưu tiên số một. Chúng tôi lắng nghe, thấu hiểu và mang đến dịch vụ xứng tầm với chất lượng sản phẩm.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo $currentYear; ?> MG Fashion. All Rights Reserved.</p>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const revealElements = document.querySelectorAll('.reveal');

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Tùy chọn: ngắt quan sát sau khi đã hiện để tiết kiệm tài nguyên
                    // observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(el => {
            observer.observe(el);
        });
    });
    </script>

</body>
</html>
