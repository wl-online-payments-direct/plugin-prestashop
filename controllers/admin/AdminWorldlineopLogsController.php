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
 * Class AdminWorldlineLogsController
 */
class AdminWorldlineopLogsController extends ModuleAdminController
{
    /** @var Worldlineop */
    public $module;

    public function processDownloadLogFile()
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->module->getService('worldlineop.logger');
        $handlers = $logger->getHandlers();
        foreach ($handlers as $handler) {
            if ($handler instanceof \Monolog\Handler\RotatingFileHandler) {
                $file = $handler->getUrl();
                if (realpath($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    readfile($file);
                    exit;
                }
            }
        }
        // @formatter:off
        $this->errors[] = $this->module->l('Log file not found. Make sure logs are enabled', 'AdminWorldlineopLogsController');
        // @formatter:on
    }
}
