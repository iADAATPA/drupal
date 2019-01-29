<?php

/**
 * @file
 * API functions for mt_hub module.
 *
 * @author Colin Harper
 */

/**
 * Translate a segment.
 *
 * Translate a segment using the MT-Hub Consumer API
 *
 * @param string $src
 * @param string $trg
 * @param array $segments
 *
 * @return mixed
 */
function mt_hub_translate($src, $trg, $segments) {
  $segments = array_map('linebreakPlaceholder', $segments);

  $apiToken = variable_get('mt_hub_token', 'xxxxxxxxxx ');
  $method = 'translate';
  $httpMethod = 'POST';
  $data = [
    'token' => $apiToken,
    'source' => $src,
    'target' => $trg,
    'segments' => $segments,
  ];

  $data = drupal_http_build_query($data);
  $options = [
    'method' => $httpMethod,
    'data' => $data,
    'timeout' => 180.0,
    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
  ];

  $baseUrl = 'https://app.mt-hub.eu/api/';

  $url = $baseUrl . $method;
  $response = drupal_http_request($url, $options);

  $data = json_decode($response->data);

  if ($data->success) {
    $segments = $data->data->segments;
    $targets = [];
    foreach ($segments as $key => $segment) {
      $targets[$key] = placeholderLinebreak($segment->translation);
    }

    return $targets;
  }
  else {
    return FALSE;
  }
}

/**
 * Replace the line breaks with a placeholder and escape them.
 *
 * @param $segment
 *
 * @return string
 */
function linebreakPlaceholder($segment) {
  return urlencode(preg_replace("/(\r\n)/", '<linebreak/>', $segment));
}

/**
 * Decode the line break placeholders and replace them
 * with the original line breaks.
 *
 * @param $segment
 *
 * @return mixed
 */
function placeholderLinebreak($segment) {
  return preg_replace("/<linebreak\/>/", "\r\n", urldecode($segment));
}
