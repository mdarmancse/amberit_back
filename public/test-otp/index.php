<!DOCTYPE html>
<html lang="en">
<head>
    <title>Test OTP</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <style type="text/css">
        .table td, th {
            text-align: center;
        }
    </style>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa; /* Bootstrap background color */
        }

        .custom-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card {
            width: 20rem;
            /*border: 2px solid #17a2b8; !* Bootstrap primary color *!*/
            border: 2px solid rgba(107, 3, 3, 0.77); /* Bootstrap primary color */
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Bootstrap card shadow */
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .text-red {
            font-weight: bold;
            color: #bd0a0a; /* Bootstrap danger color */
        }

        .text-green {
            font-weight: bold;
            color: #127c2a; /* Bootstrap success color */
        }
    </style>
    <script type="text/javascript">
        setInterval(function(){
            location.reload()
        }, 20000);

    </script>
</head>
<body>

<div class="container">
    <?php
    date_default_timezone_set('Asia/Dhaka');
//    $servername = "34.124.207.130";
    $servername = "10.187.96.8";
    $username = "root";
//    $password = "QPqg0&.HT>[eEu[J";
    $password = "M3JkEzYanDxkRY2";
    $dbname = "tsports_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "select id, msisdn, otp, otp_expire_time FROM otp_log where msisdn LIKE '%+8801958160964%' ORDER BY otp_expire_time DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) { ?>

            <div class="custom-container">

                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center">OTP (ios)</h3>
                        <hr/>
                        <h5 class="card-title">OTP:
                            <?php
                            $today = date("Y-m-d G:i:s");
                            $isExpired = strtotime($row["otp_expire_time"]) <= strtotime($today);
                            $status = $isExpired ? '(EXPIRED)' : '';
                            $textColor = $isExpired ? ' text-red' : ' text-green';

                            echo '<span class="' . $textColor . '">' . $row["otp"].' ' . $status . '</span>';
                            ?>
                        </h5>
                        <p class="card-text">MSISDN: <?php echo $row["msisdn"] ?></p>


                        <p class="card-text">EXPIRY TIME: <nobr> <?php echo date('D M, Y h:i A', strtotime($row["otp_expire_time"])); ?></nobr></p>

                    </div>
                </div>
            </div>


            <?php
        }
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>
</div>
</body>
</html>
