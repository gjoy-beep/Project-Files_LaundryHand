<?php
session_start();

// Placeholder for logged-in user (replace with database check later)
$currentUser = $_SESSION['user'] ?? null;

// Flash message
$alertMessage = $_SESSION['alert'] ?? '';
$alertType = $_SESSION['alert_type'] ?? 'success';
unset($_SESSION['alert'], $_SESSION['alert_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LaundryHand - Complete Laundry Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<style>
/* --- Pastel Theme with Cute SaaS Aesthetic --- */
body {
    background: linear-gradient(135deg, #a0e7e5 0%, #ffd6e0 50%, #cdb4db 100%);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    font-family: 'Poppins', sans-serif;
    color: #0f172a;
}

/* Floating Bubbles for Cute Playful Effect */
.bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);  
    animation: float 6s ease-in-out infinite;
}
.bubble:nth-child(odd) { animation-delay: -2s; }
.bubble:nth-child(even) { animation-delay: -4s; }
@keyframes float { 0%,100% { transform: translateY(0px);} 50% { transform: translateY(-20px);} }
.bubble-1 { width: 80px; height: 80px; top: 20%; left: 10%; }
.bubble-2 { width: 120px; height: 120px; top: 60%; left: 5%; }
.bubble-3 { width: 60px; height: 60px; top: 40%; right: 15%; }
.bubble-4 { width: 100px; height: 100px; top: 70%; right: 20%; }



/* Container */
.container {position:relative; z-index:1; max-width:1200px; margin:0 auto; padding:2rem;}

/* Alerts */
.alert {padding:1rem;border-radius:10px;margin-bottom:1rem;display:none;}
.alert.show {display:block;}
.alert-success {background:#d1fae5;color:#065f46;border-left:5px solid #059669;}
.alert-error {background:#fee2e2;color:#991b1b;border-left:5px solid #dc2626;}

/* Home Hero Section */
.welcome-card {
    background: #ffffffaa; 
    border-radius:30px; 
    padding:3rem; 
    text-align:center;
    box-shadow:0 25px 50px rgba(0,0,0,0.1); 
    margin-top: 40px;
    margin-bottom:4rem;
}
.welcome-title { font-size:3rem; color:#ff8fab; margin-bottom:1rem; font-weight:800; }
.welcome-subtitle { font-size:1.2rem; color:#333; margin-bottom:2rem; }

/* Get Started Button - Gradient, Glow */
.get-started-btn { 
    background: linear-gradient(90deg, #ffb6c1, #ff8fab); 
    color:#fff; 
    padding:1rem 2.5rem; 
    border:none; 
    border-radius:50px; 
    font-size:1.2rem; 
    cursor:pointer; 
    transition:all 0.3s ease; 
    box-shadow:0 8px 25px rgba(255,182,193,0.5);
    display: inline-flex; 
    align-items: center;
    text-decoration: none;
}
.get-started-btn i { margin-left: 10px; }
.get-started-btn:hover { 
    background: linear-gradient(90deg, #ff8fab, #ff6f91); 
    box-shadow:0 12px 35px rgba(255,111,145,0.6);
    transform: scale(1.05);
}

/* Features Section */
.features-grid { 
    display:grid; 
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); 
    gap:2rem; 
    margin-top:2rem; 
}
.feature-card { 
    background:#fff; 
    border-radius:20px; 
    padding:2rem; 
    text-align:center; 
    box-shadow: 10px 10px 20px rgba(163,177,198,0.3), 
                -10px -10px 20px rgba(255,255,255,0.9); /* neumorphism */
    transition:all 0.3s ease; 
}
.feature-card:hover { 
    transform:translateY(-8px); 
    box-shadow:0 20px 40px rgba(0,0,0,0.12); 
}
.feature-title { font-size:1.3rem; color:#ff8fab; margin-bottom:1rem; font-weight:600; }
.feature-desc { color:#444; line-height:1.6; }
.feature-card i { font-size:2rem; color:#85c1ff; margin-bottom:0.8rem; }

/* Carousel Section */
.carousel-container {
    margin-top: 4rem;
    background: rgba(255,255,255,0.8);
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
.carousel-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    color: #ff8fab;
    margin-bottom: 2rem;
}
.carousel-inner img {
    border-radius: 20px;
    width: 100%;
    height: 500px;
    object-fit: cover;
}
footer {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(10px);
    padding: 2rem 1rem;
    text-align: center;
    border-top: 4px solid #ff8fab;
    margin-top: 3rem;
}
footer h5 {
    color: #ff6f91;
    font-weight: 700;
    margin-bottom: 1rem;
}
footer p {
    color: #333;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}
footer .social-icons a {
    color: #ff8fab;
    font-size: 1.5rem;
    margin: 0 10px;
    transition: all 0.3s ease;
}
footer .social-icons a:hover {
    color: #85c1ff;
    transform: scale(1.2);
}
footer small {
    display: block;
    margin-top: 1rem;
    color: #666;
    font-size: 0.85rem;
}
</style>
</head>
<body>

<!-- Floating Bubbles -->
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>


<!-- Container -->
<div class="container">
    <?php if($alertMessage): ?>
        <div class="alert alert-<?= $alertType ?> show"><?= $alertMessage ?></div>
    <?php endif; ?>

    <!-- Home Hero -->
    <div id="home" class="page active">
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome to LaundryHand 🧺</h1>
            <p class="welcome-subtitle">Track your laundry orders, check details, and stay updated in real time.</p>
            <a href="register.php" class="get-started-btn">
                Get Started <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <!-- Features -->
        <div class="features-grid">
            <div class="feature-card">
                <i class="bi bi-clock-fill"></i>
                <h3 class="feature-title">Real-Time Updates</h3>
                <p class="feature-desc">Know your laundry status anytime, anywhere.</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-receipt"></i>
                <h3 class="feature-title">Transparent Billing</h3>
                <p class="feature-desc">View exact details of weight, soap, and fabric conditioner used.</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-phone-fill"></i>
                <h3 class="feature-title">Easy Access</h3>
                <p class="feature-desc">Login and check your laundry updates on any device.</p>
            </div>
        </div>

      
        <div class="carousel-container">
            <h2 class="carousel-title">Visit our shop 📸</h2>
            <div id="laundryCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="images/shop3.png" class="d-block w-100" alt="Laundry Shop 3">
                    </div>
                     <div class="carousel-item">
                        <img src="images/shop2.png" class="d-block w-100" alt="Laundry Shop 2">
                    </div>
                    <div class="carousel-item">
                        <img src="images/staff.png" class="d-block w-100" alt="Laundry Staff">
                    </div>
                    <div class="carousel-item">
                        <img src="images/shop1.png" class="d-block w-100" alt="Laundry Shop 1">
                    </div>
                    <div class="carousel-item">
                        <img src="images/shop4.png" class="d-block w-100" alt="Laundry Shop 4">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#laundryCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#laundryCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <!-- End Carousel -->
    </div>
</div>
<footer>
    <h5>LaundryHand 🧺</h5>
    <p>Your trusted laundry partner — fast, transparent, and hassle-free!</p>
    <p><i class="bi bi-envelope-fill"></i> laundryhandsupport@gmail.com</p>
    <p><i class="bi bi-geo-alt-fill"></i> J. Panis St. Banilad Cebu City, Philippines</p>

    <div class="social-icons mt-3">
        <a href="#"><i class="bi bi-facebook"></i></a>
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-twitter-x"></i></a>
    </div>

    <small>&copy; <?php echo date("Y"); ?> LaundryHand. All rights reserved.</small>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
