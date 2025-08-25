<x-filament-panels::page>
    <div class="flex flex-col h-full">
        @php
            if (Auth::user()->account_type === 'user') {
                $currentUser = Auth::user()->parent;
            } else {
                $currentUser = Auth::user();
            }
        @endphp
        @if ($otherUser)
            <!-- Chat Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
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
                                         <div class="flex items-center space-x-2">
                         <button 
                             id="toggle-realtime" 
                             onclick="toggleRealTime()"
                             class="inline-flex items-center px-3 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                             title="Toggle real-time updates"
                         >
                             <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                             </svg>
                             <span id="realtime-status">Live</span>
                         </button>
                         <button 
                             id="manual-refresh" 
                             onclick="manualRefresh()"
                             class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                             title="Manual refresh"
                             style="display: none;"
                         >
                             <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                             </svg>
                             Refresh
                         </button>
                     </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                @foreach ($this->getMessages() as $message)
                    @php
                        $isMe = $currentUser->id === $message->user_id;
                    @endphp
                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div
                            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $isMe ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' }}">
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
                                <p id="chat_message" class="text-sm">{{ $message->message }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs {{ $isMe ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }} mt-1">
                                    {{ $message->created_at->format('H:i') }}
                                </p>
                                @if ($isMe)
                                    @if ($message->is_read)
                                        <span class="text-xs text-green-500">✓✓</span>
                                    @else
                                        <span class="text-xs text-gray-500">✓</span>
                                    @endif
                                @endif
                            </div>

                            @if (!$isMe && $message->message)
                                <div class="mt-2">
                                    <button 
                                        type="button" 
                                        onclick="translateMessage({{ $message->id }}, '{{ addslashes($message->message) }}')"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                        </svg>
                                        Translate
                                    </button>
                                    <div id="translation-{{ $message->id }}" class="mt-1 text-sm text-gray-600 dark:text-gray-400 hidden"></div>
                                </div>
                            @endif
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
        function translateMessage(messageId, originalText) {
            const button = event.target;
            const translationDiv = document.getElementById(`translation-${messageId}`);
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Translating...';

            // Show translation div
            translationDiv.classList.remove('hidden');
            translationDiv.innerHTML = '<div class="text-sm text-gray-500">Translating...</div>';

            // Make AJAX request to backend
            fetch('/translate-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message_id: messageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store translation data as attributes for toggle functionality
                    translationDiv.setAttribute('data-translation', data.translated_text);
                    translationDiv.setAttribute('data-source-lang', data.source_language);
                    translationDiv.setAttribute('data-target-lang', data.target_language);
                    
                    translationDiv.innerHTML = `
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border-l-4 border-blue-500">
                            <div class="text-xs text-gray-500 mb-1">${data.source_language} → ${data.target_language}:</div>
                            <div class="text-sm">${data.translated_text}</div>
                            <button onclick="toggleOriginal(${messageId}, '${originalText.replace(/'/g, "\\'")}')" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                Show/Hide Original
                            </button>
                        </div>
                    `;
                    
                    // Save translation state for persistence during refresh
                    saveTranslationStates();
                } else {
                    translationDiv.innerHTML = `<div class="text-sm text-red-500">Translation failed: ${data.error}</div>`;
                }
            })
            .catch(error => {
                console.error('Translation error:', error);
                translationDiv.innerHTML = '<div class="text-sm text-red-500">Translation failed. Please try again.</div>';
            })
            .finally(() => {
                // Reset button state
                button.disabled = false;
                button.innerHTML = `
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    Translate
                `;
            });
        }
        
        function toggleOriginal(messageId, originalText) {
            const translationDiv = document.getElementById(`translation-${messageId}`);
            const currentContent = translationDiv.innerHTML;
            
            if (currentContent.includes('Original:')) {
                // Show translation - get the stored translation text
                const translationText = translationDiv.getAttribute('data-translation') || 'Translation not available';
                const sourceLang = translationDiv.getAttribute('data-source-lang') || '';
                const targetLang = translationDiv.getAttribute('data-target-lang') || '';
                
                translationDiv.innerHTML = `
                    <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border-l-4 border-blue-500">
                        <div class="text-xs text-gray-500 mb-1">${sourceLang} → ${targetLang}:</div>
                        <div class="text-sm">${translationText}</div>
                        <button onclick="toggleOriginal(${messageId}, '${originalText.replace(/'/g, "\\'")}')" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                            Show/Hide Original
                        </button>
                    </div>
                `;
            } else {
                // Show original
                translationDiv.innerHTML = `
                    <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded border-l-4 border-gray-500">
                        <div class="text-xs text-gray-500 mb-1">Original:</div>
                        <div class="text-sm">${originalText}</div>
                        <button onclick="toggleOriginal(${messageId}, '${originalText.replace(/'/g, "\\'")}')" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                            Show/Hide Translation
                        </button>
                    </div>
                `;
            }
            
            // Save translation state for persistence during refresh
            saveTranslationStates();
        }

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
                // Restore translation states immediately after refresh
                restoreTranslationStates();
            });
        });

        // Auto-scroll on page load
        window.addEventListener('load', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Store translation states
        let translationStates = new Map();
        let refreshInterval;

        // Function to save translation states before refresh
        function saveTranslationStates() {
            translationStates.clear();
            const translationDivs = document.querySelectorAll('[id^="translation-"]');
            translationDivs.forEach(div => {
                if (!div.classList.contains('hidden') && div.innerHTML.trim() !== '') {
                    const messageId = div.id.replace('translation-', '');
                    translationStates.set(messageId, {
                        html: div.innerHTML,
                        translation: div.getAttribute('data-translation'),
                        sourceLang: div.getAttribute('data-source-lang'),
                        targetLang: div.getAttribute('data-target-lang')
                    });
                }
            });
        }

        // Function to restore translation states after refresh
        function restoreTranslationStates() {
            translationStates.forEach((state, messageId) => {
                const div = document.getElementById(`translation-${messageId}`);
                if (div) {
                    div.classList.remove('hidden');
                    div.innerHTML = state.html;
                    if (state.translation) {
                        div.setAttribute('data-translation', state.translation);
                        div.setAttribute('data-source-lang', state.sourceLang);
                        div.setAttribute('data-target-lang', state.targetLang);
                    }
                }
            });
        }

        // Start auto-refresh
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                saveTranslationStates();
                @this.refreshMessages();
            }, 5000);
        }

        // Stop auto-refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // Check if user has disabled real-time updates
        const realtimeDisabled = localStorage.getItem('chat-realtime-disabled') === 'true';
        
        // Start auto-refresh initially (unless disabled)
        if (!realtimeDisabled) {
            startAutoRefresh();
        } else {
            // Update button to show disabled state
            const button = document.getElementById('toggle-realtime');
            const status = document.getElementById('realtime-status');
            const manualRefreshBtn = document.getElementById('manual-refresh');
            
            if (button && status) {
                button.className = 'inline-flex items-center px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2';
                status.textContent = 'Paused';
                button.title = 'Enable real-time updates';
            }
            
            // Show manual refresh button
            if (manualRefreshBtn) {
                manualRefreshBtn.style.display = 'inline-flex';
            }
        }

        // Use MutationObserver to detect DOM changes and restore translations immediately
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Check if any translation divs were added back
                        const hasTranslationDivs = Array.from(mutation.addedNodes).some(node => 
                            node.nodeType === 1 && node.querySelector && node.querySelector('[id^="translation-"]')
                        );
                        
                        if (hasTranslationDivs) {
                            // Small delay to ensure DOM is fully updated
                            requestAnimationFrame(() => {
                                restoreTranslationStates();
                            });
                        }
                    }
                });
            });

            observer.observe(messagesContainer, {
                childList: true,
                subtree: true
            });
        }

        // Toggle real-time updates
        function toggleRealTime() {
            const button = document.getElementById('toggle-realtime');
            const status = document.getElementById('realtime-status');
            const manualRefreshBtn = document.getElementById('manual-refresh');
            
            if (refreshInterval) {
                // Stop real-time updates
                stopAutoRefresh();
                button.className = 'inline-flex items-center px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2';
                status.textContent = 'Paused';
                button.title = 'Enable real-time updates';
                localStorage.setItem('chat-realtime-disabled', 'true');
                
                // Show manual refresh button
                if (manualRefreshBtn) {
                    manualRefreshBtn.style.display = 'inline-flex';
                }
            } else {
                // Start real-time updates
                startAutoRefresh();
                button.className = 'inline-flex items-center px-3 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2';
                status.textContent = 'Live';
                button.title = 'Disable real-time updates';
                localStorage.setItem('chat-realtime-disabled', 'false');
                
                // Hide manual refresh button
                if (manualRefreshBtn) {
                    manualRefreshBtn.style.display = 'none';
                }
            }
        }

        // Manual refresh function
        function manualRefresh() {
            const button = document.getElementById('manual-refresh');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Refreshing...';
            }
            
            @this.refreshMessages();
            
            setTimeout(() => {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refresh';
                }
            }, 1000);
        }

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
