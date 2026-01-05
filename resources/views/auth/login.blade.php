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
</head>
<body class="hold-transition login-page" style="background-image: url('{{ asset('images/Logo PTPN IV.png') }}')">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
        <img src="{{ asset('images/logoPTPNNew.png') }}" alt="logo PTP" width="100" height="100" >
        <!-- <h1 class="tittle"><b>CASH-<span> BANK</span></b></h1> -->
         <h1 class="tittle mb-2 fs-3 fs-md-2 fw-bold"><b>Cash<span style="color: #FF7518"> Bank</span></b></h1>
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
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
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