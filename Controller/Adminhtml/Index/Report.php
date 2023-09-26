<?php

namespace Freento\AuditReport\Controller\Adminhtml\Index;

use Freento\AuditReport\Block\Adminhtml\Tab\View;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutFactory;

/**
 * controller for AJAX request to get rendered report data
 */
class Report extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Freento_AuditReport::report';

    /**
     * @var LayoutFactory
     */
    private LayoutFactory $layoutFactory;

    /**
     * @var RawFactory
     */
    private RawFactory $resultRawFactory;

    /**
     * @var DataObject
     */
    private DataObject $reports;

    /**
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param RawFactory $resultRawFactory
     * @param DataObject $reports
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        RawFactory $resultRawFactory,
        DataObject $reports
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->reports = $reports;
    }

    /**
     * AJAX request for report
     *
     * @return Raw
     * @throws \Exception
     */
    public function execute(): Raw
    {
        $layout = $this->layoutFactory->create();
        $reportId = $this->getRequest()->getParam('id');
        $report = $this->reports->getData($reportId);
        $resultRaw = $this->resultRawFactory->create();
        $errorTemplate = '<div class="report-data">Can\'t load report data: %1</div>';
        if ($report === null) {
            $resultRaw->setContents(__($errorTemplate, __('Incorrect report ID')));
        } elseif (!isset($report['class'])) {
            $resultRaw->setContents(__($errorTemplate, __('Block class is not set')));
        } else {
            /** @var View $block */
            $block = $layout->createBlock(View::class)->setReport($report['class']);
            $resultRaw->setContents($block->getReportHtml());
        }
        return $resultRaw;
    }
}
