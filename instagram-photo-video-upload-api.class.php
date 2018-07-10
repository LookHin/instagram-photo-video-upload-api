<?php
/*****************************************************************
Created :  2017-02-08
Author : Mr. Khwanchai Kaewyos (LookHin)
E-mail : khwanchai@gmail.com
Website : https://www.unzeen.com
Facebook : https://www.facebook.com/LookHin
Source Code On Github : https://github.com/LookHin/instagram-photo-video-upload-api
Rewrite Code From : https://github.com/mgp25/Instagram-API
*****************************************************************/

class InstagramUpload{
  private $username;
  private $password;
  private $csrftoken;
  private $phone_id;
  private $guid;
  private $uid;
  private $device_id;
  private $cookies;

  private $api_url = 'https://i.instagram.com/api/v1';
  private $ig_sig_key = '5ad7d6f013666cc93c88fc8af940348bd067b68f0dce3c85122a923f4f74b251';

  private $sig_key_version = '4';
  private $x_ig_capabilities = '3ToAAA==';
  private $android_version = 18;
  private $android_release = '4.3';
  private $android_manufacturer = "Huawei";
  private $android_model = "EVA-L19";
  private $headers = array();
  private $user_agent = "Instagram 10.3.2 Android (18/4.3; 320dpi; 720x1280; Huawei; HWEVA; EVA-L19; qcom; en_US)";

  public function __construct(){
    $this->guid = $this->generateUUID();
    $this->phone_id = $this->generateUUID();
    $this->device_id = $this->generateDeviceId();
    $this->upload_id = $this->generateUploadId();

    $this->headers[] = "X-IG-Capabilities: ".$this->x_ig_capabilities;
    $this->headers[] = "X-IG-Connection-Type: WIFI";
  }

  public function Login($username="", $password=""){
    $this->username = $username;
    $this->password = $password;

    $this->csrftoken = $this->GetToken();

    $arrUidAndCooike = $this->GetLoginUidAndCookie();

    $this->uid = $arrUidAndCooike[0];
    $this->cookies = $arrUidAndCooike[1];
  }

  public function UploadPhoto($image, $caption){
    $this->UploadPhotoApi($image);
    $this->ConfigPhotoApi($caption);
  }

  public function UploadVideo($video, $image, $caption){
    $this->UploadVideoApi($video);
    $this->UploadPhotoApi($image);
    sleep(20);
    $this->ConfigVideoApi($caption);
  }

  private function GetToken(){
    $strUrl = $this->api_url."/si/fetch_headers/?challenge_type=signup";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$strUrl);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close ($ch);

    preg_match_all("|csrftoken=(.*);|U",$result,$arrOut, PREG_PATTERN_ORDER);
    $csrftoken = $arrOut[1][0];

