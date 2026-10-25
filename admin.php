<?php
include 'config.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $reply = trim($_POST['admin_reply']);

    $stmt = $conn->prepare("UPDATE complaints SET status=?, admin_reply=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $reply, $id);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>Admin Dashboard</h3>
        <a href="index.php?logout=1" class="btn btn-danger">Logout</a>
    </div>

    <table class="table table-bordered bg-white">
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Title</th>
            <th>Category</th>
            <th>Description</th>
            <th>Evidence</th>
            <th>Status / Reply</th>
        </tr>

        <?php
        $result = $conn->query("
            SELECT c.*, u.name AS student_name 
            FROM complaints c 
            JOIN users u ON c.user_id=u.id 
            ORDER BY c.id DESC
        ");

        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['category'] ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>

            <td>
                <?php if ($row['file_path']): ?>
                    <?php
                    $ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                    $images = ['jpg','jpeg','png','gif'];
                    ?>
                    <?php if (in_array($ext, $images)): ?>
                        <img src="<?= $row['file_path'] ?>" width="120">
                    <?php else: ?>
                        <video src="<?= $row['file_path'] ?>" width="150" controls></video>
                    <?php endif; ?>
                <?php else: ?>
                    No file
                <?php endif; ?>
            </td>

            <td>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">

                    <select name="status" class="form-select mb-2">
                        <option <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                        <option <?= $row['status']=='In Progress'?'selected':'' ?>>In Progress</option>
                        <option <?= $row['status']=='Resolved'?'selected':'' ?>>Resolved</option>
                        <option <?= $row['status']=='Rejected'?'selected':'' ?>>Rejected</option>
                    </select>

                    <textarea name="admin_reply" class="form-control mb-2" rows="2"><?= $row['admin_reply'] ?></textarea>

                    <button name="update" class="btn btn-primary btn-sm">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>