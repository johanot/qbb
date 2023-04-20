<?php

function exception_error_handler($severity, $message, $file, $line) {
  if (!(error_reporting() & $severity)) {
      // This error code is not included in error_reporting
      return;
  }
  throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

$configFile = is_string(@$_ENV["QBB_CONFIG_FILE"]) ? $_ENV["QBB_CONFIG_FILE"] : "";
if (!is_readable($configFile))
  throw new \Exception("failed to read configFile: $configFile, set env: QBB_CONFIG_FILE");

$config = file_get_contents($configFile);
$config = json_decode($config);
