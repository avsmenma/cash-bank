<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in Cash - Bank</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminLTE/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('adminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/adminlte.min.css') }}">

<style>
  /* ===== VIDEO BACKGROUND ===== */
  #bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
  }

  /* Dark overlay for readability */
  body.login-page::before {
    content: '';
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.25);
    z-index: -1;
  }

  body.login-page {
    background: #111 !important;
    background-image: none !important;
    min-height: 100vh;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  /* ===== GLASSMORPHISM CARD ===== */
  .login-box {
    position: relative;
    z-index: 1;
    width: 380px;
    max-width: 95vw;
  }

  .login-box .card {
    background: rgba(255, 255, 255, 0.12) !important;
    backdrop-filter: blur(20px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    border-radius: 16px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    overflow: hidden;
  }

  .login-box .card.card-outline {
    border-top: 3px solid rgba(40, 167, 69, 0.7) !important;
  }

  /* ===== CARD HEADER ===== */
  .login-box .card-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
    padding: 1.5rem 1.25rem 1rem !important;
  }

  /* Logo drop-shadow */
  .login-box .card-header img {
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
  }

  /* Title "Cash Bank" */
  .login-box .card-header h1,
  .login-box .card-header h1 b {
    color: #ffffff !important;
  }
  .login-box .card-header h1 .text-success {
    color: #5bda7d !important;
  }

  /* ===== CARD BODY ===== */
  .login-box .card-body {
    padding: 1.25rem 1.5rem !important;
  }

  /* "Sign in to start your session" */
  .login-box .login-box-msg {
    color: rgba(255, 255, 255, 0.75) !important;
  }

  /* ===== INPUT FIELDS ===== */
  .login-box .form-control {
    background: rgba(255, 255, 255, 0.12) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    border-radius: 8px 0 0 8px !important;
    transition: border-color 0.3s, box-shadow 0.3s;
  }

  .login-box .form-control::placeholder {
    color: rgba(255, 255, 255, 0.45) !important;
  }

  .login-box .form-control:focus {
    background: rgba(255, 255, 255, 0.18) !important;
    border-color: rgba(91, 218, 125, 0.6) !important;
    box-shadow: 0 0 0 0.15rem rgba(40, 167, 69, 0.25) !important;
    color: #ffffff !important;
  }

  /* Input group append (icon containers) */
  .login-box .input-group-text {
    background: rgba(255, 255, 255, 0.10) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    border-left: none !important;
    color: rgba(255, 255, 255, 0.7) !important;
    border-radius: 0 8px 8px 0 !important;
  }

  .login-box .input-group-text .fas,
  .login-box .input-group-text .far {
    color: rgba(255, 255, 255, 0.7) !important;
  }

  /* ===== CHECKBOX "Remember Me" ===== */
  .login-box .icheck-primary label,
  .login-box label {
    color: rgba(255, 255, 255, 0.85) !important;
  }

  /* ===== SIGN IN BUTTON ===== */
  .login-box .btn-success {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    border-radius: 8px !important;
    font-weight: 600;
    letter-spacing: 0.3px;
    padding: 0.5rem 1rem !important;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.4) !important;
    transition: all 0.3s ease;
  }

  .login-box .btn-success:hover {
    background-color: #218838 !important;
    box-shadow: 0 0 25px rgba(40, 167, 69, 0.6) !important;
    transform: translateY(-1px);
  }

  /* ===== ALERTS ===== */
  .login-box .alert-danger {
    background: rgba(220, 53, 69, 0.25) !important;
    border-color: rgba(220, 53, 69, 0.4) !important;
    color: #ff8a98 !important;
    border-radius: 8px !important;
  }

  .login-box .text-danger {
    color: #ff8a98 !important;
  }

  .login-box .alert-login {
    font-size: 0.9rem;
    padding: 0.55rem 0.9rem;
    animation: shake 0.35s ease;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
  }

  .login-box .form-control.is-invalid {
    border-color: rgba(220, 53, 69, 0.7) !important;
    background-image: none !important;
  }

  /* ===== SHOW/HIDE PASSWORD CURSOR ===== */
  .login-box .show-password {
    cursor: pointer;
  }

  /* ===== RESPONSIVE ===== */
  @media (max-width: 576px) {
    .login-box {
      width: 100%;
      padding: 0 12px;
    }
  }

  /* ===== DOMAIN ANNOUNCEMENT MODAL ===== */
  #domainAnnouncementModal .modal-content {
    background: rgba(18, 26, 36, 0.94) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
    border: 1px solid rgba(91, 218, 125, 0.45) !important;
    border-radius: 18px !important;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.7) !important;
    color: #ffffff;
    overflow: hidden;
  }

  #domainAnnouncementModal .modal-header {
    background: rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
    padding: 1.15rem 1.5rem !important;
  }

  #domainAnnouncementModal .modal-title {
    color: #ffffff;
    font-weight: 700;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    letter-spacing: 0.2px;
  }

  #domainAnnouncementModal .close {
    color: rgba(255, 255, 255, 0.7);
    text-shadow: none;
    opacity: 0.8;
    transition: all 0.2s ease;
    font-size: 1.5rem;
    line-height: 1;
    padding: 0.5rem;
  }

  #domainAnnouncementModal .close:hover {
    color: #ffffff;
    opacity: 1;
    transform: scale(1.15);
  }

  .domain-box {
    background: rgba(40, 167, 69, 0.12);
    border: 1.5px dashed rgba(91, 218, 125, 0.55);
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    margin: 1.1rem 0;
    text-align: center;
    position: relative;
    box-shadow: inset 0 0 20px rgba(40, 167, 69, 0.08);
  }

  .domain-box .domain-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255, 255, 255, 0.65);
    font-weight: 600;
    margin-bottom: 0.35rem;
  }

  .domain-box .domain-url {
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: clamp(1rem, 3.8vw, 1.25rem);
    font-weight: 700;
    color: #5bda7d;
    letter-spacing: 0.5px;
    word-break: break-all;
    display: block;
    margin-bottom: 0.65rem;
    text-shadow: 0 0 12px rgba(91, 218, 125, 0.35);
  }

  .domain-box .btn-copy-domain {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 6px;
    padding: 0.35rem 0.95rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }

  .domain-box .btn-copy-domain:hover {
    background: rgba(40, 167, 69, 0.4);
    border-color: #5bda7d;
    color: #ffffff;
    transform: translateY(-1px);
  }

  #domainAnnouncementModal .btn-dismiss {
    background: linear-gradient(135deg, #28a745, #218838) !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: 0.3px;
    padding: 0.65rem 1.5rem !important;
    box-shadow: 0 4px 18px rgba(40, 167, 69, 0.45) !important;
    transition: all 0.25s ease;
  }

  #domainAnnouncementModal .btn-dismiss:hover {
    background: linear-gradient(135deg, #2ecc71, #28a745) !important;
    box-shadow: 0 6px 24px rgba(40, 167, 69, 0.65) !important;
    transform: translateY(-1px);
  }
