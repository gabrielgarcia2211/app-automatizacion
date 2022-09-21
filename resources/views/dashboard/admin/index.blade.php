@extends('layouts.admin')
@section('content')
    @include('dashboard.admin.nav.nav')

    <div class="container-fluid mt--6">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted ls-1 mb-1">Gestion</h6>
                        <h5 class="h3 mb-0">Control de sitios</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <sites-component></sites-component>
            </div>
        </div>
    </div>

    @include('dashboard.admin.footer.footer')
@endsection
@section('script')
    <script src="{{ asset('js/admin/script.js') }}" type="application/javascript"></script>
@endsection
