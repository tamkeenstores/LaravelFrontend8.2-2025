<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Traits\CrudTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class EmailTemplateApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'email_template';
    protected $relationKey = 'email_template_id';


    public function model() {
        $data = ['limit' => -1, 'model' => EmailTemplate::class, 'sort' => ['id','desc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        return [];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {   
        return [];
    }
    
    public function store(Request $request) 
    {
        $templateContent = $request->page_content;
        $templateName =  Str::slug($request->name);
        
        $filePath = resource_path("views/email/{$templateName}.blade.php");
        File::put($filePath, $templateContent);
        $filename = ''.$templateName.'.blade.php';
        
        $emailtemplate = [
            'purpose_template' => isset($request->purpose_template) ? $request->purpose_template : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'subject' => isset($request->subject) ? $request->subject : null,
            'subject_arabic' => isset($request->subject_arabic) ? $request->subject_arabic : null,
            'page_content' => isset($request->page_content) ? $request->page_content : null,
            'sms' => isset($request->sms) ? $request->sms : null,
            'sms_arabic' => isset($request->sms_arabic) ? $request->v : null,
            'status' => isset($request->status) ? $request->status : 0,
            'file_path' => isset($filename) ? $filename : null
        ];
        
        EmailTemplate::create($emailtemplate);
        return response()->json(['success' => true, 'message' => 'Email Template Has been Created Successfully!']);
    }
    
    public function update(Request $request, $id)
    {
        $success = false;
        $originalEmailTemplate = EmailTemplate::find($id);
        // Assuming $emailtemplate contains the updated email template model or data
        $templateContent = $request->page_content;
        $oldTemplateName = $originalEmailTemplate->name;
        $newTemplateName = Str::slug($request->name); 
        $filename = ''.$newTemplateName.'.blade.php';
    
        // Create the blade file with the template content
        $oldFilePath = resource_path("views/email/{$oldTemplateName}.blade.php");
        $newFilePath = resource_path("views/email/{$newTemplateName}.blade.php");
    
        if ($oldTemplateName !== $newTemplateName) {
            // Rename the blade file if the name has changed
            if (File::exists($oldFilePath)) {
                File::move($oldFilePath, $newFilePath);
            }
        }
    
        File::put($newFilePath, $templateContent);
    
        // Delete the old blade file if it exists and the name has changed
        if ($oldTemplateName !== $newTemplateName && File::exists($oldFilePath)) {
            File::delete($oldFilePath);
        }
        
        $emailtemplate = EmailTemplate::whereId($id)->update([
            'purpose_template' => isset($request->purpose_template) ? $request->purpose_template : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'subject' => isset($request->subject) ? $request->subject : null,
            'subject_arabic' => isset($request->subject_arabic) ? $request->subject_arabic : null,
            'page_content' => isset($request->page_content) ? $request->page_content : null,
            'sms' => isset($request->sms) ? $request->sms : null,
            'sms_arabic' => isset($request->sms_arabic) ? $request->v : null,
            'status' => isset($request->status) ? $request->status : 0,
            'file_path' => isset($filename) ? $filename : null
        ]);
        $success = true;
    
        return response()->json(['success' => $success, 'message' => 'Email Template updated successfully']);
    }
    
    public function destroy($id)
    {
        $success = false;
        $emailtemplate = EmailTemplate::findOrFail($id);
    
        // Get the file path of the blade file associated with the email template
        $bladeFilePath = resource_path("views/email/{$emailtemplate->file_path}");
    
        // Check if the blade file exists, and delete it if it does
        if (File::exists($bladeFilePath)) {
            File::delete($bladeFilePath);
        }
    
        // Delete the email template from the database
        if($emailtemplate) {
            $emailtemplate->delete();
            $success = true;   
        }
    
        return response()->json(['success' => $success,'message' => 'Email Template deleted successfully']);
    }
    
}
