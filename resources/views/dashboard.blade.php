<x-app-layout>
    <div class="container-fluid p-0" style="background-color: #eef3fc; min-height: 100vh;">
        <!-- 🔹 Top Bar -->
        <div class="bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center shadow-sm"
             style="height: 60px;">
            <h5 class="mb-0 fw-semibold">Chat</h5>
            <i class="bi bi-three-dots-vertical fs-5"></i>
        </div>

        <!-- 🔹 Chat List -->
        <div class="mt-2">
            @foreach($users as $user)
                <a href="{{ route('chat', $user->id) }}"
                   class="d-flex align-items-center p-3 text-decoration-none text-dark border-bottom"
                   style="background-color: #fff; transition: background 0.2s;"
                   onmouseover="this.style.background='#e7f0ff'"
                   onmouseout="this.style.background='#fff'">
                   
                    <!-- Profile Picture -->
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0d6efd&color=fff"
                         alt="Profile"
                         class="rounded-circle me-3"
                         width="55" height="55">
                         
                    <!-- User Info -->
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                        <small class="text-muted">Tap to chat</small>
                    </div>

                    <!-- Three Dots Icon -->
                    <i class="bi bi-three-dots-vertical text-muted"></i>
                </a>
            @endforeach
        </div>
    </div>

    <!-- ✅ Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</x-app-layout>
