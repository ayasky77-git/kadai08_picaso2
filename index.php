<?php
include('function.php');

// DB接続
$pdo = connect_to_db();

// SQL作成&実行（最新の投稿順に並べる）
$sql = 'SELECT * FROM `picaso_drawings` LEFT OUTER JOIN (SELECT drawings_id, COUNT(id) As like_count From like_table GROUP BY drawings_id) AS result_table ON picaso_drawings.id = result_table.drawings_id WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 10';

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
  $output .=   "<div class='like'><img class='card-like' src='./images/heart.png' alt='いいね'></><p class='card-like-font'>{$record["like_count"]}</p></div>";
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
            <button id="login">ログイン</button>
        </div>

        <h2 class="recent_art">--最近の作品--</h2>
        <div id="gallery">
          <?= $output ?>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    <script>
        //いいねクリック時
        $('.card-like').on('click',function(){
          if (confirm('いいねにはログインが必要です')) {
              window.location.href = 'login.php'; 
          }else{
            // false だったら、リンク先への移動（削除実行）を中止する
            return false;
          }
        });
        // 画面遷移
        $('#make_atelier').on('click',function () {  
            // 実際にお絵描きする画面（保存機能がある画面）へ
            window.location.href = 'atelier.php'; 
        });
        // ログイン画面遷移
        $('#login').on('click',function () {  
            // 実際にお絵描きする画面（保存機能がある画面）へ
            window.location.href = 'login.php'; 
        });
    </script>
</body>
</html>