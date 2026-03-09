<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiSuccess;
use App\Neuron\FitnessAgent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use NeuronAI\Chat\Messages\UserMessage;

class AgentController extends Controller
{
    public function call(Request $request)
    {
        $message = FitnessAgent::make()
            ->chat(new UserMessage($request->only('message')))
            ->getMessage();

        return new ApiSuccess(
            null,
            ['response' => $message->getContent()],
            Response::HTTP_OK
        );
    }
}
