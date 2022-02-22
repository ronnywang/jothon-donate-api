<?php

if (file_exists('config.php')) {
    include('config.php');
}

$secret = getenv('otpsecret');
$secret_terms = explode('&', $secret);
$secret_terms = array_map(function($s) { return explode('=', $s); }, $secret_terms);

if ($_GET['secret'] != getenv('otppagesecret')) {
    echo 'wrong secret';
    exit;
}
/**
* Encode in Base32 based on RFC 4648.
* Requires 20% more space than base64 
* Great for case-insensitive filesystems like Windows and URL's  (except for = char which can be excluded using the pad option for urls)
*
* @package default
* @author Bryan Ruiz
**/
class Base32 {

   private static $map = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
        'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
        '='  // padding char
    );
   
   private static $flippedMap = array(
        'A'=>'0', 'B'=>'1', 'C'=>'2', 'D'=>'3', 'E'=>'4', 'F'=>'5', 'G'=>'6', 'H'=>'7',
        'I'=>'8', 'J'=>'9', 'K'=>'10', 'L'=>'11', 'M'=>'12', 'N'=>'13', 'O'=>'14', 'P'=>'15',
        'Q'=>'16', 'R'=>'17', 'S'=>'18', 'T'=>'19', 'U'=>'20', 'V'=>'21', 'W'=>'22', 'X'=>'23',
        'Y'=>'24', 'Z'=>'25', '2'=>'26', '3'=>'27', '4'=>'28', '5'=>'29', '6'=>'30', '7'=>'31'
    );
   
    /**
     *    Use padding false when encoding for urls
     *
     * @return base32 encoded string
     * @author Bryan Ruiz
     **/
    public static function encode($input, $padding = true) {
        if(empty($input)) return "";
        $input = str_split($input);
        $binaryString = "";
        for($i = 0; $i < count($input); $i++) {
            $binaryString .= str_pad(base_convert(ord($input[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32 = "";
        $i=0;
        while($i < count($fiveBitBinaryArray)) {   
            $base32 .= self::$map[base_convert(str_pad($fiveBitBinaryArray[$i], 5,'0'), 2, 10)];
            $i++;
        }
        if($padding && ($x = strlen($binaryString) % 40) != 0) {
            if($x == 8) $base32 .= str_repeat(self::$map[32], 6);
            else if($x == 16) $base32 .= str_repeat(self::$map[32], 4);
            else if($x == 24) $base32 .= str_repeat(self::$map[32], 3);
            else if($x == 32) $base32 .= self::$map[32];
        }
        return $base32;
    }
   
    public static function decode($input) {
        if(empty($input)) return;
        $paddingCharCount = substr_count($input, self::$map[32]);
        $allowedValues = array(6,4,3,1,0);
        if(!in_array($paddingCharCount, $allowedValues)) return false;
        for($i=0; $i<4; $i++){
            if($paddingCharCount == $allowedValues[$i] &&
                substr($input, -($allowedValues[$i])) != str_repeat(self::$map[32], $allowedValues[$i])) return false;
        }
        $input = str_replace('=','', $input);
        $input = str_split($input);
        $binaryString = "";
        for($i=0; $i < count($input); $i = $i+8) {
            $x = "";
            if(!in_array($input[$i], self::$map)) return false;
            for($j=0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@self::$flippedMap[@$input[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
            }
        }
        return $binaryString;
    }
}


function generateHOTP($secret, $counter) {
   $decodedSecret = Base32::decode($secret);
   $buffer = array_fill(0, 8, '');
   for ($i = 0; $i < 8; $i ++) {
      $buffer[7 - $i] = chr($counter & 0xff);
	  $counter = $counter >> 8;
   }
   // Step 1: Generate an HMAC-SHA-1 value
   $hmac = hash_hmac('sha1', implode('', $buffer), $decodedSecret, true);
   $hmacs = [];
   for ($i = 0; $i < strlen($hmac); $i ++) {
	   $hmacs[$i] = ord(substr($hmac, $i, 1));
   }
   $code = dynamicTruncationFn($hmacs);

   // Step 3: Compute an HOTP value
   return $code % 10 ** 6;
}

function dynamicTruncationFn($hmacValue) {
	$offset = $hmacValue[count($hmacValue) - 1] & 0xf;

   return (
	   (($hmacValue[$offset] & 0x7f) << 24) |
      (($hmacValue[$offset + 1] & 0xff) << 16) |
      (($hmacValue[$offset + 2] & 0xff) << 8) |
      ($hmacValue[$offset + 3] & 0xff)
   );
}

if ($_POST['name'] and $_POST['type']) {
    $counter = floor(time() / 30);
    $secret = null;
    foreach ($secret_terms as $secret_term) {
        if ($secret_term[0] == $_POST['type']) {
            $secret = $secret_term[1];
            break;
        }
    }
    if (!$secret) {
        echo 'failed';
        exit;
    }
    $code = generateHOTP(strtoupper($secret), $counter);

    $username = '揪松兩步驗證';
    $token = getenv('token');
    $channel = getenv('channel');
    $str = sprintf("%s 要登入揪松 %s 帳號，驗證碼為 %06d", $_POST['name'], $_POST['type'], $code);

    $curl = curl_init('https://slack.com/api/chat.postMessage?token=' . urlencode($token) . '&channel=' . urlencode($channel) . '&username=' . urlencode($username));
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'text=' . urlencode($str));
    curl_exec($curl);
    header('Location: otp.php?secret=' . urlencode(getenv('otppagesecret')));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>揪松兩步驗證</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css">
</head>
<body>
<h1>揪松兩步驗證</h1>
<p>六位數驗證碼將會傳送至 <?= getenv('channel') ?> 頻道</p>
<form method="post" action="">
    我是<input type="text" placeholder="請輸入您的暱稱" name="name" required="required"><br>
    服務： <select name="type">
        <?php foreach ($secret_terms as $secret_term) { ?>
        <option value="<?= $secret_term[0] ?>"><?= $secret_term[0] ?></option>
        <?php } ?>
    </select>
    <button type="submit">送出</button>
</form>
</body>
</html>
