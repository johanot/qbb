<?php

require_once 'common.php';

$files = $config->files; 
$uri = trim($_SERVER["REQUEST_URI"], '/');

if (isset($files->{$uri}) && is_readable($files->{$uri}->source)) {
  header("Content-Type: text/plain");
  readfile($files->{$uri}->source);
} else {
  http_response_code(404);
  echo "not found";
}
