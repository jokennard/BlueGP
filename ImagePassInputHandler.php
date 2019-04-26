<!DOCTYPE html>
<html>
	<body>
		<?php
			session_start();
			require('connect.php');

			const BISHOP = 1;
			const KNIGHT = 2;
			const ROOK   = 3;
			const QUEEN  = 4;

			$username = $_SESSION['username'];
			$marked = $_SESSION['marked'] ?: 0;
			$inputLength = $_POST['input_len']; 
			$inputArray = json_decode($_POST['input_arr'], true);  // Koordinat input password dari user
			$trueImagePos = json_decode($_SESSION['true_image_pos'], true); // Koordinat gambar password yang sebenarnya
			$instruction = json_decode($_SESSION['instruction'], true);
			$finishTime = time() - $_SESSION['startTime'];
			
			if ($marked) array_push($instruction, $marked);
			
			$instructionLength = count($instruction);

			// cek apakah ada mark
			if ($marked == 0) $method = 'Direct';
			else $method = 'Pointer';

			// jika jumlah input sama instruction beda, berarti salah.
			if ($marked == 0 && ($inputLength != $instructionLength)){
				echo "<h1>Password salah.</h1>";

				$res=$conn->query("INSERT INTO testlogin VALUES (NULL,'$method','$username',CURDATE(),'$finishTime','failure')");
				$num = mysqli_affected_rows($conn);
				if($num > 0) echo "Test result has been recorded.";
				echo "<br><a href='index.php'>LOGIN Page</a>";

				exit();
			}
			else if ($marked > 0 && ($inputLength+1 != $instructionLength)){
				echo "<h1>Password salah.</h1>";

				$res=$conn->query("INSERT INTO testlogin VALUES (NULL,'$method','$username',CURDATE(),'$finishTime','failure')");
				$num = mysqli_affected_rows($conn);
				if($num > 0) echo "Test result has been recorded.";
				echo "<br><a href='index.php'>LOGIN Page</a>";

				exit();
			}

			// deklarasi variabel boolean
			$correct1 = true;
			$correct2 = true;

			// Cek apakah posisi input sesuai dengan instruksi
			// jika tak ada mark, trueImagePos tujuan harus ada di posisi akhir
			if (end($inputArray) == $trueImagePos[0]){
				$point1 = array($trueImagePos[1]);
				$point2 = array($trueImagePos[0]);
			}
			else if (end($inputArray) == $trueImagePos[1] || $marked > 0){
				$point1 = array($trueImagePos[0]);
				$point2 = array($trueImagePos[1]);
			}
			else $correct1 = false;

			// BEGIN CHECKING
			// cek jika jumlah input password salah (correct1 == false) maka correct2 juga pasti false.
			// cek jika direct approach (marked == 0), correct2 pasti false. jika pointer approach (marked > 0), correct2 mungkin true
			if ($correct1 == true){
				// buat array untuk menyimpal value baru correct1 dan correct2
				$correct = array(true, true);
				for($iteration = 0; $iteration < 2; $marked?$iteration++:$iteration+=2){
					$arrayFinal = array_merge($point1, $inputArray, $point2);

					for ($i=1; $i<=$instructionLength; $i++){
						if ($instruction[$i-1] == BISHOP){
							if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) == abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column'])){
								// echo "bishop confirmed<br>";
							}
							else{
								$correct[$iteration] = false;
							}
						}
						// check if movement is knight
						else if ($instruction[$i-1] == KNIGHT){
							if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) == 1 && abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column']) == 2){
								// echo "knight confirmed<br>";
							}
							else if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) == 2 && abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column']) == 1){
								// echo "knight confirmed<br>";
							}
							else{
								$correct[$iteration] = false;
							}
						}
						// check if movement is rook
						else if ($instruction[$i-1] == ROOK){
							if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) == 0 && abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column']) > 0){
								// echo "rook confirmed<br>";
							}
							else if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) > 0 && abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column']) == 0){
								// echo "rook confirmed<br>";
							}
							else{
								$correct[$iteration] = false;
							}
						}
						// check if movement is queen
						else if ($instruction[$i-1] == QUEEN){
							if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) == abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column'])){
								// echo "queen confirmed<br>";
							}
							else if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) == 0 && abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column']) > 0){
								// echo "queen confirmed<br>";
							}
							else if (abs($arrayFinal[$i-1]['row']-$arrayFinal[$i]['row']) > 0 && abs($arrayFinal[$i-1]['column'] - $arrayFinal[$i]['column']) == 0){
								// echo "queen confirmed<br>";
							}
							else{
								$correct[$iteration] = false;
							}
						}
						if ($correct[$iteration] == false){
							break;
						}
					}
					if (!$marked) $correct[1] = false;
					else{
						// jika marked > 0, tukar value point1 dan point2
						$tmp = $point1;
						$point1 = $point2;
						$point2 = $tmp;
					}
				}
				$correct1 = $correct[0];
				$correct2 = $correct[1];
			}
			else $correct2 = false;

			if ($correct1 == true || $correct2 == true){
				$status = 'success';
				echo "<h1>Password benar.</h1>";
			}
			else {
				$status = 'failure';
				echo "<h1>Password salah.</h1>";
			}

			$res=$conn->query("INSERT INTO testlogin VALUES (NULL,'$method','$username',CURDATE(),'$finishTime','$status')");
			$num = mysqli_affected_rows($conn);
			if($num > 0) echo "Test result has been recorded.";
			echo "<br><a href='index.php'>LOGIN Page</a>";
		?>
	</body>
</html>