<?php

/*
 * Universal library to make webhooks easy to automate.
 * Initializing with GitHub webhooks to automatic-deploy
 * with site merzlyakov.pro
 * Michael S. Merzlyakov AFKA predator_pc, April, 2019
 * mailto: predator_pc@ngs.ru
 *
 */

class Telegram
{
    public $userid;
    public $token;

    public function __construct ($token, $userid) {
        $this->token  = $token;
        $this->userid = $userid;
    }

    public function send($message) {
        if($this->userid && $this->token) {
            $url = "https://api.telegram.org/bot" . $this->token . "/sendMessage?parse_mode=markdown&chat_id=" . $this->userid;
            $url = $url . "&text=" . "```" .urlencode($message)."```";

            $ch = curl_init();

            $options = [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true
            ];

            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } else {
            return false;
        }
    }
}

class DeployFromGithub
{
    public $localpath;
    public $gitlocation;
    public $codeception;
    public $json;
    public $json_pretty;
    public $data;
    public $force;

    public function __construct ($git, $codeception, $path)
    {
        $this->gitlocation = $git;
        $this->codeception = $codeception;
        $this->localpath   = $path;
    }

    public function init($secret, $headers)
    {
        $rawPost   = file_get_contents('php://input');
        $signature = 'sha1=' . hash_hmac('sha1', $rawPost, $secret);

        if ($signature == $headers['X-Hub-Signature']){
            $this->json = trim($_POST['payload']);
            $this->data = json_decode($this->json);
            $this->json_pretty = json_encode($this->data, JSON_PRETTY_PRINT);

            return true;
        } else {
            if ($this->force){
                return true;
            } else {
                return false;
            }
        }
    }

    public function pull()
    {
        if (!empty($this->localpath) && !empty($this->gitlocation)) {
            chdir($this->localpath);
            $output  = shell_exec($this->gitlocation . " stash");
            $output .= shell_exec($this->gitlocation." pull origin master 2>&1");

            if (DEBUG_LEVEL > 0) {
                file_put_contents(LOGFILE_NAME, $output . "\n\n", FILE_APPEND);
            }
            return $output;
        } else {
            return false;
        }
    }

    public function revert()
    {
        if (!empty($this->localpath) && !empty($this->gitlocation)) {
            $reflog      = shell_exec($this->gitlocation." reflog");
            $reflogLines = explode("\n", $reflog);
            $refid       = explode(" ", $reflogLines[1]);
            $refidresult = shell_exec($this->gitlocation." reset --hard " . $refid[0]);
            return $refidresult;
        } else {
            return false;
        }

    }

    public function tests()
    {
	    if (!empty($this->codeception) && !empty($this->localpath)){
            $data = shell_exec($this->codeception." run");
            $lines = explode("\n",$data);

            if (DEBUG_LEVEL > 0) {
                file_put_contents(LOGFILE_NAME, $data . "\n\n", FILE_APPEND);
            }

            $fail = false;
            $success = false;

            foreach ($lines as $line) {
                $errors = strstr($line, "ERRORS");
                if($errors!=false) $fail = true;

                $failures = strstr($line, "FAILURES");
                if($failures!=false) $fail = true;

                $oks = strstr($line, "OK");
                if($oks!=false) $success = true;
            }

            if ($success && !$errors){
                return true;
            } else {
                return false;
            }
        } else {
	        return false;
    	}
    }
}

// Ready to deploy

include_once ("update-config.php");

$telegram = new Telegram($token, $user);
$deploy = new DeployFromGithub($git, $codeception, $path);
$headers   = getallheaders();

$log = "";
$log .= "\n".$timestamp . "\nUpdate log @ ".$project;

if($force == $secret){
    $deploy->force = true;
    $log .= "\nForce            : enabled";
} else {
    $log .= "\nForce            : disabled";
}

if (($init = $deploy->init($secret, $headers)) || $deploy->force) {
    $log .= "\nInitialization   : success";
    if ($pullresult = $deploy->pull()) {
        $log .= "\nPull `master`    : success";
        if (($deploy->tests()) || $deploy->force) {
            $log .= "\nRun tests        : success";
            $log .= "\nStatus           : branch updated";
            $log .= "\nRESULT           : deploy succeed";
            $log .= "\n\n".$pullresult;
            $telegram->send($log);
        } else {
            $revertresult = $deploy->revert();
            $log .= "\nRun tests        : tests failed";
            $log .= "\nReverting        : success";
            $log .= "\nStatus           : branch reverted";
            $log .= "\nRESULT           : deploy failed";
            $log .= "\n\n".$revertresult;
            $telegram->send($log);
        }
    }
} else {
    echo $forbiddenMessage;
    $telegram->send($timestamp.
        "\nAccident report\nSomeone try to use our update URL\n".
        "merzlyakov.pro <-IN [ ".$_SERVER['REMOTE_ADDR']." ]");
}


?>