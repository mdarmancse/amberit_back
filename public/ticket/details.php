<?php
global $conn;
set_time_limit(0);
session_start(); /* Starts the session */
date_default_timezone_set("Asia/Dhaka");

if (!isset($_SESSION['UserData']['username'])) {
    header("location:ticket/login.php");
    exit;
} else {

    include 'bq-conn.php'; //for bigquery connection
    include 'db-conn.php'; //for Database connection

    function cleanString($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
    } else {
        header("location:ticket/login.php");
        exit;
    }

    if (!empty($_POST)) {
        $id = $_POST['id'];
        $comments = trim($_POST['other_comments']);
        $user_id = $_SESSION['user_id'];
        $created_at = $updated_at = date('Y-m-d H:i:s');

        $sqlUpd = "UPDATE ts_payment_complain
        SET other_comments = '$comments', solution_status = 1,updated_by = '$user_id', updated_at = '$updated_at'
        WHERE id = '" . $id . "'";

        $updateResponse = $conn->query($sqlUpd);

        if($updateResponse == 1){
        //insert log
        $sqlRow = "SELECT * FROM ts_payment_complain WHERE id='$id'";
        $result = $conn->query($sqlRow);

        if ($result->num_rows > 0) {
            $dataResult = $result->fetch_assoc();
        }

       $device_type = $dataResult['device_type'];
       $login_by = $dataResult['login_by'];
       $username = $dataResult['username'];
       $purchased_pack = $dataResult['purchased_pack'];
       $purchased_by = $dataResult['purchased_by'];
       $pay_with_msisdn = $dataResult['pay_with_msisdn'];
       $transaction_id = $dataResult['transaction_id'];
       $contact_msisdn = $dataResult['contact_msisdn'];
       $contact_email = $dataResult['contact_email'];
       $problem_id = $dataResult['problem_id'];
       $complain_title = $dataResult['complain_title'];
       $response_to_user = $dataResult['response_to_user'];
       $other_comments = $dataResult['other_comments'];
       $solution_type = $dataResult['solution_type'];
       $created_by = $dataResult['created_by'];
       $updated_by = $dataResult['updated_by'];

        $sqlLog = "INSERT INTO ts_payment_complain_log " .
                "(complain_id,device_type,login_by,username,purchased_pack,purchased_by,pay_with_msisdn,transaction_id,contact_msisdn,contact_email,problem_id,complain_title,response_to_user,other_comments,solution_type,created_by,updated_by,created_at,updated_at,solution_status) " . "VALUES " .
                "('$id','$device_type','$login_by','$username','$purchased_pack','$purchased_by','$pay_with_msisdn','$transaction_id','$contact_msisdn','$contact_email','$problem_id','$complain_title','$response_to_user','$other_comments','$solution_type','$user_id','$user_id','$created_at','$updated_at','1')";
        $logRes = $conn->query($sqlLog);


        $msg = "<span style='color:green; font-weignt:bold'>Ticket has been resolved successfully. </span>";

        }

    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title>View Details</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <link href="./css/style.css" rel="stylesheet">

            <script>
                $(document).ready(function () {
                    $('#example').DataTable({
                        "columnDefs": [
                            {"width": "5%", "targets": 0}
                        ]
                    });
                });
            </script>

            <script>
                $(document).ready(function () {
                    $(".resolved").click(function () {
                        $('.comments').toggle();
                    });
                });
            </script>

            <style>
                .requiredField{
                    color:red;
                }
                .form-horizontal .control-group {
                    margin-bottom: 5px;
                }
                .form-group {
                    margin-bottom: 0.1rem;
                }
            </style>
        </head>
        <body>
            <div class="container" style="margin-top:10px">
                <div class="row">
                    <div class="col-sm-9">
                        <?php if (isset($msg)) { ?>
                        <p class="text-center"><b><?php echo $msg; ?></b></p>
                        <?php } ?>

                    </div>
                    <div class="col-sm-3">
                        <a href="index.php" class="btn btn-warning">Back</a>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-7">
                        <form action="" method="POST">
                            <?php
                            $sql = "SELECT * FROM ts_payment_complain where id={$id}";
                            $result = $conn->query($sql);

                            // if users package found
                            if ($result->num_rows > 0) {
                                $rowArr = $result->fetch_assoc();
                                ?>
                                <fieldset class="form-group">
                                    <div class="row">
                                        <legend class="col-form-label col-sm-3 pt-0">Device Types:</legend>
                                        <div class="col-sm-9">
                                            <?php echo $rowArr['device_type']; ?>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="form-group">
                                    <div class="row">
                                        <legend class="col-form-label col-sm-3 pt-0">Login By:</legend>
                                        <div class="col-sm-9">
                                            <?php echo $rowArr['login_by']; ?>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Email/ Phone/ Facebook:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['username']; ?>
                                    </div>
                                </div>
                                <fieldset class="form-group">
                                    <div class="row">
                                        <legend class="col-form-label col-sm-3 pt-0">Premium Packs:</legend>
                                        <div class="col-sm-9">
                                            <?php echo $rowArr['purchased_pack']; ?>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="form-group">
                                    <div class="row">
                                        <legend class="col-form-label col-sm-3 pt-0">Purchased By:</legend>
                                        <div class="col-sm-9">
                                            <?php echo $rowArr['purchased_by']; ?>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Pay with MSISDN:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['pay_with_msisdn']; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="transaction_id" class="col-sm-3 col-form-label">Transaction ID:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['transaction_id']; ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="contact_msisdn" class="col-sm-3 col-form-label">Contact MSISDN:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['contact_msisdn']; ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="contact_email" class="col-sm-3 col-form-label">Contact Email:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['contact_email']; ?>
                                    </div>
                                </div>



                                <div class="form-group row comments_desc">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Complain Title:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['complain_title']; ?>
                                    </div>
                                </div>
                                <div class="form-group row comments_desc">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Response to user:</label>
                                    <div class="col-sm-9">
                                        <?php echo!empty($rowArr['response_to_user']) ? $rowArr['response_to_user'] : $rowArr['other_comments']; ?>
                                    </div>
                                </div>

                                <fieldset class="form-group">
                                    <div class="row">
                                        <legend class="col-form-label col-sm-3 pt-0">Solution Type:</legend>
                                        <div class="col-sm-9">
                                            <?php echo ($rowArr['solution_type'] == 1) ? "Solution" : "Refund"; ?>
                                        </div>
                                    </div>
                                </fieldset>

                            <?php if($rowArr['solution_status'] == 1){ ?>

                            <div class="form-group row comments_desc">
                                    <label for="inputEmail3" class="col-sm-3 col-form-label">Comments:</label>
                                    <div class="col-sm-9">
                                        <?php echo $rowArr['other_comments']; ?>
                                    </div>
                                </div>
                            <?php } ?>
                                <fieldset class="form-group">
                                    <div class="row">
                                        <legend class="col-form-label col-sm-3 pt-0">Status:</legend>
                                        <div class="col-sm-9">
                                            <?php echo ($rowArr['solution_status'] == 0) ? "Open" : "<p style='color:red'><b>Closed</b></p>"; ?>
                                        </div>
                                    </div>
                                </fieldset>


                            <?php if($rowArr['solution_status'] == 0){ ?>
                                <div class="form-group row">
                                    <label for="resolved" class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-6">
                                        <p class="btn btn-default resolved" style="color:green"><b>Resolve </b></p>
                                    </div>
                                </div>

                                <div class="comments" style="display:none">
                                    <div class="form-group row">
                                        <label for="comments" class="col-sm-2 col-form-label">Comments:</label>
                                        <div class="col-sm-6">
                                            <input type="hidden" class="form-control" name="id" value="<?php echo $id; ?>">
                                            <textarea class="form-control" rows="2" name="other_comments" id="comments" ></textarea>
                                        </div>
                                    </div>
                                    <br>

                                    <div class="form-group row text-center">
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                            <?php } ?>
                                </div>
                            <?php }
                        } ?>
                    </form>

                </div>
            </div>
            <div class="col-sm-2"></div>
        </div>
    </div>
</body>
</html>

