<?php 
	header("Cache-Control: no-store, must-revalidate, max-age=0");
	header("Pragma: no-cache");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	
	session_start();
	
	require("connect.php");
	if(isset($_POST['userName'])){
		$username = $_POST['userName'];
		$chesspass = $_POST['chessPass'];
		$_SESSION['username'] = $username;
	}
	else $username = 'N/A';
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/grid.css" />
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
	</head>
	<body>
		<center>
			<?php
				// Check if username exists
				$query0 = "SELECT username FROM user WHERE username = '$username'";
				$res=$conn->query($query0);
				$num = mysqli_affected_rows($conn);
				if($num == 0){
					echo "Username not found! Please <a href='SignUpPage.php'>Sign Up</a> first.";
					exit();
				}
			?>
			<div>
				<h1>BlueGP System</h1>
				<h3>Input Image Password Page</h3>
			</div>
			<form method="POST" action="ImagePassInputHandler.php">
				<input type="hidden" id="inputLen" name="input_len" readOnly><br> <!-- jumlah inputan -->
				<input type="hidden" id="input_arr" name="input_arr"><br> <!-- array isi koordinat inputan -->

				<?php 
					// Check if there are marked piece
					$markedPiece = '';
					$chesspassArr = str_split((string)$chesspass);
					$found = array_search('0', $chesspassArr);
					if ($found){
						$markedPiece = $chesspassArr[$found-1];
						array_splice($chesspassArr, $found, 1);
					}

					// Get all Images that corresponds with the username
					$query1 = "SELECT name_image FROM image, grouppass WHERE username = '$username' AND imagePass = id_image ORDER BY RAND()";
					$res=$conn->query($query1);
					while($row = mysqli_fetch_assoc($res)) $pickedImages[]=$row["name_image"];

					// Pick 47 decoy images including 2 of the picked 6 image passwords
					$query2 = "SELECT * FROM (SELECT name_image FROM image WHERE name_image NOT IN ('".implode("','",$pickedImages)."') ORDER BY RAND() LIMIT 47) AS a 
						UNION SELECT * FROM ($query1 LIMIT 2) AS b ORDER BY RAND()";
					$res=$conn->query($query2);
					while($row = mysqli_fetch_assoc($res)) $rows[]=$row["name_image"];

					// Locate position of user's image password
					$a = array($pickedImages);
					$index = 0;
					for($i=0; $i<6; $i++){
						$found = array_search($pickedImages[$i], $rows);
						if (++$found){
							$rowPos = (int) ceil($found/7);
							$colPos = $found%7;
							if ($colPos == 0) $colPos = 7;
							$passCoordinate[$index]["row"] = $rowPos;
							$passCoordinate[$index++]["column"] = $colPos;
						}
						if ($index == 2) break;
					}
				
					$images_grid = json_encode($rows);

					$_SESSION['startTime'] = time();
					$_SESSION['instruction'] = json_encode($chesspassArr);
					$_SESSION['marked'] = $markedPiece;
					$_SESSION['true_image_pos'] = json_encode($passCoordinate);

					// Show Grid
					echo "<script>
						var inputLen = 0;
						var input_array = [];
	
						var images_grid = ".$images_grid.";
	
						var lastClicked, TextInsideLi;
						var grid = clickableGrid(7, 7, function (el, row, col, i, name) {
							console.log('You clicked on element:', el);
							console.log('You clicked on row:', row);
							console.log('You clicked on col:', col);
							console.log('You clicked on item #:', i);
							console.log('You clicked on image:', name);

							el.className = 'clicked';

							inputLen = +document.getElementById('inputLen').value;
							if (inputLen == 0) input_array.length = 0;
							input_array.push({row : ++row, column : ++col});
							document.getElementById('inputLen').value = ++inputLen;
							document.getElementById('input_arr').value = JSON.stringify(input_array);
						});
	
						document.body.appendChild(grid);
	
						function clickableGrid(rows, cols, callback) {
							var i = 0, j=0;
							var grid = document.createElement('table');
							grid.id = 'tableImagePass';
							grid.className = 'grid';
							for (var r = 0; r < rows; ++r) {
								var tr = grid.appendChild(document.createElement('tr'));
								for (var c = 0; c < cols; ++c) {
									var cell = tr.appendChild(document.createElement('td'));
									i = (r+1) * 10 + (c+1);
	
									cell.style.backgroundImage = 'url(images/'+images_grid[j]+')';
	
									cell.addEventListener('click', (function (el, r, c, i, name) {
										return function () {
											callback(el, r, c, i, name);
										}
									})(cell, r, c, i, images_grid[j++]), false);
								}
							}
							return grid;
						}
					</script>";
				?>
				<button type="submit" name="submit" class="btn btn-primary btn-right pull-right">Submit</button>
			</form>

			<script>
			function resetInput() {
				document.getElementById('inputLen').value = "";
				document.getElementById('input_arr').value = "";

				var table = document.getElementById("tableImagePass");
				for (var i = 0, row; row = table.rows[i]; i++) {
					for (var j = 0, col; col = row.cells[j]; j++) {
						col.className = '';
					}  
				}
			}
			</script>
			<br>
			<button onClick = "resetInput()">Redo Input</button>
		</center>
	</body>
</html>