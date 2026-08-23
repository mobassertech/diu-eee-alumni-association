<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DIUEEE | Alumni Association</title>
  <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <?php include('includes/header.php'); ?>

  <style>
    .active:focus {
    color: #ffffff !important;
  }
  </style>
  
  <section>
    <div class="carousel">
      <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2" class=""></button>
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3" class="active" aria-current="true"></button>
          
        </div>
        <div class="carousel-inner">
         <div class="carousel-item"><img src="assets/img/slider-1.webp"class="d-block carousel"alt="Slider 1"style="width: 100%; height: 500px; object-fit: cover;"></div>
         <div class="carousel-item"><img src="assets/img/slider-2.webp"class="d-block carousel"alt="Slider 2"style="width: 100%; height: 500px; object-fit: cover;"></div>
         <div class="carousel-item active"><img src="assets/img/slider-3.webp"class="d-block carousel"alt="Slider 3"style="width: 100%; height: 500px; object-fit: cover;"></div>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </div>
  </section>

  <section class="py-5 py-xl-8 pb-6 mb-4">
    <div class="container-fluid pt-4">
      <div class="row justify-content-md-center mb-2">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
          <h1 class="display-5 mb-5 mb-xl-9 text-center aluh1 hidden">DIUEEE Alumni Association <br><span class="display-8 mb-2 text-secondary alutext hidden">of Dhaka International University | EEE Department</span></h1>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-md-5">
          <div class="hidden">
            <img src="assets/img/Campas.jpg" width="480px" height="auto" class="img-fluid card p-2 mx-auto" alt="NIIBS">
          </div>
        </div>
        <div class="col-md-7 text-center hidden">
          <p class="para mt-4">The Alumni Association of the Department of Electrical and Electronic Engineering (DIUEEE) at Dhaka International University plays an important role in connecting alumni, 
          students, faculty members, and the department. The association promotes professional networking, knowledge sharing, career development, mentoring, seminars, and community initiatives. 
          Through the active participation of DIUEEE alumni, it aims to support current students, strengthen professional connections, and contribute to the continued development of the department and society.</p>
        </div>
      </div>
    </div>
    </div>
  </section>

  <section>
    <div class="member-area">
      <div class="container">
        <div class="row text-center">
          <div class="col-md-12">
            <div class="section-title-wrapper text-white">
              <div class="text-white hidden">
                <h3 style="color: #ffffff;font-size: 26px; font-weight: 500;">Membership</h3>
                <hr>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-8 col-md-8 col-12" style="margin: auto;">
            <div class="text-center">
              <p class="hidden">DIUEEE Alumni is a platform that connects graduates of the Department of Electrical and Electronic Engineering, Dhaka International University. 
              It provides opportunities for members to build professional networks, enhance their skills, share knowledge, and support each other in career and personal development.
                personal life.</p>
              <a href="join-now.php" class="hidden"><button type="button" class="btn calltoactionbtn mt-2"><i aria-hidden="true" class="far fa-handshake"></i> &nbsp; Apply for Membership</button></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="team" class="team section-bg bg-light" style="padding-top: 70px; padding-bottom: 60px;">
    <div class="container team">

      <div class="container">
        <div class="row justify-content-md-center mb-2">
          <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <h1 class="display-5 mb-5 mb-xl-9 text-center aluh1 hidden">Members List<br><span class="display-8 mb-2 text-secondary alutext hidden">Executive Committee Members</span></h1>
          </div>
        </div>
      </div>

      <div class="row hidden">
        <div class="col-lg-6">
          <div class="member d-flex align-items-start">
            <div class="teampic"><img src="assets/img/people-demo.webp" class="img-fluid" alt="President"></div>
            <div class="member-info">
              <h4>President</h4>
              <span>Prof. Kasun Perera</span>
              <p>Ph.D (Amst) in Econ, M.Phill (Maast.) in Env Ec, M.A. (CMB) in Econ, B.A. (CMB) in Econ</p>
            </div>
          </div>
        </div>

        <div class="col-lg-6 mt-4 mt-lg-0">
          <div class="member d-flex align-items-start">
            <div class="teampic"><img src="assets/img/people-demo.webp" class="img-fluid" alt="General Secretary"></div>
            <div class="member-info">
              <h4>General Secretary</h4>
              <span>Dr. Nuwanthi Alwis</span>
              <p>PhD (MSU, Malaysia), MBA in TTH (Thailand), PgDplan (Belgium), B.A. (Hons) in Econ</p>
            </div>
          </div>
        </div>

        <div class="col-lg-6 mt-4">
          <div class="member d-flex align-items-start">
            <div class="teampic"><img src="assets/img/people-demo.webp" class="img-fluid" alt="Treasurer"></div>
            <div class="member-info">
              <h4>Treasurer</h4>
              <span>Mr. Ruwan Hapuarachchi</span>
              <p>M.A. in Buddhist Studies (NIIBS), B.A. (Special) in Buddhist Studies (BPU), Dip. in English (NIIBS)</p>
            </div>
          </div>
        </div>

        <div class="col-lg-6 mt-4">
          <div class="member d-flex align-items-start">
            <div class="teampic"><img src="assets/img/people-demo.webp" class="img-fluid" alt="Vice-President"></div>
            <div class="member-info">
              <h4>Vice-President</h4>
              <span>Mr. Denuwan Hewage</span>
              <p>B.A. (Hons) in Buddhist Studies (NIIBS), Dip. in Information Technology (NIIBS), Dip. in Pali (BPU)</p>
            </div>
          </div>
        </div>

      </div>
      <div class="text-center mt-5 hidden">
        <a href="executive-committee.php"><button type="button" class="btn calltoactionbtn mt-2" style="width: 200px;">View More</button></a>
      </div>

    </div>
  </section>

  <section class="py-5 py-xl-8 pt-5 mt-3 mb-3">
    <div class="container-fluid">
      <div class="row justify-content-md-center mb-2">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
          <h1 class="display-5 mb-5 mb-xl-9 text-center aluh1 hidden">News & Events<br><span class="display-8 mb-2 text-secondary alutext hidden">Latest Updats of Alumni</span></h1>
        </div>
      </div>
    </div>
    <div class="container mb-2">
      <div class="row">
        <div class="col-lg-4 mb-5 hidden">
          <div class="card h-100 shadow border-0">
            <img class="card-img-top" src="assets/img/blog-1.jpg" alt="Empowering Future Leaders" />
            <div class="card-body p-4">
              <div class="badge bg-primary bg-gradient rounded-pill mb-2">Events</div>
              <a class="text-decoration-none link-dark stretched-link" href="#!">
                <div class="h5 card-title mb-3">Empowering Future Engineers!</div>
