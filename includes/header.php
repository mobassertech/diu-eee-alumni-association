<?php
/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
| Header যেহেতু সব page-এ include হয়,
| তাই এখান থেকেই login session check করা হচ্ছে।
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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


/*
|--------------------------------------------------------------------------
| ACTIVE PAGE
|--------------------------------------------------------------------------
*/

$activePage = basename($_SERVER['PHP_SELF'], ".php");

?>


<header id="header" class="header d-flex align-items-center fixed-top">

    <div class="container container-xl position-relative d-flex align-items-center">


        <!-- =========================================================
             LOGO
        ========================================================== -->

        <a href="/" class="logo d-flex align-items-center me-auto">

            <img
                src="/assets/img/sict_alumi_logo.webp"
                alt="Alumni Association"
            >

        </a>


        <!-- =========================================================
             ACTIVE MENU STYLE
        ========================================================== -->

        <style>

            .active {
                color: var(--accent-color) !important;
            }

            .profile-menu-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .profile-menu-btn i {
                font-size: 16px;
            }

            .logout-btn {
                border: 1px solid #dc3545 !important;
                color: #dc3545 !important;
                background: transparent !important;
            }

            .logout-btn:hover {
                background: #dc3545 !important;
                color: #fff !important;
            }

        </style>


        <!-- =========================================================
             NAVIGATION
        ========================================================== -->

        <nav id="navmenu" class="navmenu">

            <ul>


                <!-- =================================================
                     HOME
                ================================================== -->

                <li>

                    <a
                        class="<?= ($activePage == 'index') ? 'active' : ''; ?>"
                        href="/"
                    >
                        Home
                    </a>

                </li>


                <!-- =================================================
                     ABOUT
                ================================================== -->

                <li class="dropdown">

                    <a
                        class="<?= (
                            $activePage == 'about-us' ||
                            $activePage == 'vision-mission' ||
                            $activePage == 'presidents-message' ||
                            $activePage == 'executive-committee' ||
                            $activePage == 'downloads'
                        ) ? 'active' : ''; ?>"
                        href="#"
                    >

                        <span>About</span>

                        <i class="bi bi-chevron-down toggle-dropdown"></i>

                    </a>


                    <ul>

                        <li>

                            <a
                                class="<?= ($activePage == 'about-us') ? 'active' : ''; ?>"
                                href="/about-us.php"
                            >
                                About Us
                            </a>

                        </li>


                        <li>

                            <a
                                class="<?= ($activePage == 'vision-mission') ? 'active' : ''; ?>"
                                href="/vision-mission.php"
                            >
                                Vision & Mission
                            </a>

                        </li>


                        <li>

                            <a
                                class="<?= ($activePage == 'presidents-message') ? 'active' : ''; ?>"
                                href="/presidents-message.php"
                            >
                                President's Message
                            </a>

                        </li>


                        <li>

                            <a
                                class="<?= ($activePage == 'executive-committee') ? 'active' : ''; ?>"
                                href="/executive-committee.php"
                            >
                                Executive Committee
                            </a>

                        </li>


                        <li>

                            <a
                                class="<?= ($activePage == 'downloads') ? 'active' : ''; ?>"
                                href="/downloads.php"
                            >
                                Downloads
                            </a>

                        </li>

                    </ul>

                </li>


                <!-- =================================================
                     JOIN WITH US
                ================================================== -->

                <li>

                    <a
                        class="<?= ($activePage == 'join-with-us') ? 'active' : ''; ?>"
                        href="/join-with-us.php"
                    >
                        Join with Us
                    </a>

                </li>


                <!-- =================================================
                     PROJECTS
                ================================================== -->

                <li>

                    <a
                        class="<?= ($activePage == 'projects') ? 'active' : ''; ?>"
                        href="/projects.php"
                    >
                        Projects
                    </a>

                </li>


                <!-- =================================================
                     ALUMNI MEMBERS
                ================================================== -->

                <li>

                    <a
                        class="<?= ($activePage == 'alumni-members') ? 'active' : ''; ?>"
                        href="/alumni-members.php"
                    >
                        Alumni Members
                    </a>

                </li>


                <!-- =================================================
                     EVENTS
                ================================================== -->

                <li>

                    <a
                        class="<?= ($activePage == 'events') ? 'active' : ''; ?>"
                        href="/events.php"
                    >
                        Events
                    </a>

                </li>


                <!-- =================================================
                     CONTACT
                ================================================== -->

                <li>

                    <a
                        class="<?= ($activePage == 'contact-us') ? 'active' : ''; ?>"
                        href="/contact-us.php"
                    >
                        Contact Us
                    </a>

                </li>


                <!-- =================================================
                     LOGIN / PROFILE
                ================================================== -->

                <?php if ($isLoggedIn): ?>


                    <!-- =============================================
                         LOGGED IN USER
                    ============================================== -->

                    <li class="logbtn">

                        <a href="/alumni/view_alumnus.php">

                            <button
                                type="button"
                                class="btn calltoactionbtn login-btn profile-menu-btn"
                            >

                                <i class="bi bi-person-circle"></i>

                                My Profile

                            </button>

                        </a>

                    </li>


                    <!-- =============================================
                         LOGOUT
                    ============================================== -->

                    <li class="joinbtn">

                        <a href="/logout.php">

                            <button
                                type="button"
                                class="btn btnjoinaction join-btn logout-btn"
                            >

                                <i class="bi bi-box-arrow-right"></i>

                                Logout

                            </button>

                        </a>

                    </li>


                <?php else: ?>


                    <!-- =============================================
                         LOGGED OUT USER
                    ============================================== -->

                    <li class="logbtn">

                        <a href="/login.php">

                            <button
                                type="button"
                                class="btn calltoactionbtn login-btn"
                            >

                                Login

                            </button>

                        </a>

                    </li>


                    <li class="joinbtn">

                        <a href="/join-now.php">

                            <button
                                type="button"
                                class="btn btnjoinaction join-btn"
                            >

                                Join Now

                            </button>

                        </a>

                    </li>


                <?php endif; ?>


            </ul>


            <!-- =====================================================
                 MOBILE MENU
            ====================================================== -->

            <i
                class="mobile-nav-toggle d-xl-none bi bi-list"
            ></i>

        </nav>


    </div>

</header>