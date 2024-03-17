<x-mail::message>
# Charging cost for the Billing Profile {{ $bill->billingProfile->name }} is ready

{{ Str::plural('Vehicle', $bill->billingProfile->vehicles->count()) }}:
<ol>
    @foreach ($bill->billingProfile->vehicles as $vehicle)
        <li>{{ $vehicle->name }} / {{ $vehicle->plate }} / {{ $vehicle->masked_vin }}</li>
    @endforeach
</ol>

Location: {{ $bill->billingProfile->address }}

|  |  |
|:---|---:|
| **From** | {{ $bill->from->format('d-M-Y') }} |
| **To** | {{ $bill->to->format('d-M-Y') }} |
| **Total Energy** | {{ number_format($bill->energy_consumed, 2) }} kWh |
| **Total Cost** | {{ $bill->billingProfile->currency }} {{ number_format($bill->total_cost, 2) }} |

<x-mail::button :url="$url">
View Breakdown
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
