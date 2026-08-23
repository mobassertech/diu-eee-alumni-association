<?php
include 'config.php';
session_start();

$error = "";

if (isset($_POST['verify_submit'])) {

    $receipt_number = trim($_POST['receipt_number']);
    $verification_code = trim($_POST['verification_code']);

    if ($receipt_number === "" || $verification_code === "") {

        $error = "Please enter both Receipt Number and Verification Code.";

    } else {

        $stmt = $conn->prepare("
            SELECT *
            FROM alumni_verification
            WHERE receipt_number = ?
            AND verification_code = ?
            LIMIT 1
        ");

        $stmt->bind_param("ss", $receipt_number, $verification_code);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $verification = $result->fetch_assoc();

            // Already verified check
            if (
                isset($verification['verification_status']) &&
                $verification['verification_status'] === 'Verified'
            ) {

                $error = "This Receipt Number and Verification Code have already been used.";

            } else {

                // Store verified information in session
                $_SESSION['verification_id'] = $verification['id'];
                $_SESSION['receipt_number'] = $verification['receipt_number'];
                $_SESSION['verification_code'] = $verification['verification_code'];

                // Go to registration form
                header("Location: registration-form.php");
                exit();
            }

        } else {

            $error = "Receipt Number and Verification Code do not match.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Join DIUEE Alumni Association</title>

    <link rel="icon"
          href="assets/img/favicon.png"
          type="image/x-icon">

    <link rel="stylesheet"
          href="assets/css/bootstrap.min.css">

    <style>

        body {
            margin: 0;
            min-height: 100vh;
            background: #f4f8f7;
            font-family: Arial, sans-serif;
        }

        .join-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .join-card {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
        }

        .logo {
            width: 230px;
            max-width: 100%;
            display: block;
            margin: 0 auto 20px;
        }

        h1 {
            text-align: center;
            color: #123f78;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .error-box {
            background: #ffe5e5;
            color: #d60000;
            padding: 14px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .info-box {
            background: #eaf8f2;
            border-left: 4px solid #00a651;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #333;
        }

        .info-box strong {
            color: #008f45;
        }

        label {
            font-weight: 600;
            color: #173f73;
            margin-bottom: 8px;
        }

        .form-control {
            height: 52px;
            border-radius: 9px;
            border: 1px solid #d5dce5;
            padding: 12px 15px;
        }

        .form-control:focus {
            border-color: #00a651;
            box-shadow: 0 0 0 3px rgba(0,166,81,.12);
        }

        .btn-continue {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 9px;
            background: #00a651;
            color: white;
            font-size: 16px;
            font-weight: 700;
            margin-top: 10px;
        }

        .btn-continue:hover {
            background: #008f45;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #123f78;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="join-wrapper">

    <div class="join-card">

        <img
            src="assets/img/sict_alumi_logo.webp"
            class="logo"
            alt="DIU Alumni Association"
        >

        <h1>Join DIUEEE Alumni Association</h1>

        <p class="subtitle">
            Verify your Money Receipt and Verification Code
            to start your alumni registration.
        </p>

        <?php if ($error != ""): ?>

            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <div class="info-box">

            <strong>Before you continue:</strong>

            <br>

            Please enter the
            <strong>Money Receipt Number</strong>
            and the
            <strong>Verification Code</strong>
            provided by the DIU Alumni Association.

        </div>

        <form method="POST" action="">

            <div class="mb-3">

                <label>
                    Money Receipt Number
                </label>

                <input
                    type="text"
                    name="receipt_number"
                    class="form-control"
                    placeholder="Enter receipt number"
                    required
                >

            </div>

            <div class="mb-4">

                <label>
                    Verification Code
                </label>

                <input
                    type="text"
                    name="verification_code"
                    class="form-control"
                    placeholder="Example: DIU-8K4P92"
                    required
                >

            </div>

            <button
                type="submit"
                name="verify_submit"
                class="btn-continue"
            >
                Verify & Continue
            </button>

        </form>

        <a href="index.php" class="back-link">
            ← Back to Home
        </a>

    </div>

</div>

</body>

</html>