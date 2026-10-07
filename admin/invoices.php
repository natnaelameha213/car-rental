<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$statuses = ['Draft', 'Sent', 'Paid', 'Overdue', 'Cancelled'];

/* ---------- Actions (POST, CSRF protected) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
        redirect('invoices.php');
    }
    $op = $_POST['op'] ?? '';
    if ($op === 'generate') {
        $rows = $pdo->query("SELECT b.* FROM bookings b LEFT JOIN invoices i ON i.booking_id = b.id
                             WHERE i.id IS NULL AND b.status <> 'Cancelled'")->fetchAll();
        $ins = $pdo->prepare("INSERT INTO invoices (invoice_number, booking_id, user_id, issue_date, due_date, subtotal, tax, discount, total, status)
                              VALUES (?, ?, ?, CURDATE(), ?, ?, 0, ?, ?, ?)");
        $made = 0;
        foreach ($rows as $b) {
            $total = (float)$b['total_amount'];
            $paid = $total > 0 && (float)$b['paid_amount'] >= $total;
            try {
                $ins->execute([generateInvoiceNumber(), $b['id'], $b['user_id'], $b['pickup_date'],
                               $b['subtotal'], $b['discount'] ?? 0, $b['total_amount'], $paid ? 'Paid' : 'Sent']);
                $made++;
            } catch (PDOException $ex) { /* duplicate number: skip, it can be generated again */ }
        }
        setFlash('success', $made . ' invoice(s) generated.');
    } elseif ($op === 'status') {
        $st = $_POST['status'] ?? '';
        if (in_array($st, $statuses, true)) {
            $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$st, (int)($_POST['id'] ?? 0)]);
            setFlash('success', 'Invoice status updated.');
        }
    }
    redirect('invoices.php');
}

