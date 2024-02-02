@extends('layouts.app')

@section('content')
    <div class="mx-2">
        <h2>Accommodations</h2>
    <ul>
        @foreach($accommodations as $accommodation)
            <li>
                {{ $accommodation->name }}
                <ul>
                    @foreach($contracts->where('accommodation_id', $accommodation->id) as $contract)
                        <li>
                            Contract Rates: {{ $contract->contract_rates }}
                            <form action="{{ route('bookings.store') }}" method="post">
                                @csrf
                                <input type="hidden" name="accommodation_id" value="{{ $accommodation->id }}">
                                <input type="date" name="start_date" required>
                                <input type="date" name="end_date" required>
                                <button type="submit">Book</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
    </div>
@endsection
