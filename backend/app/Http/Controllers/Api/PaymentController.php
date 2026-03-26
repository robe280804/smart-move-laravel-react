<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\AlreadySubscribedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCheckoutSessionRequest;
use App\Http\Responses\ApiError;
use App\Http\Responses\ApiSuccess;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function currentPlan(Request $request): ApiSuccess
    {
        $plan = $this->paymentService->getCurrentPlan($request->user());

        return new ApiSuccess(['plan' => $plan], [], Response::HTTP_OK);
    }

    public function checkout(CreateCheckoutSessionRequest $request): ApiSuccess|ApiError
    {
        try {
            $checkout = $this->paymentService->checkout($request->user(), $request->validated('plan'));
        } catch (AlreadySubscribedException $e) {
            return new ApiError($e, $e->getMessage(), Response::HTTP_CONFLICT);
        } catch (InvalidArgumentException $e) {
            return new ApiError($e, 'Payment configuration error. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (IncompletePayment $e) {
            return new ApiSuccess(['checkout_url' => route('cashier.payment', [$e->payment->id])], [], Response::HTTP_OK);
        }

        // Swap was performed — no redirect needed
        if ($checkout === null) {
            return new ApiSuccess(['checkout_url' => null], [], Response::HTTP_OK);
        }

        return new ApiSuccess(['checkout_url' => $checkout->url], [], Response::HTTP_OK);
    }

    public function billingPortal(Request $request): ApiSuccess|ApiError
    {
        $user = $request->user();

        if (! $user->hasStripeId()) {
            return new ApiError(null, 'No billing information found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $url = $this->paymentService->getBillingPortalUrl($user);

        return new ApiSuccess(['url' => $url], [], Response::HTTP_OK);
    }
}
