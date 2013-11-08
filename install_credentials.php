 <?php 
require_once 'app/Mage.php';
umask(0);

$app = Mage::app("default");

Mage::getSingleton('core/session', array('name'=>'adminhtml'));
$session = Mage::getSingleton('admin/session');
$session->start();

if ($session->isLoggedIn()) {
        echo Mage::getModel('jirafe_analytics/install')->createCredentials();
} else {
   echo "You must be logged in as an administrator to run this script.";
}
