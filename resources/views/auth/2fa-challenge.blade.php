<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi 2FA - Cash Bank</title>

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
    }

    .login-box {
      width: 440px;
      max-width: 95vw;
      margin: 1rem auto;
    }

    .card-glass {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.3);
      overflow: hidden;
    }

    .card-glass-header {
      background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
      color: #ffffff;
      padding: 1.75rem 1.5rem;
      text-align: center;
      position: relative;
    }

    .badge-2fa {
      background: rgba(56, 189, 248, 0.2);
      color: #38bdf8;
      border: 1px solid rgba(56, 189, 248, 0.35);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 1px;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      display: inline-block;
      margin-bottom: 0.75rem;
    }

    .otp-input {
      font-size: 1.85rem;
      letter-spacing: 8px;
      text-align: center;
      font-weight: 700;
      font-family: monospace;
      padding: 0.75rem;
      border: 2px solid #cbd5e1;
      border-radius: 12px;
      transition: all 0.2s ease;
      color: #0f172a;
    }

    .otp-input:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
      outline: none;
    }

    .btn-verify {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: none;
      padding: 0.8rem;
      font-weight: 700;
      font-size: 1rem;
      border-radius: 10px;
      transition: all 0.2s;
    }

    .btn-verify:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
    }
  </style>
</head>
<body class="login-page">

<div class="login-box">
  <div class="card-glass">
    <div class="card-glass-header">
      <span class="badge-2fa"><i class="fas fa-shield-alt mr-1"></i> VERIFIKASI KEAMANAN (2FA)</span>
      <h4 class="font-weight-bold mb-1">Google Authenticator</h4>
      <p class="small text-light mb-0" style="opacity: 0.85;">Autentikasi dua langkah untuk akun Administrator / Programmer</p>
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

      <div class="text-center mb-3">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-2" style="width: 56px; height: 56px; color: #2563eb;">
          <i class="fas fa-user-lock fa-2x"></i>
        </div>
        <div class="font-weight-bold text-dark" style="font-size: 1.05rem;">{{ $user->username }}</div>
        <small class="text-muted">Masukkan 6 digit kode dari aplikasi <strong>Google Authenticator</strong> Anda.</small>
      </div>

      <form action="{{ route('2fa.challenge.verify') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
          <input type="text" name="code" id="otpCodeInput" class="form-control otp-input"
                 placeholder="000000" maxlength="6" pattern="\d{6}" inputmode="numeric" autocomplete="one-time-code" autofocus required>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-verify">
          <i class="fas fa-sign-in-alt mr-2"></i> Verifikasi & Masuk
        </button>
      </form>

      <div class="text-center mt-3 pt-3 border-top">
        <a href="{{ route('2fa.cancel') }}" class="text-muted small">
          <i class="fas fa-arrow-left mr-1"></i> Batal & Login dengan akun lain
        </a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>
<script>
  // Auto submit when 6 digits are typed
  document.getElementById('otpCodeInput').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
    if (this.value.length === 6) {
      this.form.submit();
    }
  });
</script>
</body>
</html>
