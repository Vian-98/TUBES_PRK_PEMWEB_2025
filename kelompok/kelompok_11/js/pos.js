// ========================================
// POS.JS - SISTEM POS CARD-BASED UI
// ========================================

// Array untuk menyimpan semua item transaksi
let cartItems = [];
let itemCounter = 0;

// ========================================
// FUNGSI SWITCH TAB
// ========================================
function switchTab(tab) {
  const tabLayanan = document.getElementById('tabLayanan');
  const tabSparepart = document.getElementById('tabSparepart');
  const katalogLayanan = document.getElementById('katalogLayanan');
  const katalogSparepart = document.getElementById('katalogSparepart');

  if (tab === 'layanan') {
    tabLayanan.classList.add('tab-active');
    tabSparepart.classList.remove('tab-active');
    katalogLayanan.classList.remove('hidden');
    katalogSparepart.classList.add('hidden');
  } else {
    tabSparepart.classList.add('tab-active');
    tabLayanan.classList.remove('tab-active');
    katalogSparepart.classList.remove('hidden');
    katalogLayanan.classList.add('hidden');
  }

  document.getElementById('searchInput').value = '';
  searchItems();
}

// ========================================
// FUNGSI SEARCH
// ========================================
function searchItems() {
  const input = document.getElementById('searchInput').value.toLowerCase();
  const cards = document.querySelectorAll('.card-item');

  cards.forEach((card) => {
    const nama = card.getAttribute('data-nama');
    if (nama.includes(input)) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

// ========================================
// FUNGSI TAMBAH KE KERANJANG
// ========================================
function tambahKeKeranjang(tipe, id, nama, harga, stokMax) {
  const itemId = tipe === 'service' ? 's_' + id : 'p_' + id;
  const existingItem = cartItems.find((item) => item.id === itemId);

  if (existingItem) {
    if (tipe === 'part' && existingItem.qty >= stokMax) {
      alert('⚠️ Stok tidak mencukupi! Stok tersedia: ' + stokMax);
      return;
    }
    existingItem.qty++;
    existingItem.subtotal = existingItem.qty * existingItem.harga;
  } else {
    const newItem = {
      uniqueId: 'item_' + ++itemCounter,
      id: itemId,
      tipe: tipe,
      serviceId: tipe === 'service' ? id : null,
      partId: tipe === 'part' ? id : null,
      nama: nama,
      harga: harga,
      qty: 1,
      subtotal: harga,
      stokMax: stokMax,
    };
    cartItems.push(newItem);
  }

  renderCart();
  hitungTotal();
  showToast('✓ ' + nama + ' ditambahkan');
}

// ========================================
// FUNGSI RENDER KERANJANG (CARD-BASED)
// ========================================
function renderCart() {
  const container = document.getElementById('keranjangItems');
  const emptyCart = document.getElementById('emptyCart');
  const cartCount = document.getElementById('cartCount');

  if (cartItems.length === 0) {
    if (emptyCart) emptyCart.style.display = 'block';
    const cards = container.querySelectorAll('.cart-card');
    cards.forEach((card) => card.remove());
    cartCount.textContent = '0';
    return;
  }

  if (emptyCart) emptyCart.style.display = 'none';

  // Hapus card yang ada
  const cards = container.querySelectorAll('.cart-card');
  cards.forEach((card) => card.remove());

  // Render setiap item sebagai card
  cartItems.forEach((item, index) => {
    const card = document.createElement('div');
    card.className =
      'cart-card bg-gradient-to-r from-gray-50 to-white border-2 border-gray-200 rounded-lg p-4 flex items-center gap-4 hover:shadow-md transition';

    card.innerHTML = `
            <div class="flex-shrink-0 w-16 h-16 rounded-lg ${
              item.tipe === 'service' ? 'bg-blue-100' : 'bg-green-100'
            } flex items-center justify-center">
                <i class="fas ${
                  item.tipe === 'service' ? 'fa-tools text-blue-600' : 'fa-box text-green-600'
                } text-2xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-gray-800 mb-1">${item.nama}</h4>
                <p class="text-sm text-gray-600">Rp ${formatRupiah(item.harga)} × ${item.qty}</p>
                ${
                  item.stokMax
                    ? '<p class="text-xs text-gray-500">Stok tersedia: ' + item.stokMax + '</p>'
                    : ''
                }
            </div>
            <div class="flex items-center gap-2">
                <button onclick="updateQty(${index}, -1)" 
                        class="w-8 h-8 rounded-full bg-red-500 hover:bg-red-600 text-white font-bold transition flex items-center justify-center">
                    <i class="fas fa-minus"></i>
                </button>
                <span class="w-12 text-center font-bold text-lg">${item.qty}</span>
                <button onclick="updateQty(${index}, 1)" 
                        class="w-8 h-8 rounded-full bg-green-500 hover:bg-green-600 text-white font-bold transition flex items-center justify-center">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-gray-800">Rp ${formatRupiah(item.subtotal)}</p>
                <button onclick="hapusItem(${index})" 
                        class="text-red-600 hover:text-red-700 text-sm mt-1">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        `;

    container.appendChild(card);
  });

  cartCount.textContent = cartItems.length;
}

// ========================================
// FUNGSI UPDATE QTY
// ========================================
function updateQty(index, delta) {
  const item = cartItems[index];
  const newQty = item.qty + delta;

  if (newQty < 1) {
    if (confirm('🗑️ Hapus item ini dari keranjang?')) {
      cartItems.splice(index, 1);
    }
  } else {
    if (item.tipe === 'part' && item.stokMax && newQty > item.stokMax) {
      alert('⚠️ Stok tidak mencukupi! Stok tersedia: ' + item.stokMax);
      return;
    }
    item.qty = newQty;
    item.subtotal = item.qty * item.harga;
  }

  renderCart();
  hitungTotal();
}

// ========================================
// FUNGSI HAPUS ITEM
// ========================================
function hapusItem(index) {
  if (confirm('🗑️ Hapus item ini dari keranjang?')) {
    cartItems.splice(index, 1);
    renderCart();
    hitungTotal();
  }
}

// ========================================
// FUNGSI HITUNG TOTAL
// ========================================
function hitungTotal() {
  let total = 0;
  cartItems.forEach((item) => {
    total += item.subtotal;
  });

  document.getElementById('totalHarga').textContent = 'Rp ' + formatRupiah(total);
  hitungGrandTotal();
}

// ========================================
// FUNGSI HITUNG GRAND TOTAL
// ========================================
function hitungGrandTotal() {
  let total = 0;
  cartItems.forEach((item) => {
    total += item.subtotal;
  });

  const diskon = parseFloat(document.getElementById('diskon').value) || 0;
  const grandTotal = total - diskon;

  document.getElementById('grandTotal').textContent = 'Rp ' + formatRupiah(grandTotal);
  hitungKembalian();
}

// ========================================
// FUNGSI HITUNG KEMBALIAN
// ========================================
function hitungKembalian() {
  let total = 0;
  cartItems.forEach((item) => {
    total += item.subtotal;
  });

  const diskon = parseFloat(document.getElementById('diskon').value) || 0;
  const grandTotal = total - diskon;
  const bayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
  const kembalian = bayar - grandTotal;

  const kembalianEl = document.getElementById('kembalian');

  if (kembalian < 0) {
    kembalianEl.textContent = 'Kurang: Rp ' + formatRupiah(Math.abs(kembalian));
    kembalianEl.className = 'text-xl font-bold text-red-600';
  } else {
    kembalianEl.textContent = 'Rp ' + formatRupiah(kembalian);
    kembalianEl.className = 'text-xl font-bold text-green-600';
  }
}

// ========================================
// FUNGSI PROSES PEMBAYARAN
// ========================================
function prosesPembayaran() {
  if (cartItems.length === 0) {
    alert('⚠️ Keranjang masih kosong!');
    return;
  }

  const namaPelanggan = document.getElementById('namaPelanggan').value.trim();
  if (!namaPelanggan) {
    alert('⚠️ Nama pelanggan harus diisi!');
    return;
  }

  let total = 0;
  cartItems.forEach((item) => {
    total += item.subtotal;
  });

  const diskon = parseFloat(document.getElementById('diskon').value) || 0;
  const grandTotal = total - diskon;
  const metodePembayaran = document.getElementById('metodePembayaran').value;

  // Cek jika metode QRIS
  if (metodePembayaran === 'qris') {
    prosesQRIS(total, diskon, grandTotal, namaPelanggan);
    return;
  }

  // Untuk metode lain (tunai/transfer)
  const bayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
  const kembalian = bayar - grandTotal;

  if (bayar < grandTotal) {
    alert('⚠️ Jumlah pembayaran kurang! Kekurangan: Rp ' + formatRupiah(grandTotal - bayar));
    return;
  }

  if (!confirm('✅ Proses transaksi sebesar Rp ' + formatRupiah(grandTotal) + '?')) {
    return;
  }

  const formData = new FormData();
  formData.append('items', JSON.stringify(cartItems));
  formData.append('nama_pelanggan', namaPelanggan);
  formData.append('telepon_pelanggan', document.getElementById('teleponPelanggan').value.trim());
  formData.append('total', total);
  formData.append('diskon', diskon);
  formData.append('grand_total', grandTotal);
  formData.append('bayar', bayar);
  formData.append('kembali', kembalian);
  formData.append('metode_pembayaran', metodePembayaran);
  formData.append('reservation_id', document.getElementById('reservationId').value);
  formData.append('draft_transaction_id', document.getElementById('draftTransactionId').value);

  fetch('proses_transaksi.php', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 'success') {
        alert('✅ Transaksi berhasil!\nKode: ' + data.kode_transaksi);
        window.location.href = 'struk.php?id=' + data.transaction_id;
      } else {
        alert('❌ Error: ' + data.message);
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      alert('❌ Terjadi kesalahan saat memproses transaksi!');
    });
}

// ========================================
// FUNGSI PROSES QRIS
// ========================================
function prosesQRIS(total, diskon, grandTotal, namaPelanggan) {
  const payload = {
    items: cartItems,
    nama_pelanggan: namaPelanggan,
    telepon_pelanggan: document.getElementById('teleponPelanggan').value.trim(),
    total: total,
    diskon: diskon,
    grand_total: grandTotal,
    reservation_id: document.getElementById('reservationId').value,
    draft_transaction_id: document.getElementById('draftTransactionId').value,
  };

  fetch('generate_qris.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 'success') {
        showQRISModal(data);
        startPaymentPolling(data.kode_transaksi, data.transaction_id);
      } else {
        alert('❌ Error: ' + data.message);
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      alert('❌ Terjadi kesalahan saat generate QRIS!');
    });
}

// ========================================
// FUNGSI TAMPILKAN MODAL QRIS
// ========================================
function showQRISModal(data) {
  const modal = document.getElementById('modalQRIS');
  const qrContainer = document.getElementById('qrCodeContainer');
  const qrAmount = document.getElementById('qrAmount');
  const qrCode = document.getElementById('qrCode');
  const qrExpiry = document.getElementById('qrExpiry');

  qrAmount.textContent = 'Rp ' + formatRupiah(data.amount);
  qrCode.textContent = data.kode_transaksi;
  qrContainer.innerHTML =
    '<img src="' + data.qr_image_url + '" alt="QR Code" class="mx-auto" style="max-width: 300px;">';

  const expiredDate = new Date(data.expired_at);
  qrExpiry.textContent = expiredDate.toLocaleString('id-ID');

  modal.classList.remove('hidden');
}

// ========================================
// FUNGSI CLOSE MODAL QRIS
// ========================================
function closeQRISModal(skipConfirm = false) {
  if (window.pollingInterval) {
    clearInterval(window.pollingInterval);
  }

  const modal = document.getElementById('modalQRIS');
  modal.classList.add('hidden');

  // FIX: Jangan tanya konfirmasi jika pembayaran berhasil (skipConfirm = true)
  if (!skipConfirm && confirm('Batalkan transaksi QRIS?')) {
    resetForm();
  }
}

// ========================================
// FUNGSI POLLING STATUS PEMBAYARAN
// ========================================
function startPaymentPolling(kodeTransaksi, transactionId) {
  let elapsedTime = 0;
  const maxTime = 300; // 5 menit

  window.pollingInterval = setInterval(async () => {
    try {
      const response = await fetch('check_payment_status.php?tx=' + kodeTransaksi);
      const result = await response.json();

      if (result.status === 'success') {
        if (result.payment_status === 'paid') {
          clearInterval(window.pollingInterval);
          // FIX: Close modal tanpa konfirmasi (skipConfirm = true)
          const modal = document.getElementById('modalQRIS');
          modal.classList.add('hidden');
          alert('✅ Pembayaran QRIS berhasil!\nKode: ' + kodeTransaksi);
          window.location.href = 'struk.php?id=' + transactionId;
        } else if (result.payment_status === 'expired' || result.is_expired) {
          clearInterval(window.pollingInterval);
          alert('⏰ QRIS telah expired. Silakan buat transaksi baru.');
          closeQRISModal();
        }
      }

      elapsedTime += 3;

      if (elapsedTime >= maxTime) {
        clearInterval(window.pollingInterval);
        alert('⏰ Waktu pembayaran habis. QRIS expired.');
        closeQRISModal();
      }
    } catch (error) {
      console.error('Polling error:', error);
    }
  }, 3000); // Polling setiap 3 detik
}

// ========================================
// FUNGSI RESET FORM
// ========================================
function resetForm() {
  if (confirm('🔄 Reset semua data transaksi?')) {
    cartItems = [];
    renderCart();
    hitungTotal();

    document.getElementById('namaPelanggan').value = '';
    document.getElementById('teleponPelanggan').value = '';
    document.getElementById('diskon').value = '0';
    document.getElementById('jumlahBayar').value = '0';
    document.getElementById('metodePembayaran').selectedIndex = 0;

    hitungKembalian();
  }
}

// ========================================
// FUNGSI TAMBAH ITEM MANUAL (untuk draft)
// ========================================
function tambahItemManual(data) {
  const newItem = {
    uniqueId: 'item_' + ++itemCounter,
    id: data.id,
    tipe: data.tipe,
    serviceId: data.tipe === 'service' ? data.id.replace('s_', '') : null,
    partId: data.tipe === 'part' ? data.id.replace('p_', '') : null,
    nama: data.nama,
    harga: parseFloat(data.harga),
    qty: parseInt(data.qty),
    subtotal: parseFloat(data.harga) * parseInt(data.qty),
    stokMax: data.stokMax || null,
  };

  cartItems.push(newItem);
  renderCart();
  hitungTotal();
}

// ========================================
// FUNGSI FORMAT RUPIAH
// ========================================
function formatRupiah(angka) {
  return new Intl.NumberFormat('id-ID').format(angka);
}

// ========================================
// FUNGSI SHOW TOAST
// ========================================
function showToast(message) {
  // Cek apakah toast container sudah ada
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
    document.body.appendChild(toastContainer);
  }

  const toast = document.createElement('div');
  toast.className = 'bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg animate-slide-in';
  toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;

  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 2000);
}

