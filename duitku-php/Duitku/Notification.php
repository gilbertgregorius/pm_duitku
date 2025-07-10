<?php
class Duitku_Notification
{

    public $resultCode;
    public $merchantOrderId;
    public $reference;
    public $paymentCode;
    public $merchantUserId;
    public $amount;
    public $productDetail;
    public $publisherOrderId;
    public $settlementDate;
    public $vaNumber;
    public $sourceAccount;
    public $signature;
    public $additionalParam;
    public $rawData;

    public function __construct()
    {
        $this->rawData = array_merge($_GET, $_POST);

        $this->resultCode = $this->getValue('resultCode');
        $this->merchantOrderId = $this->getValue('merchantOrderId');
        $this->reference = $this->getValue('reference');
        $this->paymentCode = $this->getValue('paymentCode');
        $this->merchantUserId = $this->getValue('merchantUserId');
        $this->amount = $this->getValue('amount');
        $this->productDetail = $this->getValue('productDetail');
        $this->signature = $this->getValue('signature');

        $this->publisherOrderId = $this->getValue('publisherOrderId');
        $this->settlementDate = $this->getValue('settlementDate');
        $this->vaNumber = $this->getValue('vaNumber');
        $this->sourceAccount = $this->getValue('sourceAccount');
        $this->additionalParam = $this->getValue('additionalParam');

        $this->validateRequiredFields();
    }

    private function getValue($key)
    {
        return $_GET[$key] ?? $_POST[$key] ?? '';
    }

    public function isSuccess()
    {
        return $this->resultCode === '00';
    }

    public function isFailed()
    {
        return !empty($this->resultCode) && $this->resultCode !== '00';
    }

    public function isPending()
    {
        return empty($this->resultCode);
    }

    public function getTransactionStatus()
    {
        if ($this->isSuccess()) {
            return 'success';
        } elseif ($this->isFailed()) {
            return 'failed';
        } else {
            return 'pending';
        }
    }

    public function validateSignature($merchantCode, $secretKey)
    {
        if (empty($this->signature)) {
            return false;
        }

        $expectedSignature = md5(
            $merchantCode .
                $this->amount .
                $this->merchantOrderId .
                $secretKey
        );

        return hash_equals($expectedSignature, $this->signature);
    }

    private function validateRequiredFields()
    {
        $required = ['resultCode', 'merchantOrderId', 'reference'];

        foreach ($required as $field) {
            if (empty($this->$field)) {
                throw new Exception("Missing required notification field: $field");
            }
        }
    }

    public function toArray()
    {
        return [
            'resultCode' => $this->resultCode,
            'merchantOrderId' => $this->merchantOrderId,
            'reference' => $this->reference,
            'paymentCode' => $this->paymentCode,
            'merchantUserId' => $this->merchantUserId,
            'amount' => $this->amount,
            'productDetail' => $this->productDetail,
            'signature' => $this->signature,
            'publisherOrderId' => $this->publisherOrderId,
            'settlementDate' => $this->settlementDate,
            'vaNumber' => $this->vaNumber,
            'sourceAccount' => $this->sourceAccount,
            'additionalParam' => $this->additionalParam,
            'transactionStatus' => $this->getTransactionStatus(),
            'rawData' => $this->rawData
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