</a>
<p class="card-text mb-0">The Department of Electrical and Electronic Engineering at Dhaka International University organized an inspiring Leadership & 
Soft Skills Development Program to enhance the professional skills, leadership abilities, and confidence of EEE students.</p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 mb-5 hidden">
          <div class="card h-100 shadow border-0">
            <img class="card-img-top" src="assets/img/blog-2.jpg" alt="Nagananda Kowul Wasantha Udanaya 2024" />
            <div class="card-body p-4">
              <div class="badge bg-primary bg-gradient rounded-pill mb-2">Events</div>
              <a class="text-decoration-none link-dark stretched-link" href="#!">
                <div class="h5 card-title mb-3">DIUEEE Alumni Gathering 2026</div>
              </a>
              <p class="card-text mb-0">The DIUEEE Alumni Gathering 2026 was organized with great enthusiasm and participation from alumni, 
              students, faculty members, and well-wishers of the Department of Electrical and Electronic Engineering at Dhaka International University.</p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 mb-5 hidden">
          <div class="card h-100 shadow border-0">
            <img class="card-img-top" src="assets/img/blog-3.jpg" alt="Inaugural Meeting of Cyber Crew of SICT" />
            <div class="card-body p-4">
              <div class="badge bg-primary bg-gradient rounded-pill mb-2">News</div>
              <a class="text-decoration-none link-dark stretched-link" href="#!">
                <div class="h5 card-title mb-3">Inaugural Meeting of "DIUEEE Alumni Association"</div>
</a>
<p class="card-text mb-0">The inaugural meeting of the "DIUEEE Alumni Association" was held at Dhaka International University with the participation of alumni, 
students, faculty members, and distinguished guests from the Department of Electrical and Electronic Engineering.</p>
            </div>
          </div>
        </div>

      </div>
      <div class="text-center mb-xl-0 hidden mb-3">
        <a href="events.php"><button type="button" class="btn calltoactionbtn" style="width: 200px;">More Updates</button></a>
      </div>
    </div>
  </section>

  <?php include('includes/footer.php'); ?>

</body>

</html>