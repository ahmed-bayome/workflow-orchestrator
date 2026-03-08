#!/bin/bash

# Get the script's directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "🚀 Starting Workflow Orchestrator in macOS Terminal Tabs..."

# AppleScript to open a new terminal window with 4 tabs
osascript <<EOF
tell application "Terminal"
    # Tab 1: API
    do script "cd '$DIR/backend' && echo -n -e '\033]0;Orchestrator: API\007' && php -S localhost:8000 -t public"
    
    # Tab 2: Queue
    tell application "System Events" to keystroke "t" using command down
    do script "cd '$DIR/backend' && echo -n -e '\033]0;Orchestrator: Queue\007' && php artisan queue:work" in front window
    
    # Tab 3: Reverb
    tell application "System Events" to keystroke "t" using command down
    do script "cd '$DIR/backend' && echo -n -e '\033]0;Orchestrator: Reverb\007' && php artisan reverb:start" in front window
    
    # Tab 4: Frontend
    tell application "System Events" to keystroke "t" using command down
    do script "cd '$DIR/frontend' && echo -n -e '\033]0;Orchestrator: Frontend\007' && npm run dev -- --open" in front window
end tell
EOF

echo "✅ All tabs opened!"
