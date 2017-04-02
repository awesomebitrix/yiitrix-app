<?php

namespace app\yiitrix\components;

use yii\web\ErrorHandler;

class BitrixErrorHandler extends ErrorHandler
{
    public function handleHttpException($exception)
    {
        http_response_code($exception->statusCode);

        \Yii::$app->response->content = \Yii::$app->runAction($this->errorAction);
    }

    protected function renderException($exception)
    {
        if ($exception instanceof \yii\web\HttpException) {

        } else {

            echo  $this->renderFile($this->exceptionView, [
                'exception' => $exception,
            ]);
            \Yii::$app->end();
        }
    }
}