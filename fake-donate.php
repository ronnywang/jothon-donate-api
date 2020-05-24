<?php
$obj = json_decode(file_get_contents("/tmp/fake-donates"));
if (!is_array($obj)) {
    $obj = array();
}
if ($_POST) {
    $obj[] = array('name' => $_POST['name'], 'money' => $_POST['money'], 'say' => $_POST['say'], 'period' => $_POST['period'], 'time' => time());
    $obj = array_slice($obj, -10);
    file_put_contents("/tmp/fake-donates", json_encode($obj));
    header('Location: fake-donate.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css">
</head>
<body>
<code>https://<?= $_SERVER['HTTP_HOST'] ?>/fake-donate-api.php</code>
<br>
<textarea style="width: 500px; height: 300px"><?= htmlspecialchars(json_encode($obj, JSON_UNESCAPED_UNICODE)) ?></textarea>
<h1>新增 donate</h1>
<form method="post">
    人名: <input type="text" name="name"><br>
    金額：<input type="number" name="money"><br>
    留言：<input type="text" name="say"><br>
    定期定額：
    <label><input type="radio" name="period" value="1">是</label>
    <label><input type="radio" name="period" value="0">否</label>
    <button type="submit">新增</button>
</form>
</body>
</html>
