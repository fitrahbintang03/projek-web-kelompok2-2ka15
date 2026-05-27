// --- VARIABEL GLOBAL ---
let notifSudahMuncul = false;
let tugas = [];

// --- FUNGSI CORE (RENDER & CRUD) ---
function renderTugas() {
    const list = document.getElementById("listTugas");
    if (!list) return;
    list.innerHTML = "";

    let selesai = 0;
    let deadlineDekat = 0;

    tugas.forEach((item, index) => {
        let today = new Date();
        today.setHours(0, 0, 0, 0);
        let deadline = new Date(item.tanggal);
        deadline.setHours(0, 0, 0, 0);

        let selisih = (deadline - today) / (1000 * 60 * 60 * 24);

        if (selisih <= 3 && selisih >= 0 && !item.selesai) {
            deadlineDekat++;
        }

        if (item.selesai) selesai++;

        list.innerHTML += `
            <div class="task ${selisih <= 3 && !item.selesai ? 'deadline-warning' : ''}">
                <h3>${item.nama}</h3>
                <p>Mata Kuliah: ${item.matkul}</p>
                <p>Deadline: ${item.tanggal}</p>
                <p>Status: ${item.selesai ? "✅ Selesai" : "❌ Belum Selesai"}</p>
                <div class="task-buttons">
                    <button class="edit" onclick="editTugas(${index})">Edit</button>
                    <button class="hapus" onclick="hapusTugas(${index})">Hapus</button>
                    ${!item.selesai ? `<button class="selesai" onclick="tandaiSelesai(${index})">Tandai Selesai</button>` : ''}
                </div>
            </div>
        `;
    });

    if (document.getElementById("totalTugas")) document.getElementById("totalTugas").innerText = tugas.length;
    if (document.getElementById("selesai")) document.getElementById("selesai").innerText = selesai;
    if (document.getElementById("deadline")) document.getElementById("deadline").innerText = deadlineDekat;
}

// script.js

function filterTugas() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    
    // Mengambil semua elemen tugas (pastikan class-nya .task)
    let tasks = document.querySelectorAll('.task');
    
    tasks.forEach(task => {
        // Mengecek apakah teks dalam task mengandung input pencarian
        if (task.innerText.toLowerCase().includes(filter)) {
            task.style.display = ""; // Tampilkan
        } else {
            task.style.display = "none"; // Sembunyikan
        }
    });
}

// ... kode lainnya (cekTugasBaru, dll) tetap di sini ...
// --- FUNGSI LAINNYA (Tambah, Hapus, Edit, Navigasi) ---
function tambahTugas() { /* Isi fungsi tambah tugas Anda */ }
function hapusTugas(index) { /* Isi fungsi hapus tugas Anda */ }
function tandaiSelesai(index) { tugas[index].selesai = true; renderTugas(); }
function editTugas(index) { /* Isi fungsi edit tugas Anda */ }
function showPage(pageId, element) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active-page'));
    document.getElementById(pageId).classList.add('active-page');
    document.querySelectorAll('.menu li').forEach(li => li.classList.remove('active'));
    element.classList.add('active');
    let widgetBawah = document.getElementById('widgetBawah');
    if (widgetBawah) widgetBawah.style.display = (pageId === 'beranda' || pageId === 'tugas') ? 'block' : 'none';
}
function showDetail(id, matkul, judul, deadline) {
    document.getElementById("dIdTugas").value = id;
    document.getElementById("dMatkul").innerText = matkul;
    document.getElementById("dJudul").innerText = judul;
    document.getElementById("dDeadline").innerText = deadline;
    document.getElementById("modalDetail").style.display = "block";
}
function closeModal() { document.getElementById("modalDetail").style.display = "none"; }

// --- FUNGSI NOTIFIKASI (Diperbaiki) ---
function cekTugasBaru() {
    fetch('cek_tugas_baru.php')
        .then(response => response.json())
        .then(data => {
            if (data.ada_baru && !notifSudahMuncul) {
                notifSudahMuncul = true;
                const popUp = document.getElementById("popupNotifikasi");
                if (popUp) {
                    popUp.style.display = 'block';
                    setTimeout(() => { popUp.style.display = 'none'; }, 6000);
                }
            } else if (!data.ada_baru) {
                notifSudahMuncul = false;
            }
        })
        .catch(error => console.error('Gagal mengecek tugas baru:', error));
}

// --- INISIALISASI UTAMA (Hanya satu kali) ---
window.addEventListener("DOMContentLoaded", function () {
    // 1. Notifikasi sukses (bawaan lama)
    const successAlert = document.getElementById("successAlert");
    if (successAlert) {
        setTimeout(function () {
            successAlert.classList.add("fade-out");
            setTimeout(function () { successAlert.remove(); }, 500);
        }, 3000);
    }

    // 2. Inisialisasi pengecekan tugas baru
    cekTugasBaru();
    setInterval(cekTugasBaru, 10000);
});