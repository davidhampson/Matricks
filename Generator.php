<title> QGen </title>

<?php

include("Matrix.php");
include("MatrixException.php");
include("LUDecomposition.php");
use MCordingley\LinearAlgebra\Matrix;

function begin($identity, $o, $s, $r1, $r2, $da) {
	$operation = $o;
	$size = $s;
	$displayamount = $da;
	if ($r1 > $r2) {
		$upperBound = $r1;
		$lowerBound = $r2;
	} else {
		$lowerBound = $r1;
		$upperBound = $r2;
	}
	if (!isset($_POST["SUBMITTED"])) {
		$matrixarray = generateMatrices($size, $displayamount, $lowerBound, $upperBound);
	} else {
		$matrixarray = retrieveMatrices($size, $displayamount);
	}
	if ($operation == "add") {
		addition($identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	} else {
		multiplication($identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	}
}

function addition($identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		echo "<div style=\"float: left\">";
		displayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">+</div>";
		echo "<div style=\"float: left\">";
		displayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		displayAnswer($identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
	
}

function multiplication($identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		echo "<div style=\"float: left\">";
		displayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">X</div>";
		echo "<div style=\"float: left\">";
		displayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		displayAnswer($identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
}

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

function displayAnswer($identity, $k, $a, $b, $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray) {
	if (isset($_POST["SUBMITTED"])) {
		// SUBMISSION
		echo "<div style=\"float: left\">";
		echo "<form method=\"POST\" id=\"submission\">";
		for ($n = 0; $n < $displayamount; $n++) {
			for ($i = 0; $i < $matrixarray[0][0]->getRowCount(); $i++) {
				for ($j = 0; $j < $matrixarray[0][0]->getColumnCount(); $j++) {
					if ($operation == "add") {
						$solution = $matrixarray[$n][0]->addMatrix($matrixarray[$n][1]);
					} else {
						$solution = $matrixarray[$n][0]->multiplyMatrix($matrixarray[$n][1]);
					}
					
					$userAns = intval($_POST["userAns$n$i$j"]);
					$realAns = intval($_POST["ANS$n$i$j"]);
					if ($userAns == $realAns) {
						$colour = "green";
					} else {
						$colour = "red";
					}
					// pass values
					if ($k == $n) {
						echo "<input style=\"background-color: $colour; color: white;\" type=\"text\" size=\"1\" name=\"userAns$n$i$j\" value = \"$userAns\">";
					} else {
						echo "<input style=\"background-color: $colour; color: white;\" type=\"hidden\" size=\"1\" name=\"userAns$n$i$j\" value = \"$userAns\">";
					}
					
					// hidden values
					$arrayValuea = $matrixarray[$n][0]->get($i, $j);
					$arrayValueb = $matrixarray[$n][1]->get($i, $j);
					$sol = $solution->get($i, $j);
					echo "<input type=\"hidden\" name=\"ANS$n$i$j\" value=\"$sol\"/>";
					echo "<input type=\"hidden\" name=\"a$n$i$j\" value=\"$arrayValuea\"/>";
					echo "<input type=\"hidden\" name=\"b$n$i$j\" value=\"$arrayValueb\"/>";
				}
				echo "<br>";
			}
		}
		if ($identity === "Begin") {
			echo "<input type=\"hidden\" name=\"STUDENT\" value=\"$identity\"/>";
		} elseif ($identity === "Go") {
			echo "<input type=\"hidden\" name=\"GUEST\" value=\"$identity\"/>";
		} else {
			echo "<input type=\"hidden\" name=\"TEACHER\" value=\"$identity\"/>";
		}
		echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
		echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
		echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
		echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
		echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
		// submit/reset
		echo "<table><td>";
		echo "<input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div>";
		echo "</td><td><input type=\"submit\" value=\"Reset\" name=\"RESET\"></form></div></td></table>";
		echo "<div style=\"float: left\">";
		echo "<br style=\"clear: both\">";
	} else {
		// PRE-SUBMISSION
		echo "<div style=\"float: left\">";
		echo "<form method=\"POST\" id=\"submission\">";
		for ($n = 0; $n < $displayamount; $n++) {
			for ($i = 0; $i < $matrixarray[0][0]->getRowCount(); $i++) {
				for ($j = 0; $j < $matrixarray[0][0]->getColumnCount(); $j++) {
					if ($operation == "add") {
						$solution = $matrixarray[$n][0]->addMatrix($matrixarray[$n][1]);
					} else {
						$solution = $matrixarray[$n][0]->multiplyMatrix($matrixarray[$n][1]);
					}
					
					// pass values
					if ($k == $n) {
						echo "<input type=\"text\" size=\"1\" name=\"userAns$n$i$j\">";
					} else {
						echo "<input type=\"hidden\" size=\"1\" name=\"userAns$n$i$j\">";
					}
				
					// hidden values
					$arrayValuea = $matrixarray[$n][0]->get($i, $j);
					$arrayValueb = $matrixarray[$n][1]->get($i, $j);
					$sol = $solution->get($i, $j);
					echo "<input type=\"hidden\" name=\"ANS$n$i$j\" value=\"$sol\"/>";
					echo "<input type=\"hidden\" name=\"a$n$i$j\" value=\"$arrayValuea\"/>";
					echo "<input type=\"hidden\" name=\"b$n$i$j\" value=\"$arrayValueb\"/>";
				}
				echo "<br>";
			}
		}
		if ($identity === "Begin") {
			echo "<input type=\"hidden\" name=\"STUDENT\" value=\"$identity\"/>";
		} elseif ($identity === "Go") {
			echo "<input type=\"hidden\" name=\"GUEST\" value=\"$identity\"/>";
		} else {
			echo "<input type=\"hidden\" name=\"TEACHER\" value=\"$identity\"/>";
		}
		echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
		echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
		echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
		echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
		echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
		// submit button
		echo "<div style=\"text-align:center; margin: 5 0 0 0;\"><input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div>";
		echo "<br style=\"clear: both\">";
	}
	echo "</div></div>";
}

function generateMatrices($size, $displayamount, $lowerBound, $upperBound) {
	if ($size == "2x2") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "3x3") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "4x4") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "5x5") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "6x6") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "7x7") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "8x8") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "9x9") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	} elseif ($size == "10x10") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i] = array();
			for ($j = 0; $j < 2; $j++) {
				$matrixarray[$i][$j] = new Matrix([
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)],
					[rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound), rand($lowerBound,$upperBound)]
				]);
			}
		}
	}
	return $matrixarray;
}

