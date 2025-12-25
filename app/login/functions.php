<?php

// * Copyright 2021-2025 SnehTV, Inc.
// * Edited By : Gemini for Aritra (Direct Data Access)

$DATA_FOLDER = "../assets/data";

/**
 * Reads credentials directly from the file without decryption.
 */
function getCRED()
{
  global $DATA_FOLDER;
  $filePath = $DATA_FOLDER . "/creds.jtv";
  if (file_exists($filePath)) {
    return file_get_contents($filePath);
  }
  return null;
}

/**
 * Encryption bypassed: Returns raw data.
 */
function encrypt_data($data, $key)
{
  return $data; 
}

/**
 * Decryption bypassed: Returns raw data.
 */
function decrypt_data($e_data, $key)
{
  return $e_data;
}

/**
 * Sends OTP to the provided mobile number via Jio API.
 */
function send_jio_otp($mobile)
{
  $j_otp_api = 'https://jiotvapi.media.jio.com/userservice/apis/v1/loginotp/send';
  $j_otp_headers = array(
    'appname: RJIL_JioTV', 
    'os: android', 
    'devicetype: phone', 
    'content-type: application/json', 
    'user-agent: okhttp/3.14.9'
  );
  
  $j_otp_payload = array('number' => base64_encode('+91' . $mobile));
  
  $process = curl_init($j_otp_api);
  curl_setopt($process, CURLOPT_POST, 1);
  curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($j_otp_payload));
  curl_setopt($process, CURLOPT_HTTPHEADER, $j_otp_headers);
  curl_setopt($process, CURLOPT_TIMEOUT, 10);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
  
  $j_otp_resp = curl_exec($process);
  $j_otp_info = curl_getinfo($process);
  curl_close($process);
  
  $j_otp_data = @json_decode($j_otp_resp, true);
  
  $resp = ['status' => 'error', 'user' => $mobile, 'message' => ''];
  
  if ($j_otp_info['http_code'] == 204) {
    $resp['status'] = "success";
    $resp['message'] = "OTP Sent Successfully";
  } else {
    $resp['message'] = $j_otp_data['message'] ?? "Unknown Error: " . $j_otp_info['http_code'];
  }
  return $resp;
}

/**
 * Verifies OTP and saves the raw JSON response to creds.jtv.
 */
function verify_jio_otp($mobile, $otp)
{
  global $DATA_FOLDER;
  $j_otp_api = 'https://jiotvapi.media.jio.com/userservice/apis/v1/loginotp/verify';
  $j_otp_headers = [
    'appname: RJIL_JioTV',
    'os: android',
    'devicetype: phone',
    'content-type: application/json',
    'user-agent: okhttp/3.14.9'
  ];

  $j_otp_payload = [
    'number' => base64_encode('+91' . $mobile),
    'otp' => $otp,
    'deviceInfo' => [
      'consumptionDeviceName' => 'RMX1945',
      'info' => [
        'type' => 'android',
        'platform' => ['name' => 'RMX1945'],
        'androidId' => substr(sha1(time() . rand(00, 99)), 0, 16)
      ]
    ]
  ];

  $process = curl_init($j_otp_api);
  curl_setopt($process, CURLOPT_POST, 1);
  curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($j_otp_payload));
  curl_setopt($process, CURLOPT_HTTPHEADER, $j_otp_headers);
  curl_setopt($process, CURLOPT_TIMEOUT, 10);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  
  $j_otp_resp = curl_exec($process);
  curl_close($process);

  $j_otp_data = @json_decode($j_otp_resp, true);
  $resp = ['status' => 'error', 'user' => $mobile, 'message' => ''];

  if (isset($j_otp_data['ssoToken']) && !empty($j_otp_data['ssoToken'])) {
    // Saves raw JSON directly to the file
    if (file_put_contents($DATA_FOLDER . "/creds.jtv", json_encode($j_otp_data))) {
      $resp['status'] = 'success';
      $resp['message'] = 'Login Successful';
    } else {
      $resp['message'] = 'Failed to write credentials file';
    }
  } else {
    $resp['message'] = $j_otp_data['message'] ?? 'Verification Failed';
  }
  return $resp;
}


