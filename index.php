<?php
include 'config.php';

$msg = "";

// Register
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'student')");
    $stmt->bind_param("sss", $name, $email, $pass);

    if ($stmt->execute()) {
        $msg = "Registration successful. Please login.";
    } else {
        $msg = "Email already exists.";
    }
}

// Login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $msg = "Invalid email or password.";
    }
}

// Submit complaint
if (isset($_POST['submit_complaint']) && isLoggedIn()) {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $file_path = null;

    if (!empty($_FILES['evidence']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','mp4','webm','mov'];
        $ext = strtolower(pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $_FILES['evidence']['size'] <= 20*1024*1024) {
            if (!is_dir('uploads')) mkdir('uploads');

            $new_name = uniqid('file_') . "." . $ext;
            $file_path = "uploads/" . $new_name;

            move_uploaded_file($_FILES['evidence']['tmp_name'], $file_path);
        } else {
            $msg = "Invalid file type or file is larger than 20MB.";
        }
    }

    if ($msg == "") {
        $stmt = $conn->prepare("INSERT INTO complaints(user_id,title,category,description,file_path) 
                                VALUES(?,?,?,?,?)");
        $stmt->bind_param("issss", $_SESSION['user_id'], $title, $category, $description, $file_path);
        $stmt->execute();
        $msg = "Complaint submitted successfully.";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>College Complaint System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">

    <h3 class="text-center mb-4">College Complaint Management System</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (!isLoggedIn()): ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h5>Register</h5>
                    <form method="POST">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
                        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                        <button name="register" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-4">
                    <h5>Login</h5>
                    <form method="POST">
                        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                        <button name="login" class="btn btn-success w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="d-flex justify-content-between mb-3">
            <h5>Welcome, <?= $_SESSION['name'] ?></h5>
            <a href="?logout=1" class="btn btn-danger">Logout</a>
        </div>

        <div class="card p-4 mb-4">
            <h5>Submit Complaint</h5>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" class="form-control mb-2" placeholder="Complaint Title" required>

                <select name="category" class="form-select mb-2" required>
                    <option value="">Select Category</option>
                    <option>Hostel</option>
                    <option>Classroom</option>
                    <option>Library</option>
                    <option>Canteen</option>
                    <option>Wi-Fi</option>
                    <option>Transport</option>
                    <option>Other</option>
                </select>

                <textarea name="description" class="form-control mb-2" rows="3" placeholder="Describe your complaint" required></textarea>

                <input type="file" name="evidence" class="form-control mb-2"
                       accept=".jpg,.jpeg,.png,.gif,.mp4,.webm,.mov">

                <button name="submit_complaint" class="btn btn-primary">Submit Complaint</button>
            </form>
        </div>

        <div class="card p-4">
            <h5>My Complaints</h5>
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Admin Reply</th>
                    <th>Evidence</th>
                </tr>

                <?php
                $stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id=? ORDER BY id DESC");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['category'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['admin_reply'] ?: '-' ?></td>
                    <td>
                        <?php if ($row['file_path']): ?>
                            <?php
                            $ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                            $images = ['jpg','jpeg','png','gif'];
                            ?>
                            <?php if (in_array($ext, $images)): ?>
                                <img src="<?= $row['file_path'] ?>" width="100">
                            <?php else: ?>
                                <video src="<?= $row['file_path'] ?>" width="120" controls></video>
                            <?php endif; ?>
                        <?php else: ?>
                            No file
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    <?php endif; ?>
</div>
</body>
</html>