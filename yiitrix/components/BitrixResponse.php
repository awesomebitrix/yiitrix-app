<?php

namespace app\yiitrix\components;

use yii\web\Response;

class BitrixResponse extends Response
{
    protected $_stopBitrix = false;


    protected function sendHeaders() {
        if ($this->stopBitrix()) {
            parent::sendHeaders();
        }
    }

    /**
     * Send the response, including all headers, rendering exceptions if so
     * requested. Terminates the script to control is not returned to the
     * Bitrix.
     *
     * @return void
     */
    public function send() {
        if (!$this->stopBitrix()) {
            ob_start();
        }

        parent::send();

        if (!$this->stopBitrix()) {
            $content = ob_get_clean();
            $GLOBALS['APPLICATION']->AddViewContent('YII_OUTPUT', $content, '');

            return;
        }

        die();
    }


    /**
     * Whether or not to stop Bitrix engine (off by default)
     *
     * If called with no arguments or a null argument, returns the value of the
     * flag; otherwise, sets it and returns the current value.
     *
     * @param boolean $flag Optional
     * @return boolean
     */
    public function stopBitrix($flag = null) {
        if (null !== $flag) {
            $this->_stopBitrix = $flag ? true : false;
        }

        if (!$this->_stopBitrix && ($this->getIsRedirection() || $this->format == Response::FORMAT_JSON)) {
            return true;
        }

        return $this->_stopBitrix;
    }

    /**
     * Fire ERROR_404 flag for Bitrix engine.
     *
     * @return void
     */
    public function setBitrix404() {
        // Set ERROR_404 to "Y"
        if (!defined('ERROR_404')) {
            define('ERROR_404', 'Y');
        }
    }
}