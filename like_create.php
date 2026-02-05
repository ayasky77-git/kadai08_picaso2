<?php
include("function.php");
session_start();
check_session_id();

$user_id = $_GET['user_id'];
$drawings_id = $_GET['id'];

$pdo = connect_to_db();

// likeの有無を確認する
$sql = 'SELECT COUNT(*) FROM like_table WHERE drawings_id = :drawings_id AND user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':drawings_id', $drawings_id, PDO::PARAM_INT);

try {
  $status = $stmt->execute();
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

$like_count= $stmt->fetchColumn();
if($like_count>0){
    // likeしている状態
    $sql ='DELETE FROM like_table WHERE user_id = :user_id AND drawings_id = :drawings_id';
}else{
    // likeしていない状態
    $sql = 'INSERT INTO like_table (id, user_id, drawings_id, created_at) VALUES (NULL, :user_id, :drawings_id, now())';
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':drawings_id', $drawings_id, PDO::PARAM_INT);

try {
  $status = $stmt->execute();
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

header("Location:home.php");
exit();