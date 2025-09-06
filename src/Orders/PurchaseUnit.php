<?php

namespace PayPal\Checkout\Orders;

use PayPal\Checkout\Concerns\CastsToJson;
use PayPal\Checkout\Concerns\HasCollection;
use PayPal\Checkout\Contracts\Arrayable;
use PayPal\Checkout\Contracts\Jsonable;
use PayPal\Checkout\Exceptions\MultiCurrencyOrderException;

/**
 * https://developer.paypal.com/docs/api/orders/v2/#definition-purchase_unit.
 */
class PurchaseUnit implements Arrayable, Jsonable
{
    use CastsToJson;
    use HasCollection;

    /**
     * The total order Amount with an optional breakdown that provides details,
     * such as the total item Amount, total tax Amount, shipping, handling, insurance,
     * and discounts, if any.
     */
    protected $amount;

    /**
     * An array of items that the customer purchases from the merchant.
     *
     * @var Item[]
     */
    protected array $items = [];

    /**
     * The API caller-provided external invoice number for this order.
     * Appears in both the payer's transaction history and the emails that the payer receives.
     */
    protected ?string $reference_id = null;

    /**
     * Create a new collection.
     */
    public function __construct($amount = null)
    {
        if ($amount) {
            $this->setAmount($amount);
        }
    }

    /**
     * Set the amount for this purchase unit.
     */
    public function setAmount($amount): self
    {
        if ($amount instanceof Amount || $amount instanceof AmountBreakdown) {
            $this->amount = $amount;
        } else {
            throw new \InvalidArgumentException('Amount must be an instance of Amount or AmountBreakdown');
        }
        
        return $this;
    }

    /**
     * Set the reference ID for this purchase unit.
     */
    public function setReferenceId(string $referenceId): self
    {
        $this->reference_id = $referenceId;
        
        return $this;
    }

    /**
     * Get the reference ID for this purchase unit.
     */
    public function getReferenceId(): ?string
    {
        return $this->reference_id;
    }

    /**
     *  push a new item into items array.
     *
     * @param  Item[]  $items
     *
     * @throws MultiCurrencyOrderException
     */
    public function addItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     *  push a new item into items array.
     *
     * @throws MultiCurrencyOrderException
     */
    public function addItem(Item $item): self
    {
        if ($item->getAmount()->getCurrencyCode() != $this->amount->getCurrencyCode()) {
            throw new MultiCurrencyOrderException;
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * return's purchase unit items.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * return's the purchase unit amount.
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * convert a purchase unit instance to array.
     */
    public function toArray(): array
    {
        $data = [
            'amount' => $this->amount ? $this->amount->toArray() : null,
        ];

        if (!empty($this->items)) {
            $data['items'] = array_map(
                function (Item $item) {
                    return $item->toArray();
                },
                $this->items
            );
        }

        if ($this->reference_id) {
            $data['reference_id'] = $this->reference_id;
        }

        return array_filter($data, function($value) {
            return $value !== null;
        });
    }
}
