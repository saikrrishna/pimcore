<?php

use Pimcore\Model\Object\Product;
use Pimcore\Model\Object\Data\ObjectMetadata;
use Pimcore\Model\Object\ProductCategory;
use BuildDirect\Tool;
use Pimcore\Model\User;
use Pimcore\Model\Object\Objectbrick\Definition;
use Pimcore\Model\Object\Objectbrick;

require_once (dirname(__FILE__) . "/../pimcore/cli/startup.php");
//error_reporting(E_Warning);
set_time_limit(0);
memory_get_usage();
error_reporting(E_Warning);
if ($argv != null && !Tool::isEmpty($argv)) {
    $userId = $argv[1];
} else {
    echo "\nPass User ID as Argument";
    exit(0);
}
$inputFileName = "C:\\Users\\Saikrrishna\\Desktop\\CreatingNewProducts1111.xlsx";
\Zend_Session::$_unitTestEnabled = true;
$user = User::getById($userId);
$userName = $user->getUserName();
$phpExcelObject = PHPExcel_IOFactory::load($inputFileName);
$worksheets = $phpExcelObject->getAllSheets();
$columnIndexKeyMap = array();
$skuIds = array();
foreach ($worksheets as $worksheet) {
    $sheetname = $worksheet->getTitle();
    if ($sheetname == 'Update') {
        $highestRow = $worksheet->getHighestRow();
        var_dump($highestRow);
//        die(0);
        $highestColumn = $worksheet->getHighestColumn();
        $headerRow = 1;
        $dataArray[$sheetname] = array();
        $highestColumn = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn($headerRow));
        for ($column = 0; $column < $highestColumn; $column++) {
            $columnIndexKeyMap[$column] = $worksheet->getCellByColumnAndRow($column, $headerRow)->getValue();
        }
        $skuNumberStart = null;
        $i = 2;

        for ($row = $i; $row <= $highestRow; $row++) {
            $rowData = array();
            try {
                echo "\n<br/><hr/>Processing Row $row :   \n<br/>";
                $headSKU = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                if (intval($headSKU)) {
                    echo $headSKU . "</br>";
                    $skuNumber = Product::getBySkuNumnber($headSKU);
                    if (!Tool::isEmpty($skuNumber) && intval($skuNumber)) {
                        $currentCategoryHref = $skuNumber->getPrimaryCategory();
                        $currentCategoryObj = $currentCategoryHref[0]->getObject();
                        $categoryId = $currentCategoryObj->getId();
                        $category = ProductCategory::getById($categoryId);
                        $objectBrickKey = $category->getProperty("objectBrick");
                        $categoryDataBrickClass = "Object\Objectbrick\Data" . "\\" . ucfirst($objectBrickKey);
                        var_dump($categoryDataBrickClass);
//                        die(0);
//                        Pimcore\Model\Object\AbstractObject::setGetInheritedValues(false);
                        if (class_exists($categoryDataBrickClass)) {
                            $categoryDataObj = new $categoryDataBrickClass($skuNumber);
                        }
                        $categoryDataObjFieldDef = $categoryDataObj->getDefinition()->getFieldDefinitions();
                        $cat = $skuNumber->getCategoryData();
//                    $birck = $cat->$brickName(); //                        }
                        for ($columns = 1; $columns < $highestColumn; $columns++) {
                            $attr = $columnIndexKeyMap[$columns];
                            $fieldDef = $categoryDataObjFieldDef[$attr];
                            $setter = "set" . ucfirst($attr);
                            echo $setter . "</br>";
                            if (method_exists($categoryDataObj, $setter)) {
                                $values = $worksheet->getCellByColumnAndRow($columns, $row)->getValue();
                                if ($fieldDef->getFieldType() == 'multiselect') {
                                    $categoryDataObj->$setter(explode("|", $values));
                                } else {
                                    $categoryDataObj->$setter($values);
                                }
                                echo ($values) . "\n</br>";
                            }
                        }
                        $setter = "set" . ucfirst($objectBrickKey);
                        $cat->$setter($categoryDataObj);
                        $skuNumber->setCategoryData($cat);
                        if ($user != null) {
                            $skuNumber->setUserModification($user->getId());
                        }
                        Pimcore\Model\Object\AbstractObject::setGetInheritedValues(true);
                        $skuNumber->setNmfcCode(NULL);
                        $skuNumber->save();
                        $skuIds[$headSKU] = "Saved";
                        echo "saved" . $headSKU . "\n<BR>";
                    } else {
                        $skuIds[$headSKU] = "Sku not found";
                    }
                }
            } catch (\Exception $e) {
                echo "Exception - " . $headSKU . "-" . $e->getMessage() . "\n<br/>";
                $skuIds[$headSKU] = $e->getMessage();
            } catch (\Error $e) {
                echo "Error-" . $headSKU . "\n<br/>";
                $skuIds[$headSKU] = $e->getMessage();
            }
        }
    }
}
var_dump($skuIds);
$file = "Skus";
// $filepath = pathinfo($filename);
$date1 = time();
$filename = PIMCORE_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . "import-export" . DIRECTORY_SEPARATOR . "utility" . DIRECTORY_SEPARATOR . $userName . "_" . $date1 . "_" . $file . ".csv";

$fp = fopen($filename, 'w');
foreach ($skuIds as $key => $value) {
    fputcsv($fp, [
        $key,
        $value]);
}
fclose($fp);
echo $filename;
// Tool::sendEmail($emailAddresses, "Updated Sku Values", $fileName, $filename);
Tool::sendEmail(array(
    $emailAddresses), "Parent Change Of Skus", "Hello  " . ucfirst($userName) . " , <br/> You have an  'Parent Change Of Skus'. <br/><br/>File Path in System : " . $filename);
?>