</style>

<body class="hold-transition login-page">

<!-- Video Background -->
<video autoplay muted loop playsinline id="bg-video">
    <source src="{{ asset('images/animasi_login_page.mp4') }}" type="video/mp4">
</video>
<div class="login-box">
  <div class="card card-outline card-success">
    <div class="card-header text-center">
        <img src="{{ asset('images/logoPTPNNew.png') }}" alt="logo PTP" width="100" height="100" >
        <h1 class="tittle mb-2 fs-3 fs-md-2 fw-bold mt-4"><b>Cash<span class="text-success"> Bank</span></b></h1>
    </div>
    <div class="card-body">
        @if(session('failed'))
        <div class="alert alert-danger alert-login mb-3">
          <i class="fas fa-exclamation-circle mr-1"></i> {{ session('failed') }}
        </div>
        @endif
          <p class="login-box-msg">Sign in to start your session</p>

          <form action="/login" method="post">
            @csrf
            <div class="input-group mb-1">
              <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                     placeholder="Username" value="{{ old('username') }}">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-user"></span>
                </div>
              </div>
            </div>
            @error('username')
            <small class="text-danger d-block mb-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
            @enderror
            <div class="input-group mb-1 mt-3">
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                     placeholder="Password" id="password">
              <div class="input-group-append show-password" title="Lihat / sembunyikan password">
                <div class="input-group-text">
                  <span class="fas fa-eye" id="password-eye"></span>
                </div>
              </div>
            </div>
            @error('password')
            <small class="text-danger d-block mb-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
            @enderror
            <div class="row mt-2 mb-2">
              <div class="col-8">
                <div class="icheck-primary">
                  <input type="checkbox" id="remember" name="remember">
                  <label for="remember">
                    Remember Me
                  </label>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-success btn-block">Sign In</button>
          </form>
         

      <!-- <div class="social-auth-links text-center mt-2 mb-3">
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div> -->
      <!-- /.social-auth-links -->

      <!-- <p class="mb-1">
        <a href="forgot-password.html') }}>I forgot my password</a>
      </p>
      <p class="mb-0">
        <a href="register.html') }} class="text-center">Register a new membership</a>
      </p>
    </div> -->
    <!-- /.card-body -->
  </div>
   
  <!-- /.card -->
