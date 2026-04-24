<?php
/**
 * Medicine List – View all medicines with search and pagination.
 */
require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle  = 'Medicines';
$activePage = 'medicines';
$basePath   = '../../';

$pdo = getDBConnection();

// Search & Pagination
$search   = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 15;
$offset   = ($page - 1) * $perPage;

$where  = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE m.name LIKE ? OR c.name LIKE ?';
    $params = ["%$search%", "%$search%"];
}

// Count total
$countSql = "SELECT COUNT(*) FROM medicines m LEFT JOIN categories c ON m.category_id = c.id $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Fetch page
$sql = "SELECT m.*, c.name AS category_name
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.id
        $where
        ORDER BY m.name ASC
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
  <form method="GET" style="display:flex;gap:.5rem;flex:1;max-width:400px;">
    <input type="text" name="q" class="form-input" placeholder="Search medicines…"
           value="<?php echo e($search); ?>" style="flex:1;">
    <button type="submit" class="btn btn-secondary">Search</button>
  </form>
  <a href="add.php" class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    Add Medicine
  </a>
</div>

<div class="glass" style="padding:1.25rem;">
  <?php if (empty($medicines)): ?>
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
      <p>No medicines found. <?php echo $search ? 'Try a different search.' : 'Add your first medicine!'; ?></p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table" id="medicine-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Expiry Date</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($medicines as $i => $med): ?>
          <tr>
            <td style="color:var(--text-secondary);"><?php echo $offset + $i + 1; ?></td>
            <td style="font-weight:600;"><?php echo e($med['name']); ?></td>
            <td><?php echo e($med['category_name'] ?? '—'); ?></td>
            <td>
              <?php if ((int)$med['stock'] === 0): ?>
                <span class="badge badge-danger">Out of Stock</span>
              <?php elseif ((int)$med['stock'] <= 10): ?>
                <span class="badge badge-warning"><?php echo (int)$med['stock']; ?></span>
              <?php else: ?>
                <span class="badge badge-success"><?php echo (int)$med['stock']; ?></span>
              <?php endif; ?>
            </td>
            <td><?php echo formatCurrency($med['price']); ?></td>
            <td>
              <?php if ($med['expiry_date']): ?>
                <?php
                  $exp = strtotime($med['expiry_date']);
                  $cls = $exp < time() ? 'badge-danger' : ($exp < strtotime('+30 days') ? 'badge-warning' : 'badge-info');
                ?>
                <span class="badge <?php echo $cls; ?>"><?php echo date('d M Y', $exp); ?></span>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td style="text-align:right;">
              <div style="display:flex;gap:.35rem;justify-content:flex-end;">
                <a href="edit.php?id=<?php echo (int)$med['id']; ?>" class="btn btn-sm btn-secondary" title="Edit">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                  Edit
                </a>
                <button class="btn btn-sm btn-danger" data-delete-url="delete.php?id=<?php echo (int)$med['id']; ?>"
                        data-delete-name="<?php echo e($med['name']); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php $qs = http_build_query(array_merge($_GET, ['page' => $p])); ?>
        <a href="?<?php echo $qs; ?>" class="<?php echo $p === $page ? 'active' : ''; ?>"><?php echo $p; ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
