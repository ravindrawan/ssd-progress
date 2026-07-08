<!-- Chatbot Floating UI Widget -->
<button id="cb-trigger-btn" aria-label="Open Chatbot" style="padding: 0; overflow: hidden; display: flex; align-items: center; justify-content: center;">
    <img src="images/chatbot_icon.jpg" alt="Chatbot Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
</button>

<div id="cb-container">
    <div id="cb-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div id="cb-avatar" style="overflow: hidden; padding: 0; display: flex; align-items: center; justify-content: center;">
                <img src="images/chatbot_icon.jpg" alt="Chatbot" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </div>
            <div>
                <div id="cb-title">සමාජ සේවා සහයක</div>
                <div id="cb-status"><span id="cb-status-dot"></span>Online (සිංහල/English)</div>
            </div>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button id="cb-clear-btn" title="සංවාදය මකන්න (Clear chat)" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: color 0.2s; padding: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </button>
            <button id="cb-minimize-btn" title="Minimize Chat" style="background: transparent; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; line-height: 1; transition: color 0.2s; padding: 0;">&times;</button>
        </div>
    </div>
    
    <div id="cb-body">
        <div id="cb-messages">
            <!-- Messages will be loaded dynamically -->
        </div>
        
        <!-- Suggestions/Quick Chips -->
        <div id="cb-chips-container">
            <!-- Suggested chips will load here -->
        </div>
    </div>
    
    <div id="cb-footer">
        <input type="text" id="cb-input" placeholder="ප්‍රශ්නයක් ඇතුළත් කරන්න... (Type here)" autocomplete="off">
        <button id="cb-lang-toggle" title="කතා කරන භාෂාව: සිංහල (Toggle voice language)" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.1); color: #8b5cf6; width: 38px; height: 38px; border-radius: 10px; cursor: pointer; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; outline: none;">SI</button>
        <button id="cb-mic-btn" title="කතා කරන්න (Voice input)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3z" />
            </svg>
        </button>
        <button id="cb-send-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
            </svg>
        </button>
    </div>
</div>

<style>
/* CSS scoped to Chatbot Widget to prevent bleeding */
#cb-trigger-btn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #3b82f6);
    border: none;
    cursor: pointer;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), 0 0 0 0px rgba(139, 92, 246, 0.4);
    z-index: 9999;
    transition: all 0.3s ease;
    animation: cbFloat 3s infinite alternate ease-in-out, cbPulse 2.5s infinite;
}

#cb-trigger-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 30px rgba(139, 92, 246, 0.6);
}

#cb-container {
    position: fixed;
    bottom: 95px;
    right: 25px;
    width: 380px;
    height: 520px;
    border-radius: 20px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(20px) scale(0.95);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

#cb-container.show {
    transform: translateY(0) scale(1);
    opacity: 1;
    pointer-events: auto;
}

#cb-header {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#cb-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #8b5cf6, #3b82f6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
}

#cb-title {
    color: white;
    font-weight: 600;
    font-size: 14px;
}

#cb-status {
    color: #94a3b8;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

#cb-status-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 6px #10b981;
}

#cb-minimize-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 22px;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}

#cb-minimize-btn:hover {
    color: white;
}

#cb-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 15px;
    overflow-y: hidden;
    background: radial-gradient(circle at top right, rgba(139, 92, 246, 0.05), transparent 60%);
}

#cb-messages {
    flex: 1;
    overflow-y: auto;
    padding-right: 5px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Custom Scrollbar for Chat */
#cb-messages::-webkit-scrollbar {
    width: 5px;
}
#cb-messages::-webkit-scrollbar-track {
    background: transparent;
}
#cb-messages::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
#cb-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

.cb-msg {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13.5px;
    line-height: 1.45;
    animation: cbFadeIn 0.3s ease forwards;
}

.cb-msg.bot {
    align-self: flex-start;
    background: rgba(255, 255, 255, 0.06);
    color: #e2e8f0;
    border-top-left-radius: 2px;
    border: 1px solid rgba(255, 255, 255, 0.03);
}

.cb-msg.user {
    align-self: flex-end;
    background: linear-gradient(135deg, #8b5cf6, #3b82f6);
    color: white;
    border-top-right-radius: 2px;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
}

/* Typing Indicator */
.cb-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 12px 16px !important;
}

.cb-typing span {
    width: 6px;
    height: 6px;
    background: #94a3b8;
    border-radius: 50%;
    display: inline-block;
    animation: cbBounce 1.4s infinite ease-in-out both;
}

.cb-typing span:nth-child(1) { animation-delay: -0.32s; }
.cb-typing span:nth-child(2) { animation-delay: -0.16s; }

#cb-chips-container {
    display: none !important;
    gap: 8px;
    overflow-x: auto;
    padding: 8px 0;
    margin-top: auto;
}

#cb-chips-container::-webkit-scrollbar {
    height: 0px;
}

