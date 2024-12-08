<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

$album_id = $_GET['album_id'];
$photos = getPhotosByAlbum($pdo, $album_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album Photos</title>
    <style>
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #fafafa;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-top: 20px;
        }

        /* Grid layout for photos */
        .photos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            padding: 20px;
        }

        .photo {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .photo p {
            padding: 10px;
            font-size: 1rem;
            color: #333;
            text-align: center;
        }

        /* Delete button styling */
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        /* Style for the upload form */
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 500px;
        }

        form label {
            font-size: 1.1rem;
            color: #333;
            display: block;
            margin-bottom: 8px;
        }

        form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        form input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Footer style */
        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Photos in Album</h1>
    <div class="photos">
        <?php foreach ($photos as $photo) { ?>
            <div class="photo">
                <img src="images/<?php echo $photo['photo_name']; ?>" alt="Photo">
                <p><?php echo $photo['description']; ?></p>

                <!-- Delete button form -->
                <form action="core/handleForms.php" method="POST" style="display: inline;">
                    <input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>">
                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                    <input type="submit" name="deletePhotoBtn" value="Delete" class="delete-btn">
                </form>
            </div>
        <?php } ?>
    </div>

    <h2 style="text-align:center; margin-top: 40px;">Upload Photos to This Album</h2>
    <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
        <p>
            <label for="photo">Upload Photos:</label>
            <input type="file" name="photos[]" multiple required>
        </p>
        <p>
            <label for="description">Description (Optional):</label>
            <input type="text" name="description" placeholder="Photo description">
        </p>
        <p>
            <label for="album_id">Select Album:</label>
            <select name="album_id" required>
                <?php
                // Fetch albums for the logged-in user
                $albums = getAlbumsByUser($pdo, $_SESSION['user_id']);
                foreach ($albums as $album) {
                    echo "<option value='{$album['album_id']}'>{$album['album_name']}</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <input type="submit" name="uploadPhotosBtn" value="Upload Photos">
        </p>
    </form>

    <footer>
        <p>Photo Album System &copy; 2024</p>
    </footer>
</body>
</html>
