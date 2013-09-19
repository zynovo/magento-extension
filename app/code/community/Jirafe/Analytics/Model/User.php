<?php

/**
 * User Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_User extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('jirafe/user');
    }

}