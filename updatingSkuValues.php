<?php

use Pimcore\Model\Object\Product;
use Pimcore\Model\Object\Data\ObjectMetadata;
use Pimcore\Model\Object\ProductCategory;
use BuildDirect\Tool;
use Pimcore\Model\User;

require_once (dirname(__FILE__) . "/pimcore/cli/startup.php");
//error_reporting(E_Warning);
set_time_limit(0);
memory_get_usage();
error_reporting(E_Warning);
<<<<<<< HEAD
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
=======
$inputFileName = "C:\\Users\\Saikrrishna\\Desktop\\CreatingNewProducts.xlsx";
>>>>>>> ca9a7eb99f6fce3212cf74e9e699f6fc3096bb11
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
                $newParent = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                if (intval($headSKU)) {
                    echo $headSKU . "</br>";

                    echo "New Parent: " . $newParent . '</br>';
                    $newParentId = Product::getById($newParent);
                    $skuNumber = Product::getBySkuNumnber($headSKU);
<<<<<<< HEAD
                    if (!Tool::isEmpty($skuNumber) && intval($skuNumber)) {
                        $dp = new Product();
                        $parent = $skuNumber->getParent();
                        $parentId = $skuNumber->getParentId();
                        echo "\n<br/>Old Parent Name: " . $parent . "\n<br/>";
                        echo "\n<br/>Old Parent Id: " . $parentId . "\n<br/>";
//                            die(0);
                        $skuNumber->getProductTags()->delete($skuNumber);
                        $skuNumber->getCategoryData()->delete($skuNumber);
                        $skuNumber->setBrand($dp->getBrand());
                        $skuNumber->setSupplier($dp->getSupplier());
                        $skuNumber->setPrimaryCategory($dp->getPrimaryCategory());
                        $skuNumber->setParentId($newParent);
                        $skuNumber->setParent($newParentId);
                        Pimcore\Model\Object\AbstractObject::setGetInheritedValues(true);
                        $currentCategoryHref = $skuNumber->getPrimaryCategory();
                        $currentCategoryObj = $currentCategoryHref[0]->getObject();
                        $categoryId = $currentCategoryObj->getId();
                        $category = ProductCategory::getById($categoryId);
                        $objectBrickKey = $category->getProperty("objectBrick");
                        $categoryDataBrickClass = "Object\Objectbrick\Data" . "\\" . ucfirst($objectBrickKey);
//                        Pimcore\Model\Object\AbstractObject::setGetInheritedValues(false);
                        if (class_exists($categoryDataBrickClass)) {
                            $categoryDataObj = new $categoryDataBrickClass($skuNumber);
                        }
                        $categoryDataObjFieldDef = $categoryDataObj->getDefinition()->getFieldDefinitions();
                        $cat = $skuNumber->getCategoryData();
//                    $birck = $cat->$brickName(); //                        }
                        for ($columns = 2; $columns < $highestColumn; $columns++) {
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
=======
//                    $dp = new Product();
                    $parent = $skuNumber->getParent();
                    $parentId = $skuNumber->getParentId();
                    echo "\n<br/>Old Parent Name: " . $parent . "\n<br/>";
                    echo "\n<br/>Old Parent Id: " . $parentId . "\n<br/>";
//                            die(0);
                    $skuNumber->getProductTags()->delete($skuNumber);
                    $skuNumber->getCategoryData()->delete($skuNumber);
                    $skuNumber->setBrand(null);
                    $skuNumber->setSupplier(null);
                    $skuNumber->setPrimaryCategory(null);
                    $skuNumber->setParentId($newParent);
                    $skuNumber->setParent($newParentId);
                    foreach ($columnIndexKeyMap as $column => $headKey) {
                        if ($column == 0 || $column == 1) {
//                            echo "SAve call";
//                            $skuNumber->save();
//                            echo "saved";
                            $currentCategoryHref = $skuNumber->getPrimaryCategory();
                            $currentCategoryObj = $currentCategoryHref[0]->getObject();
                            $categoryId = $currentCategoryObj->getId();
//                            var_dump($categoryId);
//                            die(0);
                            $category = ProductCategory::getById($categoryId);
                            $objectBrickKey = $category->getProperty("objectBrick");
                            $categoryDataBrickClass = "Object\Objectbrick\Data" . "\\" . ucfirst($objectBrickKey);

                            if (class_exists($categoryDataBrickClass)) {
                                $categoryDataObj = new $categoryDataBrickClass(new Product());
                            }
                            $categoryDataObjFieldDef = $categoryDataObj->getDefinition()->getFieldDefinitions();
                            $brickName = "get" . ucfirst($objectBrickKey);

                            $cat = $skuNumber->getCategoryData()->$brickName(); //                        }
                            for ($columns = 2; $columns < $highestColumn; $columns++) {
                                $attr = $columnIndexKeyMap[$columns];
                                $fieldDef = $categoryDataObjFieldDef[$attr];
                                $setter = "set" . ucfirst($attr);
                                echo $setter . "</br>";
                                if (method_exists($cat, $setter)) {
                                    $values = $worksheet->getCellByColumnAndRow($columns, $row)->getValue();
                                    if ($fieldDef->getFieldType() == 'multiselect') {
                                        $cat->$setter(explode("|", $values));
                                    } else {
                                        $cat->$setter($values);
                                    }
                                    echo ($values) . "\n</br>";
                                }
                            }
                            $skuNumber->save();
                            echo "saved" . $headSKU . "\n<BR>";
>>>>>>> ca9a7eb99f6fce3212cf74e9e699f6fc3096bb11
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
