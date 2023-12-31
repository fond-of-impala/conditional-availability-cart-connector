<?php

namespace FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Reader;

use ArrayObject;
use FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Filter\SkusFilterInterface;
use FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Dependency\Facade\ConditionalAvailabilityCartConnectorToConditionalAvailabilityFacadeInterface;
use Generated\Shared\Transfer\ConditionalAvailabilityCriteriaFilterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

class ConditionalAvailabilityReader implements ConditionalAvailabilityReaderInterface
{
    protected SkusFilterInterface $skusFilter;

    protected CustomerReaderInterface $customerReader;

    protected ConditionalAvailabilityCartConnectorToConditionalAvailabilityFacadeInterface $conditionalAvailabilityFacade;

    /**
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Filter\SkusFilterInterface $skusFilter
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\Reader\CustomerReaderInterface $customerReader
     * @param \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Dependency\Facade\ConditionalAvailabilityCartConnectorToConditionalAvailabilityFacadeInterface $conditionalAvailabilityFacade
     */
    public function __construct(
        SkusFilterInterface $skusFilter,
        CustomerReaderInterface $customerReader,
        ConditionalAvailabilityCartConnectorToConditionalAvailabilityFacadeInterface $conditionalAvailabilityFacade
    ) {
        $this->skusFilter = $skusFilter;
        $this->customerReader = $customerReader;
        $this->conditionalAvailabilityFacade = $conditionalAvailabilityFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \ArrayObject
     */
    public function getGroupedByQuote(QuoteTransfer $quoteTransfer): ArrayObject
    {
        $skus = $this->skusFilter->filterFromQuote($quoteTransfer);
        $customerTransfer = $this->customerReader->getByQuoteTransfer($quoteTransfer);

        if ($customerTransfer === null || $customerTransfer->getAvailabilityChannel() === null || count($skus) === 0) {
            return new ArrayObject();
        }

        $conditionalAvailabilityCriteriaFilterTransfer = (new ConditionalAvailabilityCriteriaFilterTransfer())
            ->setSkus($skus)
            ->setWarehouseGroup('EU')
            ->setMinimumQuantity(1)
            ->setChannel($customerTransfer->getAvailabilityChannel());

        return $this->conditionalAvailabilityFacade->findGroupedConditionalAvailabilities(
            $conditionalAvailabilityCriteriaFilterTransfer,
        );
    }
}
