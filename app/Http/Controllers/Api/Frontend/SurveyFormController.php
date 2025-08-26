<?php

namespace App\Http\Controllers\Api\Frontend;

use Illuminate\Http\Request;
use App\Models\SurveyForm;
use App\Models\SurveyFormSubmissions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use App\Models\SurveyResponse;
use App\Http\Controllers\Controller;

class SurveyFormController extends Controller
{
    // public function showFront($id)
    // {
    //     $surveyForm = SurveyForm::find($id);
    //     return response()->json([
    //         'data' => $surveyForm,
    //         'success' => true
    //     ]);
    // }
    public function showFront($slug)
    {
        $surveyForm = SurveyForm::where('slug',$slug)->first();
         return response()->json([
            'data' => $surveyForm,
            'success' => true
        ]);
    }
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'response_data' => 'required|array', // Ensures we receive an array
        ]);
        $data = [
            'response_data' => $request->response_data,
            'form_id' => $request->form_id
            ];
        $surveyResponse = SurveyResponse::create($data);
        $response = [
            'message' => 'Survey response saved successfully!',
            'data' => $surveyResponse,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);

    }

}
