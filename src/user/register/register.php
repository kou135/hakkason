<?
require '../../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = $_POST['name'];
  $email = $_POST['email'];
  $gender = $_POST['gender'];
  $category = $_POST['category'];
  $password = $_POST['password'];

  // 画像アップロード処理
  $photoPath = null; // デフォルト値

  if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photoTmpPath = $_FILES['photo']['tmp_name'];
    $photoName = $_FILES['photo']['name'];
    $photoSize = $_FILES['photo']['size'];
    $photoType = $_FILES['photo']['type'];

    $uploadDir = '../../assets/img/'; // 画像を保存するディレクトリ
    $photoPath = $uploadDir . basename($photoName);

    // 画像の保存
    if (move_uploaded_file($photoTmpPath, $photoPath)) {
      // ファイルが正常にアップロードされた
    } else {
      echo "画像のアップロードに失敗しました。";
      exit;
    }
  }

  // 4桁のランダムな数字を生成
  $friendCord = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

  try {
    $stmt = $dbh->prepare('INSERT INTO user (name, email, gender, category, password, photo_path, friend_cord) VALUES (:name, :email, :gender, :category, :password, :photo_path, :friend_cord)');
    $stmt->execute([
      ':name' => $name,
      ':email' => $email,
      ':gender' => $gender,
      ':category' => $category,
      ':password' => $password,
      ':photo_path' => $photoPath, // 画像パスをデータベースに保存
      ':friend_cord' => $friendCord
    ]);

    //ここどこにリダイレクトするかまだ決めていないが仮で
    header('Location: ../login/login.php');
    exit;
  } catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="./style.css">
</head>

<body>
  <section class="register">
    <div>
      <img class="ookami_logo" src="../../assets/img/image 12.png" alt="狼ロゴ">
    </div>
    <div class="back_title">
      <h1 class="new_title">新規登録</h1>
    </div>
    <form action="./register.php" method="POST" enctype="multipart/form-data">
      <div class="p_new">
        <div class="new_register">
          <input class="set_register" type="text" name="name" placeholder="名前" required>
          <input class="set_register" type="text" name="email" placeholder="メールアドレス" required>
          <select class="option_register" name="gender" required>
            <option value="" disabled selected>性別を選択</option>
            <option value="1">男性</option>
            <option value="2">女性</option>
          </select>
          <select class="option_register" name="category">
            <option value="" disabled selected>所属POSSE選択</option>
            <option value="1">①</option>
            <option value="2">②</option>
            <option value="3">③</option>
            <input class="set_register" type="text" name="password" placeholder="パスワード" required>
          </select>
          <div class="profile">
            <div class="content">プロフィール写真（任意)</div>
            <div>
              <input type="file" name="photo" accept="image/*">
            </div>
          </div>
        </div>
        <div>
          <div>
            <img class="ookami_picture" src="../../assets/img/image 13.png" alt="狼遠吠え">
          </div>
        </div>
      </div>
      <div class="p_com">
        <button class="com_register" type="submit">登録</button>
      </div>
    </form>
  </section>
</body>

</html>