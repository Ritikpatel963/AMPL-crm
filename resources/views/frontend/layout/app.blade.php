<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="{{ route('shop.index') }}">MyShop</a>
  </div>
</nav>

@yield('content')

</body>
</html>
