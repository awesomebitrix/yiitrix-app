<?
namespace dev\ar;



class IblockMetaData
{
    protected $fields;

    protected $properties;

    protected $isCatalog;


    public function __construct($iblockId)
    {
        $fields = $properties = [];

        global $DB;
        $dbRes = $DB->Query("SHOW FIELDS FROM `b_iblock_element`");
        while ($field = $dbRes->Fetch()) {
            $attributes = array(
                'code' => $field['Field'],
            );

            $fieldMetaData = new FieldMetaData($attributes);
            $fields[$fieldMetaData->code] = $fieldMetaData;
        }


        $dbRes = \CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $iblockId));
        while ($attributes = $dbRes->Fetch()) {
            $propMetaData = new PropertyMetaData($attributes);
            $properties['property_' . $propMetaData->code] = $propMetaData;
        }

        $this->fields = $fields;
        $this->properties = $properties;
        $this->isCatalog = \CModule::IncludeModule('catalog') && (bool) \CCatalog::GetByID($iblockId);
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return PropertyMetaData[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function getIsCatalog()
    {
        return $this->isCatalog;
    }

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else {
            throw new \Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }
}


class IblockMetaDataContainer
{
    protected static $iblocksMetaData = [];

    /**
     * @param $iblockId
     * @return IblockMetaData
     */
    public static function getMetaData($iblockId)
    {
        $name = 'iblock_' . $iblockId;

        if (!isset(self::$iblocksMetaData[$name])) {
            self::$iblocksMetaData[$name] = new IblockMetaData($iblockId);
        }

        return self::$iblocksMetaData[$name];
    }
}