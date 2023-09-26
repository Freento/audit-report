<?php

declare(strict_types=1);

namespace Freento\AuditReport\Block\Adminhtml\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Psr\Log\LoggerInterface;

/**
 * @method getReport(): string
 */
class View extends Template
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(Context $context, LoggerInterface $logger)
    {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Render submodule's report block
     *
     * @return string
     */
    public function getReportHtml(): string
    {
        try {
            $html = $this->getLayout()->createBlock($this->getReport())->toHtml();
        } catch (\Exception $e) {
            $html = (string) __('<div class="report-data">Can\'t load report data: %1</div>', $e->getMessage());
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return $html;
    }
}
