<?php
declare(strict_types=1);

namespace App\DTO;

use Articus\DataTransfer\Annotation as DTA;

/**
 * Details from Cofidis order used as payment method at the time of the transaction.
 */
class PaymentPaymentMethodCofidis
{
    /**
     * The Cofidis&#39; order ID.
     * @DTA\Data(field="orderId", nullable=true)
     * @DTA\Validator(name="Scalar", options={"type":"string"})
     * @var string|null
     */
    public $order_id;

}
