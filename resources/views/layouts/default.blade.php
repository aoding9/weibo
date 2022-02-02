<!DOCTYPE html>
<html>

<head>
  <title>@yield('title', 'Weibo App') - Laravel 新手入门教程</title>
  {{-- <link rel="stylesheet" href="/css/app.css?v={{rand()}}"> --}}
  {{-- <script src="/js/app.js"></script> --}}
  {{-- <link rel="stylesheet" href="{{ mix('/css/app.css') }}"> --}}
  <script src="{{ mix('/js/app.js') }}"></script>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a href="/" class="navbar-brand">weibo app</a>
      <ul class="navbar-nav justify-content-end">
        <li class="nav-item">
          <a class="nav-link" href="/help">帮助</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/login">登录</a>
        </li>
      </ul>
    </div>
  </nav>



  <div class="container">
    @yield('content')
  </div>
</body>

</html>
