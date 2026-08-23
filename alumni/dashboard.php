<?php

session_start();

include '../config.php';

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usermail']) ||
    $_SESSION['user_type'] !== 'member'
) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['usermail'];

$message = '';
$message_type = '';

/*
|--------------------------------------------------------------------------
| EXISTING UPLOAD FOLDER
|--------------------------------------------------------------------------
| Folder must already exist:
| uploads/alumni_profiles/
|
| Dashboard will NOT create the folder.
|--------------------------------------------------------------------------
*/

$upload_dir = '../uploads/alumni_profiles/';

if (!is_dir($upload_dir)) {
    $message = "Profile upload folder was not found.";
    $message_type = "danger";
}

/*
|--------------------------------------------------------------------------
| GET ALUMNI INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT * FROM member WHERE Email = ? LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    session_destroy();

    header("Location: ../login.php");
    exit();
}

$member = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| PROFILE PHOTO UPLOAD
|--------------------------------------------------------------------------
*/

if (isset($_POST['upload_photo'])) {

    if (!is_dir($upload_dir)) {

        $message = "Profile upload folder was not found.";
        $message_type = "danger";

    } elseif (
        !isset($_FILES['profile_photo']) ||
        $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK
    ) {

        $message = "Please select a profile photo.";
        $message_type = "danger";

    } else {

        $file = $_FILES['profile_photo'];

        /*
        |----------------------------------------------------------------------
        | MAXIMUM 1 MB
        |----------------------------------------------------------------------
        */

        if ($file['size'] > 1024 * 1024) {

            $message = "Image size must be less than 1 MB.";
            $message_type = "danger";

        } else {

            /*
            |----------------------------------------------------------------------
            | CHECK IMAGE
            |----------------------------------------------------------------------
            */

            $image_info = @getimagesize($file['tmp_name']);

            if ($image_info === false) {

                $message = "Please upload a valid image.";
                $message_type = "danger";

            } else {

                /*
                |----------------------------------------------------------------------
                | ALLOWED IMAGE TYPES
                |----------------------------------------------------------------------
                */

                $allowed_types = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                $mime_type = $image_info['mime'];

                if (!in_array($mime_type, $allowed_types, true)) {

                    $message = "Only JPG, PNG and WEBP images are allowed.";
                    $message_type = "danger";

                } else {

                    /*
                    |----------------------------------------------------------------------
                    | EXTENSION
                    |----------------------------------------------------------------------
                    */

                    if ($mime_type === 'image/jpeg') {

                        $extension = 'jpg';

                    } elseif ($mime_type === 'image/png') {

                        $extension = 'png';

                    } else {

                        $extension = 'webp';
                    }

                    /*
                    |----------------------------------------------------------------------
                    | UNIQUE FILE NAME
                    |----------------------------------------------------------------------
                    */

                    $new_file_name =
                        'alumni_' .
                        (int)$member['Member_id'] .
                        '_' .
                        bin2hex(random_bytes(8)) .
                        '.' .
                        $extension;

                    $destination = $upload_dir . $new_file_name;

                    /*
                    |----------------------------------------------------------------------
                    | MOVE FILE
                    |----------------------------------------------------------------------
                    */

                    if (move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )) {

                        /*
                        |------------------------------------------------------------------
                        | DELETE OLD PHOTO
                        |------------------------------------------------------------------
                        */

                        if (!empty($member['Profile_picture'])) {

                            $old_photo = str_replace('\\', '/', $member['Profile_picture']);

                            $old_photo_name = basename($old_photo);

                            $old_photo_path =
                                $upload_dir . $old_photo_name;

                            if (
                                file_exists($old_photo_path) &&
                                is_file($old_photo_path)
                            ) {
                                @unlink($old_photo_path);
                            }
                        }

                        /*
                        |------------------------------------------------------------------
                        | SAVE DATABASE
                        |------------------------------------------------------------------
                        */

                        $photo_db_path =
                            'uploads/alumni_profiles/' . $new_file_name;

                        $update_stmt = $conn->prepare(
                            "UPDATE member
                             SET Profile_picture = ?
                             WHERE Member_id = ?"
                        );

                        $member_id = (int)$member['Member_id'];

                        $update_stmt->bind_param(
                            "si",
                            $photo_db_path,
                            $member_id
                        );

                        if ($update_stmt->execute()) {

                            $member['Profile_picture'] =
                                $photo_db_path;

                            $message =
                                "Profile photo updated successfully.";

                            $message_type = "success";

                        } else {

                            /*
                            |--------------------------------------------------------------
                            | DATABASE UPDATE FAILED
                            |--------------------------------------------------------------
                            */

                            if (file_exists($destination)) {
                                @unlink($destination);
                            }

                            $message =
                                "Unable to save profile photo.";

                            $message_type = "danger";
                        }

                        $update_stmt->close();

                    } else {

                        $message =
                            "Failed to upload image. Please check folder permission.";

                        $message_type = "danger";
                    }
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| PROFILE PHOTO URL
|--------------------------------------------------------------------------
*/

$profile_photo = '../assets/img/default-profile.png';

if (!empty($member['Profile_picture'])) {

    $db_photo = str_replace(
        '\\',
        '/',
        trim($member['Profile_picture'])
    );

    /*
    | If DB contains:
    | uploads/alumni_profiles/example.jpg
    |
    | Browser path becomes:
    | ../uploads/alumni_profiles/example.jpg
    */

    if (strpos($db_photo, '/') !== false) {

        $photo_path = '../' . ltrim($db_photo, '/');

    } else {

        $photo_path =
            '../uploads/alumni_profiles/' .
            rawurlencode($db_photo);
    }

    if (file_exists($photo_path)) {

        $profile_photo = $photo_path;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Alumni Dashboard | DIU EEE Alumni Association
    </title>

    <link
        rel="icon"
        href="../assets/img/favicon.png"
        type="image/x-icon"
    >

    <link
        rel="stylesheet"
        href="../assets/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        body {
            background: #f5f7fa;
            font-family: Arial, Helvetica, sans-serif;
        }

        .dashboard-wrapper {
            min-height: 100vh;
        }

        .sidebar {
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e5e5e5;
            padding: 25px 15px;
        }

        .sidebar-logo {
            width: 180px;
            max-width: 100%;
            display: block;
            margin: 0 auto 30px;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: .2s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #00a859;
            color: #fff;
        }

        .sidebar-menu .logout-link {
            color: #dc3545;
        }

        .sidebar-menu .logout-link:hover {
            background: #dc3545;
            color: #fff;
        }

        .main-content {
            padding: 35px;
        }

        .topbar {
            background: #fff;
            padding: 18px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,.05);
        }

        .profile-card {
            background: #fff;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 3px 20px rgba(0,0,0,.06);
        }

        .profile-photo-wrapper {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto 20px;
        }

        .profile-photo {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #00a859;
            background: #f1f1f1;
        }

        .photo-edit {
            position: absolute;
            right: 0;
            bottom: 5px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #00a859;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 4px solid #fff;
        }

        .photo-edit:hover {
            background: #008c4a;
        }

        .hidden-file {
            display: none;
        }

        .profile-name {
            font-size: 27px;
            font-weight: 700;
            color: #173b68;
        }

        .profile-email {
            color: #777;
        }

        .info-box {
            background: #f8fafb;
            border-radius: 10px;
            padding: 15px;
            height: 100%;
        }

        .info-label {
            font-size: 13px;
            color: #777;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            color: #173b68;
            font-weight: 600;
        }

        .logout-btn {
            color: #dc3545 !important;
        }

        @media (max-width: 767px) {

            .sidebar {
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }

            .main-content {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<div class="container-fluid dashboard-wrapper">

    <div class="row">

        <!-- =========================================================
             SIDEBAR
        ========================================================== -->

        <div class="col-lg-2 col-md-3 sidebar">

            <img
                src="../assets/img/sict_alumi_logo.webp"
                class="sidebar-logo"
                alt="DIU EEE Alumni Association"
            >

            <div class="sidebar-menu">

                <a
                    href="dashboard.php"
                    class="active"
                >
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>

                <a href="profile.php">
                    <i class="bi bi-person me-2"></i>
                    My Profile
                </a>

                <a href="../events.php">
                    <i class="bi bi-calendar-event me-2"></i>
                    Events
                </a>

                <a href="../alumni-members.php">
                    <i class="bi bi-people me-2"></i>
                    Alumni Members
                </a>

                <a
                    href="../logout.php"
                    class="logout-link"
                >
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>

            </div>

        </div>


        <!-- =========================================================
             MAIN CONTENT
        ========================================================== -->

        <div class="col-lg-10 col-md-9 main-content">

            <!-- TOP BAR -->

            <div class="topbar">

                <div
                    class="d-flex justify-content-between align-items-center"
                >

                    <div>

                        <h4 class="mb-1">
                            Alumni Dashboard
                        </h4>

                        <small class="text-muted">
                            Welcome to DIU EEE Alumni Association
                        </small>

                    </div>

                    <a
                        href="../logout.php"
                        class="btn btn-outline-danger btn-sm"
                    >
                        Logout
                    </a>

                </div>

            </div>


            <!-- MESSAGE -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show"
                    role="alert"
                >

                    <?php echo htmlspecialchars($message); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>


            <!-- PROFILE CARD -->

            <div class="profile-card">

                <div class="row align-items-center">

                    <!-- PHOTO -->

                    <div class="col-md-4 text-center">

                        <div class="profile-photo-wrapper">

                            <img
                                src="<?php echo htmlspecialchars($profile_photo); ?>"
                                class="profile-photo"
                                id="profilePreview"
                                alt="Profile Photo"
                                onerror="this.src='../assets/img/default-profile.png';"
                            >

                            <label
                                for="profile_photo"
                                class="photo-edit"
                                title="Change Profile Photo"
                            >
                                <i class="bi bi-camera-fill"></i>
                            </label>

                        </div>

                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            id="photoForm"
                        >

                            <input
                                type="file"
                                name="profile_photo"
                                id="profile_photo"
                                class="hidden-file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >

                            <button
                                type="submit"
                                name="upload_photo"
                                class="btn btn-success px-4"
                                id="uploadButton"
                                style="display:none;"
                            >
                                <i class="bi bi-upload me-1"></i>
                                Update Photo
                            </button>

                        </form>

                        <small class="text-muted d-block mt-2">
                            JPG / PNG / WEBP<br>
                            Maximum 1 MB
                        </small>

                    </div>


                    <!-- MEMBER INFORMATION -->

                    <div class="col-md-8">

                        <h2 class="profile-name">

                            <?php
                            echo htmlspecialchars(
                                $member['Name']
                                ?? 'Alumni Member'
                            );
                            ?>

                        </h2>

                        <p class="profile-email mb-4">

                            <i class="bi bi-envelope me-1"></i>

                            <?php
                            echo htmlspecialchars($email);
                            ?>

                        </p>


                        <div class="row g-3">

                            <?php if (!empty($member['Reg_no'])): ?>

                                <div class="col-md-6">

                                    <div class="info-box">

                                        <div class="info-label">
                                            Registration No
                                        </div>

                                        <div class="info-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $member['Reg_no']
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($member['Programme'])): ?>

                                <div class="col-md-6">

                                    <div class="info-box">

                                        <div class="info-label">
                                            Programme
                                        </div>

                                        <div class="info-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $member['Programme']
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($member['Batch_number'])): ?>

                                <div class="col-md-6">

                                    <div class="info-box">

                                        <div class="info-label">
                                            Batch
                                        </div>

                                        <div class="info-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $member['Batch_number']
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($member['Academic_year'])): ?>

                                <div class="col-md-6">

                                    <div class="info-box">

                                        <div class="info-label">
                                            Academic Year
                                        </div>

                                        <div class="info-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $member['Academic_year']
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($member['Company_name'])): ?>

                                <div class="col-md-6">

                                    <div class="info-box">

                                        <div class="info-label">
                                            Company
                                        </div>

                                        <div class="info-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $member['Company_name']
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($member['job_title'])): ?>

                                <div class="col-md-6">

                                    <div class="info-box">

                                        <div class="info-label">
                                            Job Title
                                        </div>

                                        <div class="info-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $member['job_title']
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>


                        <div class="mt-4">

                            <a
                                href="profile.php"
                                class="btn btn-success"
                            >
                                <i class="bi bi-pencil-square me-1"></i>
                                Edit Full Profile
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>

const photoInput = document.getElementById('profile_photo');
const preview = document.getElementById('profilePreview');
const uploadButton = document.getElementById('uploadButton');

photoInput.addEventListener('change', function () {

    const file = this.files[0];

    if (!file) {
        uploadButton.style.display = 'none';
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | 1 MB CHECK
    |--------------------------------------------------------------------------
    */

    if (file.size > 1024 * 1024) {

        alert('Image size must be less than 1 MB.');

        this.value = '';

        uploadButton.style.display = 'none';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | FILE TYPE CHECK
    |--------------------------------------------------------------------------
    */

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!allowedTypes.includes(file.type)) {

        alert('Only JPG, PNG and WEBP images are allowed.');

        this.value = '';

        uploadButton.style.display = 'none';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGE PREVIEW
    |--------------------------------------------------------------------------
    */

    const reader = new FileReader();

    reader.onload = function (e) {

        preview.src = e.target.result;

    };

    reader.readAsDataURL(file);

    /*
    |--------------------------------------------------------------------------
    | SHOW UPDATE BUTTON
    |--------------------------------------------------------------------------
    */

    uploadButton.style.display = 'inline-block';

});

</script>

</body>

</html>