document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function() {

        if(confirm("Delete product?")) {

            fetch('delete.php?id=' + this.dataset.id)
            .then(res => res.text())
            .then(() => location.reload());

        }
    });
});