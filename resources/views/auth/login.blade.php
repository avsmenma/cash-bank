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
        <div class="alert alert-danger">{{  session('failed') }}</div>
        @endif
          <p class="login-box-msg">Sign in to start your session</p>

          <form action="/login" method="post">
            @csrf
            @error('username')
            <small class="text-danger">(( $message ))</small>
            @enderror
            <div class="input-group mb-3">
              <input type="text" name="username" class="form-control" placeholder="Username">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
                @error('password')
                <small class="text-danger">(( $message ))</small>
                @enderror
              <!-- <div class="invalid-feedback">
                Please choose a username
              </div> -->
            </div>
            <div class="input-group mb-3 has-validation">
              <input type="password" name="password" class="form-control" placeholder="Password" id="password">
              <div class="input-group-append show-password">
                <div class="input-group-text">
                  <span class="fas fa-lock" id="password-lock"></span>
                </div>
              </div>
                <!-- <div class="invalid-feedback">
                Please choose a password
              </div> -->
            </div>
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

<!-- jQuery -->
<script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('adminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminLTE/dist/js/adminlte.min.js') }}"></script>


<script>
    $('.show-password').on('click',function(){
        if($('#password').attr('type') == 'password'){
            $('#password').attr('type', 'text');
            $('#password-lock').attr('class', 'fas fa-unlock');
        }else{
             $('#password').attr('type', 'password');
            $('#password-lock').attr('class', 'fas fa-lock');
        }
    })
</script>
</body>
</html>