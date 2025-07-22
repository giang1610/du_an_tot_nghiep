<?php
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons Library (Boxicons) -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /*
        ========================================
        CSS VARIABLES - TRUNG TÂM TÙY CHỈNH
        ========================================
        */
        :root {
            --font-primary: 'Jost', sans-serif;
            --font-secondary: 'Cormorant Garamond', serif;

            --color-background: #fafafa;
            --color-text: #1a1a1a;
            --color-primary: #b99a7b;
            --color-primary-dark: #9c8268;
            --color-surface: #ffffff;
            --color-subtle-text: #666666;
            --color-border: #e8e8e8;
            
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.16);
            
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 16px;
            
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
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
            font-size: 16px;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--color-background);
            color: var(--color-text);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /*
        ========================================
        TYPOGRAPHY
        ========================================
        */
        h1, h2, h3, h4 {
            font-family: var(--font-secondary);
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        h1 { font-size: 3.5rem; }
        h2 { font-size: 2.75rem; }
        h3 { font-size: 2rem; }
        h4 { font-size: 1.5rem; }

        p {
            margin-bottom: 1.5rem;
            color: var(--color-subtle-text);
        }

        @media (max-width: 768px) {
            h1 { font-size: 2.5rem; }
            h2 { font-size: 2rem; }
            h3 { font-size: 1.75rem; }
        }

        /*
        ========================================
        ANIMATION SETUP
        ========================================
        */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /*
        ========================================
        HERO SECTION - CẬP NHẬT MỚI
        ========================================
        */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 120px 0;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60%;
            height: 100%;
            background-color: var(--color-primary);
            z-index: -1;
            clip-path: polygon(20% 0, 100% 0, 100% 100%, 0% 100%);
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-text {
            max-width: 600px;
        }

        .hero-text .subtitle {
            font-size: 1rem;
            font-weight: 500;
            color: var(--color-primary);
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            display: inline-block;
            position: relative;
            padding-left: 60px;
        }

        .hero-text .subtitle::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 40px;
            height: 1px;
            background-color: var(--color-primary);
        }

        .hero-text .title {
            font-size: 4rem;
            margin-bottom: 24px;
            position: relative;
            line-height: 1.1;
        }

        .hero-text .description {
            font-size: 1.1rem;
            margin-bottom: 32px;
        }

        .hero-image {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-image img {
            width: 100%;
            max-height: 80vh;
            object-fit: cover;
            object-position: center;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
        }

        /*
        ========================================
        BUTTON STYLES - CẬP NHẬT MỚI
        ========================================
        */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 16px;
            padding: 16px 40px;
            background-color: var(--color-primary);
            color: var(--color-surface);
            font-family: var(--font-primary);
            font-weight: 500;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 50px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--color-primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%, -50%);
            transform-origin: 50% 50%;
        }

        .btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        /*
        ========================================
        STORY SECTION - CẬP NHẬT MỚI
        ========================================
        */
        .story-section {
            padding: 120px 0;
            position: relative;
        }

        .story-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(249, 247, 245, 0.8) 0%, rgba(255, 255, 255, 0.9) 100%);
            z-index: -1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--color-primary);
        }

        .section-subtitle {
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        .timeline {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background-color: var(--color-primary);
        }

        .timeline-item {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 80px;
            position: relative;
        }

        .timeline-item:nth-child(even) {
            justify-content: flex-start;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-content {
            width: calc(50% - 60px);
            padding: 30px;
            background-color: var(--color-surface);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            position: relative;
            transition: var(--transition);
        }

        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            top: 30px;
            right: -15px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 15px 0 15px 15px;
            border-color: transparent transparent transparent var(--color-surface);
        }

        .timeline-item:nth-child(even) .timeline-content::before {
            right: auto;
            left: -15px;
            border-width: 15px 15px 15px 0;
            border-color: transparent var(--color-surface) transparent transparent;
        }

        .timeline-year {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--color-primary);
            color: var(--color-surface);
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 50px;
            box-shadow: var(--shadow-sm);
            z-index: 1;
        }

        /*
        ========================================
        VALUES SECTION - CẬP NHẬT MỚI
        ========================================
        */
        .values-section {
            padding: 120px 0;
            background-color: var(--color-surface);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .value-card {
            background-color: var(--color-surface);
            padding: 40px;
            border-radius: var(--radius-md);
            transition: var(--transition);
            border: 1px solid var(--color-border);
            text-align: center;
        }
        
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .value-card .icon {
            font-size: 3.5rem;
            color: var(--color-primary);
            margin-bottom: 24px;
            display: inline-flex;
            width: 80px;
            height: 80px;
            align-items: center;
            justify-content: center;
            background-color: rgba(185, 154, 123, 0.1);
            border-radius: 50%;
        }

        .value-card h3 {
            margin-bottom: 16px;
            position: relative;
            padding-bottom: 16px;
        }

        .value-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background-color: var(--color-primary);
        }

        /*
        ========================================
        TESTIMONIAL SECTION - MỚI THÊM
        ========================================
        */
        .testimonial-section {
            padding: 100px 0;
            background-color: #f7f3f0;
        }

        .testimonials {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }

        .testimonial-card {
            background-color: var(--color-surface);
            padding: 40px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-family: var(--font-secondary);
            font-size: 5rem;
            color: rgba(185, 154, 123, 0.1);
            line-height: 1;
        }

        .testimonial-content {
            margin-bottom: 20px;
            font-style: italic;
            position: relative;
            z-index: 1;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .author-info h4 {
            font-size: 1rem;
            margin-bottom: 4px;
            font-family: var(--font-primary);
        }

        .author-info p {
            font-size: 0.9rem;
            margin-bottom: 0;
            color: var(--color-subtle-text);
        }

        /*
        ========================================
        FOOTER - CẬP NHẬT MỚI
        ========================================
        */
        .footer {
            background-color: #1a1a1a;
            color: #999;
            padding: 80px 0 40px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 60px;
        }

        .footer-column h4 {
            color: #fff;
            font-family: var(--font-primary);
            margin-bottom: 20px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column li {
            margin-bottom: 12px;
        }

        .footer-column a {
            color: #999;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-column a:hover {
            color: var(--color-primary);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid #333;
        }

        .social-links {
            display: flex;
            gap: 16px;
            margin-top: 20px;
            justify-content: center;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #333;
            color: #fff;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--color-primary);
            transform: translateY(-3px);
        }

        /*
        ========================================
        RESPONSIVE DESIGN
        ========================================
        */
        @media (max-width: 1024px) {
            .hero::before {
                width: 70%;
                clip-path: polygon(15% 0, 100% 0, 100% 100%, 0% 100%);
            }
            
            .hero-content {
                gap: 40px;
            }
            
            .hero-text .title {
                font-size: 3.5rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 24px;
            }
            
            .hero {
                padding: 100px 0;
            }
            
            .hero::before {
                display: none;
            }
            
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-text {
                order: 2;
                margin-top: 40px;
                max-width: 100%;
            }
            
            .hero-image {
                order: 1;
            }
            
            .hero-text .subtitle {
                padding-left: 0;
            }
            
            .hero-text .subtitle::before {
                display: none;
            }
            
            .hero-text .title {
                font-size: 2.8rem;
            }
            
            .timeline::before {
                left: 30px;
            }
            
            .timeline-item {
                justify-content: flex-start;
                margin-bottom: 60px;
            }
            
            .timeline-item:nth-child(even) {
                justify-content: flex-start;
            }
            
            .timeline-content {
                width: calc(100% - 90px);
                margin-left: 60px;
            }
            
            .timeline-content::before {
                right: auto;
                left: -15px;
                border-width: 15px 15px 15px 0;
                border-color: transparent var(--color-surface) transparent transparent;
            }
            
            .timeline-year {
                left: 30px;
                transform: translateX(0);
            }
            
            .section-header {
                margin-bottom: 60px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 80px 0;
            }
            
            .hero-text .title {
                font-size: 2.2rem;
            }
            
            .btn {
                width: 100%;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .value-card {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

    <main>
        <!-- HERO SECTION -->
        <section class="hero">
            <div class="container">
                <div class="hero-content reveal">
                    <div class="hero-text">
                        <p class="subtitle">Thương hiệu thời trang MG</p>
                        <h1 class="title">Nơi mỗi trang phục là một tuyên ngôn</h1>
                        <p class="description">
                            Chúng tôi tin rằng thời trang không chỉ là quần áo, đó là cách bạn kể câu chuyện về chính mình mà không cần một lời nào. MG - Định hình phong cách, khẳng định cá tính.
                        </p>
                        <a href="{{ config('app.production_url') }}" class="btn">Khám phá bộ sưu tập</a>
                    </div>
                    <figure class="hero-image">
                        <img src="https://photo.znews.vn/w1920/Uploaded/wohaahp/2021_04_11/Nguoi_mau_nam_noi_tieng_moi_thoi_dai_9.jpg" alt="Người mẫu mặc trang phục MG" loading="lazy">
                    </figure>
                </div>
            </div>
        </section>

        <!-- STORY SECTION -->
        <section class="story-section">
            <div class="container">
                <div class="section-header reveal">
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
                            <p>MG ra đời từ một cửa hàng nhỏ tại Hà Nội, với khát vọng mang đến những sản phẩm thời trang Việt Nam chất lượng cao, có gu thẩm mỹ riêng biệt, kết hợp giữa truyền thống và hiện đại.</p>
                        </div>
                    </div>
                    <div class="timeline-item reveal" style="transition-delay: 0.4s;">
                        <div class="timeline-year">2023</div>
                        <div class="timeline-content">
                            <h3>Khẳng định vị thế</h3>
                            <p>Ra mắt cửa hàng flagship đầu tiên tại TP.HCM và website chính thức, mở rộng tệp khách hàng trên toàn quốc, nhận được sự tin tưởng và yêu mến từ giới trẻ và người yêu thời trang.</p>
                        </div>
                    </div>
                    <div class="timeline-item reveal" style="transition-delay: 0.6s;">
                        <div class="timeline-year">Tương lai</div>
                        <div class="timeline-content">
                            <h3>Vươn xa</h3>
                            <p>Tiếp tục đổi mới, sáng tạo, hướng đến việc trở thành nguồn cảm hứng phong cách hàng đầu và đồng hành cùng bạn trên mọi hành trình. MG đặt mục tiêu vươn ra thị trường quốc tế, quảng bá thời trang Việt.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VALUES SECTION -->
        <section class="values-section">
            <div class="container">
                <div class="section-header reveal">
                    <h2 class="section-title">Giá Trị Cốt Lõi</h2>
                    <p class="section-subtitle">
                        Những nguyên tắc giúp MG tạo nên khác biệt và chiếm được lòng tin của khách hàng.
                    </p>
                </div>
                <div class="values-grid">
                    <div class="value-card reveal" style="transition-delay: 0.2s;">
                        <div class="icon"><i class='bx bx-diamond'></i></div>
                        <h3>Chất Lượng Vượt Trội</h3>
                        <p>Mỗi sản phẩm đều là kết tinh của chất liệu cao cấp và sự tỉ mỉ trong từng đường may, đảm bảo sự bền đẹp cùng năm tháng. Chúng tôi chỉ sử dụng vật liệu tốt nhất và quy trình kiểm soát chất lượng nghiêm ngặt.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay: 0.4s;">
                        <div class="icon"><i class='bx bx-pen'></i></div>
                        <h3>Thiết Kế Sáng Tạo</h3>
                        <p>Chúng tôi không theo đuổi xu hướng, chúng tôi tạo ra chúng. Luôn tiên phong với những thiết kế độc đáo, tôn vinh vóc dáng người Việt. Đội ngũ thiết kế của MG luôn tìm kiếm cảm hứng từ văn hóa bản địa để tạo nên những tác phẩm đậm chất nghệ thuật.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay: 0.6s;">
                        <div class="icon"><i class='bx bx-face'></i></div>
                        <h3>Trải Nghiệm Tận Tâm</h3>
                        <p>Sự hài lòng của bạn là ưu tiên số một. Chúng tôi lắng nghe, thấu hiểu và mang đến dịch vụ xứng tầm với chất lượng sản phẩm. Từ tư vấn phong cách đến chính sách hậu mãi, MG luôn đặt khách hàng làm trung tâm.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTIMONIAL SECTION - MỚI THÊM -->
        <section class="testimonial-section">
            <div class="container">
                <div class="section-header reveal">
                    <h2 class="section-title">Khách Hàng Nói Về MG</h2>
                    <p class="section-subtitle">
                        Những phản hồi chân thực từ khách hàng đã trải nghiệm sản phẩm và dịch vụ của chúng tôi.
                    </p>
                </div>
                <div class="testimonials">
                    <div class="testimonial-card reveal" style="transition-delay: 0.2s;">
                        <div class="testimonial-content">
                            <p>Tôi hoàn toàn bị chinh phục bởi chất lượng và thiết kế của MG. Mỗi bộ trang phục đều khiến tôi tự tin hơn và nhận được nhiều lời khen ngợi. Dịch vụ tư vấn cũng rất chuyên nghiệp!</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/lego/4.jpg" alt="Khách hàng Ngọc Anh" class="author-avatar">
                            <div class="author-info">
                                <h4>Kong Lý</h4>
                                <p>Dev</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card reveal" style="transition-delay: 0.4s;">
                        <div class="testimonial-content">
                            <p>Là người khó tính về thời trang, tôi rất hài lòng với những gì MG mang lại. Chất vải tốt, đường may tỉ mỉ, và quan trọng là thiết kế không đụng hàng. Đã trở thành khách hàng thân thiết được 1 thời gian.</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/lego/4.jpg" alt="Khách hàng Minh Đức" class="author-avatar">
                            <div class="author-info">
                                <h4>Mã Giang</h4>
                                <p>Dev</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card reveal" style="transition-delay: 0.6s;">
                        <div class="testimonial-content">
                            <p>Từ khi biết đến MG, tủ đồ của tôi đã thay đổi hoàn toàn. Mỗi sản phẩm đều mang lại cảm giác sang trọng nhưng vẫn rất thoải mái. Đặc biệt yêu thích các thiết kế lấy cảm hứng từ văn hóa Việt.</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/lego/4.jpg" alt="Khách hàng Thu Hà" class="author-avatar">
                            <div class="author-info">
                                <h4>Vũ Hảo</h4>
                                <p>Dev</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h4>Về MG Fashion</h4>
                    <p>Thương hiệu thời trang Việt với tầm nhìn đưa phong cách Việt vươn ra thế giới.</p>
                </div>
                <div class="footer-column">
                    <h4>Liên kết nhanh</h4>
                    <ul>
                        <li><a href="#">Trang chủ</a></li>
                        <li><a href="#">Bộ sưu tập</a></li>
                        <li><a href="#">Về chúng tôi</a></li>
                        <li><a href="#">Tin tức</a></li>
                        <li><a href="#">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Thông tin liên hệ</h4>
                    <ul>
                        <li><i class='bx bx-map'></i> 123 Đường ABC, Hà Nội</li>
                        <li><i class='bx bx-phone'></i> (024) 1234 5678</li>
                        <li><i class='bx bx-envelope'></i> info@mgfashion.com</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Đăng ký nhận tin</h4>
                    <p>Nhận thông tin mới nhất về bộ sưu tập và ưu đãi đặc biệt.</p>
                    <form>
                        <input type="email" placeholder="Email của bạn" style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #333; background: #222; color: #fff;">
                        <button type="submit" class="btn" style="width: 100%; padding: 10px;">Đăng ký</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo $currentYear; ?> MG Fashion. All Rights Reserved.</p>
                <div class="social-links">
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-pinterest'></i></a>
                    <a href="#"><i class='bx bxl-youtube'></i></a>
                </div>
            </div>
        </div>
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
                }
            });
        }, observerOptions);

        revealElements.forEach(el => {
            observer.observe(el);
        });

        // Hiệu ứng hover cho card
        const cards = document.querySelectorAll('.value-card, .testimonial-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px)';
                card.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.15)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });
        });
    });
    </script>

</body>
</html>
