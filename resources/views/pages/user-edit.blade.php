@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Your Profile'])
<div id="alert">
    @include('components.alert')
</div>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form role="form" method="POST" action={{ route('users.update', $user->id) }}
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">Edit Profile</p>
                            <button type="submit" class="btn btn-primary btn-sm ms-auto">Save</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-6">
                            <div class="mb-3">
                                @if($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" width="100">
                                @else
                                <img src="/img/team.png" width="100">
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="photo" class="form-control-label">Profile Photo</label>
                                <input class="form-control" type="file" name="photo" accept="image/*">
                            </div>
                        </div>
                        <p class="text-uppercase text-sm">User Information</p>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Email address</label>
                                    <input class="form-control" type="email" name="email"
                                        value="{{ old('email', $user->email) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">First name</label>
                                    <input class="form-control" type="text" name="firstname"
                                        value="{{ old('firstname', $user->firstname) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Last name</label>
                                    <input class="form-control" type="text" name="lastname"
                                        value="{{ old('lastname', $user->lastname) }}">
                                </div>
                            </div>
                        </div>
                        <hr class="horizontal dark">
                        <p class="text-uppercase text-sm">Contact Information</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Address</label>
                                    <input class="form-control" type="text" name="address"
                                        value="{{ old('address', $user->address) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">City</label>
                                    <input class="form-control" type="text" name="city"
                                        value="{{ old('city', $user->city) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Country</label>
                                    <input class="form-control" type="text" name="country"
                                        value="{{ old('country', $user->country) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Postal code</label>
                                    <input class="form-control" type="text" name="postal"
                                        value="{{ old('postal', $user->postal) }}">
                                </div>
                            </div>
                        </div>
                        <hr class="horizontal dark">
                        <p class="text-uppercase text-sm">About me</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">About me</label>
                                    <input class="form-control" type="text" name="about"
                                        value="{{ old('about', $user->about) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('layouts.footers.auth.footer')
</div>
@endsection