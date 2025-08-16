<x-filament-panels::page>
    <div class="flex flex-col h-full">
        @if ($otherUser)
            <!-- Chat Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center space-x-3">
                    @if ($otherUser->profile_picture)
                        <img src="{{ Storage::url($otherUser->profile_picture) }}"
                            alt="{{ $otherUser->name }}"
                            class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 dark:text-gray-300 font-medium">
                                {{ substr($otherUser->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $otherUser->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ ucfirst($otherUser->account_type) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                @foreach ($this->getMessages() as $message)
                    <div class="flex {{ $message->user_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                        <div
                            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message->user_id === Auth::id() ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' }}">
                            @if ($message->attachment_path)
                                @php
                                    $url = $message->attachment_path
                                        ? (file_exists(storage_path('app/public/' . $message->attachment_path))
                                            ? Storage::url($message->attachment_path)
                                            : env('ADMIN_DASHBOARD_URL') . '/storage/' . $message->attachment_path)
                                        : null;
                                @endphp

                                <div class="mt-2">
                                    @if ($message->message_type === 'image')
                                        <img src="{{ $url }}"
                                            alt="{{ $message->attachment_name ?? 'Image' }}"
                                            class="max-w-full h-auto rounded cursor-pointer"
                                            style="max-height: 200px;"
                                            onclick="openImageModal('{{ $url }}', '{{ $message->attachment_name ?? 'Image' }}')">
                                    @elseif($message->message_type === 'video')
                                        <video controls class="max-w-full h-auto rounded" style="max-height: 200px;">
                                            <source src="{{ $url }}" type="{{ $message->attachment_mime_type ?? 'video/mp4' }}">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <div class="flex items-center space-x-2 p-2 bg-gray-100 dark:bg-gray-600 rounded">
                                            <x-heroicon-o-document class="w-5 h-5" />
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium truncate">{{ $message->attachment_name ?? 'Document' }}</p>
                                                @if ($message->attachment_size)
                                                    <p class="text-xs text-gray-500">{{ $this->formatFileSize($message->attachment_size) }}</p>
                                                @endif
                                            </div>
                                            <a href="{{ $url }}"
                                                target="_blank"
                                                class="text-blue-500 hover:text-blue-700"
                                                title="Download {{ $message->attachment_name ?? 'Document' }}">
                                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($message->message)
                                <p class="text-sm">{{ $message->message }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs {{ $message->user_id === Auth::id() ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }} mt-1">
                                    {{ $message->created_at->format('H:i') }}
                                </p>
                                @if ($message->user_id === Auth::id())
                                    @if ($message->is_read)
                                        <span class="text-xs text-green-500">✓✓</span>
                                    @else
                                        <span class="text-xs text-gray-500">✓</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4">
                <!-- File Preview -->
                @if ($attachment)
                    <div class="mb-3 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-paper-clip class="w-4 h-4 text-gray-500" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $attachment->getClientOriginalName() }}</span>
                                <span class="text-xs text-gray-500">({{ $this->formatFileSize($attachment->getSize()) }})</span>
                            </div>
                            <button type="button" wire:click="$set('attachment', null)" class="text-red-500 hover:text-red-700">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage" class="flex space-x-2">
                    <div class="flex-1">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                wire:model="message"
                                placeholder="Type your message..."
                                class="w-full" />
                        </x-filament::input.wrapper>

                        @error('attachment')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Upload Button -->
                    <x-filament::button type="button" color="gray" onclick="document.getElementById('file-upload').click()">
                        <x-heroicon-o-paper-clip class="w-4 h-4" />
                    </x-filament::button>
                    <input id="file-upload" type="file" wire:model="attachment" class="hidden"
                        accept="image/*,video/*,.pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx">

                    <x-filament::button type="submit" color="primary">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                    </x-filament::button>
                </form>
            </div>
        @else
            <!-- No User Selected -->
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No user selected
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Please select a user to start chatting
                    </p>
                </div>
            </div>
        @endif
    </div>

</x-filament-panels::page>

@push('scripts')
    <script>
        // Auto-scroll to bottom when new messages are sent
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('message-sent', () => {
                const container = document.getElementById('messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });

            Livewire.on('messages-refreshed', () => {
                const container = document.getElementById('messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        });

        // Auto-scroll on page load
        window.addEventListener('load', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Auto-refresh messages every 5 seconds
        setInterval(() => {
            @this.refreshMessages();
        }, 5000);

        // Image modal functionality
        function openImageModal(src, alt) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
            <div class="relative max-w-4xl max-h-full p-4">
                <button onclick="this.parentElement.parentElement.remove()" class="absolute top-2 right-2 text-white text-2xl hover:text-gray-300 z-10">
                    ×
                </button>
                <img src="${src}" alt="${alt}" class="max-w-full max-h-full object-contain">
            </div>
        `;
            modal.onclick = function(e) {
                if (e.target === modal) modal.remove();
            };
            document.body.appendChild(modal);
        }
    </script>
@endpush
