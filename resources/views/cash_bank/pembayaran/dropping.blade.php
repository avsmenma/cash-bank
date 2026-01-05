@extends('layouts/index')
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <p>sendal</p>
      </div>
    </div>
  </div>
  <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Daftar Permintaan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">CashFlow</a></li>
                    <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <div class="active tab-pane" id="activity">
                      @include('cash_bank.pembayaran.dataDropping')
                      
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="timeline">

                          <p>huhuhuh</p>
                    </div>
                    <div class="tab-pane" id="settings">
                      <div class="timeline timeline-inverse">
                          <p>huhuhuh</p>
                      </div>
                    </div>
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          </div>
@endsection