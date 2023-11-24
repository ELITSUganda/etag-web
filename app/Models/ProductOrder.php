<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->total_price = 0;
            $model->status = 'Pending';
            $model->order_is_paid = 0;
        });

        static::created(function ($model) {
        });

        static::updated(function ($order) {
            if ($order->status != $this->getOriginal('status')) {
                if ($order->status == 'Completed') {
                    $sms_to_send = 'Your order has been completed. Thank you for shopping with us.';
                    Utils::send_message($order->phone_number, $sms_to_send);
                    Utils::sendNotification($order->phone_number, $sms_to_send);
                    Utils::sendNotification(
                        $sms_to_send,
                        $order->customer_id,
                        $headings =  "Order Completed",
                    );
                } else if ($order->status == 'Cancelled') {
                    $sms_to_send = 'Your order has been cancelled. Thank you for shopping with us.';
                    Utils::send_message($order->phone_number, $sms_to_send);
                    Utils::sendNotification($order->phone_number, $sms_to_send);
                    Utils::sendNotification(
                        $sms_to_send,
                        $order->customer_id,
                        $headings =  "Order Cancelled",
                    );
                } else if ($order->status == 'Pending') {
                    $sms_to_send = 'Your order has been received. Thank you for shopping with us.';
                    Utils::send_message($order->phone_number, $sms_to_send);
                    Utils::sendNotification($order->phone_number, $sms_to_send);
                    Utils::sendNotification(
                        $sms_to_send,
                        $order->customer_id,
                        $headings =  "Order Received",
                    );
                } else if ($order->status == 'Shipping') {
                    $sms_to_send = 'Your order is being shipped. Thank you for shopping with us.';
                    Utils::send_message($order->phone_number, $sms_to_send);
                    Utils::sendNotification($order->phone_number, $sms_to_send);
                    Utils::sendNotification(
                        $sms_to_send,
                        $order->customer_id,
                        $headings =  "Order Shipping",
                    );
                } else if ($order->status == 'Delivered') {
                    $sms_to_send = 'Your order has been delivered. Thank you for shopping with us.';
                    Utils::send_message($order->phone_number, $sms_to_send);
                    Utils::sendNotification($order->phone_number, $sms_to_send);
                    Utils::sendNotification(
                        $sms_to_send,
                        $order->customer_id,
                        $headings =  "Order Delivered",
                    );
                }
            }
        });
    }
    /* 
'Pending' => 'Pending',
                'Shipping' => 'Shipping',
                'Delivered' => 'Delivered',
                'Cancelled' => 'Cancelled'
*/
    //getter for product_data 
    public function getProductDataAttribute()
    {
        $items = ProductOrderItem::where('product_order_id', $this->id)->get();
        return json_encode($items);
    }


    public function generate_payment_link(
        $phone_number,
        $phone_number_type
    ) {

        $ip = $_SERVER['REMOTE_ADDR'];
        $data['tx_ref'] = 'ULITS-' . $this->id;
        $data['voucher'] = 'ULITS-' . $this->id;
        $data['amount'] = $this->total_price;
        $data['currency'] = 'UGX';
        $data['network'] = $phone_number_type;
        $data['email'] = 'mubahood360@gmail.com';
        $data['phone_number'] = $phone_number;
        $data['fullname'] = $this->name;
        $data['client_ip'] = $ip;
        $data['device_fingerprint'] = '62wd23423rq324323qew1';
        $data['meta'] = json_encode($this);

        // Create a new Guzzle client instance
        $client = new Client();

        // Specify the URL you want to send the request to
        $url = 'https://api.flutterwave.com/v3/charges?type=mobile_money_uganda';

        // Specify the headers you want to include in the request
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer FLWSECK-4131a12ce00186825da8070013bef461-18bda11a003vt-X',
            // Add any other headers as needed
        ];

        // Specify the raw body content
        $body = json_encode($data);

        // Make the HTTP POST request with the specified parameters
        $response = $client->post($url, [
            'headers' => $headers,
            'body' => $body,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // You can now work with the response as needed
        // For example, you might decode the JSON response:
        $parsedResponse = json_decode($responseBody, true);

        if ($parsedResponse == null) {
            throw new \Exception('Error Processing Request', 1);
        }
        $payment_link = '';
        if (isset($parsedResponse['meta'])) {
            if (isset($parsedResponse['meta']['authorization'])) {
                if (isset($parsedResponse['meta']['authorization']['redirect'])) {
                    $payment_link = $parsedResponse['meta']['authorization']['redirect'];
                }
            }
        }
        return $payment_link;
    }

    //customer_id
    public function customer()
    {
        return $this->belongsTo(Administrator::class, 'customer_id', 'id');
    }


    public function is_order_paid()
    {

        //$tx_ref = 'ULITS-' . $this->id;
        $tx_ref = 'ULITS-28';

        // Create a new Guzzle client instance
        $client = new Client();

        // Specify the URL you want to send the request to
        $url = 'https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=' . $tx_ref;

        // Specify the headers you want to include in the request
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer FLWSECK-4131a12ce00186825da8070013bef461-18bda11a003vt-X',
            // Add any other headers as needed
        ];


        // Make the HTTP POST request with the specified parameters
        $response = $client->get($url, [
            'headers' => $headers,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // You can now work with the response as needed
        // For example, you might decode the JSON response:
        $parsedResponse = json_decode($responseBody, true);

        if ($parsedResponse == null) {
            throw new \Exception('Error Processing Request', 1);
        }

        $status = 0;
        $payment_link = '';
        if (isset($parsedResponse['status'])) {
            $status = $parsedResponse['status'];
            if ($status == 'success') {
                if (isset($parsedResponse['data'])) {
                    if (isset($parsedResponse['data']['status'])) {
                        $status = $parsedResponse['data']['status'];
                        if ($status == 'successful') {
                            $status = 1;
                        }
                    }
                }
            }
        }
        return $status;
    }
}
