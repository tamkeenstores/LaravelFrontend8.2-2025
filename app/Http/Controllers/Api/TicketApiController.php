<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketDocument;
use App\Traits\CrudTrait;

class TicketApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'tickets';
    protected $relationKey = 'tickets_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Ticket::class, 'sort' => ['id','asc']];
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
        return ['user_id' => 'customerData'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return [];
    }
    
    public function storeduplicate(Request $request)
    {
        $success = false;
        $ticket = Ticket::where('ticket_no', $request->ticket_no)->first();
        $ticketMessage = TicketMessage::create([
            'ticket_no' => $request->ticket_no,
            'department' => $ticket->department,
            'priority' => $ticket->priority,
            'user_id'  =>  Auth::user()->id,
            'document'  =>  null,
            'subject'  =>  $ticket->subject,
            'description' => $request->get('description'),
            'status' => 1,
        ]);

        if($request->document) {
            foreach ($request->document as $key => $document) {
                $fileName = null;
                $file = $document;
                $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path('/assets/images'), $fileName);

                $ticketDcoument = TicketDocument::create([
                    'ticket_message_id' => $ticketMessage->id,
                    'document' => $fileName,
                ]);
            }
        }
        $ticket->status = isset($request->status) ? $request->status : 0;
        $ticket->update();
        $ticketMessage->save();
        
        return response()->json(['success' => true, 'message' => 'Ticket message has been sent !']);
    }
}
