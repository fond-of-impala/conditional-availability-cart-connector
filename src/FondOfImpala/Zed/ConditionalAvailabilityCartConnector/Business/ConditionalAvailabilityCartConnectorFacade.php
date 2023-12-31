<?php

declare(strict_types = 1);

namespace FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business;

use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \FondOfImpala\Zed\ConditionalAvailabilityCartConnector\Business\ConditionalAvailabilityCartConnectorBusinessFactory getFactory()
 */
class ConditionalAvailabilityCartConnectorFacade extends AbstractFacade implements ConditionalAvailabilityCartConnectorFacadeInterface
{
    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function expandQuote(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()
            ->createQuoteExpander()
            ->expand($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function expandChangedCartItems(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        return $this->getFactory()
            ->createConditionalAvailabilityItemExpander()
            ->expand($cartChangeTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function cleanDeliveryDateOnEmptyCart(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()
            ->createConditionalAvailabilityDeliveryDateCleaner()
            ->cleanDeliveryDate($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function ensureEarliestDate(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()
            ->createConditionalAvailabilityEnsureEarliestDate()
            ->ensureEarliestDate($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<string>
     */
    public function getUnavailableSkusByQuote(QuoteTransfer $quoteTransfer): array
    {
        return $this->getFactory()
            ->createUnavailableSkuReader()
            ->getByQuote($quoteTransfer);
    }
}
