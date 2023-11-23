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

namespace WorldlineOP\PrestaShop\Logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class LoggerFactory
 */
class LoggerFactory
{
    /** @var Logger */
    private $logger;

    /** @var Settings */
    private $settings;

    /**
     * Logger constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->logger = new Logger('module');
        $this->settings = $settings;
        $level = $settings->advancedSettings->logsEnabled === true ? Logger::DEBUG : Logger::INFO;
        $fileHandler = new RotatingFileHandler(
            _PS_MODULE_DIR_ . 'worldlineop/' . sprintf('logs/%s.log', Tools::hash(_PS_MODULE_DIR_)),
            3,
            $level
        );
        $fileHandler->setFilenameFormat('{date}_{filename}', 'Ym');
        $this->logger->pushHandler($fileHandler)
            ->pushProcessor(new UidProcessor(7));
    }

    /**
     * @param $channel
     *
     * @return Logger
     */
    public function setChannel($channel)
    {
        return $this->logger->withName($channel);
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
