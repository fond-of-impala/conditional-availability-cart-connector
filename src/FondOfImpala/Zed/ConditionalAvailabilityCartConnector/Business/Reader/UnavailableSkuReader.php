<?php

namespace FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Reader;

use ArrayObject;
use FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Filter\ConditionalAvailabilityPeriodsFilterInterface;
use FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Finder\IndexFinderInterface;
use FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Reducer\ConditionalAvailabilityPeriodsReducerInterface;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

class UnavailableSkuReader implements UnavailableSkuReaderInterface
{
    protected ConditionalAvailabilityReaderInterface $conditionalAvailabilityReader;

    protected ConditionalAvailabilityPeriodsFilterInterface $conditionalAvailabilityPeriodsFilter;

    protected IndexFinderInterface $indexFinder;

    protected ConditionalAvailabilityPeriodsReducerInterface $conditionalAvailabilityPeriodsReducer;

    /**
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Reader\ConditionalAvailabilityReaderInterface $conditionalAvailabilityReader
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Filter\ConditionalAvailabilityPeriodsFilterInterface $conditionalAvailabilityPeriodsFilter
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Finder\IndexFinderInterface $indexFinder
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Reducer\ConditionalAvailabilityPeriodsReducerInterface $conditionalAvailabilityPeriodsReducer
     */
    public function __construct(
        ConditionalAvailabilityReaderInterface $conditionalAvailabilityReader,
        ConditionalAvailabilityPeriodsFilterInterface $conditionalAvailabilityPeriodsFilter,
        IndexFinderInterface $indexFinder,
        ConditionalAvailabilityPeriodsReducerInterface $conditionalAvailabilityPeriodsReducer
    ) {
        $this->conditionalAvailabilityReader = $conditionalAvailabilityReader;
        $this->conditionalAvailabilityPeriodsFilter = $conditionalAvailabilityPeriodsFilter;
        $this->indexFinder = $indexFinder;
        $this->conditionalAvailabilityPeriodsReducer = $conditionalAvailabilityPeriodsReducer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<string>
     */
    public function getByQuote(QuoteTransfer $quoteTransfer): array
    {
        $unavailableSkus = [];
        $groupedConditionalAvailabilityTransfers = $this->conditionalAvailabilityReader->getGroupedByQuote(
            $quoteTransfer,
        );

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($this->isItemAvailable($itemTransfer, $groupedConditionalAvailabilityTransfers)) {
                continue;
            }

            $sku = $itemTransfer->getSku();
            $unavailableSkus[$sku] = $sku;
        }

        return array_values($unavailableSkus);
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \ArrayObject<string, \ArrayObject<\Generated\Shared\Transfer\ConditionalAvailabilityTransfer>> $groupedConditionalAvailabilityTransfers
     *
     * @return bool
     */
    protected function isItemAvailable(
        ItemTransfer $itemTransfer,
        ArrayObject $groupedConditionalAvailabilityTransfers
    ): bool {
        $conditionalAvailabilityPeriodTransfers = $this->conditionalAvailabilityPeriodsFilter
            ->filterFromGroupedConditionalAvailabilitiesByItem(
                $groupedConditionalAvailabilityTransfers,
                $itemTransfer,
            );

        if ($conditionalAvailabilityPeriodTransfers === null) {
            return false;
        }

        $effectedIndex = $this->indexFinder->findConcreteFromConditionalAvailabilityPeriods(
            $conditionalAvailabilityPeriodTransfers,
            $itemTransfer,
        );

        if ($effectedIndex === null) {
            return false;
        }

        $this->conditionalAvailabilityPeriodsReducer->reduceByItemAndEffectedIndex(
            $conditionalAvailabilityPeriodTransfers,
            $itemTransfer,
            $effectedIndex,
        );

        return true;
    }
}
