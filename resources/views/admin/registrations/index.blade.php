@extends('admin.layouts.app')

@section('title', 'Registrations')

@section('content')
<div class="card">
    <div class="card-head">
        <h2>All registrations</h2>
        <span class="t2">{{ $registrations->total() }} total</span>
    </div>

    @if ($registrations->isEmpty())
        <div class="empty">No registrations yet.</div>
    @else
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Ticket</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Transaction</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registrations as $reg)
                    <tr>
                        <td class="mono">{{ $reg->id }}</td>
                        <td class="t2" style="white-space:nowrap">
                            {{ $reg->created_at->format('M j, Y') }}<br>
                            <span style="font-size:11.5px">{{ $reg->created_at->format('g:i A') }}</span>
                        </td>
                        <td>
                            @if ($reg->first_name || $reg->last_name)
                                {{ trim($reg->first_name . ' ' . $reg->last_name) }}
                            @else
                                <span class="t2">—</span>
                            @endif
                        </td>
                        <td>{{ $reg->email }}</td>
                        <td>
                            <span style="font-weight:600">{{ $reg->event_title ?? $reg->event_slug }}</span>
                        </td>
                        <td class="t2">{{ $reg->ticket_name ?? '—' }}</td>
                        <td style="white-space:nowrap">
                            @if ($reg->amount !== null)
                                @if ($reg->amount == 0)
                                    <span class="t2">Free</span>
                                @else
                                    {{ $reg->currency }} {{ number_format($reg->amount, 2) }}
                                @endif
                            @else
                                <span class="t2">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $reg->status }}">{{ ucfirst($reg->status) }}</span>
                        </td>
                        <td class="mono">{{ $reg->transaction_id ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            <div class="pagination">
                {{-- Previous --}}
                @if ($registrations->onFirstPage())
                    <span>‹</span>
                @else
                    <a href="{{ $registrations->previousPageUrl() }}">‹</a>
                @endif

                {{-- Page numbers --}}
                @foreach ($registrations->getUrlRange(max(1, $registrations->currentPage() - 2), min($registrations->lastPage(), $registrations->currentPage() + 2)) as $page => $url)
                    @if ($page == $registrations->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($registrations->hasMorePages())
                    <a href="{{ $registrations->nextPageUrl() }}">›</a>
                @else
                    <span>›</span>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
