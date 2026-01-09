<html>
<head>

    <title>ABS</title>
    <!-- THESE ARE THE FONTS EXTRACTED BY GOOGLE -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Anta&family=Audiowide&family=Lexend:wght@100..900&family=Racing+Sans+One&family=Science+Gothic:wght@100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">

</head>
<body>
    <!-- NAVBAR -->
    <nav>
        <div class="logo">APPOINTMENT BOOKING SYSTEM</div>
        <div class="hamburger" onclick="toggleMenu()">☰</div>
    </nav>
    
    <!-- MAIN CONTAINER -->
    <div class="home_contianer">
        <section class="main-space">
        <div class="content">
            <div class="hero-details">
                <div class="text-side">
                    <h1 class="subtitle">Book Your Appointment now...!</h1>
                    <br>
                    <p class="description">
                        Welcome to our Appointment Booking System, 
                        a simple and efficient platform designed to help you 
                        schedule your appointments with ease. View real-time available slots, 
                        choose your preferred time, and receive instant booking confirmation. 
                        Our system ensures secure login, prevents double bookings, and offers a smooth, 
                        user-friendly experience. Book your appointment in seconds and enjoy 
                        a hassle-free scheduling process.
                    </p>

                    <div class="btn-home">
                        <a href="register.php" id="btn">Register</a>                 
                        <a href="login.php" id="btn1">Login</a>
                    </div>

                </div>
                <div class="main-svg">
                    <img src="./images/calendar.svg" alt="">
                </div>
            </div>
        </div>
        </section>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById("menu").classList.toggle("show");
        }
    </script>
</body>
</html>