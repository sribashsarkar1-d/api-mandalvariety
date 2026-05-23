<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="w-100">
<?php include '../includes/topbar.php'; ?>

<div class="container mt-4">

<h4>➕ Manage Category</h4>

<?php
$name = "";

// DELETE
if(isset($_GET['delete'])){
    $conn->query("DELETE FROM categories WHERE id=" . intval($_GET['delete']));
    header("Location: list.php");
}

// EDIT FETCH
if(isset($_GET['id'])){
    $cat = $conn->query("SELECT * FROM categories WHERE id=" . intval($_GET['id']))->fetch();
    $name = $cat['name'];
}

// SAVE
if(isset($_POST['save'])){
    if(isset($_GET['id'])){
        $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_GET['id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$_POST['name']]);
    }
    header("Location: list.php");
}
?>

<form method="POST">

<input type="text" name="name" value="<?= $name ?>" class="form-control mb-2" placeholder="Category Name" required>

<button name="save" class="btn btn-success">Save</button>

</form>

</div>
</div>

<?php include '../includes/footer.php'; ?>