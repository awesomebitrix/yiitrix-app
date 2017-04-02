<?php

namespace app\controllers;


use app\yiitrix\components\BitrixController;


class SiteController extends BitrixController
{
    public function actionError()
    {
        //$this->layout = '404';
        return $this->render('error');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}