function retrieveMatrices($size, $displayamount) {
	if ($size == "2x2") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"]]
			]);
		}
	} elseif ($size == "3x3") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"]]
			]);
		}
	} elseif ($size == "4x4") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"]]
			]);
		}
	} elseif ($size == "5x5") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"],$_POST["a" .$i. "04"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"],$_POST["a" .$i. "14"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"],$_POST["a" .$i. "24"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"],$_POST["a" .$i. "24"]],
				[$_POST["a" .$i. "40"],$_POST["a" .$i. "41"],$_POST["a" .$i. "42"],$_POST["a" .$i. "43"],$_POST["a" .$i. "44"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"],$_POST["b" .$i. "04"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"],$_POST["b" .$i. "14"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"],$_POST["b" .$i. "24"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"],$_POST["b" .$i. "24"]],
				[$_POST["b" .$i. "40"],$_POST["b" .$i. "41"],$_POST["b" .$i. "42"],$_POST["b" .$i. "43"],$_POST["b" .$i. "44"]]
			]);
		}
	} elseif ($size == "6x6") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"],$_POST["a" .$i. "04"],$_POST["a" .$i. "05"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"],$_POST["a" .$i. "14"],$_POST["a" .$i. "15"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"],$_POST["a" .$i. "24"],$_POST["a" .$i. "25"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"],$_POST["a" .$i. "24"],$_POST["a" .$i. "35"]],
				[$_POST["a" .$i. "40"],$_POST["a" .$i. "41"],$_POST["a" .$i. "42"],$_POST["a" .$i. "43"],$_POST["a" .$i. "44"],$_POST["a" .$i. "45"]],
				[$_POST["a" .$i. "50"],$_POST["a" .$i. "51"],$_POST["a" .$i. "52"],$_POST["a" .$i. "53"],$_POST["a" .$i. "54"],$_POST["a" .$i. "55"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"],$_POST["b" .$i. "04"],$_POST["b" .$i. "05"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"],$_POST["b" .$i. "14"],$_POST["b" .$i. "15"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"],$_POST["b" .$i. "24"],$_POST["b" .$i. "25"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"],$_POST["b" .$i. "24"],$_POST["b" .$i. "35"]],
				[$_POST["b" .$i. "40"],$_POST["b" .$i. "41"],$_POST["b" .$i. "42"],$_POST["b" .$i. "43"],$_POST["b" .$i. "44"],$_POST["b" .$i. "45"]],
				[$_POST["b" .$i. "50"],$_POST["b" .$i. "51"],$_POST["b" .$i. "52"],$_POST["b" .$i. "53"],$_POST["b" .$i. "54"],$_POST["b" .$i. "55"]]
			]);
		}
	} elseif ($size == "7x7") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"],$_POST["a" .$i. "04"],$_POST["a" .$i. "05"],$_POST["a" .$i. "06"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"],$_POST["a" .$i. "14"],$_POST["a" .$i. "15"],$_POST["a" .$i. "16"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"],$_POST["a" .$i. "24"],$_POST["a" .$i. "25"],$_POST["a" .$i. "26"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"],$_POST["a" .$i. "24"],$_POST["a" .$i. "35"],$_POST["a" .$i. "36"]],
				[$_POST["a" .$i. "40"],$_POST["a" .$i. "41"],$_POST["a" .$i. "42"],$_POST["a" .$i. "43"],$_POST["a" .$i. "44"],$_POST["a" .$i. "45"],$_POST["a" .$i. "46"]],
				[$_POST["a" .$i. "50"],$_POST["a" .$i. "51"],$_POST["a" .$i. "52"],$_POST["a" .$i. "53"],$_POST["a" .$i. "54"],$_POST["a" .$i. "55"],$_POST["a" .$i. "56"]],
				[$_POST["a" .$i. "60"],$_POST["a" .$i. "61"],$_POST["a" .$i. "62"],$_POST["a" .$i. "63"],$_POST["a" .$i. "64"],$_POST["a" .$i. "65"],$_POST["a" .$i. "66"]],
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"],$_POST["b" .$i. "04"],$_POST["b" .$i. "05"],$_POST["b" .$i. "06"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"],$_POST["b" .$i. "14"],$_POST["b" .$i. "15"],$_POST["b" .$i. "16"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"],$_POST["b" .$i. "24"],$_POST["b" .$i. "25"],$_POST["b" .$i. "26"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"],$_POST["b" .$i. "24"],$_POST["b" .$i. "35"],$_POST["b" .$i. "36"]],
				[$_POST["b" .$i. "40"],$_POST["b" .$i. "41"],$_POST["b" .$i. "42"],$_POST["b" .$i. "43"],$_POST["b" .$i. "44"],$_POST["b" .$i. "45"],$_POST["b" .$i. "46"]],
				[$_POST["b" .$i. "50"],$_POST["b" .$i. "51"],$_POST["b" .$i. "52"],$_POST["b" .$i. "53"],$_POST["b" .$i. "54"],$_POST["b" .$i. "55"],$_POST["b" .$i. "56"]],
				[$_POST["b" .$i. "60"],$_POST["b" .$i. "61"],$_POST["b" .$i. "62"],$_POST["b" .$i. "63"],$_POST["b" .$i. "64"],$_POST["b" .$i. "65"],$_POST["b" .$i. "66"]]
			]);
		}
	} elseif ($size == "8x8") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"],$_POST["a" .$i. "04"],$_POST["a" .$i. "05"],$_POST["a" .$i. "06"],$_POST["a" .$i. "07"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"],$_POST["a" .$i. "14"],$_POST["a" .$i. "15"],$_POST["a" .$i. "16"],$_POST["a" .$i. "17"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"],$_POST["a" .$i. "24"],$_POST["a" .$i. "25"],$_POST["a" .$i. "26"],$_POST["a" .$i. "27"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"],$_POST["a" .$i. "24"],$_POST["a" .$i. "35"],$_POST["a" .$i. "36"],$_POST["a" .$i. "37"]],
				[$_POST["a" .$i. "40"],$_POST["a" .$i. "41"],$_POST["a" .$i. "42"],$_POST["a" .$i. "43"],$_POST["a" .$i. "44"],$_POST["a" .$i. "45"],$_POST["a" .$i. "46"],$_POST["a" .$i. "47"]],
				[$_POST["a" .$i. "50"],$_POST["a" .$i. "51"],$_POST["a" .$i. "52"],$_POST["a" .$i. "53"],$_POST["a" .$i. "54"],$_POST["a" .$i. "55"],$_POST["a" .$i. "56"],$_POST["a" .$i. "57"]],
				[$_POST["a" .$i. "60"],$_POST["a" .$i. "61"],$_POST["a" .$i. "62"],$_POST["a" .$i. "63"],$_POST["a" .$i. "64"],$_POST["a" .$i. "65"],$_POST["a" .$i. "66"],$_POST["a" .$i. "67"]],
				[$_POST["a" .$i. "70"],$_POST["a" .$i. "71"],$_POST["a" .$i. "72"],$_POST["a" .$i. "73"],$_POST["a" .$i. "74"],$_POST["a" .$i. "75"],$_POST["a" .$i. "76"],$_POST["a" .$i. "77"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"],$_POST["b" .$i. "04"],$_POST["b" .$i. "05"],$_POST["b" .$i. "06"],$_POST["b" .$i. "07"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"],$_POST["b" .$i. "14"],$_POST["b" .$i. "15"],$_POST["b" .$i. "16"],$_POST["b" .$i. "17"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"],$_POST["b" .$i. "24"],$_POST["b" .$i. "25"],$_POST["b" .$i. "26"],$_POST["b" .$i. "27"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"],$_POST["b" .$i. "24"],$_POST["b" .$i. "35"],$_POST["b" .$i. "36"],$_POST["b" .$i. "37"]],
				[$_POST["b" .$i. "40"],$_POST["b" .$i. "41"],$_POST["b" .$i. "42"],$_POST["b" .$i. "43"],$_POST["b" .$i. "44"],$_POST["b" .$i. "45"],$_POST["b" .$i. "46"],$_POST["b" .$i. "47"]],
				[$_POST["b" .$i. "50"],$_POST["b" .$i. "51"],$_POST["b" .$i. "52"],$_POST["b" .$i. "53"],$_POST["b" .$i. "54"],$_POST["b" .$i. "55"],$_POST["b" .$i. "56"],$_POST["b" .$i. "57"]],
				[$_POST["b" .$i. "60"],$_POST["b" .$i. "61"],$_POST["b" .$i. "62"],$_POST["b" .$i. "63"],$_POST["b" .$i. "64"],$_POST["b" .$i. "65"],$_POST["b" .$i. "66"],$_POST["b" .$i. "67"]],
				[$_POST["b" .$i. "70"],$_POST["b" .$i. "71"],$_POST["b" .$i. "72"],$_POST["b" .$i. "73"],$_POST["b" .$i. "74"],$_POST["b" .$i. "75"],$_POST["b" .$i. "76"],$_POST["b" .$i. "77"]]
			]);
		}
	} elseif ($size == "9x9") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"],$_POST["a" .$i. "04"],$_POST["a" .$i. "05"],$_POST["a" .$i. "06"],$_POST["a" .$i. "07"],$_POST["a" .$i. "08"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"],$_POST["a" .$i. "14"],$_POST["a" .$i. "15"],$_POST["a" .$i. "16"],$_POST["a" .$i. "17"],$_POST["a" .$i. "18"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"],$_POST["a" .$i. "24"],$_POST["a" .$i. "25"],$_POST["a" .$i. "26"],$_POST["a" .$i. "27"],$_POST["a" .$i. "28"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"],$_POST["a" .$i. "24"],$_POST["a" .$i. "35"],$_POST["a" .$i. "36"],$_POST["a" .$i. "37"],$_POST["a" .$i. "38"]],
				[$_POST["a" .$i. "40"],$_POST["a" .$i. "41"],$_POST["a" .$i. "42"],$_POST["a" .$i. "43"],$_POST["a" .$i. "44"],$_POST["a" .$i. "45"],$_POST["a" .$i. "46"],$_POST["a" .$i. "47"],$_POST["a" .$i. "48"]],
				[$_POST["a" .$i. "50"],$_POST["a" .$i. "51"],$_POST["a" .$i. "52"],$_POST["a" .$i. "53"],$_POST["a" .$i. "54"],$_POST["a" .$i. "55"],$_POST["a" .$i. "56"],$_POST["a" .$i. "57"],$_POST["a" .$i. "58"]],
				[$_POST["a" .$i. "60"],$_POST["a" .$i. "61"],$_POST["a" .$i. "62"],$_POST["a" .$i. "63"],$_POST["a" .$i. "64"],$_POST["a" .$i. "65"],$_POST["a" .$i. "66"],$_POST["a" .$i. "67"],$_POST["a" .$i. "68"]],
				[$_POST["a" .$i. "70"],$_POST["a" .$i. "71"],$_POST["a" .$i. "72"],$_POST["a" .$i. "73"],$_POST["a" .$i. "74"],$_POST["a" .$i. "75"],$_POST["a" .$i. "76"],$_POST["a" .$i. "77"],$_POST["a" .$i. "78"]],
				[$_POST["a" .$i. "80"],$_POST["a" .$i. "81"],$_POST["a" .$i. "82"],$_POST["a" .$i. "83"],$_POST["a" .$i. "84"],$_POST["a" .$i. "85"],$_POST["a" .$i. "86"],$_POST["a" .$i. "87"],$_POST["a" .$i. "88"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"],$_POST["b" .$i. "04"],$_POST["b" .$i. "05"],$_POST["b" .$i. "06"],$_POST["b" .$i. "07"],$_POST["b" .$i. "08"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"],$_POST["b" .$i. "14"],$_POST["b" .$i. "15"],$_POST["b" .$i. "16"],$_POST["b" .$i. "17"],$_POST["b" .$i. "18"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"],$_POST["b" .$i. "24"],$_POST["b" .$i. "25"],$_POST["b" .$i. "26"],$_POST["b" .$i. "27"],$_POST["b" .$i. "28"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"],$_POST["b" .$i. "24"],$_POST["b" .$i. "35"],$_POST["b" .$i. "36"],$_POST["b" .$i. "37"],$_POST["b" .$i. "38"]],
				[$_POST["b" .$i. "40"],$_POST["b" .$i. "41"],$_POST["b" .$i. "42"],$_POST["b" .$i. "43"],$_POST["b" .$i. "44"],$_POST["b" .$i. "45"],$_POST["b" .$i. "46"],$_POST["b" .$i. "47"],$_POST["b" .$i. "48"]],
				[$_POST["b" .$i. "50"],$_POST["b" .$i. "51"],$_POST["b" .$i. "52"],$_POST["b" .$i. "53"],$_POST["b" .$i. "54"],$_POST["b" .$i. "55"],$_POST["b" .$i. "56"],$_POST["b" .$i. "57"],$_POST["b" .$i. "58"]],
				[$_POST["b" .$i. "60"],$_POST["b" .$i. "61"],$_POST["b" .$i. "62"],$_POST["b" .$i. "63"],$_POST["b" .$i. "64"],$_POST["b" .$i. "65"],$_POST["b" .$i. "66"],$_POST["b" .$i. "67"],$_POST["b" .$i. "68"]],
				[$_POST["b" .$i. "70"],$_POST["b" .$i. "71"],$_POST["b" .$i. "72"],$_POST["b" .$i. "73"],$_POST["b" .$i. "74"],$_POST["b" .$i. "75"],$_POST["b" .$i. "76"],$_POST["b" .$i. "77"],$_POST["b" .$i. "78"]],
				[$_POST["b" .$i. "80"],$_POST["b" .$i. "81"],$_POST["b" .$i. "82"],$_POST["b" .$i. "83"],$_POST["b" .$i. "84"],$_POST["b" .$i. "85"],$_POST["b" .$i. "86"],$_POST["b" .$i. "87"],$_POST["b" .$i. "88"]]
			]);
		}
	} elseif ($size == "10x10") {
		for ($i = 0; $i < $displayamount; $i++) {
			$matrixarray[$i][0] = new Matrix([
				[$_POST["a" .$i. "00"],$_POST["a" .$i. "01"],$_POST["a" .$i. "02"],$_POST["a" .$i. "03"],$_POST["a" .$i. "04"],$_POST["a" .$i. "05"],$_POST["a" .$i. "06"],$_POST["a" .$i. "07"],$_POST["a" .$i. "08"],$_POST["a" .$i. "09"]],
				[$_POST["a" .$i. "10"],$_POST["a" .$i. "11"],$_POST["a" .$i. "12"],$_POST["a" .$i. "13"],$_POST["a" .$i. "14"],$_POST["a" .$i. "15"],$_POST["a" .$i. "16"],$_POST["a" .$i. "17"],$_POST["a" .$i. "18"],$_POST["a" .$i. "19"]],
				[$_POST["a" .$i. "20"],$_POST["a" .$i. "21"],$_POST["a" .$i. "22"],$_POST["a" .$i. "23"],$_POST["a" .$i. "24"],$_POST["a" .$i. "25"],$_POST["a" .$i. "26"],$_POST["a" .$i. "27"],$_POST["a" .$i. "28"],$_POST["a" .$i. "29"]],
				[$_POST["a" .$i. "30"],$_POST["a" .$i. "31"],$_POST["a" .$i. "32"],$_POST["a" .$i. "33"],$_POST["a" .$i. "24"],$_POST["a" .$i. "35"],$_POST["a" .$i. "36"],$_POST["a" .$i. "37"],$_POST["a" .$i. "38"],$_POST["a" .$i. "39"]],
				[$_POST["a" .$i. "40"],$_POST["a" .$i. "41"],$_POST["a" .$i. "42"],$_POST["a" .$i. "43"],$_POST["a" .$i. "44"],$_POST["a" .$i. "45"],$_POST["a" .$i. "46"],$_POST["a" .$i. "47"],$_POST["a" .$i. "48"],$_POST["a" .$i. "49"]],
				[$_POST["a" .$i. "50"],$_POST["a" .$i. "51"],$_POST["a" .$i. "52"],$_POST["a" .$i. "53"],$_POST["a" .$i. "54"],$_POST["a" .$i. "55"],$_POST["a" .$i. "56"],$_POST["a" .$i. "57"],$_POST["a" .$i. "58"],$_POST["a" .$i. "59"]],
				[$_POST["a" .$i. "60"],$_POST["a" .$i. "61"],$_POST["a" .$i. "62"],$_POST["a" .$i. "63"],$_POST["a" .$i. "64"],$_POST["a" .$i. "65"],$_POST["a" .$i. "66"],$_POST["a" .$i. "67"],$_POST["a" .$i. "68"],$_POST["a" .$i. "69"]],
				[$_POST["a" .$i. "70"],$_POST["a" .$i. "71"],$_POST["a" .$i. "72"],$_POST["a" .$i. "73"],$_POST["a" .$i. "74"],$_POST["a" .$i. "75"],$_POST["a" .$i. "76"],$_POST["a" .$i. "77"],$_POST["a" .$i. "78"],$_POST["a" .$i. "79"]],
				[$_POST["a" .$i. "80"],$_POST["a" .$i. "81"],$_POST["a" .$i. "82"],$_POST["a" .$i. "83"],$_POST["a" .$i. "84"],$_POST["a" .$i. "85"],$_POST["a" .$i. "86"],$_POST["a" .$i. "87"],$_POST["a" .$i. "88"],$_POST["a" .$i. "89"]],
				[$_POST["a" .$i. "90"],$_POST["a" .$i. "91"],$_POST["a" .$i. "92"],$_POST["a" .$i. "93"],$_POST["a" .$i. "94"],$_POST["a" .$i. "95"],$_POST["a" .$i. "96"],$_POST["a" .$i. "97"],$_POST["a" .$i. "98"],$_POST["a" .$i. "99"]]
			]);
			$matrixarray[$i][1] = new Matrix([
				[$_POST["b" .$i. "00"],$_POST["b" .$i. "01"],$_POST["b" .$i. "02"],$_POST["b" .$i. "03"],$_POST["b" .$i. "04"],$_POST["b" .$i. "05"],$_POST["b" .$i. "06"],$_POST["b" .$i. "07"],$_POST["b" .$i. "08"],$_POST["b" .$i. "09"]],
				[$_POST["b" .$i. "10"],$_POST["b" .$i. "11"],$_POST["b" .$i. "12"],$_POST["b" .$i. "13"],$_POST["b" .$i. "14"],$_POST["b" .$i. "15"],$_POST["b" .$i. "16"],$_POST["b" .$i. "17"],$_POST["b" .$i. "18"],$_POST["b" .$i. "19"]],
				[$_POST["b" .$i. "20"],$_POST["b" .$i. "21"],$_POST["b" .$i. "22"],$_POST["b" .$i. "23"],$_POST["b" .$i. "24"],$_POST["b" .$i. "25"],$_POST["b" .$i. "26"],$_POST["b" .$i. "27"],$_POST["b" .$i. "28"],$_POST["b" .$i. "29"]],
				[$_POST["b" .$i. "30"],$_POST["b" .$i. "31"],$_POST["b" .$i. "32"],$_POST["b" .$i. "33"],$_POST["b" .$i. "24"],$_POST["b" .$i. "35"],$_POST["b" .$i. "36"],$_POST["b" .$i. "37"],$_POST["b" .$i. "38"],$_POST["b" .$i. "39"]],
				[$_POST["b" .$i. "40"],$_POST["b" .$i. "41"],$_POST["b" .$i. "42"],$_POST["b" .$i. "43"],$_POST["b" .$i. "44"],$_POST["b" .$i. "45"],$_POST["b" .$i. "46"],$_POST["b" .$i. "47"],$_POST["b" .$i. "48"],$_POST["b" .$i. "49"]],
				[$_POST["b" .$i. "50"],$_POST["b" .$i. "51"],$_POST["b" .$i. "52"],$_POST["b" .$i. "53"],$_POST["b" .$i. "54"],$_POST["b" .$i. "55"],$_POST["b" .$i. "56"],$_POST["b" .$i. "57"],$_POST["b" .$i. "58"],$_POST["b" .$i. "59"]],
				[$_POST["b" .$i. "60"],$_POST["b" .$i. "61"],$_POST["b" .$i. "62"],$_POST["b" .$i. "63"],$_POST["b" .$i. "64"],$_POST["b" .$i. "65"],$_POST["b" .$i. "66"],$_POST["b" .$i. "67"],$_POST["b" .$i. "68"],$_POST["b" .$i. "69"]],
				[$_POST["b" .$i. "70"],$_POST["b" .$i. "71"],$_POST["b" .$i. "72"],$_POST["b" .$i. "73"],$_POST["b" .$i. "74"],$_POST["b" .$i. "75"],$_POST["b" .$i. "76"],$_POST["b" .$i. "77"],$_POST["b" .$i. "78"],$_POST["b" .$i. "79"]],
				[$_POST["b" .$i. "80"],$_POST["b" .$i. "81"],$_POST["b" .$i. "82"],$_POST["b" .$i. "83"],$_POST["b" .$i. "84"],$_POST["b" .$i. "85"],$_POST["b" .$i. "86"],$_POST["b" .$i. "87"],$_POST["b" .$i. "88"],$_POST["b" .$i. "89"]],
				[$_POST["b" .$i. "90"],$_POST["b" .$i. "91"],$_POST["b" .$i. "92"],$_POST["b" .$i. "93"],$_POST["b" .$i. "94"],$_POST["b" .$i. "95"],$_POST["b" .$i. "96"],$_POST["b" .$i. "97"],$_POST["b" .$i. "98"],$_POST["b" .$i. "99"]]
			]);
		}
	}
	return $matrixarray;
}

