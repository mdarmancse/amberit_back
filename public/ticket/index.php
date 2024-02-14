<?php
global $conn;
set_time_limit(0);
session_start(); /* Starts the session */
date_default_timezone_set("Asia/Dhaka");

if (!isset($_SESSION['UserData']['username'])) {
    header("location:login.php");
    exit;
} else {

    include 'bq-conn.php'; //for bigquery connection
    include 'db-conn.php'; //for Database connection

    function cleanString($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title>All Ticket</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
            <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
            <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
            <!--https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css-->

            <script>
                $(document).ready(function () {
                    $('#example').DataTable({
                        "columnDefs": [
                            {"width": "5%", "targets": 0}
                        ],
                        "aaSorting": [[ 0, "desc" ]]
                    });
                });
            </script>

            <style>

                body {
                    margin: 0;
                    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
                    font-size: 0.95rem;
                    font-weight: 400;
                    line-height: 0.90;
                    color: #212529;
                    text-align: left;
                    background-color: #fff;
                }

                table tr td{
                    padding:0px !important;
                    border-top:1px solid #FFFFFF;
                }
            </style>
        </head>
        <body>
            <!--<div class="container" style="margin-top:20px">-->

                <div class="row">
                    <div class="col-sm-9  text-right">
                        <h3 class="text-center">All Complain</h3>

                    </div>
                    <div class="col-sm-3">
                        <a href="../payments.php" class="btn btn-success">Payment</a>
                        <a href="new-ticket.php" class="btn btn-primary">New Ticket</a>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-10">
                        <table id="example" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th>Device Type</th>
                                    <th>Username</th>
                                    <th>Pack Name</th>
                                    <th>Purchased By</th>
                                    <th>Date&Time</th>
                                    <th>Solution type</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                    <th>Action By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM ts_payment_complain ORDER BY id desc";
                                $result = $conn->query($sql);

                                // if users package found
                                if ($result->num_rows > 0) {
                                    $usersArr = $result->fetch_all(MYSQLI_ASSOC);

                                    //echo "<pre>";
                                    //print_r($usersArr);
                                    //die();
                                    foreach ($usersArr as $row) {

                                        //for device type
                                        $deviceType = $row['device_type'];
                                        /*
                                         *
                                          switch ($deviceType) {
                                          case "1":
                                          $deviceType = "Android";
                                          break;
                                          case "2":
                                          $deviceType = "iOS";
                                          break;
                                          case "3":
                                          $deviceType = "Web";
                                          case "4":
                                          $deviceType = "STB";
                                          break;
                                          default:
                                          $deviceType = "";
                                          }
                                         */

                                        // for solution type
                                        $solution_type = $row['solution_type'];
                                        switch ($solution_type) {
                                            case "1":
                                                $solution_type = "Solution";
                                                break;
                                            case "2":
                                                $solution_type = "Refund";
                                                break;
                                            default:
                                                $solution_type = "";
                                        }

                                        // for solution status
                                        $solution_status = $row['solution_status'];
                                        switch ($solution_status) {
                                            case "1":
                                                $solution_status = "<p style='color:red; font-weight:bold'>Closed</p>";
                                                break;
                                            default:
                                                $solution_status = "<p style='color:black; font-weight:bold'>Open</p>";
                                        }
                                        ?>
                                        <tr>
                                            <td width="5%"><?php echo $row['id']; ?></td>
                                            <td><?php echo $deviceType; ?></td>
                                            <td><?php echo $row['username']; ?></td>
                                            <td><?php echo $row['purchased_pack']; ?></td>
                                            <td><?php echo $row['purchased_by']; ?></td>
                                            <!--<td><?php
                                            /*
                                              $problem_id = $row['problem_id'];
                                              $sql_prob = "SELECT * FROM ts_payment_complain_list where id={$problem_id}";
                                              $result_prob = $conn->query($sql_prob);
                                              if ($result_prob->num_rows > 0) {
                                              $result_probArr = $result_prob->fetch_assoc();
                                              echo $result_probArr['complain_title'];
                                              } */
                                            ?>
                                            </td>-->
                                            <td><?php echo $row['created_at']; ?></td>
                                            <td><?php echo $solution_type; ?></td>
                                            <td><?php echo $solution_status; ?></td>
                                            <td><a class="btn btn-success" href="details.php?id=<?php echo $row['id']; ?>">View</a></td>
                                            <td><?php
                                                if ($row['solution_status'] == 1) {
                                                                if ($row['updated_by'] == 1) {
                                                                    $actionBy = 'Admin';
                                                                } elseif ($row['updated_by'] == 2) {
                                                                    $actionBy = 'support_nex';
                                                                } elseif ($row['updated_by'] == 3) {
                                                                    $actionBy = 'tsports';
                                                                } elseif ($row['updated_by'] == 4) {
                                                                    $actionBy = 'support_ts';
                                                                } elseif ($row['updated_by'] == 5) {
                                                                    $actionBy = 'agent1_12';
                                                                } elseif ($row['updated_by'] == 6) {
                                                                    $actionBy = 'agent2_7';
                                                                } else {
                                                                    $actionBy = '';
                                                                }
                                                                echo $actionBy."<br>".$row['updated_at'];
                                                            }
                                                            ?>
                                            </td>
                                        </tr>
                                    <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-1"></div>
                </div>
            </div>
        <!--</div>-->
    </body>
    </html>

<?php } ?>
