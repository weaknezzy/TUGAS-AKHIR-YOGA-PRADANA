@extends('main.menu_Minuman')

<link rel="stylesheet" href="{{ asset('css/menuMinuman.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

@section('content')
<div class="product-container">
    @foreach ($menus as $menu)
        <div class="product-card">
            <img src="{{ asset('storage/' . $menu->gambar) }}" alt="{{ $menu->nama_menu }}" class="menu-detail-trigger" style="cursor:pointer;"
                data-id="{{ $menu->id }}"
                data-nama="{{ $menu->nama_menu }}"
                data-gambar="{{ asset('storage/' . $menu->gambar) }}"
                data-harga="Rp. {{ number_format($menu->harga, 0, ',', '.') }}"
                data-deskripsi="{{ $menu->deskripsi ?? '-' }}">
            <h3 title="{{ $menu->nama_menu }}" class="menu-title">{{ $menu->nama_menu }}</h3>
            <div class="price">Rp. {{ number_format($menu->harga, 0, ',', '.') }}</div>
            <div class="btn-group">
                {{-- Form untuk tambah ke keranjang --}}
                <form action="{{ route('keranjang.add') }}" method="POST" style="flex:1;">
                    @csrf
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    <input type="hidden" name="jumlah" value="1">
                    <button type="submit" class="btn btn-keranjang">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                    </button>
                </form>
            </div>
            <div class="share-group" style="margin-top:8px;display:flex;gap:8px;">
                <a href="https://wa.me/?text={{ urlencode('Cek menu ' . $menu->nama_menu . ' di ' . route('menu.detail', $menu->id)) }}" target="_blank" title="Bagikan ke WhatsApp">
                    <i class="fab fa-whatsapp" style="color:#25D366;font-size:1.5em;"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('menu.detail', $menu->id)) }}" target="_blank" title="Bagikan ke Facebook">
                    <i class="fab fa-facebook" style="color:#1877F3;font-size:1.5em;"></i>
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection

<!-- Modal Detail Menu -->
<div id="menuDetailModal" class="modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
    <div style="background:#fff;padding:24px 16px;border-radius:12px;max-width:400px;width:90%;position:relative;max-height:90vh;overflow-y:auto;">
        <button id="closeMenuDetailModal" style="position:absolute;top:8px;right:12px;font-size:1.5em;background:none;border:none;cursor:pointer;">&times;</button>
        <img id="modalMenuGambar" src="" alt="" style="width:100%;border-radius:8px;max-height:180px;object-fit:cover;">
        <h2 id="modalMenuNama" style="margin-top:12px;font-size:1.3em;font-weight:bold;line-height:1.4;word-wrap:break-word;hyphens:auto;"></h2>
        <div id="modalMenuHarga" style="color:#388e3c;font-weight:bold;margin-bottom:8px;"></div>
        <div id="modalMenuDeskripsi" style="color:#666;font-size:0.9em;margin-bottom:12px;line-height:1.4;text-align:center;display:none;"></div>
        <form id="modalKeranjangForm" action="{{ route('keranjang.add') }}" method="POST" style="margin-top:12px;">
            @csrf
            <input type="hidden" name="menu_id" id="modalMenuId" value="">
            <input type="hidden" name="jumlah" value="1">
            <button type="submit" class="btn btn-keranjang" style="width:100%;background:#388e3c;color:#fff;padding:10px 0;border-radius:6px;font-weight:bold;">
                <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggers = document.querySelectorAll('.menu-detail-trigger');
    const modal = document.getElementById('menuDetailModal');
    const closeBtn = document.getElementById('closeMenuDetailModal');
    const modalNama = document.getElementById('modalMenuNama');
    const modalGambar = document.getElementById('modalMenuGambar');
    const modalHarga = document.getElementById('modalMenuHarga');
    const modalDeskripsi = document.getElementById('modalMenuDeskripsi');
    const modalKeranjangForm = document.getElementById('modalKeranjangForm');
    const modalMenuId = document.getElementById('modalMenuId');

    triggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            modalNama.textContent = this.dataset.nama;
            modalGambar.src = this.dataset.gambar;
            modalHarga.textContent = this.dataset.harga;
            modalMenuId.value = this.dataset.id;
            
            // Tampilkan deskripsi jika ada
            if (this.dataset.deskripsi && this.dataset.deskripsi !== '-') {
                modalDeskripsi.textContent = this.dataset.deskripsi;
                modalDeskripsi.style.display = 'block';
            } else {
                modalDeskripsi.style.display = 'none';
            }
            
            modal.style.display = 'flex';
        });
    });
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Fitur: buka modal otomatis jika ada menu_id di URL
    const urlParams = new URLSearchParams(window.location.search);
    const menuId = urlParams.get('menu_id');
    if (menuId) {
        const target = document.querySelector('.menu-detail-trigger[data-id="'+menuId+'"]');
        if (target) {
            target.click();
        }
    }

    // Fitur: Deteksi judul yang terpotong dan atur tooltip
    const menuTitles = document.querySelectorAll('.menu-title');
    menuTitles.forEach(title => {
        if (title.scrollHeight > title.clientHeight || title.scrollWidth > title.clientWidth) {
            title.style.cursor = 'help';
        } else {
            title.style.cursor = 'default';
            title.removeAttribute('title');
        }
    });
});
</script>
@endpush
