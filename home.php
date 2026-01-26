<?php
include("function.php");
session_start();
check_session_id();

// DB接続
$pdo = connect_to_db();

// SQL作成&実行（最新の投稿順に並べる）
$sql = 'SELECT * FROM picaso_drawings WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 10';

$stmt = $pdo->prepare($sql);

// SQL実行
try {
  $status = $stmt->execute();
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

// resultに結果を全て入れる
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$output = "";

// foreachで表示したいHTMLの形を作る
foreach($result as $record){
  $output .= "<div class='card'>";
  $output .=   "<p class='card-title'><strong>{$record["title"]}</strong></p>";
  $output .=   "<img src='{$record["canvas_data"]}' class='card-img' style='width:100%; height:180px; object-fit:contain;'>";
  $output .=   "<p class='card-author'>by {$record["username"]}</p>";
  $output .=   "<p class='card-date'>{$record["created_at"]}</p>";
  $output .=   "<a href='edit.php?id={$record["id"]}'><img class='card-edit' src='./images/edit.png' alt='編集'></a>";
  $output .=   "<a href='delete.php?id={$record["id"]}'><img class='card-delete' src='./images/trash-2.png' alt='削除'></a>";
  $output .= "</div>";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>picaso</title>
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet" />
    <link rel="stylesheet" href="./css/picaso.css">
</head>
<body>
    <div id="main">
        <img src="./images/logo.png" alt="logo" id="logo">
        <div id="opening">
            <button id="make_atelier">アトリエにいく</button>
            <button id="logout">ログアウト</button>


        </div>

        <h2 class="recent_art">--最近の作品--</h2>
        <div id="gallery">
          <?= $output ?>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    <script>
        //削除時
        $('.card-delete').on('click',function(){
          if (!confirm('本当に消していいですか？')) {
          // false だったら、リンク先への移動（削除実行）を中止する
          return false;
          }
        });
        // 画面遷移
        $('#make_atelier').on('click',function () {  
            // 実際にお絵描きする画面（保存機能がある画面）へ
            window.location.href = 'atelier.php'; 
        });
        // ログアウト画面遷移
        $('#logout').on('click',function () {  
            // 実際にお絵描きする画面（保存機能がある画面）へ
            window.location.href = 'logout.php'; 
        });
    </script>
</body>
</html>