<?php
if (file_exists('config.php')) {
    include('config.php');
}

if ($_POST['name']) {
    $username = '揪松實聯制';
    $token = getenv('token');
    $channel = getenv('channel');
    $str = sprintf("%s 送出了揪松實聯制", $_POST['name']);
    $curl = curl_init('https://slack.com/api/chat.postMessage?token=' . urlencode($token) . '&channel=' . urlencode($channel) . '&username=' . urlencode($username));
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'text=' . urlencode($str));
    curl_exec($curl);
?>
    <script>
    alert('Success!');
    document.location = 'https://jothon.g0v.tw';
    </script>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>揪松實聯制</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css">
</head>
<body>
<h1>揪松1922實聯制</h1>
<form method="post" action="1922.php">
    我是<input type="text" placeholder="請輸入您的暱稱" name="name" required="required">
    <button type="submit">送出</button>
</form>
</body>
</html>
