const form = document.getElementById("chatForm");
const messageInput = document.getElementById("message");
const receiverInput = document.getElementById("receiver_id");
const chatBody = document.getElementById("chatBody");
const fileInput = document.getElementById("fileInput");
const filePreview = document.getElementById("filePreview");
const filePreviewName = document.getElementById("filePreviewName");
const fileCancelBtn = document.getElementById("fileCancelBtn");
const sendBtn = document.getElementById("sendBtn");

// ============================================================
// Scroll ke bawah otomatis
// ============================================================
function scrollBottom() {
    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
}
scrollBottom();

// ============================================================
// Format ukuran file
// ============================================================
function formatFileSize(bytes) {
    if (!bytes) return "";
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

// ============================================================
// Render bubble pesan (teks, gambar, atau file)
// ============================================================
function addMessage(data, mine = false) {
    if (!chatBody) return;

    const wrapper = document.createElement("div");
    wrapper.className = mine ? "message-sent" : "message-received";

    const bubble = document.createElement("div");
    bubble.className = mine ? "bubble-sent" : "bubble-received";

    // Render berdasarkan tipe pesan
    if (data.message_type === "image" && data.file_path) {
        const img = document.createElement("img");
        img.src = data.file_path;
        img.alt = data.file_name ?? "Gambar";
        img.className = "chat-image";
        // Klik gambar untuk buka preview besar
        img.style.cursor = "pointer";
        img.addEventListener("click", () => {
            window.open(data.file_path, "_blank");
        });
        bubble.appendChild(img);

        // Tampilkan teks caption jika ada
        if (data.message && data.message.trim() !== "") {
            const caption = document.createElement("p");
            caption.className = "mb-0 mt-1";
            caption.innerText = data.message;
            bubble.appendChild(caption);
        }
    } else if (data.message_type === "file" && data.file_path) {
        const link = document.createElement("a");
        link.href = data.file_path;
        link.target = "_blank";
        link.download = data.file_name ?? "file";
        link.className = mine ? "file-link-sent" : "file-link-received";
        link.innerHTML = `📄 <span>${data.file_name ?? "Download File"}</span>
                          <small class="d-block">${formatFileSize(data.file_size)}</small>`;
        bubble.appendChild(link);
    } else {
        // Pesan teks biasa
        bubble.innerText = data.message;
    }

    const br = document.createElement("br");
    const time = document.createElement("small");
    time.className = "message-time";
    const date = new Date(data.created_at);
    time.innerText =
        date.getHours().toString().padStart(2, "0") +
        ":" +
        date.getMinutes().toString().padStart(2, "0");

    wrapper.appendChild(bubble);
    wrapper.appendChild(br);
    wrapper.appendChild(time);
    chatBody.appendChild(wrapper);
    scrollBottom();
}

// ============================================================
// Preview file sebelum dikirim
// ============================================================
if (fileInput) {
    fileInput.addEventListener("change", function () {
        const file = this.files[0];
        if (!file) return;

        if (filePreviewName)
            filePreviewName.textContent = `📎 ${file.name} (${formatFileSize(file.size)})`;
        if (filePreview) filePreview.classList.remove("d-none");
    });
}

if (fileCancelBtn) {
    fileCancelBtn.addEventListener("click", function () {
        if (fileInput) fileInput.value = "";
        if (filePreview) filePreview.classList.add("d-none");
        if (filePreviewName) filePreviewName.textContent = "";
    });
}

// ============================================================
// Kirim pesan via AJAX (support teks + file)
// ============================================================
if (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const hasText = messageInput && messageInput.value.trim() !== "";
        const hasFile = fileInput && fileInput.files.length > 0;

        if (!hasText && !hasFile) return;

        const btn = sendBtn || form.querySelector('button[type="submit"]');
        btn.disabled = true;

        // Gunakan FormData agar bisa kirim file
        const formData = new FormData();
        formData.append("receiver_id", receiverInput.value);
        if (hasText) formData.append("message", messageInput.value);
        if (hasFile) formData.append("file", fileInput.files[0]);

        axios
            .post("/chat/send", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            })
            .then((response) => {
                addMessage(response.data, true);
                if (messageInput) messageInput.value = "";
                if (fileInput) fileInput.value = "";
                if (filePreview) filePreview.classList.add("d-none");
                if (filePreviewName) filePreviewName.textContent = "";
                if (messageInput) messageInput.focus();
            })
            .catch((error) => {
                console.error("Gagal kirim pesan:", error);
                alert("Gagal mengirim pesan, coba lagi.");
            })
            .finally(() => {
                btn.disabled = false;
            });
    });
}

// ============================================================
// WebSocket — terima pesan realtime
// ============================================================
if (window.authUserId) {
    window.Echo.private("chat." + window.authUserId).listen(
        ".MessageSent",
        (e) => {
            console.log("[WebSocket] Pesan masuk:", e);

            const currentReceiverId = receiverInput
                ? parseInt(receiverInput.value)
                : null;
            if (
                currentReceiverId &&
                parseInt(e.sender_id) === currentReceiverId
            ) {
                addMessage(e, false);
            }
        },
    );
}
