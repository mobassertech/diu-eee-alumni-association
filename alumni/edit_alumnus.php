<?php

include("../config.php");
session_start();

/*
|--------------------------------------------------------------------------
| SECURITY CHECK
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usermail']) ||
    empty($_SESSION['usermail']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'member'
) {
    header("Location: ../login.php");
    exit();
}

$usermail = trim($_SESSION['usermail']);

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| FETCH LOGGED-IN USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM member
    WHERE Email = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database Error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("s", $usermail);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();

    session_destroy();

    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| URL VALIDATION FUNCTION
|--------------------------------------------------------------------------
*/

function validateSocialUrl($url)
{
    $url = trim($url);

    if ($url === '') {
        return true;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = strtolower(parse_url($url, PHP_URL_SCHEME));

    if (!in_array($scheme, ['http', 'https'], true)) {
        return false;
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Basic Information
    |--------------------------------------------------------------------------
    */

    $Name = trim($_POST['fullname'] ?? '');
    $Email = trim($_POST['email'] ?? '');
    $Phone = trim($_POST['phone'] ?? '');
    $NIC = trim($_POST['nic'] ?? '');
    $Reg_no = trim($_POST['id'] ?? '');
    $AC_year = trim($_POST['year'] ?? '');
    $Programme = trim($_POST['programme'] ?? '');
    $Member_type = trim($_POST['member_type'] ?? '');
    $Address = trim($_POST['address'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Social & Professional Links
    |--------------------------------------------------------------------------
    */

    $LinkedIn = trim($_POST['linkedin'] ?? '');
    $Facebook = trim($_POST['facebook'] ?? '');
    $Twitter = trim($_POST['twitter'] ?? '');
    $Instagram = trim($_POST['instagram'] ?? '');
    $ResearchGate = trim($_POST['researchgate'] ?? '');
    $GoogleScholar = trim($_POST['googlescholar'] ?? '');
    $ResearchPaper = trim($_POST['researchpaper'] ?? '');
    $PersonalWebsite = trim($_POST['website'] ?? '');
    $GitHub = trim($_POST['github'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Required Field Validation
    |--------------------------------------------------------------------------
    */

    if (
        $Name === '' ||
        $Email === '' ||
        $Phone === '' ||
        $NIC === '' ||
        $Reg_no === '' ||
        $AC_year === '' ||
        $Programme === '' ||
        $Member_type === '' ||
        $Address === ''
    ) {

        $error = "Please fill in all required fields.";
    }


    /*
    |--------------------------------------------------------------------------
    | Email Validation
    |--------------------------------------------------------------------------
    */

    if ($error === '' && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";
    }


    /*
    |--------------------------------------------------------------------------
    | Phone Validation
    |--------------------------------------------------------------------------
    */

    if ($error === '' && !preg_match('/^[0-9+\-\s]{10,20}$/', $Phone)) {

        $error = "Please enter a valid phone number.";
    }


    /*
    |--------------------------------------------------------------------------
    | Social URL Validation
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $socialLinks = [
            'LinkedIn' => $LinkedIn,
            'Facebook' => $Facebook,
            'Twitter / X' => $Twitter,
            'Instagram' => $Instagram,
            'ResearchGate' => $ResearchGate,
            'Google Scholar' => $GoogleScholar,
            'Research Paper' => $ResearchPaper,
            'Personal Website' => $PersonalWebsite,
            'GitHub' => $GitHub
        ];

        foreach ($socialLinks as $label => $url) {

            if (!validateSocialUrl($url)) {

                $error = $label . " must be a valid URL starting with http:// or https://.";

                break;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check Email Duplicate
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $emailCheck = $conn->prepare("
            SELECT Member_id
            FROM member
            WHERE Email = ?
              AND Member_id != ?
            LIMIT 1
        ");

        if (!$emailCheck) {

            $error = "Database Error: " . $conn->error;

        } else {

            $memberId = (int)$user['Member_id'];

            $emailCheck->bind_param(
                "si",
                $Email,
                $memberId
            );

            $emailCheck->execute();

            $emailResult = $emailCheck->get_result();

            if ($emailResult->num_rows > 0) {

                $error = "This email address is already registered.";
            }

            $emailCheck->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check Registration Number Duplicate
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $regCheck = $conn->prepare("
            SELECT Member_id
            FROM member
            WHERE Reg_no = ?
              AND Member_id != ?
            LIMIT 1
        ");

        if (!$regCheck) {

            $error = "Database Error: " . $conn->error;

        } else {

            $memberId = (int)$user['Member_id'];

            $regCheck->bind_param(
                "si",
                $Reg_no,
                $memberId
            );

            $regCheck->execute();

            $regResult = $regCheck->get_result();

            if ($regResult->num_rows > 0) {

                $error = "This Registration Number is already registered.";
            }

            $regCheck->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $update = $conn->prepare("
            UPDATE member
            SET
                Name = ?,
                Email = ?,
                Phone = ?,
                NIC = ?,
                Reg_no = ?,
                Academic_year = ?,
                Programme = ?,
                Member_type = ?,
                Address = ?,

                LinkedIn = ?,
                Facebook = ?,
                Twitter = ?,
                Instagram = ?,
                ResearchGate = ?,
                GoogleScholar = ?,
                ResearchPaper = ?,
                PersonalWebsite = ?,
                GitHub = ?

            WHERE Member_id = ?
            LIMIT 1
        ");

        if (!$update) {

            $error = "Database Error: " . $conn->error;

        } else {

            $memberId = (int)$user['Member_id'];

            $update->bind_param(
                "ssssssssssssssssssi",
                $Name,
                $Email,
                $Phone,
                $NIC,
                $Reg_no,
                $AC_year,
                $Programme,
                $Member_type,
                $Address,

                $LinkedIn,
                $Facebook,
                $Twitter,
                $Instagram,
                $ResearchGate,
                $GoogleScholar,
                $ResearchPaper,
                $PersonalWebsite,
                $GitHub,

                $memberId
            );


            if ($update->execute()) {

                /*
                |--------------------------------------------------------------------------
                | Update Session Email
                |--------------------------------------------------------------------------
                */

                $_SESSION['usermail'] = $Email;

                $success = "Your profile has been updated successfully.";


                /*
                |--------------------------------------------------------------------------
                | Refresh User Data
                |--------------------------------------------------------------------------
                */

                $refresh = $conn->prepare("
                    SELECT *
                    FROM member
                    WHERE Member_id = ?
                    LIMIT 1
                ");

                if ($refresh) {

                    $refresh->bind_param(
                        "i",
                        $memberId
                    );

                    $refresh->execute();

                    $refreshResult = $refresh->get_result();

                    if ($refreshResult->num_rows === 1) {

                        $user = $refreshResult->fetch_assoc();
                    }

                    $refresh->close();
                }

            } else {

                $error = "Something went wrong. Please try again.";
            }

            $update->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | If Error - Keep Submitted Values
    |--------------------------------------------------------------------------
    */

    if ($error !== '') {

        $user['Name'] = $Name;
        $user['Email'] = $Email;
        $user['Phone'] = $Phone;
        $user['NIC'] = $NIC;
        $user['Reg_no'] = $Reg_no;
        $user['Academic_year'] = $AC_year;
        $user['Programme'] = $Programme;
        $user['Member_type'] = $Member_type;
        $user['Address'] = $Address;

        $user['LinkedIn'] = $LinkedIn;
        $user['Facebook'] = $Facebook;
        $user['Twitter'] = $Twitter;
        $user['Instagram'] = $Instagram;
        $user['ResearchGate'] = $ResearchGate;
        $user['GoogleScholar'] = $GoogleScholar;
        $user['ResearchPaper'] = $ResearchPaper;
        $user['PersonalWebsite'] = $PersonalWebsite;
        $user['GitHub'] = $GitHub;
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
        Update Alumnus Details | Alumni Association
    </title>

    <link
        rel="icon"
        href="../assets/img/favicon.png"
        type="image/x-icon"
    >

    <?php include('includes/global_styles.php'); ?>


    <style>

        .social-section {
            margin-top: 25px;
        }

        .social-title {
            color: #0056a6;
            font-weight: 700;
            font-size: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
            margin-bottom: 10px;
        }

        .social-description {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .social-input .input-group-text {
            min-width: 45px;
            justify-content: center;
            background: #f8fafc;
        }

        .social-input input {
            min-height: 46px;
        }

        .social-help {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        .success-alert {
            border-left: 4px solid #198754;
        }

    </style>

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <?php

    include('includes/navbar.php');
    include('includes/sidebar.php');

    ?>


    <main class="app-main">


        <!-- =========================================================
             PAGE HEADER
        ========================================================== -->

        <div class="app-content-header">

            <div class="container-fluid">

                <div class="row">

                    <div class="col-sm-6">

                        <h3 class="mb-0">
                            Update Alumnus Details
                        </h3>

                    </div>


                    <div class="col-sm-6">

                        <ol class="breadcrumb float-sm-end">

                            <li class="breadcrumb-item">

                                <a href="dashboard.php">
                                    Home
                                </a>

                            </li>

                            <li class="breadcrumb-item active">

                                Update Alumnus Details

                            </li>

                        </ol>

                    </div>

                </div>

            </div>

        </div>



        <!-- =========================================================
             CONTENT
        ========================================================== -->

        <div class="app-content">

            <div class="container-fluid">

                <div class="row g-4">

                    <div class="col-md-12">


                        <div class="card card-primary card-outline mb-4">


                            <!-- Card Header -->

                            <div class="card-header">

                                <div class="card-title">

                                    Update Your Alumni Profile

                                </div>

                            </div>


                            <!-- =================================================
                                 ALERTS
                            ================================================== -->

                            <?php if ($success !== ''): ?>

                                <div class="alert alert-success success-alert m-3">

                                    <i class="bi bi-check-circle-fill"></i>

                                    <?php
                                    echo htmlspecialchars($success);
                                    ?>

                                </div>

                            <?php endif; ?>


                            <?php if ($error !== ''): ?>

                                <div class="alert alert-danger m-3">

                                    <i class="bi bi-exclamation-triangle-fill"></i>

                                    <?php
                                    echo htmlspecialchars($error);
                                    ?>

                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 FORM
                            ================================================== -->

                            <form
                                method="POST"
                                action=""
                            >


                                <div class="card-body">


                                    <!-- =================================================
                                         PERSONAL INFORMATION
                                    ================================================== -->

                                    <h5 class="mb-3 text-primary">

                                        <i class="bi bi-person"></i>

                                        Personal Information

                                    </h5>


                                    <div class="row g-3">


                                        <!-- Member ID -->

                                        <div class="col-md-6">

                                            <label
                                                for="user_id"
                                                class="form-label"
                                            >
                                                Member ID
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="user_id"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $user['Member_id'] ?? ''
                                                    );
                                                ?>"
                                                readonly
                                            >

                                        </div>


                                        <!-- Full Name -->

                                        <div class="col-md-6">

                                            <label
                                                for="fullname"
                                                class="form-label"
                                            >

                                                Full Name

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="fullname"
                                                name="fullname"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $user['Name'] ?? ''
                                                    );
                                                ?>"
                                                required
                                            >

                                        </div>


                                        <!-- Email -->

                                        <div class="col-md-6">

                                            <label
                                                for="email"
                                                class="form-label"
                                            >

                                                Email

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-envelope"></i>

                                                </span>

                                                <input
                                                    type="email"
                                                    class="form-control"
                                                    id="email"
                                                    name="email"
                                                    value="<?php
                                                        echo htmlspecialchars(
                                                            $user['Email'] ?? ''
                                                        );
                                                    ?>"
                                                    required
                                                >

                                            </div>

                                        </div>


                                        <!-- Phone -->

                                        <div class="col-md-6">

                                            <label
                                                for="phone"
                                                class="form-label"
                                            >

                                                Phone Number

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-telephone"></i>

                                                </span>

                                                <input
                                                    type="tel"
                                                    class="form-control"
                                                    id="phone"
                                                    name="phone"
                                                    value="<?php
                                                        echo htmlspecialchars(
                                                            $user['Phone'] ?? ''
                                                        );
                                                    ?>"
                                                    maxlength="20"
                                                    required
                                                >

                                            </div>

                                        </div>


                                        <!-- NIC -->

                                        <div class="col-md-6">

                                            <label
                                                for="nic"
                                                class="form-label"
                                            >

                                                NIC / Passport Number

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nic"
                                                name="nic"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $user['NIC'] ?? ''
                                                    );
                                                ?>"
                                                required
                                            >

                                        </div>


                                        <!-- Registration Number -->

                                        <div class="col-md-6">

                                            <label
                                                for="id"
                                                class="form-label"
                                            >

                                                Registration Number

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                id="id"
                                                name="id"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $user['Reg_no'] ?? ''
                                                    );
                                                ?>"
                                                required
                                            >

                                        </div>


                                        <!-- Academic Year -->

                                        <div class="col-md-6">

                                            <label
                                                for="year"
                                                class="form-label"
                                            >

                                                Academic Year

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <input
                                                type="date"
                                                class="form-control"
                                                id="year"
                                                name="year"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $user['Academic_year'] ?? ''
                                                    );
                                                ?>"
                                                required
                                            >

                                        </div>


                                        <!-- Programme -->

                                        <div class="col-md-6">

                                            <label
                                                for="programme"
                                                class="form-label"
                                            >

                                                Programme

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <select
                                                class="form-control form-select"
                                                id="programme"
                                                name="programme"
                                                required
                                            >

                                                <option value="">
                                                    Select
                                                </option>

                                                <option
                                                    value="Degree"
                                                    <?php
                                                    echo (($user['Programme'] ?? '') === 'Degree')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Degree Programme
                                                </option>

                                                <option
                                                    value="Diploma"
                                                    <?php
                                                    echo (($user['Programme'] ?? '') === 'Diploma')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Diploma Programme
                                                </option>

                                                <option
                                                    value="Advanced Certificate"
                                                    <?php
                                                    echo (($user['Programme'] ?? '') === 'Advanced Certificate')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Advanced Certificate Programme
                                                </option>

                                                <option
                                                    value="Certificate"
                                                    <?php
                                                    echo (($user['Programme'] ?? '') === 'Certificate')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Certificate Programme
                                                </option>

                                            </select>

                                        </div>


                                        <!-- Member Type -->

                                        <div class="col-md-12">

                                            <label
                                                for="member_type"
                                                class="form-label"
                                            >

                                                Member Type

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <select
                                                class="form-control form-select"
                                                id="member_type"
                                                name="member_type"
                                                required
                                            >

                                                <option value="">
                                                    Select
                                                </option>

                                                <option
                                                    value="Member"
                                                    <?php
                                                    echo (($user['Member_type'] ?? '') === 'Member')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Member
                                                </option>

                                                <option
                                                    value="Associate Member"
                                                    <?php
                                                    echo (($user['Member_type'] ?? '') === 'Associate Member')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Associate Member
                                                </option>

                                                <option
                                                    value="Alumni"
                                                    <?php
                                                    echo (($user['Member_type'] ?? '') === 'Alumni')
                                                        ? 'selected'
                                                        : '';
                                                    ?>
                                                >
                                                    Alumni
                                                </option>

                                            </select>

                                        </div>


                                        <!-- Address -->

                                        <div class="col-md-12">

                                            <label
                                                for="address"
                                                class="form-label"
                                            >

                                                Address

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>

                                            <textarea
                                                class="form-control"
                                                id="address"
                                                name="address"
                                                rows="4"
                                                required
                                            ><?php
                                                echo htmlspecialchars(
                                                    $user['Address'] ?? ''
                                                );
                                            ?></textarea>

                                        </div>

                                    </div>



                                    <!-- =================================================
                                         SOCIAL & PROFESSIONAL LINKS
                                    ================================================== -->

                                    <div class="social-section">


                                        <h5 class="social-title">

                                            <i class="bi bi-share"></i>

                                            Social & Professional Links

                                        </h5>


                                        <p class="social-description">

                                            Add your social or professional profiles.
                                            These fields are optional.

                                        </p>


                                        <div class="row g-3">


                                            <!-- LinkedIn -->

                                            <div class="col-md-6">

                                                <label
                                                    for="linkedin"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-linkedin text-primary"></i>

                                                    LinkedIn Profile

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-linkedin"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="linkedin"
                                                        name="linkedin"
                                                        placeholder="https://www.linkedin.com/in/your-name"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['LinkedIn'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                                <div class="social-help">
                                                    Example: https://www.linkedin.com/in/your-name
                                                </div>

                                            </div>



                                            <!-- Facebook -->

                                            <div class="col-md-6">

                                                <label
                                                    for="facebook"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-facebook text-primary"></i>

                                                    Facebook Profile

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-facebook"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="facebook"
                                                        name="facebook"
                                                        placeholder="https://www.facebook.com/your-profile"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['Facebook'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>



                                            <!-- Twitter -->

                                            <div class="col-md-6">

                                                <label
                                                    for="twitter"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-twitter-x"></i>

                                                    Twitter / X

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-twitter-x"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="twitter"
                                                        name="twitter"
                                                        placeholder="https://x.com/your-username"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['Twitter'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>



                                            <!-- Instagram -->

                                            <div class="col-md-6">

                                                <label
                                                    for="instagram"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-instagram"></i>

                                                    Instagram

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-instagram"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="instagram"
                                                        name="instagram"
                                                        placeholder="https://www.instagram.com/your-username"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['Instagram'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>



                                            <!-- ResearchGate -->

                                            <div class="col-md-6">

                                                <label
                                                    for="researchgate"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-journal-text"></i>

                                                    ResearchGate

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-journal-text"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="researchgate"
                                                        name="researchgate"
                                                        placeholder="https://www.researchgate.net/profile/your-name"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['ResearchGate'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>



                                            <!-- Google Scholar -->

                                            <div class="col-md-6">

                                                <label
                                                    for="googlescholar"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-mortarboard"></i>

                                                    Google Scholar

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-mortarboard"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="googlescholar"
                                                        name="googlescholar"
                                                        placeholder="https://scholar.google.com/citations?user=..."
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['GoogleScholar'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>



                                            <!-- Research Paper -->

                                            <div class="col-md-6">

                                                <label
                                                    for="researchpaper"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-file-earmark-text"></i>

                                                    Research Paper / Publication

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-file-earmark-text"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="researchpaper"
                                                        name="researchpaper"
                                                        placeholder="https://doi.org/..."
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['ResearchPaper'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                                <div class="social-help">
                                                    DOI, journal, conference or publication URL
                                                </div>

                                            </div>



                                            <!-- Personal Website -->

                                            <div class="col-md-6">

                                                <label
                                                    for="website"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-globe"></i>

                                                    Personal Website / Portfolio

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-globe"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="website"
                                                        name="website"
                                                        placeholder="https://example.com"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['PersonalWebsite'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>



                                            <!-- GitHub -->

                                            <div class="col-md-6">

                                                <label
                                                    for="github"
                                                    class="form-label"
                                                >

                                                    <i class="bi bi-github"></i>

                                                    GitHub

                                                </label>

                                                <div class="input-group social-input">

                                                    <span class="input-group-text">

                                                        <i class="bi bi-github"></i>

                                                    </span>

                                                    <input
                                                        type="url"
                                                        class="form-control"
                                                        id="github"
                                                        name="github"
                                                        placeholder="https://github.com/your-username"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                $user['GitHub'] ?? ''
                                                            );
                                                        ?>"
                                                    >

                                                </div>

                                            </div>


                                        </div>

                                    </div>


                                </div>


                                <!-- =================================================
                                     FOOTER
                                ================================================== -->

                                <div class="card-footer">


                                    <button
                                        class="btn btn-primary"
                                        name="member_registration"
                                        type="submit"
                                    >

                                        <i class="bi bi-floppy"></i>

                                        &nbsp;

                                        Save Changes

                                    </button>


                                    <a
                                        href="view_alumnus.php"
                                        class="btn float-end"
                                    >

                                        <i class="bi bi-arrow-left-circle"></i>

                                        &nbsp;

                                        Back to Details

                                    </a>


                                </div>


                            </form>


                        </div>


                    </div>

                </div>

            </div>

        </div>


    </main>


    <?php include("includes/footer.php"); ?>


</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Phone Validation
    |--------------------------------------------------------------------------
    */

    const phoneInput = document.getElementById("phone");

    if (phoneInput) {

        phoneInput.addEventListener('input', function () {

            phoneInput.setCustomValidity('');

        });

    }


    /*
    |--------------------------------------------------------------------------
    | URL Validation
    |--------------------------------------------------------------------------
    */

    const socialInputs = document.querySelectorAll(
        'input[type="url"]'
    );

    socialInputs.forEach(function (input) {

        input.addEventListener('blur', function () {

            if (
                input.value !== '' &&
                !input.value.startsWith('http://') &&
                !input.value.startsWith('https://')
            ) {

                input.setCustomValidity(
                    'Please enter a complete URL starting with https://'
                );

            } else {

                input.setCustomValidity('');

            }

        });

    });

</script>


</body>

</html>