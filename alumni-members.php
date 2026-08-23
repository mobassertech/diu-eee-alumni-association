<?php

session_start();

include 'config.php';


/*
|--------------------------------------------------------------------------
| LOGIN STATUS
|--------------------------------------------------------------------------
*/

$isLoggedIn = (
    isset($_SESSION['usermail']) &&
    !empty($_SESSION['usermail']) &&
    isset($_SESSION['user_type']) &&
    $_SESSION['user_type'] === 'member'
);

$loggedInEmail = $isLoggedIn
    ? $_SESSION['usermail']
    : '';


/*
|--------------------------------------------------------------------------
| SEARCH & FILTER
|--------------------------------------------------------------------------
*/

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$batch = isset($_GET['batch'])
    ? trim($_GET['batch'])
    : '';


/*
|--------------------------------------------------------------------------
| GET APPROVED ALUMNI
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            Member_id,
            Name,
            Email,
            Phone,
            Reg_no,
            Academic_year,
            Programme,
            Batch_number,
            Member_type,
            Address,

            Company_name,
            job_title,

            LinkedIn,
            Facebook,
            Twitter,
            Instagram,
            ResearchPaper,
            PersonalWebsite,

            alumni_id,
            approved_at,
            application_status,

            COALESCE(
                NULLIF(Profile_picture, ''),
                NULLIF(profile_photo, ''),
                NULLIF(photo, '')
            ) AS Profile_picture

        FROM member

        WHERE
            application_status = 'Approved'
            OR application_status IS NULL
            OR application_status = ''";


$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= " AND (
                Name LIKE ?
                OR Company_name LIKE ?
                OR job_title LIKE ?
                OR Programme LIKE ?
                OR Reg_no LIKE ?
                OR alumni_id LIKE ?
              )";

    $searchValue = "%" . $search . "%";

    $params = [
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    ];

    $types = "ssssss";
}


/*
|--------------------------------------------------------------------------
| BATCH FILTER
|--------------------------------------------------------------------------
*/

if ($batch !== '') {

    $sql .= " AND (
                CAST(Batch_number AS CHAR) = ?
                OR YEAR(Academic_year) = ?
              )";

    $params[] = $batch;
    $params[] = (int)$batch;

    $types .= "si";
}


/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY Name ASC";


/*
|--------------------------------------------------------------------------
| PREPARE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );

}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$result = $stmt->get_result();

$members = [];


while ($row = $result->fetch_assoc()) {

    $members[] = $row;

}


$stmt->close();


$totalMembers = count($members);


/*
|--------------------------------------------------------------------------
| AVAILABLE BATCHES
|--------------------------------------------------------------------------
*/

$batchQuery = "
    SELECT DISTINCT Batch_number AS batch_value
    FROM member
    WHERE application_status = 'Approved'
      AND Batch_number IS NOT NULL
      AND Batch_number <> ''
    ORDER BY Batch_number DESC
";


$batchResult = $conn->query($batchQuery);

$batches = [];


if ($batchResult) {

    while ($row = $batchResult->fetch_assoc()) {

        if (
            $row['batch_value'] !== null &&
            $row['batch_value'] !== ''
        ) {

            $batches[] =
                $row['batch_value'];

        }

    }

}


/*
|--------------------------------------------------------------------------
| PROFILE IMAGE HELPER
|--------------------------------------------------------------------------
*/

function getProfileImageUrl($profilePicture)
{

    $profilePicture = trim(
        (string)$profilePicture
    );


    if ($profilePicture === '') {

        return '';

    }


    $profilePicture = str_replace(
        '\\',
        '/',
        $profilePicture
    );


    /*
    |--------------------------------------------------------------------------
    | Full URL
    |--------------------------------------------------------------------------
    */

    if (
        preg_match(
            '/^https?:\/\//i',
            $profilePicture
        )
    ) {

        return $profilePicture;

    }


    /*
    |--------------------------------------------------------------------------
    | Already uploads path
    |--------------------------------------------------------------------------
    */

    if (
        strpos(
            $profilePicture,
            'uploads/'
        ) === 0
    ) {

        return $profilePicture;

    }


    /*
    |--------------------------------------------------------------------------
    | Remove ./ and ../
    |--------------------------------------------------------------------------
    */

    $profilePicture = ltrim(
        $profilePicture,
        './'
    );


    /*
    |--------------------------------------------------------------------------
    | Filename only
    |--------------------------------------------------------------------------
    */

    if (
        strpos(
            $profilePicture,
            '/'
        ) === false
    ) {

        return 'uploads/alumni_profiles/' .
               rawurlencode($profilePicture);

    }


    return $profilePicture;

}


