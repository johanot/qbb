<?php

require_once 'common.php';

$inputPeers = $config->peers;
$files = $config->files; 

$peers = [];
foreach ($inputPeers as $n=>$p) {
  $u = parse_url($p);
  if (is_array($u) && isset($u["host"])) {
    $peers[$n] = $u;
  }
}

foreach ($files as $n=>$f) {
  $parts = [];

  foreach ($peers as $pn=>$p) {
    if ($pn == $config->me) {
      echo "fetching local file: $f->source ($n)\n";
      $local = file_get_contents($f->source);
      if (is_string($local)) {
        $parts[] = $local;
      }
      continue;
    }

    $scheme = empty($p["scheme"]) ? "http" : $p["scheme"];
    if (!empty($p["port"])) {
      $port = $p["port"];
    } else {
      if ($scheme == "https") {
        $port = 443;
      } else {
        $port = 80;
      }
    }
    $connString = $scheme. "://". $p["host"]. ":". $port. "/". $n;
    echo "requesting: $connString\n";
    $remote = file_get_contents($connString);
    if (is_string($remote)) {
      $parts[] = $remote;
    }
  }

  echo "spooling out bundle at: $f->destination\n";
  $bundle = implode("\n", $parts);
  file_put_contents($f->destination, $bundle);
}
