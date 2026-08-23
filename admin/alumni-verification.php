<?php
include '../config.php';
session_start();

/* =========================
   ADMIN LOGIN CHECK
========================= */

if (!isset($_SESSION['usermail']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

/* =========================
   GENERATE VERIFICATION CODE
========================= */

function generateVerificationCode($conn)
{
    do {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $random = '';

        for ($i = 0; $i < 6; $i++) {
            $random .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $code = 'DIU-' . $random;

        $stmt = $conn->prepare(
            "SELECT id FROM alumni_verification WHERE verification_code = ? LIMIT 1"
        );

        $stmt->bind_param("s", $code);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        $stmt->close();

    } while ($exists);

    return $code;
}


/* =========================
   ADD RECEIPT
========================= */

if (isset($_POST['add_receipt'])) {

    $receipt_number = trim($_POST['receipt_number']);

    if ($receipt_number === '') {

        $message = "Please enter Money Receipt Number.";
        $message_type = "danger";

    } else {

        /* Check duplicate receipt */

        $stmt = $conn->prepare(
            "SELECT id FROM alumni_verification
             WHERE receipt_number = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $receipt_number);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "This receipt number already exists.";
            $message_type = "warning";

        } else {

            $verification_code = generateVerificationCode($conn);

            /*
             * Current table structure অনুযায়ী
             * payment_status এবং verification_status
             * ব্যবহার করা হচ্ছে।
             */

            $payment_status = "Paid";
            $verification_status = "Pending";

            $stmt = $conn->prepare(
                "INSERT INTO alumni_verification
                (
                    receipt_number,
                    verification_code,
                    payment_status,
                    verification_status,
                    created_at
                )
                VALUES (?, ?, ?, ?, NOW())"
            );

            $stmt->bind_param(
                "ssss",
                $receipt_number,
                $verification_code,
                $payment_status,
                $verification_status
            );

            if ($stmt->execute()) {

                $message = "Receipt successfully added! Verification Code: " . $verification_code;
                $message_type = "success";

            } else {

                $message = "Database error: " . $conn->error;
                $message_type = "danger";
            }
        }

        $stmt->close();
    }
}


/* =========================
   DELETE RECEIPT
========================= */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare(
        "DELETE FROM alumni_verification WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        header("Location: alumni-verification.php?deleted=1");
        exit();

    } else {

        $message = "Unable to delete record.";
        $message_type = "danger";
    }

    $stmt->close();
}


/* =========================
   DELETE MESSAGE
========================= */

if (isset($_GET['deleted'])) {

    $message = "Verification record deleted successfully.";
    $message_type = "success";
}


/* =========================
   GET ALL RECORDS
========================= */

$sql = "SELECT
            id,
            receipt_number,
            verification_code,
            name,
            email,
            phone,
            payment_status,
            verification_status,
            created_at,
            verified_at
        FROM alumni_verification
        ORDER BY id DESC";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Alumni Verification | Admin</title>

    <link rel="stylesheet"
          href="../assets/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="../assets/css/bootstrap-icons.min.css">

    <style>

        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .page-wrapper {
            padding: 30px;
        }

        .page-title {
            color: #123b73;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #eee;
            padding: 18px 22px;
            font-weight: 700;
            color: #123b73;
        }

        .form-control {
            height: 48px;
            border-radius: 8px;
        }

        .btn-generate {
            background: #00a859;
            color: white;
            border: none;
            height: 48px;
            padding: 0 25px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-generate:hover {
            background: #008f4b;
            color: white;
        }

        .code {
            font-weight: bold;
            color: #0b6b3a;
            background: #e9f8f0;
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-block;
            letter-spacing: 1px;
        }

        .badge-paid {
            background: #dff7e9;
            color: #08753b;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .table th {
            background: #f8f9fa;
            color: #123b73;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .copy-btn {
            border: none;
            background: transparent;
            color: #0d6efd;
            margin-left: 5px;
        }

        .delete-btn {
            color: #dc3545;
            text-decoration: none;
        }

        .delete-btn:hover {
            color: #a71d2a;
        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <!-- PAGE HEADER -->

    <div class="mb-4">

        <h2 class="page-title">
            Alumni Verification
        </h2>

        <p class="page-subtitle">
            Add Money Receipt Number and generate a unique verification code for alumni registration.
        </p>

    </div>


    <!-- MESSAGE -->

    <?php if ($message != ""): ?>

        <div class="alert alert-<?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- ADD RECEIPT -->

    <div class="card mb-4">

        <div class="card-header">

            <i class="bi bi-receipt"></i>
            Add Money Receipt

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row align-items-end">

                    <div class="col-md-8">

                        <label class="form-label fw-bold">
                            Money Receipt Number
                        </label>

                        <input
                            type="text"
                            name="receipt_number"
                            class="form-control"
                            placeholder="Example: DIU-REC-2026-001"
                            required
                        >

                    </div>

                    <div class="col-md-4">

                        <button
                            type="submit"
                            name="add_receipt"
                            class="btn btn-generate w-100"
                        >

                            <i class="bi bi-gear"></i>
                            Generate Verification Code

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- VERIFICATION LIST -->

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <span>
                <i class="bi bi-list-check"></i>
                Verification Records
            </span>

            <span class="badge bg-primary">

                <?php echo $result ? $result->num_rows : 0; ?>

                Records

            </span>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>

                    <tr>

                        <th>#</th>

                        <th>Receipt Number</th>

                        <th>Verification Code</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Payment</th>

                        <th>Status</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                    </thead>


                    <tbody>

                    <?php

                    if ($result && $result->num_rows > 0):

                        $i = 1;

                        while ($row = $result->fetch_assoc()):

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

                                <span class="code">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['verification_code']
                                    );
                                    ?>

                                </span>

                                <button
                                    type="button"
                                    class="copy-btn"
                                    onclick="copyCode('<?php echo htmlspecialchars($row['verification_code']); ?>')"
                                    title="Copy Code"
                                >

                                    <i class="bi bi-copy"></i>

                                </button>

                            </td>


                            <td>

                                <?php

                                echo $row['name']
                                    ? htmlspecialchars($row['name'])
                                    : '<span class="text-muted">Not registered</span>';

                                ?>

                            </td>


                            <td>

                                <?php

                                echo $row['email']
                                    ? htmlspecialchars($row['email'])
                                    : '-';

                                ?>

                            </td>


                            <td>

                                <?php

                                echo $row['phone']
                                    ? htmlspecialchars($row['phone'])
                                    : '-';

                                ?>

                            </td>


                            <td>

                                <?php if ($row['payment_status'] == 'Paid'): ?>

                                    <span class="badge badge-paid">
                                        Paid
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-pending">
                                        <?php
                                        echo htmlspecialchars(
                                            $row['payment_status']
                                        );
                                        ?>
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php if ($row['verification_status'] == 'Verified'): ?>

                                    <span class="badge bg-success">
                                        Verified
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php

                                echo date(
                                    'd M Y',
                                    strtotime($row['created_at'])
                                );

                                ?>

                            </td>


                            <td>

                                <a
                                    href="alumni-verification.php?delete=<?php echo $row['id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this verification record?');"
                                    title="Delete"
                                >

                                    <i class="bi bi-trash"></i>

                                </a>

                            </td>

                        </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-inbox"
                                    style="font-size:40px;color:#aaa;"
                                ></i>

                                <p class="mt-2 mb-0 text-muted">

                                    No verification records found.

                                </p>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script>

function copyCode(code) {

    navigator.clipboard.writeText(code).then(function() {

        alert("Verification Code copied: " + code);

    });

}

</script>


</body>

</html><?php
include '../config.php';
session_start();

/* Admin login check */
if (!isset($_SESSION['usermail']) || ($_SESSION['user_type'] ?? '') !== 'user') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

/* Generate unique verification code */
function generateVerificationCode($conn)
{
    do {
        $code = 'DIU-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));

        $stmt = $conn->prepare(
            "SELECT id FROM alumni_verification WHERE verification_code = ?"
        );
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

    } while ($result->num_rows > 0);

    $stmt->close();

    return $code;
}


/* Add Receipt */
if (isset($_POST['generate_code'])) {

    $receipt_number = trim($_POST['receipt_number']);

    if ($receipt_number == "") {

        $message = "Please enter Money Receipt Number.";
        $message_type = "danger";

    } else {

        /* Check duplicate receipt */
        $stmt = $conn->prepare(
            "SELECT id FROM alumni_verification WHERE receipt_number = ?"
        );

        $stmt->bind_param("s", $receipt_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "This receipt number already exists.";
            $message_type = "warning";

        } else {

            $verification_code = generateVerificationCode($conn);

            $payment_status = "Paid";
            $verification_status = "Pending";

            $stmt = $conn->prepare(
                "INSERT INTO alumni_verification
                (receipt_number, verification_code, payment_status, verification_status)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $receipt_number,
                $verification_code,
                $payment_status,
                $verification_status
            );

            if ($stmt->execute()) {

                $message = "Verification Code Generated Successfully!";
                $message_type = "success";

            } else {

                $message = "Database Error: " . $conn->error;
                $message_type = "danger";
            }
        }

        $stmt->close();
    }
}


/* Delete verification record */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare(
        "DELETE FROM alumni_verification WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: alumni-verification.php");
    exit();
}


/* Get all verification records */
$result = $conn->query(
    "SELECT * FROM alumni_verification ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Alumni Verification</title>

    <link rel="stylesheet"
          href="css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body {
            background: #f4f6f9;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #123b70;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }

        .generate-btn {
            background: #00a957;
            border: none;
            color: white;
            font-weight: 600;
        }

        .generate-btn:hover {
            background: #008f49;
            color: white;
        }

        .code-box {
            background: #eaf8f1;
            color: #008f49;
            font-size: 18px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
            letter-spacing: 1px;
        }

    </style>

</head>

<body>

<div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="page-title">
                <i class="bi bi-shield-check"></i>
                Alumni Verification
            </h2>

            <p class="text-muted mb-0">
                Generate verification code for paid alumni registration.
            </p>
        </div>

        <a href="dashboard.php"
           class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i>
            Dashboard
        </a>

    </div>


    <?php if ($message != ""): ?>

        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">

            <?php echo htmlspecialchars($message); ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- Generate Code -->

    <div class="card mb-4">

        <div class="card-header bg-white p-3">

            <h5 class="mb-0">
                <i class="bi bi-plus-circle"></i>
                Create Alumni Verification
            </h5>

        </div>


        <div class="card-body">

            <form method="POST">

                <div class="row align-items-end">

                    <div class="col-md-8">

                        <label class="form-label fw-bold">
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


                    <div class="col-md-4">

                        <button
                            type="submit"
                            name="generate_code"
                            class="btn generate-btn btn-lg w-100">

                            <i class="bi bi-gear"></i>
                            Generate Verification Code

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Records -->

    <div class="card">

        <div class="card-header bg-white p-3">

            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Verification Records
            </h5>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>Receipt Number</th>

                            <th>Verification Code</th>

                            <th>Payment Status</th>

                            <th>Verification Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    if ($result && $result->num_rows > 0):

                        $i = 1;

                        while ($row = $result->fetch_assoc()):

                    ?>

                        <tr>

                            <td>
                                <?php echo $i++; ?>
                            </td>

                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($row['receipt_number']); ?>
                                </strong>
                            </td>

                            <td>

                                <span class="code-box">

                                    <?php echo htmlspecialchars($row['verification_code']); ?>

                                </span>

                            </td>

                            <td>

                                <span class="badge bg-success">

                                    <?php echo htmlspecialchars($row['payment_status']); ?>

                                </span>

                            </td>

                            <td>

                                <?php if ($row['verification_status'] == 'Verified'): ?>

                                    <span class="badge bg-success">
                                        Verified
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <a
                                    href="?delete=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this verification record?');"
                                >

                                    <i class="bi bi-trash"></i>

                                </a>

                            </td>

                        </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted py-4">

                                No verification records found.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script src="js/bootstrap.bundle.min.js"></script>

</body>

</html>