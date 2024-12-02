<?php

use DG\BypassFinals;
use Test\Integration\Database;

require_once __DIR__ . '/../vendor/autoload.php';

BypassFinals::enable();

$database = new Database();
$database->start();
