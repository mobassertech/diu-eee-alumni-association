<?php

session_start();

require_once __DIR__ . '/config.php';


/*
|--------------------------------------------------------------------------
| SECURITY CHECK 1
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['receipt_number']) ||
    !isset($_SESSION['verification_code']) ||
    !isset($_SESSION['verification_id'])
) {

    die("
        <div style='
            font-family:Arial;
            max-width:600px;
            margin:80px auto;
            padding:30px;
            text-align:center;
            border-radius:12px;
            background:#fff3f3;
            color:#b42318;
            box-shadow:0 5px 25px rgba(0,0,0,.08);
        '>

            <h2>Registration Verification Required</h2>

            <p>
                Please complete Receipt Number and
                Verification Code verification first.
            </p>

            <a href='join-now.php'
               style='
                   display:inline-block;
                   margin-top:15px;
                   padding:12px 24px;
                   background:#008f5a;
                   color:white;
                   text-decoration:none;
                   border-radius:8px;
               '>
                Go to Join Now
            </a>

        </div>
    ");

}


/*
|--------------------------------------------------------------------------
| Verification Session Data
|--------------------------------------------------------------------------
*/

$receipt_number =
    trim($_SESSION['receipt_number']);

$verification_code =
    trim($_SESSION['verification_code']);

$verification_id =
    (int)$_SESSION['verification_id'];


/*
|--------------------------------------------------------------------------
| SECURITY CHECK 2
|--------------------------------------------------------------------------
*/

$verifyStmt = $conn->prepare("
    SELECT
        id,
        receipt_number,
        verification_code
    FROM alumni_verification
    WHERE id = ?
      AND receipt_number = ?
      AND verification_code = ?
    LIMIT 1
");


if (!$verifyStmt) {

    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );

}


$verifyStmt->bind_param(
    "iss",
    $verification_id,
    $receipt_number,
    $verification_code
);


$verifyStmt->execute();

$verifyResult =
    $verifyStmt->get_result();


