<?php
namespace dev\ar;

use \devil\base\Model;


/**
 * Class CatalogProduct
 * @package dev\ar
 *
 * @property integer QUANTITY
 * @property string QUANTITY_TRACE
 * @property string QUANTITY_TRACE_ORIG
 * @property string CAN_BUY_ZERO
 * @property string NEGATIVE_AMOUNT_TRACE
 * @property string SUBSCRIBE
 * @property string AVAILABLE
 * @property integer WEIGHT
 * @property integer WIDTH
 * @property integer LENGTH
 * @property integer HEIGHT
 * @property integer MEASURE
 * @property integer VAT
 * @property string VAT_INCLUDED
 * @property string PRICE_TYPE
 * @property string RECUR_SCHEME_TYPE
 * @property integer RECUR_SCHEME_LENGTH
 * @property integer TRIAL_PRICE_ID
 * @property string WITHOUT_ORDER
 * @property string SELECT_BEST_PRICE
 * @property integer PURCHASING_PRICE
 * @property string PURCHASING_CURRENCY
 * @property integer TYPE
 * @property float DISCOUNT_PRICE
 * @property float DISCOUNT_PERCENT
 */
class CatalogProduct extends Model
{
    /**
     * @var array
     */
    protected $_attributes;

    /**
     * @var array
     */
    protected $prices;



    public function attributes(){
        return array_keys($this->_attributes);
    }

    public function setAttributesFromIblockGetList($attributes)
    {
        foreach ($attributes as $code => $value) {
            if (preg_match('#^CATALOG_(.+?)_(\d+)$#', $code, $matches)) {
                $this->prices[$matches[2]][$matches[1]] = $value;
            } else if (preg_match('#^CATALOG_(.+)#', $code, $matches)) {
                $this->_attributes[$matches[1]] = $value;
            }
        }

        if (!$this->prices || !$this->_attributes) {
            throw new \Exception('Bad attributes for model');
        }

        //TODO
        $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
            $attributes['ID'],
            $GLOBALS['USER']->GetUserGroupArray(),
            "N",
            1,
            SITE_ID
        );
        $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
            $this->price(),
            'RUB',
            $arDiscounts
        );

        $this->_attributes['DISCOUNT_PRICE'] = (float) $discountPrice;
        $this->_attributes['DISCOUNT_PERCENT'] = (float) (($this->price() - $discountPrice) / ($this->price() / 100));
    }

    public function price($id = false)
    {
        if ($id == false) {
            $first = reset($this->prices);
            $id = $first['GROUP_ID'];
        }
        return $this->prices[$id]['PRICE'];
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        return parent::__get($name);
    }
}