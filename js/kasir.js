const barang = document.querySelectorAll('.barang');
const uangBayar = document.getElementById('uang_bayar');
const numpadButtons = document.querySelectorAll('.numpad-btn');

// PERUBAHAN JUMLAH PRODUK
barang.forEach(function(item) {
    const input = item.querySelector('.jumlah-input');
    input.addEventListener('input', function() {
            const harga = parseInt(item.dataset.harga);
            const stok =  parseInt(item.dataset.stok);
            let jumlah = parseInt(input.value) || 0;

            if (jumlah < 0) {
                jumlah = 0;
                input.value = 0;
            }
            if (jumlah > stok) {
                jumlah = stok;
                input.value = stok;
            }

            const subtotal = harga * jumlah;
            item.querySelector('.subtotal').textContent = formatRupiah(subtotal);
            updateRingkasanBarang();
            hitungTotal();
        }
    );
});

// RINGKASAN BARANG
function updateRingkasanBarang() {
    const container = document.getElementById('ringkasanBarang');
    container.innerHTML = '';
    let adaBarang = false;

    barang.forEach(function(item) {
        const input = item.querySelector('.jumlah-input');
        const jumlah = parseInt(input.value) || 0;
        if (jumlah <= 0) {
            return;
        }

        adaBarang = true;
        const nama = item.querySelector('h3').textContent.trim();
        const harga =  parseInt(item.dataset.harga);
        const subtotal = harga * jumlah;
        const div = document.createElement('div');
        div.className = 'item-ringkasan';
        div.innerHTML = `
            <div class="item-ringkasan-nama">
                ${nama}
            </div>

            <div class="item-ringkasan-detail">
                ${jumlah} × Rp ${formatRupiah(harga)}
            </div>

            <div class="item-ringkasan-total">
                Rp ${formatRupiah(subtotal)}
            </div>
        `;
        container.appendChild(div);
    });

    if (!adaBarang) {
        container.innerHTML = `<p class="ringkasan-kosong">Belum ada barang dipilih.</p>`;
    }
}


// HITUNG TOTAL
function hitungTotal() {
    let totalBarang = 0;
    let totalHarga = 0;
    barang.forEach(function(item) {
        const harga = parseInt(item.dataset.harga);
        const jumlah = parseInt(item.querySelector('.jumlah-input').value) || 0;
        totalBarang += jumlah;
        totalHarga += harga * jumlah;
    });

    document.getElementById('totalBarang').textContent = totalBarang;
    document.getElementById('totalHarga').textContent = formatRupiah(totalHarga);
    hitungKembalian();
}


// HITUNG TOTAL HARGA
function hitungTotalHarga() {
    let totalHarga = 0;
    barang.forEach(function(item) {
        const harga = parseInt(item.dataset.harga);
        const jumlah = parseInt(item.querySelector('.jumlah-input').value) || 0;
        totalHarga += harga * jumlah;
    });
    return totalHarga;
}


// HITUNG KEMBALIAN
if (uangBayar) {
    uangBayar.addEventListener('input', function() {
            hitungKembalian();
        }
    );
}

function hitungKembalian() {
    const totalHarga = hitungTotalHarga();
    const bayar = parseInt(uangBayar.value) || 0;
    const kembali = bayar - totalHarga;
    document.getElementById('uangKembali').textContent = formatRupiah(kembali > 0 ? kembali : 0);
}

// FORMAT RUPIAH
function formatRupiah(angka) {
    return angka.toLocaleString('id-ID');
}

// NUMPAD PEMBAYARAN
numpadButtons.forEach(button => {
    button.addEventListener('click', function () {
        /* ANGKA */
        if (this.dataset.number !== undefined) {
            const angka = this.dataset.number;
            uangBayar.value += angka;
            hitungKembalian();
            uangBayar.focus();
            return;
        }

        /* CLEAR */
        if (this.classList.contains('numpad-clear')) {
            uangBayar.value = '';
            hitungKembalian();
            uangBayar.focus();
            return;
        }

        /* BACKSPACE */
        if (this.classList.contains('numpad-backspace')) {
            uangBayar.value = uangBayar.value.slice(0, -1);
            hitungKembalian();
            uangBayar.focus();
        }
    });
});

// SUBMIT TRANSAKSI
const formTransaksi = document.getElementById('formTransaksi');

if (formTransaksi) {
    formTransaksi.addEventListener('submit', function(event) {
            const form = this;

            // HAPUS HIDDEN INPUT LAMA
            form.querySelectorAll('.data-barang') .forEach(function(input) {input.remove();});
            let adaProduk = false;

            // AMBIL PRODUK DENGAN JUMLAH > 0
            barang.forEach(function(item) {
                const jumlahInput = item.querySelector('.jumlah-input');
                const jumlah = parseInt(jumlahInput.value) || 0;
                if (jumlah > 0) {
                    adaProduk = true;
                    const idBarang = item.dataset.id;

                    // ID BARANG
                    const inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id_barang[]';
                    inputId.value = idBarang;
                    inputId.classList.add('data-barang');

                    // JUMLAH BARANG
                    const inputJumlah = document.createElement('input');
                    inputJumlah.type = 'hidden';
                    inputJumlah.name = 'jumlah_barang[]';
                    inputJumlah.value = jumlah;
                    inputJumlah.classList.add('data-barang');

                    // MASUKKAN KE FORM
                    form.appendChild(inputId);
                    form.appendChild(inputJumlah);
                }
            });

            // TIDAK ADA PRODUK
            if (!adaProduk) {
                event.preventDefault();
                alert('Masukkan jumlah minimal satu produk.');
                return;
            }

            // CEK PEMBAYARAN
            const totalHarga = hitungTotalHarga();
            const bayar = parseInt(uangBayar.value) || 0;

            if (bayar < totalHarga) {
                event.preventDefault();
                alert('Uang bayar kurang.');
                return;
            }
        }
    );
}


// TOMBOL MINUS
document.querySelectorAll('.btn-minus').forEach(function(button) {
        button.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                let value = parseInt(input.value) || 0;
                const min = parseInt(input.min) || 0;

                if (value > min) {
                    input.value = value - 1;
                    input.dispatchEvent(new Event('input',{bubbles: true}));
                }
            }
        );
    });

// TOMBOL PLUS
document.querySelectorAll('.btn-plus').forEach(function(button) {
        button.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                let value = parseInt(input.value) || 0;
                const max = parseInt(input.max);

                if (value < max) {
                    input.value = value + 1;
                    input.dispatchEvent(new Event('input', {bubbles: true}));
                }
            }
        );
    });