// ========================================
// KEYBOARD SHORTCUTS
// ========================================
document.addEventListener('keydown', function (e) {
  if (e.key === 'F2') {
    e.preventDefault();
    switchTab('layanan');
  }
  if (e.key === 'F3') {
    e.preventDefault();
    switchTab('sparepart');
  }
  if (e.key === 'F9') {
    e.preventDefault();
    prosesPembayaran();
  }
});

// ========================================
// FUNGSI TAMBAH LAYANAN
// ========================================
function tambahLayanan() {
  const select = document.getElementById('selectLayanan');
  const selectedOption = select.options[select.selectedIndex];

  if (!selectedOption.value) {
    alert('⚠️ Silakan pilih layanan terlebih dahulu!');
    return;
  }

  const serviceId = selectedOption.value;
  const nama = selectedOption.getAttribute('data-nama');
  const harga = parseFloat(selectedOption.getAttribute('data-harga'));

  // Cek apakah layanan sudah ada di cart
  const existingItem = cartItems.find((item) => item.id === 's_' + serviceId);
  if (existingItem) {
    alert('⚠️ Layanan ini sudah ditambahkan!');
    return;
  }

  // Tambah item ke cart
  const newItem = {
    uniqueId: 'item_' + ++itemCounter,
    id: 's_' + serviceId,
    tipe: 'service',
    serviceId: serviceId,
    partId: null,
    nama: nama,
    harga: harga,
    qty: 1,
    subtotal: harga,
    stokMax: null,
  };

  cartItems.push(newItem);

  // Reset dropdown
  select.selectedIndex = 0;

  // Render ulang tabel
  renderCart();
  hitungTotal();
}

