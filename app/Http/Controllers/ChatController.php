<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:5000',
        ]);

        $prompt = $request->input('prompt');
        $model = 'gpt-4o-mini';

        try {
            $result = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $response = $result->choices[0]->message->content;
            $usage = $result->usage;

            $chatLog = ChatLog::create([
                'prompt' => $prompt,
                'response' => $response,
                'model' => $model,
                'prompt_tokens' => $usage->promptTokens,
                'completion_tokens' => $usage->completionTokens,
                'total_tokens' => $usage->totalTokens,
            ]);

            return view('chat', [
                'prompt' => $prompt,
                'response' => $response,
                'usage' => [
                    'prompt_tokens' => $usage->promptTokens,
                    'completion_tokens' => $usage->completionTokens,
                    'total_tokens' => $usage->totalTokens,
                ],
            ]);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            ChatLog::create([
                'prompt' => $prompt,
                'response' => null,
                'model' => $model,
                'error' => $error,
            ]);

            return view('chat', [
                'prompt' => $prompt,
                'error' => $error,
            ]);
        }
    }
}
