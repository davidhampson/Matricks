<title> QGen </title>

<?php

include("Matrix.php");
include("MatrixException.php");
include("LUDecomposition.php");
use MCordingley\LinearAlgebra\Matrix;

function Initiate($teachname, $classname, $identity, $operation, $size, $r1, $r2, $displayamount) {
	if ($r1 > $r2) {
		$upperBound = $r1;
		$lowerBound = $r2;
	} else {
		$lowerBound = $r1;
		$upperBound = $r2;
	}
	
	if (!isset($_POST["SUBMITTED"])) {
		$matrixarray = GenerateMatrices($size, $displayamount, $lowerBound, $upperBound);
	} else {
		$matrixarray = RetrieveMatrices($size, $displayamount);
	}
	if ($identity === "Initiate" && !isset($_POST["SUBMITTED"])) {
		$uniqueid = MySQLInit($teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	}
	
	echo "To access the student's results, you can go to kymotsujason.ca/qgen/Generator.php?id=$uniqueid";
	echo "<br>";
	echo "Students can take the test by going to the homepage (kymotsujason.ca/qgen), <br>
	pressing the 'students' button and entering the following 'class id' or unique id: $uniqueid";
	echo "<br>";
	if ($operation == "add") {
		echo "<h1>$size Matrix Addition quiz </h1>";
		echo "<h2>Class: $classname by: $teachname </h2>";
		TeachAddition($uniqueid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	} else {
		echo "<h1>$size Matrix Multiplication quiz </h1>";
		echo "<h2>Class: $classname by: $teachname </h2>";
		TeachMultiplication($uniqueid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	}
}

function Go($identity, $o, $s, $r1, $r2, $da) {
	$operation = $o;
	$size = $s;
	$displayamount = $da;
	$uniqueid = uniqid();
	
	if ($r1 > $r2) {
		$upperBound = $r1;
		$lowerBound = $r2;
	} else {
		$lowerBound = $r1;
		$upperBound = $r2;
	}
	
	if (!isset($_POST["SUBMITTED"])) {
		$matrixarray = GenerateMatrices($size, $displayamount, $lowerBound, $upperBound);
	} else {
		$matrixarray = RetrieveMatrices($size, $displayamount);
	}
	
	if ($operation == "add") {
		echo "<h1>$size Matrix Addition quiz </h1>";
		GuestAddition($uniqueid, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	} else {
		echo "<h1>$size Matrix Multiplication quiz </h1>";
		GuestMultiplication($uniqueid, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	}
}

function Begin($identity, $uniqueid, $firstname, $lastname, $studentid) {
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	if (!$link->connect_error) {
		$sqlSelect = "SELECT teachername, classname, moperation, msize, mrange1, mrange2, mdisplayamount, element FROM class WHERE uniqueid='$uniqueid'";
		$result = $link->query($sqlSelect);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$teachname = $row["teachername"];
			$classname = $row["classname"];
			$operation = $row["moperation"];
			$size = $row["msize"];
			$displayamount = $row["mdisplayamount"];
			$lowerBound = $row["mrange1"];
			$upperBound = $row["mrange1"];
			$element = $row["element"];
		}
	} else {
		echo "Houston, we have a problem <br>";
		echo "Where the *beep* is the mysql server?";
	}
	
	if (isset($_POST["showresult"])) {
		$matrixarray = RetrieveMatrices($size, $displayamount);
	} else {
		$matrixarray = ReconstructMatrices($size, $displayamount, $element);
	}
	
	
	if ($operation == "add") {
		echo "<h1>$size Matrix Addition quiz </h1>";
		echo "<h2>Class: $classname by: $teachname </h2>";
		echo "<h3>Student: $firstname $lastname Student id: $studentid</h3>";
		Addition($uniqueid, $firstname, $lastname, $studentid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	} else {
		echo "<h1>$size Matrix Multiplication quiz </h1>";
		echo "<h2>Class: $classname by: $teachname </h2>";
		echo "<h3>Student: $firstname $lastname</h3>";
		Multiplication($uniqueid, $firstname, $lastname, $studentid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound);
	}
	$link->close();
}

function MySQLInit($teachname, $classname, $identity, $m, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	$uniqueid = uniqid();
	$class = $classname;
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	$elements = "";
	for ($n = 0; $n < $displayamount; $n++) {
		for ($k = 0; $k < 2; $k++) {
			for ($i = 0; $i < $m[0][0]->getRowCount(); $i++) {
				for ($j = 0; $j < $m[0][0]->getColumnCount(); $j++) {
					if ($j != $m[0][0]->getRowCount()-1) {
						$elements .= $m[$n][$k]->get($i, $j) . ",";
					} elseif ($j == $m[0][0]->getRowCount()-1) {
						$elements .= $m[$n][$k]->get($i, $j);
					}
				}
				if ($i != $m[0][0]->getColumnCount()-1) {
					$elements .= "|";
				}
			}
			if ($k != $m[0][0]->getColumnCount()-1) {
				$elements .= ":";
			}
		}
		if ($n != $m[0][0]->getColumnCount()-1) {
			$elements .= "_";
		}
	}
	if (!$link->connect_error) {
		$sqlInsert = "INSERT INTO class (uniqueid, teachername, classname, moperation, msize, mrange1, mrange2, mdisplayamount, element)
		VALUES ('$uniqueid', '$teachname', '$class', '$operation', '$size', '$lowerBound', '$upperBound', '$displayamount', '$elements')";
		$link->query($sqlInsert);
	} else {
		echo "Houston, we have a problem <br>";
		echo "Where the *beep* is the mysql server?";
	}
	$link->close();
	return $uniqueid;
}

function Addition($uniqueid, $firstname, $lastname, $studentid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		$qnum = $i+1;
		echo "<h4>Q$qnum</h4>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">+</div>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		SubmitAnswer($uniqueid, $firstname, $lastname, $studentid, $identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
	
}

function TeachAddition($uniqueid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		$qnum = $i+1;
		echo "<h4>Q$qnum</h4>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">+</div>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		DisplayAnswer($uniqueid, $identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
	
}

function GuestAddition($uniqueid, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		$qnum = $i+1;
		echo "<h4>Q$qnum</h4>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">+</div>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		DisplayAnswer($uniqueid, $identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
	
}

function Multiplication($uniqueid, $firstname, $lastname, $studentid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		$qnum = $i+1;
		echo "<h4>Q$qnum</h4>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">X</div>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		SubmitAnswer($uniqueid, $firstname, $lastname, $studentid, $identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
}

function TeachMultiplication($uniqueid, $teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		$qnum = $i+1;
		echo "<h4>Q$qnum</h4>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">X</div>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		DisplayAnswer($uniqueid, $identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
}

function GuestMultiplication($uniqueid, $identity, $matrixarray, $operation, $size, $displayamount, $upperBound, $lowerBound) {
	
	for ($i = 0; $i < count($matrixarray); $i++) {
		$qnum = $i+1;
		echo "<h4>Q$qnum</h4>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">X</div>";
		echo "<div style=\"float: left\">";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</div>";
		echo "<div style=\"float: left; padding: 20px;\">=</div>";
		echo "<div style=\"float: left\">";
		DisplayAnswer($uniqueid, $identity, $i, $matrixarray[$i][0], $matrixarray[$i][1], $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray);
		echo "</div>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	}
}

function DisplayMatrix($m) {
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

function DisplayAnswer($uniqueid, $identity, $k, $a, $b, $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray) {
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
		if ($identity === "Go") {
			echo "<input type=\"hidden\" name=\"GUEST\" value=\"$identity\"/>";
		} else {
			echo "<input type=\"hidden\" name=\"TEACHER\" value=\"$identity\"/>";
		}
		echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
		echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
		echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
		echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
		echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
		// submit
		if ($k == 0) {
			echo "<div style=\"text-align:center; margin: 5 0 0 0;\"><input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div>";
		}
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
		if ($identity === "Go") {
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
		//if ($k == 0) {
			echo "<div style=\"text-align:center; margin: 5 0 0 0;\"><input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div>";
		//}
		echo "<br style=\"clear: both\">";
	}
	echo "</div></div>";
}

function SubmitAnswer($uniqueid, $firstname, $lastname, $studentid, $identity, $k, $a, $b, $operation, $size, $displayamount, $upperBound, $lowerBound, $matrixarray) {
	if (isset($_POST["showresult"])) {
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
		if ($identity === "Go") {
			echo "<input type=\"hidden\" name=\"GUEST\" value=\"$identity\"/>";
		} else {
			echo "<input type=\"hidden\" name=\"TEACHER\" value=\"$identity\"/>";
		}
		echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
		echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
		echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
		echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
		echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
		echo "<div style=\"float: left\">";
		echo "<br style=\"clear: both\">";
	} else {
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
		echo "<input type=\"hidden\" name=\"STUDENT\" value=\"$identity\"/>";
		echo "<input type=\"hidden\" name=\"id\" value=\"$uniqueid\"/>";
		echo "<input type=\"hidden\" name=\"first\" value=\"$firstname\"/>";
		echo "<input type=\"hidden\" name=\"last\" value=\"$lastname\"/>";
		echo "<input type=\"hidden\" name=\"studentid\" value=\"$studentid\"/>";
		echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
		echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
		echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
		echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
		echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
		// submit button
		//if ($k == $displayamount-1) {
			echo "<div style=\"text-align:center; margin: 5 0 0 0;\"><input type=\"submit\" value=\"Submit\" name=\"SUBMITTED\"></form></div>";
		//}
		echo "<br style=\"clear: both\">";
	}
	echo "</div></div>";
}

function GenerateMatrices($size, $displayamount, $lowerBound, $upperBound) {
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

function RetrieveMatrices($size, $displayamount) {
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

function ReconstructMatrices($size, $displayamount, $element) {
	$matrices = explode("_", $element);
	if ($size == "2x2") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1]],
					[$row2[0], $row2[1]]
				]);
			}
		}
	} elseif ($size == "3x3") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2]],
					[$row2[0], $row2[1], $row2[2]],
					[$row3[0], $row3[1], $row3[2]]
				]);
			}
		}
	} elseif ($size == "4x4") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3]],
					[$row2[0], $row2[1], $row2[2], $row2[3]],
					[$row3[0], $row3[1], $row3[2], $row3[3]],
					[$row4[0], $row4[1], $row4[2], $row4[3]]
				]);
			}
		}
	} elseif ($size == "5x5") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$row5 = explode(",", $matrix[4]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3], $row1[4]],
					[$row2[0], $row2[1], $row2[2], $row2[3], $row2[4]],
					[$row3[0], $row3[1], $row3[2], $row3[3], $row3[4]],
					[$row4[0], $row4[1], $row4[2], $row4[3], $row4[4]],
					[$row5[0], $row5[1], $row5[2], $row5[3], $row5[4]]
				]);
			}
		}
	} elseif ($size == "6x6") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$row5 = explode(",", $matrix[4]);
				$row6 = explode(",", $matrix[5]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3], $row1[4], $row1[5]],
					[$row2[0], $row2[1], $row2[2], $row2[3], $row2[4], $row2[5]],
					[$row3[0], $row3[1], $row3[2], $row3[3], $row3[4], $row3[5]],
					[$row4[0], $row4[1], $row4[2], $row4[3], $row4[4], $row4[5]],
					[$row5[0], $row5[1], $row5[2], $row5[3], $row5[4], $row5[5]],
					[$row6[0], $row6[1], $row6[2], $row6[3], $row6[4], $row6[5]]
				]);
			}
		}
	} elseif ($size == "7x7") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$row5 = explode(",", $matrix[4]);
				$row6 = explode(",", $matrix[5]);
				$row7 = explode(",", $matrix[6]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3], $row1[4], $row1[5], $row1[6]],
					[$row2[0], $row2[1], $row2[2], $row2[3], $row2[4], $row2[5], $row2[6]],
					[$row3[0], $row3[1], $row3[2], $row3[3], $row3[4], $row3[5], $row3[6]],
					[$row4[0], $row4[1], $row4[2], $row4[3], $row4[4], $row4[5], $row4[6]],
					[$row5[0], $row5[1], $row5[2], $row5[3], $row5[4], $row5[5], $row5[6]],
					[$row6[0], $row6[1], $row6[2], $row6[3], $row6[4], $row6[5], $row6[6]],
					[$row7[0], $row7[1], $row7[2], $row7[3], $row7[4], $row7[5], $row7[6]]
				]);
			}
		}
	} elseif ($size == "8x8") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$row5 = explode(",", $matrix[4]);
				$row6 = explode(",", $matrix[5]);
				$row7 = explode(",", $matrix[6]);
				$row8 = explode(",", $matrix[7]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3], $row1[4], $row1[5], $row1[6], $row1[7]],
					[$row2[0], $row2[1], $row2[2], $row2[3], $row2[4], $row2[5], $row2[6], $row2[7]],
					[$row3[0], $row3[1], $row3[2], $row3[3], $row3[4], $row3[5], $row3[6], $row3[7]],
					[$row4[0], $row4[1], $row4[2], $row4[3], $row4[4], $row4[5], $row4[6], $row4[7]],
					[$row5[0], $row5[1], $row5[2], $row5[3], $row5[4], $row5[5], $row5[6], $row5[7]],
					[$row6[0], $row6[1], $row6[2], $row6[3], $row6[4], $row6[5], $row6[6], $row6[7]],
					[$row7[0], $row7[1], $row7[2], $row7[3], $row7[4], $row7[5], $row7[6], $row7[7]],
					[$row8[0], $row8[1], $row8[2], $row8[3], $row8[4], $row8[5], $row8[6], $row8[7]]
				]);
			}
		}
	} elseif ($size == "9x9") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$row5 = explode(",", $matrix[4]);
				$row6 = explode(",", $matrix[5]);
				$row7 = explode(",", $matrix[6]);
				$row8 = explode(",", $matrix[7]);
				$row9 = explode(",", $matrix[8]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3], $row1[4], $row1[5], $row1[6], $row1[7], $row1[8]],
					[$row2[0], $row2[1], $row2[2], $row2[3], $row2[4], $row2[5], $row2[6], $row2[7], $row2[8]],
					[$row3[0], $row3[1], $row3[2], $row3[3], $row3[4], $row3[5], $row3[6], $row3[7], $row3[8]],
					[$row4[0], $row4[1], $row4[2], $row4[3], $row4[4], $row4[5], $row4[6], $row4[7], $row4[8]],
					[$row5[0], $row5[1], $row5[2], $row5[3], $row5[4], $row5[5], $row5[6], $row5[7], $row5[8]],
					[$row6[0], $row6[1], $row6[2], $row6[3], $row6[4], $row6[5], $row6[6], $row6[7], $row6[8]],
					[$row7[0], $row7[1], $row7[2], $row7[3], $row7[4], $row7[5], $row7[6], $row7[7], $row7[8]],
					[$row8[0], $row8[1], $row8[2], $row8[3], $row8[4], $row8[5], $row8[6], $row8[7], $row8[8]],
					[$row9[0], $row9[1], $row9[2], $row9[3], $row9[4], $row9[5], $row9[6], $row9[7], $row9[8]]
				]);
			}
		}
	} elseif ($size == "10x10") {
		for ($i = 0; $i < $displayamount; $i++) {
			for ($j = 0; $j < 2; $j++) {
				$questions = explode(":", $matrices[$i]);
				$matrix = explode("|", $questions[$j]);
				$row1 = explode(",", $matrix[0]);
				$row2 = explode(",", $matrix[1]);
				$row3 = explode(",", $matrix[2]);
				$row4 = explode(",", $matrix[3]);
				$row5 = explode(",", $matrix[4]);
				$row6 = explode(",", $matrix[5]);
				$row7 = explode(",", $matrix[6]);
				$row8 = explode(",", $matrix[7]);
				$row9 = explode(",", $matrix[8]);
				$row10 = explode(",", $matrix[9]);
				$matrixarray[$i][$j] = new Matrix([
					[$row1[0], $row1[1], $row1[2], $row1[3], $row1[4], $row1[5], $row1[6], $row1[7], $row1[8], $row1[9]],
					[$row2[0], $row2[1], $row2[2], $row2[3], $row2[4], $row2[5], $row2[6], $row2[7], $row2[8], $row2[9]],
					[$row3[0], $row3[1], $row3[2], $row3[3], $row3[4], $row3[5], $row3[6], $row3[7], $row3[8], $row3[9]],
					[$row4[0], $row4[1], $row4[2], $row4[3], $row4[4], $row4[5], $row4[6], $row4[7], $row4[8], $row4[9]],
					[$row5[0], $row5[1], $row5[2], $row5[3], $row5[4], $row5[5], $row5[6], $row5[7], $row5[8], $row5[9]],
					[$row6[0], $row6[1], $row6[2], $row6[3], $row6[4], $row6[5], $row6[6], $row6[7], $row6[8], $row6[9]],
					[$row7[0], $row7[1], $row7[2], $row7[3], $row7[4], $row7[5], $row7[6], $row7[7], $row7[8], $row7[9]],
					[$row8[0], $row8[1], $row8[2], $row8[3], $row8[4], $row8[5], $row8[6], $row8[7], $row8[8], $row8[9]],
					[$row9[0], $row9[1], $row9[2], $row9[3], $row9[4], $row9[5], $row9[6], $row9[7], $row9[8], $row9[9]],
					[$row10[0], $row10[1], $row10[2], $row10[3], $row10[4], $row10[5], $row10[6], $row10[7], $row10[8], $row10[9]]
				]);
			}
		}
	}
	return $matrixarray;
}

