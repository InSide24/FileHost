<?php 
//disable_access_token by InSide24 aka Raphael Roth

$url = 'https://filehost.net/api/v2/disable_access_token';
$data = array('access_token ' => 'value1', 'account_id' => 'value2');

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) { /* Handle error */ }

var_dump($result); ?>