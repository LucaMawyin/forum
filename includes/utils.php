<?php
function redirect($url)
{
  header("Location: $url");
  exit;
}

function clean_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function format_date($timestamp)
{
  $date = new DateTime($timestamp);
  return $date->format('F j, Y \a\t g:i a');
}

function get_relative_time($timestamp)
{
  $time_ago = strtotime($timestamp);
  $current_time = time();
  $seconds = $current_time - $time_ago;

  $minute = 60;
  $hour = 60 * $minute;
  $day = 24 * $hour;
  $week = 7 * $day;
  $month = 30 * $day;
  $year = 365 * $day;

  if ($seconds < $minute) {
    return $seconds == 1 ? "1 second ago" : "$seconds seconds ago";
  } else if ($seconds < $hour) {
    $minutes = floor($seconds / $minute);
    return $minutes == 1 ? "1 minute ago" : "$minutes minutes ago";
  } else if ($seconds < $day) {
    $hours = floor($seconds / $hour);
    return $hours == 1 ? "1 hour ago" : "$hours hours ago";
  } else if ($seconds < $week) {
    $days = floor($seconds / $day);
    return $days == 1 ? "1 day ago" : "$days days ago";
  } else if ($seconds < $month) {
    $weeks = floor($seconds / $week);
    return $weeks == 1 ? "1 week ago" : "$weeks weeks ago";
  } else if ($seconds < $year) {
    $months = floor($seconds / $month);
    return $months == 1 ? "1 month ago" : "$months months ago";
  } else {
    $years = floor($seconds / $year);
    return $years == 1 ? "1 year ago" : "$years years ago";
  }
}

function linkify($text) {
  return preg_replace(
    '/(https?:\/\/[^\s<]+)/i',
    '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
    $text
  );
}

function return_response($success, $message) {
  return array("success" => $success, "message" => $message);
}

function format_title_content($content) {
  $content = html_entity_decode($content, 0, 'UTF-8');
  return $content;
}

function format_post_content($content) {
  $content = html_entity_decode($content, 0, 'UTF-8');

  // Bold
  $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
  $content = preg_replace('/__(.*?)__/s', '<strong>$1</strong>', $content);

  // Italic
  $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
  $content = preg_replace('/_(.*?)_/s', '<em>$1</em>', $content);

  // Links
  $content = preg_replace('/\[(.*?)\]\((.*?)\)/s', '<a href="$2">$1</a>', $content);

  // Headings
  $content = preg_replace('/^(#)(.*?)$/m', '<h1>$2</h1>', $content);
  $content = preg_replace('/^(##)(.*?)$/m', '<h2>$2</h2>', $content);
  $content = preg_replace('/^(###)(.*?)$/m', '<h3>$2</h3>', $content);
  $content = preg_replace('/^(####)(.*?)$/m', '<h4>$2</h4>', $content);
  $content = preg_replace('/^(#####)(.*?)$/m', '<h5>$2</h5>', $content);
  $content = preg_replace('/^(######)(.*?)$/m', '<h6>$2</h6>', $content);

  // Lists (unordered)
  $content = preg_replace('/^\s*[-*]\s+(.*?)$/m', '<ul><li>$1</li></ul>', $content);

  // Lists (ordered)
  $content = preg_replace('/^\s*\d+\.\s+(.*?)$/m', '<ol><li>$1</li></ol>', $content);

  // Code (single-line)
  $content = preg_replace('/\`(.*?)\`/s', '<code>$1</code>', $content);

  // Newlines to paragraph and line break
  $content = '<p>' . str_replace("\n\n", '</p><p>', $content) . '</p>';
  $content = str_replace("\n", '<br>', $content);

  $content = linkify($content);

  return $content;
}