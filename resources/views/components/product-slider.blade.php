<!-- 🌟 Horizontal Product Slider -->
<style>
    .horizontal-slider {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
        gap: 15px;
        padding: 15px;
    }

    .horizontal-slider::-webkit-scrollbar {
        height: 8px;
    }

    .horizontal-slider::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
    }

    .product-card {
        flex: 0 0 auto;
        width: 180px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 10px;
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }

    .product-name {
        font-size: 15px;
        font-weight: 600;
        margin-top: 8px;
        color: #333;
    }

    .product-price {
        color: #0d6efd;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .slider-controls {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 5px;
    }

    .slider-btn {
        background-color: #0d6efd;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .slider-btn:hover {
        background-color: #084298;
    }
</style>

<div class="container text-center mt-2">
    <h2 class="mb-3" style="font-family:'Josefin Sans', sans-serif; color:#0d6efd;">Product Collection</h2>
    <!-- ✅ Horizontal Scroll Slider -->
    <!-- 🌟 Horizontal Product Slider -->
    <div id="productSlider" class="horizontal-slider">
        @foreach ($products as $product)
            <div class="product-card" wire:click="sendProduct({{ $product->id }})" style="cursor:pointer;">

                <img src="/{{ json_decode($product->images)[0] }}" alt="Product Image">

                <div class="product-name">{{ $product->name }}</div>

                <div class="product-price">₹{{ $product->sale_price }}</div>
            </div>
        @endforeach
    </div>
    <!-- 🔹 Scroll Buttons -->
    <div class="slider-controls">
        <button class="slider-btn" id="prevBtn">&#8592;</button>
        <button class="slider-btn" id="nextBtn">&#8594;</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('productSlider');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');

        nextBtn.addEventListener('click', () => {
            slider.scrollBy({
                left: 300,
                behavior: 'smooth'
            });
        });

        prevBtn.addEventListener('click', () => {
            slider.scrollBy({
                left: -300,
                behavior: 'smooth'
            });
        });
    });
</script>
