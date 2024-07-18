<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
</head>
<body>
<form action="/login" method="post">
    @csrf
    <input type="text" name="email" placeholder="邮箱" value=""/>
    <input type="password" name="password" placeholder="密码" value="123456"/>
    <button>登录</button>
</form>
</body>
</html>