/*
|--------------------------------------------------------------------------
| ESCAPE HELPER
|--------------------------------------------------------------------------
*/

function e($value)
{

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );

}


/*
|--------------------------------------------------------------------------
| URL HELPER
|--------------------------------------------------------------------------
*/

function safeUrl($url)
{

    $url = trim((string)$url);

    if ($url === '') {
        return '';
    }

    if (
        !preg_match(
            '/^https?:\/\//i',
            $url
        )
    ) {

        $url = 'https://' . $url;

    }

    return $url;

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
        content="index, follow"
    >

    <title>
        Alumni Members | DIU Alumni Association
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

    <link
        rel="stylesheet"
        href="assets/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background: #f4f7f8;
    color: #173b68;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
}


/* =========================================================
   HERO
========================================================= */

.alumni-hero {

    position: relative;

    overflow: hidden;

    background:
        radial-gradient(
            circle at 8% 90%,
            rgba(255,255,255,.08) 0 120px,
            transparent 121px
        ),
        radial-gradient(
            circle at 94% 10%,
            rgba(255,255,255,.08) 0 155px,
            transparent 156px
        ),
        linear-gradient(
            135deg,
            #104e72 0%,
            #087d66 52%,
            #00a859 100%
        );

    padding: 78px 20px 95px;

    color: #fff;
}


.hero-glow {

    position: absolute;

    width: 400px;
    height: 400px;

    border-radius: 50%;

    background: rgba(255,255,255,.035);

    right: -150px;
    top: -160px;

    pointer-events: none;
}


.hero-content {

    position: relative;

    z-index: 2;

    max-width: 850px;

    margin: auto;

    text-align: center;
}


.hero-icon {

    width: 62px;
    height: 62px;

    margin: 0 auto 18px;

    border-radius: 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: rgba(255,255,255,.12);

    border:
        1px solid
        rgba(255,255,255,.24);

    font-size: 26px;

    backdrop-filter: blur(8px);
}


.alumni-hero h1 {

    margin: 0 0 12px;

    font-size: 46px;

    font-weight: 800;

    letter-spacing: -.6px;

    color: #fff;
}


.alumni-hero p {

    max-width: 720px;

    margin: auto;

    font-size: 16px;

    line-height: 1.8;

    color:
        rgba(255,255,255,.92);
}


.member-count {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 9px;

    margin-top: 25px;

    padding: 11px 22px;

    border-radius: 50px;

    background:
        rgba(255,255,255,.11);

    border:
        1px solid
        rgba(255,255,255,.25);

    font-size: 14px;

    font-weight: 700;

    backdrop-filter: blur(8px);
}


/* =========================================================
   MAIN
========================================================= */

.alumni-main {

    width: 100%;

    max-width: 1350px;

    margin: -48px auto 75px;

    padding: 0 25px;

    position: relative;

    z-index: 10;
}


/* =========================================================
   SEARCH BOX
========================================================= */

.search-box {

    background:
        rgba(255,255,255,.98);

    border:
        1px solid
        #e7eded;

    border-radius: 18px;

    padding: 18px;

    box-shadow:
        0 18px 45px
        rgba(0,0,0,.09);

    display: grid;

    grid-template-columns:
        1.8fr
        .75fr
        .75fr;

    gap: 12px;

    margin-bottom: 42px;
}


.input-wrap {

    position: relative;
}


.input-wrap i {

    position: absolute;

    left: 16px;

    top: 50%;

    transform:
        translateY(-50%);

    color: #00a859;

    font-size: 16px;

    z-index: 2;
}


.search-input {

    width: 100%;

    height: 52px;

    border:
        1px solid
        #dce5e2;

    border-radius: 10px;

    padding:
        0 15px 0 43px;

    outline: none;

    font-size: 14px;

    background: #fff;
}


.batch-select {

    width: 100%;

    height: 52px;

    border:
        1px solid
        #dce5e2;

    border-radius: 10px;

    padding: 0 14px;

    outline: none;

    font-size: 14px;

    background: #fff;
}


.search-input:focus,
.batch-select:focus {

    border-color: #00a859;

    box-shadow:
        0 0 0 3px
        rgba(0,168,89,.10);
}


.search-button {

    height: 52px;

    border: none;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #00a859,
            #008e4c
        );

    color: #fff;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    transition: .25s;
}


.search-button:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 7px 16px
        rgba(0,168,89,.22);
}


/* =========================================================
   SECTION HEADER
========================================================= */

.section-title-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 25px;
}


