<?php
require '../../dbconnect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $dbh->prepare('SELECT * FROM user WHERE id = :user_id');
$stmt->execute([':user_id' => $user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_cord'])) {
    $friend_cord = $_POST['friend_cord'];

    // friend_cordに基づいて友達を検索
    $stmt = $dbh->prepare('SELECT id FROM user WHERE friend_cord = :friend_cord');
    $stmt->execute([':friend_cord' => $friend_cord]);

    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($friend) {
        $friend_id = $friend['id'];

        // 既に友達かどうかを確認
        $checkStmt = $dbh->prepare('SELECT * FROM friendship WHERE user_id = :user_id AND friend_id = :friend_id');
        $checkStmt->execute([':user_id' => $user_id, ':friend_id' => $friend_id]);

        if (!$checkStmt->fetch()) {
            // 友達関係を保存
            $insertStmt = $dbh->prepare('INSERT INTO friendship (user_id, friend_id) VALUES (:user_id, :friend_id)');
            $insertStmt->execute([':user_id' => $user_id, ':friend_id' => $friend_id]);

            echo '';
        } else {
            echo '';
        }
    } else {
        echo '';
    }
}

// 期間に基づいたSQL条件を作成
$period = $_POST['period'] ?? '';
$periodCondition = '';
if ($period == '1week') {
    $periodCondition = 'AND feedback.event_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)';
} elseif ($period == '1month') {
    $periodCondition = 'AND feedback.event_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
} elseif ($period == '3months') {
    $periodCondition = 'AND feedback.event_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)';
} elseif ($period == '6months') {
    $periodCondition = 'AND feedback.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
}

$stmt = $dbh->prepare("
    SELECT
        feedback.*,
        (SELECT COUNT(*) FROM comments WHERE comments.feedback_id = feedback.id) AS comment_count,
        event_category.*
    FROM
        feedback
    INNER JOIN
        friendship ON feedback.user_id = friendship.friend_id
    LEFT JOIN
        event_category ON feedback.id = event_category.feedback_id
    WHERE
        friendship.user_id = :user_id
        $periodCondition
");
// クエリの実行
$stmt->execute([':user_id' => $user_id]);

// 結果を取得
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// フィードバックごとのコメントを取得
$comments = [];
foreach ($feedbacks as $feedback) {
    $commentStmt = $dbh->prepare('
        SELECT comments.*, user.name AS commenter_name, user.category AS commenter_category, user.photo_path AS commenter_path
        FROM comments
        LEFT JOIN user ON comments.post_user_id = user.id
        WHERE feedback_id = :feedback_id
    ');
    $commentStmt->execute([':feedback_id' => $feedback['id']]);
    $comments[$feedback['id']] = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $feedback_id = $_POST['feedback_id'];
    $comment = $_POST['comment'];

    $stmt = $dbh->prepare('INSERT INTO comments (post_user_id, feedback_id, comments) VALUES (:post_user_id, :feedback_id, :comments)');
    $stmt->execute([
        ':post_user_id' => $user_id,
        ':feedback_id' => $feedback_id,
        ':comments' => $comment
    ]);

    // 元のページにリダイレクトして、二重投稿を防ぐ
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $feedback_id = $_POST['feedback_id'];
    $comment = $_POST['comment'];

    $stmt = $dbh->prepare('INSERT INTO comments (post_user_id, feedback_id, comments) VALUES (:post_user_id, :feedback_id, :comments)');
    $stmt->execute([
        ':post_user_id' => $user_id,
        ':feedback_id' => $feedback_id,
        ':comments' => $comment
    ]);

    // 元のページにリダイレクトして、二重投稿を防ぐ
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>友達投稿画面</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white">
    <div class="flex">
        <!-- サイドバー -->
        <section class="bg-gray-800 w-1/5 h-screen p-4 sticky top-0">
            <div class="mb-10">
                <div class="flex items-center justify-between">
                    <div>
                        <img src="../../assets/img/No greed.png" alt="Logo" class="mb-6 rounded-3xl size-28">
                    </div>
                    <div class="ml-4">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="flex justify-center">
                                <img src="../../assets/img/<?php echo htmlspecialchars($user['photo_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="User Icon" class="w-16 h-16 rounded-full">
                            </div>
                            <div>
                                <span class="mr-1 text-lg">
                                    <?php
                                    $symbol = '';
                                    if ($user['category'] == 1) {
                                        $symbol = '①';
                                    } elseif ($user['category'] == 2) {
                                        $symbol = '②';
                                    } elseif ($user['category'] == 3) {
                                        $symbol = '③';
                                    }
                                    echo htmlspecialchars($symbol, ENT_QUOTES, 'UTF-8') . ' ';
                                    ?>
                                </span>
                                <span class="text-lg"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></span>さん
                            </div>
                        <?php else: ?>
                            <a href="../../user/login/login.php" class="text-white text-lg">ログイン</a>
                        <?php endif; ?>
                    </div>
                </div>
                <nav>
                    <ul class="space-y-4">
                        <li class="mb-4 h-16 hover:bg-gray-100 flex items-center">
                            <a href="../home/home.php" class="flex items-center text-lg">
                                <span class="text-3xl">ホーム</span>
                            </a>
                        </li>
                        <li class="mb-4 h-16 hover:bg-gray-100 flex items-center">
                            <a href="../../post/post.php" class="flex items-center text-lg hover:bg-gray-100">
                                <span class="text-3xl">投稿</span>
                            </a>
                        </li>
                        <li class="mb-4 h-16 hover:bg-gray-100 flex items-center">
                            <a href="./mypage.php" class="flex items-center text-lg hover:bg-gray-100">
                                <span class="text-3xl">マイページ</span>
                            </a>
                        </li>
                        <li class="mb-4 h-16 hover:bg-gray-100 flex items-center">
                            <a href="./friend.php" class="flex items-center text-lg hover:bg-gray-100">
                                <span class="text-3xl">友達</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>

        <!-- メインコンテンツ -->
        <section class="w-4/5 flex justify-center p-4">
            <div class="w-11/12 p-6 rounded-lg shadow-lg">
                <!-- 友達追加 -->
                <div class="mb-8">
                    <form action="./friend.php" method="POST">
                        <input type="text" name="friend_cord" placeholder="友達コードを入力" class="bg-gray-800 text-white p-2 rounded">
                        <button class="bg-blue-500 text-white px-4 py-2 mt-2 rounded" type="submit">友達追加</button>
                        <!-- 自分の友達コードを表示 -->
                        <div class="ml-4 text-lg">
                            あなたの友達コード: <span class="text-green-500"><?php echo htmlspecialchars($user['friend_cord'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </form>
                    <!-- メッセージをここに表示 -->
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_cord'])): ?>
                        <div class="mt-4 text-lg">
                            <?php
                            if (isset($friend_id) && !$checkStmt->fetch()) {
                                echo '<span class="text-green-500">友達が追加されました！</span>';
                            } elseif (isset($friend_id)) {
                                echo '<span class="text-yellow-500">既に友達です。</span>';
                            } else {
                                echo '<span class="text-red-500">該当する友達コードが見つかりませんでした。</span>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- タブメニュー -->
                <div class="flex space-x-4 mb-6">
                    <div id="tab-1" class="cursor-pointer px-4 py-2 bg-red-500 rounded-full text-black font-semibold" onclick="filterCategory(1)">日記</div>
                    <div id="tab-2" class="cursor-pointer px-4 py-2 bg-gray-700 rounded-full" onclick="filterCategory(2)">サバリ</div>
                    <div id="tab-3" class="cursor-pointer px-4 py-2 bg-gray-700 rounded-full" onclick="filterCategory(3)">ハッカソン</div>
                    <div id="tab-4" class="cursor-pointer px-4 py-2 bg-gray-700 rounded-full" onclick="filterCategory(4)">MU</div>
                    <div id="tab-5" class="cursor-pointer px-4 py-2 bg-gray-700 rounded-full" onclick="filterCategory(5)">イベント</div>
                    <div id="tab-0" class="cursor-pointer px-4 py-2 bg-gray-700 rounded-full" onclick="filterCategory(0)">全て</div>
                </div>

                <!-- 期間セレクトボックス -->
                <div class="mb-8">
                    <form action="./friend.php" method="POST">
                        <select name="period" onchange="this.form.submit()" class="bg-gray-800 text-white px-4 py-2 rounded">
                            <option value="">全ての期間</option>
                            <option value="1week" <?php echo $period == '1week' ? 'selected' : ''; ?>>１週間</option>
                            <option value="1month" <?php echo $period == '1month' ? 'selected' : ''; ?>>１か月</option>
                            <option value="3months" <?php echo $period == '3months' ? 'selected' : ''; ?>>３か月</option>
                            <option value="6months" <?php echo $period == '6months' ? 'selected' : ''; ?>>半年</option>
                        </select>
                    </form>
                </div>

                <!-- 投稿コンテンツ -->
                <div class="flex flex-wrap justify-center gap-8 -mx-4 items-start">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div style="width: 48%;" class="category-item border border-white p-4 rounded-lg shadow w-full mb-6 px-4" data-category="<?php echo htmlspecialchars($feedback['category'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="flex justify-between items-center">
                                <div class="text-3xl font-semibold mb-4"><?php echo htmlspecialchars($feedback['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="mb-2 text-gray-400 text-xl"><?php echo htmlspecialchars($feedback['event_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="mb-1 text-xl">○今後の目標</div>
                            <div class="text-gray-300 mb-4 text-xl"><?php echo htmlspecialchars($feedback['next_action'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php if ($feedback['comment_count'] > 0): ?>
                                <div class="cursor-pointer text-blue-500 text-xl" onclick="toggleComments(<?php echo $feedback['id']; ?>)">
                                    コメント<?php echo $feedback['comment_count']; ?>件を見る
                                </div>
                            <?php else: ?>
                                <div class="text-gray-500">コメントはありません</div>
                            <?php endif; ?>

                            <!-- コメント一覧とフォーム -->
                            <div id="comments-<?php echo $feedback['id']; ?>" class="hidden mt-4">
                                <div class="space-y-4">
                                    <?php foreach ($comments[$feedback['id']] as $comment): ?>
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

                                <!-- コメント投稿フォーム -->
                                <form action="./friend.php" method="POST" class="mt-4">
                                    <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                    <textarea name="comment" class="w-full bg-gray-800 text-white p-2 rounded" rows="3" placeholder="コメントを追加"></textarea>
                                    <button type="submit" name="add_comment" class="bg-blue-500 text-white px-4 py-2 mt-2 rounded">コメントを投稿</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
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

        function filterCategory(categoryId) {
            var items = document.querySelectorAll('.category-item');
            var tabs = document.querySelectorAll('.cursor-pointer');

            items.forEach(function(item) {
                if (item.getAttribute('data-category') == categoryId || categoryId == 0) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });

            tabs.forEach(function(tab) {
                if (tab.id === 'tab-' + categoryId) {
                    tab.classList.add('bg-red-500', 'text-black', 'font-semibold');
                    tab.classList.remove('bg-gray-700', 'text-white');
                } else {
                    tab.classList.add('bg-gray-700', 'text-white');
                    tab.classList.remove('bg-red-500', 'text-black', 'font-semibold');
                }
            });
        }
    </script>
</body>

</html>