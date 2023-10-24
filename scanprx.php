<?php
error_reporting(1);
$BBlack="\033[1;30m" ; 
$BRed="\033[1;31m" ;
$BGreen="\033[1;32m" ;
$BYellow="\033[1;33m" ;
$BBlue="\033[1;34m" ;
$BPurple="\033[1;35m" ;
$BCyan="\033[1;36m" ;
$BWhite="\033[1;37m" ;
$Blue="\033[0;34m";
$lime="\033[1;32m";
$red="\033[1;31m";
$xanh="\033[1;32m";
$cyan="\033[1;36m";
$yellow="\033[1;33m";
$turquoise="\033[1;34m";
$maugi="\033[1;35m";
$white= "\033[1;37m";

$useragent = "Mozilla/5.0 (Linux; Android 10; SM-J610F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.1.4638.51 Mobile Safari/537.36,";

echo $lime."\033[1;37m~\033[1;31m[\033[1;32m✓\033[1;31m]$white =>$BGreen  Proxy can lay: $BWhite";
$sl = trim(fgets(STDIN));
echo $lime."\033[1;37m~\033[1;31m[\033[1;32m✓\033[1;31m]$white =>$BGreen Số luồng: $BWhite";
$threads = trim(fgets(STDIN));
for ($makep=38;$makep > 0;$makep--){ 
    echo $white."-"; usleep(0); 
    echo $white."-"; 
} 
echo "\n"; 

$threads = $threads; // Number of concurrent threads
$proxyQueue = new SplQueue(); // Queue to store proxies

// Function to process each proxy
function processProxy($proxyData) {
    global $so, $BRed, $BGreen, $BCyan, $BYellow;

    $id = $proxyData['ipPort'];
    $country = $proxyData['country'];

    echo $BCyan."$Blue [ Bi TD ] </>$lime $id •$white $country\n";
    echo "\r                                           \r                       \r";

    $f = fopen("proxylist1.txt","a");
    fwrite($f, "\n$id");
    fclose($f);


    $so++;
}

// Initialize curl_multi handle
$multiCurl = curl_multi_init();

while ($so < $sl) {
    $runningThreads = 0;
    while ($runningThreads < $threads && $so < $sl) {
        $header = array( 
            "Host:pubproxy.com",
            "Upgrade-Insecure-Requests:1",
            "User-Agent:Mozilla/5.0 (Linux; Android 10; SM-J610F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.1.4692.115 Mobile Safari/537.36",
        );
        $mr = curl_init();
        curl_setopt($mr, CURLOPT_URL, 'http://pubproxy.com/api/proxy');
        curl_setopt($mr, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($mr, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($mr, CURLOPT_COOKIEFILE, 'file.txt');
        curl_setopt($mr, CURLOPT_HTTPHEADER, $header);
        curl_setopt($mr, CURLOPT_USERAGENT, $useragent);

        curl_multi_add_handle($multiCurl, $mr);
        $proxyQueue->enqueue($mr);
        $so++;
        $runningThreads++;
    }

    // Execute the cURL requests in parallel
    $active = null;
    do {
        $status = curl_multi_exec($multiCurl, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

    while (!$proxyQueue->isEmpty()) {
        $proxyHandle = $proxyQueue->dequeue();
        $proxyData = json_decode(curl_multi_getcontent($proxyHandle), true);

        // Handle the response data
        if (isset($proxyData['data'][0])) {
            processProxy($proxyData['data'][0]);
        }

        curl_multi_remove_handle($multiCurl, $proxyHandle);
        curl_close($proxyHandle);
    }
}

curl_multi_close($multiCurl);

echo $red."Scan Success Lưu Proxy Tại $BYellow proxylist.txt \n";
