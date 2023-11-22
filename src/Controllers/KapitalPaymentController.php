<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;

class KapitalPaymentController extends Controller
{
    /**
     * @return Application|RedirectResponse|Redirector|string
     * @throws \Exception
     */
    public function createOrder()
    {
        //bura description, ve amount requestden gelmelidir ve formRequest de validation edilmelidir.
        $orderData = [
            'langCode' => config('payment.lang_code'),
            'merchantId' => config('payment.merchant_id'),
            'currencyCode' => config('payment.currency_code'),
            'description' => "Requestden gelen description",
            'amount' => 0.10,
            'approveUrl' => url('payment/approve'),
            'cancelUrl' => url('payment/cancel'),
            'declineUrl' => url('payment/decline'),
        ];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<TKKPG>
      <Request>
              <Operation>CreateOrder</Operation>
              <Language>' . $orderData['langCode'] . '</Language>
              <Order>
                    <OrderType>Purchase</OrderType>
                    <Merchant>' . $orderData['merchantId'] . '</Merchant>
                    <Amount>' . ($orderData['amount'] * 100) . '</Amount>
                    <Currency>' . $orderData['currencyCode'] . '</Currency>
                    <Description>' . $orderData['description'] . '</Description>
                    <ApproveURL>' . $orderData['approveUrl'] . '</ApproveURL>
                    <CancelURL>' . $orderData['cancelUrl'] . '</CancelURL>
                    <DeclineURL>' . $orderData['declineUrl'] . '</DeclineURL>
              </Order>
      </Request>
</TKKPG>';

        $result = $this->xmlRequest($xml);
        return $this->processCurlResponse($orderData, $result);
    }


    public function processCurlResponse($orderData, $curlResponseData)
    {
        try {
            $orderId = $curlResponseData['Response']['Order']['OrderID'];
            $sessionId = $curlResponseData['Response']['Order']['SessionID'];
            $statusCode = $curlResponseData['Response']['Status'];
            $redirectUrl = $curlResponseData['Response']['Order']['URL'];

            Payment::query()->create([
                'amount' => $orderData['amount'],
                'order_id' => $orderId,
                'session_id' => $sessionId,
                'user_id' => auth()->user()->id ?? NULL,
                'status_code' => $statusCode,
                'description' => $orderData['description'],
                'currency' => $orderData['currencyCode'],
                'date' => now(),
            ]);

            $redirectUrl = $redirectUrl . "?ORDERID=" . $orderId . "&SESSIONID=" . $sessionId . '&';
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return "Server xətası baş verdi";
        }

    }


    /**
     * @param $request
     * @return mixed
     */
    public function xmlRequest($request): mixed
    {
        $ch = curl_init();
        $header = array("Content-Type: text/html; charset=utf-8");
        $options = array(
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_URL => config('payment.prod_url'),
            CURLOPT_SSLCERT => config('payment.cert_file'),
            CURLOPT_SSLKEY => config('payment.key_file'),
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_POST => true
        );
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        return json_decode(json_encode(simplexml_load_string($output)), true);
    }


    /**
     * @param Request $request
     * @return null
     */
    public function approve(Request $request)
    {
        return $this->updateOrderStatus($request->input('xmlmsg'));
    }

    public function cancel(Request $request)
    {
        return $this->updateOrderStatus($request->input('xmlmsg'));
    }

    public function decline(Request $request)
    {
        return $this->updateOrderStatus($request->input('xmlmsg'));
    }


    private function updateOrderStatus($xmlResponse): void
    {
        $response = json_decode(json_encode(simplexml_load_string($xmlResponse)), true);
        $orderId = $response["Message"]["OrderID"];
        $orderStatus = $response["Message"]["OrderStatus"];
        $statusCode = $response["Message"]["ResponseCode"];

        $updateOrder = Payment::query()->where('order_id', '=', $orderId)->first();
        if ($updateOrder) {
            $updateOrder->update([
                'status_code' => $statusCode,
                'status' => $orderStatus,
            ]);
        }
    }

}