if (isset($_POST["STUDENT"]) && isset($_POST["SUBMITTED"])){
	$ansCount = 0;
	$operation = $_POST["operations"];
	$displayamount = $_POST["displayamount"];
	$size = $_POST["msize"];
	$r1 = $_POST["range1"];
	$r2 = $_POST["range2"];
	if ($r1 > $r2) {
		$upperBound = $r1;
		$lowerBound = $r2;
	} else {
		$lowerBound = $r1;
		$upperBound = $r2;
	}
	$matrixarray = retrieveMatrices($size, $displayamount);
	for ($n = 0; $n < $displayamount; $n++) {
		for ($i = 0; $i < $matrixarray[0][0]->getRowCount(); $i++) {
			for ($j = 0; $j < $matrixarray[0][0]->getColumnCount(); $j++) {
				if ($operation == "add") {
					$solution = $matrixarray[$n][0]->addMatrix($matrixarray[$n][1]);
				} else {
					$solution = $matrixarray[$n][0]->multiplyMatrix($matrixarray[$n][1]);
				}
				$userAns = intval($_POST["userAns$n$i$j"]);
				$realAns = intval($_POST["ANS$n$i$j"]);
				if ($userAns == $realAns) {
					$colour = "green";
					$ansCount++;
				} else {
					$colour = "red";
				}
			}
		}
	}
	echo "<h1>$ansCount/$displayamount</h1>";
} else {
	if (!empty($_POST["STUDENT"])) {
		begin($_POST["STUDENT"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"]);
	} elseif (!empty($_POST["TEACHER"])){
		begin($_POST["TEACHER"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"]);
	} else {
		begin($_POST["GUEST"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"]);
	}
}

?>