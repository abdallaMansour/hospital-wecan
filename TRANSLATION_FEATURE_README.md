# Chat Translation Feature

## Overview
The chat translation feature allows users to translate messages in real-time using Google Translate API. The feature automatically detects the source language and translates between English and Arabic.

## Features
- **Automatic Language Detection**: Detects if the message is in English or Arabic using Unicode character patterns
- **Bidirectional Translation**: Translates from English to Arabic and vice versa
- **Real-time Translation**: Translates messages instantly without page refresh
- **Toggle Functionality**: Users can switch between original and translated text
- **Translation Persistence**: Translations are preserved during real-time message refreshes
- **Real-time Toggle**: Users can enable/disable real-time updates to prevent translation loss
- **Error Handling**: Graceful error handling with user-friendly messages

## Implementation Details

### Backend (PHP)
- **File**: `app/Filament/Resources/ChatResource/Pages/CustomChatPage.php`
- **Method**: `translateMessage(Request $request)`
- **Route**: `POST /translate-message`
- **Package**: `stichoza/google-translate-php`

### Frontend (JavaScript)
- **File**: `resources/views/filament/pages/custom-chat.blade.php`
- **Functions**: 
  - `translateMessage(messageId, originalText)` - Main translation function
  - `toggleOriginal(messageId, originalText)` - Toggle between original and translation
  - `toggleRealTime()` - Toggle real-time updates
  - `saveTranslationStates()` - Save translation states before refresh
  - `restoreTranslationStates()` - Restore translation states after refresh
- **Features**:
  - Loading state with spinner
  - AJAX request to backend
  - Error handling
  - Toggle between original and translated text
  - Translation state persistence during real-time refreshes
  - Real-time update toggle control

### Language Detection
The system uses Unicode character patterns to detect Arabic text:
- Arabic Unicode range: `[\x{0600}-\x{06FF}]`
- If Arabic characters are found, translates to English
- Otherwise, translates to Arabic

### API Response Format
```json
{
    "success": true,
    "translated_text": "Translated message",
    "original_text": "Original message",
    "source_language": "English",
    "target_language": "العربية"
}
```

## Usage
1. In the chat interface, click the "Translate" button on any message
2. The system will automatically detect the language and translate
3. Use the "Show/Hide Original" button to toggle between original and translated text
4. The translation is displayed in a styled box with language indicators
5. Use the "Live/Paused" toggle in the chat header to control real-time updates
6. Translations are automatically preserved during message refreshes

## Error Handling
- Network errors show "Translation failed. Please try again."
- API errors show the specific error message
- Missing messages show "Message not found"

## Dependencies
- `stichoza/google-translate-php`: ^5.3 (already installed)
- CSRF protection enabled
- Authentication required for translation endpoint

## Security
- Route protected with `auth` middleware
- CSRF token validation
- Input sanitization for message content
