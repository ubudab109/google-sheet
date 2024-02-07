@extends('welcome')
@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @error('error')
                <p style="color: red;">{{$message}}</p>
                @enderror
                <p>Your Spreadsheet: <a href="https://docs.google.com/spreadsheets/d/{{$spreadsheet}}/edit" target="_blank" rel="noreferrer">Here</a></p>

                <form action="/add-to-google-sheet" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="text-gray">Name</label>
                        <input type="name" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="John Doe">
                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone" class="text-bold">Phone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone" placeholder="+62 9493">
                        @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="email" class="text-bold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="example@mail.com">
                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <button type="submit" class="btn-blue">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection