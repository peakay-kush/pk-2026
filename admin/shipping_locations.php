<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!headers_sent()) {
    ob_start();
    $ob_started = true;
}
$page_title = 'Shipping Locations';
require_once 'header.php';

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_location'])) {
        $name = trim($_POST['name']);
        $fee = floatval($_POST['fee']);
        if ($name !== '' && $fee >= 0) {
            $stmt = $conn->prepare("INSERT INTO shipping_locations (name, fee) VALUES (?, ?)");
            $stmt->execute([$name, $fee]);
            setFlashMessage('Shipping location added successfully!', 'success');
            header('Location: shipping_locations.php');
            exit;
        } else {
            setFlashMessage('Please enter a valid name and fee.', 'error');
        }
    }
    if (isset($_POST['edit_location'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $fee = floatval($_POST['fee']);
        if ($id > 0 && $name !== '' && $fee >= 0) {
            $stmt = $conn->prepare("UPDATE shipping_locations SET name = ?, fee = ? WHERE id = ?");
            $stmt->execute([$name, $fee, $id]);
            setFlashMessage('Shipping location updated!', 'success');
            header('Location: shipping_locations.php');
            exit;
        } else {
            setFlashMessage('Invalid update data.', 'error');
        }
    }
    if (isset($_POST['delete_location'])) {
        $id = intval($_POST['id']);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM shipping_locations WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('Shipping location deleted.', 'success');
            header('Location: shipping_locations.php');
            exit;
        } else {
            setFlashMessage('Invalid location ID.', 'error');
        }
    }
}

// Fetch all shipping locations
$locations = $conn->query("SELECT * FROM shipping_locations ORDER BY name ASC");

?>
<style>
    body {
        background: #f7fafd;
    }

    .page-title {
        color: #2176d2;
        font-size: 2.3rem;
        font-weight: 800;
        margin-bottom: 1.2rem;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .page-title i {
        color: #00e676;
        font-size: 2.1rem;
    }

    .row.g-4.mb-4 {
        display: flex;
        gap: 2.5rem;
        margin-bottom: 2.5rem;
        flex-wrap: wrap;
    }

    .col-md-6 {
        flex: 1 1 350px;
        min-width: 340px;
        max-width: 520px;
        margin-bottom: 1.5rem;
    }

    .card {
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(33, 118, 210, 0.07);
        border: none;
        background: #fff;
    }

    .card-header {
        background: linear-gradient(90deg, #2176d2 70%, #00e676 100%) !important;
        color: #fff !important;
        border-radius: 18px 18px 0 0;
        padding: 1.1rem 1.5rem;
        font-size: 1.2rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .card-body {
        padding: 1.5rem 1.5rem 1.2rem 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #2176d2;
        margin-bottom: 0.3rem;
    }

    .form-control {
        border-radius: 8px;
        border: 1.5px solid #e0e6ed;
        font-size: 1rem;
        padding: 0.7rem 0.9rem;
        margin-bottom: 0.7rem;
        background: #f9fafb;
        transition: border 0.2s;
    }

    .form-control:focus {
        border: 1.5px solid #2176d2;
        outline: none;
    }

    .btn-success {
        background: #00e676;
        border: none;
        font-weight: 700;
        font-size: 1.08rem;
        padding: 0.7rem 1.3rem;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .btn-success:hover {
        background: #00c060;
    }

    .btn-primary {
        background: #2176d2;
        border: none;
    }

    .btn-primary:hover {
        background: #0b63ce;
    }

    .btn-danger {
        background: #e53935;
        border: none;
    }

    .btn-danger:hover {
        background: #b71c1c;
    }

    .table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
    }

    .table thead {
        background: #2176d2;
        color: #fff;
    }

    .table th,
    .table td {
        vertical-align: middle;
        padding: 0.8rem 0.7rem;
        font-size: 1rem;
    }

    .table-hover tbody tr:hover {
        background: #f3f7fb;
    }

    .text-muted {
        color: #888 !important;
    }

    @media (max-width: 900px) {
        .row.g-4.mb-4 {
            flex-direction: column;
            gap: 1.5rem;
        }

        .col-md-6 {
            max-width: 100%;
        }
    }
</style>
<h1 class="page-title"><i class="fas fa-map-marker-alt"></i> Manage Shipping Locations & Fees</h1>
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Location</h5>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <?php echo csrfField(); ?>
                    <div class="col-md-7">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Shipping Fee</label>
                        <input type="number" name="fee" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_location" class="btn btn-success"><i class="fas fa-plus"></i>
                            Add Location</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">All Shipping Locations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Fee</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hasLocations = false;
                            while ($loc = $locations->fetch()):
                                $hasLocations = true;
                                ?>
                                <tr>
                                    <form method="post" class="row g-2 align-items-center">
                                        <?php echo csrfField(); ?>
                                        <td>
                                            <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
                                            <input type="text" name="name"
                                                value="<?php echo htmlspecialchars($loc['name']); ?>" class="form-control"
                                                required>
                                        </td>
                                        <td>
                                            <input type="number" name="fee"
                                                value="<?php echo number_format($loc['fee'], 2); ?>" class="form-control"
                                                min="0" step="0.01" required>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <div style="display:flex; gap:0.6rem;">
                                                <button type="submit" name="edit_location" class="btn btn-sm btn-primary"><i
                                                        class="fas fa-save"></i></button>
                                                <button type="submit" name="delete_location" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this location?');"><i
                                                        class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasLocations): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No shipping locations found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>

<?php
if ($ob_started) {
    ob_end_flush();
}
?>