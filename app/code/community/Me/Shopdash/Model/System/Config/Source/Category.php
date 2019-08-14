<?php
/**
* @category    Me
* @package     Me_Shopdash
* @author      Magevolve Ltd.
 * @copyright   Copyright (c) 2013 ShopDash Inc. (http://shopdashapp.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Me_Shopdash_Model_System_Config_Source_Category
{
    public function toOptionArray($addEmpty = false)
    {
        $options = $this->getTree();

        return $options;
    }

    protected function _prepareMultiselect(Varien_Data_Tree_Node $node, $values, $level = 0)
    {
        $level++;
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');

        $values[$node->getId()]['value'] = $node->getId();
        $values[$node->getId()]['label'] = str_repeat($nonEscapableNbspChar, ($level - 1) * 4) . $node->getName();

        foreach ($node->getChildren() as $child)
        {
            $values = $this->_prepareMultiselect($child, $values, $level);
        }

        return $values;
    }

    protected function getTree()
    {
        $store = Mage::app()->getRequest()->getParam('store');
        if( $store ) $rootId = Mage::app()->getStore($store)->getRootCategoryId();
        else $rootId = 1;

        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();

        $root = $tree->getNodeById($rootId);

        if($root && $root->getId() == 1)
        {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }

        $collection = $tree->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToFilter('is_active', 1);

        $tree->addCollectionData($collection, true);

        return $this->_prepareMultiselect($root, array());
    }
}
