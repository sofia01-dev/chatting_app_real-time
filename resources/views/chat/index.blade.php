@extends('layouts.app')

@section('title', 'Quick Chat')

@section('content')
<div class="container-fluid p-0">

    {{-- NAVBAR --}}
    <div class="top-navbar">
        <div class="navbar-title">
        <i class="bi bi-chat-dots-fill"></i>
        <span>Quick Chat</span>
    </div>

        <div class="navbar-user">
            <div class="text-end me-2">
            <div class="fw-bold">
                {{ auth()->user()->name }}
            </div>

            <small class="user-status">
                <span class="status-dot online"></span>
                Online
            </small>
        </div>

            <div
                class="user-avatar-lg"
                style="cursor:pointer"
                data-bs-toggle="modal"
                data-bs-target="#profileModal">

                @if(auth()->user()->avatar)

                    <img
                        src="{{ asset('storage/'.auth()->user()->avatar) }}"
                        style="width:100%;height:100%;border-radius:50%;object-fit:cover;">

                @else

                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}

                @endif

            </div>

            <form action="/logout" method="POST">
                @csrf

                <button class="btn btn-secondary logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
    
<div class="row g-0">
    {{-- ══ SIDEBAR ══ --}}
    <div class="col-md-3 sidebar">
    <div class="position-relative">
        <i class="bi bi-search search-icon"></i>
    <input
        type="text"
        id="searchUser"
        class="search-box"
        placeholder="Cari user...">
    </div>
        <div class="px-1 mt-1">
            @foreach($users as $user)
            <a href="/chat/{{ $user->id }}"
               class="user-item user-search @if(isset($selectedUser) && $selectedUser->id == $user->id) user-active @endif" data-name="{{ strtolower($user->name) }}">
                <div class="user-avatar-sm">

            @if($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" style=" width:100%;height:100%;border-radius:50%;object-fit:cover ">
            @else
                {{ strtoupper(substr($user->name,0,1)) }}
            @endif
        </div>

        <div>
    <div class="user-name fw-semibold" style="font-size:14px">
        {{ $user->name }}
    </div>

    <small class="user-status">
        <span class="status-dot {{ $user->is_online ? 'online' : 'offline' }}"></span>

        {{ $user->is_online ? 'Online' : 'Offline' }}
    </small>
</div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ══ CHAT AREA ══ --}}
    <div class="col-md-9 chat-area">
    @if(isset($selectedUser))

        {{-- ── Header ── --}}
        <div class="chat-header">

            {{-- Kiri: info penerima pesan --}}
            <div class="d-flex align-items-center gap-3">
                <div class="user-avatar-lg">

                @if($selectedUser->avatar)
                    <img src="{{ asset('storage/'.$selectedUser->avatar) }}" style=" width:100%; height:100%; border-radius:50%; object-fit:cover;">
                @else
                    {{ strtoupper(substr($selectedUser->name,0,1)) }}
                @endif
            </div>

                <div>
                    <div class="fw-bold" style="font-size:15px;color:#0E2F76">{{ $selectedUser->name }}</div>
                    <small class="user-status">
                        <span class="status-dot {{ $selectedUser->is_online ? 'online' : 'offline' }}"></span>
                        {{ $selectedUser->is_online ? 'Online' : 'Offline' }}
                    </small>
                </div>
            </div>
        </div>

        {{-- ── Isi Chat ── --}}
        <div class="chat-body" id="chatBody">
            @foreach($messages as $message)
            @php $mine = $message->sender_id == auth()->id(); @endphp
            <div class="{{ $mine ? 'message-sent' : 'message-received' }}">
                <div class="{{ $mine ? 'bubble-sent' : 'bubble-received' }}">
                    @if($message->message_type === 'image' && $message->file_path)
                        <img src="{{ asset('storage/' . $message->file_path) }}"
                             alt="{{ $message->file_name }}"
                             class="chat-image"
                             onclick="window.open(this.src,'_blank')">
                        @if($message->message)
                            <p class="mb-0 mt-1" style="font-size:14px">{{ $message->message }}</p>
                        @endif
                    @elseif($message->message_type === 'file' && $message->file_path)
                        <a href="{{ asset('storage/' . $message->file_path) }}"
                           target="_blank"
                           download="{{ $message->file_name }}"
                           class="{{ $mine ? 'file-link-sent' : 'file-link-received' }}">
                            <span class="file-icon">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </span>
                            <div>
                                <div style="font-weight:600">{{ $message->file_name }}</div>
                                <small>{{ $message->file_size ? number_format($message->file_size/1024,1).' KB' : '' }}</small>
                            </div>
                        </a>
                    @else
                        {{ $message->message }}
                    @endif
                </div>
                <br>
                <small class="message-time">{{ $message->created_at->format('H:i') }}</small>
            </div>
            @endforeach
        </div>

        {{-- ── Footer ── --}}
        <div class="chat-footer">

            {{-- Preview file sebelum dikirim --}}
            <div id="filePreview" class="file-preview-bar d-none">
                <i class="bi bi-paperclip"></i>
                <span id="filePreviewName" style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
                <button type="button" id="fileCancelBtn"  class="btn btn-sm" title="Batal">
                    <i class="bi bi-x-lg"></i></button>
            </div>

            {{-- Emoji picker --}}
            <div class="emoji-picker-wrapper" id="emojiPickerWrapper">
                <emoji-picker id="emojiPicker"></emoji-picker>
            </div>

            <form id="chatForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="receiver_id" value="{{ $selectedUser->id }}">
                <input type="file" id="fileInput" class="d-none"
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt,.ppt,.pptx">

                <div class="footer-inner">
                    <div class="input-pill">
                        {{-- Emoji --}}
                        <button type="button" class="pill-btn" id="emojiBtn" title="Emoji">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                <line x1="9" y1="9" x2="9.01" y2="9"/>
                                <line x1="15" y1="9" x2="15.01" y2="9"/>
                            </svg>
                        </button>
                        {{-- Input teks --}}
                        <input type="text" id="message" placeholder="Tulis pesan..." autocomplete="off">
                        {{-- Lampiran --}}
                        <button type="button" class="pill-btn" id="attachBtn" title="Lampirkan file / foto">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19 a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Kirim --}}
                    <button type="submit" class="send-btn" id="sendBtn" title="Kirim">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#F5FEFF">
                            <path d="M2 12L22 2L12 22L10 14L2 12Z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

    @else
        <div class="d-flex justify-content-center align-items-center h-100 flex-column gap-2">
            <i class="bi bi-chat-left-dots-fill"
                style="font-size:52px;color:#0E2F76;"></i>
            <h5 style="color:#0E2F76;opacity:.6">Pilih user untuk memulai chat</h5>
        </div>
    @endif
    </div>
