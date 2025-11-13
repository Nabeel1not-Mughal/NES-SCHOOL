<?php
require_once('tcpdf/tcpdf.php');

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'student_dashboard';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$id = $_GET['id'] ?? 0;
$result = $conn->query("SELECT students.*, courses.name AS course_name
    FROM students
    LEFT JOIN courses ON students.course = courses.id
    WHERE students.id = $id");
$student = $result->fetch_assoc();
if (!$student)
    die("Student not found");

$currentDate = date("d-m-Y");

// TCPDF setup
class MYPDF extends TCPDF
{
    // Footer
    public function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('helvetica', 'I', 15);
        $this->SetTextColor(26, 124, 79); // Green
        $this->Cell(0, 10, 'NES SCHOOL – Nurturing Minds, Inspiring Futures.', 0, 0, 'C');
    }
}

// $registration_no = "NES-" . (rand(1000, 999999));

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// ================= STYLISH GREEN BORDER ================= //
$borderColor = [26, 124, 79]; // #1a7c4f
$pdf->SetLineStyle(['width' => 0.7, 'color' => $borderColor]);
$pdf->Rect(10, 10, 190, 277, 'D'); // outer rectangle
$pdf->SetLineStyle(['width' => 0.3, 'color' => $borderColor, 'dash' => '2,2']); // inner dashed
$pdf->Rect(12, 12, 186, 273, 'D');

// ================= HEADER ================= //
$html = '
<style>
.school-name { color:#1a7c4f; font-size:40px; font-weight:bolder; text-align:center; }
.admission { background-color:#d9f7d9; color:#1a7c4f; font-weight:bold; font-size:18px; text-align:center; padding:10px; margin:12px 0; border:1px solid #1a7c4f; border-radius:5px; }
.row-label { font-weight:bold; color:#1a7c4f; width:120px; vertical-align:top; }
.dots { border-bottom:1px dotted #000; width:100%; display:inline-block; }
.photo-cell { border:2px solid #1a7c4f; width:60mm; height:75mm; text-align:center; vertical-align:top; padding:2px; }
</style>

<table width="100%" cellpadding="5" cellspacing="0">
<tr>
    <td width="70"><img src="image/school_logo.png" width="70"></td>
    <td class="school-name" width="380">NES SCHOOL</td>
    <td></td>
</tr>
</table>

<div class="admission">ADMISSION FORM</div>
';

// ================= STUDENT PHOTO ================= //
// Get first image only if multiple exist
$imageFiles = glob("uploads/Student_ID_" . $student['id'] . "_*.*");
$imagePath = null;
if (!empty($imageFiles))
    $imagePath = $imageFiles[0]; // first file only

$html .= '<table width="100%" cellpadding="5" cellspacing="5"><tr>';
$html .= '<td width="65%"></td>'; // empty left side for spacing
$html .= '<td width="25%" height="65%" class="photo-cell">';
if ($imagePath && file_exists($imagePath)) {
    $html .= '<img src="' . $imagePath . '" width="160" height="190"/>';
} else {
    $html .= 'No Photo';
}
$html .= '</td></tr></table><br>';

// ================= FORM FIELDS ================= //
$html .= '<table cellpadding="6" cellspacing="6" width="100%" style="font-size:14px;">';

// Reg & Date
$html .= '<tr>';
$html .= '<td class="row-label">Reg #:</td><td><span class="dots" id="reg">NES-' .$student['reg_no'] . '</span></td>';
$html .= '<td class="row-label">Date:</td><td><span class="dots">' . $currentDate . '</span></td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td class="row-label">Roll #:</td><td><span class="dots">' . $student['id'] . '</span></td>';
$html .= '</tr>';
// Name & DOB
$html .= '<tr>';
$html .= '<td class="row-label">Name:</td><td><span class="dots">' . $student['student_name'] . '</span></td>';
$html .= '</tr>';

// Father's Name
$html .= '<tr>';
$html .= '<td class="row-label">Father\'s Name:</td><td colspan="3"><span class="dots">' . $student['father_name'] . '</span></td>';
$html .= '</tr>';

// Date of Birth
$html .= '<tr>';
$html .= '<td class="row-label">Date of Birth:</td><td><span class="dots">' . $student['dob'] . '</span></td>';
$html .= '</tr>';

// Course
$html .= '<tr>';
$html .= '<td class="row-label">Course:</td><td colspan="3"><span class="dots">' . $student['course_name'] . '</span></td>';
$html .= '</tr>';

// Address
$html .= '<tr>';
$html .= '<td class="row-label">Address:</td><td colspan="3"><span class="dots">' . ($student['address'] ?? '') . '</span></td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td class="row-label">Phone:</td><td colspan="3"><span class="dots">' . ($student['phone'] ?? '') . '</span></td>';
$html .= '</tr>';

// B-Form
$html .= '<tr>';
$html .= '<td class="row-label">B-Form:</td><td colspan="3"><span class="dots">' . ($student['b_form'] ?? '') . '</span></td>';
$html .= '</tr>';

$html .= '</table><br><br>';

// ================= SIGNATURE FIELDS ================= //
$html .= '
<table cellpadding="25" width="100%" style="text-align:center; font-size:12px; color:#1a7c4f;">
<tr>
<td>Father\'s Signature</td>
<td>Principal\'s Signature</td>
</tr>
</table>
';

// ================= STAMP & SIGNATURE IMAGES ================= //
if (file_exists("image/stamp.png"))
    $pdf->Image("image/stamp.png", 140, 130, 40, 40, '', '', '', true);
if (file_exists("uploads/principal_signature.png"))
    $pdf->Image("uploads/principal_signature.png", 120, 240, 55, 25, '', '', '', true);

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("AdmissionForm_" . $student['student_name'] . ".pdf", "I");
?>
