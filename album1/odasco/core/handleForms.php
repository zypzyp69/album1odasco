<?php  
require_once 'dbConfig.php';
require_once 'models.php';

if (isset($_POST['insertNewUserBtn'])) {
	$username = trim($_POST['username']);
	$first_name = trim($_POST['first_name']);
	$last_name = trim($_POST['last_name']);
	$password = trim($_POST['password']);
	$confirm_password = trim($_POST['confirm_password']);

	if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {

		if ($password == $confirm_password) {

			$insertQuery = insertNewUser($pdo, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
			$_SESSION['message'] = $insertQuery['message'];

			if ($insertQuery['status'] == '200') {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../login.php");
			}

			else {
				$_SESSION['message'] = $insertQuery['message'];
				$_SESSION['status'] = $insertQuery['status'];
				header("Location: ../register.php");
			}

		}
		else {
			$_SESSION['message'] = "Please make sure both passwords are equal";
			$_SESSION['status'] = '400';
			header("Location: ../register.php");
		}

	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}
}

if (isset($_POST['loginUserBtn'])) {
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);

	if (!empty($username) && !empty($password)) {

		$loginQuery = checkIfUserExists($pdo, $username);
		$userIDFromDB = $loginQuery['userInfoArray']['user_id'];
		$usernameFromDB = $loginQuery['userInfoArray']['username'];
		$passwordFromDB = $loginQuery['userInfoArray']['password'];

		if (password_verify($password, $passwordFromDB)) {
			$_SESSION['user_id'] = $userIDFromDB;
			$_SESSION['username'] = $usernameFromDB;
			header("Location: ../index.php");
		}

		else {
			$_SESSION['message'] = "Username/password invalid";
			$_SESSION['status'] = "400";
			header("Location: ../login.php");
		}
	}

	else {
		$_SESSION['message'] = "Please make sure there are no empty input fields";
		$_SESSION['status'] = '400';
		header("Location: ../register.php");
	}

}

if (isset($_GET['logoutUserBtn'])) {
	unset($_SESSION['user_id']);
	unset($_SESSION['username']);
	header("Location: ../login.php");
}


if (isset($_POST['insertPhotoBtn'])) {
	$album_id = $_POST['album_id']; // Get album ID from form

    // Pass $album_id to `insertPhoto` function
    $saveImgToDb = insertPhoto($pdo, $imageName, $_SESSION['username'], $description, $photo_id, $album_id);

	// Get Description
	$description = $_POST['photoDescription'];

	// Get file name
	$fileName = $_FILES['image']['name'];

	// Get temporary file name
	$tempFileName = $_FILES['image']['tmp_name'];

	// Get file extension
	$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

	// Generate random characters for image name
	$uniqueID = sha1(md5(rand(1,9999999)));

	// Combine image name and file extension
	$imageName = $uniqueID.".".$fileExtension;

	// If we want edit a photo
	if (isset($_POST['photo_id'])) {
		$photo_id = $_POST['photo_id'];
	}
	else {
		$photo_id = "";
	}

	// Save image 'record' to database
	$saveImgToDb = insertPhoto($pdo, $imageName, $_SESSION['username'], $description, $photo_id);

	// Store actual 'image file' to images folder
	if ($saveImgToDb) {

		// Specify path
		$folder = "../images/".$imageName;

		// Move file to the specified path 
		if (move_uploaded_file($tempFileName, $folder)) {
			header("Location: ../index.php");
		}
	}

}

if (isset($_POST['deletePhotoBtn'])) {
	$photo_name = $_POST['photo_name'];
	$photo_id = $_POST['photo_id'];
	$deletePhoto = deletePhoto($pdo, $photo_id);

	if ($deletePhoto) {
		unlink("../images/".$photo_name);
		header("Location: ../index.php");
	}

}

if (isset($_POST['createAlbumBtn'])) {
    $album_name = $_POST['album_name'];
    $user_id = $_SESSION['user_id'];

    if (!empty($album_name)) {
        $createAlbum = createAlbum($pdo, $album_name, $user_id);
        if ($createAlbum) {
            header("Location: ../index.php");
        } else {
            $_SESSION['message'] = "Error creating album.";
        }
    } else {
        $_SESSION['message'] = "Album name cannot be empty.";
    }
}

if (isset($_POST['uploadPhotosBtn'])) {
    $description = $_POST['description'] ?? '';
    $album_id = $_POST['album_id']; // Album ID selected in the dropdown

    // Ensure files were uploaded
    if (!empty($_FILES['photos']['name'][0])) {
        $targetDir = "../images/";
        $uploadSuccess = true;

        // Loop through each uploaded file
        foreach ($_FILES['photos']['name'] as $key => $fileName) {
            $targetFilePath = $targetDir . basename($fileName);

            // Move the file to the target directory
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $targetFilePath)) {
                // Save photo details to the database
                $uploadPhoto = insertPhoto($pdo, $fileName, $_SESSION['username'], $description, null, $album_id);

                if (!$uploadPhoto) {
                    $uploadSuccess = false;
                    break; // Exit the loop if any photo fails to upload
                }
            } else {
                $uploadSuccess = false;
                break; // Exit the loop if any file fails to move
            }
        }

        if ($uploadSuccess) {
            $_SESSION['message'] = "Photos uploaded successfully!";
        } else {
            $_SESSION['message'] = "Failed to upload all photos.";
        }
    } else {
        $_SESSION['message'] = "Please select at least one photo to upload.";
    }

    header("Location: ../index.php");
}

if (isset($_POST['deleteAlbumBtn'])) {
    $album_id = $_POST['album_id'];

    // Fetch the album details to get the photo names for deletion
    $photos = getPhotosByAlbum($pdo, $album_id);
    
    // Delete photos from the server
    foreach ($photos as $photo) {
        $photo_path = 'images/' . $photo['photo_name'];
        if (file_exists($photo_path)) {
            unlink($photo_path); // Delete the image file from the server
        }
    }

    // Delete the album from the database
    deleteAlbum($pdo, $album_id);

    // Redirect to the albums page after deletion
    header("Location: ../index.php"); // Or wherever your albums are listed
    exit;
}


if (isset($_POST['updateAlbumBtn'])) {
    $album_id = $_POST['album_id'];
    $new_album_name = trim($_POST['new_album_name']);

    if (!empty($new_album_name)) {
        // Call the function to update the album name
        $updateAlbum = updateAlbum($pdo, $album_id, $new_album_name);

        if ($updateAlbum) {
            $_SESSION['message'] = "Album updated successfully!";
        } else {
            $_SESSION['message'] = "Failed to update album name.";
        }
    } else {
        $_SESSION['message'] = "Album name cannot be empty.";
    }

    header("Location: ../index.php");
    exit;
}

