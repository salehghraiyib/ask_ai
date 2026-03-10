define(['core/ajax', 'core/notification', 'core/templates'], function(Ajax, Notification, Templates) {
    return {
        init: function(instanceId, courseId) {
            const inputField = document.getElementById(`ai-input-${instanceId}`);
            const sendBtn = document.getElementById(`ai-send-${instanceId}`);
            const history = document.getElementById(`ai-history-${instanceId}`);
            const spinner = document.getElementById(`ai-btn-spinner-${instanceId}`);
            const btnText = document.getElementById(`ai-btn-text-${instanceId}`);

            const addMessage = (text, isUser) => {
                const div = document.createElement('div');
                div.className = isUser ? 'text-end mb-2' : 'text-start mb-2';
                const inner = document.createElement('span');
                inner.className = isUser ? 'badge bg-primary text-wrap text-start p-2' : 'badge bg-secondary text-wrap text-start p-2';
                inner.style.maxWidth = '80%';
                inner.innerText = text;
                div.appendChild(inner);
                history.appendChild(div);
                history.scrollTop = history.scrollHeight;
            };

            sendBtn.addEventListener('click', () => {
                const query = inputField.value.trim();
                if (!query) return;

                inputField.value = '';
                addMessage(query, true);
                sendBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.classList.add('d-none');

                Ajax.call([{
                    methodname: 'block_ask_ai_get_response',
                    args: {
                        courseid: courseId,
                        query: query
                    }
                }])[0].then(function(data) {
                    addMessage(data.answer, false);
                }).fail(Notification.exception).always(() => {
                    sendBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.classList.remove('d-none');
                });
            });
        }
    };
});