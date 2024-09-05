<?php
require '../../dbconnect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 1;

// 過去の投稿からランダムに1件を取得
$stmt = $dbh->prepare("
    SELECT
        feedback.*,
        (SELECT COUNT(*) FROM comments WHERE comments.feedback_id = feedback.id) AS comment_count,
        event_category.*
    FROM
        feedback
    LEFT JOIN
        event_category ON feedback.id = event_category.feedback_id
    WHERE
        feedback.user_id = :user_id
    ORDER BY RAND()
    LIMIT 1
");
$stmt->execute([':user_id' => $user_id]);
$random_feedback = $stmt->fetch(PDO::FETCH_ASSOC);

// フィードバックごとのコメントを取得
$comments = [];
if ($random_feedback) {
  $commentStmt = $dbh->prepare('
        SELECT comments.*, user.name AS commenter_name, user.category AS commenter_category, user.photo_path AS commenter_path
        FROM comments
        LEFT JOIN user ON comments.post_user_id = user.id
        WHERE feedback_id = :feedback_id
    ');
  $commentStmt->execute([':feedback_id' => $random_feedback['id']]);
  $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
}

// ランダムメッセージの選択
$messages = [
  "まだまだそんなんでいいのか？！",
  "キミの限界はここまでか、、、、？",
  "振り返りは次に生かすのが重要だ"
];
$random_message = $messages[array_rand($messages)];

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POSSE 初めてのWeb制作</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .hidden {
      visibility: hidden;
    }

    .fade-in {
      animation: fadeIn 2s ease-in-out forwards;
    }

    @keyframes fadeIn {
      0% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }
  </style>
</head>

<body class="bg-black text-white">
  <div class="flex">
    <!-- サイドバー -->
    <section class="bg-gray-800 w-1/5 h-screen p-4 sticky top-0">
      <div class="mb-10 flex flex-col items-center">
        <img src="../../assets/img/No greed.png" alt="Logo" class="w-3/4 rounded-full mt-2">
        <nav class="mt-20 w-full">
          <ul class="space-y-4">
            <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
              <a href="./home.php" class="flex items-center text-lg">
                <span class="text-3xl">ホーム</span>
              </a>
            </li>
            <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
              <a href="../../post/post.php" class="flex items-center text-lg hover:bg-gray-100">
                <span class="text-3xl">投稿</span>
              </a>
            </li>
            <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
              <a href="../mypage/mypage.php" class="flex items-center text-lg hover:bg-gray-100">
                <span class="text-3xl">マイページ</span>
              </a>
            </li>
            <li class="mb-4 h-16 hover:bg-gray-100 flex items-center justify-center">
              <a href="../mypage/friend.php" class="flex items-center text-lg hover:bg-gray-100">
                <span class="text-3xl">友達</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </section>


    <!-- メインコンテンツ -->
    <section class="w-4/5 p-4">
      <!-- 上部イメージとタイトル -->
      <div class="relative">
        <img src="../../assets/img/image 19.png" alt="mainimg" class="w-full h-96 object-cover opacity-50">
        <div class="absolute top-0 left-0 w-full h-full flex flex-col justify-center items-center">
          <h1 id="title1" class="text-red-500 text-8xl font-bold hidden"></h1>
          <h1 id="title2" class="text-red-500 text-8xl font-bold hidden"></h1>
        </div>
      </div>

      <!-- 過去のキミ -->
      <div class="mt-12 p-8 bg-black">
        <h2 class="text-white text-4xl mb-8">〜過去のキミ〜</h2>
        <div class="flex justify-center gap-20 mr-20">
          <div>
            <?php if ($random_feedback): ?>
              <div class="category-item border border-white p-4 rounded-lg shadow  mb-6 px-4 w-[480px]">
                <div class="flex justify-between items-center mb-4">
                  <div class="text-3xl font-semibold text-white"><?php echo htmlspecialchars($random_feedback['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="text-gray-400 text-xl"><?php echo htmlspecialchars($random_feedback['event_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="mb-4 text-xl text-white">○今後の目標</div>
                <div class="text-gray-300 text-xl mb-4"><?php echo htmlspecialchars($random_feedback['next_action'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php if ($random_feedback['comment_count'] > 0): ?>
                  <div class="cursor-pointer text-blue-500 text-xl mb-4" onclick="toggleComments(<?php echo $random_feedback['id']; ?>)">
                    コメント<?php echo $random_feedback['comment_count']; ?>件を見る
                  </div>
                <?php else: ?>
                  <div class="text-gray-500">コメントはありません</div>
                <?php endif; ?>

                <!-- コメント一覧とフォーム -->
                <div id="comments-<?php echo $random_feedback['id']; ?>" class="hidden mt-4">
                  <div class="space-y-4">
                    <?php foreach ($comments as $comment): ?>
                      <div class="border-b border-gray-700 pb-2">
                        <div class="flex justify-between">
                          <div class="text-xl text-gray-400 flex items-center">
                            <div class="mr-2"><img src="../../assets/img/<?php echo htmlspecialchars($comment['commenter_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="User Icon" class="w-12 h-12 rounded-full"></div>
                            <div class="mr-1">
                              <?php
                              $symbol = '';
                              if ($comment['commenter_category'] == 1) {
                                $symbol = '①';
                              } elseif ($comment['commenter_category'] == 2) {
                                $symbol = '②';
                              } elseif ($comment['commenter_category'] == 3) {
                                $symbol = '③';
                              }
                              echo htmlspecialchars($symbol, ENT_QUOTES, 'UTF-8') . ' ';
                              ?>
                            </div>
                            <div><?php echo htmlspecialchars($comment['commenter_name'], ENT_QUOTES, 'UTF-8'); ?></div>:
                          </div>
                          <div><?php echo htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="text-gray-200"><?php echo htmlspecialchars($comment['comments'], ENT_QUOTES, 'UTF-8'); ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="text-white text-xl">過去の投稿はありません。</div>
            <?php endif; ?>
          </div>
          <div>
            <div class="relative">
              <div class="absolute inset-0 flex items-center justify-center text-5xl font-bold text-red-500 align-center fade-in" id="random-message"><?php echo $random_message; ?></div>
              <div><img src="../../assets/img/R.png" alt="" class="w-96 h-72"></div>
            </div>
            <div class="mt-8 text-center">
              <a href="./src/post/post.php" class="bg-red-500 text-white text-2xl py-3 px-8 rounded-full inline-block">今の自分を振り返る</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script>
    function toggleComments(feedbackId) {
      var commentsSection = document.getElementById('comments-' + feedbackId);
      if (commentsSection.classList.contains('hidden')) {
        commentsSection.classList.remove('hidden');
      } else {
        commentsSection.classList.add('hidden');
      }
    }

    // タイトルのアニメーション
    function typeWriter(element, text, delay = 100) {
      let i = 0;

      function typing() {
        if (i < text.length) {
          element.innerHTML += text.charAt(i);
          i++;
          setTimeout(typing, delay);
        }
      }
      typing();
    }

    window.onload = function() {
      const title1 = document.getElementById('title1');
      const title2 = document.getElementById('title2');
      const randomMessage = document.getElementById('random-message');

      title1.classList.remove('hidden');
      title2.classList.remove('hidden');

      typeWriter(title1, '振り返りをして', 100);
      setTimeout(() => {
        typeWriter(title2, '『最強の人格者』になる', 100);
      }, 2000); // 2秒後に次のテキストを表示

      randomMessage.classList.add('fade-in'); // ふわっと表示
    };
  </script>
</body>

</html>