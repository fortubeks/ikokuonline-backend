<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use App\Mail\ContactMessageNotification;


class ContactController extends Controller
{
    use ApiResponse;

    public function store(ContactRequest $request)
    {
        $message = ContactMessage::create($request->validated());

        Mail::to(config('mail.admin_address'))->send(new ContactMessageNotification($message));
        return $this->success($message, 'Your message has been received.');
    }
}

