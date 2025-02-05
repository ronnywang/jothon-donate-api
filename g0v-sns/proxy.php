<?php

header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Origin: *');
if ($_GET['type'] == 'domain') {
    system("curl -L 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSrxGZK64InaS4Lz25fHR4Z2eutqDpFaO7Vrl3bcMNeOjb6YbZCX-cl9BXVDjVTPozOcCJzk4iATQON/pub?gid=0&single=true&output=csv'");
} elseif ($_GET['type'] == 'calendar') {
    system("curl -L 'https://docs.google.com/spreadsheets/d/e/2PACX-1vS6X8KGPm2HzLMySiJdEFxzWGLhqbITOguHT_MHoS72H6dJWdarpmHREuNCa4O74TPm618t5IS26vD6/pub?output=csv'");
} else {
    system("curl -L 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQydJyzRhZZFKqGF7OVGv_h7xh2Ku7bS3HgsDXLWj1TknTpjxm7IJtrMX2_74tSmW6fB5ZepvyYApdC/pub?output=csv'");
}
