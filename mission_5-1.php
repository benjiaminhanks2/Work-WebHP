<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>データベース掲示板</title>
</head>
<body>


<?php
//MySQL内のデータベースへの接続(pdoの構築)
$dsn='mysql:dbname=データベース名; host=localhost';
$user='ユーザー名';
$password='パスワード';
$pdo=new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


//掲示板用のデータベースの作成(SHOW TABLEで確認済み)
$sql = "CREATE TABLE IF NOT EXISTS bulletin_board"
." ("
."id INT AUTO_INCREMENT PRIMARY KEY, "  
."name varchar(32),"
."comment TEXT,"
."time DATETIME,"
//最後はコンマで区切ると構文エラーになるので注意
."password TEXT"
.");";
$stmt = $pdo->query($sql);


//編集対象を投稿フォーム内に表示
$edit_num = "";
$edit_name = "";
$edit_mess = "";
if(isset($_POST["edit"])&&isset($_POST["editNo"])&&isset($_POST["pass3"])){ 
	$id = $_POST["editNo"];  //編集したい番号・パスワードの指定
	$pass = $_POST["pass3"];
	$sql = 'SELECT * FROM bulletin_board';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
		if(($id == $row['id'])&&($pass == $row['password'])){
			$edit_num = $row['id'];
			$edit_name = $row['name'];
			$edit_mess = $row['comment'];
		}
	}
}


?>


<!-- フォームの作成 -->
<h4>【投稿フォーム】</h4>
<form action="" method="post">

<label for="name1">名前:</label>
<input id="name1" type="text" name="name" value="<?php echo htmlspecialchars($edit_name); ?>"
placeholder="なまえ">

<br><label for="comment1">コメント：</label>
<input id="comment1" name="comment" value="<?php echo htmlspecialchars($edit_mess); ?>"
placeholder="コメント">

<br><label for="pass-word1">パスワード：</label>
<input id="pass-word1" type="password" name="pass1" placeholder="パスワード">

<br><input type="hidden" name="edit_num" value="<?php echo htmlspecialchars($edit_num); ?>"
>

<br><input type="submit" name="toukou" value="送信">



<p><p><h4>【削除フォーム】</h4>
<label for="delete1">削除番号:</label>
<input id="delete1" type="text" name="deleteNo" placeholder="削除番号">

<br><label for="pass-word2">パスワード：</label>
<input id="pass-word2" type="password" name="pass2" placeholder="パスワード">

<p><input type="submit" name="delete" value="削除">



<p><p><h4>【編集フォーム】</h4>
<label for="editing">編集番号:</label>
<input id="editing" type="text" name="editNo" placeholder="編集番号">

<br><label for="pass-word3">パスワード：</label>
<input id="pass-word3" type="password" name="pass3" placeholder="パスワード">

<p><input type="submit" name="edit" value="編集">
<p>
</form>



<?php
//投稿処理 
	//フォーム内の空欄によるエラー表示
if(empty($_POST["edit_num"])&&!empty($_POST["toukou"])){
	if($_POST["name"]==""){
	echo "【エラー】名前を入力してください<br>";
	}
	elseif($_POST["comment"]==""){
	echo "【エラー】コメントを入力してください<br>";
	}
	elseif($_POST["pass1"]==""){
	echo "【エラー】パスワードを入力してください<br>";
	}
}
echo "<br>";

	//プリペアドステートメントSQL文の準備
	//データが送信された際にプリペアド文を実行しデータベースにデータを送る
if(empty($_POST["edit_num"])&&!empty($_POST["comment"])&&!empty($_POST["name"])&&!empty($_POST["pass1"])&&!empty($_POST["toukou"])){  
	$sql = $pdo->prepare("INSERT INTO bulletin_board (name, comment, time, password) VALUES (:name, :comment, :time, :password)");
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql -> bindParam(':password', $password, PDO::PARAM_STR);
	$sql -> bindParam(':time', $date, PDO::PARAM_STR);
	$name = $_POST["name"];
	$comment = $_POST["comment"];
	$password = $_POST["pass1"];
	$date = date("Y-m-d H:i:s");
	echo "【直近の投稿】<br>";
	echo $name."さんの投稿 ".$comment." を受け付けました。<br>";
	$sql -> execute();
}


