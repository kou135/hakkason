<?
require '../dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $category = $_POST['category'] ?? '';
  $event_date = $_POST['event_date'] ?? '';
  $title = $_POST['title'] ?? '';
  $score = $_POST['score'] ?? '';
  $reason = $_POST['reason'] ?? '';
  $real_action = $_POST['real_action'] ?? '';
  $study = $_POST['study'] ?? '';
  $next_action = $_POST['next_action'] ?? '';

  // ユーザーIDをセッションから取得（ログインユーザーが投稿する前提）
  $user_id = $_SESSION['user_id'] ?? 1;  // セッションから取得するか、デフォルトで1を使用

  try {
    $stmt = $dbh->prepare('INSERT INTO feedback (user_id, title, score, reason, real_action, study, next_action, event_date, created_at, updated_at) 
                                VALUES (:user_id, :title, :score, :reason, :real_action, :study, :next_action, :event_date, NOW(), NOW())');
    $stmt->execute([
      ':user_id' => $user_id,
      ':title' => $title,
      ':score' => $score,
      ':reason' => $reason,
      ':real_action' => $real_action,
      ':study' => $study,
      ':next_action' => $next_action,
      ':event_date' => $event_date
    ]);

    $feedback_id = $dbh->lastInsertId();

    $stmt = $dbh->prepare('INSERT INTO event_category (feedback_id, category) VALUES (:feedback_id, :category)');
    $stmt->execute([
      ':feedback_id' => $feedback_id,
      ':category' => $category
    ]);

    // 成功時の処理（例: サンクスページへリダイレクト）
    header('Location: ./post.php');
    exit;
  } catch (PDOException $e) {
    //echo 'データベースエラー: ' . $e->getMessage();
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body >
  <div class="flex">
  <!-- サイドバー -->
<section class="bg-gray-800 w-1/5 h-screen p-4 sticky top-0">
    <div class="mb-10 flex flex-col items-center">
        <img src="../../assets/img/No greed.png" alt="Logo" class="w-3/4 rounded-full mt-2">
        <nav class="mt-20 w-full">
            <ul class="space-y-4">
                <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
                    <a href="../userpage/home/home.php" class="flex items-center text-lg">
                        <span class="text-3xl text-white">ホーム</span>
                    </a>
                </li>
                <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
                    <a href="./post.php" class="flex items-center text-lg hover:bg-gray-100">
                        <span class="text-3xl text-white">投稿</span>
                    </a>
                </li>
                <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
                    <a href="../userpage/mypage/mypage.php" class="flex items-center text-lg hover:bg-gray-100">
                        <span class="text-3xl text-white">マイページ</span>
                    </a>
                </li>
                <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
                    <a href="../userpage/mypage/friend.php" class="flex items-center text-lg hover:bg-gray-100">
                        <span class="text-3xl text-white">友達</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</section>

    <section class="w-4/5 p-8 bg-black text-white">
    <div class="mb-8">
        <p class="text-4xl font-bold">〜振り返り〜</p>
    </div>
    <form action="./post.php" method="POST" class="space-y-6">
        <!-- ジャンルと日付 -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xl font-semibold">ジャンル</label>
                <select name="category" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white">
                    <option value=""></option>
                    <option value="1">日記</option>
                    <option value="2">サバリ</option>
                    <option value="3">ハッカソン</option>
                    <option value="4">MU</option>
                    <option value="5">イベント</option>
                </select>
            </div>
            <div>
                <label class="block text-xl font-semibold">日付</label>
                <input type="text" name="event_date" placeholder="20XX-MM-DD" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white">
            </div>
        </div>

        <!-- タイトル -->
        <div>
            <label class="block text-xl font-semibold">タイトル</label>
            <input type="text" name="title" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white">
        </div>

        <!-- 点数 -->
        <div>
            <label class="block text-xl font-semibold">点数</label>
            <select name="score" class="w-16 p-2 border border-gray-300 rounded bg-gray-900 text-white">
                <option value=""></option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>

        <!-- 点数の理由 -->
        <div>
            <label class="block text-xl font-semibold">⚫ 点数の理由</label>
            <textarea name="reason" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white h-24"></textarea>
        </div>

        <!-- 具体的にどんなことを行いましたか？ -->
        <div>
            <label class="block text-xl font-semibold">⚫ 具体的にどんなことを行いましたか？<br>（GOOD・MOREや時系列順など）</label>
            <textarea name="real_action" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white h-24"></textarea>
        </div>

        <!-- その出来事から学んだことは？ -->
        <div>
            <label class="block text-xl font-semibold">⚫ その出来事から学んだことは？<br>（考えたこと・人から言われたこと・感情など）</label>
            <textarea name="study" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white h-24"></textarea>
        </div>

        <!-- 今回の学びは今後どのように活かせるでしょうか？ -->
        <div>
            <label class="block text-xl font-semibold">⚫ 今回の学びは今後どのように活かせるでしょうか？</label>
            <textarea name="next_action" class="w-full p-2 border border-gray-300 rounded bg-gray-900 text-white h-24"></textarea>
        </div>

        <!-- 投稿ボタン -->
        <div class="text-center">
            <button id="postButton" type="submit" class="px-6 py-3 bg-red-500 text-white font-bold rounded hover:bg-red-600 transition duration-300">
                投稿
            </button>
        </div>
    </form>
</section>

    </div>
</body>
<script>
                document.getElementById("postButton").addEventListener("click", function() {
                    alert("投稿が完了しました");
                });
            </script>
</html>