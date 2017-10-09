<?php

use Pimcore\Model\Object\Product;
use Pimcore\Model\Object\Data\ObjectMetadata;
use Pimcore\Model\Object\ProductCategory;

require_once (dirname(__FILE__) . "/pimcore/cli/startup.php");
//error_reporting(E_Warning);
set_time_limit(0);
memory_get_usage();
error_reporting(E_Warning);
$inputFileName = "C:\\Users\\Saikrrishna\\Desktop\\CreatingNewProducts1111.xlsx";
$phpExcelObject = PHPExcel_IOFactory::load($inputFileName);
$worksheets = $phpExcelObject->getAllSheets();
$columnIndexKeyMap = array();
foreach ($worksheets as $worksheet) {
    $sheetname = $worksheet->getTitle();
    if ($sheetname == 'Update') {
        $highestRow = $worksheet->getHighestRow();
        //var_dump($highestRow);
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
                    echo "Column: " . $newParent . '</br>';
                    $newParentId = Product::getById($newParent);
                    $skuNumber = Product::getBySkuNumnber($headSKU);
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
//                    foreach ($columnIndexKeyMap as $column => $headKey) {
//                        if ($column == 0) {
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
                        $categoryDataObj = new $categoryDataBrickClass($skuNumber);
                    }
                    $categoryDataObjFieldDef = $categoryDataObj->getDefinition()->getFieldDefinitions();
//                    $brickName = "get" . ucfirst($objectBrickKey);

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
                    }
                    $setter = "set" . ucfirst($objectBrickKey);
                    $cat->$setter($categoryDataObj);
                    $skuNumber->setCategoryData($cat);
                    $skuNumber->save();
                    echo "saved" . $headSKU . "\n<BR>";
//                        }
//                    }
                }
            } catch (\Exception $e) {
                echo "Exception - " . $headSKU . "-" . $e->getMessage() . "\n<br/>";
            } catch (\Error $e) {
                echo "Error-" . $headSKU . "\n<br/>";
            }
        }
    }
}