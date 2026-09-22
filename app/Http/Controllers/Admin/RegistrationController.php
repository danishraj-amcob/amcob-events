<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistrationLog;

class RegistrationController extends Controller
{
    public function index()
    {
        $registrations = EventRegistrationLog::latest()->paginate(25);

        return view('admin.registrations.index', compact('registrations'));
    }
}
