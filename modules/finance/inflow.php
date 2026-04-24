<?php
/**
 * Record Sale (Inflow) – Records a medicine sale and decreases stock.
 */
require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle  = 'Record Sale';
$activePage = 'inflow';
$basePath   = '../../';

$pdo = getDBConnection();
$medicines = $pdo->query('SELECT id, name, stock, price FROM medicines WHERE stock > 0 ORDER BY name')->fetchAll();

$errors = [];
$old = ['medicine_id'=>'','quantity'=>'','unit_price'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }

    // Anti-dupe: check idempotency token
    $formToken = $_POST['form_token'] ?? '';
    if (isset($_SESSION['last_form_token']) && $_SESSION['last_form_token'] === $formToken) {
        $errors[] = 'Duplicate submission detected. Please try again.';
    }

    $old['medicine_id'] = (int)($_POST['medicine_id'] ?? 0);
    $old['quantity']    = (int)($_POST['quantity'] ?? 0);
    $old['unit_price']  = (float)($_POST['unit_price'] ?? 0);
    $old['description'] = trim($_POST['description'] ?? '');

    if ($old['medicine_id'] <= 0) $errors[] = 'Please select a medicine.';
    if ($old['quantity'] <= 0)    $errors[] = 'Quantity must be at least 1.';
    if ($old['unit_price'] <= 0)  $errors[] = 'Unit price must be greater than 0.';

    // Verify stock availability
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT name, stock FROM medicines WHERE id = ?');
        $stmt->execute([$old['medicine_id']]);
        $med = $stmt->fetch();

        if (!$med) {
            $errors[] = 'Selected medicine not found.';
        } elseif ($med['stock'] < $old['quantity']) {
            $errors[] = "Insufficient stock. Available: {$med['stock']} units.";
        }
    }

    if (empty($errors)) {
        $totalAmount = $old['quantity'] * $old['unit_price'];
        $txnId = generateTransactionId('SAL');

        $pdo->beginTransaction();
        try {
            // Insert transaction
            $stmt = $pdo->prepare('INSERT INTO transactions (transaction_id, type, medicine_id, quantity, unit_price, total_amount, description, user_id) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$txnId, 'inflow', $old['medicine_id'], $old['quantity'], $old['unit_price'], $totalAmount, $old['description'] ?: "Sale of {$med['name']}", $_SESSION['user_id']]);

            // Decrease stock
            $stmt = $pdo->prepare('UPDATE medicines SET stock = stock - ? WHERE id = ? AND stock >= ?');
            $stmt->execute([$old['quantity'], $old['medicine_id'], $old['quantity']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Stock update failed — possible race condition.');
            }

            $pdo->commit();

            // Save anti-dupe token
            $_SESSION['last_form_token'] = $formToken;

            logAction($_SESSION['user_id'], 'SALE', "Sale $txnId: {$old['quantity']}x {$med['name']} = " . formatCurrency($totalAmount));
            setFlash('success', "Sale recorded! Transaction: $txnId — Total: " . formatCurrency($totalAmount));
            header('Location: inflow.php');
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Transaction failed: ' . $ex->getMessage();
        }
    }
}

// Generate unique form token for anti-dupe
$formToken = bin2hex(random_bytes(16));

require_once __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="flash flash-error">
    <ul style="margin:0;padding-left:1.2rem;">
      <?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="glass" style="padding:1.5rem;max-width:640px;">
  <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">💰 New Sale Transaction</h3>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="form_token" value="<?php echo e($formToken); ?>">

    <div class="form-group">
      <label class="form-label" for="medicine_id">Medicine *</label>
      <select id="medicine_id" name="medicine_id" class="form-select" required>
        <option value="">— Select Medicine —</option>
        <?php foreach ($medicines as $m): ?>
          <option value="<?php echo (int)$m['id']; ?>"
                  data-price="<?php echo $m['price']; ?>"
                  <?php echo $old['medicine_id'] == $m['id'] ? 'selected' : ''; ?>>
            <?php echo e($m['name']); ?> (Stock: <?php echo (int)$m['stock']; ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label" for="quantity">Quantity *</label>
        <input type="number" id="quantity" name="quantity" class="form-input" required min="1"
               value="<?php echo $old['quantity'] ?: ''; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="unit_price">Unit Price (Rp) *</label>
        <input type="number" id="unit_price" name="unit_price" class="form-input" required min="0" step="0.01"
               value="<?php echo $old['unit_price'] ?: ''; ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Total Amount</label>
      <p id="total_display" style="font-size:1.4rem;font-weight:700;color:var(--success);margin-top:.25rem;">Rp 0</p>
    </div>

    <div class="form-group">
      <label class="form-label" for="description">Notes</label>
      <input type="text" id="description" name="description" class="form-input"
             value="<?php echo e($old['description']); ?>" placeholder="Optional notes…">
    </div>

    <button type="submit" class="btn btn-success" style="margin-top:.5rem;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
      Record Sale
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
