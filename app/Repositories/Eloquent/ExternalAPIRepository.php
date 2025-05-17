<?php

namespace App\Repositories\Eloquent;

use App\Traits\SendRequestTrait;
use App\Http\Resources\DataWrapper;
use App\Http\Resources\GeneralDataWrapper;
use App\Models\Customer;
// use App\Models\CustomerAgreements;
// use App\Models\CustomerCards;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

/**
 * Class PaymentRepository
 *
 * This class provides methods for processing payment-related data and interactions with the payment gateway.
 */
class ExternalAPIRepository
{
    use SendRequestTrait;

    /**
     * Process a payment request by transforming the validated request into a payload to send to the client.
     *
     * @param FormRequest $request The validated payment request data.
     * @return mixed The result of the payment request.
     */
    public function SendRequest($request)
    {


        $requestData = $request->all();

        // Extract the two parameters you want to remove
        $endpoint = $request->input('endpoint');
        $key = $request->input('key');
        $method = $request->input('method');

        // Remove these parameters from the request data
        unset($requestData['endpoint']);
        unset($requestData['key']);
        unset($requestData['method']);

        // Generate the payment endpoint from the configuration file.
        // $endpoint = Config::get('TapEndpoints.business');

        // Send the payment request using the SendRequestTrait.
        return $this->sendPaymentRequest($requestData, $endpoint,$method, $key);
    }




}
