<?php

namespace BuildDirectService;

use BuildDirect\Tool;
use Pimcore\Model\Object\Product;
use Pimcore\Model\Object\ProductCategory;
use Pimcore\Model\Object\Data\ObjectMetadata;
use Pimcore\Model\Object\Objectbrick\Data\CeilingFansTags;
use Pimcore\Model\Object\Product\ProductTags;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Supplier;
use Pimcore\Model\Object\ProductBrand;
use Pimcore\Model\Object\Objectbrick;
use Pimcore\Model\User;

?>
<?php

require_once (dirname(__FILE__) . "/pimcore/cli/startup.php");
error_reporting(E_Warning);
$start_row = 1; // define start row
$i = 1;
if ($argv != null && !Tool::isEmpty($argv)) {
    $userId = $argv[1];
} else {
    echo "\nPass User ID as Argument";
    exit(0);
}
$handle = fopen(PIMCORE_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'utils' . DIRECTORY_SEPARATOR . 'skuPLines.csv', "r");
$emailAddresses = [];
$user = User::getById($userId);
$emailAddresses[] = $user->getEmail();
$userName = $user->getUserName();
$plids = array();
while (($row = fgetcsv($handle)) !== FALSE) {
    if ($i >= $start_row) {
        //$objectId = $row[0]; // 0 is the column index of 1st columnindex
        $collectionName = $row[0];
        $supplier = $row[1];
        $brand = $row[2];
        $productType = $row[3];
        $tagSetter = $row[4];
        try {

            $product = new Product();
            $product->setParentId(533957);
            $product->setName($collectionName);
            $product->setWebname($collectionName);
            $product->setType(AbstractObject::OBJECT_TYPE_OBJECT);
            $supplierObject = Supplier::getById($supplier);
            $product->setKey(\Pimcore\File::getValidFilename($product->getName()));
            $supplier = new ObjectMetadata("supplier", array(), $product);
            $supplier->setObject($supplierObject);
            $product->setSupplier(array(
                $supplier));
            $brandObject = ProductBrand::getById($brand);
            $brandMeta = new ObjectMetadata("brand", array(), $product);
            $brandMeta->setObject($brandObject);
            $product->setBrand(array(
                $brandMeta));
            $product->setProductType($productType);
//			$product->setLTLCode('85.00');
//			$product->setnmfcCode('037600');
            $product->setOrderType('Pallet');
            $productTagObj = new Product\ProductTags($product);
            $tagObj = new Objectbrick\Data\FloorMoldingsTags($product);
            $tagObj->setProductTags(explode("|", $tagSetter));
            $tagObj->setImageTagFlag('tagStyle');
            $productTagObj->setFloorMoldingsTags($tagObj);
            $product->setProductTags($productTagObj);
            $product->save();
            $plids[$product->getName()] = $product->getId();
            echo "saved" . "</br>";
            echo $product->getId() . "</br>";
        } catch (Exception $e) {
            echo "error:" . $e->getTraceAsString();
        } catch (\Error $e) {
            echo "error:" . $e->getTraceAsString();
        }
    }
    $i++;
}
var_dump($plids);
fclose($handle);
$file = "ProductLine";
//$filepath = pathinfo($filename);
$date1 = time();
$filename = PIMCORE_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . "import-export" . DIRECTORY_SEPARATOR . "utility" . DIRECTORY_SEPARATOR . $userName . "_" . $date1 . "_" . $file . ".csv";

$fp = fopen($filename, 'w');
foreach ($plids as $key => $value) {
    fputcsv($fp, [$key, $value]);
}
fclose($fp);
echo $filename;
//Tool::sendEmail($emailAddresses, "Product Line Creation", $fileName, $filename);
Tool::sendEmail(array(
    $emailAddresses), "Product Line Creation", "Hello  " . ucfirst($userName) . " , <br/> You have an  'Export' oF 'Product Line Creation'. <br/><br/>File Path in System : " . $filename, $filename);
?>
