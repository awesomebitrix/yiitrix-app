<?php

namespace app\yiitrix\components;

use \yii\web\Controller;
use yii\web\Response;

class BitrixController extends Controller
{
    public $layout = false;

    public $bodyClass = '';


    /**
     * @param array $section
     */
    public function setSectionSeo($section)
    {
        global $APPLICATION;
        $APPLICATION->SetTitle($section['NAME']);

        $seo = (new \Bitrix\Iblock\InheritedProperty\SectionValues($section['IBLOCK_ID'], $section['ID']))->getValues();

        if ($seo['SECTION_META_TITLE']) {
            $APPLICATION->SetTitle($seo['SECTION_META_TITLE']);
            $APPLICATION->SetPageProperty('title', $seo['SECTION_META_TITLE']);
        }
        if ($seo['SECTION_META_KEYWORDS']) {
            $APPLICATION->SetPageProperty('keywords', $seo['SECTION_META_KEYWORDS']);
        }
        if ($seo['SECTION_META_DESCRIPTION']) {
            $APPLICATION->SetPageProperty('description', $seo['SECTION_META_DESCRIPTION']);
        }
        if ($seo['SECTION_PAGE_TITLE']) {
            $APPLICATION->SetPageProperty('page_title', $seo['SECTION_PAGE_TITLE']);
        }
    }

    /**
     * @param \dev\ar\IblockAR $model
     */
    public function setDetailSeo($model)
    {
        global $APPLICATION;
        $APPLICATION->SetTitle($model->NAME);

        $seo = (new \Bitrix\Iblock\InheritedProperty\ElementValues($model->IBLOCK_ID, $model->ID))->getValues();

        if ($seo['ELEMENT_META_TITLE']) {
            $APPLICATION->SetTitle($seo['ELEMENT_META_TITLE']);
            $APPLICATION->SetPageProperty('title', $seo['ELEMENT_META_TITLE']);
        }
        if ($seo['ELEMENT_META_KEYWORDS']) {
            $APPLICATION->SetPageProperty('keywords', $seo['ELEMENT_META_KEYWORDS']);
        }
        if ($seo['ELEMENT_META_DESCRIPTION']) {
            $APPLICATION->SetPageProperty('description', $seo['ELEMENT_META_DESCRIPTION']);
        }
        if ($seo['ELEMENT_PAGE_TITLE']) {
            $APPLICATION->SetPageProperty('page_title', $seo['ELEMENT_PAGE_TITLE']);
        }
    }

    public function setSeoByPage($code)
    {
        $page = Page::finder()->findOne(['CODE'=>$code]);
        if ($page) {
            $this->setDetailSeo($page);
        }
    }

    public function json($data)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }
}