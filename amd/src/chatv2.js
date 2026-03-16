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
      const addMessage = (content, isUser) => {
        history.classList.remove("d-none");
        const div = document.createElement("div");

        if (isUser) {
          div.className = "user-query shadow-sm";
          div.innerText = content;
        } else {
          div.className = "ai-reply";
          div.innerHTML = formatResponse(content); // Using the formatter from before
        }

        history.appendChild(div);
        history.scrollTop = history.scrollHeight;
      };

      /**
       * Sends the query to the Moodle external function.
       */
      const performSearch = () => {
        const query = inputField.value.trim();
        if (!query) return;

        // 1. Set the loading text inside the button or a separate status div
        const loadingText = "Identifiziere relevante Inhalte...";

        // Update the button text to show the agent is thinking
        btnText.innerText = loadingText;

        // UI State: Reset and show loading spinner
        inputField.value = "";
        addMessage(query, true); // Add user query to history

        sendBtn.disabled = true;
        spinner.classList.remove("d-none");

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
            // Reset the button to its original state
            sendBtn.disabled = false;
            spinner.classList.add("d-none");
            btnText.innerText = "Erschließen"; // Back to original German CTA
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
