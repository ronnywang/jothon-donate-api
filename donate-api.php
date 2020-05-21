<?php

$obj = json_decode(file_get_contents("/tmp/donates"));
if (!is_array($obj)) {
        $obj = array();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
echo json_encode($obj);
