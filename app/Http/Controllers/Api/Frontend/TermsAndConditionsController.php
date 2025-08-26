<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TermsAndConditions;
use App\Models\BrowserAnalytics;
use App\Models\Order;
use App\Models\TermsAndConditionsContent;
use Str;
use Illuminate\Support\Facades\Mail;

class TermsAndConditionsController extends Controller
{
    public function index()
    {
        $terms = TermsAndConditionsContent::with('terms')->get();
        return response()->json(['success' => true,'data' => $terms ]);
    }
    
    public function emailSendTest()
    {
        // $order = Order::with('warehouse.showroomData')->where('id', 21462929)->first();
        // dd($order);
        $email = 'qaiserabbas613@gmail.com';
        
        try {
        Mail::raw('This is a plain text test email.', function ($message) {
            $message->from('sales@tamkeenstores.com.sa', 'Tamkeen Stores');
            $message->to('qaiserabbas613@gmail.com');
            $message->subject('Plain Text Test Email');
        });

        return response()->json(['success' => true, 'message' => 'Email sent']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
    
        // try {
        //     $data = ['name' => 'Qaiser Abbas'];
    
        //     Mail::send([], $data, function ($message) use ($email) {
        //         $message->from('sales@tamkeenstores.com', 'Tamkeen Stores');
        //         $message->to('qaiserabbas613@gmail.com');
        //         $message->subject('Test Email Using Laravel Mail::send');
        //         $message->html('Hello <b>Qaiser</b>, this is a <i>test email</i>.');
        //     });
    
        //     return response()->json(['success' => true, 'email' => $email]);
        // } catch (\Exception $e) {
        //     return response()->json(['success' => false, 'message' => $e->getMessage()]);
        // }
    }
    
    public function store(Request $request)
    {
        $data = TermsAndConditionsContent::with('terms')->first();
        if($data) {
            $oldterms = TermsAndConditions::where('terms_id', '=',$data->id)->get();
            $oldterms->each->delete();
            $data->status = $request->status;
            $data->save();
        } else {
             $data = TermsAndConditionsContent::create([
                'status' => $request->status,
            ]);
        }
        
        $termsArray = $request->terms;
        for ($i = 0; $i < count($termsArray); $i++) {
            TermsAndConditions::create([
                'terms_id' => $data->id,
                'title' => $termsArray[$i]['title'],
                'title_arabic' => $termsArray[$i]['title_arabic'],
                'tags' => $termsArray[$i]['tags'],
                'tags_arabic' => $termsArray[$i]['tags_arabic'],
                'page_content' => $termsArray[$i]['page_content'],
                'page_content_ar' => $termsArray[$i]['page_content_ar'],
            ]);
        }
        
        return response()->json(['success' => true, 'message' => 'General Setting Has been Saved!']);
    }
    
    public function storeUserBrowser(Request $request) {
        $success = false;
        $message = '';
        $userId = Str::random(8); 
        $browser = $request->browser;
        $countryCode = $request->country;
        $data = [
            'user_id' => $userId,
            'browser' => $browser,
            'country_code' => $countryCode
        ];
        $user = BrowserAnalytics::create($data);

        if($success) {
            $messsage = 'User browser cannot be detected!';
        }
        
        return response()->json([
            'message' => 'User created successfully',
            'user_id' => $user->user_id,
            'browser' => $user->browser,
            'country_code' => $user->country_code
        ]);
    }
    
        public function getBrowserStats()
    {
        $totalUsers = BrowserAnalytics::count();

        if ($totalUsers === 0) {
            return response()->json([]);
        }

        $browsers = BrowserAnalytics::selectRaw('browser, COUNT(*) as count')
                                    ->groupBy('browser')
                                    ->get();

        $browserStats = $browsers->map(function ($item) use ($totalUsers) {
            return [
                'browser' => $item->browser,
                'percentage' => round(($item->count / $totalUsers) * 100, 2),
            ];
        });

        return response()->json($browserStats);
    }
}
