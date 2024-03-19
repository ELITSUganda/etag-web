<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationOrder extends Model
{
    use HasFactory;


    public function generate_payment_link(
        $phone_number,
        $phone_number_type
    ) {

        $this->total_price = 500;
        $ip = $_SERVER['REMOTE_ADDR'];
        $data['tx_ref'] = 'ULITS-VAC-' . $this->id;
        $data['voucher'] = 'ULITS-VAC-' . $this->id;
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


    public function is_order_paid()
    {

        $tx_ref = 'ULITS-VAC-' . $this->id;
        //$tx_ref = 'ULITS-28';

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
        try {
            $responseBody = $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }

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
                        $status = strtolower($status);
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