/* ---------- Printable single invoice ---------- */
if (isset($_GET['view'])) {
    $s = $pdo->prepare("SELECT i.*, b.booking_number, b.customer_name, b.customer_email, b.customer_phone,
                               b.pickup_date, b.return_date, b.rental_days, b.daily_rate, b.additional_fees, b.paid_amount,
                               c.brand, c.model
                        FROM invoices i JOIN bookings b ON b.id = i.booking_id LEFT JOIN cars c ON c.id = b.car_id
                        WHERE i.id = ?");
    $s->execute([(int)$_GET['view']]);
    $inv = $s->fetch();
    if (!$inv) { http_response_code(404); exit('Invoice not found.'); }
    $company = getSetting($pdo, 'company_name', 'EliteDrive Car Rental');
    $balance = max(0, (float)$inv['total'] - (float)$inv['paid_amount']);
    ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice <?= e($inv['invoice_number']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>@media print{.no-print{display:none!important}}</style></head>
<body class="bg-light"><div class="container py-4" style="max-width:800px">
  <div class="no-print mb-3"><button class="btn btn-dark" onclick="window.print()">Print / Save PDF</button>
    <a class="btn btn-outline-secondary" href="invoices.php">Back</a></div>
  <div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between">
      <div><h3 class="mb-0"><?= e($company) ?></h3><small class="text-muted">Car rental invoice</small></div>
      <div class="text-end"><h4 class="mb-0"><?= e($inv['invoice_number']) ?></h4>
        <small>Issued: <?= e($inv['issue_date']) ?><?= $inv['due_date'] ? '<br>Due: ' . e($inv['due_date']) : '' ?></small><br><?= statusBadge($inv['status']) ?></div>
    </div><hr>
    <div class="row mb-3">
      <div class="col-sm-6"><b>Billed to</b><br><?= e($inv['customer_name']) ?><br><?= e($inv['customer_email']) ?><br><?= e($inv['customer_phone']) ?></div>
      <div class="col-sm-6"><b>Booking</b><br><?= e($inv['booking_number']) ?><br><?= e(trim(($inv['brand'] ?? '') . ' ' . ($inv['model'] ?? ''))) ?><br><?= e($inv['pickup_date']) ?> &rarr; <?= e($inv['return_date']) ?></div>
    </div>
    <table class="table">
      <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
      <tbody>
        <tr><td>Rental: <?= (int)$inv['rental_days'] ?> day(s) &times; <?= formatPrice($inv['daily_rate']) ?></td><td class="text-end"><?= formatPrice($inv['subtotal']) ?></td></tr>
        <?php if ((float)$inv['additional_fees'] > 0): ?><tr><td>Additional fees</td><td class="text-end"><?= formatPrice($inv['additional_fees']) ?></td></tr><?php endif; ?>
        <?php if ((float)$inv['discount'] > 0): ?><tr><td>Discount</td><td class="text-end">-<?= formatPrice($inv['discount']) ?></td></tr><?php endif; ?>
        <?php if ((float)$inv['tax'] > 0): ?><tr><td>Tax</td><td class="text-end"><?= formatPrice($inv['tax']) ?></td></tr><?php endif; ?>
        <tr class="fw-bold"><td>Total</td><td class="text-end"><?= formatPrice($inv['total']) ?></td></tr>
        <tr><td>Paid</td><td class="text-end"><?= formatPrice($inv['paid_amount']) ?></td></tr>
        <tr class="fw-bold"><td>Balance due</td><td class="text-end"><?= formatPrice($balance) ?></td></tr>
      </tbody>
    </table>
    <?php if ($inv['notes']): ?><p class="small text-muted"><?= e($inv['notes']) ?></p><?php endif; ?>
  </div></div></body></html>
    <?php
    exit;
}

/* ---------- List ---------- */
$invoices = $pdo->query("SELECT i.*, b.booking_number, b.customer_name FROM invoices i
                         LEFT JOIN bookings b ON b.id = i.booking_id ORDER BY i.id DESC")->fetchAll();
$missing = (int)$pdo->query("SELECT COUNT(*) FROM bookings b LEFT JOIN invoices i ON i.booking_id = b.id
                             WHERE i.id IS NULL AND b.status <> 'Cancelled'")->fetchColumn();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices | EliteDrive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="admin-sidebar" style="width:260px;flex-shrink:0;">
            <div class="px-3 mb-4">
                <a href="dashboard.php" class="text-decoration-none text-white">
                    <i class="fas fa-car-side text-accent me-2"></i><strong>EliteDrive Admin</strong>
                </a>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a class="nav-link" href="cars.php"><i class="bi bi-car-front me-2"></i>Cars</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>Bookings</a>
                <a class="nav-link" href="customers.php"><i class="bi bi-people me-2"></i>Customers</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a>
                <a class="nav-link active" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a>
                <a class="nav-link" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                <hr class="border-secondary mx-3">
                <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
        </div>
        <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Invoices</h2>
                <form method="POST" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="op" value="generate">
                    <button class="btn btn-accent" <?= $missing ? '' : 'disabled' ?>>Generate missing invoices (<?= $missing ?>)</button>
                </form>
            </div>
            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <div class="card-dark p-3">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr><th>Invoice #</th><th>Booking</th><th>Customer</th><th>Issued</th><th>Total</th><th>Status</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No invoices yet. Use "Generate missing invoices" to create them from bookings.</td></tr>
                            <?php else: foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?= e($inv['invoice_number']) ?></td>
                                    <td><?= e($inv['booking_number'] ?? '-') ?></td>
                                    <td><?= e($inv['customer_name'] ?? '-') ?></td>
                                    <td><?= e($inv['issue_date']) ?></td>
                                    <td class="text-accent"><?= formatPrice($inv['total']) ?></td>
                                    <td><?= statusBadge($inv['status']) ?></td>
                                    <td class="text-end text-nowrap">
                                        <form method="POST" class="d-inline-flex gap-1">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="op" value="status">
                                            <input type="hidden" name="id" value="<?= (int)$inv['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width:auto">
                                                <?php foreach ($statuses as $s): ?><option <?= $inv['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-sm btn-outline-light">Update</button>
                                        </form>
                                        <a class="btn btn-sm btn-outline-info" href="invoices.php?view=<?= (int)$inv['id'] ?>" target="_blank">View / Print</a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
