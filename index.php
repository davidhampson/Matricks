<html>
<head>
<!-- HEAD -->
</head>
<body>

<h1> 3x3 Matrix Addition </h1>

<?php

include("Matrix.php");
include("MatrixException.php");
include("LUDecomposition.php");
use MCordingley\LinearAlgebra\Matrix;

$lowerBound = 0;
$upperBound = 9;

if (!isset($_GET["SUBMITTED"])) {
    $matrix1 = new Matrix([
        [rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
        [rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
        [rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
    ]);
    $matrix2 = new Matrix([
        [rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
        [rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
        [rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
    ]);
} /*else {
    $matrix1 = new Matrix([
        $matrix1->get(0, 0), $matrix1->get(0, 1), $matrix1->get(0, 2),
        $matrix1->get(1, 0), $matrix1->get(1, 1), $matrix1->get(1, 2),
        $matrix1->get(2, 0), $matrix1->get(2, 1), $matrix1->get(2, 2)
    ]);
    $matrix2 = new Matrix([
        $matrix2->get(0, 0), $matrix2->get(0, 1), $matrix2->get(0, 2),
        $matrix2->get(1, 0), $matrix2->get(1, 1), $matrix2->get(1, 2),
        $matrix2->get(2, 0), $matrix2->get(2, 1), $matrix2->get(2, 2)
    ]);
}*/

function displayMatrix($m) {
	echo "<table style=\"border: 1px solid black; font-family:Consolas; line-height:25px;\"><tr>";
	for ($i = 0; $i < $m->getColumnCount(); $i++) {
		for ($j = 0; $j < $m->getRowCount(); $j++) {
			$arrayValue = $m->get($i, $j);
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
	$sol = $a->addMatrix($b);

	if (isset($_GET["SUBMITTED"])) {
		// SUBMISSION
		echo "<div style=\"float: left\">";
		echo "<form action=\"index.php\">";
		for ($i = 0; $i < $a->getColumnCount(); $i++) {
			for ($j = 0; $j < $a->getRowCount(); $j++) {				
				
				$oldAns = intval($_GET["$i$j"]);
				$solution = intval($_GET["ANS$i$j"]);
				$arrayValuea = $a->get($i, $j);
				$arrayValueb = $b->get($i, $j);

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
		for ($i = 0; $i < $a->getColumnCount(); $i++) {
			for ($j = 0; $j < $a->getRowCount(); $j++) {
				$solution = $sol[$i][$j];
				$arrayValuea = $a->get($i, $j);
				$arrayValueb = $b->get($i, $j);

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

question($matrix1, "+", $matrix2);

?>
<hr>
</body>
</html>