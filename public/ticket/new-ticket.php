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

    if (!empty($_POST)) {

        //echo "<pre>";
        //print_r($_POST);
        //die();
        $device_type = trim($_POST['device_type']);
        $login_by = trim($_POST['login_by']);
        $username = trim($_POST['username']);
        $purchased_pack = trim($_POST['purchased_pack']);
        $purchased_by = trim($_POST['purchased_by']);
        $pay_with_msisdn = trim($_POST['pay_with_msisdn']);
        $transaction_id = trim($_POST['transaction_id']);
        $contact_msisdn = trim($_POST['contact_msisdn']);
        $contact_email = trim($_POST['contact_email']);
        $complain_id = trim($_POST['problem_id']);
        $response_to_user = trim($_POST['response_to_user']);
        $other_comments = trim($_POST['other_comments']);
        $solution_type = trim($_POST['solution_type']);
        $user_id = $_SESSION['user_id'];

        if ($complain_id !== 'Others') {
            $sqlComp = "SELECT * FROM ts_payment_complain_list where id={$complain_id} ORDER BY id asc";
            $resultComp = $conn->query($sqlComp);
            if ($resultComp->num_rows > 0) {
                $complain_listArr = $resultComp->fetch_assoc();
                //echo "<pre>";
                //print_r($complain_listArr);
                $complain_title = $complain_listArr['complain_title'];
                $problem_id = $complain_listArr['id'];
            }
        } else {
            $complain_title = 'Others';
            $problem_id = 0;
        }

        $sql = "INSERT INTO ts_payment_complain " .
                "(device_type,login_by,username,purchased_pack,purchased_by,pay_with_msisdn,transaction_id,contact_msisdn,contact_email,problem_id,complain_title,response_to_user,other_comments,solution_type,created_by,updated_by) " . "VALUES " .
                "('$device_type','$login_by','$username','$purchased_pack','$purchased_by','$pay_with_msisdn','$transaction_id','$contact_msisdn','$contact_email','$problem_id','$complain_title','$response_to_user','$other_comments','$solution_type','$user_id','$user_id')";

        if ($conn->query($sql)) {
            $last_id = $conn->insert_id; // last inserted row ID.
            //insert log
            $sqlLog = "INSERT INTO ts_payment_complain_log " .
                    "(complain_id,device_type,login_by,username,purchased_pack,purchased_by,pay_with_msisdn,transaction_id,contact_msisdn,contact_email,problem_id,complain_title,response_to_user,other_comments,solution_type,created_by,updated_by) " . "VALUES " .
                    "('$last_id','$device_type','$login_by','$username','$purchased_pack','$purchased_by','$pay_with_msisdn','$transaction_id','$contact_msisdn','$contact_email','$problem_id','$complain_title','$response_to_user','$other_comments','$solution_type','$user_id','$user_id')";
            $conn->query($sqlLog);

            $msg = "<span style='color:green; font-weignt:bold'>Ticket inserted successfully. </span>";
        }
        if ($conn->errno) {
            $msg = "<span style='color:red'>Something went to wrong! Please try again. </span>";
        }
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

            <script type="text/javascript">
                function fetch_select(val) {
                    //alert(val);
                    $.ajax({
                        type: 'post',
                        url: 'problem-list.php',
                        datatype: 'json',
                        data: {complain_id: val},
                        success: function (response) {
                            //alert(response);
                            if (response !== '') {
                                $('.comments_desc').show();
                                $('.comments_two').hide();
                                $('#comment').html(response);//This will print you result
                            } else {
                                $('.comments_desc').show();
                                $('.comments_two').show();
                                $('#comment').html('').removeAttr('readonly');
                                ;//This will print you result
                            }
                        }
                    });
                }
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
                    <div class="col-sm-9 text-right">
                        <h3 class="text-center">New Ticket</h3>
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
                    <!--<div class="col-sm-1"></div>-->
                    <div class="col-sm-12">
                        <form action="" method="POST">
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Device Types:<span class="requiredField">*</span></legend>
                                    <div class="col-sm-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="device_type" id="device_type1" value="Android" required>
                                            <label class="form-check-label" for="device_type1">
                                                Android
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="device_type" id="device_type2" value="iOS" required>
                                            <label class="form-check-label" for="device_type2">
                                                iOS
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="device_type" id="device_type3" value="Web" required>
                                            <label class="form-check-label" for="device_type3">
                                                Web
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="device_type" id="AndroidTV" value="AndroidTV" required>
                                            <label class="form-check-label" for="AndroidTV">
                                                Android TV
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Login By:<span class="requiredField">*</span></legend>
                                    <div class="col-sm-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="login_by" id="Phone" value="Phone" required>
                                            <label class="form-check-label" for="Phone">
                                                Phone
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="login_by" id="Gmail" value="Gmail" required>
                                            <label class="form-check-label" for="Gmail">
                                                Gmail
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="login_by" id="Facebook" value="Facebook" required>
                                            <label class="form-check-label" for="Facebook">
                                                Facebook
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </fieldset>

                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Email/ Phone/ Facebook:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="username" id="inputEmail3" placeholder="Email/Phone/Facebook">
                                </div>
                            </div>
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Premium Packs: <span class="requiredField">*</span></legend>
                                    <div class="col-sm-10">

                                        <?php
                                        $sql = "SELECT * FROM premium_packages where is_active = 1 ORDER BY id asc";
                                        $result = $conn->query($sql);

                                        // if users package found
                                        if ($result->num_rows > 0) {
                                            $premium_packagesArr = $result->fetch_all(MYSQLI_ASSOC);
                                            foreach ($premium_packagesArr as $row) {
                                                ?>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="purchased_pack" id="pack_name_<?php echo $row['id'] ?>" value="<?php echo $row['package_name'] ?>" required>
                                                    <label class="form-check-label" for="pack_name_<?php echo $row['id'] ?>">
                                                        <?php echo $row['package_name'] ?>
                                                    </label>
                                                </div>

                                            <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Purchased By: <span class="requiredField">*</span></legend>
                                    <div class="col-sm-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="purchased_by" id="bKash" value="bKash" required>
                                            <label class="form-check-label" for="bKash">
                                                bKash
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="purchased_by" id="Nagad" value="Nagad" required>
                                            <label class="form-check-label" for="Nagad">
                                                Nagad
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="purchased_by" id="Bank Card" value="Bank Card" required>
                                            <label class="form-check-label" for="Bank_Card">
                                                Bank Card
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="purchased_by" id="Others" value="Others" required>
                                            <label class="form-check-label" for="Others">
                                                Others
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </fieldset>

                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Pay with MSISDN:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="pay_with_msisdn" id="inputEmail3" placeholder="ex:+8801xxxxxxxxx">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="transaction_id" class="col-sm-2 col-form-label">Transaction ID:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="transaction_id" id="transaction_id" placeholder="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="contact_msisdn" class="col-sm-2 col-form-label">Contact MSISDN:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="contact_msisdn" id="contact_msisdn" placeholder="+8801xxxxxxxxx">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact_email" class="col-sm-2 col-form-label">Contact Email:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="contact_email" id="contact_email" placeholder="">
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">Problem: <span class="requiredField">*</span></label>
                                <div class="col-sm-10">
                                    <div class="form-group">
                                        <select class="form-control" id="sel1" name="problem_id" required onchange="fetch_select(this.value);">
                                            <option value="">Select any..</option>
                                            <?php
                                            $sql = "SELECT * FROM ts_payment_complain_list where is_active = 1 ORDER BY id asc";
                                            $result = $conn->query($sql);
                                            if ($result->num_rows > 0) {
                                                $complain_listArr = $result->fetch_all(MYSQLI_ASSOC);
                                                foreach ($complain_listArr as $row) {
                                                    ?>
                                                    <option value="<?php echo $row['id']; ?>"> <?php echo $row['complain_title']; ?></option>
                                                <?php
                                                }
                                            }
                                            ?>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row comments_desc" style="display:none">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Response to user:</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" rows="2" name="response_to_user" id="comment" readonly=""></textarea>
                                </div>
                            </div>
                            <div class="form-group row comments_two" style="display:none">
                                <label for="other_comments" class="col-sm-2 col-form-label">Request:</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" rows="2" name="other_comments" id="other_comments" ></textarea>
                                </div>
                            </div>

                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Solution Type:<span class="requiredField">*</span></legend>
                                    <div class="col-sm-10">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="solution_type" id="Solution" value="1" required>
                                            <label class="form-check-label" for="Solution">
                                                Solution
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="solution_type" id="Refund" value="2" required>
                                            <label class="form-check-label" for="Refund">
                                                Refund
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group row text-center">
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--<div class="col-sm-1"></div>-->
            </div>
        </div>
    </body>
    </html>

<?php } ?>
