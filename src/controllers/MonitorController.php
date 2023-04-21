<?php

namespace korcontrol\queueheartbeat\controllers;

use Craft;
use korcontrol\queueheartbeat\QueueHeartbeat;
use yii\base\Controller;
use yii\web\ForbiddenHttpException;

class MonitorController extends Controller
{
    public function actionIndex()
    {
        if (
            !array_key_exists("apiKey", QueueHeartbeat::getConfig()) ||
            QueueHeartbeat::getConfig()["apiKey"] !==
                Craft::$app->getRequest()->getQueryParam("apiKey")
        ) {
            throw new ForbiddenHttpException();
        }

        return [
            "delayed" => Craft::$app->getQueue()->totalDelayed,
            "failed" => Craft::$app->getQueue()->totalFailed,
            "reserved" => Craft::$app->getQueue()->totalReserved,
            "waiting" => Craft::$app->getQueue()->totalWaiting,
            "total" => Craft::$app->getQueue()->totalJobs,
        ];
    }
}
