<?php
$page_title = "Gold & Silver Market Rates";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="table-section">
    <div class="container">
        <h2>Gold & Silver Market Rates</h2>
        <p style="text-align: center; max-width: 600px; margin: -20px auto 40px; color: var(--color-warm-gray); font-size: 0.9rem;">
            Real-time market tracking for precious metals. All prices are stated in Nepalese Rupees (NPR) per Tola (1 Tola = 11.664 grams).
        </p>

        <div class="table-container">
            <table class="luxury-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Effective Date</th>
                        <th>Gold Rate (Per Tola)</th>
                        <th>Silver Rate (Per Tola)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch rates ordered by date descending
                    $selectquery = "SELECT * FROM rate ORDER BY Id DESC";
                    $query = mysqli_query($con, $selectquery);
                    
                    if (mysqli_num_rows($query) > 0) {
                        while ($data = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['Id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($data['Date']); ?></strong></td>
                                <td style="color: var(--color-champagne-gold); font-weight: 500;">NPR <?php echo htmlspecialchars($data['Gold']); ?></td>
                                <td style="color: var(--color-warm-gray);">NPR <?php echo htmlspecialchars($data['Silver']); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align: center;">No market rates registered yet.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 30px; text-align: center; color: var(--color-warm-gray); font-size: 0.8rem; font-style: italic;">
            * Rates are based on regional trading aggregates and updated by the administrative office.
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
