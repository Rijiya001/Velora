<?php
// Initialize configuration and verify database connection
require_once dirname(__DIR__) . '/config/database.php';
$brand_name = get_setting($con, 'brand_name', 'Velora');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? xss_clean($page_title) . " | " . xss_clean($brand_name) : xss_clean($brand_name) . " | Premium Fashion Jewelry"; ?></title>
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?php echo $rel_path; ?>style.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Promo Banner Text from Website Settings -->
    <?php 
    $promo_text = get_setting($con, 'promo_banner_text', '');
    if (!empty($promo_text)): 
    ?>
    <div class="promo-bar">
        <p><?php echo xss_clean($promo_text); ?></p>
    </div>
    <?php endif; ?>
