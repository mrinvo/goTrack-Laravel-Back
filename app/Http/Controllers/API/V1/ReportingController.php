<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Repositories\Eloquent\ExternalAPIRepository;

class ReportingController extends Controller
{


    /**
     * The GeneralOperationsRepository instance used for handling payment-related database operations.
     *
     * @var ExternalAPIRepository
     */
    protected $ExternalAPIRepository;

    /**
     * PaymentController constructor.
     *
     * @param ExternalAPIRepository $requestRepository The PaymentRepository instance for processing payment requests.
     */
    public function __construct(ExternalAPIRepository $requestRepository)
    {
        $this->ExternalAPIRepository = $requestRepository;
    }


    public function fetch(Request $request)
    {


        // Check if a redirect URL is provided in the response and redirect the user if available.
        return $response = $this->ExternalAPIRepository->SendRequest($request);
    }

}