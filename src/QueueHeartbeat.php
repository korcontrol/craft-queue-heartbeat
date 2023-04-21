<?php

namespace korcontrol\queueheartbeat;

use Craft;
use GuzzleHttp\Exception\GuzzleException;
use craft\base\Plugin;
use craft\helpers\Console;
use craft\queue\Queue;
use yii\base\Event;
use yii\queue\cli\WorkerEvent;

class QueueHeartbeat extends Plugin
{
    public function init()
    {
        parent::init();

        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace =
                "korcontrol\\queueheartbeat\\controllers";
        }

        $config = self::getConfig();

        Event::on(Queue::class, Queue::EVENT_WORKER_LOOP, function (
            WorkerEvent $event,
        ) use ($config) {
            if (
                !(array_key_exists("interval", $config) && $config["interval"])
            ) {
                return;
            }

            Craft::$app->getCache()->getOrSet(
                "QUEUEHEARTBEAT_LAST_HEARTBEAT_AT",
                function () use ($config) {
                    if (!(array_key_exists("url", $config) && $config["url"])) {
                        return;
                    }

                    try {
                        Craft::createGuzzleClient([
                            "timeout" => array_key_exists("timeout", $config)
                                ? $config["timeout"]
                                : 2,
                        ])->get($config["url"]);
                    } catch (GuzzleException $e) {
                        $message =
                            "Queue Heartbeat received an error: " .
                            json_encode($e);
                        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
                            Console::output($message);
                        } else {
                            Craft::warning($message);
                        }
                    }

                    return time();
                },
                $config["interval"],
            );
        });
    }

    public static function getConfig()
    {
        return Craft::$app->getConfig()->getConfigFromFile("queueheartbeat");
    }
}