.section-heading {

    display: flex;

    align-items: center;

    gap: 12px;
}


.section-heading-icon {

    width: 42px;
    height: 42px;

    border-radius: 12px;

    background: #e8f7ef;

    color: #00a859;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 17px;
}


.section-title-row h2 {

    margin: 0;

    font-size: 27px;

    font-weight: 800;

    color: #11396b;
}


.found-count {

    background: #e7f8ef;

    color: #008e4c;

    border-radius: 30px;

    padding: 8px 16px;

    font-size: 13px;

    font-weight: 700;
}


/* =========================================================
   FIVE CARDS PER ROW
========================================================= */

.alumni-grid {

    display: grid;

    grid-template-columns:
        repeat(5, minmax(0, 1fr));

    gap: 18px;
}


/* =========================================================
   MEMBER CARD
========================================================= */

.member-card {

    position: relative;

    overflow: hidden;

    background: #fff;

    border-radius: 17px;

    border:
        1px solid
        #e7eded;

    box-shadow:
        0 7px 24px
        rgba(0,0,0,.045);

    transition:
        transform .3s ease,
        box-shadow .3s ease;
}


.member-card::before {

    content: "";

    position: absolute;

    left: 0;
    right: 0;
    top: 0;

    height: 4px;

    background:
        linear-gradient(
            90deg,
            #00a859,
            #1a8dad
        );

    z-index: 3;
}


.member-card:hover {

    transform:
        translateY(-7px);

    box-shadow:
        0 18px 40px
        rgba(0,0,0,.10);
}


/* =========================================================
   PROFILE IMAGE
========================================================= */

.member-image {

    height: 215px;

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #eaf8f2,
            #dcefeb
        );

    display: flex;

    align-items: center;

    justify-content: center;
}


.member-image::after {

    content: "";

    position: absolute;

    left: 0;
    right: 0;

    bottom: 0;

    height: 55px;

    background:
        linear-gradient(
            transparent,
            rgba(0,0,0,.08)
        );
}


.member-profile-image {

    width: 100%;
    height: 100%;

    object-fit: cover;

    object-position: center;

    display: block;

    transition:
        transform .4s ease;
}


.member-card:hover
.member-profile-image {

    transform:
        scale(1.035);
}


.default-profile-icon {

    font-size: 60px;

    color: #00a859;
}


/* =========================================================
   VERIFIED BADGE
========================================================= */

.verified-badge {

    position: absolute;

    right: 10px;
    top: 12px;

    z-index: 5;

    padding:
        5px 9px;

    border-radius: 30px;

    background:
        rgba(255,255,255,.94);

    color: #008f4c;

    font-size: 10px;

    font-weight: 700;

    box-shadow:
        0 4px 10px
        rgba(0,0,0,.10);
}


/* =========================================================
   CARD BODY
========================================================= */

.member-body {

    padding:
        18px
        14px
        18px;

    text-align: left;
}


.member-name {

    margin:
        0 0 12px;

    font-size: 18px;

    font-weight: 800;

    color: #123d70;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =========================================================
   INFORMATION ROW
========================================================= */

.member-info-row {

    display: flex;

    align-items: flex-start;

    gap: 8px;

    margin-bottom: 9px;

    min-height: 32px;
}


.info-icon {

    width: 29px;
    height: 29px;

    flex: 0 0 29px;

    border-radius: 8px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #edf8f3;

    color: #00a859;

    font-size: 11px;
}


.info-content {

    min-width: 0;

    flex: 1;
}


.info-label {

    display: block;

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: .55px;

    color: #98a3ab;

    margin-bottom: 2px;

    font-weight: 700;
}


.info-value {

    display: block;

    color: #3d4b56;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.position-value {

    color: #00a859;
}


/* =========================================================
   BATCH
========================================================= */

.batch-pill {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding:
        6px
        10px;

    border-radius: 20px;

    background: #f3f7fa;

    color: #4c5d6a;

    font-size: 11px;

    font-weight: 700;

    margin-top: 1px;

    margin-bottom: 15px;
}


.batch-pill i {

    color: #00a859;
}


/* =========================================================
   VIEW PROFILE BUTTON
========================================================= */

.view-profile {

    width: 100%;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    text-decoration: none;

    border:
        1px solid
        #00a859;

    color: #00a859;

    background: #fff;

    padding:
        9px 10px;

    border-radius: 9px;

    font-size: 11px;

    font-weight: 700;

    transition: .25s;

    cursor: pointer;
}


.view-profile:hover {

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #00a859,
            #008e4c
        );

    border-color: #00a859;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    background: #fff;

    border:
        1px solid
        #e7eded;

    border-radius: 18px;

    padding:
        75px 25px;

    text-align: center;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.04);
}


