@extends('admin.layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="login-wrap">
    <div class="login-box">
        <div class="brand">
            <span>AMCOB Events</span>
            <small>Admin Panel</small>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Sign in</button>
        </form>
    </div>
</div>
@endsection
