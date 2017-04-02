<?


use app\yiitrix\components\BitrixApplication;

class Yiitrix {

    public static function init() {
        AddEventHandler("main", "OnBeforeProlog", array("Yiitrix", "bootstrap"));
    }


    public static function bootstrap() {
        global $APPLICATION, $DB;


        //Main\Application::getInstance()
        /*if (defined('ADMIN_SECTION') && ADMIN_SECTION) {
            $APPLICATION->SetAdditionalCSS(P_CSS . 'admin/admin-small.css');
            $APPLICATION->AddHeadString("<script src='".P_JS."jquery-1.10.1.js'>\x3C/script>");

            if (in_array(5, explode(',', $GLOBALS['USER']->GetGroups()))) {
                $APPLICATION->AddHeadScript(P_JS . 'admin.js');
            }
        }*/

        //require P_LIBRARY . '/IblockAR/init.php';
        if (defined('ADMIN_SECTION') && ADMIN_SECTION) {


        } else {

        }

        define('YII_DEBUG', true);
        define('YII_ENV', 'dev');

        require(__DIR__ . '/../vendor/autoload.php');
        require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
        require(__DIR__ . '/components/BitrixApplication.php');

        $config = require(__DIR__ . '/../config/web.php');

        $config['components']['db'] = [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . $DB->DBHost . ';dbname=' . $DB->DBName,
            'username' => $DB->DBLogin,
            'password' => $DB->DBPassword,
            'charset' => 'utf8',
        ];

        (new BitrixApplication($config))->run();
    }
}


define("P_APP",       "/local/");
define("P_ASSETS",       "/assets/");
define("P_CSS",       P_ASSETS . "css/");
define("P_JS",        P_ASSETS . "js/");
define("P_IMAGES",    P_ASSETS . "images/");
define("P_PICTURES",  P_ASSETS . "upload/");


define("P_LAYOUT",    P_APP . "layout/");

define("P_AJAX",      P_APP . "ajax/");
define("P_UPLOAD",    "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/");

define("P_DR",        $_SERVER["DOCUMENT_ROOT"]);
define("P_APP_PATH",  P_DR . P_APP);
define("P_UPLOAD_PATH",     P_DR . P_UPLOAD);

define("P_BUNDLE_PATH",     P_APP_PATH . 'bundle/');
define("P_BUNDLE",          P_APP . 'bundle/');

define("P_INCLUDES",  P_APP_PATH . "includes/");
define("P_LIBRARY",   P_APP_PATH . "libs/");
define("P_CLASSES",   P_LIBRARY . "classes/");
define("P_MODELS",   P_LIBRARY . "models/");

define("P_LOG_DIR",   P_APP_PATH . "logs/");
define("P_LOG_FILE",  P_LOG_DIR . "app.log");

define("P_PARTIALS",  P_APP . "yii-app/views/partials/");
define("P_PARTIALS_PATH",  P_APP_PATH . "yii-app/views/partials/");

define('CACHE_LT', 3600*24*30);