</div>

<!-- /.login-box -->

<!-- Modal Informasi Domain Baru -->
<div class="modal fade" id="domainAnnouncementModal" tabindex="-1" role="dialog" aria-labelledby="domainAnnouncementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="domainAnnouncementModalLabel">
          <i class="fas fa-globe text-success mr-2"></i> Informasi Domain Resmi
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center px-4 pt-3 pb-2">
        <div class="mb-3">
          <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size: 0.78rem; letter-spacing: 0.5px;">
            <i class="fas fa-bullhorn mr-1"></i> PENGUMUMAN RESMI
          </span>
        </div>
        <p style="font-size: 0.95rem; line-height: 1.5; color: rgba(255,255,255,0.9);">
          Sistem aplikasi <strong>Cash &amp; Bank</strong> kini telah aktif menggunakan domain resmi tersendiri:
        </p>
        <div class="domain-box">
          <div class="domain-label"><i class="fas fa-link mr-1"></i> Alamat Website Baru</div>
          <span class="domain-url" id="domainTargetText">https://cashbankreg5.my.id/</span>
          <button type="button" class="btn btn-copy-domain" id="btnCopyDomain" title="Salin ke papan klip">
            <i class="far fa-copy" id="copyIcon"></i> <span id="copyBtnText">Salin Alamat</span>
          </button>
        </div>
        <p class="mb-2 text-white-50" style="font-size: 0.85rem; line-height: 1.4;">
          <i class="fas fa-bookmark text-warning mr-1"></i> Mohon bookmark / simpan alamat domain <strong>cashbankreg5.my.id</strong> ini pada browser Anda untuk memudahkan akses login selanjutnya.
        </p>
      </div>
      <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-center">
        <button type="button" class="btn btn-dismiss btn-block text-white" data-dismiss="modal">
          <i class="fas fa-check-circle mr-1"></i> Mengerti &amp; Lanjutkan Login
        </button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('adminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminLTE/dist/js/adminlte.min.js') }}"></script>


<script>
    // ===== Popup Pengumuman Domain Baru =====
    $(document).ready(function() {
        $('#domainAnnouncementModal').modal('show');

        $('#domainAnnouncementModal').on('hidden.bs.modal', function () {
            $('input[name="username"]').focus();
        });

        $('#btnCopyDomain').on('click', function() {
            var url = $('#domainTargetText').text().trim();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(showCopied, fallbackCopy);
            } else {
                fallbackCopy();
            }

            function showCopied() {
                $('#copyBtnText').text('Tersalin!');
                $('#copyIcon').attr('class', 'fas fa-check text-success');
                $('#btnCopyDomain').css('border-color', '#5bda7d');
                setTimeout(function() {
                    $('#copyBtnText').text('Salin Alamat');
                    $('#copyIcon').attr('class', 'far fa-copy');
                    $('#btnCopyDomain').css('border-color', '');
                }, 2000);
            }

            function fallbackCopy() {
                var $temp = $('<input>');
                $('body').append($temp);
                $temp.val(url).select();
                try {
                    document.execCommand('copy');
                } catch (e) {}
                $temp.remove();
                showCopied();
            }
        });
    });

    // ===== Ingat username & password (Remember Me) =====
    // Disimpan di localStorage browser saat login dengan Remember Me dicentang,
    // supaya form terisi otomatis lagi setelah logout.
    (function(){
        var $user = $('input[name=username]');
        var savedU = localStorage.getItem('cb_login_u');
        var savedP = localStorage.getItem('cb_login_p');
        if (savedU !== null && $user.val() === '') {
            $user.val(savedU);
        }
        if (savedP !== null) {
            try { $('#password').val(atob(savedP)); } catch (e) {}
        }
        if (savedU !== null || savedP !== null) {
            $('#remember').prop('checked', true);
        }
        $('form').on('submit', function(){
            if ($('#remember').is(':checked')) {
                localStorage.setItem('cb_login_u', $user.val());
                try { localStorage.setItem('cb_login_p', btoa($('#password').val())); } catch (e) {}
            } else {
                localStorage.removeItem('cb_login_u');
                localStorage.removeItem('cb_login_p');
            }
        });
    })();

    $('.show-password').on('click',function(){
        if($('#password').attr('type') == 'password'){
            $('#password').attr('type', 'text');
            $('#password-eye').attr('class', 'fas fa-eye-slash');
        }else{
            $('#password').attr('type', 'password');
            $('#password-eye').attr('class', 'fas fa-eye');
        }
    })
</script>
</body>
</html>