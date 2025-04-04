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