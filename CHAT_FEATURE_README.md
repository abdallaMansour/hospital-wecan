# Chat Feature Documentation

## Overview
This feature allows doctors and patients to chat with each other through a custom chat interface integrated into the Filament admin panel.

## Features

### Chat Buttons
- **DoctorResource**: Each doctor/patient in the list has a chat button that opens a chat with the authenticated user
- **UserResource**: Each user in the list has a chat button for easy access to chat
- **ChatResource**: Existing chats can be opened directly from the chat list

### Chat Interface
- **Modern UI**: Clean, responsive chat interface with message bubbles
- **Real-time Updates**: Messages are automatically refreshed every 5 seconds
- **Auto-scroll**: New messages automatically scroll to the bottom
- **User Profiles**: Shows profile pictures and user information
- **Message Timestamps**: Each message shows the time it was sent

### Smart Chat Room Management
- **Automatic Creation**: Chat rooms are created automatically when needed
- **Duplicate Prevention**: Prevents creating multiple chat rooms between the same users
- **Role-based Access**: Doctors can chat with patients and other doctors, patients can chat with doctors

## How to Use

### For Doctors
1. Navigate to "Doctors & Patients" in the admin panel
2. Click the chat button (💬) next to any patient or doctor
3. The chat interface will open in a new tab
4. Start typing and press Enter or click the send button

### For Patients
1. Navigate to "Users" in the admin panel
2. Click the chat button (💬) next to any doctor
3. The chat interface will open in a new tab
4. Start typing and press Enter or click the send button

### Accessing Existing Chats
1. Navigate to "Chats" in the admin panel
2. Click the chat button (💬) next to any existing chat
3. The chat interface will open with the conversation history

**Note**: The custom chat page is only accessible through chat action buttons and is not visible in the sidebar navigation.

## Technical Implementation

### Files Modified/Created
- `app/Filament/Resources/DoctorResource.php` - Added chat action button
- `app/Filament/Resources/UserResource.php` - Added chat action button
- `app/Filament/Resources/ChatResource.php` - Updated form and table for better chat management
- `app/Filament/Resources/ChatResource/Pages/CreateChat.php` - Enhanced to handle URL parameters
- `app/Filament/Resources/ChatResource/Pages/CustomChatPage.php` - New custom chat page
- `resources/views/filament/pages/custom-chat.blade.php` - Chat interface view
- `app/Providers/Filament/AdminPanelProvider.php` - Registered custom chat page
- `lang/en/dashboard.php` - Added chat translations
- `lang/ar/dashboard.php` - Added Arabic chat translations

### Database
- Uses existing `chat_rooms` and `chat_messages` tables
- Automatically creates chat rooms when needed
- Prevents duplicate chat rooms between the same users

### Security
- Users can only chat with users they have access to
- Chat rooms are scoped to the user's hospital
- Users cannot chat with themselves

## Future Enhancements
- File attachments in messages
- Read receipts
- Typing indicators
- Push notifications
- Message search functionality
- Chat room management (delete, archive)