.empty-state i {

    width: 75px;
    height: 75px;

    margin:
        0 auto 18px;

    border-radius: 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eaf8f2;

    color: #00a859;

    font-size: 30px;
}


.empty-state h3 {

    margin-bottom: 8px;

    color: #173b68;

    font-weight: 800;
}


.empty-state p {

    color: #7a8793;

    margin-bottom: 20px;
}


/* =========================================================
   PROFILE MODAL
========================================================= */

.profile-modal {

    position: fixed;

    inset: 0;

    z-index: 99999;

    display: none;

    align-items: center;

    justify-content: center;

    padding: 25px;

    background:
        rgba(8,25,40,.72);

    backdrop-filter:
        blur(5px);
}


.profile-modal.show {

    display: flex;

}


.profile-modal-content {

    width: 100%;

    max-width: 850px;

    max-height: 92vh;

    overflow-y: auto;

    background: #fff;

    border-radius: 22px;

    box-shadow:
        0 30px 80px
        rgba(0,0,0,.28);

    animation:
        profileModalIn .25s ease;
}


@keyframes profileModalIn {

    from {

        opacity: 0;

        transform:
            translateY(25px)
            scale(.97);

    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);

    }

}


.profile-modal-header {

    position: relative;

    padding:
        28px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #104e72,
            #087d66,
            #00a859
        );
}


.profile-modal-close {

    position: absolute;

    right: 18px;

    top: 18px;

    width: 38px;
    height: 38px;

    border: none;

    border-radius: 50%;

    background:
        rgba(255,255,255,.15);

    color: #fff;

    font-size: 18px;

    cursor: pointer;
}


.profile-modal-top {

    display: flex;

    align-items: center;

    gap: 20px;

    padding-right: 40px;
}


.modal-profile-image {

    width: 105px;
    height: 105px;

    flex: 0 0 105px;

    border-radius: 18px;

    object-fit: cover;

    border:
        4px solid
        rgba(255,255,255,.7);

    background: #eaf8f2;
}


.modal-default-image {

    width: 105px;
    height: 105px;

    flex: 0 0 105px;

    border-radius: 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(255,255,255,.14);

    color: #fff;

    font-size: 42px;

    border:
        3px solid
        rgba(255,255,255,.35);
}


.modal-profile-name {

    margin: 0 0 6px;

    font-size: 27px;

    font-weight: 800;

    color: #fff;
}


.modal-profile-position {

    margin: 0;

    color:
        rgba(255,255,255,.88);

    font-size: 14px;
}


.profile-modal-body {

    padding: 28px;
}


.profile-section {

    margin-bottom: 25px;
}


.profile-section:last-child {

    margin-bottom: 0;

}


.profile-section-title {

    display: flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 15px;

    padding-bottom: 10px;

    border-bottom:
        1px solid
        #edf0f1;

    color: #123d70;

    font-size: 17px;

    font-weight: 800;
}


.profile-section-title i {

    color: #00a859;
}


.profile-details-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 13px;
}


.profile-detail {

    padding: 13px;

    border-radius: 11px;

    background: #f7faf9;

    border:
        1px solid
        #edf2ef;
}


.profile-detail-label {

    display: block;

    margin-bottom: 4px;

    color: #8b969d;

    font-size: 9px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .6px;
}


.profile-detail-value {

    display: block;

    color: #344650;

    font-size: 13px;

    font-weight: 600;

    word-break: break-word;
}


.social-links {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;
}


.social-link {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 9px 13px;

    border-radius: 9px;

    background: #f5f8f7;

    border:
        1px solid
        #e3eae7;

    color: #345;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    transition: .2s;
}


.social-link:hover {

    color: #00a859;

    border-color: #00a859;

    background: #edfaf3;
}


.no-data {

    color: #9aa4aa;

    font-size: 13px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1200px) {

    .alumni-grid {

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

    }

}


@media (max-width: 1000px) {

    .alumni-grid {

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

    }

}


@media (max-width: 760px) {

    .search-box {

        grid-template-columns:
            1fr;

    }


    .alumni-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }


    .section-title-row {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

    }


    .profile-details-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 540px) {

    .alumni-hero {

        padding:
            60px
            16px
            85px;

    }


    .alumni-hero h1 {

        font-size: 34px;

    }


    .alumni-hero p {

        font-size: 14px;

    }


    .alumni-main {

        padding:
            0 12px;

    }


    .alumni-grid {

        grid-template-columns: 1fr;

    }


    .member-image {

        height: 270px;

    }


    .profile-modal {

        padding: 10px;

    }


    .profile-modal-header {

        padding: 22px 18px;

    }


    .profile-modal-body {

        padding: 20px 18px;

    }


    .profile-modal-top {

        gap: 13px;

    }


    .modal-profile-image,
    .modal-default-image {

        width: 80px;
        height: 80px;

        flex-basis: 80px;

    }


    .modal-profile-name {

        font-size: 21px;

    }

}

