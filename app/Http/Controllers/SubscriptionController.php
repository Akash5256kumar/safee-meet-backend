<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index()
    {
        return view('subscription.index');
    }
}

