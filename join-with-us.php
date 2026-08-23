<?php
// Keep your existing header exactly as it is
include('includes/header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Join With Us | DIU EEE Alumni Association</title>

    <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* =========================================================
           JOIN WITH US PAGE ONLY
           Header.php is NOT modified
        ========================================================= */

        .join-page {
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        /* =========================
           HERO SECTION
        ========================= */

        .join-hero {
            width: 100%;
            min-height: 420px;

            background: linear-gradient(
                110deg,
                #174b70 0%,
                #216d79 38%,
                #3dbd7a 100%
            );

            display: flex;
            align-items: center;

            /* Important: prevents content from going under header */
            padding: 70px 0 75px;

            position: relative;
            z-index: 1;
        }

        .join-hero .container {
            position: relative;
            z-index: 2;
        }

        .join-hero-content {
            max-width: 720px;
        }

        .join-badge {
            display: inline-block;

            padding: 8px 17px;
            margin-bottom: 22px;

            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 30px;

            background: rgba(255, 255, 255, 0.10);

            color: #ffffff;
            font-size: 13px;
            font-weight: 500;

            line-height: 1.2;
        }

        .join-hero h1 {
            margin: 0 0 20px;

            color: #ffffff;

            font-size: clamp(42px, 5vw, 64px);
            line-height: 1.05;
            font-weight: 700;

            letter-spacing: -1.5px;
        }

        .join-hero p {
            max-width: 700px;

            margin: 0 0 28px;

            color: #ffffff;

            font-size: 16px;
            line-height: 1.7;
            font-weight: 400;
        }

        /* =========================
           BUTTONS
        ========================= */

        .join-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .join-btn-primary,
        .join-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 44px;
            padding: 11px 25px;

            border-radius: 30px;

            font-size: 14px;
            font-weight: 600;

            text-decoration: none;

            transition: all 0.25s ease;
        }

        .join-btn-primary {
            background: #ffffff;
            color: #00a85a;

            border: 1px solid #ffffff;
        }

        .join-btn-primary:hover {
            background: #00a85a;
            color: #ffffff;
            border-color: #00a85a;
        }

        .join-btn-outline {
            background: transparent;
            color: #ffffff;

            border: 1px solid rgba(255, 255, 255, 0.75);
        }

        .join-btn-outline:hover {
            background: #ffffff;
            color: #12618a;
        }

        /* =========================
           WHY JOIN SECTION
        ========================= */

        .why-join {
            background: #f7fbfa;

            padding: 75px 0 80px;
        }

        .why-join-heading {
            text-align: center;

            max-width: 760px;
            margin: 0 auto 45px;
        }

        .why-join-heading h2 {
            margin: 0 0 12px;

            color: #07518b;

            font-size: 32px;
            line-height: 1.2;
            font-weight: 700;
        }

        .why-join-heading p {
            margin: 0;

            color: #65758b;

            font-size: 15px;
            line-height: 1.8;
        }

        /* =========================
           CARDS
        ========================= */

        .join-card {
            height: 100%;

            padding: 32px 28px;

            background: #ffffff;

            border: 0;
            border-radius: 14px;

            box-shadow: 0 8px 30px rgba(20, 60, 80, 0.08);

            text-align: left;

            transition: transform 0.25s ease,
                        box-shadow 0.25s ease;
        }

        .join-card:hover {
            transform: translateY(-5px);

            box-shadow: 0 14px 35px rgba(20, 60, 80, 0.13);
        }

        .join-card-icon {
            width: 52px;
            height: 52px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 20px;

            background: #e6f8ef;

            border-radius: 12px;

            color: #00a85a;

            font-size: 23px;
        }

        .join-card h3 {
            margin: 0 0 12px;

            color: #123f68;

            font-size: 20px;
            font-weight: 600;
        }

        .join-card p {
            margin: 0;

            color: #68788b;

            font-size: 14px;
            line-height: 1.75;
        }

        /* =========================
           CTA SECTION
        ========================= */

        .join-cta {
            padding: 70px 20px;

            background: #ffffff;

            text-align: center;
        }

        .join-cta-box {
            max-width: 850px;

            margin: 0 auto;

            padding: 50px 30px;

            background: linear-gradient(
                110deg,
                #174b70 0%,
                #247c7c 50%,
                #39b978 100%
            );

            border-radius: 18px;
        }

        .join-cta h2 {
            margin: 0 0 14px;

            color: #ffffff;

            font-size: 30px;
            font-weight: 700;
        }

        .join-cta p {
            max-width: 650px;

            margin: 0 auto 25px;

            color: rgba(255, 255, 255, 0.92);

            font-size: 15px;
            line-height: 1.7;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 991px) {

            .join-hero {
                min-height: auto;
                padding: 60px 0 65px;
            }

            .join-hero h1 {
                font-size: 48px;
            }

            .why-join {
                padding: 60px 0;
            }
        }

        @media (max-width: 767px) {

            .join-hero {
                padding: 50px 20px 60px;
            }

            .join-hero h1 {
                font-size: 40px;
                letter-spacing: -0.5px;
            }

            .join-hero p {
                font-size: 15px;
            }

            .join-badge {
                margin-bottom: 18px;
            }

            .join-buttons {
                width: 100%;
            }

            .join-btn-primary,
            .join-btn-outline {
                width: 100%;
            }

            .why-join {
                padding: 55px 20px;
            }

            .why-join-heading h2 {
                font-size: 28px;
            }

            .join-card {
                padding: 28px 24px;
            }

            .join-cta {
                padding: 50px 15px;
            }

            .join-cta-box {
                padding: 40px 22px;
            }

            .join-cta h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

<main class="join-page">

    <!-- =========================================================
         HERO
    ========================================================== -->

    <section class="join-hero">

        <div class="container">

            <div class="join-hero-content">

                <span class="join-badge">
                    DIU EEE Alumni Association
                </span>

                <h1>
                    Stay Connected.<br>
                    Grow Together.
                </h1>

                <p>
                    Become a part of the DIU EEE Alumni community and stay connected
                    with your classmates, department, seniors and juniors through
                    professional networking, events and alumni activities.
                </p>

                <div class="join-buttons">

                    <a href="#join-form" class="join-btn-primary">
                        Join the Alumni Association
                    </a>

                    <a href="alumni-members.php" class="join-btn-outline">
                        Explore Alumni
                    </a>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         WHY JOIN
    ========================================================== -->

    <section class="why-join">

        <div class="container">

            <div class="why-join-heading">

                <h2>
                    Why Join With Us?
                </h2>

                <p>
                    Your university journey does not end after graduation.
                    Stay connected and continue building meaningful relationships
                    with the DIU EEE alumni community.
                </p>

            </div>


            <div class="row g-4">

                <!-- Card 1 -->

                <div class="col-lg-4 col-md-6">

                    <div class="join-card">

                        <div class="join-card-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <h3>
                            Stay Connected
                        </h3>

                        <p>
                            Connect with classmates, seniors, juniors and
                            fellow alumni from the DIU EEE community.
                        </p>

                    </div>

                </div>


                <!-- Card 2 -->

                <div class="col-lg-4 col-md-6">

                    <div class="join-card">

                        <div class="join-card-icon">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>

                        <h3>
                            Professional Networking
                        </h3>

                        <p>
                            Build valuable professional relationships and
                            discover opportunities through the alumni network.
                        </p>

                    </div>

                </div>


                <!-- Card 3 -->

                <div class="col-lg-4 col-md-6">

                    <div class="join-card">

                        <div class="join-card-icon">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>

                        <h3>
                            Events & Activities
                        </h3>

                        <p>
                            Take part in alumni events, reunions, seminars,
                            workshops and community activities.
                        </p>

                    </div>

                </div>


                <!-- Card 4 -->

                <div class="col-lg-4 col-md-6">

                    <div class="join-card">

                        <div class="join-card-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>

                        <h3>
                            Support Students
                        </h3>

                        <p>
                            Share your experience, knowledge and career guidance
                            with current students and young graduates.
                        </p>

                    </div>

                </div>


                <!-- Card 5 -->

                <div class="col-lg-4 col-md-6">

                    <div class="join-card">

                        <div class="join-card-icon">
                            <i class="bi bi-lightbulb-fill"></i>
                        </div>

                        <h3>
                            Share Knowledge
                        </h3>

                        <p>
                            Exchange ideas, experiences and industry knowledge
                            with members of the alumni community.
                        </p>

                    </div>

                </div>


                <!-- Card 6 -->

                <div class="col-lg-4 col-md-6">

                    <div class="join-card">

                        <div class="join-card-icon">
                            <i class="bi bi-heart-fill"></i>
                        </div>

                        <h3>
                            Give Back
                        </h3>

                        <p>
                            Contribute to the growth of the department and help
                            create a stronger DIU EEE alumni community.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         CTA
    ========================================================== -->

    <section class="join-cta" id="join-form">

        <div class="join-cta-box">

            <h2>
                Be Part of Our Alumni Community
            </h2>

            <p>
                Stay connected with DIU EEE, reconnect with your university
                community and grow together with fellow alumni.
            </p>

            <a href="join.php" class="join-btn-primary">
                Join Now
            </a>

        </div>

    </section>

</main>


<?php
include('includes/footer.php');
?>

</body>

</html>