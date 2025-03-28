<?php // NO blank line before this
// Start session if not already started (safety check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Assuming db.php is in the includes directory
require_once 'includes/db.php';
require_once 'includes/navbar.php'; // This includes the file with session_start() and Remember Me logic

// --- Get Logout Message (Flash Message) ---
$logout_message = $_SESSION['logout_message'] ?? null; // Get message if set
unset($_SESSION['logout_message']); // Clear it so it doesn't show again
// --- End Get Logout Message ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wellness Apparel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />

    <link rel="stylesheet" href="assets/css/main.css" />
    <script src="assets/js/main.js" defer></script> <?php /* Added defer to JS */ ?>

    <style>
        /* Style for the logout message */
        .logout-success-message {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green text */
            border: 1px solid #c3e6cb;
            padding: 15px;
            margin: 20px auto; /* Centered with space */
            border-radius: 5px;
            text-align: center;
            max-width: 1160px; /* Approx container width */
            box-sizing: border-box; /* Include padding in width */
            /* Ensure it appears below fixed navbar if applicable */
            position: relative;
            z-index: 1; /* Lower than navbar */
        }
    </style>
</head>
<body>

    <?php // Navbar is already included via require_once above ?>

    <main class="page-container">

        <?php // --- Display the Logout Message Here --- ?>
        <?php if ($logout_message): ?>
            <div class="logout-success-message">
                <?php echo htmlspecialchars($logout_message); ?>
            </div>
        <?php endif; ?>
        <?php // --- End Display Logout Message --- ?>


        <header class="header-section">
            <div class="header-content">
                <h1>Elevate Your Comfort with The Wellness Apparel</h1>
                <p>
                    Discover stylish, breathable, and cozy clothing designed to bring relaxation
                    and well-being into your everyday life. Experience the perfect blend of comfort
                    and style that enhances your daily routine.
                </p>
                <div class="header-buttons">
                    <a href="shop.php" class="btn-primary-orange">Shop Our Collection</a>
                </div>
            </div>
            <div class="header-image">
                <img src="assets/img/header-image.jpg" alt="Cozy Apparel">
            </div>
        </header>


        <section id="categories" class="categories-section">
            <h2 class="section-title">Discover Your Perfect Fit: Everyday Wellness Style</h2>
            <div class="category-cards">
                <article class="category-card">
                    <img src="assets/img/categories/casual.jpg" alt="Casual Collection" class="category-image" />
                    <div class="category-content">
                        <h3 class="category-title">Unleash Your Potential with Our Versatile Casual Collection</h3>
                        <p class="category-description">Experience the ideal blend of style and comfort with our casual wear.</p>
                    </div>
                </article>

                <article class="category-card">
                    <img src="assets/img/categories/loungewear.jpg" alt="Loungewear Collection" class="category-image" />
                    <div class="category-content">
                        <h3 class="category-title">Elevate Your Comfort with Our Loungewear: Relaxation and Style</h3>
                        <p class="category-description">Our loungewear offers unmatched softness for your downtime.</p>
                    </div>
                </article>

                <article class="category-card">
                    <img src="assets/img/categories/activewear.jpg" alt="Activewear Collection" class="category-image" />
                    <div class="category-content">
                        <h3 class="category-title">Move Freely: Activewear That Supports Your Every Move</h3>
                        <p class="category-description">Stay comfortable and stylish while you conquer your workouts.</p>
                    </div>
                </article>
            </div>
            <a href="shop.php" class="btn-secondary-blue">Explore Collection <i class="ti ti-arrow-right"></i></a>


        </section>



        <section id="testimonials" class="testimonials-section">
            <h2 class="testimonials-title">Customer testimonials</h2>
            <p class="testimonials-subtitle">Our customers love the comfort and style!</p>

            <div class="testimonials-container">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <blockquote class="testimonial-text">
                        "The softest loungewear I’ve ever owned! Perfect for relaxing at home."
                    </blockquote>
                    <div class="testimonial-user">
                        <img src="assets/img/testimonials/jennie.jpg" alt="Jennie Kim" class="testimonial-avatar" />
                        <div>
                            <p class="testimonial-name">Jennie Kim</p>
                            <p class="testimonial-role">Yoga Instructor</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <blockquote class="testimonial-text">
                        "Love the breathable activewear—keeps me comfortable during my yoga sessions!"
                    </blockquote>
                    <div class="testimonial-user">
                        <img src="assets/img/testimonials/olivia.jpg" alt="Olivia Rodrigo" class="testimonial-avatar" />
                        <div>
                            <p class="testimonial-name">Olivia Rodrigo</p>
                            <p class="testimonial-role">Fitness Coach</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
    <?php include 'includes/footer.php'; ?>

    </body>
</html>