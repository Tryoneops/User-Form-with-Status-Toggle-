<?php
// DB connection
$host = 'localhost';
$db = 'testdb';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Insert data if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['age'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $conn->query("INSERT INTO users (name, age) VALUES ('$name', $age)");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Toggle status
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $result = $conn->query("SELECT status FROM users WHERE id=$id");
    if ($row = $result->fetch_assoc()) {
        $new_status = $row['status'] == 1 ? 0 : 1;
        $conn->query("UPDATE users SET status=$new_status WHERE id=$id");
        echo $new_status;
    } else {
        echo "error";
    }
    exit();
}

// Fetch records
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Form</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f4f4f4; }
    input, button { padding: 8px; margin: 5px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; background: #fff; }
    th, td { padding: 10px; border: 1px solid #ddd; }
    .toggle-btn.active { background-color: #4CAF50; color: white; }
  </style>
</head>
<body>

<h2>User Form</h2>
<form method="post">
  <input type="text" name="name" placeholder="Name" required>
  <input type="number" name="age" placeholder="Age" required>
  <button type="submit">Submit</button>
</form>

<table>
  <tr><th>ID</th><th>Name</th><th>Age</th><th>Status</th><th>Toggle</th></tr>
  <?php while ($row = $users->fetch_assoc()): ?>
  <tr data-id="<?= $row['id'] ?>">
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= $row['age'] ?></td>
    <td class="status"><?= $row['status'] ?></td>
    <td><button class="toggle-btn <?= $row['status'] ? 'active' : '' ?>">Toggle</button></td>
  </tr>
  <?php endwhile; ?>
</table>

<script>
document.querySelectorAll('.toggle-btn').forEach(button => {
  button.addEventListener('click', () => {
    const tr = button.closest('tr');
    const id = tr.dataset.id;

    fetch(`?toggle_id=${id}`)
      .then(res => res.text())
      .then(newStatus => {
        if (newStatus === "error") return alert("Toggle failed");
        tr.querySelector('.status').textContent = newStatus;
        button.classList.toggle('active', newStatus === "1");
      });
  });
});
</script>

</body>
</html>
