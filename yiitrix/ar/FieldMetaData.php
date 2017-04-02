<?
namespace dev\ar;

/**
 * Class FieldMetaData
 * @package dev\ar
 *
 * @property string $code
 */
class FieldMetaData extends \devil\base\Object
{
	private $_data = array(
		'code' 				=> null,	
		'name' 				=> null,
	); 


	public function __construct(array $attributes)
	{
		foreach ($attributes as $name => $value) {
			$this->$name = $value;
		}		
	}

	public function __set($name, $value)
	{
		if (array_key_exists($name, $this->_data)) {
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