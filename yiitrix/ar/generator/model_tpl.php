<?php

use \dev\ar\IblockAR;

/**
 * Class {className}
 *
 * @property integer $ID
 * @property string $NAME
 * @property string $CODE
 * @property string $PREVIEW_TEXT
 * @property string $DETAIL_TEXT
 * @property integer $PREVIEW_PICTURE
 * @property integer $DETAIL_PICTURE{propsDoc}{relationsDoc}
 */
class {className} extends IblockAR
{
    public static function iblockId()
    {
        return {iblockId};
    }

    public static function scopes()
    {
        return [];
    }

    public function getRelations()
    {
        return {relations};
    }
}
