<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 Not Found</title>

    <!-- CDN Lottie Web -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

    <style>
       body {
        display: flex;
        justify-content: center;
        align-items: center;    
        height: 100vh;         
        margin: 0;
        background: #f9f9f9;
    }
    #lottie-container {
        width: 900px;
        height: 500px;
    }
    </style>
</head>
<body>
    <div id="lottie-container"></div>

    <script>
        lottie.loadAnimation({
            container: document.getElementById('lottie-container'), 
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: "{{ asset('animation/404.json') }}" 
        });
    </script>
</body>
</html>
