<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="w-100">
<?php include '../includes/topbar.php'; ?>

<div class="container mt-4">

<h4>📂 Categories</h4>

<a href="manage.php" class="btn btn-primary mb-3">+ Add Category</a>

<table class="table table-bordered bg-white">
<tr>
<th>ID</th>
<th>Name</th>
<th>Action</th>
</tr>

<?php
$data = $conn->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
foreach($data as $row):
?>

<tr>
<td><?= $row['id'] ?></td>
<td><?= e($row['name']) ?></td>
<td>
<a href="manage.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

<a href="manage.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>

<?php endforeach; ?>
</table>

</div>
</div>

<?php include '../includes/footer.php'; ?>