// ========================================
// FUNGSI TAMBAH SPAREPART
// ========================================
function tambahPart() {
  const select = document.getElementById('selectPart');
  const selectedOption = select.options[select.selectedIndex];

  if (!selectedOption.value) {
    alert('⚠️ Silakan pilih sparepart terlebih dahulu!');
    return;
  }

  const partId = selectedOption.value;
  const nama = selectedOption.getAttribute('data-nama');
  const harga = parseFloat(selectedOption.getAttribute('data-harga'));
  const stok = parseInt(selectedOption.getAttribute('data-stok'));

  // Cek apakah part sudah ada di cart
  const existingItem = cartItems.find((item) => item.id === 'p_' + partId);
  if (existingItem) {
    // Jika sudah ada, tambah qty
    if (existingItem.qty >= stok) {
      alert('⚠️ Stok tidak mencukupi! Stok tersedia: ' + stok);
      return;
    }
    existingItem.qty++;
    existingItem.subtotal = existingItem.qty * existingItem.harga;
  } else {
    // Tambah item baru
    const newItem = {
      uniqueId: 'item_' + ++itemCounter,
      id: 'p_' + partId,
      tipe: 'part',
      serviceId: null,
      partId: partId,
      nama: nama,
      harga: harga,
      qty: 1,
      subtotal: harga,
      stokMax: stok,
    };

    cartItems.push(newItem);
  }

  // Reset dropdown
  select.selectedIndex = 0;

  // Render ulang tabel
  renderCart();
  hitungTotal();
}

