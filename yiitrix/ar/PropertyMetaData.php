<?
namespace dev\ar;

/**
 * Class PropertyMetaData
 * @package dev\ar
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $sort
 * @property string $property_type
 * @property string $list_type
 * @property bool $multiple
 * @property array $variants
 */
class PropertyMetaData extends \devil\base\Object
{
	const TYPE_STRING 			= 'S';
	const TYPE_NUMBER 			= 'N';
	const TYPE_LIST 			= 'L';
	const TYPE_LINK 			= 'E';
	const TYPE_FILE 			= 'F';
	
	const LIST_TYPE_SELECT 		= 'L';
	const LIST_TYPE_CHECKBOX 	= 'C';

	private $_data = array(
		'id' 				=> null,
		'code' 				=> null,	
		'name' 				=> null,
        'sort'              => null,
		'property_type' 	=> null,
		'list_type' 		=> null,
		'multiple' 			=> null,
		'variants' 			=> null,
	); 


	public function __construct(array $attributes)
	{
		foreach ($attributes as $name => $value) {
			$attrName = strtolower($name);
			$this->$attrName = $value;
		}

		if ($this->id && $this->property_type == self::TYPE_LIST) {
            $variantValues = array();
			$dbPropVariant = \CIBlockProperty::GetPropertyEnum($this->id, array('VALUE' => 'ASC'));
			while ($propVariant = $dbPropVariant->Fetch()) { 
				$variantValues[$propVariant['ID']] = $propVariant['VALUE']; 
			}
			$this->_data['variants'] = $variantValues;
		}		
	}

    protected function setMultiple($value)
    {
        $value = ($value != 'N') ? true : false;
        $this->_data['multiple'] = $value;
    }

    public function setVariants($variants)
    {
        $this->_data['variants'] = $variants;
    }

	public function __set($name, $value)
	{
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else if (array_key_exists($name, $this->_data)) {
			$this->_data[$name] = $value;
		}
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
		} else {
            return parent::__get($name);
		}		
	}
}
?>