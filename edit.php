<?php
include("function.php");

// id受け取り
$id = $_GET['id'];

// DB接続
$pdo = connect_to_db();


// SQL実行
$sql = 'SELECT * FROM drawings WHERE id=:id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
try {
  $status = $stmt->execute();
} catch (PDOException $e) {
  echo json_encode(["sql error" => "{$e->getMessage()}"]);
  exit();
}

$record = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>picaso atelier</title>
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet" />
    <link rel="stylesheet" href="./css/picaso.css">
<body>
    <div id="atelier_room">
        <section id="drow_area">
            <div id="palette">
                <div id="color_area">
                    <p>色</p>
                    <input id="color-palette" type="color" value=#000000>
                </div>

                <div id="line_width">
                    <p>太さ</p>
                    <div id="line_weight_range">
                        <input type="range" id="line_weight" name="line_weight" min="1" max="10" step="1" value="3">
                        <p id="current_value_line"></p>
                    </div>

                </div>

                <div id="speed_area">
                    <p>かいてん</p>
                    <div id="speed_range">
                        <input type="range" id="speed" name="speed" min="0" max="5" step="1" value="0">
                        <p id="current_value_speed"></p>
                    </div>
                </div>
            </div>
            <canvas id="drow" width="500" height="500" style="background-color:#fff; box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25); border-radius: 50%; margin-top: 24px;"></canvas>
        </section>

        <section id="control_area">
            <input type="text" id="username" value="<?=  $record['username'] ?>">
            <input type="text" id="title" value="<?=  $record['title'] ?>">
            <button id="php_save_btn">更新して投稿する</button>
            <br>
            <br>
            <button id="clear_btn">絵をけす</button>
            <button id="go_top">おわる</button>
        </section>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>

        // canvas関係
        //初期化(変数letで宣言)
        let canvas_mouse_event = false; //スイッチ [ true=線を引く, false=線は引かない ]  ＊＊＊
        let lastPoint = null;// 直前の「回転後の座標」
        let bold_line = 3; //ラインの太さをここで指定
        let color = "#000000"; //ラインの色をここで指定
        let spin_speed = 0; // 速さの初期設定
        let angle = 0; // 現在の角度

        // マウスの現在地を「記憶」するための変数（loopで使います）
        let currentMouseX = 0;
        let currentMouseY = 0;

        //描画（表示用）
        const can = $("#drow")[0];
        const ctx = can.getContext("2d");

        //描画（裏キャンバス）
        const drawCan = document.createElement('canvas');
        drawCan.width = can.width;
        drawCan.height = can.height;
        const drawCtx = drawCan.getContext('2d');

        // キャンバスを円形に切り抜く設定
        const radius = drawCan.width / 2; // 半径を計算
        drawCtx.beginPath();
        drawCtx.arc(radius, radius, radius, 0, Math.PI * 2); // sizeをradiusに変更
        drawCtx.clip();

        // キャンバスの初期値
        drawCtx.fillStyle = "#fff";
        drawCtx.fillRect(0, 0, drawCan.width, drawCan.height);

        //mousedown：フラグをTrue
        $(can).on("mousedown",function(e){
            lastPoint = getRotatedPoint(e.offsetX, e.offsetY, angle);
            canvas_mouse_event=true;
        });

        //mousemove：フラグがTrueだったら描く ※e：イベント引数取得
        $(can).on("mousemove",function(e){
            currentMouseX = e.offsetX;
            currentMouseY = e.offsetY;      
            
        });

        // 座標変換の関数
        function getRotatedPoint(x, y, currentAngle) {
            const rad = -currentAngle * Math.PI / 180; // 角度をラジアンに変換
            const tx = x - can.width / 2;  // 中心を(0,0)にずらす
            const ty = y - can.height / 2;
            // 回転後の座標を計算
            const rx = tx * Math.cos(rad) - ty * Math.sin(rad);
            const ry = tx * Math.sin(rad) + ty * Math.cos(rad);
            return { x: rx + can.width / 2, y: ry + can.height / 2 };
        }

        // 描画ループ（映写機の役割）
        function loop() {
            angle += spin_speed; // 角度を更新

            if (canvas_mouse_event === true && lastPoint) {
                // 現在のマウス位置を、今の角度で「紙の上」に翻訳
                const currentPoint = getRotatedPoint(currentMouseX, currentMouseY, angle);

                drawCtx.strokeStyle = color;
                drawCtx.lineWidth = bold_line;
                drawCtx.lineCap = "round";
                drawCtx.beginPath();
                drawCtx.moveTo(lastPoint.x, lastPoint.y);
                drawCtx.lineTo(currentPoint.x, currentPoint.y);
                drawCtx.stroke();

                lastPoint = currentPoint; // 記憶を更新
            }

            // 表側のキャンバスを一旦真っさらにする
            ctx.clearRect(0, 0, can.width, can.height);

            // 回転させて裏キャンバスを貼り付ける
            ctx.save();
            ctx.translate(can.width / 2, can.height / 2); // 中心に移動
            ctx.rotate(angle * Math.PI / 180);           // 回転させる
            ctx.translate(-can.width / 2, -can.height / 2); // 元に戻す
            ctx.drawImage(drawCan, 0, 0);                 // 裏キャンバスをコピー
            ctx.restore();

            // 次のフレームを予約（1秒間に60回実行）
            requestAnimationFrame(loop);
        }

        // ループを開始する
        loop();


        $(can).on("mouseup", async function(e){
            canvas_mouse_event=false;                
            lastPoint = null;
        });

        // mouseleave：フラグをFalse (キャンバスからマウスが出たとき)
        $(can).on("mouseleave",function(e){
            canvas_mouse_event = false;
            lastPoint = null;
        });

        // mouseenter：再突入時のフラグチェック (より安全にするため)
        $(can).on("mouseenter",function(e){
            // マウスボタンが押されたままキャンバス外で離され、再度入ってきた場合の誤作動を防ぐ
            if (e.buttons !== 1) { // buttons === 1 は左クリックが押されている状態
                canvas_mouse_event = false;
                lastPoint = null;
            }
        });

        // 線の太さ、回転の速さのスライダーの数値を表示させる
        $(document).ready(function(){
            // 画像オブジェクトを作成
            const img = new Image();
            
            // PHPから渡された画像データ(Base64)をセット
            img.src = "<?= $record['canvas_data'] ?>"; 

            // 画像が読み込み終わったら実行
            img.onload = function() {
                // 裏キャンバス(drawCtx)に描くことで、自動的にloopで回転
                drawCtx.drawImage(img, 0, 0, drawCan.width, drawCan.height);
            };


            // 線の太さの初期設定
            $('#current_value_line').text(bold_line);
            // 線の太さが変わってもスライダーの数値を表示させる
            $('#line_weight').on('input', async function(){
                const new_value = $(this).val();
                $('#current_value_line').text(new_value);
                // グローバルに数値型に変更して入れる
                bold_line = Number(new_value);
            });

            // 回転の速さの初期設定
            // 別途定義しているのでそれに修正する必要あり
            $('#current_value_speed').text(spin_speed+"倍");
            // 回転の速さが変わってもスライダーの数値を表示させる
            $('#speed').on('input',function(){
                const new_value = $(this).val();
                $('#current_value_speed').text(new_value+"倍");
                // グローバルに数値型に変更して入れる
                spin_speed = Number(new_value);
            });

            let color = $('#color-pallet').val();             
            });
        
        // パレットの色が変わった時に、描画色（color）を更新する
        $('#color-palette').on('input', function() {
            color = $(this).val();
        });    

        // PHPへ保存するボタンの処理
        $('#php_save_btn').on('click', function() {
            const canvas_data = drawCan.toDataURL('image/png');
            const username = $('#username').val();
            const title = $('#title').val();
            const id = "<?= $record['id'] ?>";

            if (!username || !title || !canvas_data) {
                alert("なまえとタイトルをいれてね！");
                return;
            }

            // FormDataを使って PHP(create.php) に送る準備
            const params = new FormData();
            params.append('id', id);
            params.append('username', username);
            params.append('title', title);
            params.append('canvas_data', canvas_data);

            // fetchで create.php を実行しparamsを送る
            fetch('update.php', {
                method: 'POST',
                body: params
            })
            .then(response => {
                alert("アトリエに保存しました！");
                // 保存が終わったら一覧画面（index.php）へ移動
                window.location.href = 'index.php';
            })
            .catch(error => {
                console.error('Error:', error);
                alert("更新に失敗しました");
            });
        });

        //#clear_btn：クリアーボタンAction
        $('#clear_btn').on('click',async function(){
            drawCtx.save(); // 現在の状態（クリップ設定）を保存
            drawCtx.setTransform(1, 0, 0, 1, 0, 0); // 念のため変形をリセット
            drawCtx.fillStyle = "#fff";
            drawCtx.fillRect(0, 0, drawCan.width, drawCan.height);
            drawCtx.restore(); // 保存した状態を戻す
            
            // 表側のキャンバス（見えている画面）を消す
            ctx.clearRect(0, 0, can.width, can.height);
            
            // 描きかけの座標メモも消す
            lastPoint = null;
        });    
             
        //go_top：HOMEに戻る
        $('#go_top').on('click',function(){
            window.location.href = 'index.php'; 
        });
        
    </script>

</body>
</html>