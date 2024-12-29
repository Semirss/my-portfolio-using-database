<?php
session_start();

// Database credentials
$servername = "localhost"; // Update with your database server
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "portfolio"; // Database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: adminlogin.php");
    exit;
}

// Handle form submission for adding a project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    // Handle the image upload
    $targetDir = "uploads/"; // Directory where the uploaded files will be stored
    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (getimagesize($_FILES["image"]["tmp_name"])) {
        if ($_FILES["image"]["size"] < 5000000) { // 5MB limit
            if (in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $image = $targetFile; 
                    $category = $_POST['category'];
                    $github_link = $_POST['github_link'];

                    $stmt = $conn->prepare("INSERT INTO projects (image, category, github_link) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $image, $category, $github_link);
                    $stmt->execute();
                    $stmt->close();

                    $success = "Project added successfully!";
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $error = "Sorry, your file is too large.";
        }
    } else {
        $error = "File is not an image.";
    }
}

// Handle delete project request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $index = $_POST['index'];

    $stmt = $conn->prepare("SELECT image FROM projects WHERE id = ?");
    $stmt->bind_param("i", $index);
    $stmt->execute();
    $stmt->bind_result($image);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $index);
    $stmt->execute();
    $stmt->close();

    if (file_exists($image)) {
        unlink($image);
    }

    $success = "Project deleted successfully!";
}

// Handle delete contact request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_contact'])) {
    $contact_id = $_POST['contact_id'];

    $stmt = $conn->prepare("DELETE FROM contact WHERE id = ?");
    $stmt->bind_param("i", $contact_id);
    $stmt->execute();
    $stmt->close();

    $success = "Contact deleted successfully!";
}

// Fetch all projects from the database
$sql = "SELECT * FROM projects";
$result = $conn->query($sql);
$projects = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
// Handle form submission for updating a project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
    $id = $_POST['id'];
    $category = $_POST['category'];
    $github_link = $_POST['github_link'];

    // Handle the image upload (optional)
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (getimagesize($_FILES["image"]["tmp_name"])) {
            if ($_FILES["image"]["size"] < 5000000) { // 5MB limit
                if (in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        $image = $targetFile;

                        // Update project with new image
                        $stmt = $conn->prepare("UPDATE projects SET image = ?, category = ?, github_link = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $image, $category, $github_link, $id);
                        $stmt->execute();
                        $stmt->close();

                        $success = "Project updated successfully!";
                    } else {
                        $error = "Error uploading the image.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                $error = "Image file is too large.";
            }
        } else {
            $error = "File is not a valid image.";
        }
    } else {
        // Update project without changing the image
        $stmt = $conn->prepare("UPDATE projects SET category = ?, github_link = ? WHERE id = ?");
        $stmt->bind_param("ssi", $category, $github_link, $id);
        $stmt->execute();
        $stmt->close();

        $success = "Project updated successfully!";
    }
}

// Fetch all contacts from the database
$contact_sql = "SELECT * FROM contact";
$contact_result = $conn->query($contact_sql);
$contact = [];
if ($contact_result->num_rows > 0) {
    while ($row = $contact_result->fetch_assoc()) {
        $contact[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link href="adminpage.css" rel="stylesheet">
</head>
<body>
<header>
    <nav>
        <ul>
            <h1>💗Welcome <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
            <form action="logout.php" method="POST">
                <button type="submit" style="color:#fff; background-color:#6f42c1;margin-top: -5px;">Logout</button>
            </form>            
        </ul>
    </nav>
</header>    

<?php if (isset($success)) { ?>
    <p class="success"><?php echo $success; ?></p>
<?php } ?>
<?php if (isset($error)) { ?>
    <p class="error" style="color: red;"><?php echo $error; ?></p>
<?php } ?>

<div class="section">
    <form method="POST" enctype="multipart/form-data">
        <h2>Add New Project</h2>
        <div class="form-group">
            <label for="image">Image:</label>
            <input type="file" id="image" name="image" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Web Design">Web Design</option>
            </select>
        </div>
        <div class="form-group">
            <label for="github_link">GitHub Link:</label>
            <input type="text" id="github_link" name="github_link" required>
        </div>
        <button type="submit" name="add_project">Add Project</button>
    </form>
</div>
<h3>Update Existing Project</h3> 
<div class="section" style="margin-top: -10px;">
   
  <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">Project ID:</label>
            <select id="id" name="id" required>
                <?php foreach ($projects as $project) { ?>
                    <option value="<?php echo $project['id']; ?>"><?php echo $project['id']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="image">New Image (optional):</label>
            <input type="file" id="image" name="image">
        </div>
        <div class="form-group">
            <label for="category">New Category:</label>
            <select id="category" name="category" required>
                <option value="Web Design">Web Design</option>
            </select>
        </div>
        <div class="form-group">
            <label for="github_link">New GitHub Link:</label>
            <input type="text" id="github_link" name="github_link" required>
        </div>
        <button type="submit" name="update_project">Update Project</button>
    </form>
</div>
<h2>Existing Projects</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Image</th>
            <th>Category</th>
            <th>GitHub Link</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project) { ?>
            <tr>
                <td><?php echo $project['id']; ?></td>
                <td><img src="<?php echo htmlspecialchars($project['image']); ?>" alt="Project Image" style="width: 100px;"></td>
                <td><?php echo htmlspecialchars($project['category']); ?></td>
                <td><a href="<?php echo htmlspecialchars($project['github_link']); ?>" target="_blank"><?php echo htmlspecialchars($project['github_link']); ?></a></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="index" value="<?php echo $project['id']; ?>">
                        <button style="background:#ff4a4a;color: #fff;" type="submit" name="delete_project" onclick="return confirm('Are you sure you want to delete this project?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<h2>Contact Data</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contact as $contact) { ?>
            <tr>
                <td><?php echo $contact['id']; ?></td>
                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                <td><?php echo htmlspecialchars($contact['message']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                        <button style="background:#ff4a4a;color: #fff;" type="submit" name="delete_contact" onclick="return confirm('Are you sure you want to delete this contact?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
</body>
</html>
