<?php
session_start();
require_once '../php/database.php';

// Verify student session
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../index.php?role=student');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get student ID
    $stmt_student = $pdo->prepare("SELECT id FROM etudiants WHERE id_user = :user_id");
    $stmt_student->bindParam(':user_id', $user_id);
    $stmt_student->execute();
    $student_id = $stmt_student->fetchColumn();

    if (!$student_id) {
        $_SESSION['error'] = "Informations étudiant introuvables.";
        header('Location: ../php/logout.php');
        exit();
    }

    // Get all grades for the student with subject names and teacher info
    $stmt_grades = $pdo->prepare("
        SELECT m.nom_matiere, n.note, n.commentaire, n.date_creation, en.nom AS enseignant_nom, en.prenom AS enseignant_prenom
        FROM notes n
        JOIN matieres m ON n.id_matiere = m.id
        JOIN enseignants en ON n.id_enseignant = en.id
        WHERE n.id_etudiant = :student_id
        ORDER BY m.nom_matiere, n.date_creation DESC
    ");
    $stmt_grades->bindParam(':student_id', $student_id);
    $stmt_grades->execute();
    $grades = $stmt_grades->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des notes : " . $e->getMessage();
    header('Location: grades.php');
    exit();
}

// Include FPDF library
require_once '../php/lib/fpdf186/fpdf.php';

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo
        $this->Image('../images/logo_keyce.JPEG',10,6,30);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,'Mes Resultats',0,0,'C');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

if (empty($grades)) {
    $pdf->Cell(0,10,'Aucun resultat disponible.',0,1);
} else {
    // Table header
    $pdf->SetFillColor(200,220,255);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(50,10,'Matiere',1,0,'C',true);
    $pdf->Cell(20,10,'Note',1,0,'C',true);
    $pdf->Cell(70,10,'Commentaire',1,0,'C',true);
    $pdf->Cell(30,10,'Date',1,0,'C',true);
    $pdf->Cell(40,10,'Enseignant',1,1,'C',true);

    // Table rows
    $pdf->SetFont('Arial','',12);
    foreach ($grades as $grade) {
        $pdf->Cell(50,10,utf8_decode($grade['nom_matiere']),1);
        $pdf->Cell(20,10,$grade['note'].'/20',1,0,'C');
        $pdf->Cell(70,10,utf8_decode($grade['commentaire']),1);
        $pdf->Cell(30,10,date('d/m/Y', strtotime($grade['date_creation'])),1,0,'C');
        $pdf->Cell(40,10,utf8_decode($grade['enseignant_prenom'].' '.$grade['enseignant_nom']),1,1);
    }
}

$pdf->Output('D', 'Mes_Resultats.pdf');
exit();
?>
