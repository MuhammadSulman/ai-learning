<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat - Learning</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; min-height: 100vh; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { font-size: 1.5rem; color: #111827; margin-bottom: 1.5rem; }
        .card { background: white; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        label { display: block; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        textarea { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem; font-family: inherit; resize: vertical; min-height: 100px; }
        textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        button { background: #6366f1; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-size: 1rem; cursor: pointer; margin-top: 1rem; }
        button:hover { background: #4f46e5; }
        .response { white-space: pre-wrap; line-height: 1.6; color: #1f2937; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 0.375rem; }
        .usage { display: flex; gap: 1.5rem; flex-wrap: wrap; }
        .usage-item { background: #f9fafb; padding: 0.75rem 1rem; border-radius: 0.375rem; border: 1px solid #e5e7eb; }
        .usage-item span { display: block; font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .usage-item strong { font-size: 1.25rem; color: #111827; }
    </style>
</head>
<body>
    <div class="container">
        <h1>AI Chat</h1>

        <div class="card">
            <form action="/chat" method="POST">
                @csrf
                <label for="prompt">Your Prompt</label>
                <textarea id="prompt" name="prompt" placeholder="Ask me anything...">{{ old('prompt', $prompt ?? '') }}</textarea>
                @error('prompt')
                    <p style="color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
                <button type="submit">Send</button>
            </form>
        </div>

        @if(isset($error))
            <div class="card">
                <div class="error">
                    <strong>Error:</strong> {{ $error }}
                </div>
            </div>
        @endif

        @if(isset($response))
            <div class="card">
                <label>Response</label>
                <div class="response">{{ $response }}</div>
            </div>
        @endif

        @if(isset($usage))
            <div class="card">
                <label>Token Usage</label>
                <div class="usage">
                    <div class="usage-item">
                        <span>Prompt Tokens</span>
                        <strong>{{ $usage['prompt_tokens'] }}</strong>
                    </div>
                    <div class="usage-item">
                        <span>Completion Tokens</span>
                        <strong>{{ $usage['completion_tokens'] }}</strong>
                    </div>
                    <div class="usage-item">
                        <span>Total Tokens</span>
                        <strong>{{ $usage['total_tokens'] }}</strong>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
