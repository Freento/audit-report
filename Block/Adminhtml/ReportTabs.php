<?php

declare(strict_types=1);

namespace Freento\AuditReport\Block\Adminhtml;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Phrase;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;

/**
 * @method setId(string $string)
 */
class ReportTabs extends Tabs
{
    /**
     * @var DataObject
     */
    private DataObject $reports;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param DataObject $reports
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        LoggerInterface $logger,
        Escaper $escaper,
        DataObject $reports,
        array $data = []
    ) {
        $this->reports = $reports;
        $this->logger = $logger;
        $this->escaper = $escaper;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('report_tab');
        $this->setDestElementId('report_tab_content');
    }

    /**
     * Render tabs added by report submodules
     *
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _prepareLayout(): ReportTabs
    {
        $firstTab = true;

        $this->prepareReports();

        foreach ($this->reports->getData() as $id => $report) {
            // load first active tab statically
            if ($firstTab) {
                // if an exception is thrown on the static tab then show its text without stopping page loading
                try {
                    $content = $this->getLayout()->createBlock($report['class'])->toHtml();
                } catch (\Exception $e) {
                    $content = __(
                        '<div class="report-data">Can\'t load report data: %1</div>',
                        $e->getMessage()
                    )->render();
                    $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
                }

                $this->addTab(
                    $id,
                    [
                        'label' => __($report['label'] ?? ''),
                        'content' => $content,
                        'active' => true,
                        'class' => $report['tab_class'],
                    ]
                );

                $firstTab = false;
                continue;
            }

            // load other tabs with ajax
            $this->addTab(
                $id,
                [
                    'label' => __($report['label'] ?? ''),
                    'url' => $this->getUrl(
                        'freento_auditreport/index/report',
                        [
                            '_current' => true,
                            'id' => $this->escaper->escapeUrl($id)
                        ]
                    ),
                    'class' => 'ajax ' . $report['tab_class'],
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Validate, sanitize and sort report list
     *
     * @return void
     */
    private function prepareReports(): void
    {
        $reports = [];
        foreach ($this->reports->getData() as $id => $report) {
            // renderer class must be set
            if (!empty($report['class'])) {
                $reports[$id] = [
                    'class' => $report['class'],
                    'order' => $report['order'] ?? 0,
                    'label' => __($report['label'] ?? $id),
                    'tab_class' => $report['tab_class'] ?? '',
                ];
            }
        }

        // sort reports by order field
        uasort($reports, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        $this->reports->setData($reports);
    }
}
