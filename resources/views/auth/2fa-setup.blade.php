<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup 2FA Google Authenticator - Cash Bank</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminLTE/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/adminlte.min.css') }}">

  <style>
    body.login-page {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f3460 100%) !important;
      min-height: 100vh;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-family: 'Source Sans Pro', sans-serif;
      padding: 1.5rem 0;
    }

    .setup-box {
      width: 520px;
      max-width: 95vw;
      margin: 1rem auto;
    }

    .card-glass {
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card-glass-header {
      background: linear-gradient(135deg, #065f46 0%, #047857 100%);
      color: #ffffff;
      padding: 1.75rem 1.5rem;
      text-align: center;
    }

    .badge-setup {
      background: rgba(167, 243, 208, 0.25);
      color: #a7f3d0;
      border: 1px solid rgba(167, 243, 208, 0.4);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 1px;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      display: inline-block;
      margin-bottom: 0.75rem;
    }

    .qr-container {
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: 14px;
      padding: 1rem;
      display: inline-block;
      margin-bottom: 1rem;
    }

    .otp-input {
      font-size: 1.75rem;
      letter-spacing: 8px;
      text-align: center;
      font-weight: 700;
      font-family: monospace;
      padding: 0.65rem;
      border: 2px solid #cbd5e1;
      border-radius: 12px;
    }

    .otp-input:focus {
      border-color: #059669;
      box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15);
      outline: none;
    }

    .secret-badge {
      font-family: monospace;
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: 2px;
      background: #f1f5f9;
      border: 1px solid #cbd5e1;
      padding: 0.4rem 0.8rem;
      border-radius: 8px;
      color: #0f172a;
      display: inline-block;
    }
  </style>
</head>
<body class="login-page">

<div class="setup-box">
  <div class="card-glass">
    <div class="card-glass-header">
      <span class="badge-setup"><i class="fas fa-qrcode mr-1"></i> AKTIVASI KEAMANAN 2FA</span>
      <h4 class="font-weight-bold mb-1">Setup Google Authenticator</h4>
      <p class="small text-light mb-0" style="opacity: 0.9;">Tingkatkan proteksi akun Programmer dari peretasan dan manipulasi data</p>
    </div>

    <div class="card-body p-4">
      @if(session('error'))
        <div class="alert alert-danger py-2 px-3 mb-3 small rounded-lg">
          <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger py-2 px-3 mb-3 small rounded-lg">
          <ul class="mb-0 pl-3">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- LANGKAH 1 --}}
      <div class="mb-3">
        <h6 class="font-weight-bold text-dark mb-1">
          <span class="badge badge-success mr-1">1</span> Pindai QR Code
        </h6>
        <p class="small text-muted mb-2">Buka aplikasi <strong>Google Authenticator</strong> atau <strong>Microsoft Authenticator</strong> di HP Anda, lalu arahkan kamera ke QR Code berikut:</p>
        
        <div class="text-center">
          <div class="qr-container">
            <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" width="180" height="180" style="border-radius: 8px;">
          </div>
        </div>
      </div>

      {{-- LANGKAH 2 --}}
      <div class="mb-3 p-3 bg-light rounded-lg border">
        <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.9rem;">
          <span class="badge badge-secondary mr-1">2</span> Atau Masukkan Kode Kunci Manual
        </h6>
        <p class="small text-muted mb-2">Jika kamera tidak dapat memindai, pilih <em>"Enter a setup key"</em> pada aplikasi dan masukkan kode ini:</p>
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.5rem;">
          <span class="secret-badge" id="secretText">{{ $secret }}</span>
          <button type="button" id="copySecretBtn" class="btn btn-sm btn-outline-primary font-weight-bold" onclick="copySecret()">
            <i class="fas fa-copy mr-1"></i> Salin Kunci
          </button>
        </div>
        <div id="copyFeedback" class="text-success small font-weight-bold mt-2" style="display: none; transition: opacity 0.3s ease;">
          <i class="fas fa-check-circle mr-1"></i> Kunci berhasil disalin ke clipboard!
        </div>
      </div>

      {{-- LANGKAH 3 --}}
      <div class="mb-2">
        <h6 class="font-weight-bold text-dark mb-1">
          <span class="badge badge-success mr-1">3</span> Masukkan Kode Verifikasi 6 Digit
        </h6>
        <p class="small text-muted mb-2">Ketikkan 6 digit angka dari aplikasi authenticator Anda (otomatis terverifikasi saat 6 digit terisi):</p>

        <form action="{{ route('2fa.setup.confirm') }}" method="POST" id="setup2faForm">
          @csrf
          <div class="form-group mb-2">
            <input type="text" name="code" id="setupCodeInput" class="form-control otp-input"
                   placeholder="000000" maxlength="6" pattern="\d{6}" inputmode="numeric" autocomplete="one-time-code" required autofocus>
          </div>
          <div id="submittingStatus" class="text-center text-success font-weight-bold small py-1" style="display: none;">
            <i class="fas fa-circle-notch fa-spin mr-1"></i> Memverifikasi kode & mengaktifkan 2FA...
          </div>
        </form>
      </div>

      <div class="text-center mt-3 pt-3 border-top">
        <a href="{{ route('2fa.cancel') }}" class="text-muted small">
          <i class="fas fa-arrow-left mr-1"></i> Batal & Kembali ke Login
        </a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>
<script>
  let copyTimer = null;
  function copySecret() {
    let text = document.getElementById('secretText').innerText.trim();
    let btn = document.getElementById('copySecretBtn');
    let feedback = document.getElementById('copyFeedback');

    function notifySuccess() {
      if (btn) {
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Berhasil Disalin!';
      }
      if (feedback) {
        feedback.style.display = 'block';
        feedback.style.opacity = '1';
      }

      if (copyTimer) clearTimeout(copyTimer);
      copyTimer = setTimeout(function() {
        if (btn) {
          btn.classList.remove('btn-success');
          btn.classList.add('btn-outline-primary');
          btn.innerHTML = '<i class="fas fa-copy mr-1"></i> Salin Kunci';
        }
        if (feedback) {
          feedback.style.opacity = '0';
          setTimeout(function() {
            feedback.style.display = 'none';
          }, 300);
        }
      }, 2500);
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(notifySuccess).catch(function() {
        fallbackCopy(text, notifySuccess);
      });
    } else {
      fallbackCopy(text, notifySuccess);
    }
  }

  function fallbackCopy(text, callback) {
    let temp = document.createElement('textarea');
    temp.value = text;
    temp.style.position = 'fixed';
    temp.style.left = '-9999px';
    document.body.appendChild(temp);
    temp.focus();
    temp.select();
    try {
      document.execCommand('copy');
      if (callback) callback();
    } catch (e) {
      console.error('Copy failed', e);
    }
    document.body.removeChild(temp);
  }

  const setupForm = document.getElementById('setup2faForm');
  const setupInput = document.getElementById('setupCodeInput');
  const setupStatus = document.getElementById('submittingStatus');

  setupInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
    if (this.value.length === 6) {
      if (setupStatus) setupStatus.style.display = 'block';
      this.readOnly = true;
      setupForm.submit();
    }
  });
</script>
</body>
</html>
