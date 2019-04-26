<?php
	session_start();
	session_unset();
?>
<!DOCTYPE html>
<html>
<center>

	<head>
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/grid.css" />
		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
		<meta charset="utf-8">
		<title>Sign Up</title>
	</head>

	<style>
		#image_pass {
			width: 500px;
			height: 100%;
			text-align: center;
			padding: 12px 20px;
			box-sizing: border-box;
			border: 2px solid #ccc;
			border-radius: 4px;
			background-color: #f8f8f8;
			resize: none;
		}
	</style>

	<body>
		<form method="POST" action="SignUpHandler.php">
			<div class="container">
				<div>
					<h1>BlueGP System</h1>
					<h3>Sign Up Page</h3>
				</div>

				<div class="form-group row">
					<div class="col-sm-2"></div>
					<label for="username_input" class="col-sm-2 col-form-label">Username:</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" name="username" id="username_input" value="">
					</div>
					<div class="col-sm-2"></div>
				</div>

				<input id="image_pass" type="password" name="imagePass" hidden></p>
				<p>Please pick exactly 6 images.</p>
				<div>Images picked:</div>
				<p id="limit">0</p>
				<div>
					<?php 
						require("connect.php");
						$res=$conn->query("SELECT name_image FROM image ORDER BY RAND() LIMIT 30");
		
						while($row = mysqli_fetch_assoc($res))$rows[]=$row["name_image"];
						
						$js_array = json_encode($rows);

						$_SESSION['startTime'] = time();
						
						echo "<script>
							var string = '';
							var limit = 0;
		
							var js_array = ".$js_array.";
							console.log(typeof(js_array[0]));
		
							var lastClicked, TextInsideLi;
							var grid = clickableGrid(5, 6, function (el, row, col, i, name) {
								console.log(\"You clicked on element:\", el);
								console.log(\"You clicked on row:\", row);
								console.log(\"You clicked on col:\", col);
								console.log(\"You clicked on item #:\", i);
								console.log(\"You clicked on image:\", name);

								el.className = 'clicked';

								// check if image is already selected
								TextInsideP = document.getElementById('image_pass').value;
								if (TextInsideP.indexOf(name) > 0);
								else {
									limit = +document.getElementById('limit').innerHTML;
									string = TextInsideP + ' ' + name;
									document.getElementById('image_pass').value = string;
									document.getElementById('limit').innerHTML = ++limit;
								}
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
		
										cell.style.backgroundImage = 'url(images/'+js_array[j]+')';
		
										cell.addEventListener('click', (function (el, r, c, i, name) {
											return function () {
												callback(el, r, c, i, name);
											}
										})(cell, r, c, i, js_array[j++]), false);
									}
								}
								return grid;
							}
						</script>";

						
					?>
				</div>
				
				<div>Click submit when you're done!</div>
				<button type="submit" name="submit" class="btn btn-primary btn-right pull-right">Submit</button>
			</div>
			
		</form>

		<script>
			function resetInput() {
				document.getElementById("image_pass").value = "";
				document.getElementById('limit').innerHTML = "0";

				var table = document.getElementById("tableImagePass");
				for (var i = 0, row; row = table.rows[i]; i++) {
					for (var j = 0, col; col = row.cells[j]; j++) {
						col.className = '';
					}  
				}
			}
		</script>
		<br>
		<button class="btn btn-secondary" onClick = "resetInput()">Redo Image Picking</button>

	</body>
</center>

</html>