    if($csrftoken != ""){
      return $csrftoken;
    }else{
      print $result;
      exit;
    }
  }

  private function GetLoginUidAndCookie(){
    $arrPostData = array();
    $arrPostData['login_attempt_count'] = "0";
    $arrPostData['_csrftoken'] = $this->csrftoken;
    $arrPostData['phone_id'] = $this->phone_id;
    $arrPostData['guid'] = $this->guid;
    $arrPostData['device_id'] = $this->device_id;
    $arrPostData['username'] = $this->username;
    $arrPostData['password'] = $this->password;

    $strUrl = $this->api_url."/accounts/login/";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$strUrl);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->generateSignature(json_encode($arrPostData)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close ($ch);

    list($header, $body) = explode("\r\n\r\n", $result, 2);

    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
    $cookies = implode(";", $matches[1]);

    $arrResult = json_decode($body, true);

    if($arrResult['status'] == "ok"){
      $uid = $arrResult['logged_in_user']['pk'];

      return array($uid, $cookies);
    }else{
      print $body;
      exit;
    }
  }

  private function UploadPhotoApi($file){
    $arrPostData = array();
    $arrPostData['_uuid'] = $this->upload_id;
    $arrPostData['_csrftoken'] = $this->csrftoken;
    $arrPostData['upload_id'] = $this->upload_id;
    $arrPostData['image_compression'] = '{"lib_name":"jt","lib_version":"1.3.0","quality":"100"}';
    $arrPostData['photo'] = curl_file_create(realpath($file));

    $strUrl = $this->api_url."/upload/photo/";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$strUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
    $result = curl_exec($ch);
    curl_close ($ch);

    $arrResult = json_decode($result, true);

    if($arrResult['status'] == "ok"){
      return true;
    }else{
      print $result;
      exit;
    }
  }

  private function UploadVideoApi($file){
    $arrPostData = array();
    $arrPostData['_uuid'] = $this->upload_id;
    $arrPostData['_csrftoken'] = $this->csrftoken;
    $arrPostData['upload_id'] = $this->upload_id;
    $arrPostData['media_type'] = '2';

    $strUrl = $this->api_url."/upload/video/";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$strUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
    $result = curl_exec($ch);
    curl_close ($ch);

    $arrResult = json_decode($result, true);

    $uploadUrl = $arrResult['video_upload_urls'][3]['url'];
    $job = $arrResult['video_upload_urls'][3]['job'];

    $headers = $this->headers;
    $headers[] = "Session-ID: ".$this->upload_id;
    $headers[] = "job: ".$job;
    $headers[] = "Content-Disposition: attachment; filename=\"video.mp4\"";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$uploadUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents(realpath($file)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
    $result = curl_exec($ch);
    curl_close ($ch);

    if($arrResult['status'] == "ok"){
      return true;
    }else{
      print $result;
      exit;
    }
  }

  private function ConfigPhotoApi($caption){
    $arrPostData = array();
    $arrPostData['media_folder'] = "Instagram";
    $arrPostData['source_type'] = "4";
    $arrPostData['filter_type'] = "0";

    $arrPostData['_csrftoken'] = $this->csrftoken;
    $arrPostData['_uid'] = $this->uid;
    $arrPostData['_uuid'] = $this->upload_id;
    $arrPostData['upload_id'] = $this->upload_id;
    $arrPostData['caption'] = $caption;

    $arrPostData['device']['manufacturer'] = $this->android_manufacturer;
    $arrPostData['device']['model'] = $this->android_model;
    $arrPostData['device']['android_version'] = $this->android_version;
    $arrPostData['device']['android_release'] = $this->android_release;

    $strUrl = $this->api_url."/media/configure/";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$strUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->generateSignature(json_encode($arrPostData)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
    $result = curl_exec($ch);
    curl_close ($ch);

    $arrResult = json_decode($result, true);

    if($arrResult['status'] == "ok"){
      return true;
    }else{
      print $result;
      exit;
    }
  }

  private function ConfigVideoApi($caption){
    $arrPostData = array();
    $arrPostData['source_type'] = "3";
    $arrPostData['filter_type'] = "0";
    $arrPostData['poster_frame_index'] = "0";
    $arrPostData['length'] = "0.00";
    $arrPostData['"length":0'] = '"length":0.00';
    $arrPostData['audio_muted'] = "false";
    $arrPostData['video_result'] = "deprecated";

    $arrPostData['_csrftoken'] = $this->csrftoken;
    $arrPostData['_uid'] = $this->uid;
    $arrPostData['_uuid'] = $this->upload_id;
    $arrPostData['upload_id'] = $this->upload_id;
    $arrPostData['caption'] = $caption;

    $arrPostData['device']['manufacturer'] = $this->android_manufacturer;
    $arrPostData['device']['model'] = $this->android_model;
    $arrPostData['device']['android_version'] = $this->android_version;
    $arrPostData['device']['android_release'] = $this->android_release;

    $strUrl = $this->api_url."/media/configure/?video=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$strUrl);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->generateSignature(json_encode($arrPostData)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
    $result = curl_exec($ch);
    curl_close ($ch);

    $arrResult = json_decode($result, true);

    if($arrResult['status'] == "ok"){
      return true;
    }else{
      print $result;
      exit;
    }
  }

  private function generateUUID(){
      $uuid = sprintf(
          '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
          mt_rand(0, 0xffff),
          mt_rand(0, 0xffff),
          mt_rand(0, 0xffff),
          mt_rand(0, 0x0fff) | 0x4000,
          mt_rand(0, 0x3fff) | 0x8000,
          mt_rand(0, 0xffff),
          mt_rand(0, 0xffff),
          mt_rand(0, 0xffff)
      );

      return $uuid;
  }

  private function generateDeviceId(){
      return 'android-'.substr(md5(time()), 16);
  }

  private function generateSignature($data){
      $hash = hash_hmac('sha256', $data, $this->ig_sig_key);

      return 'ig_sig_key_version='.$this->sig_key_version.'&signed_body='.$hash.'.'.urlencode($data);
  }

  function generateUploadId(){
      return number_format(round(microtime(true) * 1000), 0, '', '');
  }

}

?>
