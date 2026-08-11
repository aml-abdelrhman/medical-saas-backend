<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    // حفظ رسالة تواصل جديدة قادمة من الموقع
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رسالتك بنجاح وسنتواصل معك قريباً.',
            'data' => $contactMessage
        ], 201);
    }

    // جلب جميع رسائل التواصل لعرضها للأدمن
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(15); // جلب الرسائل بترتيب الأحدث مع تقسيمها لصفحات

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}