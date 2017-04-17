<head>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<title> QGen </title>

<?php

include("Matrix.php");
include("MatrixException.php");
include("LUDecomposition.php");
use MCordingley\LinearAlgebra\Matrix;


// Grade student quiz if submitted
if (isset($_POST["STUDENT"]) && isset($_POST["SUBMITTED"])){
	SaveMark(RetrieveMatrices($_POST["msize"], $_POST["displayamount"]));
	backToQgen();

// Show class results if there is an id on the url
} elseif ($_POST["CHECKMARK"]) {
	echo "<h1>Class Results:</h1>";
	ShowMarks($_POST["KEY"], $_POST["PASS"]);
} else {
	if (!empty($_GET["STUDENT"])) { // Show quiz for id entered
		ShowQuizStudent($_GET["STUDENT"], $_GET["classid"], $_GET["firstname"], $_GET["lastname"], $_GET["studentid"]);
	} elseif (!empty($_POST["TEACHER"])){ // Shows the teacher the quiz he created
		ShowQuizTeacher($_POST["name"], $_POST["class"], $_POST["TEACHER"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"], $_POST["password"]);
	} else {
		if (isset($_POST["PDF"])) { // PDF mode
			GenerateQuiz($_POST["GUEST"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"], "PDF");
		} else { // Guest mode
			GenerateQuiz($_POST["GUEST"], $_POST["operations"], $_POST["msize"], $_POST["range1"], $_POST["range2"], $_POST["displayamount"]);
		}
	}
}

///////////////////////////////////////////////////////////////////HIGHEST LEVEL DISPLAY FUCNTIONS

// Shows the quize to a user wanting to practice
function GenerateQuiz($identity, $operation, $size, $r1, $r2, $displayamount, $mode="Input") {
	$uniqueid = uniqid();
	
	if ($r1 > $r2) {
		$upperBound = $r1;
		$lowerBound = $r2;
	} else {
		$lowerBound = $r1;
		$upperBound = $r2;
	}
	
	if (!isset($_POST["SUBMITTED"])) {
		$matrixarray = GenerateMatrices($size, $displayamount, $lowerBound, $upperBound, $operation);
	} else {
		$matrixarray = RetrieveMatrices($size, $displayamount);
	}

	// pass values
	echo "<form method=\"POST\" id=\"submission\">";
	echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
	echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
	echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
	echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
	echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
	echo "<input type=\"hidden\" name=\"GUEST\" value=\"$identity\"/>";

	ShowQuiz($matrixarray, $operation, $mode);

	echo "</form>";
}

// Shows the quiz to a teacher that just made the quiz
function ShowQuizTeacher($teachname, $classname, $identity, $operation, $size, $r1, $r2, $displayamount, $password) {
	if ($r1 > $r2) {
		$upperBound = $r1;
		$lowerBound = $r2;
	} else {
		$lowerBound = $r1;
		$upperBound = $r2;
	}

	$matrixarray = GenerateMatrices($size, $displayamount, $lowerBound, $upperBound, $operation);
	$uniqueid = SaveQuiz($teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $password);
	
	echo "<h1>ID: $uniqueid</h1><br>";
	echo "Students can take the test by going to the homepage (kymotsujason.ca/qgen), <br>
	pressing the 'student' button and entering the ID. You can check the grades by presing <br>
	'teacher', then 'check marks'. Don't forget the password you set.";
	
	echo "<br><h1>Answer Key</h1><br>";
	ShowQuiz($matrixarray, $operation, "Show Answer");
}

// Shows the quiz to a student taking the quiz
function ShowQuizStudent($identity, $uniqueid, $firstname, $lastname, $studentid) {
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	if (!$link->connect_error) {
		$sqlSelect = "SELECT * FROM QUIZZES WHERE id='$uniqueid'";
		$result = $link->query($sqlSelect);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$teachname = $row["prof"];
			$classname = $row["class"];
			$element = $row["elements"];
		}
		else {
			echo "Invalid Quiz ID.";
			backToQgen();
			return;
		}
	} else {
		echo "Problem with SQL. <br>" . $link->error;
	}
	$matrixarray = ReconstructMatrices($element);	
	$operation = $matrixarray["operation"];
	$size = count($matrixarray[0][0][0]);
	$displayamount = count($matrixarray)-1;
	$link->close();

	echo "<h1>Quiz</h1>";
	echo "<h2>Class: $classname<br>Professor: $teachname </h2>";
	echo "<h3>Student id: $studentid</h3>";

	// pass values
	echo "<form method=\"POST\" id=\"submission\">";
	echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";
	echo "<input type=\"hidden\" name=\"msize\" value=\"$size\"/>";
	echo "<input type=\"hidden\" name=\"displayamount\" value=\"$displayamount\"/>";
	echo "<input type=\"hidden\" name=\"range2\" value=\"$upperBound\"/>";
	echo "<input type=\"hidden\" name=\"range1\" value=\"$lowerBound\"/>";
	echo "<input type=\"hidden\" name=\"operation\" value=\"$operation\"/>";
	echo "<input type=\"hidden\" name=\"TEACHER\" value=\"$identity\"/>";
	echo "<input type=\"hidden\" name=\"STUDENT\" value=\"$identity\"/>";
	echo "<input type=\"hidden\" name=\"id\" value=\"$uniqueid\"/>";
	echo "<input type=\"hidden\" name=\"first\" value=\"$firstname\"/>";
	echo "<input type=\"hidden\" name=\"last\" value=\"$lastname\"/>";
	echo "<input type=\"hidden\" name=\"studentid\" value=\"$studentid\"/>";
	echo "<input type=\"hidden\" name=\"operations\" value=\"$operation\"/>";

	ShowQuiz($matrixarray, $matrixarray["operation"],"Quiz");

	echo "</form>";
}

// Displays a quiz. Can be configured in several modes
// MODE          			
// "Input"		 			Allows user to input answers
// "Show Answer" 			Shows correct answer
// "Show Student Answer"	Shows student answer, pulled from the matrix array
// "PDF"					Shows only the question
function ShowQuiz($matrixarray, $operation, $mode="Input") {

	if ($mode == "Input") {
		echo "<form><input type=\"submit\" value=\"PDF mode\" class=\"PDFButton\" name=\"PDF\"></form>";
	}

	$len = count($matrixarray);

	if ($matrixarray["operation"]) {
		$len--;
	}

	echo "<table class=\"quizTable\">";


	for ($i = 0; $i < $len; $i++) {
		
		$qnum = $i+1;
		echo "<tr>";

		// "Q#"
		echo "<td><b class=\"questionNumber\">$qnum.</b></td>";

		// First matrix
		echo "<td>";
		DisplayMatrix($matrixarray[$i][0]);
		echo "</td>";

		if ($operation == "add") {
			// "+"
			echo "<TD class=\"mathSymbol\">+</td>";
		} elseif ($operation == "multi") {
			echo "<TD class=\"mathSymbol\">*</td>";
		}
		
		// Second matrix
		echo "<td>";
		DisplayMatrix($matrixarray[$i][1]);
		echo "</td>";

		// "="
		echo "<TD class=\"mathSymbol\">=</td>";

		// input
		echo "<td>";
		if ($mode === "Show Answer") {
			DisplayMatrix($matrixarray[$i][2]);
		} elseif ($mode === "Show Student Answer") {
			DisplayMatrix($matrixarray[$i][3]);
		} elseif ($mode === "Input" || $mode === "Quiz") {
			DisplayInputs($matrixarray[$i], $i);
		} elseif ($mode === "PDF") {
			DisplayEmptyMatrix($matrixarray[0][0]->getColumnCount());
		}
		echo "</td>";
		echo "</tr>";
	}

	echo "<tr><td></td><td></td><td></td><td></td><td></td><td>";
	if ($mode === "Input" || $mode === "Quiz") {
		echo "<input type=\"submit\" value=\"Submit\" class=\"submitButton\" name=\"SUBMITTED\">";
	}
	echo "</td></tr></table>";
}

///////////////////////////////////////////////////////////////////LOWEST LEVEL DISPLAY FUNCTIONS

// displays a button that takes you back to the main page

function backToQgen() {
	echo "<form action=\"http://kymotsujason.ca/qgen/index.html\"><input type=\"submit\" class=\"backToQgen\" value=\"Back to QGen\" /></form>";
}

// displays a matrix in html
function DisplayMatrix($matrix) {
	echo "<table class=\"matrixArray\"><tr>";
	for ($i = 0; $i < $matrix->getColumnCount(); $i++) {
		echo "<tr>";
		for ($j = 0; $j < $matrix->getRowCount(); $j++) {
			$arrayValue = $matrix->get($i, $j);
			echo "<td class=\"matrixData\"> $arrayValue</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}

// empyty matrix
function DisplayEmptyMatrix($size) {
	echo "<table class=\"matrixArray\"><tr>";
	for ($i = 0; $i < $size; $i++) {
		echo "<tr>";
		for ($j = 0; $j < $size; $j++) {
			echo "<td class=\"matrixData\">&nbsp;</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}

// takes an array of three matrices and displays an array of inputs in html
// if the state of the page is submitted, we color the correct answers green
// and the incorrect answers red
function DisplayInputs($matrices, $k) {
	for ($i = 0; $i < $matrices[0]->getRowCount(); $i++) {
		for ($j = 0; $j < $matrices[0]->getColumnCount(); $j++) {

			if (isset($_POST["SUBMITTED"])) {
				if ($_POST["userAns$k$i$j"] == "") { // so it doesn't turn it into 0;
					$colour = "red";
					$userAns = "";
				} 
				else {
					$userAns = intval($_POST["userAns$k$i$j"]);
					$realAns = intval($_POST["ANS$k$i$j"]);
					if ($userAns == $realAns) {
						$colour = "green";
					} else {
						$colour = "red";
					}
				}
				echo "<input style=\"background-color: $colour; color: white;\" type=\"text\" size=\"1\" name=\"userAns$k$i$j\" value = \"$userAns\">";
			} else {
				echo "<input type=\"text\" size=\"1\" name=\"userAns$k$i$j\">";
			}
			
			// hidden values
			$arrayValuea = $matrices[0]->get($i, $j);
			$arrayValueb = $matrices[1]->get($i, $j);
			$sol = $matrices[2]->get($i, $j);
			
			echo "<input type=\"hidden\" name=\"a$k$i$j\" value=\"$arrayValuea\"/>";
			echo "<input type=\"hidden\" name=\"b$k$i$j\" value=\"$arrayValueb\"/>";
			echo "<input type=\"hidden\" name=\"ANS$k$i$j\" value=\"$sol\"/>";
		}
		echo "<br>";
	}
}

///////////////////////////////////////////////////////////////////DATABASE ACCESS

// returns an ASCII encoded version of the current matrix array
// depth param can be set to 4 to include user answers
function SaveState($matrixarray, $operation, $displayamount, $depth=3) {
	$elements = "";

	for ($n = 0; $n < $displayamount; $n++) { // questions
		if ($n > 0) {
			$elements .= "_";
		}
		for ($k = 0; $k < $depth; $k++) { // matrices
			if ($k > 0) {
				$elements .= ":";	
			}
			for ($i = 0; $i < $matrixarray[0][0]->getRowCount(); $i++) { // rows
				if ($i > 0) {
					$elements .= "|";
				}
				for ($j = 0; $j < $matrixarray[0][0]->getColumnCount(); $j++) { // elements
					if ($j > 0) {
						$elements .= ",";
					}
					$elements .= $matrixarray[$n][$k]->get($i, $j);
				}
			}
		}
	}

	$elements .= "_" . $operation;
	return $elements;
}

// Saves quiz to database
function SaveQuiz($teachname, $classname, $identity, $matrixarray, $operation, $size, $displayamount, $password) {

	// condense matrices and operation to an ASCII string
	$elements = SaveState($matrixarray, $operation, $displayamount);
	$uniqueid = uniqid();
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	if (!$link->connect_error) {
		$sqlInsert = "INSERT INTO QUIZZES (id, class, prof, elements, password)
		VALUES ('$uniqueid', '$classname', '$teachname', '$elements', '$password')";
		$link->query($sqlInsert);
	} else {
		echo "Problem with SQL. <br>" . $link->error;
	}
	$link->close();
	return $uniqueid;
}

// Grade student quiz: returns the number of correct responses
function GradeQuiz($matrixarray) {

	$correct = 0;
	foreach ($matrixarray as &$matrices) {

		if ($matrices[2] == $matrices[3]) {
			$correct++;
		}
	}

	return $correct;
}

// Grade student quiz
function SaveMark($matrixarray) {

	$correct = GradeQuiz($matrixarray);

	// display student's mark
	$displayamount = $_POST["displayamount"];
	$mark = "$correct" . "/" . "$displayamount";

	$uniqueid = $_POST["id"];
	$studentid = $_POST["studentid"];
	$operation = $_POST["operation"];
	
	if ($studentid == "") {
		echo "Invalid student id";
		return;
	}

	$elements = SaveState($matrixarray, $operation, $displayamount, 4); // 4 is for depth, see SaveState for more info
	// upload to database
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	if (!$link->connect_error) {
		$sqlSelect = "SELECT * FROM GRADES WHERE quizNum = '$uniqueid' AND studentNum = '$studentid'";
		$result = $link->query($sqlSelect);
		if ($result->num_rows > 0) {
				echo "$studentid has already completed this quiz.";
				$link->close();
				return;
		} else {
				$sqlInsert = "INSERT INTO GRADES (quizNum, studentNum, grade, elements) VALUES ('$uniqueid', '$studentid', '$mark', '$elements')";
				$link->query($sqlInsert);
		}
	} else {
		echo "Problem with SQL. <br>" . $link->error;
	}
	$link->close();

	echo "<h1>Your mark is: $mark</h1>";
}

// Looks at database to see marks of students who have taken the given exam.
function ShowMarks($uniqueid, $password) {

	// pull marks from database
	$link = new mysqli("localhost", "kymot_qgen", "cmpt386isaclass", "kymotsujason_qgen");
	// check password and id
	$checkPW = "SELECT password FROM QUIZZES WHERE id='$uniqueid'";
	$checkPass = $link->query($checkPW);
	if ($checkPass->num_rows == 1) {
		$quizRow = $checkPass->fetch_assoc();
		if ($quizRow["password"] != $password) {
			echo "Invalid password.";
			return;		
		} 
	} else {
		echo "Invalid ID.";
		return;
	}
	


	if (isset($_POST["showresult"])) {	
		$studentId = $_POST["studentid"];
		$matrixarray=ReconstructMatrices($_POST["elements"]);
		
		echo "<h3>$studentId</h3>";
		
		ShowQuiz($matrixarray, $_POST["operation"], "Show Student Answer");
	
	} elseif (!$link->connect_error) {
		$sqlSelect = "SELECT * FROM GRADES WHERE quizNum='$uniqueid'";
		$result = $link->query($sqlSelect);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				echo $row["studentNum"] . " : " . $row["grade"];
				$elements = $row["elements"];
				$studentid = $row["studentNum"];
				$operation = array_pop(explode("_", $elements));
				echo "  ";
				echo "<form method=\"POST\" id=\"showresult\">";
				echo "<input type=\"hidden\" name=\"studentid\" value=\"$studentid\"/>";
				echo "<input type=\"hidden\" name=\"elements\" value=\"$elements\"/>";
				echo "<input type=\"hidden\" name=\"operation\" value=\"$operation\"/>";
				echo "<input type=\"hidden\" name=\"PASS\" value=\"$password\" />";
				echo "<input type=\"hidden\" name=\"KEY\" value=\"$uniqueid\" />";
				echo "<input type=\"hidden\" name=\"CHECKMARK\" value=\"true\" />";
				echo "<input type=\"submit\" value=\"See Answer\" name=\"showresult\"></form>";
				echo "<br><br>";
			}
		} else {
			echo "No students have taken the quiz yet.";
		}
	} else {
		echo "Problem with SQL. <br>" . $link->error;
	}
	$link->close();
}

///////////////////////////////////////////////////////////////////MATRIX GENERATION AND RETREIVAL
// We store our matrices in arrays. Each array entry has
// 1. The first matrix for the question
// 2. The second matrix for the question
// 3. The solution to the problem
// 4. The solution that the user inputted (if avaliable)

// Construct matrices, return array
function GenerateMatrices($size, $displayamount, $lowerBound, $upperBound, $operation) {

	// Generate questions
	for ($a = 0; $a < $displayamount; $a++) { // question
		for ($b = 0; $b < 2; $b++) { // two matrices per question
			$matrix = [];
			for ($i = 0; $i < $size; $i++) {
				$row = [];
				for ($j = 0; $j < $size; $j++) {
					$row[] = rand($lowerBound,$upperBound);
				}
			$matrix[] = $row;
			}
			$matrixarray[$a][$b] = new Matrix($matrix);
		}
	}

	// Generate answers
	foreach ($matrixarray as $question => $matrices) {
		if ($operation == "add") {
			$matrixarray[$question][2] = $matrices[0]->addMatrix($matrices[1]);
		} elseif ($operation == "multi") {
			$matrixarray[$question][2] = $matrices[0]->multiplyMatrix($matrices[1]);
		}
	}
	return $matrixarray;
}


// Get matrices from cookies, return array
function RetrieveMatrices($size, $displayamount) {
	$toCheck = ["a", "b", "ANS", "userAns"];
	foreach ($toCheck as $n => $k) {
	
		for ($a = 0; $a < $displayamount; $a++) { // question
			
			// First matrix
			$matrix = [];
			for ($i = 0; $i < $size; $i++) {
				$row = [];
				for ($j = 0; $j < $size; $j++) {
					$row[] = intval($_POST["$k$a$i$j"]);		
				}
			$matrix[] = $row;
			}
			$matrixarray[$a][$n] = new Matrix($matrix);	
		}

	}
	return $matrixarray;
}

// Get matrices from SQL, return array
function ReconstructMatrices($element) {
	// deconstruct encoded matrices
	$questions = explode("_", $element);
	$operation = array_pop($questions);
	foreach ($questions as $q => $question) {
		$questions[$q] = explode(":", $question);
		foreach ($questions[$q] as $m => $matrix) {
			$questions[$q][$m] = explode("|", $matrix);
			foreach ($questions[$q][$m] as $r => $row) {
				$questions[$q][$m][$r] = explode(",", $row);
			}
		}
	}

	// generate matrix array
	for ($a = 0; $a < count($questions); $a++) { // question
		for ($b = 0; $b < 4; $b++) { // two matrices per question, plus a correct answer and a student answer
			$matrix = [];
			for ($i = 0; $i < count($questions[0][0]); $i++) {
				$row = [];
				for ($j = 0; $j < count($questions[0][0]); $j++) {	
					$row[] = intval($questions[$a][$b][$i][$j]);
				}
			$matrix[] = $row;
			}
			$matrixarray[$a][$b] = new Matrix($matrix);
		}
	
	}
	
	$matrixarray["operation"] = $operation;
	return $matrixarray;
}

?>
