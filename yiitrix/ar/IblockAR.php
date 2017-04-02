<?
namespace dev\ar;

use \devil\base\Model;

\CModule::IncludeModule('iblock');
\CModule::IncludeModule('catalog');





/**
 * Class IblockAR
 * @package dev\ar
 *
 * @property CatalogProduct $catalog
 */
class IblockAR extends Model
{
    /**
     * Relation type
     */
    const HAS = 1;

    /**
     * Relation type
     */
    const BELONGS = 2;

    /**
     * @var bool
     */
	protected $isNew;

    /**
     * @var array
     */
    protected $_attributes;

    /**
     * @var array
     */
    protected $_attributeDescriptions;

    /**
     * @var PropertyMetaData[]
     */
	protected $propertyMetaData = array();

    /**
     * @var FieldMetaData[]
     */
	protected $fieldMetaData = null;

    /**
     * @var IblockARFinder
     */
    protected static $finder = array();

    /**
     * @var array
     */
    protected $relations = array();

    /**
     * @var array
     */
    protected $bxErrors = array();

    /**
     * @var CatalogProduct
     */
    protected $catalogProductModel = null;

    /**
     * @var array
     */
    protected $fileAttributesChangeMap = array();

    protected $cached = null;


	public static function iblockId()
    {
        return null;
    }

	public function __construct()
	{
        $metaData = IblockMetaDataContainer::getMetaData(static::iblockId());

        $this->fieldMetaData = $metaData->getFields();
        $this->propertyMetaData = $metaData->getProperties();

		foreach ($this->propertyMetaData as $code => $propMetaData) {
			$this->_attributes[$code] = null;
            if ($propMetaData->property_type == PropertyMetaData::TYPE_FILE) {
                $this->fileAttributesChangeMap[$code] = false;
            }
            $this->_attributeDescriptions[$code] = null;
		}		
		foreach ($this->fieldMetaData as $code => $v) {
			$this->_attributes[$code] = null;
		}

        $this->fileAttributesChangeMap['PREVIEW_PICTURE'] = false;
        $this->fileAttributesChangeMap['DETAIL_PICTURE'] = false;

		$this->isNew = true;
	}

    /**
     * @return IblockARFinder
     */
    public static function finder()
    {
        if (!isset(self::$finder[static::iblockId()])) {
            self::$finder[static::iblockId()] = new IblockARFinder(get_called_class());
        }
        return self::$finder[static::iblockId()];
    }

    /**
     * @return array
     */
    public static function scopes()
    {
        return array();
    }

	public function attributes()
	{
		return array_keys($this->_attributes);
	}

    protected function reInstantiate()
    {
        $raw = $this->finder()->_findRaw(array('ID'=>$this->ID));
        $this->finder()->instantiateModel($this, $raw[0]);
    }

	public function save()
	{
		$insertFields = array();
        $insertProperties = array();

        $this->beforeSave();

        foreach ($this->_attributes as $code => $value) {
            if (isset($this->propertyMetaData[$code])) {
                if (isset($this->fileAttributesChangeMap[$code]) && !$this->fileAttributesChangeMap[$code]) {
                    $value = null;
                }
                $insertProperties[$this->propertyMetaData[$code]->code] = $value;
            } elseif (isset($this->fieldMetaData[$code])) {
                if (isset($this->fileAttributesChangeMap[$code]) && !$this->fileAttributesChangeMap[$code]) {
                    $value = null;
                }
                if ($code == 'IBLOCK_SECTION_ID' && is_array($value)) {
                    $insertFields['IBLOCK_SECTION'] = $value;
                } else {
                    $insertFields[$this->fieldMetaData[$code]->code] = $value;
                }
            }
        }
        $insertFields['IBLOCK_ID'] = static::iblockId();
        $insertFields['PROPERTY_VALUES'] = $insertProperties;

		$ibElement = (new \CIBlockElement);

		$result = ($this->isNew)
            ? $ibElement->Add($insertFields)
            : $ibElement->Update($this->ID, $insertFields);

		if ($result && $this->isNew) {
			$this->ID = $result;
			$this->isNew = false;
		}

		if ($result) {
			$this->reInstantiate();
            $this->afterSave();
		} else {
			$this->bxErrors = $ibElement->LAST_ERROR;
		}

		return (bool) $result;
	}

	public function delete()
	{
		if (!$this->isNew) {
			return \CIBlockElement::Delete($this->ID);
		}
		return false;
	}

    public function beforeSave()
    {

    }

    public function afterSave()
    {

    }

    public function getErrors($attribute = null)
    {
        return array_merge(['bxErrors'=>$this->bxErrors], parent::getErrors($attribute));
    }

    /**
     * @return CatalogProduct
     * @throws \Exception
     */
    public function getCatalog()
    {
        if ($this->catalogProductModel !== null) {
            return $this->catalogProductModel;
        }
        throw new \Exception('Not a catalog iblock');
    }

    // TODO:
    public function getPropertyListValue($name)
    {
        $result = null;
        $attr = 'property_'.$name;
        $md = $this->getPropertyMetaData($attr);
        if ($md && $md->property_type == PropertyMetaData::TYPE_LIST) {
            $value = $this->$attr;

            if (is_array($value)) {
                foreach ($value as $v) {
                    $result[] = $md->variants[$v];
                }
            } else {
                $result = $md->variants[$value];
            }
        }
        return $result;
    }
    
