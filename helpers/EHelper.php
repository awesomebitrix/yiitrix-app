<?
/**
 * Class EHelper
 * Набор общих хелперов
 */
class EHelper {

    protected static $siteData;

    protected static $settings = null;


    /**
     * Проверяет, находимся ли мы на главной странице
     * @return bool
     */
    public static function isMain() {
        return ($GLOBALS['APPLICATION']->GetCurPage(false) == '/');
    }

    /**
     * форматирует цену, разбивает на разряды
     * @param int $price
     * @return string
     */
    public static function price($price, $c=0, $d=' ') {
        return number_format((float)$price, $c, '.', $d);
    }

    /**
     * Получает все настройки сайта
     */
    protected static function getData() {
        if (self::$siteData === null) {
            $cSite   = new CSite();
            $rsSites = CSite::GetByID($cSite->GetDefSite(SITE_ID));
            self::$siteData = $rsSites->Fetch();
        }
        return self::$siteData;
    }


    /**
     * Получает значение параметра сайта по ключу
     * @param string $key
     * @return string
     */
    public static function get($key) {
        $data = self::getData();
        if (isset($data[$key])) {
            return $data[$key];
        }

        switch ($key) {
            case 'HOST':
                return $_SERVER['HTTP_HOST'];
        }

        return null;
    }

    /**
     * JS константы для добавления в шапку
     * @return string
     */
    public static function jsApp() {
       
        $jsApp = array(
            'USER' => array(
                'IS_LOGGED' => $GLOBALS['USER']->IsAuthorized()
            ),
            'C' => array(
                'P_AJAX_LOADER_DEFAULT' => P_IMAGES . 'ajax-loader.gif'
            )
        );

        return '<script type="text/javascript">var APP = ' . json_encode($jsApp) . '; </script>';
    }
    
    /**
     * Возвращает информацию о файле
     * @param int|array $fid ID файла, либо массив ID файлов
     * @return bool|array - данные информация о файле
     */
    public static function getFileData($fid) {
        if (!isset($fid)) return false;

        $cFile = new CFile();
        if (is_array($fid)) {
            $rsFile = $cFile->GetList(array(), array("@ID" => implode(",", $fid)));
        } else {
            $rsFile = $cFile->GetByID($fid);
        }

        $ret = array();

        while ($ifile = $rsFile->Fetch()) {
            if (array_key_exists("~src", $ifile)) {
                if($ifile["~src"]) {
                    $ifile["src"] = $ifile["~src"];
                } else {
                    $ifile["src"] = $cFile->GetFileSRC($ifile, false, false);
                }
            } else {
                $ifile["src"] = $cFile->GetFileSRC($ifile, false);
            }


            if (preg_match('#\.([\w]+)$#', $ifile["src"], $m)) {
                $ifile["ext"] = $m[1];
            }

            $ret[$ifile['ID']] = $ifile;
        }

        if (is_array($fid)) {
            return $ret;
        } else {
            return $ret[$fid];
        }
    }
    
    /**
     * Обернуть письмо в шаблон
     * @param $arFields
     * @param $dbMailResultArray
     */
    public static function wrapMailTemplate(&$arFields, &$dbMailResultArray) {
        if ($dbMailResultArray['BODY_TYPE'] != 'html') return;  // только для писем с типом HTML
        
        $isAlreadyWrapped = strpos(strToUpper($dbMailResultArray['MESSAGE']), '<!DOCTYPE') !== false;
        if ($isAlreadyWrapped) return;  // к письму уже примене шаблон в админке

        $template = file_get_contents(P_DR . P_LAYOUT . 'email-template.php');
        // Скопируем заголовок письма внутрь шаблона
        $template = str_replace('#EMAIL_TITLE#', $dbMailResultArray['SUBJECT'], $template);
        
        // Добавим домен к ссылкам на картинки
        $domain = 'http://' . COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
        $template = str_replace('/local/', $domain . '/local/', $template);

        // Обернем письмо половинками шаблона 
        $template = explode('#CONTENT#', $template);
        $header = $template[0];
        $footer = $template[1];
        $dbMailResultArray['MESSAGE'] = $header . $dbMailResultArray['MESSAGE'] . $footer;
    }

    public static function setting($code)
    {
        if (self::$settings === null) {
            self::$settings = Setting::finder()
                ->cache()
                ->findOne();
        }

        return self::$settings->{'property_'.$code};
    }

    public static function renderIncludedPartial($partial = '')
    {
        $partialFile = P_PARTIALS . SITE_ID . DIRECTORY_SEPARATOR . $partial . '.php';
        $partialPath = P_PARTIALS_PATH . SITE_ID . DIRECTORY_SEPARATOR . $partial . '.php';
        if (!file_exists($partialPath)) {
            $partialFile = P_PARTIALS . $partial . '.php';
            $partialPath = P_PARTIALS_PATH . $partial . '.php';
        }
        if (file_exists($partialPath)) {
            $GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.include", "", Array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => $partialFile,
                    "EDIT_TEMPLATE" => "",
                ),
                false
            );
        }
    }

    public static function wordForm($count, $form1 = "", $form2_4 = "а", $form5_0 = "ов")
    {
        $n100 = $count % 100;
        $n10  = $count % 10;
        if (($n100 > 10) && ($n100 < 21)) {
            return $form5_0;
        } else if ((!$n10) || ($n10 >= 5)) {
            return $form5_0;
        } else if ($n10 == 1) {
            return $form1;
        }
        return $form2_4;
    }

    public static function listData($a, $k, $v = false)
    {
        $result = array();
        foreach ($a as $values) {
            if (is_callable($k)) {
                $result = array_merge($result, call_user_func_array($k, [$values]));
            } elseif (is_array($values)) {
                $result[$values[$k]] = (!$v) ? $values : $values[$v];
            } elseif (is_object($values)) {
                $result[$values->{$k}] = (!$v) ? $values : $values->{$v};
            }
        }
        return $result;
    }
}
