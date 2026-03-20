define(["core/ajax", "core/notification"], function (Ajax, Notification) {
  return {
    init: function (instanceId, courseId) {
      const inputField = document.getElementById(`ai-input-${instanceId}`);
      const sendBtn = document.getElementById(`ai-send-${instanceId}`);
      const history = document.getElementById(`ai-history-${instanceId}`);
      const spinner = document.getElementById(`ai-btn-spinner-${instanceId}`);
      const btnText = document.getElementById(`ai-btn-text-${instanceId}`);

      /**
       * Formats raw text from Gemini into clean HTML.
       * Handles Bold, Lists, and Clickable Links.
       */
      const formatResponse = (text) => {
        // 1. Zuerst den Text bereinigen und Zeilenumbrüche in normales HTML umwandeln
        let html = text.trim().replace(/\n/g, "<br>");

        // 2. Standard-Markdown für Fettgedrucktes
        html = html.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

        // 3. Den Button-Marker suchen (toleranter gegenüber Leerzeichen)
        const buttonRegex = /\[BUTTON:(.*?)\|(.*?)\]/g;

        html = html.replace(buttonRegex, function (match, courseName, url) {
          // Entfernt alle versehentlichen Leerzeichen aus der URL (z.B. "https:// cati...")
          const cleanUrl = url.replace(/\s+/g, '').trim();
          
          return `<div class="button-container mt-2">
                    <a href="${cleanUrl}" target="_blank" class="btn btn-outline-primary custom-nav-btn rounded-pill px-4 shadow-sm fw-bold">
                        ${courseName} <i class="fa fa-external-link ms-1"></i>
                    </a>
                </div>`;
        }); 

        // 4. Bereinigung: Falls die KI noch alte Markdown-Links [Name](URL) sendet,
        // entfernen wir die Klammern, um doppelten Text zu vermeiden.
        html = html.replace(/\[(.*?)\]\((.*?)\)/g, "$1");

        return html;
      };

      /**
       * Adds a message bubble to the history area.
       */
      // Inside your init function in chatv2.js
      const addMessage = (content, isUser) => {
        history.classList.remove("d-none");

        if (isUser) {
          // Clear everything for a fresh start
          history.innerHTML = "";
          const div = document.createElement("div");
          div.className = "user-query-label mb-2 text-muted small italic";
          div.innerText = `Ihre Anfrage: "${content}"`;
          history.appendChild(div);
        } else {
          // Create the bordered response container
          const div = document.createElement("div");
          div.className = "ai-response-card p-4 shadow-sm";
          div.innerHTML = formatResponse(content);
          history.appendChild(div);
        }
        history.scrollTop = 0; // Scroll to top to show the new answer
      };

      const performSearch = () => {
        const query = inputField.value.trim();
        if (!query) return;

        // Safety check: ensure elements exist before touching classList
        if (spinner) spinner.classList.remove("d-none");
        if (btnText) btnText.innerText = "Identifiziere relevante Inhalte...";

        inputField.value = "";
        addMessage(query, true);
        sendBtn.disabled = true;

        Ajax.call([
          {
            methodname: "block_ask_ai_get_response",
            args: { courseid: courseId, query: query },
          },
        ])[0]
          .then(function (data) {
            addMessage(data.answer, false);
          })
          .fail(Notification.exception)
          .always(() => {
            sendBtn.disabled = false;
            // Safety check again
            if (spinner) spinner.classList.add("d-none");
            if (btnText) btnText.innerText = "Erschließen";
          });
      };

      // Event Listeners
      sendBtn.addEventListener("click", function (e) {
        e.preventDefault();
        performSearch();
      });

      inputField.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
          e.preventDefault();
          performSearch();
        }
      });
    },
  };
});
