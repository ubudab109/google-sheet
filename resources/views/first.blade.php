@extends('welcome')
@section('content')
    <div class="row justify-content-center p-5">
        <div class="col-12 text-center">
            <div class="card">
                <div class="card-body">
                    <a href="{{ $authUrl }}" class="btn-red-bg"> <span style="color: white;"> <img src="{{asset('icon/user-plus.svg')}}" /> </span> Connect Google Account</a>
                </div>
            </div>
        </div>
    </div>
@endsection