</div>
</div>

    <!-- Modal Foto Profil -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
    <h5 class="modal-title">Foto Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body text-center">
        @if(auth()->user()->avatar)
            <img id="previewImage" src="{{ asset('storage/'.auth()->user()->avatar) }}" style="width:140px; height:140px; border-radius:50%; object-fit:cover; border:3px solid #0E2F76;">
        @else
            <img id="previewImage" src="{{ asset('images/avatar.png') }}" style="width:140px; height:140px; border-radius:50%; object-fit:cover; border:3px solid #0E2F76;">
        @endif

        <form action="/profile/avatar" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="form-control mt-4">
                <div class="mt-4">
                    <button class="btn btn-primary">Simpan</button>
                </div>
        </form>
    </div>
</div>
</div>
</div>

<script>
window.authUserId = {{ auth()->id() }};

document.addEventListener('DOMContentLoaded', function () {

    // Indikator status WebSocket (dot + label kecil) 
    const dot   = document.getElementById('wsDot');
    const label = document.getElementById('wsLabel');

    if (dot && label && window.Echo) {
        const conn = window.Echo.connector.pusher.connection;
        conn.bind('connected', () => {
            dot.className   = 'ws-dot connected';
            label.textContent = 'Online';
        });
        conn.bind('disconnected', () => {
            dot.className   = 'ws-dot disconnected';
            label.textContent = 'Terputus';
        });
        conn.bind('error', () => {
            dot.className   = 'ws-dot error';
            label.textContent = 'Error';
        });
        conn.bind('connecting', () => {
            dot.className   = 'ws-dot';
            label.textContent = 'Menghubungkan...';
        });
    }

    // Tombol lampiran
    const attachBtn = document.getElementById('attachBtn');
    const fileInput = document.getElementById('fileInput');
    if (attachBtn && fileInput) {
        attachBtn.addEventListener('click', () => fileInput.click());
    }

    // Emoji picker 
    const emojiBtn     = document.getElementById('emojiBtn');
    const emojiWrapper = document.getElementById('emojiPickerWrapper');
    const emojiPicker  = document.getElementById('emojiPicker');
    const msgInput     = document.getElementById('message');

    if (emojiBtn && emojiWrapper) {
        emojiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            emojiWrapper.classList.toggle('show');
        });
        if (emojiPicker) {
            emojiPicker.addEventListener('emoji-click', (e) => {
                if (msgInput) {
                    const pos = msgInput.selectionStart ?? msgInput.value.length;
                    msgInput.value =
                        msgInput.value.slice(0, pos) +
                        e.detail.unicode +
                        msgInput.value.slice(pos);
                    msgInput.focus();
                    msgInput.selectionStart = msgInput.selectionEnd = pos + e.detail.unicode.length;
                }
                emojiWrapper.classList.remove('show');
            });
        }
        document.addEventListener('click', (e) => {
            if (!emojiWrapper.contains(e.target) && e.target !== emojiBtn) {
                emojiWrapper.classList.remove('show');
            }
        });
    }
});

    // Preview Avatar
    const avatarInput=document.getElementById('avatarInput');

    if(avatarInput){
        avatarInput.addEventListener('change',function(e){
            const file=e.target.files[0];
            if(file){
                document.getElementById('previewImage').src=
                    URL.createObjectURL(file);}
            });
        }

    // Search User
    const searchInput = document.getElementById('searchUser');

    searchInput.addEventListener('keyup', function () {
        let keyword = this.value.toLowerCase();

        document.querySelectorAll('.user-search').forEach(function(user){let name = user.dataset.name;
            if(name.includes(keyword)){
                user.style.display = "flex";
            }else{
                user.style.display = "none";
            }
        });
    });
</script>
@endsection