<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
        <h2>Refund Report ({{ $refunds->count() }} Records)</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Card</th>
                <th>Rec#</th>
                <th>Passenger</th>
                <th>Sale</th>
                <th>Cost</th>
                <th>Tax</th>
                <th>Refund to Customer</th>
                <th>Refund to Vendor</th>
                <th>Apply Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($refunds as $index => $refund)
         
                <tr>
                    <td>{{ $refund->approve_date??"N/A" }}</td>
                    <td>{{$refund->card->card_name}}</td>
                    <td>{{$refund->record_no}}</td>
                    <td>{{$refund->name}}</td>
                    <td>{{number_format($refund->sale, 2) }}</td>
                    <td>{{ number_format($refund->cost, 2) }}</td>
                    <td>{{ number_format($refund->tax, 2) }}</td>
                    <td>{{ number_format($refund->ref_to_cus, 2) }}</td>
                    <td>{{ number_format($refund->ref_to_vendor, 2) }}</td>
                    <td>{{ $refund->apply_date }}</td>
                    <td>{{ ucfirst($refund->status ?? 'N/A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
