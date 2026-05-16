<div>
    <div style="overscroll-behavior: none;">

        <!-- 🔹 Top Bar -->
        <div class="fixed w-full h-16 pt-2 text-white flex items-center justify-between shadow-md px-3"
            style="top:0; background-color:#0d6efd; overscroll-behavior:none;">

            <!-- Left Section: Back Button + Profile Picture + Username -->
            <div class="flex items-center space-x-3">
                <!-- Back Button -->
                <a href="/dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-8 h-8" style="color:#cfe2ff;">
                        <path fill="currentColor"
                            d="M9.41 11H17a1 1 0 0 1 0 2H9.41l2.3 2.3a1 1 0 1 1-1.42 1.4l-4-4a1 1 0 0 1 0-1.4l4-4a1 1 0 0 1 1.42 1.4L9.4 11z" />
                    </svg>
                </a>

                <!-- Profile Picture -->
                <img src="{{ $user->profile_photo_url ?? asset('default-avatar.png') }}" alt="DP"
                    class="rounded-full border-2 border-white shadow" style="width:45px; height:45px;">

                <!-- Username -->
                <div class="font-bold text-lg tracking-wide" style="color:#e0eaff;">
                    {{ $user->name }}
                </div>
            </div>

            <!-- Right Section: Call + 3 Dots -->
            <div style="display:flex;align-items:center;gap:14px;">
                <!-- Call Icon -->
                <a href="#" title="Call" style="color:#cfe2ff;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                        fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M2.25 6.75c0-.621.504-1.125 1.125-1.125h2.25a1.125 1.125 0 0 1 1.125.99c.06.45.18.888.345 1.308a1.125 1.125 0 0 1-.255 1.26L6.78 10.18a16.5 16.5 0 0 0 6.615 6.615l1.005-1.005a1.125 1.125 0 0 1 1.26-.255c.42.165.858.285 1.308.345a1.125 1.125 0 0 1 .99 1.125v2.25c0 .621-.504 1.125-1.125 1.125H6.75A4.5 4.5 0 0 1 2.25 17.25V6.75z" />
                    </svg>
                </a>

                <!-- 3 Dots -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                    style="color:#cfe2ff;">
                    <circle cx="12" cy="6" r="2" fill="currentColor" />
                    <circle cx="12" cy="12" r="2" fill="currentColor" />
                    <circle cx="12" cy="18" r="2" fill="currentColor" />
                </svg>
            </div>
        </div>

        <!-- 🔹 Chat Messages -->
        <div class="mt-20 mb-20 px-4"
            style="background-color:#f3f6ff; min-height:calc(100vh - 140px); padding-top:10px; padding-bottom:63px;">
            @php $lastDate = null; @endphp

            @foreach ($messages as $message)
                @if ($message['type'] === 'product')
                    <!-- PRODUCT BUBBLE -->
                    <div
                        class="flex {{ $message['sender'] != auth()->user()->name ? 'justify-start' : 'justify-end' }} my-2">
                        <div class="px-4 py-2 rounded-2xl shadow text-right"
                            style="background-color:#b3d1ff; color:#000; max-width:70%; word-wrap:break-word;">
                            <b>You: </b>
                            <img src="/{{ $message['data']['image'] }}" class="rounded-lg mb-2"
                                style="width:100%; height:150px; object-fit:cover;">

                            <div class="font-bold text-gray-800">
                                {{ $message['data']['name'] }}
                            </div>
                            <div class="text-primary font-semibold">
                                Rs {{ $message['data']['price'] }}
                            </div>

                            <div class="text-xs text-gray-600 text-right mt-1">
                                {{ $message['time'] }}
                            </div>
                        </div>
                    </div>
                @elseif ($message['type'] === 'text')
                    @if ($lastDate !== $message['date'])
                        <div class="text-center text-gray-500 text-xs my-3 font-semibold">
                            {{ $message['date'] }}
                        </div>
                        @php $lastDate = $message['date']; @endphp
                    @endif

                    @if ($message['sender'] != auth()->user()->name)
                        <div class="flex justify-start my-2">
                            <div class="px-4 py-2 rounded-2xl shadow"
                                style="background-color:#e0e7ff; color:#000; max-width:70%; word-wrap:break-word;">
                                <b>{{ $message['sender'] }}:</b> {{ $message['message'] }}
                                <div class="text-xs text-gray-600 mt-1 text-right">{{ $message['time'] }}</div>
                            </div>
                        </div>
                    @else
                        <div class="flex justify-end my-2">
                            <div class="px-4 py-2 rounded-2xl shadow text-right"
                                style="background-color:#b3d1ff; color:#000; max-width:70%; word-wrap:break-word;">
                                <b>You: </b>{{ $message['message'] }}
                                <div class="text-xs text-gray-700 mt-1">{{ $message['time'] }}</div>
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach
        </div>

        <!-- 🔹 Send Message Form -->
        <form wire:submit.prevent="sendMessage">
            <div class="fixed w-full flex items-center justify-between p-2 space-x-2"
                style="bottom:0; background-color:#dbe9ff;">

                <!-- 🔹 Product Icon (Button to open Modal) -->
                <button type="button" title="Add Product" data-bs-toggle="modal" data-bs-target="#productModal"
                    style="background-color:#0d6efd; padding:9px; border-radius:50%; box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-8 h-8"
                        style="color:white; width:28px; height:28px;">
                        <path fill="currentColor"
                            d="M16 6V4a4 4 0 0 0-8 0v2H3v16h18V6h-5zm-6-2a2 2 0 0 1 4 0v2h-4V4zm9 16H5V8h14v12z" />
                    </svg>
                </button>


                <!-- Message Input -->
                <textarea class="flex-grow py-2 px-4 rounded-full border border-gray-300 resize-none focus:outline-none"
                    style="background-color:#f8f9ff;" rows="1" wire:model="message" placeholder="Message..."></textarea>

                <!-- ✅ Send Button -->
                <button type="submit"
                    style="background-color:#0d6efd; border:none; border-radius:50%; width:45px; height:45px;
                       display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="22" height="22"
                        style="color:white;">
                        <path fill="currentColor"
                            d="M476 3.2L12.5 270.6c-18.1 10.4-15.8 35.6 2.2 43.2L121 358.4l287.3-253.2c5.5-4.9 13.3 2.6 8.6 8.3L176 407v80.5c0 23.6 28.5 32.9 42.5 15.8L282 426l124.6 52.2c14.2 6 30.4-2.9 33-18.2l72-432C515 7.8 493.3-6.8 476 3.2z" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- 🔹 Product Popup Modal (Moved OUTSIDE the form) -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="productModalLabel">Product Collection</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('components.product-slider')
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
