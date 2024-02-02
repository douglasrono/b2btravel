@extends('layouts.app')

@section('content')
<div class="mx-3">
    <div class="row">
        <div class="col-md-12">
            <h1>Dashboard</h1>
            <p>Welcome to your dashboard, {{ auth()->user()->name }}!</p>


            <!-- Dummy content for key metrics -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Bookings</h5>
                            <p class="card-text">37</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Revenue</h5>
                            <p class="card-text">$12,500</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pending Approvals</h5>
                            <p class="card-text">5</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dummy content for recent bookings -->
            <div class="mt-4">
                <h3>Recent Bookings</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Accommodation</th>
                            <th scope="col">Check-in</th>
                            <th scope="col">Check-out</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">1</th>

                            <td>Hotel ABC</td>
                            <td>2024-02-10</td>
                            <td>2024-02-15</td>
                            <td><span class="badge badge-success">Confirmed</span></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

