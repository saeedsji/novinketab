<?php

namespace App\Http\Controllers;

use App\Imports\BooksImport;
use App\Lib\Journey\ConditionEvaluationService;
use App\Models\Contact;
use App\Models\JourneyStep;

class HomeController extends Controller
{

    public function index()
    {
        $book = new BooksImport();
        $book->startRow();
        return view('pages.index');
    }


    public function docs()
    {
        return view('pages.docs');
    }

    public function logout()
    {
        if (auth()->check()) {
            auth()->logout();
        }
        return redirect(route('login'));
    }

    public function test()
    {
        $journeyStep = JourneyStep::find(6);
        $contact = Contact::find(1);
        // Inject the service via the method or constructor
        $conditionService = new ConditionEvaluationService();

        // condition_data is already a PHP array because of the $casts property on the model.
        $conditionGroups =$journeyStep->condition_data;
//        return $journeyStep->condition_data;

        return $conditionService->isConditionMet($contact, $conditionGroups) ? 'true' : 'false';

    }
}
