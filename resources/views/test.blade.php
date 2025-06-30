<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="{{ route('pay') }}">
        @csrf
        <input
            name="payment"
            value="online"
        >
        <input
            name="factor_id"
            value="factor_id"
        >
        <input
            name="gateway"
            value="saman"
        >
        <button type="submit">test</button>
    </form>
</body>
</html>
