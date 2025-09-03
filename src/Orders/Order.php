<?php

namespace PayPal\Checkout\Orders;

use ArrayAccess;
use PayPal\Checkout\Concerns\CastsToJson;
use PayPal\Checkout\Contracts\Arrayable;
use PayPal\Checkout\Contracts\Jsonable;
use PayPal\Checkout\Exceptions\InvalidOrderException;
use PayPal\Checkout\Exceptions\InvalidOrderIntentException;

const CAPTURE = 'CAPTURE';
const AUTHORIZE = 'AUTHORIZE';

/**
 * https://developer.paypal.com/docs/api/orders/v2/#definition-order.
 */
class Order implements Arrayable, ArrayAccess, Jsonable
{
    use CastsToJson;

    /**
     * The ID of the order.
     *
     * @var string read only
     */
    protected string $id;

    /**
     * The intent to either capture payment immediately
     * or authorize a payment for an order after order creation.
     *
     * CAPTURE : The merchant intends to capture payment immediately after
     * the customer makes a payment.
     *
     * AUTHORIZE : The merchant intends to authorize a payment and place funds
     * on hold after the customer makes a payment.
     */
    protected string $intent;

    /**
     * An array of purchase units. At present only 1 purchase_unit is supported.
     * Each purchase unit establishes a contract between a payer and the payee.
     * https://developer.paypal.com/docs/api/orders/v2/#definition-purchase_unit_request.
     *
     * @var PurchaseUnit[]
     */
    protected array $purchase_units = [];

    /**
     * The intent to either capture payment immediately or authorize a payment for an order after order creation.
     * - CREATED : The order was created with the specified context.
     * - SAVED : The order was saved and persisted.
     * - APPROVED :  The customer approved the payment through the PayPal wallet
     *   or another form of guest or unbranded payment. For example, a card,
     *   bank account, or so on.
     * - VOIDED : All purchase units in the order are voided.
     * - COMPLETED : The payment was authorized or the authorized payment was captured
     *   for the order.
     *
     * @var string read only
     */
    protected string $status;

    /**
     * The order application context.
     * https://developer.paypal.com/docs/api/orders/v2/#definition-order_application_context.
     */
    protected ?ApplicationContext $application_context = null;

    /**
     * The order payee.
     * https://developer.paypal.com/docs/api/orders/v2/#definition-payee.
     */
    protected ?Payee $payee = null;

    /**
     * creates a new order instance.
     */
    public function __construct(string $intent = CAPTURE)
    {
        $this->setIntent($intent);
        $this->application_context = new ApplicationContext;
    }

    /**
     *  push a new item into purchase_units array.
     */
    public function addPurchaseUnit(PurchaseUnit $purchase_unit): self
    {
        if (count($this->purchase_units) >= 1) {
            throw new InvalidOrderException('At present only 1 purchase_unit is supported.');
        }

        $this->purchase_units[] = $purchase_unit;

        return $this;
    }

    /**
     * return's order purchase units.
     *
     * @return PurchaseUnit[]
     */
    public function getPurchaseUnits(): array
    {
        return $this->purchase_units;
    }

    /**
     * return's order application context.
     */
    public function getApplicationContext(): ?ApplicationContext
    {
        return $this->application_context;
    }

    /**
     * set's order application context.
     */
    public function setApplicationContext(ApplicationContext $application_context): self
    {
        $this->application_context = $application_context;

        return $this;
    }

    /**
     * return's order intent.
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * set's order intent.
     */
    public function setIntent(string $intent): self
    {
        if (! in_array($intent, [CAPTURE, AUTHORIZE])) {
            throw new InvalidOrderIntentException;
        }

        $this->intent = $intent;

        return $this;
    }

    /**
     * return's order id.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * return's order status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        if (empty($this->purchase_units)) {
            throw InvalidOrderException::invalidPurchaseUnit();
        }

        return [
            'intent' => $this->intent,
            'purchase_units' => array_map(
                function (PurchaseUnit $purchase_unit) {
                    return $purchase_unit->toArray();
                },
                $this->purchase_units
            ),
            'application_context' => $this->application_context ? $this->application_context->toArray() : null,
        ];
    }

    /**
     * @param  mixed  $offset
     * @param  mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->purchase_units[] = $value;
        } else {
            $this->purchase_units[$offset] = $value;
        }
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  mixed  $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->purchase_units[$offset]);
    }

    /**
     * @param  mixed  $offset
     */
    public function offsetGet($offset): ?PurchaseUnit
    {
        return $this->purchase_units[$offset] ?? null;
    }

    /**
     * Determine if a key exists on the purchase_units.
     *
     * @param  mixed  $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->purchase_units[$offset]);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getPayee(): ?Payee
    {
        return $this->payee;
    }
}
