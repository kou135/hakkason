<?
require '../../dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $email = $_POST['email'];
  $password = $_POST['password'];

  try {
    $stmt = $dbh->prepare('SELECT * FROM user WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !(password_verify($password, $user['password']))) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['email'] = $user['email'];

      // ログイン成功後にリダイレクトするがまだ決めていない
      header('Location: ../../userpage/mypage/mypage.php');
      exit;
    } else {
      echo 'メールアドレスまたはパスワードが正しくありません。';
    }
  } catch (PDOException $e) {
    echo 'データベース接続に失敗しました: ' . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POSSEの足跡</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slideDown {
      from {
        transform: translateY(-100%);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .animate-slide-down {
      animation: slideDown 2s ease-out;
    }
  </style>
</head>

<body class="bg-black relative h-screen">
  <!-- タイトル -->
  <h1 id="title" class="text-center text-5xl text-white bg-black bg-opacity-75 py-4 absolute inset-x-0 top-0 hidden">POSSEの足跡</h1>

  <!-- 背景画像 -->
  <div class="flex justify-center items-center h-full w-full">
    <img src="../../assets/img/image 22.png" alt="狼" class="object-cover w-full h-full opacity-50">
  </div>

  <!-- ログインフォーム -->
  <section class="absolute inset-0 flex flex-col justify-center items-center">
    <form action="./login.php" method="POST" class="bg-gray-900 bg-opacity-75 p-8 rounded-lg">
      <div class="flex flex-col space-y-6">
        <input class="w-96 h-12 p-4 bg-gray-200 text-gray-700 placeholder-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" name="email" placeholder="メールアドレス">
        <input class="w-96 h-12 p-4 bg-gray-200 text-gray-700 placeholder-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" type="password" name="password" placeholder="パスワード">
        <button class="w-32 h-12 bg-red-600 text-white font-bold rounded-full hover:bg-red-700 transition-colors duration-300 mx-auto" type="submit">ログイン</button>
      </div>
    </form>
    <div class="mt-6 text-lg">
      <a href="../register/register.php" class="text-white bg-blue-900 border-2 border-blue-900 rounded-full px-6 py-2 inline-block hover:bg-blue-800 hover:border-blue-800 transition-colors duration-300">新規登録の方はコチラ</a>
    </div>
  </section>

  <script>
    window.onload = function () {
      const title = document.getElementById('title');
      title.classList.remove('hidden');
      title.classList.add('animate-slide-down');
    };
  </script>
</body>

</html>
