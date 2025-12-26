<?php

$DATA_FOLDER = "../assets/data";

function getCRED()
{
  global $DATA_FOLDER;
  $filePath = $DATA_FOLDER . "/creds.jtv";
  if (file_exists($filePath)) {
    return file_get_contents($filePath);
  }
  return null;
}

function encrypt_data($data, $key) { return $data; }
function decrypt_data($e_data, $key) { return $e_data; }

function send_jio_otp($mobile)
{
  $url = "https://t-sneh-proxy.vercel.app/userservice/apis/v1/loginotp/send";
  
  $headers = array(
    'appname: RJIL_JioTV',
    'os: android',
    'devicetype: phone',
    'content-type: application/json'
  );
  
  $payload = array(
    'number' => base64_encode('+91' . $mobile)
  );

  $process = curl_init($url);
  curl_setopt($process, CURLOPT_POST, 1);
  curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
  
  $return = curl_exec($process);
  $info = curl_getinfo($process);
  curl_close($process);

  if ($info['http_code'] == 204) {
    return array('status' => 'success', 'message' => 'OTP Sent');
  } else {
    return array('status' => 'error', 'message' => 'Failed');
  }
}

function verify_jio_otp($mobile, $otp)
{
  global $DATA_FOLDER;
  $url = "https://t-sneh-proxy.vercel.app/userservice/apis/v1/loginotp/verify";
  
  $headers = array(
    'appname: RJIL_JioTV',
    'os: android',
    'devicetype: phone',
    'content-type: application/json'
  );

  $payload = array(
    'number' => base64_encode('+91' . $mobile),
    'otp' => $otp,
    'deviceInfo' => array(
      'consumptionDeviceName' => 'RMX1945',
      'info' => array(
        'type' => 'android',
        'platform' => array('name' => 'RMX1945'),
        'androidId' => '1234567890abcdef'
      )
    )
  );

  $process = curl_init($url);
  curl_setopt($process, CURLOPT_POST, 1);
  curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
  
  $return = curl_exec($process);
  curl_close($process);

  $data = json_decode($return, true);

  if (isset($data['ssoToken']) && !empty($data['ssoToken'])) {
    file_put_contents($DATA_FOLDER . "/creds.jtv", $return);
    return array('status' => 'success', 'message' => 'Success');
  } else {
    return array('status' => 'error', 'message' => 'Failed');
  }
}






