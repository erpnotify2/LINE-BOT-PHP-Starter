<?php  
require_once __DIR__ . '/vendor/autoload.php';
$host = "";
$user = "";
$pass = "";
$dbname = "";
$conn = mysqli_connect($host,$user,$pass,$dbname);
mysqli_query($conn,'SET NAMES utf8');



$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(' กรอกตรงนี้เอง ');
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => 'กรอกตรงนี้เอง ']);

$content = file_get_contents('php://input');
$events = json_decode($content, true);
if (!is_null($events['events'])) {
	foreach ($events['events'] as $event) {
		$text = $event['message']['text'];
		$replyToken = $event['replyToken'];
		$userId = $event['source']['userId'];

		if(preg_match('/register/', $text)){

			$textexplode = explode('#', $text);
			$student_id = $textexplode[1];
			$student_phone = $textexplode[2];
			$sql  = "SELECT member_userId FROM member WHERE member_userId = '$userId'";
			$query = mysqli_query($conn,$sql);
			$result = mysqli_fetch_array($query);

			if(empty($result)){
				$response = $bot->getProfile($userId);
				if ($response->isSucceeded()) {
					$profile = $response->getJSONDecodedBody();
					$displayName = mysqli_real_escape_string($conn,$profile['displayName']);

					$sql  = "INSERT INTO member(member_name,member_userId,member_student_id,member_phone) 
					VALUES('$displayName','$userId','$student_id','$student_phone')";
					$query = mysqli_query($conn,$sql);	
					$textReply = 'ทำการลงทะเบียนร้อย!! ';
				}

			}else{
				$textReply = 'คุณได้ทำการลงทะเบียนไปแล้ว ';
				
			}

			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($textReply);
			$response = $bot->replyMessage($replyToken, $textMessageBuilder);

		}

		if($text == "img"){

			$imgOrigi = 'https://www.picz.in.th/images/2018/01/15/banner1.jpg';
			$outputText = new LINE\LINEBot\MessageBuilder\ImageMessageBuilder($imgOrigi,$imgOrigi);
			$response = $bot->replyMessage($replyToken, $outputText);

		}

		if($text == "list"){
			$columns = array();
			$img_url = "https://www.picz.in.th/images/2018/01/15/banner1.jpg";
			for($i=0;$i<5;$i++) {
				$actions = array(
					new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","#".$i),
					new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("View","http://www.google.com")
				);
				$column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("ทดสอบ", "description", $img_url , $actions);
				$columns[] = $column;
			}
			$carousel = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
			$outputText = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("Carousel Demo", $carousel);
			$response = $bot->replyMessage($replyToken, $outputText);
		}

	}
}
echo "OK";