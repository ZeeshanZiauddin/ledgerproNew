<!-- resources/views/pdf/payment.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <title>Payment Details</title>
</head>

<body>
    <h1>Payment Details</h1>
    <p>Supplier: {{ $payment->supplier->name }}</p>
    <p>Cheque No: {{ $payment->cheque_no }}</p>
    <p>Bank: {{ $payment->bank->name }}</p>
    <p>Total: {{ $payment->total }}</p>
    <p>Details: {{ $payment->details }}</p>
    <!-- Add more details as needed -->
</body>

</html>
