<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendTestMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $toEmail = $request->email;

        
        Mail::raw('Đây là email test gửi từ API.', function ($message) use ($toEmail) {
            $message->to($toEmail)
                    ->subject('Test gửi mail từ API Laravel');
        });

        return response()->json(['message' => 'Mail đã được gửi đến ' . $toEmail]);
    }
}
