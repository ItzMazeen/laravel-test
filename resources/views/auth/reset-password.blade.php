@extends('layouts.app')

@section('content')
<div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12">
            @include('layouts.navbars.guest.navbar')
        </div>
    </div>
</div>
<main class="main-content  mt-0">
    <section>
        <div class="page-header min-vh-100">
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                        <div class="card card-plain">
                            <div class="card-header pb-0 text-start">
                                <h4 class="font-weight-bolder">Reset your password</h4>
                                <p class="mb-0">Enter your email and please wait a few seconds</p>
                            </div>
                            <div class="card-body">
                                <form role="form" method="POST" action="{{ route('reset.perform') }}">
                                    @csrf
                                    @method('post')
                                    <div class="flex flex-col mb-3">
                                        <input type="email" name="email" class="form-control form-control-lg"
                                            placeholder="Email" value="{{ old('email') }}" aria-label="Email">
                                        @error('email') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Send
                                            Reset Link</button>
                                    </div>
                                </form>
                            </div>
                            <div id="alert">
                                @include('components.alert')
                            </div>
                        </div>
                    </div>
                    <div
                        class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                        <div id="carouselExampleIndicators" class="carousel slide w-100" style="height: 600px;"
                            data-bs-ride="carousel" data-bs-interval="2500">
                            <!-- Indicators -->
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                    class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                    aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                    aria-label="Slide 3"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3"
                                    aria-label="Slide 4"></button>
                            </div>
                            <!-- Slides -->
                            <div class="carousel-inner h-100">

                                <!-- Slide 1 -->
                                <div class="carousel-item active h-100">
                                    <div class="position-relative bg-gradient-primary h-100 m-2 px-5 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                        style="background-image: url('/img/ooredoo.jpg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
                                    </div>
                                </div>

                                <!-- Slide 2 -->
                                <div class="carousel-item h-100">
                                    <div class="position-relative bg-gradient-primary h-100 m-2 px-5 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                        style="background-image: url('/img/Exscape.jpg'); background-size: contain; background-position: center; background-repeat: no-repeat;">

                                    </div>
                                </div>

                                <!-- Slide 3 -->
                                <div class="carousel-item h-100">
                                    <div class="position-relative bg-gradient-primary h-100 m-2 px-5 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                        style="background-image: url('/img/telecom.webp'); background-size: contain; background-position: center; background-repeat: no-repeat;">

                                    </div>
                                </div>

                                <!-- Slide 4 -->
                                <div class="carousel-item h-100">
                                    <div class="position-relative bg-gradient-primary h-100 m-2 px-5 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                        style="background-image: url('/img/orange.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection