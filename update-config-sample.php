<?php

//
// force update link use for your health
//
// http://merzlyakov.pro/update.php?force=Aca1Bmfn2Wx68SvnLRQVf40ctJ3L8AyKdX
//

define("LOGFILE_NAME", "update.log");
define("DEBUG_LEVEL",1);

if (DEBUG_LEVEL > 1) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

$secret = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

$forbiddenMessage = "<h1>Forbidden 403</h1><h4>You're not authorized to see this page</h4><hr>".
    "<p>In case you're think you should have access ask administraion".
    " <a href='mailto:admin@site.com'>admin@site.com</a></p>";

// https://api.telegram.org/bot<YourBOTToken>/getUpdates
// my ID is 232291795 predator_pc
$user = "XXXXXXXXXX";

$token = "ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ";

$git = "/path/to/git";

$path = "/path/to/your/www";

$codeception = "./vendor/bin/codecept";

$timestamp = date("Y-m-d H:m:s", strtotime("now"));

$force = $_GET['force'];

$project = "site.com";

?>
