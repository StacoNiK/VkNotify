<?php

/**
 * @author: Stas Pazhoha
 */
include('VkAuth.php');
include('VK.php');

//Подключаем конфиг
$config = include('config.php');

//получаем access_token
$token = file('token.txt');
if (array_key_exists('0', $token)) {
	$token = $token[0];
} else {
	$vk_auth = new VkAuth();
	$data = $vk_auth->auth($config['vk_login'], $config['vk_pass']);
	$token = $data['access_token'];
	$f = fopen('token.txt', 'w+');
	fwrite($f, $token);
	fclose($f);
}

$vk = new VK('3697615', 'AlVXZFMUqyrnABp8ncuU', $token);

$result = $vk->api('messages.getLongPollServer', array("use_ssl" => "0"));

$m = 0;
$users = array();
while (1) {
	if ($m > $config['max']) {
		echo 'Работа завершена!';
		break;
	}
	$data = json_decode(file_get_contents("http://".$result['response']['server']."?act=a_check&key=".$result['response']['key']."&ts=".$result['response']['ts']."&wait=25&mode=2"), true);
	if (array_key_exists('failed', $data)) {
		$result = $vk->api('messages.getLongPollServer', array("use_ssl" => "0"));
		continue;
	}
	$result['response']['ts'] = $data['ts'];
	foreach ($data['updates'] as $arr) {
		if ($arr[0] == 4) {
			if (!array_key_exists("$arr[3]", $users)) {
				$users[$arr[3]] = $vk->api('users.get', array("user_ids" => $arr[3], "fields" => "photo_50"));
			}
			$user_data = $users[$arr[3]]['response'][0];
			$user_name = $user_data['first_name']." ".$user_data['last_name'];
			$message = str_replace("<br>", " ", trim($arr[6]));
			$icon = __DIR__."/vk-icon.png";
			if ($arr[2] != 19) {
				if ($arr[2]-19 % 8 != 0) {	
					exec("notify-send '[VK] $user_name' '$message' -i '$icon'");
					echo "Notify sended\n";
				}
			}
		}
	}
	sleep($config['sleep']);
	$m++;
}
