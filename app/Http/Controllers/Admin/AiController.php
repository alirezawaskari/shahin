<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\Setting\UpdateRequest;
use App\Models\AiSetting;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatMessage::query()
            ->join('laleh_ais', 'chat_messages.laleh_ai_id', '=', 'laleh_ais.id')
            ->leftJoin('users', 'chat_messages.user_id', '=', 'users.id')
            ->select('chat_messages.id', 'chat_messages.user_id', 'laleh_ais.session_id', 'users.name', 'users.family');

        if ($request->filled('user_name')) {
            $query->where(function($q) use ($request) {
                $q->where('users.name', 'like', '%' . $request->user_name . '%')
                    ->orWhere('users.family', 'like', '%' . $request->user_name . '%');
            });
        }

        if ($request->filled('session_id')) {
            $query->where('laleh_ais.session_id', $request->session_id);
        }

        $messages = $query->groupBy('chat_messages.laleh_ai_id')->orderByDesc('chat_messages.id')->get();

        return view('Admin.Ai.index', compact('messages'));
    }

    public function show($id)
    {
        $chat = ChatMessage::query()
            ->with('user')
            ->join('laleh_ais', 'chat_messages.laleh_ai_id', '=', 'laleh_ais.id')
            ->select('chat_messages.*', 'laleh_ais.session_id')
            ->where('chat_messages.id', $id)
            ->first();

        if (!$chat) {
            abort(404);
        }

        $chatMessages = ChatMessage::query()
            ->join('laleh_ais', 'chat_messages.laleh_ai_id', '=', 'laleh_ais.id')
            ->select('chat_messages.*')
            ->where(function ($query) use ($chat) {
                $query->where('chat_messages.user_id', $chat->user_id)
                    ->orWhere('laleh_ais.session_id', $chat->session_id);
            })
            ->orderBy('chat_messages.created_at')
            ->get();

        return view('Admin.Ai.show', [
            'chatMessages' => $chatMessages,
            'chat' => $chat,
        ]);
    }

    public function setting()
    {
        $setting = AiSetting::first();

        if (!$setting) {
            $setting = AiSetting::create([
                'model' => array_key_first(AiSetting::MODELS),
                'role' => ''
            ]);

            return redirect()->route('ai_settings.setting');
        }

        $models = AiSetting::MODELS;

        return view('Admin.Ai.setting', compact('setting', 'models'));
    }


    public function updateSetting(UpdateRequest $request, $id)
    {
        $setting = AiSetting::findOrFail($id);
        $setting->update($request->only('role', 'model'));
        alert()->success('موفق','تنظیمات با موفقیت به روز رسانی شد');
        return redirect()->back();
    }
}
