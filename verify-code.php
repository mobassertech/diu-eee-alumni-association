<?php
include 'config.php';
session_start();

$error = '';

if (!isset($_SESSION['receipt_number'])) {

    header("Location: join-now.php");
    exit();

}

$receipt_number = $_SESSION['receipt_number'];


if (isset($_POST['verify_code'])) {

    $verification_code = trim($_POST['verification_code']);

    if (empty($verification_code)) {

        $error = "Please enter your verification code.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                id,
                receipt_number,
                verification_code,
                name,
                email,
                phone,
                payment_status,
                verification_status
            FROM alumni_verification
            WHERE receipt_number = ?
            AND verification_code = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "ss",
            $receipt_number,
            $verification_code
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $row = $result->fetch_assoc();


            /*
            |--------------------------------------------------------------------------
            | Save verification information in session
            |--------------------------------------------------------------------------
            */

            $_SESSION['verification_id'] =
                $row['id'];

            $_SESSION['verified_receipt'] =
                $row['receipt_number'];

            $_SESSION['verification_code'] =
                $row['verification_code'];

            $_SESSION['verified_name'] =
                $row['name'];

            $_SESSION['verified_email'] =
                $row['email'];

            $_SESSION['verified_phone'] =
                $row['phone'];

            $_SESSION['registration_verified'] = true;


            /*
            |--------------------------------------------------------------------------
            | Go to Registration Form
            |--------------------------------------------------------------------------
            */

            header("Location: registration-form.php");
            exit();

        } else {

            $error =
                "Invalid verification code. Please check your money receipt.";

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

    <title>Verify Code | DIU Alumni Association</title>

    <link rel="stylesheet"
          href="assets/css/bootstrap.min.css">

    <style>

        body {
            margin: 0;
            min-height: 100vh;
            background: #f4f7f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .verify-card {
            width: 100%;
            max-width: 500px;
            background: #fff;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 10px 40px rgba(0,0,0,.10);
        }

        .logo {
            width: 260px;
            max-width: 100%;
            margin-bottom: 25px;
        }

        h2 {
            color: #123b70;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        .receipt-box {
            background: #eef8f3;
            border: 1px solid #c8e8d7;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .receipt-label {
            font-size: 13px;
            color: #666;
        }

        .receipt-number {
            color: #008c45;
            font-size: 18px;
            font-weight: 700;
        }

        .form-control {
            height: 55px;
            border-radius: 10px;
        }

        .verify-btn {
            width: 100%;
            height: 55px;
            border: 0;
            border-radius: 10px;
            background: #00a651;
            color: #fff;
            font-size: 17px;
            font-weight: 700;
        }

        .verify-btn:hover {
            background: #008c45;
        }

        .error {
            background: #ffe8e8;
            color: #c62828;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #123b70;
            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="verify-card">

    <div class="text-center">

        <img
            src="assets/img/sict_alumi_logo.webp"
            class="logo"
            alt="DIU Alumni Association">

        <h2>
            Verify Registration
        </h2>

        <p class="subtitle">
            Enter the verification code written on your money receipt.
        </p>

    </div>


    <div class="receipt-box">

        <div class="receipt-label">
            Money Receipt Number
        </div>

        <div class="receipt-number">
            <?php
            echo htmlspecialchars($receipt_number);
            ?>
        </div>

    </div>


    <?php if (!empty($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="mb-3">

            <label class="form-label">
                Verification Code
            </label>

            <input
                type="text"
                name="verification_code"
                class="form-control"
                placeholder="Example: DIU-8K4P92"
                required
                autocomplete="off">

        </div>


        <button
            type="submit"
            name="verify_code"
            class="verify-btn">

            Verify & Continue

        </button>

    </form>


    <a href="join-now.php" class="back">
        ← Change Receipt Number
    </a>

</div>

</body>

</html>