.cb-chip {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.cb-chip:hover {
    background: rgba(139, 92, 246, 0.15);
    border-color: rgba(139, 92, 246, 0.4);
    color: white;
}

#cb-footer {
    padding: 12px 15px;
    background: rgba(0, 0, 0, 0.2);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    gap: 8px;
    align-items: center;
}

#cb-input {
    flex: 1;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    padding: 10px 14px;
    border-radius: 12px;
    outline: none;
    font-size: 13px;
    transition: all 0.2s;
}

#cb-input:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.15);
}

#cb-send-btn {
    background: #8b5cf6;
    border: none;
    color: white;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

#cb-send-btn:hover {
    background: #7c3aed;
    transform: translateY(-1px);
}

#cb-send-btn:active {
    transform: translateY(0);
}

#cb-mic-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

#cb-mic-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

#cb-mic-btn.recording {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
    animation: cbPulseRed 1.5s infinite;
}

.cb-speak-btn {
    background: transparent;
    border: none;
    color: #a5b4fc;
    cursor: pointer;
    font-size: 11px;
    margin-top: 5px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.cb-speak-btn:hover {
    opacity: 1;
    color: white;
}

@keyframes cbPulseRed {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Animations */
@keyframes cbFloat {
    0% { transform: translateY(0px); }
    100% { transform: translateY(-5px); }
}

@keyframes cbPulse {
    0% { box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), 0 0 0 0px rgba(139, 92, 246, 0.4); }
    70% { box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), 0 0 0 10px rgba(139, 92, 246, 0); }
    100% { box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), 0 0 0 0px rgba(139, 92, 246, 0); }
}

@keyframes cbFadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes cbBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1.0); }
}

