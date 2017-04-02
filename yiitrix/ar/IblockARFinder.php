<?
namespace dev\ar;


class IblockARFinder
{
    protected $iblockId;

    /**
     * @var IblockMetaData
     */
    protected $iblockMetaData;

    /**
     * @var FieldMetaData[]
     */
    protected $fieldMetaData;

    /**
     * @var PropertyMetaData[]
     */
    protected $propertyMetaData;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var \CDBResult
     */
    protected $bxDbResult;

    protected $pager = false;

    protected $group = false;

    protected $select = false;

    protected $order = array();

    protected $filter = array();

    protected $cacheLt = null;

    protected $renderedPager = null;

    protected $scopes;


    /**
     * @param $modelClass
     */
    public function __construct($modelClass)
    {
        /**
         * @var $modelClass IblockAR
         */
        $iblockId = $modelClass::iblockId();
        $this->iblockMetaData = IblockMetaDataContainer::getMetaData($iblockId);

        $this->iblockId = $iblockId;
        $this->fieldMetaData = $this->iblockMetaData->getFields();
        $this->propertyMetaData = $this->iblockMetaData->getProperties();
        $this->modelClass = $modelClass;

        $this->scopes = $modelClass::scopes();
    }

    /**
     * @param $model IblockAR
     * @param $rawAttributes array
     */
    public function instantiateModel($model, $rawAttributes)
    {
        //$model->setIsNew(null);

        $attributes = array();
        $attributeDescriptions = array();
        foreach ($this->fieldMetaData as $field) {
            if (array_key_exists($field->code, $rawAttributes)) {
                $attributes[$field->code] = $rawAttributes[$field->code];
            }
        }

        foreach ($this->propertyMetaData as $prop) {
            $codeO = $prop->code;
            $codeU = strtoupper($prop->code);

            $code = array_key_exists('PROPERTY_'.$codeO.'_VALUE', $rawAttributes)
                ? $codeO
                : (array_key_exists('PROPERTY_'.$codeU.'_VALUE', $rawAttributes) ? $codeU : false);

            if ($code) {

                $value = $rawAttributes['PROPERTY_'.$code.'_VALUE'];
                $description = (isset($rawAttributes['PROPERTY_'.$code.'_DESCRIPTION'])) ? $rawAttributes['PROPERTY_'.$code.'_DESCRIPTION'] : null; //TODO

                if ($prop->property_type == PropertyMetaData::TYPE_LIST) {
                    if ($prop->multiple) {
                        $newVal = array();
                        foreach ($value as $id => $val) {
                            $newVal[] = $id;
                        }
                        $value = $newVal;
                    } else {
                        $value = $rawAttributes['PROPERTY_'.$code.'_ENUM_ID'];
                    }
                }

                $attributeDescriptions['property_'.$prop->code] = $description;
                $attributes['property_'.$prop->code] = $value;
            }
        }

        $catalogFields = array();
        foreach ($rawAttributes as $code => $value) {
            if (strpos($code, 'CATALOG_') === 0) {
                $catalogFields[$code] = $value;
            }
        }
        if (count($catalogFields)) {
            $catalogFields['ID'] = $rawAttributes['ID'];
            $catalogProduct = new CatalogProduct();
            $catalogProduct->setAttributesFromIblockGetList($catalogFields);
            $model->setCatalogProductModel($catalogProduct);
        }

        $model->setAttributesRaw($attributes);
        $model->setAttributeDescriptionsRaw($attributeDescriptions);
        $model->setIsNew(false);
    }

    /**
     * @param array $filter
     * @return array
     */
    public function _findRaw(array $filter = array())
    {
        $result = array();

        if ($this->cacheLt !== null) {
            $cache = new \CPHPCache();

            $cacheOrder = $this->order;
            $cacheFilter = $this->_getFilterArray($filter);

            //$get = $_GET;
            ksort($get);
            ksort($cacheOrder);
            ksort($cacheFilter);

            $cacheId = md5(serialize(array(
               /// $get,
                $cacheOrder,
                $cacheFilter,
                count($this->group) == 0 ? false : $this->group,
                $this->pager,
            )));

            if (!$cache->InitCache($this->cacheLt, $cacheId) && $cache->StartDataCache()) {
                $this->bxDbResult = (new \CIBlockElement)->GetList(
                    $cacheOrder,
                    $cacheFilter,
                    $this->group,
                    $this->pager,
                    $this->_getSelectArray());

                if (is_object($this->bxDbResult)) {
                    while ($attributes = $this->bxDbResult->Fetch()) {
                        $result[] = $attributes;
                    }
                } else {
                    $result = $this->bxDbResult;
                    $this->bxDbResult = null;
                }

                $cache->EndDataCache([
                    'attributes' => $result,
                    'renderedPager' => $this->renderPager(),
                ]);
            } else {
                $this->bxDbResult = null;
                $cachedResult = $cache->GetVars();
                $result = $cachedResult['attributes'];
                $this->renderedPager = $cachedResult['renderedPager'];
            }
        } else {
            $this->bxDbResult = (new \CIBlockElement)->GetList(
                $this->order,
                $this->_getFilterArray($filter),
                $this->group,
                $this->pager,
                $this->_getSelectArray());

            if (is_object($this->bxDbResult)) {
                while ($attributes = $this->bxDbResult->Fetch()) {
                    $result[] = $attributes;
                }
            } else {
                $result = $this->bxDbResult;
                $this->bxDbResult = null;
            }
        }
        $this->resetQuery();

        return $result;
    }