if ($verifyResult->num_rows !== 1) {

    $verifyStmt->close();

    session_unset();

    session_destroy();

    die("
        <div style='
            font-family:Arial;
            max-width:600px;
            margin:80px auto;
            padding:30px;
            text-align:center;
            border-radius:12px;
            background:#fff3f3;
            color:#b42318;
            box-shadow:0 5px 25px rgba(0,0,0,.08);
        '>

            <h2>Invalid Verification</h2>

            <p>
                Your Receipt Number and Verification Code
                could not be verified.
            </p>

            <a href='join-now.php'
               style='
                   display:inline-block;
                   margin-top:15px;
                   padding:12px 24px;
                   background:#008f5a;
                   color:white;
                   text-decoration:none;
                   border-radius:8px;
               '>
                Try Again
            </a>

        </div>
    ");

}


$verifyStmt->close();


/*
|--------------------------------------------------------------------------
| SECURITY CHECK 3
|--------------------------------------------------------------------------
| Same receipt number already registered?
|--------------------------------------------------------------------------
*/

$usedStmt = $conn->prepare("
    SELECT Member_id
    FROM member
    WHERE transaction_id = ?
    LIMIT 1
");


if (!$usedStmt) {

    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );

}


$usedStmt->bind_param(
    "s",
    $receipt_number
);


$usedStmt->execute();

$usedResult =
    $usedStmt->get_result();


if ($usedResult->num_rows > 0) {

    $usedStmt->close();

    session_unset();

    session_destroy();

    die("
        <div style='
            font-family:Arial;
            max-width:600px;
            margin:80px auto;
            padding:30px;
            text-align:center;
            border-radius:12px;
            background:#fff8e6;
            color:#8a5a00;
            box-shadow:0 5px 25px rgba(0,0,0,.08);
        '>

            <h2>Registration Already Completed</h2>

            <p>
                This Money Receipt Number has already been
                used for registration.
            </p>

            <a href='login.php'
               style='
                   display:inline-block;
                   margin-top:15px;
                   padding:12px 24px;
                   background:#008f5a;
                   color:white;
                   text-decoration:none;
                   border-radius:8px;
               '>
                Go to Login
            </a>

        </div>
    ");

}


$usedStmt->close();


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$error = "";

$success = false;

$generated_alumni_id = "";


/*
|--------------------------------------------------------------------------
| Registration Submit
|--------------------------------------------------------------------------
*/

if (isset($_POST['register_alumni'])) {


    /*
    |--------------------------------------------------------------------------
    | Basic Information
    |--------------------------------------------------------------------------
    */

    $Name =
        trim($_POST['Name'] ?? '');

    $Email =
        trim($_POST['Email'] ?? '');

    $Phone =
        trim($_POST['Phone'] ?? '');

    $Reg_no =
        trim($_POST['Reg_no'] ?? '');

    $Academic_year =
        trim(
            $_POST['academic_year']
            ??
            $_POST['Academic_year']
            ??
            ''
        );

    $Programme =
        trim($_POST['Programme'] ?? '');

    $Batch_number =
        trim($_POST['Batch_number'] ?? '');

    $Company_name =
        trim($_POST['Company_name'] ?? '');

    $Job_title =
        trim($_POST['Job_title'] ?? '');

    $Address =
        trim($_POST['Address'] ?? '');

    $Password =
        $_POST['Password'] ?? '';

    $ConfirmPassword =
        $_POST['ConfirmPassword'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Social Links
    |--------------------------------------------------------------------------
    */

    $linkedin_url =
        trim($_POST['linkedin_url'] ?? '');

    $facebook_url =
        trim($_POST['facebook_url'] ?? '');

    $twitter_url =
        trim($_POST['twitter_url'] ?? '');

    $instagram_url =
        trim($_POST['instagram_url'] ?? '');

    $research_url =
        trim($_POST['research_url'] ?? '');

    $website_url =
        trim($_POST['website_url'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Profile Picture
    |--------------------------------------------------------------------------
    */

    $profile_picture = '';


    /*
    |--------------------------------------------------------------------------
    | Required Validation
    |--------------------------------------------------------------------------
    */

    if (
        $Name === '' ||
        $Email === '' ||
        $Phone === '' ||
        $Reg_no === '' ||
        $Academic_year === '' ||
        $Programme === '' ||
        $Batch_number === '' ||
        $Address === '' ||
        $Password === ''
    ) {

        $error =
            "Please fill in all required fields.";

    }


    /*
    |--------------------------------------------------------------------------
    | Academic Year Validation
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        if (
            !preg_match(
                '/^[0-9]{4}$/',
                $Academic_year
            )
        ) {

            $error =
                "Academic Year must be a 4-digit year. Example: 2020.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Programme Validation
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        if (
            !in_array(
                $Programme,
                ['EEE', 'EETE'],
                true
            )
        ) {

            $error =
                "Please select a valid Programme / Department.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Email Validation
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        !filter_var(
            $Email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Please enter a valid email address.";

    }


    /*
    |--------------------------------------------------------------------------
    | Password Validation
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        $Password !== $ConfirmPassword
    ) {

        $error =
            "Password and Confirm Password do not match.";

    }


    if (
        $error === '' &&
        strlen($Password) < 6
    ) {

        $error =
            "Password must be at least 6 characters.";

    }


    /*
    |--------------------------------------------------------------------------
    | Phone Validation
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        !preg_match(
            '/^[0-9+\-\s]{10,15}$/',
            $Phone
        )
    ) {

        $error =
            "Please enter a valid phone number.";

    }


    /*
    |--------------------------------------------------------------------------
    | Social URL Validation
    |--------------------------------------------------------------------------
    */

    $socialLinks = [

        'LinkedIn' =>
            $linkedin_url,

        'Facebook' =>
            $facebook_url,

        'Twitter / X' =>
            $twitter_url,

        'Instagram' =>
            $instagram_url,

        'Research' =>
            $research_url,

        'Website' =>
            $website_url

    ];


    if ($error === '') {

        foreach (
            $socialLinks
            as $label => $url
        ) {

            if (
                $url !== '' &&
                !filter_var(
                    $url,
                    FILTER_VALIDATE_URL
                )
            ) {

                $error =
                    "Please enter a valid URL for " .
                    $label . ".";

                break;

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Profile Picture Upload
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error']
            !== UPLOAD_ERR_NO_FILE
    ) {

        $file =
            $_FILES['profile_picture'];


        if (
            $file['error'] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                "Profile picture upload failed. Please try again.";

        }

        elseif (
            $file['size'] >
            2 * 1024 * 1024
        ) {

            $error =
                "Profile picture must be 2 MB or smaller.";

        }

        else {

            $finfo =
                new finfo(FILEINFO_MIME_TYPE);

            $mime =
                $finfo->file(
                    $file['tmp_name']
                );


            $allowedMime = [

                'image/jpeg' =>
                    'jpg',

                'image/png' =>
                    'png',

                'image/webp' =>
                    'webp'

            ];


            if (
                !isset(
                    $allowedMime[$mime]
                )
            ) {

                $error =
                    "Only JPG, PNG and WEBP profile pictures are allowed.";

            }

            else {

                $uploadDir =
                    __DIR__ .
                    '/uploads/alumni_profiles/';


                if (!is_dir($uploadDir)) {

                    if (
                        !mkdir(
                            $uploadDir,
                            0755,
                            true
                        ) &&
                        !is_dir($uploadDir)
                    ) {

                        $error =
                            "Could not create profile picture upload directory.";

                    }

                }


                if ($error === '') {

                    $newFileName =
                        'profile_' .
                        $verification_id .
                        '_' .
                        bin2hex(
                            random_bytes(8)
                        ) .
                        '.' .
                        $allowedMime[$mime];


                    $targetPath =
                        $uploadDir .
                        $newFileName;


                    if (
                        !move_uploaded_file(
                            $file['tmp_name'],
                            $targetPath
                        )
                    ) {

                        $error =
                            "Could not save the profile picture. Please try again.";

                    }

                    else {

                        $profile_picture =
                            'uploads/alumni_profiles/' .
                            $newFileName;

                    }

                }

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Email
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $checkEmail =
            $conn->prepare("
                SELECT Member_id
                FROM member
                WHERE Email = ?
                LIMIT 1
            ");


        if (!$checkEmail) {

            $error =
                "Database Error: " .
                $conn->error;

        }

        else {

            $checkEmail->bind_param(
                "s",
                $Email
            );


            $checkEmail->execute();


            $emailResult =
                $checkEmail->get_result();


            if (
                $emailResult->num_rows > 0
            ) {

                $error =
                    "This email address is already registered.";

            }


            $checkEmail->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Registration Number
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $checkReg =
            $conn->prepare("
                SELECT Member_id
                FROM member
                WHERE Reg_no = ?
                LIMIT 1
            ");


        if (!$checkReg) {

            $error =
                "Database Error: " .
                $conn->error;

        }

        else {

            $checkReg->bind_param(
                "s",
                $Reg_no
            );


            $checkReg->execute();


            $regResult =
                $checkReg->get_result();


            if (
                $regResult->num_rows > 0
            ) {

                $error =
                    "This Registration Number is already registered.";

            }


            $checkReg->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Re-check Receipt
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $receiptCheck =
            $conn->prepare("
                SELECT id
                FROM alumni_verification
                WHERE id = ?
                  AND receipt_number = ?
                  AND verification_code = ?
                LIMIT 1
            ");


        if (!$receiptCheck) {

            $error =
                "Database Error: " .
                $conn->error;

        }

        else {

            $receiptCheck->bind_param(
                "iss",
                $verification_id,
                $receipt_number,
                $verification_code
            );


            $receiptCheck->execute();


            $receiptCheckResult =
                $receiptCheck->get_result();


            if (
                $receiptCheckResult->num_rows !== 1
            ) {

                $error =
                    "Verification expired or is no longer valid.";

            }


            $receiptCheck->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Alumni ID
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        do {

            $generated_alumni_id =
                'DIU-EEE-' .
                strtoupper(
                    substr(
                        bin2hex(
                            random_bytes(4)
                        ),
                        0,
                        8
                    )
                );


            $idCheck =
                $conn->prepare("
                    SELECT Member_id
                    FROM member
                    WHERE alumni_id = ?
                    LIMIT 1
                ");


            if (!$idCheck) {

                $error =
                    "Database Error: " .
                    $conn->error;

                break;

            }


            $idCheck->bind_param(
                "s",
                $generated_alumni_id
            );


            $idCheck->execute();


            $idResult =
                $idCheck->get_result();


            $idExists =
                $idResult->num_rows > 0;


            $idCheck->close();


        } while ($idExists);

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE ALUMNI
    |--------------------------------------------------------------------------
    */

    if ($error === '') {


        /*
        | Login Password
        */

        $HashedPassword =
            md5($Password);


        /*
        | Member Type
        */

        $Member_type =
            'Alumni';


        /*
        | Payment Status
        */

        $payment_status =
            'Paid';


        /*
        | Application Status
        */

        $application_status =
            'Approved';


        /*
        | Receipt Number
        */

        $transaction_id =
            $receipt_number;


        /*
        | Approved Time
        */

        $approved_at =
            date('Y-m-d H:i:s');


        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        $sql = "

            INSERT INTO member

            (

                Name,
                Email,
                Phone,
                Reg_no,
                Academic_year,
                Programme,
                Batch_number,
                Company_name,
                job_title,
                Profile_picture,

                linkedin_url,
                facebook_url,
                twitter_url,
                instagram_url,
                research_url,
                website_url,

                Member_type,
                Password,
                Address,
                payment_status,
                transaction_id,
                application_status,
                alumni_id,
                approved_at

            )

            VALUES

            (

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?

            )

        ";


        $insert =
            $conn->prepare($sql);


        if (!$insert) {

            $error =
                "Database Error: " .
                $conn->error;

        }

        else {


            /*
            |--------------------------------------------------------------------------
            | 24 Parameters
            |--------------------------------------------------------------------------
            */

            $insert->bind_param(

                "ssssssssssssssssssssssss",

                $Name,
                $Email,
                $Phone,
                $Reg_no,
                $Academic_year,
                $Programme,
                $Batch_number,
                $Company_name,
                $Job_title,
                $profile_picture,

                $linkedin_url,
                $facebook_url,
                $twitter_url,
                $instagram_url,
                $research_url,
                $website_url,

                $Member_type,
                $HashedPassword,
                $Address,
                $payment_status,
                $transaction_id,
                $application_status,
                $generated_alumni_id,
                $approved_at

            );


            /*
            |--------------------------------------------------------------------------
            | Execute
            |--------------------------------------------------------------------------
            */

            if ($insert->execute()) {


                /*
                |--------------------------------------------------------------------------
                | Mark Verification Used
                |--------------------------------------------------------------------------
                */

                $statusUpdate =
                    $conn->prepare("

                        UPDATE alumni_verification

                        SET
                            verification_status = 'Verified',
                            verified_at = NOW()

                        WHERE id = ?

                    ");


                if ($statusUpdate) {

                    $statusUpdate->bind_param(
                        "i",
                        $verification_id
                    );

                    $statusUpdate->execute();

                    $statusUpdate->close();

                }


                /*
                |--------------------------------------------------------------------------
                | Clear Session
                |--------------------------------------------------------------------------
                */

                unset(
                    $_SESSION['receipt_number'],
                    $_SESSION['verification_code'],
                    $_SESSION['verification_id']
                );


                /*
                |--------------------------------------------------------------------------
                | Success
                |--------------------------------------------------------------------------
                */

                $success =
                    true;

            }

            else {

                $error =
                    "Registration failed: " .
                    $insert->error;

            }


            $insert->close();

        }

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

<meta
    name="robots"
    content="noindex, nofollow"
>

<title>
    Alumni Registration | DIUEEE Alumni Association
</title>


<link
    rel="icon"
    href="assets/img/favicon.png"
    type="image/x-icon"
>


<link
    rel="stylesheet"
    href="assets/css/bootstrap.min.css"
>


<link
    rel="stylesheet"
    href="assets/css/bootstrap-icons.min.css"
>


<style>

body {

    margin: 0;

    background: #f4f7f9;

    font-family:
        Arial,
        sans-serif;

}


.registration-wrapper {

    min-height: 100vh;

    padding:
        45px 15px;

}


.registration-card {

    width: 100%;

    max-width: 1050px;

    margin: auto;

    background: #ffffff;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 10px 40px
        rgba(0,0,0,.08);

}


.registration-header {

    background:
        linear-gradient(
            135deg,
            #0056a6,
            #008f5a
        );

    padding: 32px;

    text-align: center;

    color: #fff;

}


.registration-header img {

    max-width: 240px;

    height: auto;

    margin-bottom: 15px;

}


.registration-header h1 {

    font-size: 30px;

    font-weight: 700;

    margin-bottom: 8px;

}


.registration-header p {

    margin: 0;

    opacity: .95;

}


.registration-body {

    padding: 35px;

}


.verified-box {

    background: #effaf4;

    border:
        1px solid
        #b8e6ca;

    border-radius: 10px;

    padding: 18px;

    margin-bottom: 30px;

}


.verified-label {

    color: #555;

    font-size: 13px;

}


.verified-value {

    font-size: 16px;

    font-weight: 700;

    color: #008f5a;

}


.section-title {

    color: #0056a6;

    font-size: 21px;

    font-weight: 700;

    border-bottom:
        2px solid
        #eee;

    padding-bottom: 10px;

    margin:
        30px 0 22px;

}


.form-label {

    font-weight: 600;

    color: #333;

}


.form-control,
.form-select {

    min-height: 48px;

    border-radius: 8px;

}


textarea.form-control {

    min-height: 110px;

    resize: vertical;

}


.social-help {

    font-size: 12px;

    color: #7a8793;

    margin-top: 5px;

}


.btn-register {

    width: 100%;

    min-height: 54px;

    border: none;

    border-radius: 8px;

    background: #008f5a;

    color: #fff;

    font-size: 17px;

    font-weight: 700;

    margin-top: 15px;

}


.btn-register:hover {

    background: #007747;

    color: #fff;

}


.success-box {

    text-align: center;

    padding: 70px 30px;

}


.success-icon {

    width: 85px;

    height: 85px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin:
        0 auto 20px;

    border-radius: 50%;

    background: #eaf8f0;

    color: #008f5a;

    font-size: 46px;

    font-weight: 700;

}


.success-box h2 {

    color: #123b70;

    font-weight: 700;

}


.alumni-id-box {

    display: inline-block;

    margin: 15px 0;

    padding:
        12px 25px;

    border-radius: 10px;

    background: #effaf4;

    color: #008f5a;

    font-size: 22px;

    font-weight: 800;

    letter-spacing: 1px;

}


.login-btn {

    display: inline-block;

    margin-top: 15px;

    padding:
        12px 28px;

    background: #008f5a;

    color: #fff;

    border-radius: 8px;

    text-decoration: none;

    font-weight: 600;

}


.login-btn:hover {

    background: #007747;

    color: #fff;

}


@media(max-width:767px) {

    .registration-wrapper {

        padding:
            20px 10px;

    }


    .registration-body {

        padding: 22px;

    }


    .registration-header {

        padding:
            25px 15px;

    }


    .registration-header h1 {

        font-size: 25px;

    }

}

</style>

</head>


<body>


<div class="registration-wrapper">

<div class="registration-card">


<?php if ($success): ?>


<!-- =====================================================
     SUCCESS
====================================================== -->

<div class="success-box">

    <div class="success-icon">

        ✓

    </div>


    <h2>

        Registration Successful!

    </h2>


    <p class="text-muted">

        Congratulations! Your Alumni registration
        has been completed successfully.

    </p>


    <p>

        Your Alumni ID:

    </p>


    <div class="alumni-id-box">

        <?php

        echo htmlspecialchars(
            $generated_alumni_id
        );

        ?>

    </div>


    <p class="text-muted">

        You can now login using your
        registered email and password.

    </p>


    <a
        href="login.php"
        class="login-btn"
    >

        <i class="bi bi-box-arrow-in-right"></i>

        Login to Alumni Account

    </a>

</div>


<?php else: ?>


<!-- =====================================================
     HEADER
====================================================== -->

<div class="registration-header">

    <img
        src="assets/img/sict_alumi_logo.webp"
        alt="DIUEEE Alumni Association"
    >


    <h1>

        Alumni Registration Form

    </h1>


    <p>

        Complete your information to join
        the DIUEEE Alumni Association.

    </p>

</div>


<!-- =====================================================
     BODY
====================================================== -->

<div class="registration-body">


<!-- =====================================================
     VERIFICATION
====================================================== -->

<div class="verified-box">

    <div class="row">

        <div class="col-md-6">

            <div class="verified-label">

                Money Receipt Number

            </div>


            <div class="verified-value">

                <?php

                echo htmlspecialchars(
                    $receipt_number
                );

                ?>

            </div>

        </div>


        <div class="col-md-6 mt-3 mt-md-0">

            <div class="verified-label">

                Verification Code

            </div>


            <div class="verified-value">

                <?php

                echo htmlspecialchars(
                    $verification_code
                );

                ?>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     ERROR
====================================================== -->

<?php if ($error !== ''): ?>

<div class="alert alert-danger">

    <i class="bi bi-exclamation-triangle"></i>

    <?php

    echo htmlspecialchars(
        $error
    );

    ?>

</div>

<?php endif; ?>


<!-- =====================================================
     FORM
====================================================== -->

<form
    method="POST"
    action="registration-form.php"
    enctype="multipart/form-data"
>


<!-- =====================================================
     PERSONAL INFORMATION
====================================================== -->

<h4 class="section-title">

    <i class="bi bi-person"></i>

    Personal Information

</h4>


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

    Full Name *

</label>

<input
    type="text"
    name="Name"
    class="form-control"
    placeholder="Enter your full name"
    value="<?php
        echo htmlspecialchars(
            $_POST['Name'] ?? ''
        );
    ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Email Address *

</label>

<input
    type="email"
    name="Email"
    class="form-control"
    placeholder="name@example.com"
    value="<?php
        echo htmlspecialchars(
            $_POST['Email'] ?? ''
        );
    ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Phone Number / WhatsApp *

</label>

<input
    type="text"
    name="Phone"
    class="form-control"
    placeholder="01XXXXXXXXX"
    value="<?php
        echo htmlspecialchars(
            $_POST['Phone'] ?? ''
        );
    ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Profile Picture

</label>

<input
    type="file"
    name="profile_picture"
    class="form-control"
    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
>

<small class="text-muted">

    JPG, PNG or WEBP — maximum 2 MB

</small>

</div>


</div>


<!-- =====================================================
     ACADEMIC INFORMATION
====================================================== -->

<h4 class="section-title">

    <i class="bi bi-mortarboard"></i>

    Academic Information

</h4>


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

    Registration Number *

</label>

<input
    type="text"
    name="Reg_no"
    class="form-control"
    placeholder="Enter registration number"
    value="<?php
        echo htmlspecialchars(
            $_POST['Reg_no'] ?? ''
        );
    ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Academic Passing Year *

</label>

<input
    type="number"
    name="academic_year"
    class="form-control"
    placeholder="Example: 2020"
    min="1950"
    max="2099"
    step="1"
    value="<?php
        echo htmlspecialchars(
            $_POST['academic_year']
            ??
            $_POST['Academic_year']
            ??
            ''
        );
    ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Programme / Department *

</label>

<select
    name="Programme"
    class="form-select"
    required
>

<option value="">

    Select Programme

</option>


<option
    value="EEE"
    <?php

    echo (
        ($_POST['Programme'] ?? '')
        === 'EEE'
    )
    ? 'selected'
    : '';

    ?>
>

    EEE

</option>


<option
    value="EETE"
    <?php

    echo (
        ($_POST['Programme'] ?? '')
        === 'EETE'
    )
    ? 'selected'
    : '';

    ?>
>

    EETE

</option>

</select>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Batch Number *

</label>

<input
    type="text"
    name="Batch_number"
    class="form-control"
    placeholder="Example: 45"
    value="<?php
        echo htmlspecialchars(
            $_POST['Batch_number'] ?? ''
        );
    ?>"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Company Name

</label>

<input
    type="text"
    name="Company_name"
    class="form-control"
    placeholder="Current company / organization"
    value="<?php
        echo htmlspecialchars(
            $_POST['Company_name'] ?? ''
        );
    ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Job Title

</label>

<input
    type="text"
    name="Job_title"
    class="form-control"
    placeholder="Example: Software Engineer"
    value="<?php
        echo htmlspecialchars(
            $_POST['Job_title'] ?? ''
        );
    ?>"
>

</div>


</div>


<!-- =====================================================
     ADDRESS
====================================================== -->

<h4 class="section-title">

    <i class="bi bi-geo-alt"></i>

    Address Information

</h4>


<div class="mb-3">

<label class="form-label">

    Address *

</label>

<textarea
    name="Address"
    class="form-control"
    placeholder="Enter your current address"
    required
><?php

echo htmlspecialchars(
    $_POST['Address'] ?? ''
);

?></textarea>

</div>


<!-- =====================================================
     SOCIAL LINKS
====================================================== -->

<h4 class="section-title">

    <i class="bi bi-share"></i>

    Social & Professional Links

</h4>


<p class="text-muted">

    Add your social or professional profiles.
    These fields are optional.

</p>


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

    <i class="bi bi-linkedin text-primary"></i>

    LinkedIn Profile

</label>

<input
    type="url"
    name="linkedin_url"
    class="form-control"
    placeholder="https://www.linkedin.com/in/your-name"
    value="<?php
        echo htmlspecialchars(
            $_POST['linkedin_url'] ?? ''
        );
    ?>"
>

<div class="social-help">

    Example:
    https://www.linkedin.com/in/your-name

</div>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    <i class="bi bi-facebook text-primary"></i>

    Facebook Profile

</label>

<input
    type="url"
    name="facebook_url"
    class="form-control"
    placeholder="https://www.facebook.com/your-profile"
    value="<?php
        echo htmlspecialchars(
            $_POST['facebook_url'] ?? ''
        );
    ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    <i class="bi bi-twitter-x"></i>

    Twitter / X

</label>

<input
    type="url"
    name="twitter_url"
    class="form-control"
    placeholder="https://x.com/your-username"
    value="<?php
        echo htmlspecialchars(
            $_POST['twitter_url'] ?? ''
        );
    ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    <i class="bi bi-instagram"></i>

    Instagram

</label>

<input
    type="url"
    name="instagram_url"
    class="form-control"
    placeholder="https://www.instagram.com/your-username"
    value="<?php
        echo htmlspecialchars(
            $_POST['instagram_url'] ?? ''
        );
    ?>"
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    <i class="bi bi-journal-text"></i>

    Research Paper / Google Scholar

</label>

<input
    type="url"
    name="research_url"
    class="form-control"
    placeholder="https://scholar.google.com/..."
    value="<?php
        echo htmlspecialchars(
            $_POST['research_url'] ?? ''
        );
    ?>"
>

<div class="social-help">

    Google Scholar, ResearchGate,
    ORCID or Research Paper URL

</div>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    <i class="bi bi-globe"></i>

    Personal Website / Portfolio

</label>

<input
    type="url"
    name="website_url"
    class="form-control"
    placeholder="https://example.com"
    value="<?php
        echo htmlspecialchars(
            $_POST['website_url'] ?? ''
        );
    ?>"
>

</div>


</div>


<!-- =====================================================
     ACCOUNT SECURITY
====================================================== -->

<h4 class="section-title">

    <i class="bi bi-shield-lock"></i>

    Account Security

</h4>


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

    Password *

</label>

<input
    type="password"
    name="Password"
    class="form-control"
    minlength="6"
    placeholder="Create a password"
    required
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Confirm Password *

</label>

<input
    type="password"
    name="ConfirmPassword"
    class="form-control"
    minlength="6"
    placeholder="Confirm your password"
    required
>

</div>


</div>


<!-- =====================================================
     SUBMIT
====================================================== -->

<button
    type="submit"
    name="register_alumni"
    class="btn-register"
>

    <i class="bi bi-person-check"></i>

    Complete Alumni Registration

</button>


</form>


</div>


<?php endif; ?>


</div>

</div>


<script
    src="assets/js/bootstrap.bundle.min.js"
></script>


</body>

</html>