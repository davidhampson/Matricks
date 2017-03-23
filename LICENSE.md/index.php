<html>
<head>
<!-- HEAD -->
</head>
<body>

<h1> 3x3 Matrix Addition </h1>

<?php

/*
include('Matrix.php');

use Matrix;

$matrix1 = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);
$matrix2 = new Matrix([
    [4, 2, 6],
    [1, 7, 3],
    [7, 3, 2],
]);

*/

$lowerBound = 0;
$upperBound = 9;

if (!isset($_GET["SUBMITTED"])) {
	$matrix1 = array
	(
		array(rand($lowerBound,$upperBound),rand($lowerBound,$upperBound),rand($lowerBound,$upperBound)),
		array(rand($lowerBound,$upperBound),rand($lowerBound,$upperBound),rand($lowerBound,$upperBound)),
		array(rand($lowerBound,$upperBound),rand($lowerBound,$upperBound),rand($lowerBound,$upperBound))
	);

	$matrix2 = array
	(
		array(rand($lowerBound,$upperBound),rand($lowerBound,$upperBound),rand($lowerBound,$upperBound)),
		array(rand($lowerBound,$upperBound),rand($lowerBound,$upperBound),rand($lowerBound,$upperBound)),
		array(rand($lowerBound,$upperBound),rand($lowerBound,$upperBound),rand($lowerBound,$upperBound))
	);
} else {
	$matrix1 = array(
		array($_GET["a00"],$_GET["a01"],$_GET["a02"]),
		array($_GET["a10"],$_GET["a11"],$_GET["a12"]),
		array($_GET["a20"],$_GET["a21"],$_GET["a22"])
	);

	$matrix2 = array(
		array($_GET["b00"],$_GET["b01"],$_GET["b02"]),
		array($_GET["b10"],$_GET["b11"],$_GET["b12"]),
		array($_GET["b20"],$_GET["b21"],$_GET["b22"])
	);
}


function displayMatrix($m) {
	echo "<table style=\"border: 1px solid black; font-family:Consolas; line-height:25px;\"><tr>";
	for ($i = 0; $i < count($m); $i++) {
		for ($j = 0; $j < 3; $j++) {
			$arrayValue = $[$i][$j];
			echo "<td style=\"padding: 0 13 0 13\">$arrayValue</td>";
		}
		echo "</tr><tr>";
	}
	echo "</tr></table>";
}

function question($a, $operation, $b) {

	// MATRIX A
	echo "<div style=\"float: left\">";
	displayMatrix($a);
	echo "</div>";
	
	// OPERATION
	echo "<div style=\"float: left; padding: 20px;\">$operation</div>";
	
	// MATRIX B
	echo "<div style=\"float: left\">";
	displayMatrix($b);
	echo "</div>";

	// EQUALS
	echo "<div style=\"float: left; padding: 20px;\">=</div>";

	// CALCULATE CORRECT RESULT
	$sol = array(
		array($a[0][0]+$b[0][0], $a[0][1]+$b[0][1], $a[0][2]+$b[0][2]),
		array($a[1][0]+$b[1][0], $a[1][1]+$b[1][1], $a[1][2]+$b[1][2]),
		array($a[2][0]+$b[2][0], $a[2][1]+$b[2][1], $a[2][2]+$b[2][2])
	);

	if (isset($_GET["SUBMITTED"])) {
		// SUBMISSION
		echo "<div style=\"float: left\">";
		echo "<form action=\"index.php\">";
		for ($i = 0; $i < count($a); $i++) {
			for ($j = 0; $j < 3; $j++) {				
				
				$oldAns = intval($_GET["$i$j"]);
				$solution = intval($_GET["ANS$i$j"]);
				$arrayValuea = $a[$i][$j];
				$arrayValueb = $b[$i][$j];

				if ($oldAns == $solution) {
					$colour = "green";
				} else {
					$colour = "red";
				}

				// pass values
				echo "<input style=\"background-color: $colour; color: white;\" type=\"text\" size=\"1\" name=\"$i$j\" value = \"$oldAns\">";
				
				// hidden values
				echo "<input type=\"hidden\" name=\"ANS$i$j\" value=\"$solution\"/>";
				echo "<input type=\"hidden\" name=\"a$i$j\" value=\"$arrayValuea\"/>";	
				echo "<input type=\"hidden\" name=\"b$i$j\" value=\"$arrayValueb\"/>";	
			}
			echo "<br>";
		}

		// submit/reset
		echo "<table><td>";
		echo "<input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div>";
		echo "</td><td><input type=\"submit\" value=\"Reset\" name=\"RESET\"></form></div></td></table>";
		echo "<div style=\"float: left\">";
		echo "<br style=\"clear: both\">";

	} else {
		// SUBMISSION
		echo "<div style=\"float: left\">";
		echo "<form action=\"index.php\">";
		for ($i = 0; $i < count($a); $i++) {
			for ($j = 0; $j < 3; $j++) {
				$solution = $sol[$i][$j];
				$arrayValuea = $a[$i][$j];
				$arrayValueb = $b[$i][$j];

				// pass values
				echo "<input type=\"text\" size=\"1\" name=\"$i$j\">";
				
				// hidden values
				echo "<input type=\"hidden\" name=\"ANS$i$j\" value=\"$solution\"/>";	
				echo "<input type=\"hidden\" name=\"a$i$j\" value=\"$arrayValuea\"/>";	
				echo "<input type=\"hidden\" name=\"b$i$j\" value=\"$arrayValueb\"/>";
			}
			echo "<br>";
		}


		// submit button
		echo "<div style=\"text-align:center; margin: 5 0 0 0;\"><input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div></div>";
		echo "<br style=\"clear: both\">";
	}

}

question($matrix1,"+",$matrix2);
?>
<hr>
</body>
</html>