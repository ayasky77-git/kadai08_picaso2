<?php
include('function.php');

// データ受け取り
if (
  !isset($_GET['id']) || $_GET['id'] === ''
) {
  exit('paramError');
}

$id = $_GET['id'];

// DB接続
$pdo = connect_to_db();

// SQL実行(論理削除)
$sql = 'UPDATE picaso_drawings SET deleted_at=now() WHERE id=:id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

try {
  $status = $stmt->execute();
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

header("Location:index.php");
exit();
?>