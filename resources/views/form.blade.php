@extends('welcome')
@section('content')
<form action="/add-to-google-sheet" method="post">
    @csrf
    <label for="name">Name:</label>
    <input type="text" name="name" required><br>
    <label for="phone">Phone:</label>
    <input type="text" name="phone" required><br>
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <button type="submit">Add</button>
</form>
@endsection