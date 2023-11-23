<?php
/**
 * 2021 Worldline Online Payments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop partner
 * @copyright 2021 Worldline Online Payments
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class WorldlineopRejectedModuleFrontController
 */
class WorldlineopRejectedModuleFrontController extends ModuleFrontController
{
    /** @var Worldlineop */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->auth = true;
    }

    /**
     * @return array
     */
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['robots'] = 'noindex';

        return $page;
    }

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        $reOrderLink = $this->context->link->getPageLink(
            'order', null, null, ['id_order' => Tools::getValue('id_order'), 'submitReorder' => '']
        );
        $this->context->smarty->assign([
            'reorder_link' => $reOrderLink,
        ]);

        $this->setTemplate('module:worldlineop/views/templates/front/rejected.tpl');
    }
}
