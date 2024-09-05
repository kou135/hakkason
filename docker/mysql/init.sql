DROP DATABASE IF EXISTS posse;
CREATE DATABASE posse;
USE posse;

DROP TABLE IF EXISTS user;
CREATE TABLE user(
  id INT PRIMARY KEY AUTO_INCREMENT,
  password VARCHAR(32) NOT NULL,
  email VARCHAR(32) NOT NULL,
  name VARCHAR(20) NOT NULL,
  gender INT,
  category INT,
  number FLOAT,
  photo_path VARCHAR(255) DEFAULT NULL,
  friend_cord VARCHAR(4)
);

INSERT INTO user (id, password, email, name, gender, category, number, photo_path, friend_cord)
  VALUES (1, "test1234", "test1@gmail.com", "鈴木鴻太", 1, 2, 4.0, "S__378249219.jpg", 2536),
  (2, "test2234", "test2@gmail.com", "吉川貴之", 1, 1, 3.0, "S__38912014.jpg", 7366),
  (3, "test3234", "test3@gmail.com", "比嘉珠佑", 2, 3, 4.5, "S__259203074.jpg", 9365),
  (4, "test4234", "test4@gmail.com", "堤めい", 2, 2, 4.5, "S__17924100.jpg", 9333);

DROP TABLE IF EXISTS feedback;
CREATE TABLE feedback(
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  title TEXT,
  score INT,
  reason TEXT,
  real_action TEXT,
  study TEXT,
  next_action TEXT,
  event_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO feedback(id, user_id, title, score, reason, real_action, study, next_action, event_date, created_at, updated_at)
VALUES
(1, 1, "テストタイトル", 4, "よくできたから", "こうやりました", "これを学んだ", "次はもっとこうします", '2024-08-27', NOW(), NOW()),
(2, 1, "テストタイトル2", 2, "だめだめだったから", "こうやりました2", "それを学んだ", "自分の弱点が把握できたので改善します", '2024-08-25', NOW(), NOW()),
(3, 3, "テストタイトル3", 5, "最高でした！", "こうやりました3", "あれを学んだ", "これを続けていきたい！", '2024-08-28', NOW(), NOW()),
(4, 1, "テストタイトル4", 4, "5期生に擬似クラスを説明できた!", "カリキュラムを何度も見直した", "理解するだけではなく教えることで脳に定着した", "常に人に教えるつもりで学ぶ", '2024-07-21', NOW(), NOW()),
(5, 2, "テストタイトル5", 2, "dockerの概念を理解できなかった", "youtubeの動画を見まくった", "先輩や同期に聞いた方が早かったかも", "先輩や同期に相談してみる", '2024-03-07', NOW(), NOW()),
(6, 1, "テストタイトル6", 3, "カリキュラムを予定通りに進めた", "わからないところをこうたに聞いた", "Harborsですぐ近くの人に聞くのが良かった", "できるだけHarborsに行きたい", '2024-08-28', NOW(), NOW()),
(7, 2, "テストタイトル7", 2, "よくできたから9", "ハッカソンでsqlの呼びだしコードができなかった", "youtubeの動画で見て学んだ", "カリキュラムを理解していきたい", '2023-08-28', NOW(), NOW()),
(8, 2, "テストタイトル8", 5, "コード賞を受賞できた！！", "カリキュラムの内容を完全に理解していた", "カリキュラムを深く理解すればある程度のコード力は身に付く", "まずはカリキュラムの内容を理解する", '2024-06-08', NOW(), NOW()),
(9, 3, "テストタイトル9", 1, "カリキュラムが全く進まなかった", "Harborsでおしゃべりしすぎた", "メリハリをつけてやる！", "今日までにここだけは終わらせるという範囲を決める", '2024-02-18', NOW(), NOW()),
(10, 4, "テストタイトル10", 4, "カリキュラムを２週間分早く進めれ続けている", "毎週月曜日位のMU前にできるだけ進めている", "月曜日にするからその週は気持ちが楽になる", "自分は余裕を持って勉強する方が理解しやすいのかも？", '2023-11-17', NOW(), NOW());


DROP TABLE IF EXISTS comments;
CREATE TABLE comments(
  id INT PRIMARY KEY AUTO_INCREMENT,
  post_user_id INT,
  accept_user_id INT,
  feedback_id INT,
  comments text,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO comments(id, post_user_id, accept_user_id, feedback_id, comments, created_at)
VALUES(1, 2 , 1, 1, "めっちゃいいじゃん！", NOW()),
(2, 2 , 1, 2, "改善できた？", NOW()),
(3, 1 , 3, 3, "え、ほんとわかる", NOW()),
(4, 3 , 1, 1, "え、すごい！！", NOW()),
(5, 3 , 1, 2, "それはやばすぎるて", NOW()),
(6, 4 , 3, 3, "え、ほんとわかる", NOW()),
(7, 2 , 1, 6, "めっちゃいいじゃん！", NOW()),
(8, 1 , 2, 2, "改善できた？", NOW()),
(9, 1 , 3, 9, "え、ほんとわかる", NOW());

DROP TABLE IF EXISTS event_category;
CREATE TABLE event_category(
  id INT PRIMARY KEY AUTO_INCREMENT,
  feedback_id INT,
  category INT
);

INSERT INTO event_category(id, feedback_id, category)
VALUES(1, 1, 2),
(2, 2, 3),
(3, 3, 2);

CREATE TABLE friendship (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO friendship(id, user_id, friend_id, created_at)
VALUES(1, 1, 2, NOW()),
(2, 1, 3, NOW()),
(3, 2, 3, NOW());