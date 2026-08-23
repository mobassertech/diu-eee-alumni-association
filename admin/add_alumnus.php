```php
<?php

session_start();
include("../config.php");

/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usermail']) ||
    empty($_SESSION['usermail']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'user'
) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$success = "";
$error   = "";


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];


/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if (isset($_POST['member_registration'])) {

    /*
    |--------------------------------------------------------------------------
    | CSRF CHECK
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {

        $error =
            "Invalid security token. Please refresh the page and try again.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | GET FORM DATA
        |--------------------------------------------------------------------------
        */

        $Name =
            trim($_POST['fullname'] ?? '');

        $Email =
            trim($_POST['email'] ?? '');

        $Phone =
            trim($_POST['phone'] ?? '');

        $Reg_no =
            trim($_POST['reg_no'] ?? '');

        $AC_year =
            trim($_POST['academic_year'] ?? '');

        $Programme =
            trim($_POST['programme'] ?? '');

        $Batch =
            trim($_POST['batch_number'] ?? '');

        $Member_type =
            trim($_POST['member_type'] ?? '');

        $PasswordRaw =
            $_POST['password'] ?? '';

        $Address =
            trim($_POST['address'] ?? '');

        $Company =
            trim($_POST['company_name'] ?? '');

        $JobTitle =
            trim($_POST['job_title'] ?? '');


        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $Name === '' ||
            $Email === '' ||
            $Phone === '' ||
            $Reg_no === '' ||
            $AC_year === '' ||
            $Programme === '' ||
            $Batch === '' ||
            $Member_type === '' ||
            $PasswordRaw === '' ||
            $Address === ''
        ) {

            $error =
                "Please fill in all required fields.";

        }


        /*
        |--------------------------------------------------------------------------
        | EMAIL VALIDATION
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
        | PHONE VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $error === '' &&
            !preg_match(
                '/^[0-9]{10,15}$/',
                $Phone
            )
        ) {

            $error =
                "Please enter a valid phone number.";

        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $error === '' &&
            !preg_match(
                '/^[0-9]{4}$/',
                $AC_year
            )
        ) {

            $error =
                "Academic Year must be a 4-digit year. Example: 2020.";

        }


        if (
            $error === '' &&
            (
                (int)$AC_year < 1950 ||
                (int)$AC_year > 2099
            )
        ) {

            $error =
                "Please enter a valid Academic Year between 1950 and 2099.";

        }


        /*
        |--------------------------------------------------------------------------
        | PROGRAMME VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $error === '' &&
            !in_array(
                $Programme,
                ['EEE', 'EETE'],
                true
            )
        ) {

            $error =
                "Please select a valid Programme / Department.";

        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $error === '' &&
            strlen($PasswordRaw) < 6
        ) {

            $error =
                "Password must be at least 6 characters.";

        }


        /*
        |--------------------------------------------------------------------------
        | DUPLICATE EMAIL / REGISTRATION CHECK
        |--------------------------------------------------------------------------
        */

        if ($error === '') {

            $checkSql = "
                SELECT Member_id
                FROM member
                WHERE Email = ?
                   OR Reg_no = ?
                LIMIT 1
            ";

            $checkStmt =
                $conn->prepare($checkSql);


            if (!$checkStmt) {

                $error =
                    "Database Error: " .
                    $conn->error;

            } else {

                $checkStmt->bind_param(
                    "ss",
                    $Email,
                    $Reg_no
                );

                $checkStmt->execute();

                $checkResult =
                    $checkStmt->get_result();


                if (
                    $checkResult->num_rows > 0
                ) {

                    $error =
                        "An alumni with this Email or Registration Number already exists.";

                }


                $checkStmt->close();

            }

        }


        /*
        |--------------------------------------------------------------------------
        | PROFILE PHOTO UPLOAD
        |--------------------------------------------------------------------------
        */

        $Profile_picture = NULL;


        if (
            $error === '' &&
            isset($_FILES['profile_picture'])
        ) {

            if (
                $_FILES['profile_picture']['error']
                !== UPLOAD_ERR_NO_FILE
            ) {

                if (
                    $_FILES['profile_picture']['error']
                    !== UPLOAD_ERR_OK
                ) {

                    $error =
                        "There was an error uploading the profile photo.";

                } else {

                    $file =
                        $_FILES['profile_picture'];


                    /*
                    |--------------------------------------------------------------------------
                    | MAXIMUM 2MB
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $file['size'] >
                        2 * 1024 * 1024
                    ) {

                        $error =
                            "Profile photo must be less than 2MB.";

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | REAL MIME TYPE
                        |--------------------------------------------------------------------------
                        */

                        $finfo =
                            finfo_open(
                                FILEINFO_MIME_TYPE
                            );

                        $mimeType =
                            finfo_file(
                                $finfo,
                                $file['tmp_name']
                            );

                        finfo_close($finfo);


                        $allowedTypes = [

                            'image/jpeg' => 'jpg',

                            'image/png' => 'png',

                            'image/webp' => 'webp'

                        ];


                        if (
                            !isset(
                                $allowedTypes[$mimeType]
                            )
                        ) {

                            $error =
                                "Only JPG, PNG and WEBP images are allowed.";

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | UPLOAD DIRECTORY
                            |--------------------------------------------------------------------------
                            */

                            $uploadDir =
                                "../uploads/alumni_profiles/";


                            if (
                                !is_dir($uploadDir)
                            ) {

                                if (
                                    !mkdir(
                                        $uploadDir,
                                        0755,
                                        true
                                    ) &&
                                    !is_dir($uploadDir)
                                ) {

                                    $error =
                                        "Could not create upload directory.";

                                }

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | SAVE PHOTO
                            |--------------------------------------------------------------------------
                            */

                            if ($error === '') {

                                $extension =
                                    $allowedTypes[$mimeType];


                                $newFileName =
                                    'alumni_' .
                                    bin2hex(
                                        random_bytes(12)
                                    ) .
                                    '.' .
                                    $extension;


                                $destination =
                                    $uploadDir .
                                    $newFileName;


                                if (
                                    move_uploaded_file(
                                        $file['tmp_name'],
                                        $destination
                                    )
                                ) {

                                    $Profile_picture =
                                        'uploads/alumni_profiles/' .
                                        $newFileName;

                                } else {

                                    $error =
                                        "Failed to save profile photo.";

                                }

                            }

                        }

                    }

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | INSERT ALUMNI
        |--------------------------------------------------------------------------
        */

        if ($error === '') {

            /*
            |--------------------------------------------------------------------------
            | MD5 PASSWORD
            |--------------------------------------------------------------------------
            | Existing login system compatibility
            |--------------------------------------------------------------------------
            */

            $Password =
                md5($PasswordRaw);


            /*
            |--------------------------------------------------------------------------
            | GENERATE ALUMNI ID
            |--------------------------------------------------------------------------
            */

            $alumni_id = '';


            do {

                $alumni_id =
                    'DIU-EEE-' .
                    strtoupper(
                        substr(
                            bin2hex(
                                random_bytes(6)
                            ),
                            0,
                            8
                        )
                    );


                $idCheckSql = "
                    SELECT Member_id
                    FROM member
                    WHERE alumni_id = ?
                    LIMIT 1
                ";


                $idCheckStmt =
                    $conn->prepare(
                        $idCheckSql
                    );


                if (!$idCheckStmt) {

                    $error =
                        "Database Error: " .
                        $conn->error;

                    break;

                }


                $idCheckStmt->bind_param(
                    "s",
                    $alumni_id
                );


                $idCheckStmt->execute();


                $idCheckResult =
                    $idCheckStmt->get_result();


                $idExists =
                    $idCheckResult->num_rows > 0;


                $idCheckStmt->close();


            } while ($idExists);


            /*
            |--------------------------------------------------------------------------
            | AUTO STATUS
            |--------------------------------------------------------------------------
            */

            $application_status =
                "Approved";

            $payment_status =
                "Paid";


            /*
            |--------------------------------------------------------------------------
            | APPROVED TIME
            |--------------------------------------------------------------------------
            */

            $approved_at =
                date("Y-m-d H:i:s");


            /*
            |--------------------------------------------------------------------------
            | ACADEMIC YEAR DATABASE COMPATIBILITY
            |--------------------------------------------------------------------------
            |
            | যদি Academic_year এখনো DATE/DATETIME থাকে:
            | 2020 -> 2020-01-01
            |
            | যদি VARCHAR/CHAR থাকে:
            | 2020 -> 2020
            |
            |--------------------------------------------------------------------------
            */

            $academicYearForDatabase =
                $AC_year;


            $yearTypeSql = "
                SELECT DATA_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'member'
                  AND COLUMN_NAME = 'Academic_year'
                LIMIT 1
            ";


            $yearTypeStmt =
                $conn->prepare(
                    $yearTypeSql
                );


            if ($yearTypeStmt) {

                $yearTypeStmt->execute();

                $yearTypeResult =
                    $yearTypeStmt->get_result();

                $yearColumnType =
                    'varchar';


                if (
                    $yearTypeResult->num_rows === 1
                ) {

                    $yearRow =
                        $yearTypeResult->fetch_assoc();

                    $yearColumnType =
                        strtolower(
                            $yearRow['DATA_TYPE']
                        );

                }


                $yearTypeStmt->close();


                if (
                    in_array(
                        $yearColumnType,
                        [
                            'date',
                            'datetime',
                            'timestamp'
                        ],
                        true
                    )
                ) {

                    $academicYearForDatabase =
                        $AC_year . "-01-01";

                }

            }


            /*
            |--------------------------------------------------------------------------
            | INSERT QUERY
            |--------------------------------------------------------------------------
            |
            | NIC বাদ দেওয়া হয়েছে।
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
                    Member_type,
                    Password,
                    Address,
                    Company_name,
                    job_title,
                    Profile_picture,
                    payment_status,
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
                    ?
                )

            ";


            $stmt =
                $conn->prepare($sql);


            if (!$stmt) {

                $error =
                    "Database Error: " .
                    $conn->error;

            } else {

                $stmt->bind_param(

                    "sssssssssssssssss",

                    $Name,

                    $Email,

                    $Phone,

                    $Reg_no,

                    $academicYearForDatabase,

                    $Programme,

                    $Batch,

                    $Member_type,

                    $Password,

                    $Address,

                    $Company,

                    $JobTitle,

                    $Profile_picture,

                    $payment_status,

                    $application_status,

                    $alumni_id,

                    $approved_at

                );


                if ($stmt->execute()) {

                    $success =
                        "Alumnus added successfully. Alumni ID: " .
                        $alumni_id;


                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR FORM
                    |--------------------------------------------------------------------------
                    */

                    $_POST = [];

                } else {

                    $error =
                        "Database Error: " .
                        $stmt->error;

                }


                $stmt->close();

            }

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

    <title>
        Add Alumnus | DIU EEE Alumni Association
    </title>


    <link
        rel="icon"
        href="../assets/img/favicon.png"
        type="image/x-icon"
    >


    <?php include('includes/global_styles.php'); ?>


    <style>

        .photo-preview {

            width: 130px;

            height: 130px;

            border-radius: 50%;

            object-fit: cover;

            border: 4px solid #e8f5ee;

            display: none;

            margin-top: 12px;

        }


        .photo-placeholder {

            width: 130px;

            height: 130px;

            border-radius: 50%;

            background: #f1f5f7;

            border: 2px dashed #b8c5cc;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #7d8b94;

            font-size: 38px;

            margin-top: 12px;

        }


        .required {

            color: red;

        }

    </style>

</head>


<body
    class="layout-fixed sidebar-expand-lg bg-body-tertiary"
>


<div class="app-wrapper">


<?php

include('includes/navbar.php');

include('includes/sidebar.php');

?>


<main class="app-main">


<!-- HEADER -->

<div class="app-content-header">

    <div class="container-fluid">

        <div class="row">

            <div class="col-sm-6">

                <h3 class="mb-0">
                    Add Alumnus
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

                        Add Alumnus

                    </li>

                </ol>

            </div>

        </div>

    </div>

</div>


<!-- CONTENT -->

<div class="app-content">

<div class="container-fluid">

<div class="row">

<div class="col-md-12">


<!-- SUCCESS -->

<?php if ($success !== ''): ?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill"></i>

    <?php
    echo htmlspecialchars($success);
    ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
    ></button>

</div>

<?php endif; ?>


<!-- ERROR -->

<?php if ($error !== ''): ?>

<div class="alert alert-danger alert-dismissible fade show">

    <i class="bi bi-exclamation-triangle-fill"></i>

    <?php
    echo htmlspecialchars($error);
    ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
    ></button>

</div>

<?php endif; ?>


<div class="card card-primary card-outline">


<div class="card-header">

    <div class="card-title">

        <i class="bi bi-person-plus"></i>

        Add New Alumni Member

    </div>

</div>


<form
    method="POST"
    enctype="multipart/form-data"
>


<input
    type="hidden"
    name="csrf_token"
    value="<?php
        echo htmlspecialchars(
            $csrf_token
        );
    ?>"
>


<div class="card-body">


<div class="row g-3">


<!-- =====================================================
     FULL NAME
====================================================== -->

<div class="col-md-12">

<label class="form-label">

    Full Name
    <span class="required">*</span>

</label>


<input
    type="text"
    name="fullname"
    class="form-control"
    placeholder="Enter full name"
    value="<?php
        echo htmlspecialchars(
            $_POST['fullname'] ?? ''
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     EMAIL
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Email
    <span class="required">*</span>

</label>


<input
    type="email"
    name="email"
    class="form-control"
    placeholder="name@example.com"
    value="<?php
        echo htmlspecialchars(
            $_POST['email'] ?? ''
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     PHONE
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Phone Number
    <span class="required">*</span>

</label>


<input
    type="tel"
    name="phone"
    class="form-control"
    placeholder="01XXXXXXXXX"
    value="<?php
        echo htmlspecialchars(
            $_POST['phone'] ?? ''
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     REGISTRATION NUMBER
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Registration Number
    <span class="required">*</span>

</label>


<input
    type="text"
    name="reg_no"
    class="form-control"
    placeholder="Enter registration number"
    value="<?php
        echo htmlspecialchars(
            $_POST['reg_no'] ?? ''
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     ACADEMIC YEAR
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Academic Year
    <span class="required">*</span>

</label>


<input
    type="number"
    name="academic_year"
    class="form-control"
    placeholder="Example: 2020"
    value="<?php
        echo htmlspecialchars(
            $_POST['academic_year'] ?? ''
        );
    ?>"
    min="1950"
    max="2099"
    step="1"
    inputmode="numeric"
    required
>


<small class="text-muted">

    Enter year only. Example: 2020

</small>

</div>


<!-- =====================================================
     BATCH NUMBER
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Batch Number
    <span class="required">*</span>

</label>


<input
    type="text"
    name="batch_number"
    class="form-control"
    placeholder="Example: D-57"
    value="<?php
        echo htmlspecialchars(
            $_POST['batch_number'] ?? ''
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     PROGRAMME
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Programme / Department
    <span class="required">*</span>

</label>


<select
    name="programme"
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
        ($_POST['programme'] ?? '')
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
        ($_POST['programme'] ?? '')
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


<!-- =====================================================
     MEMBER TYPE
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Member Type
    <span class="required">*</span>

</label>


<select
    name="member_type"
    class="form-select"
    required
>


<option value="">

    Select

</option>


<option
    value="Member"
    <?php

    echo (
        ($_POST['member_type'] ?? '')
        === 'Member'
    )
    ? 'selected'
    : '';

    ?>
>

    Member

</option>


<option
    value="Associate Member"
    <?php

    echo (
        ($_POST['member_type'] ?? '')
        === 'Associate Member'
    )
    ? 'selected'
    : '';

    ?>
>

    Associate Member

</option>


</select>

</div>


<!-- =====================================================
     COMPANY
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Company Name

</label>


<input
    type="text"
    name="company_name"
    class="form-control"
    placeholder="Company name"
    value="<?php
        echo htmlspecialchars(
            $_POST['company_name'] ?? ''
        );
    ?>"
>

</div>


<!-- =====================================================
     JOB TITLE
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Job Title

</label>


<input
    type="text"
    name="job_title"
    class="form-control"
    placeholder="e.g. Software Engineer"
    value="<?php
        echo htmlspecialchars(
            $_POST['job_title'] ?? ''
        );
    ?>"
>

</div>


<!-- =====================================================
     PASSWORD
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Password
    <span class="required">*</span>

</label>


<input
    type="password"
    name="password"
    class="form-control"
    placeholder="Create password"
    minlength="6"
    required
>


<small class="text-muted">

    Minimum 6 characters

</small>

</div>


<!-- =====================================================
     PROFILE PHOTO
====================================================== -->

<div class="col-md-6">

<label class="form-label">

    Profile Photo

</label>


<input
    type="file"
    name="profile_picture"
    id="profile_picture"
    class="form-control"
    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
>


<small class="text-muted">

    JPG, PNG or WEBP — Maximum 2MB

</small>


<div>

    <div
        id="photoPlaceholder"
        class="photo-placeholder"
    >

        <i class="bi bi-person"></i>

    </div>


    <img
        id="photoPreview"
        class="photo-preview"
        alt="Profile Preview"
    >

</div>

</div>


<!-- =====================================================
     ADDRESS
====================================================== -->

<div class="col-md-12">

<label class="form-label">

    Address
    <span class="required">*</span>

</label>


<textarea
    name="address"
    class="form-control"
    rows="4"
    placeholder="Enter current address"
    required
><?php

echo htmlspecialchars(
    $_POST['address'] ?? ''
);

?></textarea>

</div>


</div>


<!-- =====================================================
     AUTO APPROVAL NOTICE
====================================================== -->

<div class="alert alert-info mt-4 mb-0">

    <i class="bi bi-info-circle"></i>

    <strong>Admin Added Member:</strong>

    This alumnus will be automatically marked as

    <strong>Approved</strong>

    and

    <strong>Paid</strong>.

    The member will appear directly on the public
    Alumni Members page after saving.

</div>


</div>


<!-- =====================================================
     FOOTER
====================================================== -->

<div class="card-footer">


<button
    class="btn btn-primary"
    name="member_registration"
    type="submit"
>

    <i class="bi bi-person-plus"></i>

    Add Alumni Member

</button>


<a
    href="all_alumni.php"
    class="btn btn-secondary float-end"
>

    <i class="bi bi-people"></i>

    View All Alumni

</a>


</div>


</form>


</div>


</div>

</div>

</div>

</div>


</main>


<?php

include("includes/footer.php");

?>


</div>


<script>

/*
|--------------------------------------------------------------------------
| PROFILE PHOTO PREVIEW
|--------------------------------------------------------------------------
*/

const profileInput =
    document.getElementById(
        'profile_picture'
    );


if (profileInput) {

    profileInput.addEventListener(
        'change',
        function(event) {

            const file =
                event.target.files[0];


            const preview =
                document.getElementById(
                    'photoPreview'
                );


            const placeholder =
                document.getElementById(
                    'photoPlaceholder'
                );


            if (!file) {

                preview.style.display =
                    'none';

                placeholder.style.display =
                    'flex';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | File Size Check
            |--------------------------------------------------------------------------
            */

            if (
                file.size >
                2 * 1024 * 1024
            ) {

                alert(
                    'Profile photo must be less than 2MB.'
                );

                profileInput.value =
                    '';

                preview.style.display =
                    'none';

                placeholder.style.display =
                    'flex';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            const reader =
                new FileReader();


            reader.onload =
                function(e) {

                    preview.src =
                        e.target.result;

                    preview.style.display =
                        'block';

                    placeholder.style.display =
                        'none';

                };


            reader.readAsDataURL(file);

        }
    );

}

</script>


</body>

</html>
```
