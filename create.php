<?php
include('function.php');

// usernameとcanvas_dataが揃っていることを確認
if(
  !isset($_POST['username']) || $_POST['username']==='' || 
  !isset($_POST['title']) || $_POST['title']==='' || 
  !isset($_POST['canvas_data']) || $_POST['canvas_data']===''
){
  exit('データがありません');
}

$username = $_POST['username'];
$title = $_POST['title'];
$canvas_data = $_POST['canvas_data'];

// DB接続（さくらサーバーに上げる時はここを書き換える）
$pdo = connect_to_db();

// SQL作成&実行
$sql = 'INSERT INTO picaso_drawings (id, username, title, canvas_data, created_at, updated_at, deleted_at) VALUES(Null, :username, :title, :canvas_data, now(),now(),Null);';

$stmt = $pdo->prepare($sql);

// バインド変数を設定
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->bindValue(':title', $title, PDO::PARAM_STR);
$stmt->bindValue(':canvas_data', $canvas_data, PDO::PARAM_STR);

// SQL実行
try {
  $status = $stmt->execute();
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

// 登録が終わったら、一覧画面（index.php）に移動する
header('Location:index.php');
exit();