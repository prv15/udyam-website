<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial,sans-serif;
        }

        body{
            background:#f5f7fb;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .login-box{
            width:400px;
            background:#fff;
            padding:35px;
            border-radius:12px;
            box-shadow:0 15px 40px rgba(0,0,0,.08);
        }

        h2{
            margin-bottom:25px;
            text-align:center;
        }

        input{
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border:1px solid #ddd;
            border-radius:8px;
        }

        button{
            width:100%;
            padding:12px;
            background:#4f46e5;
            color:#fff;
            border:none;
            border-radius:8px;
            cursor:pointer;
        }

        button:hover{
            background:#4338ca;
        }

        .error{
            color:red;
            margin-bottom:15px;
        }
    </style>

</head>

<body>

<div class="login-box">

<h2>Admin Login</h2>

<?php if(!empty($error)): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(url('/admin/login')) ?>">

<?= csrf_field() ?>

<input
type="email"
name="email"
placeholder="Email"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit">
Login
</button>

</form>

</div>

</body>

</html>
