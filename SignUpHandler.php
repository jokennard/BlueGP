<!DOCTYPE html>
<html>
	<body>
		<?php
			session_start();
			require("connect.php");
			$username = $_POST['username'];
			$imagePass = $_POST['imagePass'];
			$finishTime = time() - $_SESSION['startTime'];

			$arrSelected = explode(" ", trim($imagePass));

			$ctr = count($arrSelected);
			if($username == "") echo "Username tidak boleh kosong.";
			elseif(empty($arrSelected)) echo "Pilih gambar terlebih dahulu.";	
			elseif($ctr != 6 ) echo "Anda belum memilih 6 gambar.";
			else{
				// cek jika username sudah ada
				$res=$conn->query("SELECT username FROM user WHERE LOWER(username) = LOWER('$username')");
				$num = mysqli_affected_rows($conn);
				if($num) echo "Username sudah digunakan. Silahkan coba yang lain.";
				else{
					$res=$conn->query("INSERT INTO user VALUES ('$username')");	
					for($i = 0 ; $i < $ctr ; $i++){
						$res=$conn->query("INSERT INTO grouppass VALUES (NULL,'$username',".intval(substr($arrSelected[$i],0,3)).")");	
					}
					$num = mysqli_affected_rows($conn);

					if($num){
						echo "User Berhasil dibuat";
						$res=$conn->query("INSERT INTO testregister VALUES (NULL,'$username',CURDATE(),'$finishTime')");	
					}
					else echo "User Gagal dibuat";		
				}
			}
			echo "<br><a href='index.php'>LOGIN Page</a>";
		?>
	</body>
</html>