// ========================================
// FUNGSI TAMBAH ITEM MANUAL (untuk load draft)
// ========================================
function tambahItemManual(data) {
  const newItem = {
    uniqueId: 'item_' + ++itemCounter,
    id: data.id,
    tipe: data.tipe,
    serviceId: data.tipe === 'service' ? data.id.replace('s_', '') : null,
    partId: data.tipe === 'part' ? data.id.replace('p_', '') : null,
    nama: data.nama,
    harga: parseFloat(data.harga),
    qty: parseInt(data.qty),
    subtotal: parseFloat(data.harga) * parseInt(data.qty),
    stokMax: data.stokMax || null,
  };

  cartItems.push(newItem);
  renderCart();
  hitungTotal();
}

// ========================================
// FUNGSI FORMAT RUPIAH
// ========================================
function formatRupiah(angka) {
  return new Intl.NumberFormat('id-ID').format(angka);
}

// ========================================
// KEYBOARD SHORTCUTS
// ========================================
document.addEventListener('keydown', function (e) {
  // F2 = Focus ke layanan
  if (e.key === 'F2') {
    e.preventDefault();
    document.getElementById('selectLayanan').focus();
  }

  // F3 = Focus ke sparepart
  if (e.key === 'F3') {
    e.preventDefault();
    document.getElementById('selectPart').focus();
  }

  // F9 = Proses pembayaran
  if (e.key === 'F9') {
    e.preventDefault();
    prosesPembayaran();
  }
});
