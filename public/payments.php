<?php
global $conn;
set_time_limit(0);
session_start(); /* Starts the session */
date_default_timezone_set("Asia/Dhaka");
include 'config-staging.php';

$usersArr = [];
$userInfo = [];
$profileArr = [];
$packageArr = [];
$subscriptionsArr = [];
$subscriptions_historyArr = [];
$transactionsArr = [];
$sslCommerzArr = [];
$userRadisData = "";

function cleanString($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

if (!empty($_POST)) {
    $mobile = $_POST['mobile'];

    // Query from MySQL
    $subscriptionsSql = "SELECT * FROM subscriptions WHERE user_name='$mobile' order by id desc";
    $subscriptions = $conn->query($subscriptionsSql);
    if ($subscriptions->num_rows > 0) {
        $subscriptionsArr = $subscriptions->fetch_all(MYSQLI_ASSOC);
    }


    $subscriptions_historySql = "SELECT * FROM subscriptions_history WHERE user_name='$mobile' order by id desc";
    $subscriptions_history = $conn->query($subscriptions_historySql);
    if ($subscriptions_history->num_rows > 0) {
        $subscriptions_historyArr = $subscriptions_history->fetch_all(MYSQLI_ASSOC);
    }

    $transactionsSql = "SELECT * FROM transactions WHERE username='$mobile' order by id desc";
    $transactionsRes = $conn->query($transactionsSql);
    if ($transactionsRes->num_rows > 0) {
        $transactionsArr = $transactionsRes->fetch_all(MYSQLI_ASSOC);
    }

//    echo "<pre>";
//    print_r($transactionsArr);
//    die();

    $sslCommerzSql = "SELECT * FROM ssl_commerz_transactions_via_ipn WHERE username='$mobile' order by id desc";
    $sslCommerzRes = $conn->query($sslCommerzSql);
    if ($sslCommerzRes->num_rows > 0) {
        $sslCommerzArr = $sslCommerzRes->fetch_all(MYSQLI_ASSOC);
    }

    //Query from GCP Bigquery
    $transactionsQuery = "SELECT * FROM `t-sports-361206.events.transactions` WHERE username='$mobile' order by reporting_time desc";
    $jobConfig = $bigQuery->query($transactionsQuery);
    $ResultsTransactions = $bigQuery->runQuery($jobConfig);

    foreach ($ResultsTransactions as $row) {
        if (!empty($row['tran_date'])) {
            $tran_date = date_format($row['tran_date'], 'Y-m-d H:i:s');
        } else {
            $tran_date = date_format($row['reporting_time'], 'Y-m-d H:i:s');
        }
        $transactionArr[] = [
            'subscriber_id'                 => $row['subscriber_id'],
            'username'                      => $row['username'],
            'reg_type'                      => $row['reg_type'],
            'device_type'                   => $row['device_type'],
            'package_id'                    => $row['package_id'],
            'package_short_code'            => $row['package_short_code'],
            'package_amount'                => $row['package_amount']->get(),
            'discount_amount'               => $row['discount_amount']->get(),
            'package_amount_after_discount' => $row['package_amount_after_discount']->get(),
            'coupon_code'                   => $row['coupon_code'],
            'tran_gw'                       => $row['tran_gw'],
            'validation_id'                 => $row['validation_id'],
            'transaction_id'                => $row['transaction_id'],
            'tran_date'                     => $tran_date,
            'amount'                        => !empty($row['amount']) ? $row['amount']->get() : '',
            'store_amount'                  => !empty($row['store_amount']) ? $row['store_amount']->get() : '',
            'currency'                      => $row['currency'],
            'card_no'                       => $row['card_no'],
            'card_type'                     => $row['card_type'],
            'card_brand'                    => $row['card_brand'],
            'card_issuer'                   => $row['card_issuer'],
            'card_issuer_country'           => $row['card_issuer_country'],
            'currency_type'                 => $row['currency_type'],
            'currency_amount'               => $row['currency_amount']->get(),
            'tran_status'                   => $row['tran_status'],
            'risk_level'                    => $row['risk_level'],
            'notes'                         => $row['notes'],
            //'reporting_time' =>date_format($row['reporting_time'], 'Y-m-d H:i:s')
            'reporting_time'                => date_format($row['reporting_time'], 'Y-m-d H:i:s')
        ];
        //echo "<pre>";
        //print_r($transactionArr);
        //die();
    }



    $subscriptions_historyQuery = "SELECT * FROM `t-sports-361206.events.subscriptions_history` WHERE user_name='$mobile' order by reporting_time desc";
    $jobConfig = $bigQuery->query($subscriptions_historyQuery);
    $subscriptions_historyQuery = $bigQuery->runQuery($jobConfig);
    foreach ($subscriptions_historyQuery as $row) {
        $subsArr[] = [
            'subscription_id'    => $row['subscription_id'],
            'tran_id'            => $row['tran_id'],
            'subscriber_id'      => $row['subscriber_id'],
            'user_name'          => $row['user_name'],
            'reg_type'           => $row['reg_type'],
            'package_id'         => $row['package_id'],
            'package_name'       => $row['package_name'],
            'package_short_code' => $row['package_short_code'],
            'package_type'       => $row['package_type'],
            'package_price'      => $row['package_price']->get(),
            'package_details'    => $row['package_details'],
            'discount'           => $row['discount']->get(),
            'promo_code'         => $row['promo_code'],
            'payable_price'      => $row['payable_price']->get(),
            'payment_method'     => $row['payment_method'],
            'start_date'         => date_format($row['start_date'], 'Y-m-d H:i:s'),
            'end_date'           => date_format($row['end_date'], 'Y-m-d H:i:s'),
            'is_active'          => ($row['is_active'] == 1) ? "Active" : "Deactive",
            'comments'           => $row['comments'],
            'reporting_time'     => date_format($row['reporting_time'], 'Y-m-d H:i:s')
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>OTT - T Sports</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <style>
            table tr td{
                padding-top:0px !important;
                padding-bottom:0px !important;
                min-width:200px!important;
                max-width:400px!important;
                /*white-space:nowrap;*/
            }
            h3
            {
                font-size:18px;
                font-weight: bold;
            }
            .rowWidth{
                /*
                    overflow-x: hidden;
                    overflow-y: hidden;
                    white-space: nowrap;
                    max-width: 350px;
                */
                /*word-break: break-all;*/
                word-break: break-word;
                max-width:350px!important;
                min-width:200px!important;
            }
            .blink {
                animation: blink 1s steps(1, end) infinite;
            }

            @keyframes blink {
                0% {
                    opacity: 1;
                }
                50% {
                    opacity: 0;
                }
                100% {
                    opacity: 1;
                }
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">

            <h3 class="text-center" style="margin-top: 20px">Transaction history by MSISDN or Email or Facebook ID</h3>
            <form action="" method="POST">
                <div class="row">
                    <div class="col-sm-4" style="background-color:#ffe680;">
                        <a href="ticket" class="btn btn-danger">Ticket</a>
                    </div>
                    <div class="col-sm-4" style="background-color:#ffe680;">
                        <div class="form-group">
                            <label for="pwd"></label>
                            <input type="hidden" name="form_1" value="form_1">
                            <input type="text" class="form-control" placeholder="Type MSISDN or Email or Facebook ID" id="pwd" name="mobile" value="<?php
                            if (!empty($_POST['mobile'])) {
                                echo $_POST['mobile'];
                            }
                            ?>" required="">
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </div>
                    <div class="col-sm-4" style="background-color:#ffe680;"></div>
                </div>
            </form>
        </div>


        <?php
        if (!empty($subscriptionsArr)) {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">0. Redis Data (redis->hGetAll('RSUBSCRIPTIONS:<?php echo $mobile; ?>');)</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <tbody>
                                <tr>
                                    <?php
                                    $allData = $redis->hGetAll('RSUBSCRIPTIONS:' . $mobile);
                                    echo "<pre>";
                                    print_r($allData);
                                    ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <br>

            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">1. Subscription Status (Source: MySQL, TableName: subscriptions)</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Start Date</th>
                                    <th style="width: 5%">End Date</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 10%">Reg Type</th>
                                    <th style="width: 10%">package name</th>
                                    <th style="width: 5% !important">package short code</th>
                                    <th style="width: 15%">package type</th>
                                    <th style="width: 10%">package price</th>
                                    <th style="width: 10%">Payable price</th>
                                    <!--<th style="width: 15%">package details</th>-->
                                    <th style="width: 15%">discount</th>
                                    <th style="width: 15%">Payment method</th>
                                    <th style="width: 15%">Promo Code</th>
                                    <th style="width: 15%">Tran id</th>
                                    <th style="width: 15%">Subscriber id</th>
                                    <th style="width: 15%">Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($subscriptionsArr as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td><?php echo $row['end_date']; ?></td>
                                        <td><?php echo $row['is_active']; ?></td>
                                        <td><?php echo $row['reg_type']; ?></td>
                                        <td><?php echo $row['package_name']; ?></td>
                                        <td><?php echo $row['package_short_code']; ?></td>
                                        <td><?php echo $row['package_type']; ?></td>
                                        <td><?php echo $row['package_price']; ?></td>
                                        <td><?php echo $row['payable_price']; ?></td>
                                        <!--<td><?php // echo $row['package_details'];    ?></td>-->
                                        <td><?php echo $row['discount']; ?></td>
                                        <td><?php echo $row['payment_method']; ?></td>
                                        <td><?php echo $row['promo_code']; ?></td>
                                        <td><?php echo $row['tran_id']; ?></td>
                                        <td><?php echo $row['subscriber_id']; ?></td>
                                        <td><?php echo $row['comment']; ?></td>

                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">1. Subscription</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <p class="text-center" style="color:red; font-weight: bold; text-align: center">No record found!</p>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>

        <?php
        if (!empty($subscriptions_historyArr)) {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">2. Subscription History (Source: MySQL, TableName: subscriptions_history )</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Tran Date</th>
                                    <th style="width: 15%">Tran Status</th>
                                    <th style="width: 15%">amount</th>
                                    <th style="width: 10%">Reg Type</th>
                                    <th style="width: 10%">Package short code</th>
                                    <th style="width: 5% !important">Package amount</th>
                                    <th style="width: 15%">Tran gw</th>
                                    <th width="50%" style="width: 10%">Validation id</th>
                                    <th style="width: 15%">Transaction id</th>
                                    <th style="width: 15%">Card no</th>
                                    <th style="width: 15%">Card type</th>
                                    <th style="width: 15%">Card brand</th>
                                    <th style="width: 15%">Card issuer</th>
                                    <th style="width: 15%">Coupon code</th>
                                    <th style="width: 15%">Subscriber ID</th>
                                    <th style="width: 15%">Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($subscriptions_historyArr as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td><?php echo $row['end_date']; ?></td>
                                        <td><?php echo $row['is_active']; ?></td>
                                        <td><?php echo $row['user_name']; ?></td>
                                        <td><?php echo $row['package_id']; ?></td>
                                        <td><?php echo $row['package_name']; ?></td>
                                        <td><?php echo $row['package_short_code']; ?></td>
                                        <td><?php echo $row['package_type']; ?></td>
                                        <td><?php echo $row['package_price']; ?></td>
                                        <td><?php echo $row['package_details']; ?></td>
                                        <td><?php echo $row['card_type']; ?></td>
                                        <td><?php echo $row['card_brand']; ?></td>
                                        <td><?php echo $row['card_issuer']; ?></td>
                                        <td><?php echo $row['coupon_code']; ?></td>
                                        <td><?php echo $row['subscriber_id']; ?></td>
                                        <td><?php echo $row['comments']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div style="margin-top: 20px;" class="alert alert-primary" role="alert">
                        <h3 class="text-center">2. Subscription History </h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <p class="text-center" style="color:red; font-weight: bold; text-align: center">No record found!</p>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <?php
        if (!empty($transactionsArr)) {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">3. Transactions (Source: MySQL, TableName: transactions )</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Tran Date</th>
                                    <th style="width: 15%">Tran Status</th>
                                    <th style="width: 15%">transaction_id</th>
                                    <th style="width: 15%">validation_id</th>
                                    <th style="width: 15%">package_amount</th>
                                    <th style="width: 15%">amount</th>
                                    <th style="width: 15%">store_amount</th>
                                    <th style="width: 15%">currency_amount</th>
                                    <th style="width: 15%">tran_gw</th>
                                    <th style="width: 15%">Reg Type</th>
                                    <th style="width: 15%">Device Type</th>
                                    <th style="width: 15%">package_id</th>
                                    <th style="width: 15%">package_short_code</th>
                                    <th style="width: 15%">card_no</th>
                                    <th style="width: 15%">card_type</th>
                                    <th style="width: 15%">card_brand</th>
                                    <th style="width: 15%">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($transactionsArr as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['tran_date']; ?></td>
                                        <td><?php echo $row['tran_status']; ?></td>
                                        <td><?php echo $row['transaction_id']; ?></td>
                                        <td><?php echo $row['validation_id']; ?></td>
                                        <td><?php echo $row['package_amount']; ?></td>
                                        <td><?php echo $row['amount']; ?></td>
                                        <td><?php echo $row['store_amount']; ?></td>
                                        <td><?php echo $row['currency_amount']; ?></td>
                                        <td><?php echo $row['tran_gw']; ?></td>
                                        <td><?php echo $row['reg_type']; ?></td>
                                        <td><?php echo $row['device_type']; ?></td>
                                        <td><?php echo $row['package_id']; ?></td>
                                        <td><?php echo $row['package_short_code']; ?></td>
                                        <td><?php echo $row['card_no']; ?></td>
                                        <td><?php echo $row['card_type']; ?></td>
                                        <td><?php echo $row['card_brand']; ?></td>
                                        <td><?php echo $row['notes']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div style="margin-top: 20px;" class="alert alert-primary" role="alert">
                        <h3 class="text-center">3. Transactions (Source: MySQL, TableName: transactions </h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <p class="text-center" style="color:red; font-weight: bold; text-align: center">No record found!</p>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <?php
        if (!empty($sslCommerzArr)) {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">4. SSL Commerz Transactions via ipn (Source: MySQL, TableName: ssl_commerz_transactions_via_ipn )</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Tran Date</th>
                                    <th style="width: 15%">Tran Status</th>
                                    <th style="width: 15%">package_code</th>
                                    <th style="width: 15%">tran_id</th>
                                    <th style="width: 15%">bank_tran_id</th>
                                    <th style="width: 15%">amount</th>
                                    <th style="width: 15%">store_amount</th>
                                    <th style="width: 15%">card_no</th>
                                    <th style="width: 15%">card_type</th>
                                    <th style="width: 15%">reconciliation_started</th>
                                    <th style="width: 15%">reconciliation_ended</th>
                                    <th style="width: 15%">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($sslCommerzArr as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['tran_date']; ?></td>
                                        <td><?php echo $row['tran_status']; ?></td>
                                        <td><?php echo $row['package_code']; ?></td>
                                        <td><?php echo $row['tran_id']; ?></td>
                                        <td><?php echo $row['bank_tran_id']; ?></td>
                                        <td><?php echo $row['amount']; ?></td>
                                        <td><?php echo $row['store_amount']; ?></td>
                                        <td><?php echo $row['card_no']; ?></td>
                                        <td><?php echo $row['card_type']; ?></td>
                                        <td><?php echo $row['reconciliation_started']; ?></td>
                                        <td><?php echo $row['reconciliation_ended']; ?></td>
                                        <td><?php echo $row['notes']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div style="margin-top: 20px;" class="alert alert-primary" role="alert">
                        <h3 class="text-center">4. SSL Commerz Transactions via ipn (Source: MySQL, TableName: ssl_commerz_transactions_via_ipn ) </h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <p class="text-center" style="color:red; font-weight: bold; text-align: center">No record found!</p>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>



        <br>
        <div class="alert alert-danger text-center">
            <span class="blink"><strong>Bigquery Section Start</strong></span>
        </div>

        <?php
        if (!empty($subsArr)) {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div style="margin-top: 20px;" class="alert alert-primary" role="alert">
                        <h3 class="text-center">1. Subscription Status (Source: Bigquery, TableName: subscriptions_history)</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Start Date</th>
                                    <th style="width: 5%">End Date</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 10%">Reg Type</th>
                                    <th style="width: 10%">package name</th>
                                    <th style="width: 5% !important">package short code</th>
                                    <th style="width: 15%">package type</th>
                                    <th style="width: 10%">package price</th>
                                    <th style="width: 10%">Payable price</th>
                                    <!--<th style="width: 15%">package details</th>-->
                                    <th style="width: 15%">discount</th>
                                    <th style="width: 15%">Payment method</th>
                                    <th style="width: 15%">Promo Code</th>
                                    <th style="width: 15%">Tran id</th>
                                    <th style="width: 15%">Subscriber id</th>
                                    <th style="width: 15%">Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($subsArr as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td><?php echo $row['end_date']; ?></td>
                                        <td><?php echo $row['is_active']; ?></td>
                                        <td><?php echo $row['reg_type']; ?></td>
                                        <td><?php echo $row['package_name']; ?></td>
                                        <td><?php echo $row['package_short_code']; ?></td>
                                        <td><?php echo $row['package_type']; ?></td>
                                        <td><?php echo $row['package_price']; ?></td>
                                        <td><?php echo $row['payable_price']; ?></td>
                                        <!--<td><?php // echo $row['package_details'];    ?></td>-->
                                        <td><?php echo $row['discount']; ?></td>
                                        <td><?php echo $row['payment_method']; ?></td>
                                        <td><?php echo $row['promo_code']; ?></td>
                                        <td><?php echo $row['tran_id']; ?></td>
                                        <td><?php echo $row['subscriber_id']; ?></td>
                                        <td><?php echo $row['comments']; ?></td>

                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div style="margin-top: 20px;" class="alert alert-primary" role="alert">
                        <h3 class="text-center">1. Subscription</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <p class="text-center" style="color:red; font-weight: bold; text-align: center">No record found!</p>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>

        <?php
        if (!empty($transactionArr)) {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">2. Transaction Status (Source: Bigquery, TableName:transactions )</h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <table class="table table-bordered  w-auto">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Tran Date</th>
                                    <th style="width: 15%">Tran Status</th>
                                    <th style="width: 15%">amount</th>
                                    <th style="width: 10%">Reg Type</th>
                                    <th style="width: 10%">Package short code</th>
                                    <th style="width: 5% !important">Package amount</th>
                                    <th style="width: 15%">Tran gw</th>
                                    <th width="50%" style="width: 10%">Validation id</th>
                                    <th style="width: 15%">Transaction id</th>
                                    <th style="width: 15%">Card no</th>
                                    <th style="width: 15%">Card type</th>
                                    <th style="width: 15%">Card brand</th>
                                    <th style="width: 15%">Card issuer</th>
                                    <th style="width: 15%">Coupon code</th>
                                    <th style="width: 15%">Device Type</th>
                                    <th style="width: 15%">Subscriber ID</th>
                                    <th style="width: 15%">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($transactionArr as $row) {

                                    //for device type
                                    $deviceType = $row['device_type'];
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
                                    ?>
                                    <tr>
                                        <td><?php echo $row['tran_date']; ?></td>
                                        <td><?php echo $row['tran_status']; ?></td>
                                        <td><?php echo $row['amount']; ?></td>
                                        <td><?php echo $row['reg_type']; ?></td>
                                        <td><?php echo $row['package_short_code']; ?></td>
                                        <td><?php echo $row['package_amount']; ?></td>
                                        <td><?php echo $row['tran_gw']; ?></td>
                                        <td><?php echo $row['validation_id']; ?></td>
                                        <td><?php echo $row['transaction_id']; ?></td>
                                        <td><?php echo $row['card_no']; ?></td>
                                        <td><?php echo $row['card_type']; ?></td>
                                        <td><?php echo $row['card_brand']; ?></td>
                                        <td><?php echo $row['card_issuer']; ?></td>
                                        <td><?php echo $row['coupon_code']; ?></td>
                                        <td><?php echo $deviceType; ?></td>
                                        <td><?php echo $row['subscriber_id']; ?></td>
                                        <td><?php echo $row['notes']; ?></td>

                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else {
            ?>
            <div class="row" style="margin: 10px">
                <div class="col-sm-12">
                    <div class="alert alert-primary" role="alert">
                        <h3 class="text-center">2. Transaction </h3>
                    </div>
                    <p></p>
                    <div class="table-responsive w-auto">
                        <p class="text-center" style="color:red; font-weight: bold; text-align: center">No record found!</p>

                    </div>
                </div>
            </div>
            <?php
        }
        ?>

    </body>
</html>
