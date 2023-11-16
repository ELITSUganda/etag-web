<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    use HasFactory;

    //getter for product_data 
    public function getProductDataAttribute()
    {
        $items = ProductOrderItem::where('product_order_id', $this->id)->get();
        return json_encode($items);
    } 
 

    public function generate_payment_link()
    {

        $data['tx_ref'] = 'ULITS-'.$this->id;
        $data['voucher'] = 'ULITS-'.$this->id;
        $data['amount'] = $this->total;
        $data['currency'] = 'UGX';
        $data['network'] = 'MTN';
        $data['email'] = 'mubahood360@gmail.com';
        $data['phone_number'] = '0783204665';
        $data['fullname'] = $this->name;
        $data['client_ip'] = '154.123.220.1';
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

        // Do something with $parsedResponse

        print_r($parsedResponse);
        die();

        


    }
}
