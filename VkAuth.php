<?php

class VkAuth
{
	private $check = false;

	protected $client_id = '3697615';
	protected $client_secret = 'AlVXZFMUqyrnABp8ncuU';

	/*Функция возвращает токен пользователя для дальнейшей работы с vk api*/
	public function auth($login, $password, $for_check = '')
	{
		$data = array();
		$url = "https://oauth.vk.com/token?grant_type=password&client_id=".$this->client_id."&client_secret=".$this->client_secret."&username=".$login."&password=".$password."&scope=groups,wall,photos,messages,offline&v=5.37";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = json_decode(curl_exec($ch), true);
		if (array_key_exists('error', $data)) {
			if ($data['error'] == 'need_captcha') {
				/*$code = $this->antigate->get($data['captcha_img']);
                $captcha_sid = $data['captcha_sid'];
                return $this->auth($login, $password, $client_id, $client_secret, $for_check, $captcha_sid, $code);
                */
                echo 'Капча!';
                return false;
			}
			curl_setopt($ch, CURLOPT_URL, $data['redirect_uri']);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$res = curl_exec($ch);
			$d = explode('<form method="post" action="', $res);
			$d1 = $d[1];
			$d2 = explode('">', $d1);
			$url = 'https://m.vk.com'.$d2[0];
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/43.0.2357.130 Chrome/43.0.2357.130 Safari/537.36');
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'code='.$for_check);
			sleep(1);
			$res2 = curl_exec($ch);
			$info = curl_getinfo($ch);
			if ($this->check == true) exit; //чтобы не было рекурсии
			$check = true;
			$data = $this->auth($login, $password, $for_check);
			if (array_key_exists('access_token', $data)) {
				return $data;
			} else {
				throw new Exception("Ошибка авторизации Вконтакте", 1);
				return false;
			}
		}
		return $data;
	}
}