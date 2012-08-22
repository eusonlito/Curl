<?php
include (__DIR__.'/libs/ANS/Curl/Curl.php');

# Load and init Curl
$Curl = new \ANS\Curl\Curl;

$Curl->init('https://github.com/');

# GET request
$response = $Curl->get('eusonlito.atom');

# POST request
$response = $Curl->post('search', array(
    'q' => 'curl',
    'start_value' => 1,
    'type' => 'Everything',
    'language' => 'PHP'
));

# Custom request
$response = $Curl->custom('DELETE', 'notifications/subscribe', array(
    'repository_id' => 0
));

# Set Cookie
$Curl->setCookie('logged=1');

# Get raw response
$Curl->getResponse();

# Get request info
$Curl->getInfo();

# Process Json response
$Curl->init('https://api.github.com/');

$Curl->setOption(CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Content-Type: application/json; charset=utf-8',
    'Connection: Keep-Alive'
));

$Curl->setJson(true);

$Curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
$Curl->setOption(CURLOPT_USERPWD, 'username:password');

$response = $Curl->get('user/repos');

# Request a URL outside the main server
$Curl->fullGet('http://google.com/');

# Advanced integration (https://github.com/eusonlito/Timer)
include (__DIR__.'/libs/ANS/Timer/Timer.php');

$Timer = new Timer;

$Curl->setTimer($Timer);

$response = $Curl->get('user/repos');

foreach ($Timer->get() as $timer) {
    echo "\n".sprintf('%01.6f', $timer['total']).' - '.$timer['text'];
}
