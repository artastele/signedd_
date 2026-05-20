<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — Claude AI Configuration

// Claude API Key
// Get your API key from: https://console.anthropic.com/
// Set in .env file as: CLAUDE_API_KEY=your-actual-api-key
$claudeApiKey = getenv('CLAUDE_API_KEY') ?: '';

// Check if API key is configured
if (empty($claudeApiKey) || $claudeApiKey === 'your-api-key-here') {
    define('CLAUDE_API_KEY', null);
    define('CLAUDE_API_ENABLED', false);
} else {
    define('CLAUDE_API_KEY', $claudeApiKey);
    define('CLAUDE_API_ENABLED', true);
}

// Claude API Configuration
define('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages');
define('CLAUDE_MODEL', 'claude-sonnet-4-20250514');
define('CLAUDE_MAX_TOKENS', 4096);
