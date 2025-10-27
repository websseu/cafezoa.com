<?php
session_start();

$_SESSION['admin_auth'] = true;
$_SESSION['login_time'] = time(); 
$_SESSION['expire_time'] = 5 * 60 * 60;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $correct = '3162'; 

    if ($password === $correct) {
        $_SESSION['admin_auth'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "비밀번호가 올바르지 않습니다.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 | 010number</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/css/_fonts.css">
    <style>
        body {
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: "NanumSquareNeo", sans-serif;
            margin: 0;
            padding: 0;
        }
        .login-box {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            max-width: 320px;
            text-align: center;
            margin-top: -100px;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #222;
            margin-top: 0;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background: #333; }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>관리자 로그인</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="password" maxlength="4" placeholder="비밀번호" required autofocus>
            <button type="submit">접속</button>
        </form>
    </div>
</body>
</html>
