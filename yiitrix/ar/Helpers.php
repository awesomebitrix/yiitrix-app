<?
namespace dev\ar;

class CatalogHelper
{
    protected static $prices = null;


    public static function getPrices()
    {
        if (self::$prices === null) {
            $dbRes = \CCatalogGroup::GetList();
            while ($price = $dbRes->Fetch()) {
                self::$prices[] = $price;
            }
        }

        return self::$prices;
    }
}