</style>

</head>


<body>


<?php include 'includes/header.php'; ?>


<!-- =========================================================
     HERO
========================================================= -->

<section class="alumni-hero">

    <div class="hero-glow"></div>


    <div class="hero-content">


        <div class="hero-icon">

            <i class="fa-solid fa-user-group"></i>

        </div>


        <h1>
            Our Alumni Community
        </h1>


        <p>

            Connecting our proud DIU EEE alumni worldwide,
            building lifelong relationships, sharing experiences,
            and celebrating the achievements of our graduates.

        </p>


        <div class="member-count">

            <i class="fa-solid fa-users"></i>

            <?php
            echo number_format($totalMembers);
            ?>

            Approved Alumni Members

        </div>


    </div>

</section>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="alumni-main">


    <!-- SEARCH -->

    <form
        method="GET"
        action="alumni-members.php"
        class="search-box"
    >


        <div class="input-wrap">

            <i class="fa-solid fa-magnifying-glass"></i>


            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search by name, company, position, programme..."
                value="<?php echo e($search); ?>"
            >

        </div>


        <select
            name="batch"
            class="batch-select"
        >

            <option value="">
                All Batches
            </option>


            <?php foreach ($batches as $batchValue): ?>

                <option
                    value="<?php echo e($batchValue); ?>"
                    <?php
                    echo (
                        $batch == $batchValue
                    )
                    ? 'selected'
                    : '';
                    ?>
                >

                    Batch
                    <?php echo e($batchValue); ?>

                </option>

            <?php endforeach; ?>


        </select>


        <button
            type="submit"
            class="search-button"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

            Search Alumni

        </button>


    </form>


    <!-- SECTION TITLE -->

    <div class="section-title-row">


        <div class="section-heading">


            <div class="section-heading-icon">

                <i class="fa-solid fa-graduation-cap"></i>

            </div>


            <h2>
                Alumni Members
            </h2>


        </div>


        <div class="found-count">

            <?php echo $totalMembers; ?>

            Members Found

        </div>


    </div>


    <!-- ALUMNI GRID -->

    <?php if ($totalMembers > 0): ?>


        <div class="alumni-grid">


            <?php foreach ($members as $member): ?>


                <?php

                /*
                | Profile Image
                */

                $profileImage =
                    getProfileImageUrl(
                        $member['Profile_picture']
                        ?? ''
                    );


                /*
                | Batch
                */

                $memberBatch = '';


                if (
                    !empty(
                        $member['Batch_number']
                    )
                ) {

                    $memberBatch =
                        $member['Batch_number'];

                }

                elseif (
                    !empty(
                        $member['Academic_year']
                    )
                ) {

                    $timestamp =
                        strtotime(
                            $member['Academic_year']
                        );


                    if (
                        $timestamp !== false
                    ) {

                        $memberBatch =
                            date(
                                'Y',
                                $timestamp
                            );

                    }

                }


                /*
                | Company
                */

                $company =
                    trim(
                        (string)(
                            $member['Company_name']
                            ?? ''
                        )
                    );


                /*
                | Position
                */

                $position =
                    trim(
                        (string)(
                            $member['job_title']
                            ?? ''
                        )
                    );

                ?>


                <!-- MEMBER CARD -->

                <article class="member-card">


                    <!-- Verified -->

                    <div class="verified-badge">

                        <i class="fa-solid fa-circle-check"></i>

                        Verified

                    </div>


                    <!-- Image -->

                    <div class="member-image">


                        <?php if ($profileImage !== ''): ?>


                            <img
                                src="<?php echo e($profileImage); ?>"
                                alt="<?php echo e($member['Name']); ?>"
                                class="member-profile-image"
                                loading="lazy"
                                onerror="
                                    this.style.display='none';
                                    this.nextElementSibling.style.display='block';
                                "
                            >


                            <i
                                class="
                                    fa-solid
                                    fa-user
                                    default-profile-icon
                                "
                                style="display:none;"
                            ></i>


                        <?php else: ?>


                            <i
                                class="
                                    fa-solid
                                    fa-user
                                    default-profile-icon
                                "
                            ></i>


                        <?php endif; ?>


                    </div>


                    <!-- Body -->

                    <div class="member-body">


                        <h3
                            class="member-name"
                            title="<?php echo e($member['Name']); ?>"
                        >

                            <?php
                            echo e(
                                $member['Name']
                            );
                            ?>

                        </h3>


                        <!-- Company -->

                        <div class="member-info-row">


                            <div class="info-icon">

                                <i class="fa-solid fa-building"></i>

                            </div>


                            <div class="info-content">

                                <span class="info-label">
                                    Company
                                </span>


                                <span
                                    class="info-value"
                                    title="<?php
                                    echo e(
                                        $company !== ''
                                            ? $company
                                            : 'Not Provided'
                                    );
                                    ?>"
                                >

                                    <?php

                                    echo e(

                                        $company !== ''
                                            ? $company
                                            : 'Not Provided'

                                    );

                                    ?>

                                </span>

                            </div>

                        </div>


                        <!-- Position -->

                        <div class="member-info-row">


                            <div class="info-icon">

                                <i class="fa-solid fa-briefcase"></i>

                            </div>


                            <div class="info-content">

                                <span class="info-label">
                                    Position
                                </span>


                                <span
                                    class="info-value position-value"
                                    title="<?php
                                    echo e(
                                        $position !== ''
                                            ? $position
                                            : 'Not Provided'
                                    );
                                    ?>"
                                >

                                    <?php

                                    echo e(

                                        $position !== ''
                                            ? $position
                                            : 'Not Provided'

                                    );

                                    ?>

                                </span>

                            </div>

                        </div>


                        <!-- Batch -->

                        <div>

                            <span class="batch-pill">

                                <i
                                    class="fa-solid fa-calendar-days"
                                ></i>

                                Batch

                                <?php

                                echo e(

                                    $memberBatch !== ''
                                        ? $memberBatch
                                        : 'Not Provided'

                                );

                                ?>

                            </span>

                        </div>


                        <!-- Profile Button -->

                        <?php if ($isLoggedIn): ?>


                            <button
                                type="button"
                                class="view-profile"
                                onclick="openAlumniProfile(
                                    <?php echo (int)$member['Member_id']; ?>
                                )"
                            >

                                <i class="fa-solid fa-user"></i>

                                View Full Profile

                            </button>


                        <?php else: ?>


                            <a
                                href="login.php"
                                class="view-profile"
                            >

                                <i class="fa-solid fa-lock"></i>

                                Login to View Full Profile

                            </a>


                        <?php endif; ?>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <!-- EMPTY STATE -->

        <div class="empty-state">


            <i
                class="fa-solid fa-users-slash"
            ></i>


            <h3>
                No Alumni Members Found
            </h3>


            <p>

                No approved alumni matched
                your search criteria.

            </p>


            <?php if (
                $search !== '' ||
                $batch !== ''
            ): ?>


                <a
                    href="alumni-members.php"
                    class="view-profile"
                    style="
                        max-width:220px;
                        margin:auto;
                    "
                >

                    View All Alumni

                </a>


            <?php endif; ?>


        </div>


    <?php endif; ?>


