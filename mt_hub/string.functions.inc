<?php

/**
 * Convert string to lowercase with underscore instead of spaces.
 *
 * @param $str
 *
 * @return mixed|string
 */
function strtolowerUnderscore($str) {
  $str = strtolower($str);
  $str = preg_replace('/[ ]/', '_', $str);

  return $str;
}

/**
 * Segmentation.
 *
 * Break a long string in to segments.
 *
 * Handles nested arrays of segments also.
 *
 * @param $paragraph
 *
 * @return array|false|string[]
 */
function segmentation($paragraph) {
  if (is_array($paragraph)) {
    $result = [];
    foreach ($paragraph as $string) {
      $result[] = segmentation($string);
    }
  }
  else {
    $paragraph = wrapLinebreakWithSpaces($paragraph);

    $result = preg_split("/(?<=[\.\?\!;:\r\n])\h+/", $paragraph, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
  }

  return $result;
}

/**
 * Join segments together into one string.
 *
 * Handles nested arrays of segments also.
 *
 * @param $segments
 *
 * @return array|mixed|string
 */
function segmentJoining($segments) {
  if (is_array($segments[0])) {
    $joinedSegments = [];
    foreach ($segments as $strings) {
      $joinedSegments[] = segmentJoining($strings);
    }
  }
  else {
    $joinedSegments = join(' ', $segments);
    $joinedSegments = removeSpacesAroundLinebreak($joinedSegments);
  }

  return $joinedSegments;
}

/**
 * Translate Segments.
 *
 * Translates segments using mt_hub_translate()
 *
 * Handles nested arrays of segments also.
 *
 * @param $src
 * @param $trg
 * @param $segments
 *
 * @return array/string/bool
 */
function translateSegments($src, $trg, $segments) {
  $targets = [];
  // If the segments are in a nested array
  if (is_array($segments[0])) {
    foreach ($segments as $sources) {
      $targets[] = translateSegments($src, $trg, $sources);
    }
  }
  else {
    module_load_include('inc', 'mt_hub', 'mt_hub.api');
    $segmentsChunks = array_chunk($segments, 10);
    foreach ($segmentsChunks as $chunk) {
      drupal_set_time_limit(100);
      $translatedChunk = mt_hub_translate($src, $trg, $chunk);
      // If there is an error in the translating, return the error
      if (empty($translatedChunk)) {
        $targets = $translatedChunk;
        break;
      }
      $targets = array_merge($targets, $translatedChunk);
    }
  }

  return $targets;
}

/**
 * Generate the filename for exported files.
 *
 * @param $node
 * @param $langcode
 *
 * @return string
 */
function generateFilename($node, $langcode) {
  $siteName = variable_get('site_name_programmatic');
  $title = strtolowerUnderscore($node->title);
  $title = substr($title, 0, 20);

  $filename = $siteName . '_';
  $filename .= $title . '_';
  $filename .= $node->language . '_';
  $filename .= $langcode;

  return $filename;
}

/**
 * Wrap line breaks in spaces so they are segmented correctly.
 *
 * @param $content
 *
 * @return mixed
 */
function wrapLinebreakWithSpaces($content) {
  $pattern = '/\r\n/';
  $replace = " \r\n ";
  $content = preg_replace($pattern, $replace, $content);

  return $content;
}

/**
 * Remove extra spaces around line breaks which were added for segmentation.
 *
 * @param $content
 *
 * @return mixed
 */
function removeSpacesAroundLinebreak($content) {
  $pattern = '/ \r\n/ ';
  $replace = "\r\n";
  $content = preg_replace($pattern, $replace, $content);

  return $content;
}

/**
 * Pseudo Translate function for dev purposes.
 *
 * @param $segment
 *
 * @return mixed
 */
function pseudoTranslate($segment) {
  $segment = str_replace('a', 'à', $segment);
  $segment = str_replace('e', 'é', $segment);
  $segment = str_replace('i', 'í', $segment);
  $segment = str_replace('o', 'ö', $segment);
  $segment = str_replace('u', 'ú', $segment);

  return $segment;
}