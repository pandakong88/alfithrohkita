<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Pedoman Santri | Official Digital Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            overflow-x: hidden;
            transition: background 0.5s ease;
            touch-action: manipulation; /* Optimasi sentuhan mobile */
        }
        .hero-section {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 60px 0;
            color: white;
            border-radius: 0 0 40px 40px;
            text-align: center;
        }
        .main-card {
            border: none;
            border-radius: 24px;
            background: white;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            margin-top: -40px;
        }
        #btn-prank {
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 9999;
            white-space: nowrap;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            touch-action: none;
        }
        @keyframes shake {
            0% { transform: translate(2px, 2px) rotate(0deg); }
            10% { transform: translate(-2px, -3px) rotate(-1deg); }
            30% { transform: translate(-4px, 0px) rotate(1deg); }
            100% { transform: translate(2px, -3px) rotate(-1deg); }
        }
        .shake-screen { animation: shake 0.3s infinite; }
        .chaos-btn { position: fixed !important; z-index: 9998; cursor: pointer; }
        .fade-out { opacity: 0; transition: 0.8s; }
    </style>
</head>
<body>

<div class="hero-section">
    <div class="container px-4">
        <h1 id="main-title" class="fw-bold fs-2">📘 Pusat Pedoman Santri</h1>
        <p id="sub-title" class="opacity-75 small">Akses dokumen tata tertib santri terbaru.</p>
    </div>
</div>

<div class="container py-4">
    @if($latest)
    <div class="row justify-content-center mb-4">
        <div class="col-lg-7 col-md-9">
            <div class="card main-card border-0">
                <div class="card-body p-4 p-md-5 text-center">
                    <span id="status-badge" class="badge bg-success-subtle text-success px-3 py-2 rounded-pill mb-3">
                        <i class="fas fa-check-circle me-1"></i> Versi Aktif: {{ $latest->version }}
                    </span>
                    <h3 id="ver-text" class="fw-bold mb-4">Update: {{ $latest->release_date->format('d/m/y') }}</h3>
                    
                    <div id="prank-container" style="min-height: 120px; position: relative;" class="d-flex align-items-center justify-content-center">
                        <a href="{{ route('public.handbook.download', $latest->id) }}" id="btn-prank" class="btn btn-primary btn-lg px-4 py-3 rounded-pill fw-bold">
                            <i class="fas fa-file-download me-2"></i> <span id="btn-text">Download Pedoman PDF</span>
                        </a>
                    </div>
                    <p id="footer-note" class="text-muted mt-3 small">PDF (2.4 MB)</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h5 class="fw-bold mb-3 text-center">Riwayat Versi</h5>
            <div class="table-responsive bg-white rounded-3 shadow-sm">
                <table class="table table-sm mb-0">
                    <tbody class="small">
                        @foreach($history as $item)
                        <tr class="align-middle">
                            <td class="p-3 fw-bold">{{ $item->version }}</td>
                            <td class="text-muted">{{ $item->release_date->format('d/m/y') }}</td>
                            <td class="text-end p-3">
                                <span class="badge bg-light text-dark border">Locked</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const btn = document.getElementById('btn-prank');
    const btnText = document.getElementById('btn-text');
    const title = document.getElementById('main-title');
    const subTitle = document.getElementById('sub-title');
    let attempt = 0;
    let isRecovering = false;

    const jokes = ["Wlee..!", "Gak Kena!", "Ups!", "Kabuur!", "Eitss!", "Coba Lagi!", "Hampir!", "Wkwk!"];

    function moveButton() {
        if (attempt >= 12 || isRecovering) return;

        attempt++;
        if (attempt < 10) {
            // Kalkulasi area aman agar tidak keluar layar HP
            const padding = 20;
            const x = Math.random() * (window.innerWidth - btn.clientWidth - padding);
            const y = Math.random() * (window.innerHeight - btn.clientHeight - padding);
            
            btn.style.position = 'fixed';
            btn.style.left = Math.max(padding, x) + 'px';
            btn.style.top = Math.max(padding, y) + 'px';
            btn.classList.replace('btn-primary', 'btn-danger');
            btnText.innerText = jokes[Math.floor(Math.random() * jokes.length)];
        } else {
            startChaos();
        }
    }

    // Support Mouse (Desktop) & Touch (Mobile)
    btn.addEventListener('mouseover', moveButton);
    btn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        moveButton();
    });

    function startChaos() {
        if (isRecovering) return;
        document.body.classList.add('shake-screen');
        title.innerText = "SISTEM OVERLOAD!!!";
        title.classList.add('text-danger');
        btn.style.display = 'none';

        // Munculkan tombol palsu
        for (let i = 0; i < 25; i++) {
            setTimeout(() => {
                if (isRecovering) return;
                let f = document.createElement('button');
                f.className = "btn btn-danger chaos-btn shadow-sm btn-sm px-3 rounded-pill";
                f.innerHTML = "DOWNLOAD!";
                f.style.left = Math.random() * 80 + 'vw';
                f.style.top = Math.random() * 80 + 'vh';
                f.onclick = () => alert("Link Rusak! Coba lagi.");
                document.body.appendChild(f);
            }, i * 100);
        }

        // Mulai Pemulihan setelah 5 detik chaos
        setTimeout(restoreSystem, 5000);
    }

    function restoreSystem() {
        isRecovering = true;
        document.body.classList.remove('shake-screen');
        const fakes = document.querySelectorAll('.chaos-btn');
        fakes.forEach(el => el.classList.add('fade-out'));

        setTimeout(() => {
            fakes.forEach(el => el.remove());
            title.innerText = "Sistem Pulih Kembali";
            title.classList.replace('text-danger', 'text-white');
            subTitle.innerText = "Maaf atas ketidaknyamanannya. Silakan download sekarang.";
            
            // Kembalikan tombol asli ke tempatnya
            btn.style.position = 'relative';
            btn.style.left = '0';
            btn.style.top = '0';
            btn.style.display = 'inline-block';
            btn.classList.replace('btn-danger', 'btn-success');
            
            // Hitung mundur biar makin dramatis
            let count = 3;
            const timer = setInterval(() => {
                btnText.innerText = `Tunggu (${count}s)...`;
                btn.classList.add('disabled');
                count--;
                if (count < 0) {
                    clearInterval(timer);
                    btnText.innerText = "Download Sekarang (ASLI)";
                    btn.classList.remove('disabled');
                    attempt = 20; // Mengunci agar tidak lari lagi
                }
            }, 1000);
        }, 800);
    }

    // Blokir klik asli sebelum fase restore selesai
    btn.onclick = function(e) {
        if (attempt < 15) {
            e.preventDefault();
        }
    };
</script>

</body>
</html>