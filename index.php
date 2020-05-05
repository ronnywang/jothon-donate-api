<?php

$content = file_get_contents('php://input');
$content = json_decode($content);
file_put_contents('/tmp/tmp', json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESACPED_SLASHES));

if (strpos($content->mail->commonHeaders->subject, '付款成功確認信') === false) {
    exit;
}
$body = base64_decode($content->content);
if (!preg_match('#Content-Type: multipart/alternative;\s+boundary="([^"]*)"#s', $body, $matches)) {
    exit;
}
foreach (explode($matches[1], $body) as $part) {
    if (false === stripos(trim($part), 'Content-Type: text/html; charset=utf-8')) {
        continue;
    }

    $doc = new DOMDocument;
	$body = quoted_printable_decode(explode("\r\n\r\n", $part, 2)[1]);
    @$doc->loadHTML('<!DOCTYPE html> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $body . '</body></html>');;
    $values = new StdClass;
    foreach ($doc->getElementsByTagName('tr') as $tr_dom) {
        $td_doms = $tr_dom->getElementsByTagName('td');
        if (trim($td_doms->item(0)->nodeValue) == '') {
            continue;
        }
        $values->{trim($td_doms->item(0)->nodeValue)} = trim($td_doms->item(1)->nodeValue);
    }
	if (strpos($body, '這是一個定期定額捐款')) {
        $freq = true;
        $str = '[定期定額]';
	} else {
        $freq = false;
        $str = '';
    }

    $str .= " {$values->{'捐款徵信顯示名稱'}} 捐了 {$values->{'金額'}}";
    if ($c = $values->{'捐款備註'}) {
        $str .= "({$c})";
    }
    echo $str . "\n";
    $token = getenv('token');
    $curl = curl_init('https://slack.com/api/chat.postMessage?token=' . urlencode($token) . '&channel=' . urlencode('#jothon-organizers') . '&username=' . urlencode('揪松機器人'));
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'text=' . urlencode($str));
    curl_exec($curl);
//     [金額] => NT$ 300
	//[捐款徵信顯示名稱] => YiJin
   // [捐款備註] =>
}
