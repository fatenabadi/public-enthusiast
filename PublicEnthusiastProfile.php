<?php
session_start();
require 'config.php';

// Check if a user_id is provided in the URL
if (!isset($_GET['user_id'])) {
    header("Location: signin.php");
    exit();
}

$profile_user_id = (int)$_GET['user_id'];

// Get public profile data
$stmt = $conn->prepare("SELECT username, role FROM users WHERE user_id = ?");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: signin.php");
    exit();
}

$username = htmlspecialchars($user['username']);
$role = $user['role'];

// Get art preferences if available
$art_preferences = [];
$stmt = $conn->prepare("
    SELECT ap.mediums, ap.styles, ap.budget_max 
    FROM artpreferences ap
    JOIN enthusiasts e ON ap.enthusiast_id = e.enthusiast_id
    WHERE e.user_id = ?
");
$stmt->execute([$profile_user_id]);
if ($stmt->rowCount() > 0) {
    $art_preferences = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $username ?>'s Public Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root { 
        --primary-light: #a4e0dd;
        --primary: #78cac5;
        --primary-dark: #4db8b2;
        --secondary-light: #f2e6b5;
        --secondary: #e7cf9b;
        --secondary-dark: #96833f;
        --light: #EEF9FF;
        --dark: #173836;
    }

    body {
        background-color: var(--light);
        font-family: 'Nunito', sans-serif;
    }

    .profile-header {
        height: 300px;
        object-fit: fill;
        background-image: url('img/Teal Gold Dust Motivational Quote Facebook Cover.png');
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        position: relative;
        border-radius: 0% 0% 30% 30%;
        overflow: hidden;
    }

    .profile-image-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin-top: -75px;
        margin-right: auto;
        margin-bottom: 1rem;
        margin-left: auto;
    }
    
    .profile-image {
        width: 100%;
        height: 100%;
        border-width: 4px;
        border-style: solid;
        border-color: var(--light);
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
    }

    .art-form {
        background-image: linear-gradient(150deg, var(--primary-light) 20%, var(--secondary-light) 80%);
        border-radius: 20px;
        padding: 3rem;
        max-width: 800px;
        margin: 2rem auto;
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .form-title {
        color: var(--dark);
        border-bottom: 2px solid var(--primary);
        padding-bottom: 1rem;
        margin-bottom: 2rem;
        font-size: 1.5rem;
    }

    .preference-item {
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
    }

    .preference-label {
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 0.5rem;
    }

    .preference-value {
        font-size: 1.1rem;
    }

    .tag {
        display: inline-block;
        background-color: var(--secondary);
        color: var(--dark);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .back-arrow-btn {
        position: fixed;
        top: 50px;
        left: 50px;
        width: 50px;
        height: 50px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .back-arrow-btn:hover {
        background-color: var(--primary-dark);
        transform: scale(1.05);
    }

    .back-arrow-btn i {
        font-size: 1.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--secondary-dark);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
    </style>
</head>
<body>
    <!-- Back Arrow Button -->
    <button id="backArrow" class="back-arrow-btn" title="Go back">
        <i class="fas fa-arrow-left"></i>
    </button>

    <div class="profile-header"></div>

    <div class="container text-center">
        <div class="profile-image-container">
            <img src="_Prompt for Enthusiast Cover Picture_A clean, modern cover image with the words 'ENTHUSIAST' in a .jpeg" class="profile-image">
        </div>
        
        <h1 class="d-inline-block mt-3 text-center"><?php echo $username ?></h1>
        <p class="d-inline-block lead text-muted mt-2 text-center"><?php echo $role ?></p>  
    </div>

    <div class="art-form">
        <h3 class="form-title">Art Preferences</h3>
        
        <?php if (empty($art_preferences)): ?>
            <div class="empty-state">
                <i class="fas fa-palette"></i>
                <h4>No Art Preferences Shared Yet</h4>
                <p>This user hasn't shared their art preferences publicly.</p>
            </div>
        <?php else: ?>
            <!-- Mediums -->
            <div class="preference-item">
                <div class="preference-label">Favorite Medium(s)</div>
                <div class="preference-value">
                    <?php 
                    $mediums = explode(',', $art_preferences['mediums']);
                    foreach ($mediums as $medium): 
                        if (!empty($medium)): ?>
                            <span class="tag"><?php echo htmlspecialchars(ucfirst($medium)) ?></span>
                        <?php endif; 
                    endforeach; ?>
                </div>
            </div>

            <!-- Styles -->
            <div class="preference-item">
                <div class="preference-label">Preferred Art Styles</div>
                <div class="preference-value">
                    <?php 
                    $styles = explode(',', $art_preferences['styles']);
                    foreach ($styles as $style): 
                        if (!empty($style)): ?>
                            <span class="tag"><?php echo htmlspecialchars(ucfirst($style)) ?></span>
                        <?php endif; 
                    endforeach; ?>
                </div>
            </div>

            <!-- Budget -->
            <div class="preference-item">
                <div class="preference-label">Budget Range</div>
                <div class="preference-value">
                    Up to $<?php echo number_format($art_preferences['budget_max']) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Back arrow functionality
        document.getElementById('backArrow').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'home2.php';
        });
    </script>
</body>
</html>