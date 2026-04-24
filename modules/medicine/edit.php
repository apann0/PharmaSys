<?php
/**
 * Edit Medicine – Update existing medicine data.
 */
require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle  = 'Edit Medicine';
$activePage = 'medicines';
$basePath   = '../../';

$pdo = getDBConnection();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: list.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM medicines WHERE id = ?');
$stmt->execute([$id]);
$medicine = $stmt->fetch();
if (!$medicine) {
    setFlash('error', 'Medicine not found.');
    header('Location: list.php');
    exit;
}

$errors = [];
$old = $medicine;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }

    $old['name']           = trim($_POST['name'] ?? '');
    $old['category_id']    = (int)($_POST['category_id'] ?? 0);
    $old['stock']          = (int)($_POST['stock'] ?? 0);
    $old['price']          = (float)($_POST['price'] ?? 0);
    $old['purchase_price'] = (float)($_POST['purchase_price'] ?? 0);
    $old['expiry_date']    = trim($_POST['expiry_date'] ?? '');
    $old['description']    = trim($_POST['description'] ?? '');

    if (empty($old['name']))        $errors[] = 'Medicine name is required.';
    if (strlen($old['name']) > 150) $errors[] = 'Name must be 150 characters or less.';
    if ($old['stock'] < 0)          $errors[] = 'Stock cannot be negative.';
    if ($old['price'] < 0)          $errors[] = 'Price cannot be negative.';
    if ($old['purchase_price'] < 0) $errors[] = 'Purchase price cannot be negative.';

    // Check duplicate name (exclude self)
    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM medicines WHERE name = ? AND id != ?');
        $chk->execute([$old['name'], $id]);
        if ($chk->fetchColumn() > 0) $errors[] = 'A medicine with this name already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE medicines SET name=?, category_id=?, stock=?, price=?, purchase_price=?, expiry_date=?, description=? WHERE id=?');
        $stmt->execute([
            $old['name'],
            $old['category_id'] ?: null,
            $old['stock'],
            $old['price'],
            $old['purchase_price'],
            $old['expiry_date'] ?: null,
            $old['description'] ?: null,
            $id,
        ]);

        logAction($_SESSION['user_id'], 'EDIT_MEDICINE', "Updated medicine ID $id: {$old['name']}");
        setFlash('success', "Medicine \"{$old['name']}\" updated successfully.");
        header('Location: list.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="flash flash-error">
    <ul style="margin:0;padding-left:1.2rem;">
      <?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="glass" style="padding:1.5rem;max-width:720px;">
  <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Edit: <?php echo e($medicine['name']); ?></h3>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label" for="name">Medicine Name *</label>
        <input type="text" id="name" name="name" class="form-input" required value="<?php echo e($old['name']); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="category_id">Category</label>
        <select id="category_id" name="category_id" class="form-select">
          <option value="">— Select Category —</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
              <?php echo e($cat['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="stock">Stock *</label>
        <input type="number" id="stock" name="stock" class="form-input" required min="0" value="<?php echo (int)$old['stock']; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="price">Selling Price (Rp) *</label>
        <input type="number" id="price" name="price" class="form-input" required min="0" step="0.01" value="<?php echo $old['price']; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="purchase_price">Purchase Price (Rp)</label>
        <input type="number" id="purchase_price" name="purchase_price" class="form-input" min="0" step="0.01" value="<?php echo $old['purchase_price']; ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="expiry_date">Expiry Date</label>
        <input type="date" id="expiry_date" name="expiry_date" class="form-input" value="<?php echo e($old['expiry_date'] ?? ''); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="description">Description</label>
      <textarea id="description" name="description" class="form-textarea"><?php echo e($old['description'] ?? ''); ?></textarea>
    </div>
    <div style="display:flex;gap:.5rem;margin-top:.5rem;">
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="list.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
