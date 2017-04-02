<?php

namespace app\yiitrix\components;

use yii\web\Application;
use yii\web\NotFoundHttpException;

class BitrixApplication extends Application
{
    public function handleRequest($request)
    {
        try {
            return parent::handleRequest($request);
        } catch (NotFoundHttpException $e) {
            \Yii::$app->errorHandler->handleHttpException($e);
            return \Yii::$app->response;
        }

    }
}