//削除処理
	//エラーメッセージの表記
if(!empty($_POST["delete"])){
	if($_POST["deleteNo"]==""){
	echo "<br>【エラー】削除番号を指定してください<br>";
	}
	elseif($_POST["pass2"]==""){
	echo "<br>【エラー】削除用パスワードを入力してください<br>";
	}
}
	//削除の条件分岐
if(isset($_POST["delete"])&&isset($_POST["deleteNo"])&&isset($_POST["pass2"])){  
	$id = $_POST["deleteNo"];  //削除したい番号・パスワードの指定
	$pass = $_POST["pass2"];

	//テーブル内のレコードをfetchAllで配列化。pass,idの一致確認。
	//それによるメッセージの表記
	$sql = 'SELECT * FROM bulletin_board';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
		if(($id == $row['id'])&&($pass == $row['password'])){
			echo "【報告】投稿番号 ".$id." が削除されました。<br>";
		}
	}

	//プリペアドステートメントSQL文の準備
	//SQL文を実行し削除処理
	$sql = 'delete from bulletin_board where id = :id and password = :pass2';
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->bindParam(':pass2', $pass, PDO::PARAM_STR);
	$stmt->execute();
}      
					//今後：パスワードの不一致によるエラーメッセージの実装、


//編集処理
	//編集選択後の投稿フォーム内でのエラー表示
if(!empty($_POST["edit_num"])){
	if($_POST["name"]==""){
	echo "<br>【エラー】編集をやり直してください<br>";
	}
	elseif($_POST["comment"]==""){
	echo "<br>【エラー】編集をやり直してください<br>";
	}
	elseif($_POST["pass1"]==""){
	echo "<br>【エラー】編集をやり直してください<br>";
	}
}

	//編集処理の条件分岐
if(!empty($_POST["edit_num"])&&!empty($_POST["comment"])&&!empty($_POST["name"])&&!empty($_POST["pass1"])){
	$edinum=$_POST["edit_num"];
	$ediname=$_POST["name"];
	$edimess=$_POST["comment"];
	$edipass=$_POST["pass1"];
	
	//削除の際と同様にSELECT取得後、配列化して編集の完了報告
	$sql1 = 'SELECT * FROM bulletin_board';
	$stmt1 = $pdo->query($sql1);
	$results = $stmt1->fetchAll();
	foreach ($results as $row){
		if(($edinum == $row['id'])){
			echo "【報告】投稿番号 ".$edinum." が編集されました。<br>";
		}
	}

	//編集対象の取得時にパスと番号の一致確認は済ませてあるので、ここでのWHERE句はidでの比較のみ
	$sql = 'update bulletin_board set name=:name, comment=:comment, password=:password where id=:id';
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':name', $ediname, PDO::PARAM_STR);
	$stmt->bindParam(':comment', $edimess, PDO::PARAM_STR);
	$stmt->bindParam(':password', $edipass, PDO::PARAM_STR);
	$stmt->bindValue(':id', $edinum, PDO::PARAM_INT);
	$stmt->execute();
}


	//編集フォームのエラーメッセージ
if(!empty($_POST["edit"])){
	if($_POST["editNo"]==""){
	echo "<br>【エラー】編集番号を指定してください<br>";
	}
	elseif($_POST["pass3"]==""){
	echo "<br>【エラー】編集用パスワードを入力してください<br>";
	}
}


					//今後：パスワードの不一致によるエラーメッセージの実装、

//データベース内の一覧表示
echo "<br>【投稿一覧】<br>";
$sql = 'SELECT * FROM bulletin_board';
$stmt = $pdo->query($sql);
//PDOStatementsを配列に変換している
$results = $stmt->fetchAll();
foreach ($results as $row){
//$row['カラム(フィールド)名']とし、各行(レコード)を一つずつ表示していく
	echo $row['id'].', ';
	echo $row['name'].', ';
	echo $row['comment'].', ';
	echo $row['time'].', ';
	echo $row['password'].'<br>';
echo "<hr>";
}
?>


</body>
</html>
