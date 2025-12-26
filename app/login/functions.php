<?php

// * Edited By : Gemini for Aritra (Using Cloudflare Worker Proxy)

$DATA_FOLDER = "../assets/data";
// Your Cloudflare Worker URL
$PROXY_GATEWAY = "https://billowing-union-5a63.banasrisamanta463.workers.dev";

function getCRED()
{
  global $DATA_FOLDER;
  $filePath = $DATA_FOLDER . "/creds.jtv";
  if (file_exists($filePath)) {
    return file_get_contents($filePath);
  }
  return null;
}

// Bypassing encryption for easy manual updates
function encrypt_data($data, $key) { return $data; }
function decrypt_data($e_data, $key) { return $e_data; }

/**
 * Main Proxy Request Function
 */
function proxy_request($path, $method = 'GET', $headers = [], $payload = null)
{
  global $PROXY_GATEWAY;
  $url = $PROXY_GATEWAY . $path; // Route via Cloudflare
  
  $process = curl_init($url);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($process, CURLOPT_TIMEOUT, 20);
  curl_setopt($process, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
  
  if ($payload) {
    curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($payload));
  }

  $response = curl_exec($process);
  $info = curl_getinfo($process);
  curl_close($process);
  
  return ['body' => $response, 'http_code' => $info['http_code']];
}

function send_jio_otp($mobile)
{
  $path = '/userservice/apis/v1/loginotp/send';
  $headers = ['appname: RJIL_JioTV', 'os: android', 'devicetype: phone', 'content-type: application/json'];
  $payload = ['number' => base64_encode('+91' . $mobile)];
  
  $res = proxy_request($path, 'POST', $headers, $payload);
  
  if ($res['http_code'] == 204) {
    return ['status' => 'success', 'message' => 'OTP Sent via Proxy'];
  }
  return ['status' => 'error', 'message' => 'Proxy Error: ' . $res['http_code']];
}

function verify_jio_otp($mobile, $otp)
{
  global $DATA_FOLDER;
  $path = '/userservice/apis/v1/loginotp/verify';
  $headers = ['appname: RJIL_JioTV', 'os: android', 'devicetype: phone', 'content-type: application/json'];
  
  $payload = [
    'number' => base64_encode('+91' . $mobile),
    'otp' => $otp,
    'deviceInfo' => ['consumptionDeviceName' => 'RMX1945', 'info' => ['type' => 'android', 'platform' => ['name' => 'RMX1945'], 'androidId' => '1234567890abcdef']]
  ];

  $res = proxy_request($path, 'POST', $headers, $payload);
  $data = json_decode($res['body'], true);

  if (isset($data['ssoToken']) && !empty($data['ssoToken'])) {
    file_put_contents($DATA_FOLDER . "/creds.jtv", $res['body']);
    return ['status' => 'success', 'message' => 'Login Successful!'];
  }
  return ['status' => 'error', 'message' => 'Verification Failed via Proxy'];
}




