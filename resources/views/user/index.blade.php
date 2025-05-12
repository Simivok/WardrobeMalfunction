@extends('layouts.app')
@section('content')



<style>
.user-dashboard {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-item {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
    padding-right: 5px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    padding: 15px;
}

.product-item .image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    gap: 10px;
    padding: 5px;
    border-radius: 10px;
    background: #f4f6f8;
}

.divider {
    height: 1px;
    background: #ddd;
    margin: 0 10px;
}

</style>
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
      <h2 class="page-title">My Account</h2>
      <div class="row">
        <div class="col-lg-3">
          @include('user.account-nav')
        </div>
        <div class="col-lg-9">
          <div class="page-content my-account__dashboard">
            <p>Hello <strong>{{ Auth::user()->name }}</strong></p>
            <ul class="user-dashboard gap14">
    <!-- Email Card -->
            <li class="product-item gap14 mb-10">
                <div class="image no-bg">
                    <i class="fas fa-at" style="font-size: 24px;"></i>
                </div>
                <div class="flex items-center justify-between gap20 flex-grow">
                    <div class="name">
                        <div class="body-text">Email</div>
                        <div class="body-title-2">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </li>

            <li class="mb-10">
                <div class="divider"></div>
            </li>

            <!-- Orders Card -->
            <li class="product-item gap14 mb-10">
                <div class="image no-bg">
                    <i class="fas fa-credit-card" style="font-size: 24px;"></i>
                </div>
                <div class="flex items-center justify-between gap20 flex-grow">
                    <div class="name">
                        <div class="body-text">Total Orders</div>
                        <div class="body-title-2">{{ $ordersCount ?? 0 }}</div>
                    </div>
                </div>
            </li>
            <!-- Orders Card -->
            <li class="mb-10">
                <div class="divider"></div>
            </li>
            <li class="product-item gap14 mb-10">
                <div class="image no-bg">
                    <i class="fa fa-envelope" style="font-size: 24px;"></i>
                </div>
                <div class="flex items-center justify-between gap20 flex-grow">
                    <div class="name">
                        <div class="body-text">Change Email</div>
                        <a href="{{route('email.edit')}}">Change it <strong>Here</strong></a>
                    </div>
                </div>
            </li>
            <li class="mb-10">
                <div class="divider"></div>
            </li>
            <li class="product-item gap14 mb-10">
                <div class="image no-bg">
                    <i class="fas fa-fingerprint" style="font-size: 24px;"></i>
                </div>
                <div class="flex items-center justify-between gap20 flex-grow">
                    <div class="name">
                        <div class="body-text">Change Password</div>
                        <a href="{{route('password.edit')}}">Change it <strong>Here</strong></a>
                    </div>
                </div>
            </li>

            <li class="mb-10">
                <div class="divider"></div>
            </li>
        </ul>

          </div>
        </div>
      </div>
    </section>
  </main>
@endsection