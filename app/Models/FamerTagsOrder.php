<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamerTagsOrder extends Model
{
    use HasFactory;


    //boot
    protected static function boot()
    {
        parent::boot();

        // Add your custom logic here
        static::creating(function ($model) {
            // Perform actions before creating a new record
            $model = self::doPrepare($model);
        });

        static::updating(function ($model) {
            // Perform actions before updating an existing record
            $model = self::doPrepare($model);
        });
        
        
    }

    //do prepare
    public static function doPrepare($data)
    {
        $farm = Farm::where('id', $data->farm_id)->first();
        if($farm == null){
            throw new \Exception('Farm not found.');
        }
        $data->district_id = $farm->district_id;
        $data->famer_id = $farm->administrator_id;
        $data->sub_county_id = $farm->sub_county_id;
        return $data;
    }

    /**
     * This function generates a payment link for the farmer to pay for the
     * tags they have ordered. It throws an exception if the required parameters
     * are not found. It uses the Flutterwave API to generate the link.
     * 
     * @throws \Exception
     * @return string|null
     */

    public function get_flutterwave_link()
    {
        $flutterwave_link  = $this->flutterwave_link;
        if($flutterwave_link != null && strlen($flutterwave_link) > 10){
            return null;
        }

        if($this->flutterwave_phone_number == null || strlen($this->flutterwave_phone_number) < 8){
            throw new \Exception('Phone number not found.'); 
        }

        if($this->total_tags_ordered_amount == null || $this->total_tags_ordered_amount < 1){
            throw new \Exception('Amount not found.'); 
        }

        if($this->phone_number_type == null || strlen($this->phone_number_type) < 1){
            throw new \Exception('Phone number type not found.'); 
        }
        if($this->name == null || strlen($this->name) < 1){
            throw new \Exception('Name not found.'); 
        }
        if($this->id == null || $this->id < 1){
            throw new \Exception('ID not found.'); 
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $data['tx_ref'] = 'ULITS-PRO-' . $this->id;
        $data['voucher'] = 'ULITS-PRO-' . $this->id;
        $data['amount'] = $this->total_tags_ordered_amount;
        $data['currency'] = 'UGX';
        $data['network'] = $this->phone_number_type;
        $data['email'] = 'mubahood360@gmail.com';
        $data['phone_number'] = $this->flutterwave_phone_number;
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
        $responseBody = null;

        try {
            $responseBody = $response->getBody()->getContents();
        } catch (\Throwable $th) {
            // Handle the exception as needed
            throw new \Exception('Error Processing Request because of: ' . $th->getMessage(), 1);
        }

        if($responseBody == null){
            throw new \Exception('Error Processing Request', 1);
        } 

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

        if($payment_link == null || strlen($payment_link) < 10){
            throw new \Exception('Error Processing Request', 1);
        }

        //SAVE
        $this->flutterwave_link = $payment_link;
        $this->flutterwave_status = 'Processed';

        try {
            $this->save();
        } catch (\Throwable $th) {
            throw new \Exception('Error Processing Request because of: ' . $th->getMessage(), 1);
        }

        return $payment_link;
    }
 
    //belongs to user
    public function user()
    {
        return $this->belongsTo(User::class, 'famer_id');
    }


    public function is_order_paid()
    {

        $tx_ref = 'ULITS-PRO-' . $this->id;
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
        $responseBody = $response->getBody()->getContents();

        // You can now work with the response as needed
        // For example, you might decode the JSON response:
        $parsedResponse = json_decode($responseBody, true);

        if ($parsedResponse == null) {
            throw new \Exception('Error Processing Request', 1);
        }

        $status = 0; 
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
        if($status == 1){
            $this->flutterwave_status = 'PAID';
            $this->save();
        }else{
            $this->flutterwave_status = 'NOT PAID';
            $this->save();
        }
        return $status;
    }

    //appends farm_text
    protected $appends = ['farm_text','famer_text'];
    public function getFarmTextAttribute()
    {
        $farm = Farm::where('id', $this->farm_id)->first();
        if($farm == null){
            return 'N/A';
        }
        return $farm->holding_code;
    }

    //famer_text 
    public function getFamerTextAttribute()
    {
        $famer = User::where('id', $this->famer_id)->first();
        if($famer == null){
            return 'N/A';
        }
        return $famer->name;
    } 
}
