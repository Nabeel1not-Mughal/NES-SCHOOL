<?php
require_once __DIR__ . '/tcpdf/tcpdf.php';

$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT students.*, courses.name AS course_name 
        FROM students 
        LEFT JOIN courses ON students.course = courses.id 
        ORDER BY students.id DESC";
$result = $conn->query($sql);

// Custom PDF class for footer
class MYPDF extends TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(
            0,
            10,
            '© ' . date('Y') . ' NES School | Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(),
            0,
            false,
            'C'
        );
    }
}

$pdf = new MYPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('NES School');
$pdf->SetTitle('All Students Report');
$pdf->SetHeaderData('', 0, "NES School", "Students Report | Generated on " . date('d-m-Y'));
$pdf->setHeaderFont(['helvetica', '', 12]);
$pdf->setFooterFont(['helvetica', '', 9]);
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();

// --- Modern Styling ---
$html = '
<style>
    h2 {
        text-align:center; 
        color:#2e7d32;
        font-family: helvetica;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th {
        background-color: #2e7d32;
        color: #fff;
        font-weight: bold;
        text-align: center;
    }
    td {
        background-color: #f9f9f9;
        text-align: center;
    }
    tr:nth-child(even) td {
        background-color: #e8f5e9;
    }
</style>
';

$html .= '<h2>All Students List</h2>';
$html .= '<table border="1" cellpadding="6">
<thead>
<tr>
    <th>Sr.No</th>
    <th>Name</th>
    <th>Father</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Course</th>
    <th>DOB</th>
    <th>Gender</th>
</tr>
</thead><tbody>';

$i = 1;
while ($row = $result->fetch_assoc()) {
    $dob = !empty($row['dob']) ? date("d-m-Y", strtotime($row['dob'])) : 'N/A';

    $html .= '<tr>
        <td>' . $i++ . '</td>
        <td>' . htmlspecialchars($row['student_name']) . '</td>
        <td>' . htmlspecialchars($row['father_name']) . '</td>
        <td>' . htmlspecialchars($row['phone']) . '</td>
        <td>' . htmlspecialchars($row['address']) . '</td>
        <td>' . htmlspecialchars($row['course_name']) . '</td>
        <td>' . $dob . '</td>
        <td>' . htmlspecialchars($row['gender']) . '</td>
    </tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('students_list.pdf', 'I');