function MySQLMark() {
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
	$useranswer = "";
	$actualanswer = "";
	$matrixelements = "";
	$matrixarray = RetrieveMatrices($size, $displayamount);
	for ($n = 0; $n < $displayamount; $n++) {
		for ($i = 0; $i < $matrixarray[0][0]->getRowCount(); $i++) {
			for ($j = 0; $j < $matrixarray[0][0]->getColumnCount(); $j++) {
				if ($operation == "add") {
					$solution = $matrixarray[$n][0]->addMatrix($matrixarray[$n][1]);
				} else {
					$solution = $matrixarray[$n][0]->multiplyMatrix($matrixarray[$n][1]);
				}
				
				$useranswer .= "userAns$n$i$j=" . intval($_POST["userAns$n$i$j"]) . "&";
				$actualanswer .= "ANS$n$i$j=" . intval($_POST["ANS$n$i$j"]) . "&";
				
				$arrayValuea = $matrixarray[$n][0]->get($i, $j);
				$arrayValueb = $matrixarray[$n][1]->get($i, $j);
				
				$matrixelements .= "a$n$i$j=" . $arrayValuea . "&" . "b$n$i$j=" . $arrayValueb . "&";
				
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
	if (($ansCount % 4) == 0) {
		$userAns = $ansCount/4;
	} else {
		$userAns = floor($ansCount/4) * 4;
	}
	$mark = "$userAns" . "/" . "$displayamount";
	echo "<h1>Your mark is: $mark</h1>";
	$uniqueid = $_POST["id"];
	$studentid = $_POST["studentid"];
	$firstname = $_POST["first"];
	$lastname = $_POST["last"];
	$useranswer = rtrim($useranswer, "&");
	$actualanswer = rtrim($actualanswer, "&");
	$matrixelements = rtrim($matrixelements, "&");
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	if (!$link->connect_error) {
		$sqlSubmit = "INSERT INTO marks (uniqueid, studentid, firstname, lastname, mark, userans, actualans, matrixelements)
		VALUES ('$uniqueid', '$studentid', '$firstname', '$lastname', '$mark', '$useranswer', '$actualanswer', '$matrixelements')";
		$link->query($sqlSubmit);
	} else {
		echo "Houston, we have a problem <br>";
		echo "Where the *beep* is the mysql server?";
	}
	$link->close();
}

function ShowMarks($uniqueid) {
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	if (isset($_POST["showresult"])) {
		Begin("Begin", $uniqueid, $_POST["firstname"], $_POST["lastname"], $_POST["studentid"]);
	} elseif (!$link->connect_error) {
		$sqlSelect = "SELECT uniqueid, studentid, firstname, lastname, mark, userans, actualans, matrixelements FROM marks WHERE uniqueid='$uniqueid'";
		$result = $link->query($sqlSelect);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				echo $row["firstname"] . " " . $row["lastname"] . " (" . $row["studentid"] . "): " . $row["mark"];
				
				$useranswer = $row['userans'];
				$actualanswer = $row['actualans'];
				$matrixelements = $row['matrixelements'];
				
				$id = explode("&", $useranswer);
				for ($i = 0; $i < count($id); $i++) {
					$key = explode("=", $id[$i]);
					echo "<input type=\"hidden\" name=\"$key[0]\" value=\"$key[1]\"/>";
				}
				
				$id = explode("&", $actualanswer);
				for ($i = 0; $i < count($id); $i++) {
					$key = explode("=", $id[$i]);
					echo "<input type=\"hidden\" name=\"$key[0]\" value=\"$key[1]\"/>";
				}
				
				$id = explode("&", $matrixelements);
				for ($i = 0; $i < count($id); $i++) {
					$key = explode("=", $id[$i]);
					echo "<input type=\"hidden\" name=\"$key[0]\" value=\"$key[1]\"/>";
				}
				
				$firstname = $row["firstname"];
				$lastname = $row["lastname"];
				$studentid = $row["studentid"];
				echo "<br>";
				echo "<form method=\"POST\" id=\"showresult\">";
				echo "<input type=\"hidden\" name=\"firstname\" value=\"$firstname\"/>";
				echo "<input type=\"hidden\" name=\"lastname\" value=\"$lastname\"/>";
				echo "<input type=\"hidden\" name=\"studentid\" value=\"$studentid\"/>";
				echo "<input type=\"hidden\" name=\"useranswer\" value=\"$useranswer\"/>";
				echo "<input type=\"hidden\" name=\"actualanswer\" value=\"$actualanswer\"/>";
				echo "<input type=\"hidden\" name=\"matrixelements\" value=\"$matrixelements\"/>";
				echo "<input type=\"submit\" value=\"See Answer\" name=\"showresult\">";
				echo "<br><br>";
			}
		} else {
			echo "Houston, we have a problem <br>";
			echo "Where the *beep* is the mysql server?";
		}
	}
	$link->close();
}

if (isset($_POST["STUDENT"]) && isset($_POST["SUBMITTED"])){
	MySQLMark();
} elseif (strpos($_SERVER["REQUEST_URI"], "?id=") !== false) {
	echo "<h1>Class Results:</h1>";
	$request = $_SERVER["REQUEST_URI"];
	$uniqueid = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "?id=")+4, strlen($request));
	ShowMarks($uniqueid);
} else {
	if (!empty($_GET["STUDENT"])) {
		Begin($_GET["STUDENT"], $_GET["classid"], $_GET["firstname"], $_GET["lastname"], $_GET["studentid"]);
	} elseif (!empty($_POST["TEACHER"])){
		Initiate($_POST["name"], $_POST["class"], $_POST["TEACHER"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"]);
	} else {
		Go($_POST["GUEST"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"]);
	}
}

?>