    /**
     * @return array
     */
    protected function _getSelectArray()
    {
        $select = [];

        foreach ($this->fieldMetaData as $field) {
            $select[] = $field->code;
        }
        foreach ($this->propertyMetaData as $prop) {
            $select[] = 'PROPERTY_'.$prop->code;
        }

        if ($this->iblockMetaData->getIsCatalog()) {
            foreach (CatalogHelper::getPrices() as $price) {
                $select[] = 'CATALOG_GROUP_' . $price['ID'];
            }
        }

        return $select;
    }

    /**
     * @param array $filter
     * @return array
     */
    protected function _getFilterArray(array $filter = array())
    {
        return array_merge($filter, $this->filter, array('IBLOCK_ID' => $this->iblockId));
    }

    /**
     * @param array $filter
     * @return IblockAR[]
     */
    public function find(array $filter = array())
    {
        $result = array();
        $tmpCache = $this->cacheLt;
        $raw = $this->_findRaw($filter, true);

        foreach ($raw as $attributes) {
            $model = new $this->modelClass();
            $this->instantiateModel($model, $attributes);

            if ($tmpCache !== null) {
                $model->setCached($tmpCache);
            }

            $result[] = $model;
        }

        return $result;
    }

    /**
     * @param array $filter
     * @return IblockAR
     */
    public function findOne(array $filter = array())
    {
        $models = $this->find($filter);
        if (count($models)) {
            return $models[0];
        }
        return false;
    }

    /**
     * @param $id
     * @return IblockAR
     */
    public function findById($id)
    {
        $models = $this->find(array('ID'=>intval($id)));
        if (count($models)) {
            return $models[0];
        }
        return false;
    }

    protected function resetQuery()
    {
        $this->pager = false;
        $this->group = false;
        $this->select = false;
        $this->order = array();
        $this->filter = array();
        $this->cacheLt = null;
    }

    /**
     * @param array $filter
     * @return $this
     */
    public function filter(array $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param array $select
     * @return $this
     */
    public function select(array $select)
    {
        if (!empty($select)) {
            $this->select = $select;
        }
        return $this;
    }

    /**
     * @param array $pager
     * @return $this
     */
    public function pager(array $pager)
    {
        $this->pager = $pager;
        return $this;
    }

    /**
     * @param array $order
     * @return $this
     */
    public function order(array $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param array $group
     * @return $this
     */
    public function group(array $group)
    {
        $this->group = $group;
        return $this;
    }

    public function count()
    {
        return (int) $this->group([])->_findRaw();
    }

    /**
     * @param int $lt
     * @return $this
     */
    public function cache($lt = 3600)
    {
        //$this->cacheLt = $lt;
        return $this;
    }

    public function renderPager()
    {

        if ($this->bxDbResult !== null) {
            return $this->bxDbResult->GetPageNavStringEx($ob, '');
        } elseif ($this->renderedPager !== null) {
            return $this->renderedPager;
        }
    }

    public function bindScope($scope)
    {
        if (isset($scope['pager'])) {
            $this->pager = $scope['pager'];
        }
        if (isset($scope['order'])) {
            $this->order = array_merge($this->order, $scope['order']);
        }
        if (isset($scope['filter'])) {
            $this->filter = array_merge($this->filter, $scope['filter']);
        }
        return $this;
    }

    public function __call($method, $arguments)
    {
        if (isset($this->scopes[$method])) {
            $this->bindScope($this->scopes[$method]);
        } elseif (method_exists($this->modelClass, 'scope'.$method)) {
            $this->bindScope(call_user_func_array(array($this->modelClass, 'scope'.$method), $arguments));
        }
        return $this;
    }
}
