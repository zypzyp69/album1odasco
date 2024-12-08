<?php require_once 'core/dbConfig.php'; ?>
<?php require_once 'core/models.php'; ?>
<?php  
if (!isset($_SESSION['username'])) {
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link rel="stylesheet" href="styles/index.css">
	<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>
<body>
	<?php include 'navbar.php'; ?>

	<div class="insertPhotoForm" style="display: flex; justify-content: center;">
		<form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
			<p>
				<label for="#">Description</label>
				<input type="text" name="photoDescription">
			</p>
			<p>
				<label for="#">Photo Upload</label>
				<input type="file" name="image">
				<input type="submit" name="insertPhotoBtn" style="margin-top: 10px;">
			</p>
		</form>
	</div>
	<div class="createAlbumForm" style="display: flex; justify-content: center;">
    <form action="core/handleForms.php" method="POST">
        <p>
            <label for="album_name">Album Name</label>
            <input type="text" name="album_name" required>
            <input type="submit" name="createAlbumBtn" value="Create Album">
        </p>
    </form>
</div>
<?php $albums = getAlbumsByUser($pdo, $_SESSION['user_id']); ?>

<<div class="albums" style="display: flex; flex-wrap: wrap; gap: 15px;">
    <?php foreach ($albums as $album) { ?>
        <div class="album" style="border: 1px solid gray; padding: 10px;">
            <h3><?php echo htmlspecialchars($album['album_name']); ?></h3>
            <a href="viewalbum.php?album_id=<?php echo $album['album_id']; ?>">View Album</a>

            <?php if ($_SESSION['user_id'] == $album['user_id']) { ?>
                <!-- Update Album Name Form -->
                <form action="core/handleForms.php" method="POST" style="margin-top: 5px;">
                    <input type="hidden" name="album_id" value="<?php echo $album['album_id']; ?>">
                    <input type="text" name="new_album_name" value="<?php echo htmlspecialchars($album['album_name']); ?>" required>
                    <input type="submit" name="updateAlbumBtn" value="Update" style="background-color: blue; color: white; border: none; padding: 5px 10px;">
                </form>

                <!-- Delete Album Form -->
                <form action="core/handleForms.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this album? This will delete all the photos inside it as well.')" style="margin-top: 5px;">
                    <input type="hidden" name="album_id" value="<?php echo $album['album_id']; ?>">
                    <input type="submit" name="deleteAlbumBtn" value="Delete Album" style="background-color: red; color: white; border: none; padding: 5px 10px;">
                </form>
            <?php } ?>
        </div>
    <?php } ?>
</div>




	<?php $getAllPhotos = getAllPhotos($pdo); ?>
	<?php foreach ($getAllPhotos as $row) { ?>

	<div class="images" style="display: flex; justify-content: center; margin-top: 25px;">
		<div class="photoContainer" style="background-color: ghostwhite; border-style: solid; border-color: gray;width: 50%;">

			<img src="images/<?php echo $row['photo_name']; ?>" alt="" style="width: 100%;">

			<div class="photoDescription" style="padding:25px;">
				<a href="profile.php?username=<?php echo $row['username']; ?>"><h2><?php echo $row['username']; ?></h2></a>
				<p><i><?php echo $row['date_added']; ?></i></p>
				<h4><?php echo $row['description']; ?></h4>

				<?php if ($_SESSION['username'] == $row['username']) { ?>
					<a href="editphoto.php?photo_id=<?php echo $row['photo_id']; ?>" style="float: right;"> Edit </a>
					<br>
					<br>
					<a href="deletephoto.php?photo_id=<?php echo $row['photo_id']; ?>" style="float: right;"> Delete</a>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php } ?>
</body>
</html>