@media (max-width: 480px) {
    #cb-container {
        width: calc(100% - 30px);
        right: 15px;
        left: 15px;
        bottom: 90px;
        height: 480px;
    }
    #cb-trigger-btn {
        right: 15px;
        bottom: 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggerBtn = document.getElementById('cb-trigger-btn');
    const container = document.getElementById('cb-container');
    const minimizeBtn = document.getElementById('cb-minimize-btn');
    const clearBtn = document.getElementById('cb-clear-btn');
    const sendBtn = document.getElementById('cb-send-btn');
    const inputField = document.getElementById('cb-input');
    const messagesContainer = document.getElementById('cb-messages');
    const chipsContainer = document.getElementById('cb-chips-container');
    const micBtn = document.getElementById('cb-mic-btn');
    const langToggleBtn = document.getElementById('cb-lang-toggle');
    
    let isInitialized = false;
    let currentInputLang = 'si-LK';

    // Toggle Chat Panel
    triggerBtn.addEventListener('click', function() {
        container.classList.toggle('show');
        if (container.classList.contains('show')) {
            inputField.focus();
            if (!isInitialized) {
                initChat();
            }
        }
    });

    minimizeBtn.addEventListener('click', function() {
        container.classList.remove('show');
    });

    clearBtn.addEventListener('click', function() {
        if (confirm("සංවාදය මකා දැමීමට ඔබට විශ්වාසද? (Are you sure you want to clear the conversation?)")) {
            messagesContainer.innerHTML = '';
            isInitialized = false;
            initChat();
        }
    });

    // Send Message on click
    sendBtn.addEventListener('click', handleSend);

    // Send Message on enter
    inputField.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            handleSend();
        }
    });

    function initChat() {
        isInitialized = true;
        // Initial bot greeting
        appendMessage('bot', `
            <div class='chat-message-group'>
                <p>🙋‍♂️ <strong>ආයුබෝවන්!</strong> මම සමාජ සේවා මාසික ප්‍රගති සහයක.</p>
                <p>වයඹ පළාතේ ප්‍රාදේශීය ලේකම් කාර්යාලවල ප්‍රතිපාදන, වියදම් සහ ප්‍රතිලාභීන්ගේ දත්ත සම්බන්ධයෙන් මගෙන් විමසන්න පුළුවන්.</p>
            </div>
        `);
        
        setChips([
            "📊 2026 සමස්ත ප්‍රගතිය",
            "🏆 වැඩිම වියදම් කළ කාර්යාලය",
            "📍 ප්‍රාදේශීය ලේකම් කාර්යාල",
            "💡 ආධාර කාණ්ඩ ලැයිස්තුව"
        ]);
    }

    function speakText(text) {
        if (!('speechSynthesis' in window)) {
            alert("කරුණාකර වෙනත් browser එකක් භාවිතා කරන්න (Speech synthesis not supported in this browser).");
            return;
        }
        window.speechSynthesis.cancel();
        
        const temp = document.createElement('div');
        temp.innerHTML = text;
        const speakBtn = temp.querySelector('.cb-speak-btn');
        if (speakBtn) speakBtn.remove();
        const cleanText = temp.textContent || temp.innerText || '';

        const utterance = new SpeechSynthesisUtterance(cleanText);
        const voices = window.speechSynthesis.getVoices();
        const siVoice = voices.find(v => v.lang.startsWith('si'));
        if (siVoice) {
            utterance.voice = siVoice;
        } else {
            utterance.lang = 'si-LK';
        }

        window.speechSynthesis.speak(utterance);
    }

    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `cb-msg ${sender}`;
        if (sender === 'bot') {
            msgDiv.innerHTML = text + `
                <div style="margin-top: 5px;">
                    <button class="cb-speak-btn" title="කතා කරන්න (Speak response)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 12px; height: 12px; vertical-align: middle; margin-right: 3px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
                        </svg>
                        අහන්න (Listen)
                    </button>
                </div>
            `;
            const btn = msgDiv.querySelector('.cb-speak-btn');
            btn.addEventListener('click', function() {
                speakText(text);
            });
        } else {
            msgDiv.innerHTML = text;
        }
        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
        return msgDiv;
    }

    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'cb-msg bot cb-typing';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
        return typingDiv;
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function setChips(chips) {
        chipsContainer.innerHTML = '';
        chips.forEach(chipText => {
            const chip = document.createElement('div');
            chip.className = 'cb-chip';
            chip.textContent = chipText;
            chip.addEventListener('click', function() {
                // Strip emojis for the actual query if needed, or send as is
                const queryText = chipText.replace(/[\uE000-\uF8FF]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|[\u2011-\u26FF]|\uD83E[\uDD10-\uDDFF]/g, '').trim();
                inputField.value = queryText;
                handleSend();
            });
            chipsContainer.appendChild(chip);
        });
    }

    async function handleSend() {
        const text = inputField.value.trim();
        if (!text) return;

        // Append user query
        appendMessage('user', text);
        inputField.value = '';

        // Show typing indicator
        const typingIndicator = showTypingIndicator();

        try {
            const response = await fetch('chat_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ query: text })
            });
            
            const data = await response.json();
            
            // Remove typing indicator
            typingIndicator.remove();
            
            // Append bot reply
            appendMessage('bot', data.reply);

            // Update host page dropdown and reload data/chart
            if (data.office_id) {
                const agOfficeSelect = document.getElementById('ag_office');
                if (agOfficeSelect) {
                    agOfficeSelect.value = data.office_id;
                    const event = new Event('change');
                    agOfficeSelect.dispatchEvent(event);
                }
            }
            
            // Update chips
            if (data.suggested_chips && data.suggested_chips.length > 0) {
                setChips(data.suggested_chips);
            }
        } catch (error) {
            console.error('Error fetching chatbot reply:', error);
            typingIndicator.remove();
            appendMessage('bot', '<p>❌ සමාවන්න, ප්‍රතිචාරය ලබාගැනීමේදී දෝෂයක් සිදු විය. කරුණාකර නැවත උත්සාහ කරන්න.</p>');
        }
    }

    // Speech Recognition
    let recognition = null;
    let isRecording = false;

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.lang = currentInputLang;
        recognition.interimResults = false;

        recognition.onstart = function() {
            isRecording = true;
            micBtn.classList.add('recording');
            if (currentInputLang === 'si-LK') {
                inputField.placeholder = "සිංහලෙන් අහගෙන ඉන්නේ... (Listening in Sinhala...)";
            } else {
                inputField.placeholder = "Listening in English...";
            }
        };

        recognition.onend = function() {
            isRecording = false;
            micBtn.classList.remove('recording');
            inputField.placeholder = "ප්‍රශ්නයක් ඇතුළත් කරන්න... (Type here)";
        };

        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            isRecording = false;
            micBtn.classList.remove('recording');
            inputField.placeholder = "ප්‍රශ්නයක් ඇතුළත් කරන්න... (Type here)";
        };

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            inputField.value = transcript;
            handleSend();
        };

        micBtn.addEventListener('click', function() {
            if (isRecording) {
                recognition.stop();
            } else {
                if (recognition) recognition.lang = currentInputLang;
                recognition.start();
            }
        });

        // Lang Toggle Logic
        langToggleBtn.addEventListener('click', function() {
            if (currentInputLang === 'si-LK') {
                currentInputLang = 'en-US';
                langToggleBtn.textContent = 'EN';
                langToggleBtn.style.color = '#3b82f6';
                langToggleBtn.title = "Input Language: English";
            } else {
                currentInputLang = 'si-LK';
                langToggleBtn.textContent = 'SI';
                langToggleBtn.style.color = '#8b5cf6';
                langToggleBtn.title = "කතා කරන භාෂාව: සිංහල";
            }
            if (recognition) recognition.lang = currentInputLang;
        });
    } else {
        if (micBtn) micBtn.style.display = 'none';
        if (langToggleBtn) langToggleBtn.style.display = 'none';
    }
});
</script>
