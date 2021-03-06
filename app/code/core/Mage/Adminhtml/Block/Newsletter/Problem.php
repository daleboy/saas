<?php
/**
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 Jongjian Ltd.Co.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter problem block template.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Mio Core Team <developer@ioe9.com>
 */
class Mage_Adminhtml_Block_Newsletter_Problem extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('newsletter/problem/list.phtml');
        $collection = Mage::getResourceSingleton('newsletter/problem_collection')
            ->addSubscriberInfo()
            ->addQueueInfo();

    }

    protected function _prepareLayout()
    {
        $this->setChild('grid',
            $this->getLayout()->createBlock('adminhtml/newsletter_problem_grid','newsletter.problem.grid')
        );

        $this->setChild('deleteButton',
            $this->getLayout()->createBlock('adminhtml/widget_button','del.button')
                ->setData(
                    array(
                        'label' => Mage::helper('newsletter')->__('Delete Selected Problems'),
                        'onclick' => 'problemController.deleteSelected();'
                    )
                )
        );

        $this->setChild('unsubscribeButton',
            $this->getLayout()->createBlock('adminhtml/widget_button','unsubscribe.button')
                ->setData(
                    array(
                        'label' => Mage::helper('newsletter')->__('Unsubscribe Selected'),
                        'onclick' => 'problemController.unsubscribe();'
                    )
                )
        );
        return parent::_prepareLayout();
    }

    public function getUnsubscribeButtonHtml()
    {
        return $this->getChildHtml('unsubscribeButton');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('deleteButton');
    }

    public function getShowButtons()
    {
        return  Mage::getResourceSingleton('newsletter/problem_collection')->getSize() > 0;
    }

}// Class Mage_Adminhtml_Block_Newsletter_Problem END
