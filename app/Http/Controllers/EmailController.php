<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportMail;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $pdfPath = $request->file('pdf')->store('temp');

        $mailData = [
            'title' => 'Rapport quotidien de Drouss',
            'body' => 'Please find the attached daily report.',
            'files' => [storage_path('app/' . $pdfPath)]
        ];

        $email = 'bassirouseye53@gmail.com';
        Mail::to($email)->send(new DailyReportMail($mailData));

        unlink(storage_path('app/' . $pdfPath));

        return response()->json(['message' => 'Email sent successfully']);
    }
}
