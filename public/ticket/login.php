<?php
session_start(); /* Starts the session */

/* Check Login form submitted */
if (isset($_POST['Submit'])) {

    /* Define username and associated password array */
    //$logins = array('admin' => 'AdminP@ss', 'nex' => 'nexP@ss');
    $logins = array('admin' => 'Ts@NexD#23', 'support_nex' => 'Nex@778#TS', 'tsports' => 'TS&3787##', 'support_ts' => 'ts@2233#TS', 'agent1_12' => 'Ag12@Ts', 'agent2_7' => 'Ag7@P@ss');

    /* Check and assign submitted username and password to new variable */
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    /* Check username and password existence in defined array */
    if (isset($logins[$username]) && $logins[$username] == $password) {
        /* Success: Set session variables and redirect to Protected page  */
        $_SESSION['UserData']['username'] = $logins[$username];
        if ($username == 'admin') {
            $_SESSION['user_id'] = 1;
        } elseif ($username == 'support_nex') {
            $_SESSION['user_id'] = 2;
        } elseif ($username == 'tsports') {
            $_SESSION['user_id'] = 3;
        } elseif ($username == 'support_ts') {
            $_SESSION['user_id'] = 4;
        } elseif ($username == 'agent1_12') {
            $_SESSION['user_id'] = 5;
        } elseif ($username == 'agent2_7') {
            $_SESSION['user_id'] = 6;
        } else {
            $_SESSION['user_id'] = 0;
        }
        
        //header("location:index.php");
        header("location:index.php");
        exit;
    } else {
        /* Unsuccessful attempt: Set error message */
        $msg = "<span style='color:red'>Invalid Login Details</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>TSPORTS Ticketing Tools</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <link href="./css/style.css" rel="stylesheet">
    </head>
    <body>

        <div class="container" style="margin-top:100px">
            <h2 class="text-center">TS Ticket Tool - Login</h2>
            <?php if (isset($msg)) { ?>
                <p class="text-center"><?php echo $msg; ?></p>
            <?php } ?>

            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <form action="" method="post" name="Login_Form">
                        <div class="form-group">
                            <label for="email">Username:</label>
                            <input type="text" class="form-control" id="email" placeholder="Enter username" name="username">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
                        </div>

                        <div class="form-group text-center">
                            <input name="Submit" type="submit" value="Login" class="btn btn-primary">
                        </div>
                    </form>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>

    </body>
</html>
