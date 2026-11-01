<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Magnolia Management · Sign in</title>
<style>body{font:16px system-ui;background:#f5f1ea;color:#352a24;display:grid;place-items:center;min-height:100vh;margin:0}main{background:#fff;padding:36px;border-radius:18px;width:min(380px,90vw);box-shadow:0 16px 40px #352a2415}h1{font-family:Georgia,serif;font-size:28px}label{display:block;margin:16px 0 5px}input,button{box-sizing:border-box;width:100%;padding:12px;border-radius:9px;border:1px solid #ccc}button{margin-top:22px;background:#684638;color:#fff;border:0;cursor:pointer}.error{color:#a22}</style></head>
<body><main><h1>Magnolia Management</h1><p>Authorized hotel staff only.</p>
@if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
<form method="post" action="{{ route('admin.login.submit') }}">@csrf
<label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required>
<label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="submit">Sign in</button></form></main></body></html>