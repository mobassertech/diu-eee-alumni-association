<?php
include '../config.php';
session_start();

/* Admin Login Check */
if (!isset($_SESSION['usermail']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

/* Generate Verification Code */
if (isset($_POST['generate_code'])) {

    $receipt_number = trim($_POST['receipt_number']);

    if ($receipt_number == "") {

        $message = "Please enter a Money Receipt Number.";
        $message_type = "danger";

    } else {

        /* Check if receipt already exists */
        $stmt = $conn->prepare(
            "SELECT id, verification_code 
             FROM alumni_verification 
             WHERE receipt_number = ? 
             LIMIT 1"
        );

        $stmt->bind_param("s", $receipt_number);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            $message = "This receipt number already exists. Verification Code: " .
                       htmlspecialchars($row['verification_code']);

            $message_type = "warning";

        } else {

            /* Generate Unique Code */
            do {

                $verification_code =
                    "DIU-" .
                    strtoupper(substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6));

                $check = $conn->prepare(
                    "SELECT id 
                     FROM alumni_verification 
                     WHERE verification_code = ? 
                     LIMIT 1"
                );

                $check->bind_param("s", $verification_code);
                $check->execute();

                $check_result = $check->get_result();

            } while ($check_result->num_rows > 0);

            /* Insert */
            $stmt = $conn->prepare(
                "INSERT INTO alumni_verification
                (receipt_number, verification_code, payment_status, verification_status)
                VALUES (?, ?, 'Paid', 'Pending')"
            );

            $stmt->bind_param(
                "ss",
                $receipt_number,
                $verification_code
            );

            if ($stmt->execute()) {

                $message =
                    "Verification Code Generated Successfully: " .
                    htmlspecialchars($verification_code);

                $message_type = "success";

            } else {

                $message = "Database error: " . $conn->error;
                $message_type = "danger";
            }
        }
    }
}

/* Get recent verification records */
$records = $conn->query(
    "SELECT *
     FROM alumni_verification
     ORDER BY id DESC
     LIMIT 20"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Receipt Verification | Admin</title>

    <link rel="stylesheet"
          href="css/bootstrap.min.css">

    <link rel="stylesheet"
          href="css/admin.css">

    <style>

        body {
            background: #f4f6f9;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #123b70;
        }

        .verification-card {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        .btn-generate {
            background: #00a859;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 6px;
        }

        .btn-generate:hover {
            background: #008f4c;
            color: #fff;
        }

        .code-box {
            background: #eaf8f1;
            border: 2px dashed #00a859;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #008f4c;
            letter-spacing: 2px;
            border-radius: 8px;
        }

        .table-card {
            background: #fff;
            margin-top: 25px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

    </style>

</head>

<body>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="page-title">
                Receipt Verification
            </h2>

            <p class="text-muted mb-0">
                Create a verification code for an alumni money receipt.
            </p>
        </div>

        <a href="dashboard.php"
           class="btn btn-outline-secondary">
            ← Dashboard
        </a>

    </div>


    <!-- Message -->

    <?php if ($message != ""): ?>

        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>

    <?php endif; ?>


    <!-- Generate Code -->

    <div class="verification-card">

        <h5 class="mb-4">
            Generate Alumni Verification Code
        </h5>

        <form method="POST">

            <div class="row align-items-end">

                <div class="col-md-8">

                    <label class="form-label">
                        Money Receipt Number
                    </label>

                    <input
                        type="text"
                        name="receipt_number"
                        class="form-control form-control-lg"
                        placeholder="Enter Money Receipt Number"
                        required
                    >

                </div>


                <div class="col-md-4 mt-3 mt-md-0">

                    <button
                        type="submit"
                        name="generate_code"
                        class="btn btn-generate w-100">

                        Generate Verification Code

                    </button>

                </div>

            </div>

        </form>

    </div>


    <!-- Existing Records -->

    <div class="table-card">

        <h5 class="mb-4">
            Recent Receipt Verification
        </h5>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th>#</th>

                        <th>Receipt Number</th>

                        <th>Verification Code</th>

                        <th>Payment Status</th>

                        <th>Verification Status</th>

                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if ($records && $records->num_rows > 0):

                    $i = 1;

                    while ($row = $records->fetch_assoc()):

                ?>

                    <tr>

                        <td>
                            <?php echo $i++; ?>
                        </td>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $row['receipt_number']
                                );
                                ?>
                            </strong>
                        </td>

                        <td>

                            <span class="badge bg-success fs-6">

                                <?php
                                echo htmlspecialchars(
                                    $row['verification_code']
                                );
                                ?>

                            </span>

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row['payment_status']
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row['verification_status']
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            if (isset($row['verified_at']) &&
                                $row['verified_at'] != NULL) {

                                echo htmlspecialchars(
                                    $row['verified_at']
                                );

                            } elseif (isset($row['created_at'])) {

                                echo htmlspecialchars(
                                    $row['created_at']
                                );

                            } else {

                                echo "-";

                            }
                            ?>

                        </td>

                    </tr>

                <?php

                    endwhile;

                else:

                ?>

                    <tr>

                        <td colspan="6"
                            class="text-center text-muted py-4">

                            No receipt verification records found.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>