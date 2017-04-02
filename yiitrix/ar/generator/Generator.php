<?
namespace dev\ar;

class Generator
{
    public static function init()
    {
        global $APPLICATION;

        AddEventHandler("iblock", "OnAfterIBlockAdd", Array("\dev\ar\Generator", "onAfterIBlockAdd"));
        AddEventHandler("iblock", "OnAfterIBlockUpdate", Array("\dev\ar\Generator", "onAfterIBlockUpdate"));

        AddEventHandler("main", "onProlog", Array("\dev\ar\Generator", "onProlog"));

        if ($APPLICATION->GetCurPage() == '/bitrix/admin/iblock_edit.php') {

            if (isset($_REQUEST['ID'])) {


                $file = __DIR__ . DIRECTORY_SEPARATOR . 'config.ini';
                $param = 'iblock_' . $_REQUEST['ID'];

                $lines = explode("\n", file_get_contents($file));
                foreach ($lines as $line) {
                    list($ib, $name) = explode(':', $line);
                    if ($ib == $param) {
                        break;
                    }
                    $name = false;
                }


                $script = "
                    <script>
                        window.arIblockClassName = '$name';
                    </script>
                ";

                $APPLICATION->AddHeadString($script);
            }

            $APPLICATION->AddHeadString('<script>'.file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'ar.generator.js').'</script>');

        }
    }

    public static function onProlog()
    {

        if (isset($_SESSION['_arGenerator'])) {
            $iblockId = $_SESSION['_arGenerator']['iblockId'];
            $className = $_SESSION['_arGenerator']['className'];
            $classFile = P_MODELS . $className . '.php';

            if (true) {
                $metaData = IblockMetaDataContainer::getMetaData($iblockId);

                /**
                 * @var PropertyMetaData $prop
                 */
                $propsDoc = array();
                foreach ($metaData->getProperties() as $prop) {
                    $type = 'integer';
                    if ($prop->multiple) {
                        $type = 'array';
                    } else if ($prop->property_type == PropertyMetaData::TYPE_STRING) {
                        $type = 'string';
                    }
                    $propsDoc[] = ' * @property ' . $type . ' $property_' . $prop->code;
                }

                if (count($propsDoc)) {
                    $propsDoc = "\r\n * \r\n" . join("\r\n", $propsDoc) . "\r\n *";
                } else {
                    $propsDoc = '';
                }

                $data = array(
                    '{className}' => $className,
                    '{iblockId}' => $iblockId,
                    '{relations}' => '[]',
                    '{relationsDoc}' => '',
                    '{propsDoc}' => $propsDoc,
                );

                $tpl = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'model_tpl.php');
                $tpl = str_replace(array_keys($data), $data, $tpl);
                file_put_contents($classFile, $tpl);


                $confFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.ini';
                $param = 'iblock_' . $iblockId;

                $lines = explode("\n", file_get_contents($confFile));
                $lines[] = $param.":".$className;
                file_put_contents($confFile, trim(join("\n", $lines)));

                unset($_SESSION['_arGenerator']);
            }
        }
    }

    public static function onAfterIBlockAdd(& $fields)
    {
        if ($fields['ID'] > 0 && isset($_REQUEST['ar_iblock_class_name']) && strlen($_REQUEST['ar_iblock_class_name'])>0) {
            $_SESSION['_arGenerator'] = array(
                'iblockId' => $fields['ID'],
                'className' => $_REQUEST['ar_iblock_class_name'],
            );
        }
    }

    public static function onAfterIBlockUpdate(& $fields)
    {
        if ($fields['ID'] > 0 && isset($_REQUEST['ar_iblock_rewrite_model'])  && isset($_REQUEST['ar_iblock_class_name']) && strlen($_REQUEST['ar_iblock_class_name'])>0) {
            $_SESSION['_arGenerator'] = array(
                'iblockId' => $fields['ID'],
                'className' => $_REQUEST['ar_iblock_class_name'],
            );
        }
    }
}


