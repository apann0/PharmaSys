<?php
/**
 * Record Expense (Outflow) – Purchases or operational costs.
 */
require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle  = 'Record Expense';
$activePage = 'outflow';
$basePath   = '../../';

$pdo = getDBConnection();
$medicines = $pdo->query('SELECT id, name, purchase_price FROM medicines ORDER BY name')->fetchAll();

$errors = [];
$old = ['medicine_id'=>'','quantity'=>'','unit_price'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }

    $formToken = $_POST['form_token'] ?? '';
    if (isset($_SESSION['last_form_token_out']) && $_SESSION['last_form_token_out'] === $formToken) {
        $errors[] = 'Duplicate submission detected.';
    }

    $old['medicine_id'] = (int)($_POST['medicine_id'] ?? 0);
    $old['quantity']    = (int)($_POST['quantity'] ?? 0);
    $old['unit_price']  = (float)($_POST['unit_price'] ?? 0);
    $old['description'] = trim($_POST['description'] ?? '');

    if ($old['unit_price'] <= 0)  $errors[] = 'Amount must be greater than 0.';
    if ($old['quantity'] <= 0 && $old['medicine_id'] > 0) $errors[] = 'Quantity must be at least 1 for medicine purchases.';
    if ($old['medicine_id'] === 0 && empty($old['description'])) $errors[] = 'Please provide a description for operational costs.';

    if (empty($errors)) {
        $totalAmount = $old['medicine_id'] > 0 ? $old['quantity'] * $old['unit_price'] : $old['unit_price'];
        $qty = $old['medicine_id'] > 0 ? $old['quantity'] : 0;
        $txnId = generateTransactionId('EXP');

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO transactions (transaction_id, type, medicine_id, quantity, unit_price, total_amount, description, user_id) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $txnId, 'outflow',
                $old['medicine_id'] ?: null,
                $qty,
                $old['unit_price'],
                $totalAmount,
                $old['description'] ?: 'Medicine purchase',
                $_SESSION['user_id']
            ]);

            // If medicine purchase, increase stock
            if ($old['medicine_id'] > 0 && $qty > 0) {
                $pdo->prepare('UPDATE medicines SET stock = stock + ? WHERE id = ?')
                    ->execute([$qty, $old['medicine_id']]);
            }

            $pdo->commit();
            $_SESSION['last_form_token_out'] = $formToken;

            logAction($_SESSION['user_id'], 'EXPENSE', "Expense $txnId: " . formatCurrency($totalAmount));
            setFlash('success', "Expense recorded! Transaction: $txnId — Total: " . formatCurrency($totalAmount));
            header('Location: outflow.php');
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Transaction failed: ' . $ex->getMessage();
        }
    }
}

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
  <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">📤 New Expense / Purchase</h3>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="form_token" value="<?php echo e($formToken); ?>">

    <div class="form-group">
      <label class="form-label" for="medicine_id">Medicine (optional – leave blank for operational costs)</label>
      <select id="medicine_id" name="medicine_id" class="form-select">
        <option value="0">— Operational Cost —</option>
        <?php foreach ($medicines as $m): ?>
          <option value="<?php echo (int)$m['id']; ?>"
                  data-price="<?php echo $m['purchase_price']; ?>"
                  <?php echo $old['medicine_id'] == $m['id'] ? 'selected' : ''; ?>>
            <?php echo e($m['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label" for="quantity">Quantity</label>
        <input type="number" id="quantity" name="quantity" class="form-input" min="0"
               value="<?php echo $old['quantity'] ?: ''; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="unit_price">Unit Price / Amount (Rp) *</label>
        <input type="number" id="unit_price" name="unit_price" class="form-input" required min="0" step="0.01"
               value="<?php echo $old['unit_price'] ?: ''; ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Total Amount</label>
      <p id="total_display" style="font-size:1.4rem;font-weight:700;color:var(--danger);margin-top:.25rem;">Rp 0</p>
    </div>

    <div class="form-group">
      <label class="form-label" for="description">Description *</label>
      <input type="text" id="description" name="description" class="form-input"
             value="<?php echo e($old['description']); ?>" placeholder="e.g. Electricity bill, Medicine restock…">
    </div>

    <button type="submit" class="btn btn-danger" style="margin-top:.5rem;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
      Record Expense
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
