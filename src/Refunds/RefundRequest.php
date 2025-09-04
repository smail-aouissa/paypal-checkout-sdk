<?php

namespace PayPal\Checkout\Refunds;

use PayPal\Checkout\Concerns\CastsToJson;
use PayPal\Checkout\Contracts\Arrayable;
use PayPal\Checkout\Contracts\Jsonable;
use PayPal\Checkout\Orders\Amount;

class RefundRequest implements Arrayable, Jsonable
{
    use CastsToJson;

    protected ?Amount $amount = null;

    protected ?string $invoiceId = null;

    protected ?string $noteToPayer = null;

    protected ?string $reason = null;

    public function __construct(?string $currencyCode = null, ?float $value = null)
    {
        if ($currencyCode && $value) {
            $this->amount = new Amount($currencyCode, (string) $value);
        }
    }

    public function setAmount(Amount $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): ?Amount
    {
        return $this->amount;
    }

    public function setInvoiceId(string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    public function setNoteToPayer(string $noteToPayer): self
    {
        $this->noteToPayer = $noteToPayer;
        return $this;
    }

    public function getNoteToPayer(): ?string
    {
        return $this->noteToPayer;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->amount) {
            $data['amount'] = $this->amount->toArray();
        }

        if ($this->invoiceId) {
            $data['invoice_id'] = $this->invoiceId;
        }

        if ($this->noteToPayer) {
            $data['note_to_payer'] = $this->noteToPayer;
        }

        if ($this->reason) {
            $data['reason'] = $this->reason;
        }

        return $data;
    }
}