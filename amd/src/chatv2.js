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
        let html = text;

        // 1. Convert URLs to clickable buttons/links
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        html = html.replace(urlRegex, function (url) {
          return `<a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 d-inline-block">View Course <i class="fa fa-external-link ms-1"></i></a>`;
        });

        // 2. Convert **bold** to <strong>
        html = html.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

        // 3. Convert * bullet points to list items (handle multi-line)
        if (html.includes("* ")) {
          html = html.replace(/^\* (.*)/gm, "<li>$1</li>");
          // Wrap bullet lines in a <ul> if they exist
          html = html.replace(
            /(<li>.*<\/li>)/s,
            '<ul class="ps-3 mb-0">$1</ul>',
          );
        }

        // 4. Convert newlines to breaks for general spacing
        html = html.replace(/\n/g, "<br>");

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