</main>


<!-- =========================================================
     FULL PROFILE MODAL
========================================================= -->

<div
    id="profileModal"
    class="profile-modal"
    onclick="closeProfileOutside(event)"
>


    <div
        class="profile-modal-content"
        onclick="event.stopPropagation()"
    >


        <!-- HEADER -->

        <div class="profile-modal-header">


            <button
                type="button"
                class="profile-modal-close"
                onclick="closeAlumniProfile()"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>


            <div class="profile-modal-top">


                <div id="modalImageContainer"></div>


                <div>

                    <h2
                        id="modalName"
                        class="modal-profile-name"
                    >
                        Alumni Name
                    </h2>


                    <p
                        id="modalPosition"
                        class="modal-profile-position"
                    >
                        Alumni
                    </p>

                </div>


            </div>


        </div>


        <!-- BODY -->

        <div class="profile-modal-body">


            <!-- BASIC INFORMATION -->

            <div class="profile-section">


                <div class="profile-section-title">

                    <i class="fa-solid fa-user"></i>

                    Personal Information

                </div>


                <div class="profile-details-grid">


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Full Name
                        </span>

                        <span
                            id="modalFullName"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Alumni ID
                        </span>

                        <span
                            id="modalAlumniId"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Email
                        </span>

                        <span
                            id="modalEmail"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Phone
                        </span>

                        <span
                            id="modalPhone"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                </div>

            </div>


            <!-- ACADEMIC -->

            <div class="profile-section">


                <div class="profile-section-title">

                    <i class="fa-solid fa-graduation-cap"></i>

                    Academic Information

                </div>


                <div class="profile-details-grid">


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Registration Number
                        </span>

                        <span
                            id="modalRegNo"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Programme
                        </span>

                        <span
                            id="modalProgramme"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Academic Year
                        </span>

                        <span
                            id="modalAcademicYear"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Batch
                        </span>

                        <span
                            id="modalBatch"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Member Type
                        </span>

                        <span
                            id="modalMemberType"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                </div>

            </div>


            <!-- PROFESSIONAL -->

            <div class="profile-section">


                <div class="profile-section-title">

                    <i class="fa-solid fa-briefcase"></i>

                    Professional Information

                </div>


                <div class="profile-details-grid">


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Company
                        </span>

                        <span
                            id="modalCompany"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                    <div class="profile-detail">

                        <span class="profile-detail-label">
                            Job Title
                        </span>

                        <span
                            id="modalJobTitle"
                            class="profile-detail-value"
                        >
                            -
                        </span>

                    </div>


                </div>

            </div>


            <!-- ADDRESS -->

            <div class="profile-section">


                <div class="profile-section-title">

                    <i class="fa-solid fa-location-dot"></i>

                    Address

                </div>


                <div class="profile-detail">

                    <span
                        id="modalAddress"
                        class="profile-detail-value"
                    >
                        -
                    </span>

                </div>


            </div>


            <!-- SOCIAL -->

            <div class="profile-section">


                <div class="profile-section-title">

                    <i class="fa-solid fa-share-nodes"></i>

                    Social & Professional Links

                </div>


                <div
                    id="modalSocialLinks"
                    class="social-links"
                ></div>


            </div>


        </div>

    </div>

