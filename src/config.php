<?php

use craft\helpers\App;

return [
    "url" => App::env("QUEUE_HEARTBEAT_URL"),
    "timeout" => 2,
    "interval" => 30,
    "apiKey" => App::env("QUEUE_HEARTBEAT_API_KEY"),
];
