<?php
require_once 'includes/db.php';
$page_title = 'Shipping Locations & Fees';
require_once 'includes/header.php';

$stmt = $conn->query("SELECT name, fee FROM shipping_locations ORDER BY name ASC");
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container py-5">
    <h1 class="mb-4"><i class="fas fa-map-marker-alt"></i> Shipping Locations & Fees</h1>
    <?php if (count($locations) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Location</th>
                        <th>Shipping Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $loc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loc['name']); ?></td>
                            <td><?php echo number_format($loc['fee'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No shipping locations available at this time.</div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