    /**
     * @return array
     */
    public function getRelations()
    {
        return array();
    }

    public function getRelated($name)
    {
        /**
         * @var $class IblockAR
         */
        $relation = $this->getRelations()[$name];
        if (!isset($this->relations[$name])) {
            $type = $relation[0];
            $class = $relation[1];
            $scope = isset($relation[3]) && is_array($relation[3]) ? $relation[3] : [];

            if ($type == self::HAS) {
                $property = 'property_' . $relation[2];
                $metaData = $this->getPropertyMetaData($property);
                $method = ($metaData->multiple) ? 'find' : 'findOne';


                if ($this->{$property}) {
                    if ($this->cached) {
                        $this->relations[$name] = $class::finder()->cache($this->cached)
                            ->bindScope($scope)
                            ->{$method}(['ID' => $this->{$property}]);
                    } else {
                        $this->relations[$name] = $class::finder()
                            ->bindScope($scope)
                            ->{$method}(['ID' => $this->{$property}]);
                    }
                }

            } else if ($type == self::BELONGS) {
                $property = 'PROPERTY_' . $relation[2];
                if ($this->cached) {
                    $this->relations[$name] = $class::finder()->cache($this->cached)
                        ->bindScope($scope)
                        ->find(array($property => $this->ID));
                } else {
                    $this->relations[$name] = $class::finder()
                        ->bindScope($scope)
                        ->find(array($property => $this->ID));
                }
            }
        }

        return $this->relations[$name];
    }

    public function getPropertyMetaData($name)
    {
        if (isset($this->propertyMetaData[$name])) {
            return $this->propertyMetaData[$name];
        }
        return null;
    }

    public function getImageAttributePath($name)
    {
        return \CFile::GetPath($this->{$name});
    }

    public function setCatalogProductModel(CatalogProduct $catalogProductModel)
    {
        $this->catalogProductModel = $catalogProductModel;
    }

	public function setAttributeDescriptionsRaw(array $attributeDescriptions = array())
    {
        foreach ($attributeDescriptions as $name => $value) {
            if (array_key_exists($name, $this->_attributeDescriptions)) {
                $this->_attributeDescriptions[$name] = $value;
            }
        }
    }

	public function setAttributesRaw(array $attributes = array())
	{
		foreach ($attributes as $name => $value) {
            if (array_key_exists($name, $this->_attributes)) {
                $this->_attributes[$name] = $value;
            }
		}
	}

    /**
     * @param array $attributes
     * TODO: wtf? set throw setter!!!
     */
    public function setAttributes(array $attributes = array())
    {
        $allowed = array(); // todo
        foreach ($attributes as $name => $value) {
            $this->{$name} = $value;
        }
    }

    public function setIsNew($value = true)
    {
        $this->isNew = $value;
    }

    public function setCached($cacheLifeTime)
    {
        $this->cached  = $cacheLifeTime;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \devil\base\UnknownPropertyException
     *
     * TODO: name conflict
     */
	public function __get($name)
	{
		if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];

        } else if (strpos($name, '_get_description') !== false) {
            return $this->_attributeDescriptions[substr($name, 0, strlen($name)-16)];

        } else if (preg_match('#(?:_(\d+))?_path$#', $name, $matches)) {

            return isset($matches[1])
                ? \CFile::GetPath($this->_attributes[substr($name, 0, strlen($name)- strlen($matches[0]))][$matches[1]])
                : \CFile::GetPath($this->_attributes[substr($name, 0, strlen($name)- strlen($matches[0]))]);

        } else if (preg_match('#(?:_(\d+))?_rsz(\d+)x(\d+)(_crop)?$#', $name, $matches)) {

            $method = (isset($matches[4]) && $matches[4] == '_crop') ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL;

            return strlen($matches[1]) > 0
                ? \CFile::ResizeImageGet($this->_attributes[substr($name, 0, strlen($name)-strlen($matches[0]))][$matches[1]], ['width'=>$matches[2], 'height'=> $matches[3]], $method)['src']
                : \CFile::ResizeImageGet($this->_attributes[substr($name, 0, strlen($name)-strlen($matches[0]))], ['width'=>$matches[2], 'height'=> $matches[3]], $method)['src'];

        } else {
            $relations = $this->getRelations();
            if (count($relations)) {
                if (isset($relations[$name])) {
                    return $this->getRelated($name);
                }
            }
        }
		return parent::__get($name);
	}

	public function __set($name, $value)
	{
        if (array_key_exists($name, $this->_attributes)) {
            // if attribute is property
            if (strpos($name, 'property_') == 0) {
                $metaData = $this->getPropertyMetaData($name);

                // all list-prop values save as array
                if ($metaData->property_type == PropertyMetaData::TYPE_LIST) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    $newVal = false;
                    foreach ($value as $val) {
                        if (isset($metaData->variants[$val])) {
                            $newVal[] = $val;
                        }
                    }
                    if ($newVal) {
                        $value = $newVal;
                    }
                }
            }

			$this->_attributes[$name] = $value;

            if (isset($this->fileAttributesChangeMap[$name])) {
                $this->fileAttributesChangeMap[$name] = true;
            }
		} else {
            parent::__set($name, $value);
        }
	}
}
?>