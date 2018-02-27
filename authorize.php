<?php


/**
 * Class User
 * For login and register
 */
class User {

	private $isLoggedIn = false;
	
	private $is2Factor = false;
	
	private $id;

	public function __construct() {
		if(isset($_SESSION['userid'])) {
			$this->id = $_SESSION['userid'];
			$this->isLoggedIn = true;
			$this->is2Factor = false;
			return;
		}
		$factor2 = explode(",", $_SESSION['2factor']);
		if(isset($_SESSION['2factor']) && $factor2[1] == '1') {
			$this->id = $factor[0];
			$this->isLoggedIn = false;
			$this->is2Factor = true;
			return;
		}
	}

	public function login($email, $password) {
		$status = 'false';
		$db = new DB();
		$db->select('users')->where('email', $email)->run();
		if($db->count() && password_verify($password, $db->first()->password)) {
			$userid = $db->first()->id;
			if($db->first()->phone != 0 AND $db->first()->fa2active == 1){
				
				$num_str = sprintf("%06d", mt_rand(1, 999999));
				$time = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +10 minutes"));
			$db1 = new DB();
$db1->query("UPDATE `users` SET `factor2`='".$num_str."', `fa2activeuntil`='".$time."' WHERE `id`='".$db->first()->id."'")->run();
$msg = 'Your 2F-Auth Code is: '.$num_str.'
 It is only usable for 10 Minutes.';
SendSMS($db->first()->phone, $msg);
			$this->id = $db->first()->id;
			$_SESSION['2factor'] = $userid .','. true;
			$this->is2Factor = true;
			$status = '2factor';
			//HIER 2 FACTOR
			}else{
				$this->id = $_SESSION['userid'] = $userid;
				$this->isLoggedIn = true;
				$status = 'true';
			}
		}
		return $status;
	}
	
	public function factor2login($uid, $code) {
		$status = 'false';
		$db = new DB();
		$db->select('users')->where('id', $uid)->run();
		if($db->count() == 1 &&  $db->first()->factor2 == $code && date("Y-m-d H:i:s") <= $db->first()->fa2activeuntil) {
			$this->id = $_SESSION['userid'] = $db->first()->id;
				$this->isLoggedIn = true;
				$status = 'true';
			}
			unset($_SESSION['2factor']);
		return $status;
		}
		
	public function passwordreset($password, $code) {
		$status = 'false';
		$db = new DB();
		$db->select('users')->where('passwordreset', $code)->run();
		if($db->count() == 1 && date("Y-m-d H:i:s") <= $db->first()->pwresetdate && $db->first()->pwresetactive == 1) {
			$db1 = new DB();
$db1->query("UPDATE `users` SET `password`='". password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]) ."',`pwresetactive`=0 WHERE `id`='".$db->first()->id."'")->run();
				$status = 'true';
			}
		return $status;
		}
	


	public function register($email,$password,$surename,$forename,$mobile,$ip,$referer) {
$apikey = '411d385215be15a2ff40512935dc8e7f';
$res = file_get_contents('https://www.iplocate.io/api/lookup/'.$ip.'?apikey='.$apikey);
$res = json_decode($res, true);

$country = $res['country'];
		$db = new DB();
		$db->select('users')->where('email', $email)->run();
		if($db->count() == 0){
		$db = new DB();
		$db->insert('users')
			->set('email', $email)
			->set('password', password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]))
			->set('surename', $surename)
			->set('forename', $forename)
			->set('country', $country)
			->set('uid2', generateRandomString(16))
			->set('ip', $ip)
			->set('registerdate', date("Y-m-d H:i:s"))
			->set('phone', $mobile)
			->set('refid', generateRandomString(8))
			->set('refererid', $referer)
			->run();
			$endpoint = new MailWizzApi_Endpoint_ListSubscribers();
			$response = $endpoint->create('lb709ad2g73f1', array(
    'EMAIL'    => $email, // the confirmation email will be sent!!! Use valid email address
    'FNAME'    => $forename,
    'LNAME'    => $surename
));
			return 'success';
		}else{
			return 'emailexist';
		}
	}

	public function isLoggedIn() {
		return $this->isLoggedIn;
	}
	public function is2Factor() {
		return $this->is2Factor;
	}
	public function getId() {
		return $this->id;
	}
}