</div>


<script
    src="assets/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| Alumni Data
|--------------------------------------------------------------------------
*/

const alumniData = <?php

    echo json_encode(
        $members,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

?>;


/*
|--------------------------------------------------------------------------
| Find Alumni
|--------------------------------------------------------------------------
*/

function getAlumniById(memberId)
{

    return alumniData.find(
        function(member) {

            return Number(
                member.Member_id
            ) === Number(memberId);

        }
    );

}


/*
|--------------------------------------------------------------------------
| Safe HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value)
{

    if (
        value === null ||
        value === undefined
    ) {

        return '';

    }


    return String(value)
        .replace(
            /&/g,
            '&amp;'
        )
        .replace(
            /</g,
            '&lt;'
        )
        .replace(
            />/g,
            '&gt;'
        )
        .replace(
            /"/g,
            '&quot;'
        )
        .replace(
            /'/g,
            '&#039;'
        );

}


/*
|--------------------------------------------------------------------------
| Show Value
|--------------------------------------------------------------------------
*/

function displayValue(value)
{

    if (
        value === null ||
        value === undefined ||
        String(value).trim() === ''
    ) {

        return 'Not Provided';

    }


    return escapeHtml(value);

}


/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
*/

function createProfileImage(member)
{

    let image =
        member.Profile_picture || '';


    image = String(image)
        .replace(/\\/g, '/');


    if (
        image &&
        !/^https?:\/\//i.test(image)
    ) {

        image = image.replace(
            /^(\.\/|\.\.\/)+/,
            ''
        );


        if (
            !image.startsWith(
                'uploads/'
            ) &&
            !image.includes('/')
        ) {

            image =
                'uploads/alumni_profiles/' +
                encodeURIComponent(image);

        }

    }


    if (image) {

        return `
            <img
                src="${escapeHtml(image)}"
                class="modal-profile-image"
                alt="${escapeHtml(member.Name)}"
                onerror="
                    this.style.display='none';
                    this.nextElementSibling.style.display='flex';
                "
            >

            <div
                class="modal-default-image"
                style="display:none;"
            >
                <i class="fa-solid fa-user"></i>
            </div>
        `;

    }


    return `
        <div class="modal-default-image">
            <i class="fa-solid fa-user"></i>
        </div>
    `;

}


/*
|--------------------------------------------------------------------------
| Social Link
|--------------------------------------------------------------------------
*/

function createSocialLink(
    url,
    icon,
    label
)
{

    if (
        !url ||
        String(url).trim() === ''
    ) {

        return '';

    }


    let safeLink =
        String(url).trim();


    if (
        !/^https?:\/\//i.test(
            safeLink
        )
    ) {

        safeLink =
            'https://' +
            safeLink;

    }


    return `
        <a
            href="${escapeHtml(safeLink)}"
            target="_blank"
            rel="noopener noreferrer"
            class="social-link"
        >
            <i class="${icon}"></i>
            ${label}
        </a>
    `;

}


