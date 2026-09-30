<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Roast & Co. - Kasir POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* Format Struk Thermal 58mm / 80mm */
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-print, #receipt-print * {
                visibility: visible;
            }
            #receipt-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 8px;
                background: white;
                color: black;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-stone-100 text-stone-800 font-sans antialiased" x-data="posSystem({{ json_encode($products) }})">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Left: Product Section -->
        <div class="flex-1 flex flex-col h-full overflow-hidden border-r border-stone-200">
            <!-- Header Bar -->
            <header class="bg-stone-900 text-stone-50 px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 shadow-md">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center text-amber-50 shadow-inner font-black text-xl">
                        ☕
                    </div>
                    <div>
                        <h1 class="text-lg font-black tracking-wider uppercase text-amber-400 leading-none">Roast & Co.</h1>
                        <span class="text-[11px] text-stone-300 font-medium tracking-wide">Specialty Coffee & Kitchen POS</span>
                    </div>
                </div>

                <!-- Live Search Box -->
                <div class="relative flex-1 max-w-xs">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari kopi / makanan..."
                           class="w-full pl-9 pr-8 py-1.5 bg-stone-800/80 border border-stone-700 rounded-xl text-sm text-stone-100 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-stone-800 transition">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-stone-400 hover:text-white text-xs font-bold">✕</button>
                </div>

                <!-- Category Filters & Navigation Buttons -->
                <div class="flex items-center space-x-2">
                    <div class="flex space-x-1 bg-stone-800/80 p-1 rounded-xl border border-stone-700">
                        <button @click="category = 'all'" :class="category === 'all' ? 'bg-amber-600 text-white font-bold' : 'text-stone-300 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition">Semua</button>
                        <button @click="category = 'minuman'" :class="category === 'minuman' ? 'bg-amber-600 text-white font-bold' : 'text-stone-300 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition">☕ Minuman</button>
                        <button @click="category = 'makanan'" :class="category === 'makanan' ? 'bg-amber-600 text-white font-bold' : 'text-stone-300 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition">🥐 Makanan</button>
                    </div>

                    <!-- Riwayat Button -->
                    <button @click="openHistoryModal" class="px-3 py-1.5 bg-stone-800 hover:bg-stone-700 text-stone-200 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm border border-stone-700">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span>Riwayat</span>
                    </button>

                    <!-- Kelola Menu Button (PIN Protected) -->
                    <button @click="openPinModal" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Kelola Menu</span>
                    </button>
                </div>
            </header>

            <!-- Product Grid -->
            <main class="flex-1 overflow-y-auto p-5">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product.id, product.name, product.price, product.stock)"
                             class="bg-white rounded-2xl p-3 shadow-sm border border-stone-200 hover:shadow-md hover:border-amber-500 transition cursor-pointer flex flex-col justify-between group select-none">
                            <div>
                                <div class="relative overflow-hidden rounded-xl h-32 mb-2.5 bg-stone-100">
                                    <img :src="product.image" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <span class="absolute top-2 right-2 bg-stone-900/80 text-amber-300 text-[10px] font-semibold px-2 py-0.5 rounded-full backdrop-blur-sm shadow-sm">
                                        Stok: <span x-text="product.stock"></span>
                                    </span>
                                </div>
                                <h3 class="font-bold text-stone-800 text-sm line-clamp-1 group-hover:text-amber-700 transition" x-text="product.name"></h3>
                                <p class="text-[11px] text-stone-400 capitalize mb-1" x-text="product.category"></p>
                            </div>
                            <div class="text-amber-700 font-extrabold text-sm" x-text="formatRupiah(product.price)"></div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="filteredProducts.length === 0" class="h-64 flex flex-col items-center justify-center text-stone-400">
                    <div class="w-12 h-12 rounded-full bg-stone-200 flex items-center justify-center mb-2 text-stone-500 text-lg">🔍</div>
                    <p class="text-sm font-semibold">Tidak ada menu yang sesuai</p>
                    <p class="text-xs text-stone-400">Coba ubah kata kunci pencarian atau kategori</p>
                </div>
            </main>
        </div>

        <!-- Right: Cart & Checkout Section -->
        <div class="w-[410px] bg-white flex flex-col h-full shadow-2xl border-l border-stone-200">
            <!-- Cart Header & Customer Details -->
            <div class="p-4 border-b border-stone-200 bg-stone-50 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-stone-900 flex items-center space-x-1.5">
                        <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Pesanan Saat Ini</span>
                    </h2>
                    <button x-show="cart.length > 0" @click="clearCart" class="text-xs text-rose-500 hover:text-rose-700 font-bold transition">
                        Kosongkan
                    </button>
                </div>

                <!-- Customer Name & Order Type -->
                <div class="space-y-2">
                    <input type="text"
                           x-model="customerName"
                           placeholder="Nama Pelanggan (opsional)"
                           class="w-full px-3 py-1.5 bg-white border border-stone-200 rounded-xl text-xs font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">

                    <!-- Toggle Dine-in / Takeaway -->
                    <div class="flex space-x-2">
                        <button type="button"
                                @click="orderType = 'dine_in'"
                                :class="orderType === 'dine_in' ? 'bg-amber-700 text-white shadow-sm' : 'bg-white text-stone-600 border border-stone-200 hover:bg-stone-100'"
                                class="flex-1 py-1.5 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1">
                            <span>🍽️ Dine In</span>
                        </button>
                        <button type="button"
                                @click="orderType = 'takeaway'"
                                :class="orderType === 'takeaway' ? 'bg-amber-700 text-white shadow-sm' : 'bg-white text-stone-600 border border-stone-200 hover:bg-stone-100'"
                                class="flex-1 py-1.5 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1">
                            <span>🛍️ Take Away</span>
                        </button>
                    </div>

                    <!-- Table Number if Dine In -->
                    <div x-show="orderType === 'dine_in'" x-cloak>
                        <input type="text"
                               x-model="tableNumber"
                               placeholder="Nomor Meja (Contoh: Meja 05)"
                               class="w-full px-3 py-1.5 bg-white border border-stone-200 rounded-xl text-xs font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-stone-400">
                        <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center mb-2 text-stone-400 text-xl">
                            ☕
                        </div>
                        <p class="text-xs font-medium">Klik menu untuk menambah ke pesanan</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center justify-between bg-stone-50 p-2.5 rounded-xl border border-stone-200 hover:border-amber-300 transition">
                        <div class="flex-1 pr-2">
                            <h4 class="font-bold text-xs text-stone-800" x-text="item.name"></h4>
                            <p class="text-[11px] text-amber-700 font-semibold" x-text="formatRupiah(item.price * item.quantity)"></p>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <button @click="updateQty(index, -1)" class="w-6 h-6 rounded-lg bg-stone-200 text-stone-800 font-bold flex items-center justify-center hover:bg-stone-300 transition text-xs">-</button>
                            <span class="text-xs font-bold w-5 text-center text-stone-800" x-text="item.quantity"></span>
                            <button @click="updateQty(index, 1)" class="w-6 h-6 rounded-lg bg-stone-200 text-stone-800 font-bold flex items-center justify-center hover:bg-stone-300 transition text-xs">+</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Notes & Payment Details -->
            <div class="p-4 border-t border-stone-200 bg-stone-50/50 space-y-3">
                <!-- Order Notes -->
                <div>
                    <input type="text"
                           x-model="notes"
                           placeholder="Catatan pesanan (contoh: Less ice, less sugar)"
                           class="w-full px-3 py-1.5 bg-white border border-stone-200 rounded-xl text-xs text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <!-- Subtotal -->
                <div class="flex justify-between items-center text-stone-600 text-sm">
                    <span class="font-semibold">Subtotal</span>
                    <span class="font-black text-stone-900 text-base" x-text="formatRupiah(subtotal)"></span>
                </div>

                <!-- Payment Method Toggle (Cash vs QRIS) -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-stone-700">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                @click="setPaymentMethod('cash')"
                                :class="paymentMethod === 'cash' ? 'bg-amber-700 text-white font-bold shadow-md' : 'bg-white text-stone-700 border border-stone-200 hover:bg-stone-100'"
                                class="py-2 px-3 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition">
                            <span>💵 Tunai (Cash)</span>
                        </button>
                        <button type="button"
                                @click="setPaymentMethod('qris')"
                                :class="paymentMethod === 'qris' ? 'bg-amber-700 text-white font-bold shadow-md' : 'bg-white text-stone-700 border border-stone-200 hover:bg-stone-100'"
                                class="py-2 px-3 rounded-xl text-xs flex items-center justify-center space-x-1.5 transition">
                            <span>📱 QRIS / E-Wallet</span>
                        </button>
                    </div>
                </div>

                <!-- Sub-pilihan E-Wallet (Tampil saat QRIS dipilih) -->
                <div x-show="paymentMethod === 'qris'" x-cloak class="space-y-1">
                    <label class="text-[11px] font-semibold text-stone-600">Pilih E-Wallet / Provider</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="paymentProvider = 'GoPay'" :class="paymentProvider === 'GoPay' ? 'bg-amber-100 border-amber-500 text-amber-900 font-bold ring-1 ring-amber-500' : 'bg-white border-stone-200 text-stone-700 hover:bg-stone-100'" class="py-1.5 px-1 border rounded-lg text-xs text-center transition">GoPay</button>
                        <button type="button" @click="paymentProvider = 'DANA'" :class="paymentProvider === 'DANA' ? 'bg-amber-100 border-amber-500 text-amber-900 font-bold ring-1 ring-amber-500' : 'bg-white border-stone-200 text-stone-700 hover:bg-stone-100'" class="py-1.5 px-1 border rounded-lg text-xs text-center transition">DANA</button>
                        <button type="button" @click="paymentProvider = 'OVO'" :class="paymentProvider === 'OVO' ? 'bg-amber-100 border-amber-500 text-amber-900 font-bold ring-1 ring-amber-500' : 'bg-white border-stone-200 text-stone-700 hover:bg-stone-100'" class="py-1.5 px-1 border rounded-lg text-xs text-center transition">OVO</button>
                        <button type="button" @click="paymentProvider = 'ShopeePay'" :class="paymentProvider === 'ShopeePay' ? 'bg-amber-100 border-amber-500 text-amber-900 font-bold ring-1 ring-amber-500' : 'bg-white border-stone-200 text-stone-700 hover:bg-stone-100'" class="py-1.5 px-1 border rounded-lg text-xs text-center transition">Shopee</button>
                    </div>
                </div>

                <!-- Cash Options -->
                <div x-show="paymentMethod === 'cash'" x-cloak class="space-y-2">
                    <!-- Quick Cash Buttons -->
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="payAmount = subtotal" class="py-1 px-1 bg-stone-200 text-stone-800 rounded-lg text-[11px] font-bold hover:bg-stone-300 transition">Uang Pas</button>
                        <button type="button" @click="payAmount = 20000" class="py-1 px-1 bg-stone-200 text-stone-800 rounded-lg text-[11px] font-bold hover:bg-stone-300 transition">20.000</button>
                        <button type="button" @click="payAmount = 50000" class="py-1 px-1 bg-stone-200 text-stone-800 rounded-lg text-[11px] font-bold hover:bg-stone-300 transition">50.000</button>
                        <button type="button" @click="payAmount = 100000" class="py-1 px-1 bg-stone-200 text-stone-800 rounded-lg text-[11px] font-bold hover:bg-stone-300 transition">100.000</button>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-semibold text-stone-500">Nominal Pembayaran Tunai</label>
                        <input type="number"
                               x-model.number="payAmount"
                               placeholder="0"
                               class="w-full px-3 py-2 bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 font-extrabold text-stone-900 text-sm">
                    </div>

                    <div class="flex justify-between items-center text-xs pt-0.5">
                        <span class="text-stone-600 font-semibold">Kembalian:</span>
                        <span class="font-extrabold text-sm" :class="change >= 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="formatRupiah(change)"></span>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button @click="checkout" 
                        :disabled="cart.length === 0 || (paymentMethod === 'cash' && payAmount < subtotal) || isLoading"
                        class="w-full py-3 bg-stone-900 text-white rounded-xl font-bold shadow-lg shadow-stone-950/20 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center space-x-2">
                    <span x-show="!isLoading" x-text="paymentMethod === 'qris' ? 'Scan & Bayar QRIS' : 'Bayar & Cetak Struk'"></span>
                    <span x-show="isLoading" x-cloak>Memproses Transaksi...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Scan QRIS -->
    <div x-show="showQrisModal" x-cloak class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl flex flex-col items-center text-center">
            <h3 class="text-base font-extrabold text-stone-900">Pembayaran QRIS</h3>
            <p class="text-xs text-stone-500 mt-1">Silakan scan kode QR menggunakan e-wallet <span class="font-bold text-amber-700" x-text="paymentProvider"></span></p>

            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 my-4 flex flex-col items-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=ROAST-CO-POS" alt="QRIS Code" class="w-44 h-44 rounded-xl shadow-sm border bg-white p-2">
                <span class="text-sm font-black text-amber-800 mt-3" x-text="formatRupiah(subtotal)"></span>
            </div>

            <p class="text-[11px] text-stone-400 mb-4">Pastikan pembayaran pelanggan telah diverifikasi sebelum konfirmasi.</p>

            <div class="w-full flex space-x-2">
                <button @click="confirmQrisPayment" class="flex-1 bg-emerald-600 text-white font-bold py-2.5 rounded-xl hover:bg-emerald-700 transition text-xs shadow-md">
                    Verifikasi & Cetak
                </button>
                <button @click="showQrisModal = false" class="px-4 bg-stone-200 text-stone-700 font-bold py-2.5 rounded-xl hover:bg-stone-300 transition text-xs">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Modal PIN Manager (Security Check) -->
    <div x-show="showPinModal" x-cloak class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xs w-full p-6 shadow-2xl flex flex-col items-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mb-3 text-xl font-bold">
                🔒
            </div>
            <h3 class="font-extrabold text-base text-stone-900">PIN Manager</h3>
            <p class="text-xs text-stone-500 text-center mb-4">Masukkan 4-digit PIN untuk mengelola menu produk (Default: 1234)</p>

            <form @submit.prevent="verifyPin" class="w-full space-y-3">
                <input type="password"
                       maxlength="6"
                       x-model="managerPin"
                       placeholder="••••"
                       autofocus
                       class="w-full text-center tracking-[1em] text-2xl font-black py-2 border-2 border-stone-300 rounded-xl focus:outline-none focus:border-amber-600">

                <div class="flex space-x-2 pt-2">
                    <button type="button" @click="closePinModal" class="flex-1 py-2 rounded-xl bg-stone-200 text-stone-700 text-xs font-bold hover:bg-stone-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kelola Menu (Product Management) -->
    <div x-show="showProductManagerModal" x-cloak class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-stone-200">
                <div class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-sm">
                        ☕
                    </div>
                    <div>
                        <h3 class="text-base font-black text-stone-900">Kelola Menu - Roast & Co.</h3>
                        <p class="text-xs text-stone-500">Tambah menu baru, update harga & kelola stok</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button x-show="managerTab === 'list'" @click="openCreateProductForm" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 shadow-sm">
                        <span>+ Tambah Menu Baru</span>
                    </button>
                    <button x-show="managerTab === 'form'" @click="managerTab = 'list'" class="px-3 py-1.5 bg-stone-200 hover:bg-stone-300 text-stone-700 rounded-xl text-xs font-bold transition">
                        ← Kembali ke Daftar
                    </button>
                    <button @click="showProductManagerModal = false" class="text-stone-400 hover:text-stone-600 text-lg font-bold pl-2">✕</button>
                </div>
            </div>

            <!-- View 1: List Products Table -->
            <div x-show="managerTab === 'list'" class="flex-1 overflow-y-auto py-4 space-y-3">
                <div class="overflow-hidden border border-stone-200 rounded-xl">
                    <table class="w-full text-left text-xs text-stone-700">
                        <thead class="bg-stone-100 text-stone-600 font-bold border-b border-stone-200">
                            <tr>
                                <th class="p-3">Foto & Nama Menu</th>
                                <th class="p-3">Kategori</th>
                                <th class="p-3">Harga</th>
                                <th class="p-3">Stok</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <template x-for="p in allProducts" :key="p.id">
                                <tr class="hover:bg-stone-50 transition">
                                    <td class="p-3 flex items-center space-x-3">
                                        <img :src="p.image" class="w-10 h-10 object-cover rounded-lg bg-stone-100 border border-stone-200">
                                        <span class="font-bold text-stone-800" x-text="p.name"></span>
                                    </td>
                                    <td class="p-3 capitalize">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                              :class="p.category === 'minuman' ? 'bg-amber-100 text-amber-800' : 'bg-orange-100 text-orange-800'"
                                              x-text="p.category"></span>
                                    </td>
                                    <td class="p-3 font-extrabold text-stone-900" x-text="formatRupiah(p.price)"></td>
                                    <td class="p-3">
                                        <span class="font-bold px-2 py-0.5 rounded-full text-[10px]"
                                              :class="p.stock > 10 ? 'bg-emerald-100 text-emerald-800' : (p.stock > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')"
                                              x-text="p.stock + ' porsi'"></span>
                                    </td>
                                    <td class="p-3 text-right space-x-1">
                                        <button @click="editProduct(p)" class="px-2.5 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold rounded-lg transition">Edit</button>
                                        <button @click="deleteProduct(p.id)" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-lg transition">Hapus</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 2: Form Tambah / Edit Product -->
            <div x-show="managerTab === 'form'" class="flex-1 overflow-y-auto py-4">
                <form @submit.prevent="saveProduct" class="space-y-4 max-w-xl mx-auto">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Nama Menu</label>
                        <input type="text"
                               x-model="productForm.name"
                               required
                               placeholder="Contoh: Caramel Macchiato, Pain au Chocolat"
                               class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-semibold text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Kategori</label>
                            <select x-model="productForm.category" class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-semibold text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="minuman">☕ Minuman</option>
                                <option value="makanan">🥐 Makanan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Harga Jual (Rp)</label>
                            <input type="number"
                                   x-model.number="productForm.price"
                                   required
                                   min="0"
                                   placeholder="25000"
                                   class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-semibold text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Stok Awal</label>
                            <input type="number"
                                   x-model.number="productForm.stock"
                                   required
                                   min="0"
                                   placeholder="50"
                                   class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-semibold text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- Image Input (File or URL) -->
                    <div class="space-y-2 border-t border-stone-100 pt-3">
                        <label class="block text-xs font-bold text-stone-700">Foto Menu</label>
                        <div class="flex space-x-2 text-xs mb-2">
                            <button type="button" @click="productForm.imageSource = 'upload'" :class="productForm.imageSource === 'upload' ? 'bg-amber-100 text-amber-900 font-bold' : 'text-stone-500'" class="px-2.5 py-1 rounded-lg">Upload Gambar</button>
                            <button type="button" @click="productForm.imageSource = 'url'" :class="productForm.imageSource === 'url' ? 'bg-amber-100 text-amber-900 font-bold' : 'text-stone-500'" class="px-2.5 py-1 rounded-lg">Link URL Gambar</button>
                        </div>

                        <div x-show="productForm.imageSource === 'upload'">
                            <input type="file"
                                   id="product-image-file"
                                   accept="image/*"
                                   @change="handleImageFileChange"
                                   class="w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100">
                        </div>

                        <div x-show="productForm.imageSource === 'url'">
                            <input type="url"
                                   x-model="productForm.imageUrl"
                                   placeholder="https://images.unsplash.com/..."
                                   class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <!-- Image Preview -->
                        <div x-show="productForm.previewUrl" class="mt-2 flex items-center space-x-3">
                            <img :src="productForm.previewUrl" class="w-16 h-16 object-cover rounded-xl border border-stone-300 shadow-sm">
                            <span class="text-xs text-stone-500">Pratinjau Foto Menu</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-stone-100 flex space-x-2">
                        <button type="button" @click="managerTab = 'list'" class="flex-1 py-2.5 bg-stone-200 text-stone-700 font-bold rounded-xl text-xs hover:bg-stone-300 transition">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isSavingProduct"
                                class="flex-1 py-2.5 bg-amber-600 text-white font-bold rounded-xl text-xs hover:bg-amber-700 transition disabled:opacity-50 shadow-md">
                            <span x-show="!isSavingProduct" x-text="productForm.id ? 'Simpan Perubahan' : 'Tambah Menu'"></span>
                            <span x-show="isSavingProduct" x-cloak>Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Receipt / Pop-up Struk -->
    <div x-show="showReceiptModal" x-cloak class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl flex flex-col items-center">
            
            <!-- Struk Printable Area (Thermal 58mm/80mm) -->
            <div id="receipt-print" class="w-full text-stone-800 font-mono text-xs">
                <div class="text-center mb-3">
                    <h2 class="font-black text-base uppercase tracking-wider">ROAST & CO.</h2>
                    <p class="text-[10px] text-stone-500 font-sans">Specialty Coffee & Artisan Kitchen</p>
                    <p class="text-[10px] text-stone-500">Jl. Kopi Warmah No. 12, Bandung</p>
                    <p class="text-[10px] text-stone-500">Telp: 0812-3456-7890</p>
                    <div class="border-b border-dashed border-stone-400 my-2"></div>
                </div>

                <div class="space-y-1 mb-2 text-[11px]">
                    <div class="flex justify-between">
                        <span>Invoice:</span>
                        <span class="font-bold" x-text="lastTransaction?.invoice"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tanggal:</span>
                        <span x-text="lastTransaction?.date"></span>
                    </div>
                    <div class="flex justify-between" x-show="lastTransaction?.customer">
                        <span>Pelanggan:</span>
                        <span class="font-bold" x-text="lastTransaction?.customer"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tipe:</span>
                        <span class="font-bold" x-text="lastTransaction?.orderType === 'dine_in' ? 'Dine In (' + (lastTransaction?.table || '-') + ')' : 'Take Away'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Metode:</span>
                        <span class="font-bold uppercase" x-text="lastTransaction?.paymentMethod === 'qris' ? 'QRIS (' + lastTransaction?.paymentProvider + ')' : 'TUNAI'"></span>
                    </div>
                    <div class="flex justify-between text-stone-500" x-show="lastTransaction?.notes">
                        <span>Catatan:</span>
                        <span class="italic text-right" x-text="lastTransaction?.notes"></span>
                    </div>
                </div>

                <div class="border-b border-dashed border-stone-400 my-2"></div>

                <!-- Items -->
                <div class="space-y-1.5">
                    <template x-for="item in lastTransaction?.items" :key="item.id">
                        <div>
                            <div class="font-bold" x-text="item.name"></div>
                            <div class="flex justify-between text-stone-600">
                                <span x-text="item.quantity + ' x ' + formatRupiah(item.price)"></span>
                                <span class="font-bold text-stone-800" x-text="formatRupiah(item.price * item.quantity)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="border-b border-dashed border-stone-400 my-2"></div>

                <!-- Totals -->
                <div class="space-y-1">
                    <div class="flex justify-between font-bold text-sm">
                        <span>Total:</span>
                        <span x-text="formatRupiah(lastTransaction?.total)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar:</span>
                        <span x-text="formatRupiah(lastTransaction?.pay)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kembali:</span>
                        <span x-text="formatRupiah(lastTransaction?.change)"></span>
                    </div>
                </div>

                <div class="border-b border-dashed border-stone-400 my-2"></div>

                <div class="text-center text-[10px] text-stone-500 mt-2 font-sans">
                    <p class="font-bold text-stone-700">Terima Kasih atas Kunjungan Anda!</p>
                    <p>Selamat Menikmati Kopi Spesial Roast & Co.</p>
                </div>
            </div>

            <!-- Action Buttons (Hidden saat Print) -->
            <div class="w-full flex space-x-2 mt-5 no-print">
                <button @click="printReceipt" class="flex-1 bg-amber-600 text-white font-bold py-2.5 rounded-xl hover:bg-amber-700 transition flex items-center justify-center space-x-2 text-sm shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Struk</span>
                </button>
                <button @click="closeReceiptModal" class="px-4 bg-stone-200 text-stone-700 font-bold py-2.5 rounded-xl hover:bg-stone-300 transition text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Transaksi -->
    <div x-show="showHistoryModal" x-cloak class="fixed inset-0 bg-stone-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                        📋
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-stone-900">Riwayat Transaksi - Roast & Co.</h3>
                        <p class="text-xs text-stone-500">Daftar transaksi kasir & cetak ulang struk</p>
                    </div>
                </div>
                <button @click="showHistoryModal = false" class="text-stone-400 hover:text-stone-600 text-lg font-bold">✕</button>
            </div>

            <!-- List Content -->
            <div class="flex-1 overflow-y-auto py-4 space-y-3">
                <div x-show="isLoadingHistory" class="text-center py-8 text-stone-400 text-sm">
                    Memuat riwayat transaksi...
                </div>

                <div x-show="!isLoadingHistory && historyOrders.length === 0" class="text-center py-8 text-stone-400 text-sm">
                    Belum ada riwayat transaksi.
                </div>

                <template x-for="order in historyOrders" :key="order.id">
                    <div class="p-3.5 rounded-xl border border-stone-200 hover:border-amber-300 bg-stone-50 hover:bg-amber-50/40 transition flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-xs text-stone-900" x-text="order.invoice_number"></span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                      :class="order.payment_method === 'qris' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'"
                                      x-text="order.payment_method === 'qris' ? ('QRIS (' + (order.payment_provider || 'E-WALLET') + ')') : 'TUNAI'"></span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800"
                                      x-text="order.order_type === 'dine_in' ? 'Dine In' : 'Take Away'"></span>
                            </div>
                            <div class="text-xs text-stone-600">
                                <span x-text="order.customer_name ? 'Pelanggan: ' + order.customer_name + ' • ' : ''"></span>
                                <span x-text="new Date(order.created_at).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-xs font-semibold text-stone-500">
                                <span x-text="order.items.length + ' item: ' + order.items.map(i => i.product ? i.product.name : 'Item').join(', ')"></span>
                            </div>
                        </div>

                        <div class="text-right space-y-2">
                            <div class="font-extrabold text-amber-800 text-sm" x-text="formatRupiah(order.total_amount)"></div>
                            <button @click="reprintOrder(order)" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>Cetak Ulang</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-3 border-t border-stone-100 text-right">
                <button @click="showHistoryModal = false" class="px-4 py-2 bg-stone-200 text-stone-700 font-bold rounded-xl text-xs hover:bg-stone-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function posSystem(initialProducts = []) {
            return {
                products: initialProducts,
                allProducts: [...initialProducts],
                category: 'all',
                searchQuery: '',
                customerName: '',
                orderType: 'dine_in',
                tableNumber: '',
                paymentMethod: 'cash',
                paymentProvider: 'GoPay',
                notes: '',
                cart: [],
                payAmount: 0,
                isLoading: false,
                showQrisModal: false,
                showReceiptModal: false,
                showHistoryModal: false,
                isLoadingHistory: false,
                historyOrders: [],
                lastTransaction: null,

                // Product Management State
                showPinModal: false,
                managerPin: '',
                showProductManagerModal: false,
                managerTab: 'list',
                isSavingProduct: false,
                productForm: {
                    id: null,
                    name: '',
                    category: 'minuman',
                    price: 20000,
                    stock: 50,
                    imageSource: 'upload',
                    imageFile: null,
                    imageUrl: '',
                    previewUrl: '',
                },

                get filteredProducts() {
                    return this.products.filter(product => {
                        let matchesCategory = this.category === 'all' || product.category === this.category;
                        let matchesSearch = !this.searchQuery || product.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        let hasStock = Number(product.stock) > 0;
                        return matchesCategory && matchesSearch && hasStock;
                    });
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get change() {
                    if (this.paymentMethod === 'qris') return 0;
                    return this.payAmount ? this.payAmount - this.subtotal : 0;
                },

                setPaymentMethod(method) {
                    this.paymentMethod = method;
                    if (method === 'qris') {
                        this.payAmount = this.subtotal;
                    }
                },

                addToCart(id, name, price, stock) {
                    let existing = this.cart.find(i => i.id === id);
                    if (existing) {
                        if (existing.quantity < stock) {
                            existing.quantity++;
                        } else {
                            alert('Stok tidak mencukupi!');
                        }
                    } else {
                        this.cart.push({ id, name, price: Number(price), quantity: 1, stock });
                    }

                    if (this.paymentMethod === 'qris') {
                        this.payAmount = this.subtotal;
                    }
                },

                updateQty(index, delta) {
                    let item = this.cart[index];
                    if (delta > 0 && item.quantity >= item.stock) {
                        alert('Stok tidak mencukupi!');
                        return;
                    }
                    item.quantity += delta;
                    if (item.quantity <= 0) {
                        this.cart.splice(index, 1);
                    }

                    if (this.paymentMethod === 'qris') {
                        this.payAmount = this.subtotal;
                    }
                },

                clearCart() {
                    if (confirm('Kosongkan keranjang pesanan?')) {
                        this.cart = [];
                        this.payAmount = 0;
                    }
                },

                formatRupiah(val) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
                },

                checkout() {
                    if (this.paymentMethod === 'qris') {
                        this.showQrisModal = true;
                    } else {
                        this.processPayment();
                    }
                },

                confirmQrisPayment() {
                    this.showQrisModal = false;
                    this.payAmount = this.subtotal;
                    this.processPayment();
                },

                async processPayment() {
                    this.isLoading = true;
                    try {
                        let payload = {
                            cart: this.cart,
                            pay_amount: this.paymentMethod === 'qris' ? this.subtotal : this.payAmount,
                            payment_method: this.paymentMethod,
                            payment_provider: this.paymentMethod === 'qris' ? this.paymentProvider : null,
                            order_type: this.orderType,
                            customer_name: this.customerName || null,
                            table_number: this.orderType === 'dine_in' ? (this.tableNumber || null) : null,
                            notes: this.notes || null,
                        };

                        let response = await fetch("{{ route('pos.checkout') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        let result = await response.json();

                        if (response.ok) {
                            this.cart.forEach(c => {
                                let p = this.products.find(prod => prod.id === c.id);
                                if (p) p.stock -= c.quantity;
                            });

                            this.lastTransaction = {
                                invoice: result.invoice,
                                date: new Date().toLocaleString('id-ID'),
                                customer: this.customerName,
                                orderType: this.orderType,
                                table: this.tableNumber,
                                paymentMethod: this.paymentMethod,
                                paymentProvider: this.paymentProvider,
                                notes: this.notes,
                                items: [...this.cart],
                                total: this.subtotal,
                                pay: payload.pay_amount,
                                change: result.change
                            };

                            this.showReceiptModal = true;
                        } else {
                            alert(result.message || 'Transaksi gagal!');
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan sistem saat memproses transaksi!');
                    } finally {
                        this.isLoading = false;
                    }
                },

                printReceipt() {
                    window.print();
                },

                closeReceiptModal() {
                    this.showReceiptModal = false;
                    this.cart = [];
                    this.payAmount = 0;
                    this.customerName = '';
                    this.tableNumber = '';
                    this.notes = '';
                },

                async openHistoryModal() {
                    this.showHistoryModal = true;
                    this.isLoadingHistory = true;
                    try {
                        let response = await fetch("{{ route('pos.history') }}", {
                            headers: { "Accept": "application/json" }
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.historyOrders = data.orders;
                        }
                    } catch (e) {
                        alert('Gagal mengambil data riwayat pesanan!');
                    } finally {
                        this.isLoadingHistory = false;
                    }
                },

                reprintOrder(order) {
                    this.lastTransaction = {
                        invoice: order.invoice_number,
                        date: new Date(order.created_at).toLocaleString('id-ID'),
                        customer: order.customer_name,
                        orderType: order.order_type,
                        table: order.table_number,
                        paymentMethod: order.payment_method,
                        paymentProvider: order.payment_provider || 'E-Wallet',
                        notes: order.notes,
                        items: order.items.map(item => ({
                            id: item.product_id,
                            name: item.product ? item.product.name : 'Item',
                            price: Number(item.price),
                            quantity: item.quantity,
                        })),
                        total: Number(order.total_amount),
                        pay: Number(order.pay_amount),
                        change: Number(order.change_amount),
                    };

                    this.showHistoryModal = false;
                    this.showReceiptModal = true;
                },

                // --- PRODUCT MANAGER LOGIC ---
                openPinModal() {
                    this.managerPin = '';
                    this.showPinModal = true;
                },

                closePinModal() {
                    this.showPinModal = false;
                    this.managerPin = '';
                },

                async verifyPin() {
                    if (!this.managerPin) return;
                    try {
                        let res = await fetch("{{ route('products.verify-pin') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ pin: this.managerPin })
                        });

                        let data = await res.json();
                        if (res.ok && data.success) {
                            this.showPinModal = false;
                            await this.loadAllProducts();
                            this.managerTab = 'list';
                            this.showProductManagerModal = true;
                        } else {
                            alert(data.message || 'PIN salah!');
                        }
                    } catch (e) {
                        alert('Gagal memverifikasi PIN Manager');
                    }
                },

                async loadAllProducts() {
                    try {
                        let res = await fetch("{{ route('products.index') }}", {
                            headers: { "Accept": "application/json" }
                        });
                        let data = await res.json();
                        if (data.success) {
                            this.allProducts = data.products;
                            this.products = [...data.products];
                        }
                    } catch (e) {
                        console.error('Error fetching products', e);
                    }
                },

                openCreateProductForm() {
                    this.productForm = {
                        id: null,
                        name: '',
                        category: 'minuman',
                        price: 25000,
                        stock: 50,
                        imageSource: 'upload',
                        imageFile: null,
                        imageUrl: '',
                        previewUrl: '',
                    };
                    this.managerTab = 'form';
                },

                editProduct(p) {
                    this.productForm = {
                        id: p.id,
                        name: p.name,
                        category: p.category,
                        price: Number(p.price),
                        stock: Number(p.stock),
                        imageSource: 'url',
                        imageFile: null,
                        imageUrl: p.image.startsWith('http') ? p.image : '',
                        previewUrl: p.image,
                    };
                    this.managerTab = 'form';
                },

                handleImageFileChange(e) {
                    let file = e.target.files[0];
                    if (file) {
                        this.productForm.imageFile = file;
                        this.productForm.previewUrl = URL.createObjectURL(file);
                    }
                },

                async saveProduct() {
                    this.isSavingProduct = true;
                    try {
                        let formData = new FormData();
                        formData.append('pin', this.managerPin);
                        formData.append('name', this.productForm.name);
                        formData.append('category', this.productForm.category);
                        formData.append('price', this.productForm.price);
                        formData.append('stock', this.productForm.stock);

                        if (this.productForm.imageSource === 'upload' && this.productForm.imageFile) {
                            formData.append('image_file', this.productForm.imageFile);
                        } else if (this.productForm.imageSource === 'url' && this.productForm.imageUrl) {
                            formData.append('image_url', this.productForm.imageUrl);
                        }

                        let url = this.productForm.id
                            ? `/products/${this.productForm.id}/update`
                            : "{{ route('products.store') }}";

                        let res = await fetch(url, {
                            method: "POST",
                            headers: {
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        let data = await res.json();
                        if (res.ok && data.success) {
                            alert(data.message);
                            await this.loadAllProducts();
                            this.managerTab = 'list';
                        } else {
                            alert(data.message || 'Gagal menyimpan menu');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat menyimpan menu');
                    } finally {
                        this.isSavingProduct = false;
                    }
                },

                async deleteProduct(id) {
                    if (!confirm('Yakin ingin menghapus menu ini dari katalog?')) return;
                    try {
                        let res = await fetch(`/products/${id}`, {
                            method: "DELETE",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ pin: this.managerPin })
                        });

                        let data = await res.json();
                        if (res.ok && data.success) {
                            await this.loadAllProducts();
                        } else {
                            alert(data.message || 'Gagal menghapus produk');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat menghapus menu');
                    }
                }
            }
        }
    </script>
</body>
</html>