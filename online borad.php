<?php
// 初期化
$editnum = "";
$defname = "名無し";
$deftext = "";
$message="";

// DB接続設定
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	
//新規投稿
if (! empty($_POST["text"]) && ! empty($_POST["name"]) && empty($_POST["num"])) {
    $name = $_POST["name"];
    $text = $_POST["text"];
    $date = date("Y/m/d H:i:s");
    $pass = $_POST["newpass"];
	//DB書き込み
	$sql = $pdo -> prepare("INSERT INTO tbtest (name, comment, dt, pass) VALUES (:name, :comment, :dt, :pass)");
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $text, PDO::PARAM_STR);
	$sql -> bindParam(':dt', $date, PDO::PARAM_STR);
	$sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
	$sql -> execute();
    $message = "書き込み成功!<br>";
}

//編集処理
if (! empty($_POST["text"]) && ! empty($_POST["name"]) && ! empty($_POST["num"])){
    $num = $_POST["num"];
    $name = $_POST["name"]."(編集済み)";
    $text = $_POST["text"];
    //$date = date("Y/m/d H:i:s");
    $pass = $_POST["newpass"];
    $edited = false;
    //DB読み込み
    $sql = 'SELECT * FROM tbtest';
	$stmt = $pdo->query($sql);
	$contents = $stmt->fetchAll();
	//すべての行($contents)について編集番号を比較
	foreach ($contents as $content){
		//投稿番号が一致した行のみを書き換える
		if ($content['id']==$num){
		    $date = $content['dt'];
		    //DBupdate
		    $sql = 'UPDATE tbtest SET name=:name,comment=:comment,dt=:dt,pass=:pass WHERE id=:id';
		    $stmt = $pdo->prepare($sql);
		    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
		    $stmt->bindParam(':comment', $text, PDO::PARAM_STR);
		    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
		    $stmt->bindParam(':dt', $date, PDO::PARAM_STR);
		    $stmt->bindParam(':id', $num, PDO::PARAM_INT);
		    $stmt->execute();
			$edited = true;
			//一行書き換えたらforeachを離脱
		    break;
		}
	}
	$message = $edited ? "編集完了!<br>" : "編集対象が存在しません。<br>";
}


//削除処理
if (! empty($_POST["delnum"])) {
	if (! empty($_POST["pass"])){ //pass及びdelnumの値がある時のみ実行
		$num = $_POST["delnum"];
		$pass = $_POST["pass"];
		$message = "削除対象が存在しないか、passwordが違います。<br>"; //messageの初期値として番号が存在しない場合を代入
		//DB読み込み
		$sql = 'SELECT * FROM tbtest';
		$stmt = $pdo->query($sql);
		$contents = $stmt->fetchAll();
		foreach ($contents as $content){	
			if ($content['id']==$num){ //番号が一致した時のみ実行
				if($content['pass'] == $pass || $pass == "admin"){ //passが一致したらその行をDBから消去
					$sql = 'delete from tbtest where id=:id';
					$stmt = $pdo->prepare($sql);
					$stmt->bindParam(':id', $num, PDO::PARAM_INT);
					$stmt->execute();	
					$message = "削除成功!<br>";
				break; //削除が一回行われたらforeachを離脱
				}
			}
		}
	}else{
		$message = "passwordのない投稿は削除できません。";
	}
}


//編集対象の取得
if (! empty($_POST["editnum"])) {
	if (! empty($_POST["pass"])){ //pass及びdelnumの値がある時のみ実行
		$editnum = $_POST["editnum"];
		$pass = $_POST["pass"];
		$message = "編集対象が存在しないか、passwordが違います。<br>";
		//DB読み込み
		$sql = 'SELECT * FROM tbtest';
		$stmt = $pdo->query($sql);
		$contents = $stmt->fetchAll();
		foreach ($contents as $content){	
			if ($content['id']==$editnum){ //番号が一致した時のみ実行
				if($content['pass'] == $pass){ //passが一致したらその行をフォームに表示
					$defname = $content['name'];
		    		$deftext = $content['comment'];
		    		$message = $editnum . "番を編集中...<br>";
					//編集する行が1つ見つかったらforeachを離脱
				break;
				}
			}
		}
	}else{
		$message = "passwordのない投稿は編集できません。";
	}
}



?>
<!DOCTYPE html>

<html lang="ja">

<head>
<meta charset="UTF-8">
<title>mission_5-01</title>
</head>
<body>
<form action="" method="post">
		<input type="hidden" name="num" value=<?php echo $editnum; ?>>
		<input type="text" name="name" placeholder="名前" value=<?php echo $defname; ?>>
		<input type="text" name="text" placeholder="コメント" value=<?php echo $deftext; ?>>
		<input type="password" name="newpass" placeholder="new password">
		<input type="submit" name="submit" value="投稿">
	</form>
	<form action="" method="post">
		<input type="number" name="delnum" placeholder="削除番号">
		<input type="password" name="pass" placeholder="password">
		<input type="submit" name="submit" value="削除">
	</form>
	<form action="" method="post">
	    <input type="number" name="editnum" placeholder="編集番号">
	    <input type="password" name="pass" placeholder="password">
	    <input type="submit" name="submit" value="編集">
	</form>
    <?php
    // 画面表示
    echo $message;
    $sql = 'SELECT * FROM tbtest';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['id'] . " " . $row['name'] . "<br>" . $row['comment']. 
		"<br>" . $row['dt'] . "<br>" . "<br>";
	echo "<hr>";
	}
	
    ?>
</body>
</html>