/*
|--------------------------------------------------------------------------
| Open Profile
|--------------------------------------------------------------------------
*/

function openAlumniProfile(memberId)
{

    const member =
        getAlumniById(memberId);


    if (!member) {

        alert(
            'Alumni profile not found.'
        );

        return;

    }


    /*
    | Basic
    */

    document.getElementById(
        'modalImageContainer'
    ).innerHTML =
        createProfileImage(member);


    document.getElementById(
        'modalName'
    ).textContent =
        member.Name || 'Alumni';


    document.getElementById(
        'modalPosition'
    ).textContent =
        member.job_title ||
        member.Company_name ||
        'DIU EEE Alumni';


    document.getElementById(
        'modalFullName'
    ).innerHTML =
        displayValue(member.Name);


    document.getElementById(
        'modalAlumniId'
    ).innerHTML =
        displayValue(member.alumni_id);


    document.getElementById(
        'modalEmail'
    ).innerHTML =
        displayValue(member.Email);


    document.getElementById(
        'modalPhone'
    ).innerHTML =
        displayValue(member.Phone);


    /*
    | Academic
    */

    document.getElementById(
        'modalRegNo'
    ).innerHTML =
        displayValue(member.Reg_no);


    document.getElementById(
        'modalProgramme'
    ).innerHTML =
        displayValue(member.Programme);


    document.getElementById(
        'modalAcademicYear'
    ).innerHTML =
        displayValue(member.Academic_year);


    let batch =
        member.Batch_number || '';


    if (
        !batch &&
        member.Academic_year
    ) {

        const date =
            new Date(
                member.Academic_year
            );


        if (
            !isNaN(date.getTime())
        ) {

            batch =
                date.getFullYear();

        }

    }


    document.getElementById(
        'modalBatch'
    ).innerHTML =
        displayValue(batch);


    document.getElementById(
        'modalMemberType'
    ).innerHTML =
        displayValue(member.Member_type);


    /*
    | Professional
    */

    document.getElementById(
        'modalCompany'
    ).innerHTML =
        displayValue(
            member.Company_name
        );


    document.getElementById(
        'modalJobTitle'
    ).innerHTML =
        displayValue(
            member.job_title
        );


    /*
    | Address
    */

    document.getElementById(
        'modalAddress'
    ).innerHTML =
        displayValue(
            member.Address
        );


    /*
    | Social Links
    */

    let socialHtml = '';


    socialHtml +=
        createSocialLink(
            member.LinkedIn,
            'fa-brands fa-linkedin-in',
            'LinkedIn'
        );


    socialHtml +=
        createSocialLink(
            member.Facebook,
            'fa-brands fa-facebook-f',
            'Facebook'
        );


    socialHtml +=
        createSocialLink(
            member.Twitter,
            'fa-brands fa-x-twitter',
            'Twitter / X'
        );


    socialHtml +=
        createSocialLink(
            member.Instagram,
            'fa-brands fa-instagram',
            'Instagram'
        );


    socialHtml +=
        createSocialLink(
            member.ResearchPaper,
            'fa-solid fa-file-lines',
            'Research Paper'
        );


    socialHtml +=
        createSocialLink(
            member.PersonalWebsite,
            'fa-solid fa-globe',
            'Personal Website'
        );


    if (socialHtml === '') {

        socialHtml =
            '<span class="no-data">' +
            'No social or professional links provided.' +
            '</span>';

    }


    document.getElementById(
        'modalSocialLinks'
    ).innerHTML =
        socialHtml;


    /*
    | Show Modal
    */

    const modal =
        document.getElementById(
            'profileModal'
        );


    modal.classList.add(
        'show'
    );


    document.body.style.overflow =
        'hidden';

}


/*
|--------------------------------------------------------------------------
| Close Profile
|--------------------------------------------------------------------------
*/

function closeAlumniProfile()
{

    const modal =
        document.getElementById(
            'profileModal'
        );


    modal.classList.remove(
        'show'
    );


    document.body.style.overflow =
        '';

}


/*
|--------------------------------------------------------------------------
| Close Outside
|--------------------------------------------------------------------------
*/

function closeProfileOutside(event)
{

    if (
        event.target.id ===
        'profileModal'
    ) {

        closeAlumniProfile();

    }

}


/*
|--------------------------------------------------------------------------
| ESC Key
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event) {

        if (
            event.key === 'Escape'
        ) {

            closeAlumniProfile();

        }

    }
);

</script>


</body>

</html>